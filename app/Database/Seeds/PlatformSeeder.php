<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Database;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('ModuleSeeder');

        $userModel = model(\App\Models\UserModel::class);
        $userModel->insert([
            'name'              => 'مدیر پلتفرم',
            'email'             => 'admin@demo.local',
            'password'          => password_hash('password', PASSWORD_DEFAULT),
            'locale'            => 'fa',
            'theme'             => 'system',
            'is_platform_admin' => 1,
            'status'            => 'active',
        ]);

        $adminId = (int) $userModel->getInsertID();

        $demoSeeder = new DemoDataSeeder(config(Database::class));
        $demoSeeder->setAdminId($adminId)->run();

        $roleSeeder = new RoleDemoSeeder(config(Database::class));
        $roleSeeder->run();
    }
}
