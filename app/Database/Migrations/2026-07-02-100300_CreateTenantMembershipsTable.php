<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantMembershipsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['owner', 'admin', 'accountant', 'hr', 'viewer'],
                'default'    => 'viewer',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'user_id']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('tenant_memberships', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tenant_memberships', true);
    }
}
