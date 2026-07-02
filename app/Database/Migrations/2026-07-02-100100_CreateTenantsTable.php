<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantsTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'trial', 'suspended'],
                'default'    => 'active',
            ],
            'plan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'starter',
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'default'    => 'Asia/Tehran',
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'IRR',
            ],
            'fiscal_year_start' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'default'    => 1,
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
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('owner_id');
        $this->forge->createTable('tenants', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tenants', true);
    }
}
