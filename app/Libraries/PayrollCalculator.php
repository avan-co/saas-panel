<?php

namespace App\Libraries;

class PayrollCalculator
{
    protected float $employeeInsuranceRate = 0.07;
    protected float $employerInsuranceRate = 0.23;
    protected float $insuranceCeiling = 348000000; // monthly ceiling approx

    public function calculate(array $employee): array
    {
        $base = (float) $employee['base_salary'];
        $insurable = min($base, $this->insuranceCeiling);

        $insuranceEmployee = round($insurable * $this->employeeInsuranceRate, 0);
        $insuranceEmployer = round($insurable * $this->employerInsuranceRate, 0);
        $taxable = max(0, $base - $insuranceEmployee);
        $tax = $this->incomeTax($taxable);
        $net = $base - $insuranceEmployee - $tax;

        return [
            'base_salary'        => $base,
            'insurable_salary'   => $insurable,
            'insurance_employee' => $insuranceEmployee,
            'insurance_employer' => $insuranceEmployer,
            'tax_amount'         => $tax,
            'net_pay'            => $net,
        ];
    }

    protected function incomeTax(float $taxable): float
    {
        if ($taxable <= 0) {
            return 0.0;
        }

        // Simplified progressive brackets (monthly, IRR)
        $brackets = [
            [10000000, 0.0],
            [50000000, 0.10],
            [100000000, 0.15],
            [PHP_INT_MAX, 0.20],
        ];

        $remaining = $taxable;
        $prevLimit = 0;
        $tax       = 0.0;

        foreach ($brackets as [$limit, $rate]) {
            $slice = min($remaining, $limit - $prevLimit);

            if ($slice <= 0) {
                break;
            }

            $tax += $slice * $rate;
            $remaining -= $slice;
            $prevLimit = $limit;
        }

        return round($tax, 0);
    }

    public function dskRows(array $employees, int $year, int $month): array
    {
        $rows = [];

        foreach ($employees as $emp) {
            $calc = $this->calculate($emp);
            $rows[] = [
                'national_id'      => $emp['national_id'] ?? '',
                'insurance_number' => $emp['insurance_number'] ?? '',
                'name'             => $emp['name'],
                'days'             => 30,
                'insurable'        => $calc['insurable_salary'],
                'employee_share'   => $calc['insurance_employee'],
                'employer_share'   => $calc['insurance_employer'],
                'year'             => $year,
                'month'            => $month,
            ];
        }

        return $rows;
    }
}
