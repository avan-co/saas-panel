<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollRunItemModel extends Model
{
    protected $table         = 'payroll_run_items';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'run_id', 'tenant_id', 'employee_id', 'base_salary', 'insurable_salary',
        'insurance_employee', 'insurance_employer', 'tax_amount', 'net_pay',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForRun(int $runId): array
    {
        return $this->select('payroll_run_items.*, payroll_employees.name, payroll_employees.national_id')
            ->join('payroll_employees', 'payroll_employees.id = payroll_run_items.employee_id')
            ->where('run_id', $runId)
            ->findAll();
    }
}
