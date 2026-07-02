<?php

namespace App\Controllers;

use App\Models\FinAssetModel;
use App\Models\FinCheckModel;
use App\Models\FinContactModel;
use App\Models\FinInvoiceModel;
use App\Models\FinLoanModel;
use App\Models\FinTransactionModel;
use App\Models\ProjectModel;

class Search extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $tenantId = (int) $tenant['id'];
        $q        = trim((string) $this->request->getGet('q'));
        $results  = [
            'transactions' => [],
            'projects'     => [],
            'contacts'     => [],
            'invoices'     => [],
            'checks'       => [],
            'loans'        => [],
            'assets'       => [],
        ];

        if (strlen($q) >= 2) {
            $tenantContext = service('tenantContext');

            if ($tenantContext->hasModule('finance')) {
                $results['transactions'] = model(FinTransactionModel::class)->search($tenantId, $q);
                $results['contacts']     = model(FinContactModel::class)->search($tenantId, $q);
                $results['invoices']     = model(FinInvoiceModel::class)->search($tenantId, $q);
                $results['checks']       = model(FinCheckModel::class)->search($tenantId, $q);
                $results['loans']        = model(FinLoanModel::class)->search($tenantId, $q);
                $results['assets']       = model(FinAssetModel::class)->search($tenantId, $q);
            }

            if ($tenantContext->hasModule('projects')) {
                $results['projects'] = model(ProjectModel::class)->search($tenantId, $q);
            }
        }

        return $this->render('search/index', [
            'title'   => lang('Search.title'),
            'query'   => $q,
            'results' => $results,
            'breadcrumbs' => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Search.title')],
            ],
        ]);
    }
}
