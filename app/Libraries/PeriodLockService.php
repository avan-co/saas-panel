<?php

namespace App\Libraries;

class PeriodLockService
{
    public function isLocked(int $tenantId, string $date): bool
    {
        $ts = strtotime($date);

        if ($ts === false) {
            return false;
        }

        $year  = (int) date('Y', $ts);
        $month = (int) date('m', $ts);

        return model(\App\Models\PeriodLockModel::class)
            ->where('tenant_id', $tenantId)
            ->where('year', $year)
            ->where('month', $month)
            ->countAllResults() > 0;
    }

    public function lock(int $tenantId, int $year, int $month, int $userId): void
    {
        $model = model(\App\Models\PeriodLockModel::class);

        if ($model->where('tenant_id', $tenantId)->where('year', $year)->where('month', $month)->first()) {
            return;
        }

        $model->insert([
            'tenant_id' => $tenantId,
            'year'      => $year,
            'month'     => $month,
            'locked_by' => $userId,
            'locked_at' => date('Y-m-d H:i:s'),
        ]);

        service('audit')->log($tenantId, 'lock', 'period', null, "Locked {$year}/{$month}");
    }
}
