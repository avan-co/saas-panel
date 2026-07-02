<?php

namespace App\Models;

use CodeIgniter\Model;

class WebhookModel extends Model
{
    protected $table         = 'webhooks';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'url', 'events', 'secret', 'is_active'];
    protected $useTimestamps = true;
}
