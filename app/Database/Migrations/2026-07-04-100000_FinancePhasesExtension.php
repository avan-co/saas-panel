<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinancePhasesExtension extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE tenant_memberships MODIFY role ENUM('owner','admin','accountant','hr','manager','viewer') NOT NULL DEFAULT 'viewer'");

        if (! $this->db->fieldExists('contact_id', 'fin_transactions')) {
            $this->forge->addColumn('fin_transactions', [
                'contact_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'contact_name'],
                'invoice_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'contact_id'],
            ]);
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 160],
            'type'       => ['type' => 'ENUM', 'constraint' => ['supplier', 'contractor', 'employee', 'customer', 'both'], 'default' => 'both'],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'tax_id'     => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'address'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'balance'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'note'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'type']);
        $this->forge->createTable('fin_contacts', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'contact_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'project_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'number'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'      => ['type' => 'ENUM', 'constraint' => ['draft', 'sent', 'paid', 'overdue', 'cancelled'], 'default' => 'draft'],
            'issue_date'  => ['type' => 'DATE'],
            'due_date'    => ['type' => 'DATE', 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'number']);
        $this->forge->createTable('fin_invoices', true);

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'unsigned' => true],
            'invoice_id'    => ['type' => 'INT', 'unsigned' => true],
            'file_path'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'mime'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'size'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_id');
        $this->forge->createTable('fin_invoice_files', true);

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
            'contact_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'direction'    => ['type' => 'ENUM', 'constraint' => ['received', 'payable'], 'default' => 'payable'],
            'check_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'bank'         => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'due_date'     => ['type' => 'DATE'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'deposited', 'cleared', 'bounced', 'paid'], 'default' => 'pending'],
            'note'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'due_date']);
        $this->forge->createTable('fin_checks', true);

        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'           => ['type' => 'INT', 'unsigned' => true],
            'bank'                => ['type' => 'VARCHAR', 'constraint' => 80],
            'principal'           => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'interest_rate'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'total_installments'  => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'paid_installments'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'installment_amount'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'start_date'          => ['type' => 'DATE', 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['active', 'paid', 'defaulted'], 'default' => 'active'],
            'note'                => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('fin_loans', true);

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'unsigned' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 160],
            'category'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'purchase_price' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'purchase_date'  => ['type' => 'DATE', 'null' => true],
            'custodian'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'location'       => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'serial_number'  => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['active', 'disposed'], 'default' => 'active'],
            'note'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('fin_assets', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('fin_assets', true);
        $this->forge->dropTable('fin_loans', true);
        $this->forge->dropTable('fin_checks', true);
        $this->forge->dropTable('fin_invoice_files', true);
        $this->forge->dropTable('fin_invoices', true);
        $this->forge->dropTable('fin_contacts', true);
    }
}
