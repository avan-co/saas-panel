<?php

namespace App\Filters;

use App\Libraries\Installer;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class InstallFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $uri           = service('uri');
        $firstSegment  = $uri->getSegment(1) ?? '';
        $isInstallPath = $firstSegment === 'install';
        $isLocalePath  = $firstSegment === 'locale';

        if (Installer::isInstalled()) {
            if ($isInstallPath) {
                return;
            }

            return;
        }

        if (! $isInstallPath && ! $isLocalePath) {
            return redirect()->to('/install');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
