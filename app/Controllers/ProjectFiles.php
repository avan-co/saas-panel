<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\DocumentModel;
use App\Models\ProjectModel;

class ProjectFiles extends BaseController
{
    use HasTenantModule;
    use HasProjectNav;
    use ChecksPermission;

    public function index(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $project  = model(ProjectModel::class)->findForTenant($projectId, $tenantId);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        return $this->render('projects/files', [
            'title'           => lang('Projects.files') . ' — ' . $project['name'],
            'project'         => $project,
            'documents'       => model(DocumentModel::class)->forEntity($tenantId, 'project', $projectId),
            'canEdit'         => $this->requirePermission('projects.tasks'),
            'projectNav'      => 'files',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.files')),
        ]);
    }

    public function store(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        if (model(ProjectModel::class)->findForTenant($projectId, $tenantId) === null) {
            return redirect()->back()->with('error', lang('Projects.not_found'));
        }

        $file = $this->request->getFile('file');
        $stored = service('upload')->storeForTenant($tenantId, 'projects/' . $projectId, $file);

        if ($stored === null) {
            return redirect()->back()->with('error', lang('Projects.file_upload_failed'));
        }

        service('document')->attach(
            $tenantId,
            'project',
            $projectId,
            $stored,
            (string) ($this->request->getPost('title') ?: $stored['original_name']),
            (string) ($this->request->getPost('doc_type') ?: 'other'),
        );

        return redirect()->to('/module/projects/' . $projectId . '/files')->with('success', lang('Projects.file_uploaded'));
    }

    public function approve(int $projectId, int $docId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $doc = model(DocumentModel::class)->findForTenant($docId, (int) $tenant['id']);

        if ($doc === null || (int) $doc['entity_id'] !== $projectId) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $status = (string) $this->request->getPost('approval_status');

        if (! in_array($status, ['approved', 'rejected', 'draft'], true)) {
            return redirect()->back();
        }

        model(DocumentModel::class)->update($docId, [
            'approval_status' => $status,
            'approved_by'     => (int) session('user_id'),
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/files')->with('success', lang('Projects.file_updated'));
    }

    public function download(int $projectId, int $docId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $doc = model(DocumentModel::class)->findForTenant($docId, (int) $tenant['id']);

        if ($doc === null || empty($doc['file_path'])) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $path = service('upload')->fullPath($doc['file_path']);

        if (! is_file($path)) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        return $this->response->download($path, null)->setFileName($doc['original_name'] ?? 'file');
    }
}
