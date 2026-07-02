<?php

namespace App\Libraries;

class WebhookDispatcher
{
    public function dispatch(int $tenantId, string $event, array $payload): void
    {
        $hooks = model(\App\Models\WebhookModel::class)
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->findAll();

        foreach ($hooks as $hook) {
            $events = json_decode($hook['events'] ?? '[]', true) ?: [];

            if ($events !== [] && ! in_array($event, $events, true) && ! in_array('*', $events, true)) {
                continue;
            }

            $body = json_encode(['event' => $event, 'payload' => $payload, 'timestamp' => time()]);

            $ch = curl_init($hook['url']);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'X-Webhook-Secret: ' . $hook['secret'],
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}
