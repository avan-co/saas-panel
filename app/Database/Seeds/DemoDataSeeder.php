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

        if ($this->db->table('tenants')->countAllResults() > 0) {
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
        }
    }
}
