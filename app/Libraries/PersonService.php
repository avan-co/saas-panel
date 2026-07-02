<?php

namespace App\Libraries;

use App\Models\FinContactModel;
use App\Models\PayrollEmployeeModel;
use App\Models\PersonModel;
use App\Models\PersonRoleModel;

class PersonService
{
    protected PersonModel $persons;
    protected PersonRoleModel $roles;

    public function __construct()
    {
        $this->persons = model(PersonModel::class);
        $this->roles   = model(PersonRoleModel::class);
    }

    public function syncFromEmployee(int $tenantId, array $employee): int
    {
        $personId = (int) ($employee['person_id'] ?? 0);

        if ($personId > 0) {
            $this->persons->update($personId, [
                'name'        => $employee['name'],
                'national_id' => $employee['national_id'] ?? null,
            ]);
            $this->roles->ensureRole($personId, 'employee');

            return $personId;
        }

        $existing = null;

        if (! empty($employee['national_id'])) {
            $existing = $this->persons->findByNationalId($tenantId, (string) $employee['national_id']);
        }

        if ($existing !== null) {
            $personId = (int) $existing['id'];
            model(PayrollEmployeeModel::class)->update((int) $employee['id'], ['person_id' => $personId]);
            $this->roles->ensureRole($personId, 'employee');

            return $personId;
        }

        $personId = (int) $this->persons->insert([
            'tenant_id'   => $tenantId,
            'name'        => $employee['name'],
            'national_id' => $employee['national_id'] ?? null,
        ]);

        model(PayrollEmployeeModel::class)->update((int) $employee['id'], ['person_id' => $personId]);
        $this->roles->ensureRole($personId, 'employee');

        return $personId;
    }

    public function syncFromContact(int $tenantId, array $contact): int
    {
        $personId = (int) ($contact['person_id'] ?? 0);
        $role     = $this->contactTypeToRole((string) ($contact['type'] ?? 'both'));

        if ($personId > 0) {
            $this->persons->update($personId, [
                'name'    => $contact['name'],
                'phone'   => $contact['phone'] ?? null,
                'email'   => $contact['email'] ?? null,
                'address' => $contact['address'] ?? null,
                'note'    => $contact['note'] ?? null,
            ]);
            $this->roles->ensureRole($personId, $role);

            return $personId;
        }

        $personId = (int) $this->persons->insert([
            'tenant_id' => $tenantId,
            'name'      => $contact['name'],
            'phone'     => $contact['phone'] ?? null,
            'email'     => $contact['email'] ?? null,
            'address'   => $contact['address'] ?? null,
            'note'      => $contact['note'] ?? null,
        ]);

        model(FinContactModel::class)->update((int) $contact['id'], ['person_id' => $personId]);
        $this->roles->ensureRole($personId, $role);

        return $personId;
    }

    public function getWithRoles(int $tenantId): array
    {
        $people = $this->persons->getForTenant($tenantId);

        foreach ($people as &$person) {
            $person['roles'] = $this->roles->rolesForPerson((int) $person['id']);
        }

        return $people;
    }

    protected function contactTypeToRole(string $type): string
    {
        return match ($type) {
            'supplier'   => 'supplier',
            'contractor' => 'contractor',
            'employee'   => 'employee',
            'customer'   => 'customer',
            default      => 'customer',
        };
    }
}
