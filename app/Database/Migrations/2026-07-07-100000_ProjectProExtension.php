<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProjectProExtension extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('priority', 'projects')) {
            $this->forge->addColumn('projects', [
                'priority'      => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high', 'critical'], 'default' => 'medium', 'after' => 'status'],
                'description'   => ['type' => 'TEXT', 'null' => true, 'after' => 'priority'],
                'health_status' => ['type' => 'ENUM', 'constraint' => ['green', 'yellow', 'red'], 'default' => 'green', 'after' => 'progress'],
            ]);
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'project_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'role'       => ['type' => 'ENUM', 'constraint' => ['manager', 'expert', 'intern', 'client', 'viewer'], 'default' => 'expert'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['project_id', 'user_id']);
        $this->forge->createTable('project_members', true);

        if ($this->db->fieldExists('status', 'project_tasks')) {
            $this->db->query("ALTER TABLE project_tasks MODIFY status ENUM('backlog','todo','doing','review','testing','done','cancelled') NOT NULL DEFAULT 'todo'");
        }

        if (! $this->db->fieldExists('start_date', 'project_tasks')) {
            $this->forge->addColumn('project_tasks', [
                'start_date'         => ['type' => 'DATE', 'null' => true, 'after' => 'due_date'],
                'labels'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'start_date'],
                'depends_on_task_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'labels'],
            ]);
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'task_id'    => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'is_done'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sort_order' => ['type' => 'SMALLINT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('task_id');
        $this->forge->createTable('project_task_checklist', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'task_id'    => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'parent_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'body'       => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('task_id');
        $this->forge->createTable('project_task_comments', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'project_id'  => ['type' => 'INT', 'unsigned' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'due_date'    => ['type' => 'DATE', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['pending', 'done'], 'default' => 'pending'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_milestones', true);

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'unsigned' => true],
            'project_id'     => ['type' => 'INT', 'unsigned' => true],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'probability'    => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high'], 'default' => 'medium'],
            'impact'         => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high'], 'default' => 'medium'],
            'mitigation'     => ['type' => 'TEXT', 'null' => true],
            'owner_user_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['open', 'mitigated', 'closed'], 'default' => 'open'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_risks', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'unsigned' => true],
            'project_id'  => ['type' => 'INT', 'unsigned' => true],
            'type'        => ['type' => 'ENUM', 'constraint' => ['technical', 'financial', 'supply', 'client', 'internal'], 'default' => 'internal'],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['open', 'in_progress', 'resolved'], 'default' => 'open'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_issues', true);

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
            'project_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'trigger_type' => ['type' => 'ENUM', 'constraint' => ['task_done', 'deadline_passed'], 'default' => 'task_done'],
            'action_type'  => ['type' => 'ENUM', 'constraint' => ['create_task', 'notify'], 'default' => 'notify'],
            'config'       => ['type' => 'TEXT', 'null' => true],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_automation_rules', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'project_id' => ['type' => 'INT', 'unsigned' => true],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'content'    => ['type' => 'MEDIUMTEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_wiki_pages', true);

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'unsigned' => true],
            'project_id'    => ['type' => 'INT', 'unsigned' => true],
            'decision'      => ['type' => 'TEXT'],
            'owner_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'due_date'      => ['type' => 'DATE', 'null' => true],
            'task_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'project_id']);
        $this->forge->createTable('project_decisions', true);

        if ($this->db->tableExists('documents') && ! $this->db->fieldExists('approval_status', 'documents')) {
            $this->forge->addColumn('documents', [
                'approval_status' => ['type' => 'ENUM', 'constraint' => ['draft', 'approved', 'rejected'], 'default' => 'draft', 'after' => 'is_locked'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('project_decisions', true);
        $this->forge->dropTable('project_wiki_pages', true);
        $this->forge->dropTable('project_automation_rules', true);
        $this->forge->dropTable('project_issues', true);
        $this->forge->dropTable('project_risks', true);
        $this->forge->dropTable('project_milestones', true);
        $this->forge->dropTable('project_task_comments', true);
        $this->forge->dropTable('project_task_checklist', true);
        $this->forge->dropTable('project_members', true);
    }
}
