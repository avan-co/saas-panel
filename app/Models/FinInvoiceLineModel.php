<?php

namespace App\Models;

use CodeIgniter\Model;

class FinInvoiceLineModel extends Model
{
    protected $table         = 'fin_invoice_lines';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['invoice_id', 'tenant_id', 'description', 'quantity', 'unit_price', 'vat_rate', 'line_total'];
    protected $useTimestamps = true;

    public function getForInvoice(int $invoiceId, int $tenantId): array
    {
        return $this->where('invoice_id', $invoiceId)->where('tenant_id', $tenantId)->findAll();
    }

    public function deleteForInvoice(int $invoiceId): void
    {
        $this->where('invoice_id', $invoiceId)->delete();
    }
}
