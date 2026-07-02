<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->db->table('modules')->countAllResults() > 0) {
            return;
        }

        $now = Time::now()->toDateTimeString();

        $modules = [
            ['code' => 'dashboard', 'icon' => 'grid', 'sort_order' => 1],
            ['code' => 'finance', 'icon' => 'dollar', 'sort_order' => 2],
            ['code' => 'payroll', 'icon' => 'users', 'sort_order' => 3],
            ['code' => 'insurance', 'icon' => 'shield', 'sort_order' => 4],
            ['code' => 'tax', 'icon' => 'file', 'sort_order' => 5],
            ['code' => 'projects', 'icon' => 'folder', 'sort_order' => 6],
        ];

        foreach ($modules as $module) {
            $this->db->table('modules')->insert(array_merge($module, [
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
