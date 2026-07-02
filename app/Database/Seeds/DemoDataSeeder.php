<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DemoDataSeeder extends Seeder
{
    protected int $adminId = 0;

    public function setAdminId(int $adminId): self
    {
        $this->adminId = $adminId;

        return $this;
    }

    public function run(): void
    {
        if ($this->adminId === 0) {
            return;
        }

        if ($this->db->table('tenant_memberships')->where('user_id', $this->adminId)->countAllResults() > 0) {
            return;
        }

        $now = Time::now()->toDateTimeString();

        $tenants = [
            ['name' => 'رستوران نمونه', 'slug' => 'restaurant-demo', 'plan' => 'pro'],
            ['name' => 'فروشگاه نمونه', 'slug' => 'shop-demo', 'plan' => 'starter'],
            ['name' => 'آژانس نمونه', 'slug' => 'agency-demo', 'plan' => 'pro'],
        ];

        $tenantModuleMap = [
            'restaurant-demo' => ['dashboard', 'finance', 'payroll', 'insurance', 'tax'],
            'shop-demo'       => ['dashboard', 'finance'],
            'agency-demo'     => ['dashboard', 'finance', 'projects'],
        ];

        foreach ($tenants as $tenantData) {
            $this->db->table('tenants')->insert([
                'name'              => $tenantData['name'],
                'slug'              => $tenantData['slug'],
                'owner_id'          => $this->adminId,
                'status'            => 'active',
                'plan'              => $tenantData['plan'],
                'timezone'          => 'Asia/Tehran',
                'currency'          => 'IRR',
                'fiscal_year_start' => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $tenantId = $this->db->insertID();

            $this->db->table('tenant_memberships')->insert([
                'tenant_id'  => $tenantId,
                'user_id'    => $this->adminId,
                'role'       => 'owner',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $enabledCodes = $tenantModuleMap[$tenantData['slug']];
            $moduleRows   = $this->db->table('modules')->whereIn('code', $enabledCodes)->get()->getResultArray();

            foreach ($moduleRows as $mod) {
                $this->db->table('tenant_modules')->insert([
                    'tenant_id'  => $tenantId,
                    'module_id'  => $mod['id'],
                    'enabled'    => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (in_array('finance', $enabledCodes, true)) {
                $this->seedFinanceDemo((int) $tenantId, $now);
            }
            if (in_array('payroll', $enabledCodes, true)) {
                $this->seedPayrollDemo((int) $tenantId, $now);
            }
            if (in_array('insurance', $enabledCodes, true)) {
                $this->seedInsuranceDemo((int) $tenantId, $now);
            }
            if (in_array('tax', $enabledCodes, true)) {
                $this->seedTaxDemo((int) $tenantId, $now);
            }
            if (in_array('projects', $enabledCodes, true)) {
                $this->seedProjectsDemo((int) $tenantId, $now);
            }
        }
    }

    public function seedFinanceDemo(int $tenantId, string $now): void
    {
        if ($this->db->table('fin_accounts')->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $categories = [
            ['name' => 'فروش', 'type' => 'income', 'color' => '#10b981', 'sort_order' => 1],
            ['name' => 'خدمات', 'type' => 'income', 'color' => '#3b82f6', 'sort_order' => 2],
            ['name' => 'حقوق', 'type' => 'expense', 'color' => '#f59e0b', 'sort_order' => 3],
            ['name' => 'اجاره', 'type' => 'expense', 'color' => '#ef4444', 'sort_order' => 4],
            ['name' => 'متفرقه', 'type' => 'expense', 'color' => '#64748b', 'sort_order' => 5],
        ];

        $catIds = [];

        foreach ($categories as $cat) {
            $this->db->table('fin_categories')->insert(array_merge($cat, [
                'tenant_id'  => $tenantId,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
            $catIds[$cat['name']] = $this->db->insertID();
        }

        $this->db->table('fin_accounts')->insert([
            'tenant_id'  => $tenantId,
            'name'       => 'حساب اصلی',
            'type'       => 'bank',
            'balance'    => 48500000,
            'currency'   => 'IRR',
            'is_default' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $accountId = $this->db->insertID();

        $this->db->table('fin_accounts')->insert([
            'tenant_id'  => $tenantId,
            'name'       => 'صندوق',
            'type'       => 'cash',
            'balance'    => 3200000,
            'currency'   => 'IRR',
            'is_default' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $txns = [
            ['type' => 'income', 'amount' => 12450000, 'cat' => 'فروش', 'desc' => 'فروش ماه جاری', 'days' => 2],
            ['type' => 'expense', 'amount' => 3100000, 'cat' => 'حقوق', 'desc' => 'پرداخت حقوق پرسنل', 'days' => 5],
            ['type' => 'expense', 'amount' => 1800000, 'cat' => 'اجاره', 'desc' => 'اجاره محل', 'days' => 8],
            ['type' => 'income', 'amount' => 5200000, 'cat' => 'خدمات', 'desc' => 'درآمد خدمات', 'days' => 12],
            ['type' => 'expense', 'amount' => 450000, 'cat' => 'متفرقه', 'desc' => 'هزینه اداری', 'days' => 15],
        ];

        foreach ($txns as $txn) {
            $this->db->table('fin_transactions')->insert([
                'tenant_id'   => $tenantId,
                'account_id'  => $accountId,
                'category_id' => $catIds[$txn['cat']],
                'type'        => $txn['type'],
                'amount'      => $txn['amount'],
                'description' => $txn['desc'],
                'reference'   => null,
                'txn_date'    => date('Y-m-d', strtotime('-' . $txn['days'] . ' days')),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function seedPayrollDemo(int $tenantId, string $now): void
    {
        if ($this->db->table('payroll_employees')->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $employees = [
            ['name' => 'علی رضایی', 'job_title' => 'مدیر فروش', 'base_salary' => 45000000],
            ['name' => 'مریم احمدی', 'job_title' => 'حسابدار', 'base_salary' => 38000000],
            ['name' => 'حسین کریمی', 'job_title' => 'پشتیبانی', 'base_salary' => 28000000],
        ];

        foreach ($employees as $emp) {
            $this->db->table('payroll_employees')->insert([
                'tenant_id'   => $tenantId,
                'name'        => $emp['name'],
                'job_title'   => $emp['job_title'],
                'base_salary' => $emp['base_salary'],
                'status'      => 'active',
                'hired_at'    => date('Y-m-d', strtotime('-1 year')),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $this->db->table('payroll_runs')->insert([
            'tenant_id'      => $tenantId,
            'period_year'    => (int) date('Y'),
            'period_month'   => (int) date('m'),
            'total_amount'   => 111000000,
            'employee_count' => 3,
            'status'         => 'paid',
            'paid_at'        => date('Y-m-d'),
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
    }

    public function seedInsuranceDemo(int $tenantId, string $now): void
    {
        if ($this->db->table('insurance_policies')->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $policies = [
            ['policy_number' => 'SOC-1404-001', 'provider' => 'تأمین اجتماعی', 'type' => 'social', 'premium' => 8500000],
            ['policy_number' => 'HLT-1404-012', 'provider' => 'بیمه ایران', 'type' => 'health', 'premium' => 3200000],
        ];

        foreach ($policies as $p) {
            $this->db->table('insurance_policies')->insert([
                'tenant_id'     => $tenantId,
                'policy_number' => $p['policy_number'],
                'provider'      => $p['provider'],
                'type'          => $p['type'],
                'premium'       => $p['premium'],
                'start_date'    => date('Y-01-01'),
                'end_date'      => date('Y-12-31'),
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function seedTaxDemo(int $tenantId, string $now): void
    {
        if ($this->db->table('tax_periods')->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $year = (int) date('Y');
        $q    = (int) ceil((int) date('m') / 3);

        $this->db->table('tax_periods')->insert([
            'tenant_id'      => $tenantId,
            'period_year'    => $year,
            'period_quarter' => $q,
            'taxable_income' => 185000000,
            'tax_amount'     => 9250000,
            'status'         => 'pending',
            'due_date'       => date('Y-m-d', strtotime('+30 days')),
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->db->table('tax_periods')->insert([
            'tenant_id'      => $tenantId,
            'period_year'    => $year,
            'period_quarter' => max(1, $q - 1),
            'taxable_income' => 162000000,
            'tax_amount'     => 8100000,
            'status'         => 'paid',
            'due_date'       => date('Y-m-d', strtotime('-15 days')),
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
    }

    public function seedProjectsDemo(int $tenantId, string $now): void
    {
        if ($this->db->table('projects')->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $projects = [
            ['name' => 'طراحی سایت فروشگاه', 'code' => 'PRJ-001', 'client' => 'فروشگاه نمونه', 'budget' => 120000000, 'progress' => 75, 'status' => 'active'],
            ['name' => 'سیستم انبارداری', 'code' => 'PRJ-002', 'client' => 'انبار مرکزی', 'budget' => 85000000, 'progress' => 40, 'status' => 'active'],
            ['name' => 'مشاوره مالی', 'code' => 'PRJ-003', 'client' => 'شرکت آلفا', 'budget' => 45000000, 'progress' => 100, 'status' => 'completed'],
        ];

        foreach ($projects as $p) {
            $this->db->table('projects')->insert([
                'tenant_id'   => $tenantId,
                'name'        => $p['name'],
                'code'        => $p['code'],
                'client_name' => $p['client'],
                'status'      => $p['status'],
                'budget'      => $p['budget'],
                'progress'    => $p['progress'],
                'start_date'  => date('Y-m-d', strtotime('-60 days')),
                'end_date'    => date('Y-m-d', strtotime('+90 days')),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
