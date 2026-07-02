<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessModulesTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'national_id' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'job_title'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'base_salary' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'hired_at'    => ['type' => 'DATE', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('payroll_employees');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
            'period_year'  => ['type' => 'SMALLINT', 'unsigned' => true],
            'period_month' => ['type' => 'TINYINT', 'unsigned' => true],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'employee_count' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'status'       => ['type' => 'ENUM', 'constraint' => ['draft', 'paid'], 'default' => 'draft'],
            'paid_at'      => ['type' => 'DATE', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'period_year', 'period_month']);
        $this->forge->createTable('payroll_runs');

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'unsigned' => true],
            'policy_number' => ['type' => 'VARCHAR', 'constraint' => 80],
            'provider'      => ['type' => 'VARCHAR', 'constraint' => 120],
            'type'          => ['type' => 'ENUM', 'constraint' => ['social', 'health', 'liability', 'other'], 'default' => 'social'],
            'premium'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'start_date'    => ['type' => 'DATE'],
            'end_date'      => ['type' => 'DATE', 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['active', 'expired', 'pending'], 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('insurance_policies');

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'unsigned' => true],
            'period_year'    => ['type' => 'SMALLINT', 'unsigned' => true],
            'period_quarter' => ['type' => 'TINYINT', 'unsigned' => true],
            'taxable_income' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'tax_amount'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'filed', 'paid'], 'default' => 'pending'],
            'due_date'       => ['type' => 'DATE', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'period_year', 'period_quarter']);
        $this->forge->createTable('tax_periods');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 160],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'client_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['planning', 'active', 'on_hold', 'completed'], 'default' => 'planning'],
            'budget'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'progress'    => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'start_date'  => ['type' => 'DATE', 'null' => true],
            'end_date'    => ['type' => 'DATE', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('projects');
    }

    public function down(): void
    {
        $this->forge->dropTable('projects', true);
        $this->forge->dropTable('tax_periods', true);
        $this->forge->dropTable('insurance_policies', true);
        $this->forge->dropTable('payroll_runs', true);
        $this->forge->dropTable('payroll_employees', true);
    }
}
