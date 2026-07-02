<?php

namespace App\Libraries;

use App\Models\PayrollEmployeeModel;
use App\Models\ProjectModel;
use App\Models\ProjectTaskModel;
use App\Models\TimesheetModel;

class TimesheetService
{
    protected const MONTHLY_HOURS = 176.0;

    public function record(int $tenantId, array $data, array $tenant): int
    {
        $employee = model(PayrollEmployeeModel::class)->findForTenant((int) $data['employee_id'], $tenantId);

        if ($employee === null) {
            throw new \RuntimeException(lang('Payroll.not_found'));
        }

        if (model(ProjectModel::class)->findForTenant((int) $data['project_id'], $tenantId) === null) {
            throw new \RuntimeException(lang('Projects.not_found'));
        }

        $hours = $this->resolveHours($data);

        if ($hours <= 0) {
            throw new \RuntimeException(lang('Projects.timesheet_invalid_hours'));
        }

        $hourlyRate = (float) ($data['hourly_rate'] ?? 0);

        if ($hourlyRate <= 0) {
            $hourlyRate = (float) $employee['base_salary'] / self::MONTHLY_HOURS;
        }

        $laborCost = round($hours * $hourlyRate, 2);

        $sheetId = (int) model(TimesheetModel::class)->insert([
            'tenant_id'   => $tenantId,
            'employee_id' => (int) $data['employee_id'],
            'project_id'  => (int) $data['project_id'],
            'task_id'     => ! empty($data['task_id']) ? (int) $data['task_id'] : null,
            'work_date'   => (string) $data['work_date'],
            'start_time'  => $data['start_time'] ?? null,
            'end_time'    => $data['end_time'] ?? null,
            'hours'       => $hours,
            'hourly_rate' => $hourlyRate,
            'labor_cost'  => $laborCost,
            'status'      => 'approved',
            'note'        => $data['note'] ?? null,
        ]);

        if (! empty($data['task_id'])) {
            $task = model(ProjectTaskModel::class)->findForTenant((int) $data['task_id'], $tenantId);

            if ($task !== null) {
                model(ProjectTaskModel::class)->update((int) $data['task_id'], [
                    'actual_hours' => (float) ($task['actual_hours'] ?? 0) + $hours,
                    'actual_cost'  => (float) ($task['actual_cost'] ?? 0) + $laborCost,
                ]);
            }
        }

        service('erp')->onTimesheetRecorded($tenantId, $sheetId, $tenant);

        return $sheetId;
    }

    protected function resolveHours(array $data): float
    {
        if (! empty($data['hours'])) {
            return (float) $data['hours'];
        }

        $start = $data['start_time'] ?? null;
        $end   = $data['end_time'] ?? null;

        if ($start && $end) {
            $diff = strtotime((string) $end) - strtotime((string) $start);

            return $diff > 0 ? round($diff / 3600, 2) : 0.0;
        }

        return 0.0;
    }
}
