<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class RoleDemoSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->db->table('users')->where('email', 'admin@agency.local')->countAllResults() > 0) {
            return;
        }

        $tenant = $this->db->table('tenants')->where('slug', 'agency-demo')->get()->getRowArray();

        if ($tenant === null) {
            return;
        }

        $tenantId = (int) $tenant['id'];
        $now      = Time::now()->toDateTimeString();
        $userModel = model(\App\Models\UserModel::class);

        $users = [
            ['name' => 'مدیر آژانس', 'email' => 'admin@agency.local', 'role' => 'admin', 'department' => 'مدیریت'],
            ['name' => 'مدیر پروژه', 'email' => 'manager@agency.local', 'role' => 'manager', 'department' => 'پروژه'],
            ['name' => 'توسعه‌دهنده ۱', 'email' => 'dev1@agency.local', 'role' => 'employee', 'department' => 'توسعه'],
            ['name' => 'توسعه‌دهنده ۲', 'email' => 'dev2@agency.local', 'role' => 'employee', 'department' => 'توسعه'],
            ['name' => 'مشاهده‌گر', 'email' => 'viewer@agency.local', 'role' => 'viewer', 'department' => 'پشتیبانی'],
        ];

        $userIds = [];

        foreach ($users as $u) {
            $userId = $userModel->insert([
                'name'              => $u['name'],
                'email'             => $u['email'],
                'password'          => password_hash('password', PASSWORD_DEFAULT),
                'locale'            => 'fa',
                'theme'             => 'system',
                'is_platform_admin' => 0,
                'status'            => 'active',
            ]);
            $userIds[$u['email']] = $userId;

            $this->db->table('tenant_memberships')->insert([
                'tenant_id'  => $tenantId,
                'user_id'    => $userId,
                'role'       => $u['role'],
                'department' => $u['department'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->db->table('tenant_teams')->insert([
            'tenant_id'      => $tenantId,
            'name'           => 'تیم توسعه',
            'description'    => 'تیم فنی پروژه‌ها',
            'leader_user_id' => $userIds['manager@agency.local'],
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        $teamId = $this->db->insertID();

        foreach (['dev1@agency.local', 'dev2@agency.local', 'manager@agency.local'] as $email) {
            $this->db->table('tenant_team_members')->insert([
                'team_id'    => $teamId,
                'user_id'    => $userIds[$email],
                'created_at' => $now,
            ]);
        }

        $projects = $this->db->table('projects')->where('tenant_id', $tenantId)->get()->getResultArray();

        if ($projects === []) {
            $seeder = new DemoDataSeeder(config(\Config\Database::class));
            $seeder->seedProjectsDemo($tenantId, $now);
            $projects = $this->db->table('projects')->where('tenant_id', $tenantId)->get()->getResultArray();
        }

        foreach ($projects as $i => $project) {
            $projectId = (int) $project['id'];

            if ($i === 0) {
                $this->db->table('project_teams')->insert([
                    'tenant_id'  => $tenantId,
                    'project_id' => $projectId,
                    'team_id'    => $teamId,
                    'created_at' => $now,
                ]);
                $this->db->table('projects')->where('id', $projectId)->update([
                    'manager_user_id' => $userIds['manager@agency.local'],
                ]);
            }

            if ($i === 1) {
                $this->db->table('project_members')->insert([
                    'tenant_id'  => $tenantId,
                    'project_id' => $projectId,
                    'user_id'    => $userIds['dev1@agency.local'],
                    'role'       => 'expert',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $persons = [
            ['name' => 'شرکت آلفا', 'roles' => ['customer'], 'phone' => '02112345678'],
            ['name' => 'تأمین‌کننده بتا', 'roles' => ['supplier'], 'phone' => '02187654321'],
            ['name' => 'علی رضایی', 'roles' => ['employee', 'contractor'], 'phone' => '09121234567'],
        ];

        foreach ($persons as $p) {
            $this->db->table('persons')->insert([
                'tenant_id'  => $tenantId,
                'name'       => $p['name'],
                'phone'      => $p['phone'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $personId = $this->db->insertID();

            foreach ($p['roles'] as $role) {
                $this->db->table('person_roles')->insert([
                    'person_id'  => $personId,
                    'role'       => $role,
                    'created_at' => $now,
                ]);
            }
        }

        $this->db->table('tenants')->where('id', $tenantId)->update([
            'subscription_starts_at' => date('Y-m-d H:i:s', strtotime('-3 months')),
            'subscription_ends_at'   => date('Y-m-d H:i:s', strtotime('+9 months')),
        ]);
    }
}
