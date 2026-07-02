<?php

namespace App\Controllers;

use App\Libraries\Installer;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

class Install extends Controller
{
    protected $helpers = ['url', 'form'];

    public function index()
    {
        return $this->render('requirements', [
            'title'        => lang('Install.step_requirements'),
            'step'         => 1,
            'requirements' => Installer::getRequirements(),
            'canContinue'  => Installer::allRequirementsPassed(),
        ]);
    }

    public function database()
    {
        if (! Installer::allRequirementsPassed()) {
            return redirect()->to('/install');
        }

        $saved = session('install_db') ?? [];

        return $this->render('database', [
            'title' => lang('Install.step_database'),
            'step'  => 2,
            'db'    => [
                'hostname' => $saved['hostname'] ?? 'localhost',
                'database' => $saved['database'] ?? '',
                'username' => $saved['username'] ?? '',
                'password' => $saved['password'] ?? '',
                'port'     => $saved['port'] ?? '3306',
            ],
        ]);
    }

    public function saveDatabase(): RedirectResponse
    {
        if (! Installer::allRequirementsPassed()) {
            return redirect()->to('/install');
        }

        $rules = [
            'hostname' => 'required|max_length[120]',
            'database' => 'required|max_length[120]',
            'username' => 'required|max_length[120]',
            'password' => 'permit_empty|max_length[255]',
            'port'     => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $config = [
            'hostname' => (string) $this->request->getPost('hostname'),
            'database' => (string) $this->request->getPost('database'),
            'username' => (string) $this->request->getPost('username'),
            'password' => (string) $this->request->getPost('password'),
            'port'     => (string) $this->request->getPost('port'),
        ];

        $error = Installer::testDatabase($config);

        if ($error !== null) {
            return redirect()->back()->withInput()->with('error', lang('Install.db_connection_failed') . ' ' . $error);
        }

        session()->set('install_db', $config);

        return redirect()->to('/install/setup');
    }

    public function setup()
    {
        if (session('install_db') === null) {
            return redirect()->to('/install/database');
        }

        return $this->render('setup', [
            'title'   => lang('Install.step_setup'),
            'step'    => 3,
            'baseURL' => Installer::detectBaseUrl(),
        ]);
    }

    public function runSetup(): RedirectResponse
    {
        $dbConfig = session('install_db');

        if ($dbConfig === null) {
            return redirect()->to('/install/database');
        }

        $rules = [
            'baseURL'         => 'required|valid_url|max_length[255]',
            'admin_name'      => 'required|min_length[2]|max_length[120]',
            'admin_email'     => 'required|valid_email|max_length[191]',
            'admin_password'  => 'required|min_length[8]|max_length[255]',
            'admin_password_confirm' => 'required|matches[admin_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $baseURL = rtrim((string) $this->request->getPost('baseURL'), '/') . '/';
        $baseURL = Installer::normalizeBaseUrl($baseURL);

        session()->set('install_app', [
            'baseURL'    => $baseURL,
            'admin_name' => (string) $this->request->getPost('admin_name'),
            'admin_email'=> (string) $this->request->getPost('admin_email'),
            'admin_password' => (string) $this->request->getPost('admin_password'),
            'seed_demo'  => (bool) $this->request->getPost('seed_demo'),
            'encryption_key' => Installer::generateEncryptionKey(),
        ]);

        $appConfig = session('install_app');
        $written   = Installer::writeEnvFile(array_merge($dbConfig, [
            'baseURL'        => $appConfig['baseURL'],
            'encryption_key' => $appConfig['encryption_key'],
        ]));

        if (! $written) {
            return redirect()->back()->withInput()->with('error', lang('Install.env_write_failed'));
        }

        return redirect()->to('/install/process');
    }

    public function process()
    {
        if (session('install_db') === null || session('install_app') === null) {
            return redirect()->to('/install');
        }

        return $this->render('process', [
            'title' => lang('Install.step_process'),
            'step'  => 4,
        ]);
    }

    public function execute(): RedirectResponse
    {
        if (session('install_db') === null || session('install_app') === null) {
            return redirect()->to('/install');
        }

        $appConfig = session('install_app');

        $migrationError = Installer::runMigrations();

        if ($migrationError !== null) {
            return redirect()->to('/install/setup')->with('error', lang('Install.migration_failed') . ' ' . $migrationError);
        }

        try {
            Installer::seedModules();
            $adminId = Installer::createAdmin([
                'name'     => $appConfig['admin_name'],
                'email'    => $appConfig['admin_email'],
                'password' => $appConfig['admin_password'],
            ]);

            if (! empty($appConfig['seed_demo'])) {
                Installer::seedDemoData($adminId);
            }
        } catch (\Throwable $e) {
            return redirect()->to('/install/setup')->with('error', lang('Install.seed_failed') . ' ' . $e->getMessage());
        }

        Installer::markInstalled();
        session()->remove('install_db');
        session()->remove('install_app');

        return redirect()->to('/login')->with('success', lang('Install.complete_help'));
    }

    protected function render(string $bodyView, array $data = []): string
    {
        $viewOptions = ['debug' => false];

        $data['body'] = view('install/bodies/' . $bodyView, $data, $viewOptions);

        return view('install/shell', $data, $viewOptions);
    }
}
