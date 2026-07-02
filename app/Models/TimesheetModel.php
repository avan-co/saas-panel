<?php

namespace App\Models;

use CodeIgniter\Model;

class TimesheetModel extends Model
{
    protected $table         = 'timesheets';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'employee_id', 'project_id', 'task_id', 'work_date',
        'start_time', 'end_time', 'hours', 'hourly_rate', 'labor_cost', 'status', 'note',
    ];
    protected $useTimestamps = true;

    public function getForProject(int $tenantId, int $projectId, int $limit = 100): array
    {
        return $this->select('timesheets.*, payroll_employees.name AS employee_name, project_tasks.title AS task_title')
            ->join('payroll_employees', 'payroll_employees.id = timesheets.employee_id')
            ->join('project_tasks', 'project_tasks.id = timesheets.task_id', 'left')
            ->where('timesheets.tenant_id', $tenantId)
            ->where('timesheets.project_id', $projectId)
            ->orderBy('work_date', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getForEmployeeMonth(int $tenantId, int $employeeId, int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        return $this->where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->where('work_date >=', $start)
            ->where('work_date <=', $end)
            ->where('status', 'approved')
            ->findAll();
    }

    public function hoursByProjectForMonth(int $tenantId, int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        $rows = $this->select('project_id, SUM(hours) AS total_hours')
            ->where('tenant_id', $tenantId)
            ->where('work_date >=', $start)
            ->where('work_date <=', $end)
            ->where('status', 'approved')
            ->groupBy('project_id')
            ->findAll();

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['project_id']] = (float) $row['total_hours'];
        }

        return $map;
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function countForTenant(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)->countAllResults();
    }
}
