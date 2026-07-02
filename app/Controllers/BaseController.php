<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function render(string $view, array $data = []): string
    {
        $locale = session('locale') ?? 'fa';
        $theme  = session('theme') ?? 'system';

        $shared = [
            'locale'            => $locale,
            'theme'             => $theme,
            'isRtl'             => $locale === 'fa',
            'userName'          => session('user_name'),
            'isPlatformAdmin'   => (bool) session('is_platform_admin'),
        ];

        if (session('user_id')) {
            $tenantContext = service('tenantContext');
            $tenantContext->loadFromSession((int) session('user_id'));

            $shared['currentTenant'] = $tenantContext->getTenant();
            $shared['tenantModules'] = $tenantContext->getModules();

            if ($shared['currentTenant'] !== null) {
                $tenantModel = model('TenantModel');
                $shared['userTenants'] = $tenantModel->getForUser((int) session('user_id'));
            } else {
                $shared['userTenants'] = [];
            }
        }

        return view($view, array_merge($shared, $data));
    }
}
