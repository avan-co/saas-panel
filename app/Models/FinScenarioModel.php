<?php

namespace App\Models;

use CodeIgniter\Model;

class FinScenarioModel extends Model
{
    protected $table         = 'fin_scenarios';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'params', 'result'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
