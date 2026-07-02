<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonRoleModel extends Model
{
    protected $table         = 'person_roles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['person_id', 'role'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForPerson(int $personId): array
    {
        return $this->where('person_id', $personId)->findAll();
    }

    public function rolesForPerson(int $personId): array
    {
        return array_column($this->getForPerson($personId), 'role');
    }

    public function ensureRole(int $personId, string $role): void
    {
        if ($this->where('person_id', $personId)->where('role', $role)->countAllResults() > 0) {
            return;
        }

        $this->insert(['person_id' => $personId, 'role' => $role]);
    }
}
