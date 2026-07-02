<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Controllers\Concerns\HasPlatformNav;

class System extends BaseController
{
    use HasPlatformNav;

    public function index()
    {
        return $this->render('platform/system/index', [
            'title'          => lang('Platform.system_settings'),
            'moduleNav'      => 'system',
            'moduleNavItems' => $this->platformNavItems(),
            'modianReady'    => service('modian')->isConfigured(),
            'appUrl'         => base_url(),
            'breadcrumbs'    => [
                ['label' => lang('Platform.title'), 'url' => site_url('platform/tenants')],
                ['label' => lang('Platform.system_settings')],
            ],
        ]);
    }
}
