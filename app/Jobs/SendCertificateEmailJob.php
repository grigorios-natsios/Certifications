<?php

namespace App\Jobs;

use App\Mail\CertificateReadyMail;
use App\Models\BulkEmailResult;
use App\Models\Client;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCertificateEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $clientId,
        public string $reportRecipient,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $client = Client::with('certificateCategories')->find($this->clientId);
        if (! $client || ! $client->email) {
            return;
        }

        Mail::to($client->email)->send(new CertificateReadyMail($client));

        BulkEmailResult::create([
            'batch_id'         => $this->batch()?->id ?? 'no-batch',
            'client_id'        => $client->id,
            'name'             => trim(($client->lastname ?? '').' '.($client->name ?? '')),
            'email'            => $client->email,
            'status'           => 'sent',
            'report_recipient' => $this->reportRecipient,
        ]);
    }

    public function failed(Throwable $e): void
    {
        $client = Client::find($this->clientId);

        BulkEmailResult::create([
            'batch_id'         => $this->batch()?->id ?? 'no-batch',
            'client_id'        => $this->clientId,
            'name'             => $client ? trim(($client->lastname ?? '').' '.($client->name ?? '')) : null,
            'email'            => $client?->email ?? '(unknown)',
            'status'           => 'failed',
            'error'            => mb_substr($e->getMessage(), 0, 1000),
            'report_recipient' => $this->reportRecipient,
        ]);
    }
}
