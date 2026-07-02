<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinanceMvpExtension extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE fin_accounts MODIFY type ENUM('bank','cash','card','wallet','petty_cash','personal') NOT NULL DEFAULT 'bank'");

        if (! $this->db->fieldExists('is_active', 'fin_categories')) {
            $this->forge->addColumn('fin_categories', [
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'sort_order'],
            ]);
        }

        if (! $this->db->fieldExists('project_id', 'fin_transactions')) {
            $this->forge->addColumn('fin_transactions', [
                'project_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'category_id'],
                'transfer_to_account_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'account_id'],
                'contact_name'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'description'],
            ]);
        }

        if (! $this->db->fieldExists('permissions', 'tenant_memberships')) {
            $this->forge->addColumn('tenant_memberships', [
                'permissions' => ['type' => 'TEXT', 'null' => true, 'after' => 'role'],
                'manager_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'permissions'],
                'department'  => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'manager_id'],
            ]);
        }

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'category_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'year'        => ['type' => 'SMALLINT', 'unsigned' => true],
            'month'       => ['type' => 'TINYINT', 'unsigned' => true],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'year', 'month']);
        $this->forge->createTable('fin_budgets', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 160],
            'type'        => ['type' => 'ENUM', 'constraint' => ['tax', 'insurance', 'rent', 'loan', 'check', 'contract', 'other'], 'default' => 'other'],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'due_date'    => ['type' => 'DATE'],
            'status'      => ['type' => 'ENUM', 'constraint' => ['pending', 'paid', 'overdue'], 'default' => 'pending'],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'due_date']);
        $this->forge->createTable('fin_payment_reminders', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'scope'      => ['type' => 'ENUM', 'constraint' => ['platform', 'tenant', 'user'], 'default' => 'user'],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'info'],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 160],
            'body'       => ['type' => 'TEXT', 'null' => true],
            'link'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'read_at']);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('notifications', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('fin_payment_reminders', true);
        $this->forge->dropTable('fin_budgets', true);
    }
}
