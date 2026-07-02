<?php

namespace App\Libraries;

use App\Models\TenantMembershipModel;

class PermissionService
{
    protected ?array $membership = null;

    protected array $roleDefaults = [
        'owner'      => ['*'],
        'admin'      => ['*'],
        'accountant' => ['finance.*', 'projects.view', 'settings.view'],
        'manager'    => ['finance.view', 'finance.transactions', 'projects.*'],
        'hr'         => ['payroll.*', 'insurance.*', 'settings.view'],
        'viewer'     => ['*.view'],
    ];

    public function load(int $userId, int $tenantId): void
    {
        if (session('is_platform_admin')) {
            $this->membership = ['role' => 'owner', 'permissions' => null];

            return;
        }

        $this->membership = model(TenantMembershipModel::class)
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function can(string $permission): bool
    {
        if (session('is_platform_admin')) {
            return true;
        }

        if ($this->membership === null) {
            return false;
        }

        $role = $this->membership['role'] ?? 'viewer';
        $custom = $this->decodePermissions($this->membership['permissions'] ?? null);

        $grants = $custom !== [] ? $custom : ($this->roleDefaults[$role] ?? ['*.view']);

        foreach ($grants as $grant) {
            if ($this->matchGrant($grant, $permission)) {
                return true;
            }
        }

        return false;
    }

    protected function decodePermissions(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    protected function matchGrant(string $grant, string $permission): bool
    {
        if ($grant === '*') {
            return true;
        }

        if ($grant === $permission) {
            return true;
        }

        if (str_ends_with($grant, '.*')) {
            $prefix = substr($grant, 0, -2);

            return str_starts_with($permission, $prefix . '.') || $permission === $prefix;
        }

        if (str_starts_with($grant, '*.') && str_contains($permission, '.')) {
            $suffix = substr($grant, 1);

            return str_ends_with($permission, $suffix);
        }

        return false;
    }

    public function membership(): ?array
    {
        return $this->membership;
    }
}
