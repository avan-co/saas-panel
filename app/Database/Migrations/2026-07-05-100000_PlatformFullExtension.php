<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PlatformFullExtension extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('economic_code', 'tenants')) {
            $this->forge->addColumn('tenants', [
                'economic_code'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'currency'],
                'national_id'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'economic_code'],
                'vat_registered' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'national_id'],
                'approval_threshold' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 10000000, 'after' => 'vat_registered'],
            ]);
        }

        if (! $this->db->fieldExists('subtotal', 'fin_invoices')) {
            $this->forge->addColumn('fin_invoices', [
                'subtotal'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'amount'],
                'vat_amount'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'subtotal'],
                'vat_rate'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 10, 'after' => 'vat_amount'],
                'modian_uuid'    => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'description'],
                'modian_status'  => ['type' => 'ENUM', 'constraint' => ['none', 'pending', 'sent', 'confirmed', 'rejected'], 'default' => 'none', 'after' => 'modian_uuid'],
            ]);
        }

        if (! $this->db->fieldExists('approval_status', 'fin_transactions')) {
            $this->forge->addColumn('fin_transactions', [
                'approval_status' => ['type' => 'ENUM', 'constraint' => ['approved', 'pending', 'rejected'], 'default' => 'approved', 'after' => 'txn_date'],
                'approved_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'approval_status'],
                'approved_at'     => ['type' => 'DATETIME', 'null' => true, 'after' => 'approved_by'],
            ]);
        }

        if (! $this->db->fieldExists('insurance_number', 'payroll_employees')) {
            $this->forge->addColumn('payroll_employees', [
                'insurance_number' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'national_id'],
                'marital_status'   => ['type' => 'ENUM', 'constraint' => ['single', 'married'], 'default' => 'single', 'after' => 'insurance_number'],
                'children_count'   => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0, 'after' => 'marital_status'],
            ]);
        }

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'invoice_id'  => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255],
            'quantity'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 1],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'vat_rate'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 10],
            'line_total'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_id');
        $this->forge->createTable('fin_invoice_lines', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 40],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 60],
            'entity_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'summary'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta'        => ['type' => 'TEXT', 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'created_at']);
        $this->forge->createTable('audit_logs', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'year'       => ['type' => 'SMALLINT', 'unsigned' => true],
            'month'      => ['type' => 'TINYINT', 'unsigned' => true],
            'locked_by'  => ['type' => 'INT', 'unsigned' => true],
            'locked_at'  => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'year', 'month']);
        $this->forge->createTable('period_locks', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'type'        => ['type' => 'ENUM', 'constraint' => ['asset', 'liability', 'equity', 'income', 'expense'], 'default' => 'expense'],
            'is_system'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'code']);
        $this->forge->createTable('fin_chart_accounts', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'entry_date'  => ['type' => 'DATE'],
            'reference'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'source_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'source_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'entry_date']);
        $this->forge->createTable('fin_journal_entries', true);

        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'journal_entry_id' => ['type' => 'INT', 'unsigned' => true],
            'account_code'     => ['type' => 'VARCHAR', 'constraint' => 20],
            'debit'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'credit'           => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'description'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('journal_entry_id');
        $this->forge->createTable('fin_journal_lines', true);

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
            'entity_type'  => ['type' => 'VARCHAR', 'constraint' => 40],
            'entity_id'    => ['type' => 'INT', 'unsigned' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'requested_by' => ['type' => 'INT', 'unsigned' => true],
            'reviewed_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'note'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'status']);
        $this->forge->createTable('approval_requests', true);

        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'run_id'             => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'          => ['type' => 'INT', 'unsigned' => true],
            'employee_id'        => ['type' => 'INT', 'unsigned' => true],
            'base_salary'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'insurable_salary'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'insurance_employee' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'insurance_employer' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'tax_amount'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'net_pay'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('run_id');
        $this->forge->createTable('payroll_run_items', true);

        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'        => ['type' => 'INT', 'unsigned' => true],
            'project_id'       => ['type' => 'INT', 'unsigned' => true],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'assignee_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['todo', 'in_progress', 'done', 'cancelled'], 'default' => 'todo'],
            'priority'         => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high'], 'default' => 'medium'],
            'due_date'         => ['type' => 'DATE', 'null' => true],
            'sort_order'       => ['type' => 'SMALLINT', 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_tasks', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'key_hash'   => ['type' => 'VARCHAR', 'constraint' => 64],
            'key_prefix' => ['type' => 'VARCHAR', 'constraint' => 12],
            'scopes'     => ['type' => 'TEXT', 'null' => true],
            'last_used'  => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'key_prefix']);
        $this->forge->createTable('api_keys', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'url'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'events'     => ['type' => 'TEXT', 'null' => true],
            'secret'     => ['type' => 'VARCHAR', 'constraint' => 64],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('webhooks', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'params'      => ['type' => 'TEXT', 'null' => true],
            'result'      => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fin_scenarios', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('fin_scenarios', true);
        $this->forge->dropTable('webhooks', true);
        $this->forge->dropTable('api_keys', true);
        $this->forge->dropTable('project_tasks', true);
        $this->forge->dropTable('payroll_run_items', true);
        $this->forge->dropTable('approval_requests', true);
        $this->forge->dropTable('fin_journal_lines', true);
        $this->forge->dropTable('fin_journal_entries', true);
        $this->forge->dropTable('fin_chart_accounts', true);
        $this->forge->dropTable('period_locks', true);
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('fin_invoice_lines', true);
    }
}
