<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $now = Time::now()->toDateTimeString();

        $this->db->table('users')->insert([
            'name'              => 'مدیر پلتفرم',
            'email'             => 'admin@demo.local',
            'password'          => password_hash('password', PASSWORD_DEFAULT),
            'locale'            => 'fa',
            'theme'             => 'system',
            'is_platform_admin' => 1,
            'status'            => 'active',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $adminId = $this->db->insertID();

        $modules = [
            ['code' => 'dashboard', 'icon' => 'grid', 'sort_order' => 1],
            ['code' => 'finance',   'icon' => 'dollar', 'sort_order' => 2],
            ['code' => 'payroll',   'icon' => 'users', 'sort_order' => 3],
            ['code' => 'insurance', 'icon' => 'shield', 'sort_order' => 4],
            ['code' => 'tax',       'icon' => 'file', 'sort_order' => 5],
            ['code' => 'projects',  'icon' => 'folder', 'sort_order' => 6],
        ];

        foreach ($modules as $module) {
            $this->db->table('modules')->insert(array_merge($module, [
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $allModuleIds = $this->db->table('modules')->select('id')->get()->getResultArray();

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
                'owner_id'          => $adminId,
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
                'user_id'    => $adminId,
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
