<?php

namespace App\Controllers\Concerns;

trait HasProjectNav
{
    protected function projectNavItems(int $projectId): array
    {
        return [
            ['key' => 'dashboard', 'route' => 'module/projects/' . $projectId,              'label' => 'Projects.dashboard'],
            ['key' => 'tasks',     'route' => 'module/projects/' . $projectId . '/tasks',  'label' => 'Projects.tasks'],
            ['key' => 'gantt',     'route' => 'module/projects/' . $projectId . '/gantt',  'label' => 'Projects.gantt'],
            ['key' => 'calendar',  'route' => 'module/projects/' . $projectId . '/calendar', 'label' => 'Projects.calendar'],
            ['key' => 'timesheets','route' => 'module/projects/' . $projectId . '/timesheets', 'label' => 'Projects.timesheets'],
            ['key' => 'files',     'route' => 'module/projects/' . $projectId . '/files',  'label' => 'Projects.files'],
            ['key' => 'risks',     'route' => 'module/projects/' . $projectId . '/risks',  'label' => 'Projects.risks'],
            ['key' => 'reports',   'route' => 'module/projects/' . $projectId . '/reports','label' => 'Projects.reports'],
            ['key' => 'wiki',      'route' => 'module/projects/' . $projectId . '/wiki',   'label' => 'Projects.wiki'],
        ];
    }

    protected function projectBreadcrumbs(array $project, ?string $pageLabel = null): array
    {
        $crumbs = [
            ['label' => lang('Projects.title'), 'url' => site_url('module/projects')],
            ['label' => $project['name'], 'url' => site_url('module/projects/' . $project['id'])],
        ];

        if ($pageLabel !== null) {
            $crumbs[] = ['label' => $pageLabel];
        }

        return $crumbs;
    }
}
