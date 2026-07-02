<?php

namespace App\Libraries;

class JournalService
{
    public function recordTransaction(int $tenantId, array $txn, ?int $txnId = null): void
    {
        $this->ensureChart($tenantId);

        $amount = (float) $txn['amount'];
        $type   = $txn['type'];
        $date   = $txn['txn_date'];

        if ($type === 'transfer') {
            return;
        }

        $cashCode = '1101';
        $catCode  = $type === 'income' ? '4101' : '5101';

        if (! empty($txn['category_id'])) {
            $cat = model(\App\Models\FinCategoryModel::class)->find($txn['category_id']);

            if ($cat !== null) {
                $catCode = $type === 'income' ? '41' . str_pad((string) $cat['id'], 2, '0', STR_PAD_LEFT) : '51' . str_pad((string) $cat['id'], 2, '0', STR_PAD_LEFT);
            }
        }

        $entryModel = model(\App\Models\FinJournalEntryModel::class);
        $lineModel  = model(\App\Models\FinJournalLineModel::class);

        $entryId = $entryModel->insert([
            'tenant_id'   => $tenantId,
            'entry_date'  => $date,
            'reference'   => 'TXN-' . ($txnId ?? ''),
            'description' => $txn['description'] ?? '',
            'source_type' => 'transaction',
            'source_id'   => $txnId,
        ]);

        if ($type === 'income') {
            $lineModel->insert(['journal_entry_id' => $entryId, 'account_code' => $cashCode, 'debit' => $amount, 'credit' => 0]);
            $lineModel->insert(['journal_entry_id' => $entryId, 'account_code' => $catCode, 'debit' => 0, 'credit' => $amount]);
        } else {
            $lineModel->insert(['journal_entry_id' => $entryId, 'account_code' => $catCode, 'debit' => $amount, 'credit' => 0]);
            $lineModel->insert(['journal_entry_id' => $entryId, 'account_code' => $cashCode, 'debit' => 0, 'credit' => $amount]);
        }
    }

    protected function ensureChart(int $tenantId): void
    {
        $model = model(\App\Models\FinChartAccountModel::class);

        if ($model->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $defaults = [
            ['code' => '1101', 'name' => 'صندوق و بانک', 'type' => 'asset'],
            ['code' => '4101', 'name' => 'درآمد عملیاتی', 'type' => 'income'],
            ['code' => '5101', 'name' => 'هزینه عملیاتی', 'type' => 'expense'],
            ['code' => '2101', 'name' => 'بدهی‌ها', 'type' => 'liability'],
            ['code' => '3101', 'name' => 'سرمایه', 'type' => 'equity'],
        ];

        foreach ($defaults as $row) {
            $model->insert(array_merge($row, ['tenant_id' => $tenantId, 'is_system' => 1]));
        }
    }
}
