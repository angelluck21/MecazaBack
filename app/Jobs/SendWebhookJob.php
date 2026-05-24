<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;

    public function __construct(
        private string $url,
        private array $payload
    ) {}

    public function handle(): void
    {
        Http::timeout(10)->post($this->url, $this->payload);
    }

    public function backoff(): array
    {
        return [30, 120, 300]; // segundos entre reintentos: 30s, 2min, 5min
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Webhook N8N falló después de todos los reintentos', [
            'url'   => $this->url,
            'error' => $e->getMessage(),
        ]);
    }
}
