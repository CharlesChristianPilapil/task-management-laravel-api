<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function queueAssigned(int $taskId, int $userId, array $details = []): void
    {
        $this->send('assigned', $taskId, $userId, $details);
    }

    public function queueStatusChanged(int $taskId, int $userId, array $details = []): void
    {
        $this->send('status_changed', $taskId, $userId, $details);
    }

    private function send(string $eventType, int $taskId, int $userId, array $details): void
    {
        $baseUrl = config('services.node.url');
        $serviceKey = config('services.internal.service_key');

        if (! is_string($baseUrl) || $baseUrl === '' || ! is_string($serviceKey) || $serviceKey === '') {
            Log::warning('Notification skipped due to missing service configuration.', [
                'task_id' => $taskId,
                'user_id' => $userId,
                'event_type' => $eventType,
            ]);

            return;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Service-Key' => $serviceKey,
                    'Accept' => 'application/json',
                ])
                ->post(rtrim($baseUrl, '/').'/api/notifications/send', [
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'event_type' => $eventType,
                    'details' => $details,
                ]);

            if (! $response->successful()) {
                Log::warning('Notification request failed.', [
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'event_type' => $eventType,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Notification request error.', [
                'task_id' => $taskId,
                'user_id' => $userId,
                'event_type' => $eventType,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
