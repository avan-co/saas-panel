<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\FinTransactionModel;

class Transactions extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'No tenant']);
        }

        $limit = min(100, max(1, (int) ($this->request->getGet('limit') ?: 50)));

        return $this->response->setJSON([
            'data' => model(FinTransactionModel::class)->recentForTenant((int) $tenant['id'], $limit),
        ]);
    }

    public function store()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'No tenant']);
        }

        $json = $this->request->getJSON(true) ?: [];

        $rules = [
            'account_id' => 'required|integer',
            'type'       => 'required|in_list[income,expense,transfer]',
            'amount'     => 'required|decimal|greater_than[0]',
            'txn_date'   => 'required',
        ];

        if (! $this->validateData($json, $rules)) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $tenantId = (int) $tenant['id'];
        $payload  = [
            'tenant_id'              => $tenantId,
            'account_id'             => (int) $json['account_id'],
            'transfer_to_account_id' => isset($json['transfer_to_account_id']) ? (int) $json['transfer_to_account_id'] : null,
            'category_id'            => isset($json['category_id']) ? (int) $json['category_id'] : null,
            'project_id'             => isset($json['project_id']) ? (int) $json['project_id'] : null,
            'contact_id'             => isset($json['contact_id']) ? (int) $json['contact_id'] : null,
            'type'                   => (string) $json['type'],
            'amount'                 => (float) $json['amount'],
            'description'            => (string) ($json['description'] ?? ''),
            'txn_date'               => (string) $json['txn_date'],
        ];

        try {
            $id = service('financeTxn')->create($tenantId, $payload, $tenant);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $e->getMessage()]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'id'   => $id,
            'data' => model(FinTransactionModel::class)->findForTenant($id, $tenantId),
        ]);
    }

    public function show(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'No tenant']);
        }

        $txn = model(FinTransactionModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($txn === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        return $this->response->setJSON(['data' => $txn]);
    }
}
