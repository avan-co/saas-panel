<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LocaleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $locale = session('locale');

        if ($locale === null && session('user_id')) {
            $userModel = model('UserModel');
            $user      = $userModel->find(session('user_id'));

            if ($user !== null) {
                $locale = $user['locale'];
            }
        }

        if ($locale === null) {
            $locale = 'fa';
        }

        if (! in_array($locale, ['fa', 'en'], true)) {
            $locale = 'fa';
        }

        session()->set('locale', $locale);
        service('request')->setLocale($locale);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
