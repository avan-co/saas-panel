<?php

namespace App\Models;

use CodeIgniter\Model;

class FinJournalEntryModel extends Model
{
    protected $table         = 'fin_journal_entries';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'entry_date', 'reference', 'description', 'source_type', 'source_id'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForTenant(int $tenantId, int $limit = 50): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('entry_date', 'DESC')->limit($limit)->findAll();
    }
}
