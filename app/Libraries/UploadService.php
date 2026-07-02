<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class UploadService
{
    protected array $allowedMimes = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    public function storeForTenant(int $tenantId, string $subdir, ?UploadedFile $file): ?array
    {
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $ext = strtolower($file->getClientExtension());

        if (! isset($this->allowedMimes[$ext])) {
            return null;
        }

        $dir = WRITEPATH . 'uploads/' . $tenantId . '/' . trim($subdir, '/');

        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return null;
        }

        $stored = $file->getRandomName();
        $file->move($dir, $stored);

        return [
            'file_path'     => $tenantId . '/' . trim($subdir, '/') . '/' . $stored,
            'original_name' => $file->getClientName(),
            'mime'          => $file->getClientMimeType(),
            'size'          => $file->getSize(),
        ];
    }

    public function fullPath(string $relativePath): string
    {
        return WRITEPATH . 'uploads/' . ltrim($relativePath, '/');
    }
}
