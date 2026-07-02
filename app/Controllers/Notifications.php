<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    public function index()
    {
        $userId = (int) session('user_id');
        $tenant = service('tenantContext')->getTenant();

        return $this->render('notifications/index', [
            'title'         => lang('Notifications.title'),
            'notifications' => model(NotificationModel::class)->unreadForUser($userId, $tenant['id'] ?? null),
        ]);
    }

    public function markRead(int $id)
    {
        model(NotificationModel::class)->markRead($id, (int) session('user_id'));

        return redirect()->back();
    }

    public function dropdown()
    {
        $userId = (int) session('user_id');
        $tenant = service('tenantContext')->getTenant();
        $items  = model(NotificationModel::class)->unreadForUser($userId, $tenant['id'] ?? null);

        return $this->response->setJSON([
            'count' => model(NotificationModel::class)->unreadCount($userId),
            'items' => $items,
        ]);
    }
}
