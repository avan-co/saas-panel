<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasSettingsNav;
use App\Models\ApiKeyModel;
use App\Models\WebhookModel;

class SettingsApi extends BaseController
{
    use HasSettingsNav;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $tenant === null ? redirect()->to('/dashboard') : $this->settingsDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('settings/api', [
            'title'          => lang('Settings.api_access'),
            'moduleNav'      => 'api',
            'moduleNavItems' => $this->settingsNavItems(),
            'apiKeys'        => model(ApiKeyModel::class)->where('tenant_id', $tenantId)->findAll(),
            'webhooks'       => model(WebhookModel::class)->where('tenant_id', $tenantId)->findAll(),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.api_access')],
            ],
        ]);
    }

    public function storeApiKey()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $name = (string) $this->request->getPost('name');

        if ($name === '') {
            return redirect()->back()->with('error', lang('Settings.api_key_name_required'));
        }

        $raw    = bin2hex(random_bytes(24));
        $prefix = 'bp_' . substr($raw, 0, 8);
        $secret = $prefix . '.' . substr($raw, 8);

        model(ApiKeyModel::class)->insert([
            'tenant_id'  => (int) $tenant['id'],
            'user_id'    => (int) session('user_id'),
            'name'       => $name,
            'key_hash'   => hash('sha256', $secret),
            'key_prefix' => $prefix,
            'scopes'     => json_encode(['transactions.read', 'transactions.write']),
        ]);

        return redirect()->to('/module/settings/api')->with('success', lang('Settings.api_key_created'))->with('new_api_key', $secret);
    }

    public function deleteApiKey(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        model(ApiKeyModel::class)->where('id', $id)->where('tenant_id', (int) $tenant['id'])->delete();

        return redirect()->to('/module/settings/api')->with('success', lang('App.deleted'));
    }

    public function storeWebhook()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $url = (string) $this->request->getPost('url');

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return redirect()->back()->with('error', lang('Settings.webhook_url_invalid'));
        }

        $events = array_filter(array_map('trim', explode(',', (string) $this->request->getPost('events'))));

        model(WebhookModel::class)->insert([
            'tenant_id' => (int) $tenant['id'],
            'url'       => $url,
            'events'    => json_encode($events ?: ['*']),
            'secret'    => bin2hex(random_bytes(16)),
            'is_active' => 1,
        ]);

        return redirect()->to('/module/settings/api')->with('success', lang('Settings.webhook_created'));
    }

    public function deleteWebhook(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        model(WebhookModel::class)->where('id', $id)->where('tenant_id', (int) $tenant['id'])->delete();

        return redirect()->to('/module/settings/api')->with('success', lang('App.deleted'));
    }
}
