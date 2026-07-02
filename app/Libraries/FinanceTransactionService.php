<?php

namespace App\Libraries;

use App\Models\FinAccountModel;
use App\Models\FinContactModel;
use App\Models\FinTransactionModel;

class FinanceTransactionService
{
    public function create(int $tenantId, array $payload, array $tenant): int
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (service('periodLock')->isLocked($tenantId, $payload['txn_date'])) {
                throw new \RuntimeException(lang('Finance.period_locked'));
            }

            $needsApproval = $this->needsApproval($payload, $tenant);
            $payload['approval_status'] = $needsApproval ? 'pending' : 'approved';

            if (! $needsApproval) {
                $payload['approved_by'] = (int) session('user_id');
                $payload['approved_at'] = date('Y-m-d H:i:s');
            }

            $txnModel = model(FinTransactionModel::class);
            $id       = $txnModel->insert($payload);

            if ($needsApproval) {
                model(\App\Models\ApprovalRequestModel::class)->insert([
                    'tenant_id'    => $tenantId,
                    'entity_type'  => 'transaction',
                    'entity_id'    => $id,
                    'amount'       => $payload['amount'],
                    'requested_by' => (int) session('user_id'),
                    'status'       => 'pending',
                ]);
            } else {
                $this->applyBalances($tenantId, $payload);
                service('journal')->recordTransaction($tenantId, $payload, $id);
                $this->syncContact($tenantId, $payload);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            service('audit')->log($tenantId, 'create', 'transaction', $id, $payload['description'] ?? '');
            service('webhook')->dispatch($tenantId, 'transaction.created', array_merge($payload, ['id' => $id]));

            return $id;
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    public function update(int $tenantId, int $id, array $payload, array $tenant): void
    {
        $txnModel = model(FinTransactionModel::class);
        $existing = $txnModel->findForTenant($id, $tenantId);

        if ($existing === null) {
            throw new \RuntimeException(lang('App.not_found'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (service('periodLock')->isLocked($tenantId, $existing['txn_date']) || service('periodLock')->isLocked($tenantId, $payload['txn_date'])) {
                throw new \RuntimeException(lang('Finance.period_locked'));
            }

            if ($existing['approval_status'] === 'approved') {
                $this->reverseBalances($tenantId, $existing);
                $this->syncContact($tenantId, $existing, $existing, true);
            }

            $needsApproval = $this->needsApproval($payload, $tenant) && ! ($existing['approval_status'] === 'approved' && (float) $payload['amount'] === (float) $existing['amount']);
            $payload['approval_status'] = $needsApproval ? 'pending' : 'approved';

            $txnModel->update($id, $payload);

            if ($payload['approval_status'] === 'approved') {
                $this->applyBalances($tenantId, $payload);
                service('journal')->recordTransaction($tenantId, $payload, $id);
                $this->syncContact($tenantId, $payload);
            }

            $db->transComplete();
            service('audit')->log($tenantId, 'update', 'transaction', $id);
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    public function delete(int $tenantId, int $id): void
    {
        $txnModel = model(FinTransactionModel::class);
        $existing = $txnModel->findForTenant($id, $tenantId);

        if ($existing === null) {
            throw new \RuntimeException(lang('App.not_found'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($existing['approval_status'] === 'approved') {
                $this->reverseBalances($tenantId, $existing);
                $this->syncContact($tenantId, $existing, $existing, true);
            }

            $txnModel->delete($id);
            $db->transComplete();
            service('audit')->log($tenantId, 'delete', 'transaction', $id);
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    public function approve(int $tenantId, int $txnId, int $reviewerId): void
    {
        $txnModel = model(FinTransactionModel::class);
        $txn      = $txnModel->findForTenant($txnId, $tenantId);

        if ($txn === null || $txn['approval_status'] !== 'pending') {
            throw new \RuntimeException(lang('App.not_found'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $txnModel->update($txnId, [
            'approval_status' => 'approved',
            'approved_by'     => $reviewerId,
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);

        $txn['approval_status'] = 'approved';
        $this->applyBalances($tenantId, $txn);
        service('journal')->recordTransaction($tenantId, $txn, $txnId);
        $this->syncContact($tenantId, $txn);

        model(\App\Models\ApprovalRequestModel::class)
            ->where('entity_type', 'transaction')
            ->where('entity_id', $txnId)
            ->set(['status' => 'approved', 'reviewed_by' => $reviewerId])
            ->update();

        $db->transComplete();
        service('audit')->log($tenantId, 'approve', 'transaction', $txnId);
    }

    public function payInvoice(int $tenantId, int $invoiceId, int $accountId, string $date): void
    {
        $invoice = model(\App\Models\FinInvoiceModel::class)->findForTenant($invoiceId, $tenantId);

        if ($invoice === null || $invoice['status'] === 'paid') {
            throw new \RuntimeException(lang('App.not_found'));
        }

        $tenant = service('tenantContext')->getTenant();

        $this->create($tenantId, [
            'tenant_id'       => $tenantId,
            'account_id'      => $accountId,
            'type'            => 'income',
            'amount'          => (float) $invoice['amount'],
            'description'     => 'پرداخت فاکتور ' . $invoice['number'],
            'contact_id'      => $invoice['contact_id'],
            'invoice_id'      => $invoiceId,
            'txn_date'        => $date,
            'approval_status' => 'approved',
        ], $tenant);

        model(\App\Models\FinInvoiceModel::class)->update($invoiceId, ['status' => 'paid']);
        service('audit')->log($tenantId, 'pay', 'invoice', $invoiceId);
    }

    protected function needsApproval(array $payload, array $tenant): bool
    {
        if ($payload['type'] !== 'expense') {
            return false;
        }

        $threshold = (float) ($tenant['approval_threshold'] ?? 10000000);

        return (float) $payload['amount'] >= $threshold;
    }

    protected function applyBalances(int $tenantId, array $payload): void
    {
        $amount = (float) $payload['amount'];
        $type   = $payload['type'];

        if ($type === 'transfer' && ! empty($payload['transfer_to_account_id'])) {
            $this->adjustAccount((int) $payload['account_id'], $tenantId, 'expense', $amount);
            $this->adjustAccount((int) $payload['transfer_to_account_id'], $tenantId, 'income', $amount);

            return;
        }

        $this->adjustAccount((int) $payload['account_id'], $tenantId, $type, $amount);
    }

    protected function reverseBalances(int $tenantId, array $txn): void
    {
        if ($txn['type'] === 'transfer' && ! empty($txn['transfer_to_account_id'])) {
            $this->adjustAccount((int) $txn['account_id'], $tenantId, 'expense', (float) $txn['amount'], true);
            $this->adjustAccount((int) $txn['transfer_to_account_id'], $tenantId, 'income', (float) $txn['amount'], true);

            return;
        }

        $this->adjustAccount((int) $txn['account_id'], $tenantId, (string) $txn['type'], (float) $txn['amount'], true);
    }

    protected function adjustAccount(int $accountId, int $tenantId, string $type, float $amount, bool $reverse = false): void
    {
        $accountModel = model(FinAccountModel::class);
        $account      = $accountModel->find($accountId);

        if ($account === null || (int) $account['tenant_id'] !== $tenantId) {
            return;
        }

        $delta = $type === 'income' ? $amount : -$amount;

        if ($reverse) {
            $delta = -$delta;
        }

        $accountModel->update($accountId, ['balance' => (float) $account['balance'] + $delta]);
    }

    protected function syncContact(int $tenantId, array $payload, ?array $previous = null, bool $reverse = false): void
    {
        $contactId = (int) ($payload['contact_id'] ?? 0);

        if ($contactId <= 0) {
            return;
        }

        $contactModel = model(FinContactModel::class);
        $contact      = $contactModel->findForTenant($contactId, $tenantId);

        if ($contact === null) {
            return;
        }

        $delta = 0.0;

        if ($payload['type'] === 'income') {
            $delta = -(float) $payload['amount'];
        } elseif ($payload['type'] === 'expense') {
            $delta = (float) $payload['amount'];
        }

        if ($reverse) {
            $delta = -$delta;
        }

        if ($previous !== null && (int) ($previous['contact_id'] ?? 0) === $contactId) {
            $prevDelta = $previous['type'] === 'income' ? -(float) $previous['amount'] : (float) $previous['amount'];
            $delta -= $prevDelta;
        }

        $contactModel->update($contactId, ['balance' => (float) $contact['balance'] + $delta]);
    }
}
