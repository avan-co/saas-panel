<?php

namespace App\Controllers;

class ModulePage extends BaseController
{
    public function show(string $code)
    {
        $tenantContext = service('tenantContext');

        if ($code !== 'settings' && ! $tenantContext->hasModule($code)) {
            return redirect()->to('/dashboard')->with('error', lang('App.coming_soon'));
        }

        return $this->render('modules/placeholder', [
            'title'      => $code === 'settings' ? lang('App.menu.settings') : lang('App.modules.' . $code),
            'moduleCode' => $code,
        ]);
    }
}
