<?php

namespace App\Libraries;

class ModianService
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('MODIAN_API_URL', 'https://api.tax.gov.ir/modian');
    }

    public function isConfigured(): bool
    {
        return env('MODIAN_CLIENT_ID') !== null && env('MODIAN_PRIVATE_KEY') !== null;
    }

    /**
     * @return array{success:bool,uuid?:string,message:string}
     */
    public function submitInvoice(array $invoice, array $lines, array $tenant): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Modian credentials not configured. Set MODIAN_CLIENT_ID and MODIAN_PRIVATE_KEY in .env',
            ];
        }

        $uuid = bin2hex(random_bytes(16));

        // Production: sign payload and POST to tax authority API
        return [
            'success' => true,
            'uuid'    => $uuid,
            'message' => 'Invoice queued for Modian submission (stub — configure live API)',
        ];
    }
}
