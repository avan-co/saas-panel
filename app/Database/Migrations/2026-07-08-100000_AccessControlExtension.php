<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AccessControlExtension extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('last_login_at', 'users')) {
            $this->forge->addColumn('users', [
                'last_login_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'status'],
                'last_login_ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true, 'after' => 'last_login_at'],
            ]);
        }

        if (! $this->db->fieldExists('subscription_starts_at', 'tenants')) {
            $this->forge->addColumn('tenants', [
                'subscription_starts_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'plan'],
                'subscription_ends_at'   => ['type' => 'DATETIME', 'null' => true, 'after' => 'subscription_starts_at'],
                'deleted_at'             => ['type' => 'DATETIME', 'null' => true, 'after' => 'updated_at'],
            ]);
        }

        if (! $this->db->fieldExists('person_id', 'tenant_memberships')) {
            $this->forge->addColumn('tenant_memberships', [
                'person_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'department'],
            ]);
        }

        $this->db->query("ALTER TABLE tenant_memberships MODIFY role ENUM('owner','admin','accountant','hr','manager','employee','viewer') NOT NULL DEFAULT 'viewer'");

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'leader_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('tenant_teams', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'team_id'    => ['type' => 'INT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['team_id', 'user_id']);
        $this->forge->createTable('tenant_team_members', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'project_id' => ['type' => 'INT', 'unsigned' => true],
            'team_id'    => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['project_id', 'team_id']);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('project_teams', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('project_teams', true);
        $this->forge->dropTable('tenant_team_members', true);
        $this->forge->dropTable('tenant_teams', true);

        if ($this->db->fieldExists('person_id', 'tenant_memberships')) {
            $this->forge->dropColumn('tenant_memberships', 'person_id');
        }

        if ($this->db->fieldExists('deleted_at', 'tenants')) {
            $this->forge->dropColumn('tenants', ['subscription_starts_at', 'subscription_ends_at', 'deleted_at']);
        }

        if ($this->db->fieldExists('last_login_at', 'users')) {
            $this->forge->dropColumn('users', ['last_login_at', 'last_login_ip']);
        }

        $this->db->query("ALTER TABLE tenant_memberships MODIFY role ENUM('owner','admin','accountant','hr','manager','viewer') NOT NULL DEFAULT 'viewer'");
    }
}
