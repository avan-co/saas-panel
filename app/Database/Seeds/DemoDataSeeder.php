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
}
