<?php

namespace App\Libraries;

use App\Models\ModuleModel;
use App\Models\TenantMembershipModel;
use App\Models\TenantModel;

class TenantContext
{
    protected ?array $tenant = null;
    protected array $modules = [];

    public function __construct(
        protected TenantModel $tenantModel,
        protected TenantMembershipModel $membershipModel,
        protected ModuleModel $moduleModel,
    ) {}

    public function load(int $tenantId, int $userId): bool
    {
        if (! $this->membershipModel->userBelongsToTenant($userId, $tenantId)) {
            return false;
        }

        $tenant = $this->tenantModel->find($tenantId);

        if ($tenant === null || $tenant['status'] === 'suspended') {
            return false;
        }

        $this->tenant  = $tenant;
        $this->modules = $this->moduleModel->getForTenant($tenantId);

        session()->set('current_tenant_id', $tenantId);

        return true;
    }

    public function loadFromSession(int $userId): bool
    {
        $tenantId = session('current_tenant_id');

        if ($tenantId === null) {
            $tenants = $this->tenantModel->getForUser($userId);

            if ($tenants === []) {
                return false;
            }

            return $this->load((int) $tenants[0]['id'], $userId);
        }

        return $this->load((int) $tenantId, $userId);
    }

    public function getTenant(): ?array
    {
        return $this->tenant;
    }

    public function setTenant(array $tenant): void
    {
        $this->tenant  = $tenant;
        $this->modules = $this->moduleModel->getForTenant((int) $tenant['id']);
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function hasModule(string $code): bool
    {
        foreach ($this->modules as $module) {
            if ($module['code'] === $code) {
                return true;
            }
        }

        return false;
    }

    public function clear(): void
    {
        $this->tenant  = null;
        $this->modules = [];
        session()->remove('current_tenant_id');
    }
}
