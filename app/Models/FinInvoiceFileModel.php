<?php

namespace App\Models;

use CodeIgniter\Model;

class FinInvoiceFileModel extends Model
{
    protected $table         = 'fin_invoice_files';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'invoice_id', 'file_path', 'original_name', 'mime', 'size'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForInvoice(int $invoiceId, int $tenantId): array
    {
        return $this->where('invoice_id', $invoiceId)->where('tenant_id', $tenantId)->findAll();
    }
}
