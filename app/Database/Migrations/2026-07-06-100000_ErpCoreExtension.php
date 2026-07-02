<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ErpCoreExtension extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 160],
            'national_id' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'phone'       => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'address'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'national_id']);
        $this->forge->createTable('persons', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'person_id'  => ['type' => 'INT', 'unsigned' => true],
            'role'       => ['type' => 'ENUM', 'constraint' => ['employee', 'customer', 'supplier', 'contractor', 'shareholder'], 'default' => 'customer'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['person_id', 'role']);
        $this->forge->createTable('person_roles', true);

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'unsigned' => true],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 200],
            'doc_type'      => ['type' => 'ENUM', 'constraint' => ['contract', 'invoice', 'insurance', 'tax', 'payroll', 'minutes', 'blueprint', 'other'], 'default' => 'other'],
            'file_path'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'mime'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'size'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'entity_type'   => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'entity_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'version'       => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'is_locked'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'approved_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at'   => ['type' => 'DATETIME', 'null' => true],
            'uploaded_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'entity_type', 'entity_id']);
        $this->forge->createTable('documents', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'unsigned' => true],
            'project_id'  => ['type' => 'INT', 'unsigned' => true],
            'task_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'work_date'   => ['type' => 'DATE'],
            'start_time'  => ['type' => 'TIME', 'null' => true],
            'end_time'    => ['type' => 'TIME', 'null' => true],
            'hours'       => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'hourly_rate' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'labor_cost'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'      => ['type' => 'ENUM', 'constraint' => ['draft', 'approved'], 'default' => 'approved'],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'employee_id', 'work_date']);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('timesheets', true);

        if (! $this->db->fieldExists('person_id', 'payroll_employees')) {
            $this->forge->addColumn('payroll_employees', [
                'person_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'tenant_id'],
            ]);
        }

        if (! $this->db->fieldExists('person_id', 'fin_contacts')) {
            $this->forge->addColumn('fin_contacts', [
                'person_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'tenant_id'],
            ]);
        }

        if (! $this->db->fieldExists('contact_id', 'projects')) {
            $this->forge->addColumn('projects', [
                'contact_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'client_name'],
                'manager_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'contact_id'],
                'actual_cost'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'budget'],
                'labor_cost'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'actual_cost'],
            ]);
        }

        if (! $this->db->fieldExists('source_type', 'fin_transactions')) {
            $this->forge->addColumn('fin_transactions', [
                'source_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true, 'after' => 'invoice_id'],
                'source_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'source_type'],
                'employee_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'source_id'],
            ]);
        }

        if (! $this->db->fieldExists('direction', 'fin_invoices')) {
            $this->forge->addColumn('fin_invoices', [
                'direction' => ['type' => 'ENUM', 'constraint' => ['sale', 'purchase'], 'default' => 'sale', 'after' => 'number'],
            ]);
        }

        if (! $this->db->fieldExists('finance_txn_id', 'payroll_runs')) {
            $this->forge->addColumn('payroll_runs', [
                'finance_txn_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'paid_at'],
                'insurance_txn_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'finance_txn_id'],
                'tax_txn_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'insurance_txn_id'],
            ]);
        }

        if (! $this->db->fieldExists('finance_txn_id', 'tax_periods')) {
            $this->forge->addColumn('tax_periods', [
                'finance_txn_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'due_date'],
            ]);
        }

        if (! $this->db->fieldExists('finance_txn_id', 'insurance_policies')) {
            $this->forge->addColumn('insurance_policies', [
                'finance_txn_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'status'],
            ]);
        }

        if ($this->db->fieldExists('status', 'project_tasks')) {
            $this->db->query("UPDATE project_tasks SET status = 'doing' WHERE status = 'in_progress'");
            $this->db->query("ALTER TABLE project_tasks MODIFY status ENUM('backlog','todo','doing','review','done','cancelled') NOT NULL DEFAULT 'todo'");
        }

        if (! $this->db->fieldExists('estimated_hours', 'project_tasks')) {
            $this->forge->addColumn('project_tasks', [
                'estimated_hours' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0, 'after' => 'due_date'],
                'actual_hours'    => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0, 'after' => 'estimated_hours'],
                'estimated_cost'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'actual_hours'],
                'actual_cost'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'estimated_cost'],
            ]);
        }

        $this->backfillPersons();
    }

    protected function backfillPersons(): void
    {
        if (! $this->db->tableExists('persons')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($this->db->table('payroll_employees')->where('person_id', null)->get()->getResultArray() as $emp) {
            $this->db->table('persons')->insert([
                'tenant_id'   => $emp['tenant_id'],
                'name'        => $emp['name'],
                'national_id' => $emp['national_id'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $personId = (int) $this->db->insertID();
            $this->db->table('payroll_employees')->where('id', $emp['id'])->update(['person_id' => $personId]);
            $this->db->table('person_roles')->insert(['person_id' => $personId, 'role' => 'employee', 'created_at' => $now]);
        }

        $roleMap = [
            'customer'   => 'customer',
            'supplier'   => 'supplier',
            'contractor' => 'contractor',
            'employee'   => 'employee',
            'both'       => 'customer',
        ];

        foreach ($this->db->table('fin_contacts')->where('person_id', null)->get()->getResultArray() as $contact) {
            $personId = $this->db->table('persons')->insert([
                'tenant_id'   => $contact['tenant_id'],
                'name'        => $contact['name'],
                'phone'       => $contact['phone'],
                'email'       => $contact['email'],
                'address'     => $contact['address'],
                'note'        => $contact['note'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $personId = (int) $this->db->insertID();
            $this->db->table('fin_contacts')->where('id', $contact['id'])->update(['person_id' => $personId]);
            $role = $roleMap[$contact['type']] ?? 'customer';
            $this->db->table('person_roles')->insert(['person_id' => $personId, 'role' => $role, 'created_at' => $now]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('timesheets', true);
        $this->forge->dropTable('documents', true);
        $this->forge->dropTable('person_roles', true);
        $this->forge->dropTable('persons', true);
    }
}
