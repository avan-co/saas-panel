<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table         = 'api_keys';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'user_id', 'name', 'key_hash', 'key_prefix', 'scopes', 'last_used'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function findByPrefixAndHash(string $prefix, string $hash): ?array
    {
        return $this->where('key_prefix', $prefix)->where('key_hash', $hash)->first();
    }
}
