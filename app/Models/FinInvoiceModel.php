<?php

namespace App\Models;

use CodeIgniter\Model;

class FinInvoiceModel extends Model
{
    protected $table         = 'fin_invoices';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'contact_id', 'project_id', 'number', 'amount', 'subtotal', 'vat_amount', 'vat_rate',
        'status', 'issue_date', 'due_date', 'description', 'modian_uuid', 'modian_status',
    ];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->select('fin_invoices.*, fin_contacts.name AS contact_name, projects.name AS project_name')
            ->join('fin_contacts', 'fin_contacts.id = fin_invoices.contact_id', 'left')
            ->join('projects', 'projects.id = fin_invoices.project_id', 'left')
            ->where('fin_invoices.tenant_id', $tenantId)
            ->orderBy('issue_date', 'DESC')
            ->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->select('fin_invoices.*, fin_contacts.name AS contact_name')
            ->join('fin_contacts', 'fin_contacts.id = fin_invoices.contact_id', 'left')
            ->where('fin_invoices.id', $id)
            ->where('fin_invoices.tenant_id', $tenantId)
            ->first();

        return $row ?: null;
    }

    public function search(int $tenantId, string $q, int $limit = 20): array
    {
        return $this->select('fin_invoices.*, fin_contacts.name AS contact_name')
            ->join('fin_contacts', 'fin_contacts.id = fin_invoices.contact_id', 'left')
            ->where('fin_invoices.tenant_id', $tenantId)
            ->groupStart()
            ->like('fin_invoices.number', $q)
            ->orLike('fin_invoices.description', $q)
            ->orLike('fin_contacts.name', $q)
            ->groupEnd()
            ->limit($limit)
            ->findAll();
    }
}
