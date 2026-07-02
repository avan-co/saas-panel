<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinanceTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'type'       => ['type' => 'ENUM', 'constraint' => ['income', 'expense'], 'default' => 'expense'],
            'color'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#64748b'],
            'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('fin_categories');

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'type'       => ['type' => 'ENUM', 'constraint' => ['bank', 'cash', 'card'], 'default' => 'bank'],
            'balance'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'currency'   => ['type' => 'VARCHAR', 'constraint' => 8, 'default' => 'IRR'],
            'is_default' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('fin_accounts');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'account_id'  => ['type' => 'INT', 'unsigned' => true],
            'category_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type'        => ['type' => 'ENUM', 'constraint' => ['income', 'expense', 'transfer'], 'default' => 'expense'],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reference'   => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'txn_date'    => ['type' => 'DATE'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'txn_date']);
        $this->forge->addKey('account_id');
        $this->forge->createTable('fin_transactions');
    }

    public function down(): void
    {
        $this->forge->dropTable('fin_transactions', true);
        $this->forge->dropTable('fin_accounts', true);
        $this->forge->dropTable('fin_categories', true);
    }
}
