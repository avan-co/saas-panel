<?php

namespace App\Models;

use CodeIgniter\Model;

class FinJournalLineModel extends Model
{
    protected $table         = 'fin_journal_lines';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['journal_entry_id', 'account_code', 'debit', 'credit', 'description'];
    protected $useTimestamps = false;
}
