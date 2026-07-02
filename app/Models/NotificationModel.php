<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'user_id', 'scope', 'type', 'title', 'body', 'link', 'read_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function unreadForUser(int $userId, ?int $tenantId = null): array
    {
        $builder = $this->where('user_id', $userId)->where('read_at', null);

        if ($tenantId !== null) {
            $builder->groupStart()
                ->where('tenant_id', $tenantId)
                ->orWhere('tenant_id', null)
                ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')->limit(15)->findAll();
    }

    public function unreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)->where('read_at', null)->countAllResults();
    }

    public function markRead(int $id, int $userId): void
    {
        $this->where('id', $id)->where('user_id', $userId)->set('read_at', date('Y-m-d H:i:s'))->update();
    }

    public function notifyUser(int $userId, string $title, ?string $body = null, ?string $link = null, ?int $tenantId = null, string $type = 'info'): void
    {
        $this->insert([
            'user_id'   => $userId,
            'tenant_id' => $tenantId,
            'scope'     => $tenantId ? 'tenant' : 'platform',
            'type'      => $type,
            'title'     => $title,
            'body'      => $body,
            'link'      => $link,
        ]);
    }
}
