<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'locale' => session('locale') ?? 'fa',
            'theme'  => session('theme') ?? 'system',
            'isRtl'  => (session('locale') ?? 'fa') === 'fa',
        ]);
    }

    public function attempt()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $userModel = model(UserModel::class);
        $user      = $userModel->findByEmail($email);

        if ($user === null || ! $userModel->verifyPassword($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', lang('Auth.invalid_credentials'));
        }

        if ($user['status'] === 'suspended') {
            return redirect()->back()->withInput()->with('error', lang('Auth.account_suspended'));
        }

        session()->set([
            'user_id'           => $user['id'],
            'user_name'         => $user['name'],
            'user_email'        => $user['email'],
            'is_platform_admin' => (bool) $user['is_platform_admin'],
            'locale'            => $user['locale'],
            'theme'             => $user['theme'],
        ]);

        service('tenantContext')->loadFromSession((int) $user['id']);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        service('tenantContext')->clear();
        session()->destroy();

        return redirect()->to('/login');
    }
}
