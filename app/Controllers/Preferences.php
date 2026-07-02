<?php

namespace App\Controllers;

use App\Models\UserModel;

class Preferences extends BaseController
{
    public function locale(string $locale)
    {
        if (! in_array($locale, ['fa', 'en'], true)) {
            return redirect()->back();
        }

        session()->set('locale', $locale);

        if (session('user_id')) {
            model(UserModel::class)->update(session('user_id'), ['locale' => $locale]);
        }

        return redirect()->back();
    }

    public function theme(string $theme)
    {
        if (! in_array($theme, ['light', 'dark', 'system'], true)) {
            return redirect()->back();
        }

        session()->set('theme', $theme);

        if (session('user_id')) {
            model(UserModel::class)->update(session('user_id'), ['theme' => $theme]);
        }

        return redirect()->back();
    }
}
