<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public Client $client;
    public string $publicUrl;
    public array $downloads;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->publicUrl = route('certificate.show', ['slug' => $client->url_slug]);

        $this->downloads = $client->certificateCategories
            ->filter(fn ($c) => $c->html_template)
            ->map(fn ($c) => [
                'name' => $c->name,
                'url'  => route('certificate.download', [
                    'slug'     => $client->url_slug,
                    'category' => $c->slug,
                ]),
            ])
            ->values()
            ->all();
    }

    public function build()
    {
        $name = trim(($this->client->lastname ?? '').' '.($this->client->name ?? ''));
        $subject = $name !== ''
            ? "Το πιστοποιητικό σας — {$name}"
            : 'Το πιστοποιητικό σας';

        return $this->subject($subject)
            ->view('emails.certificate-ready');
    }
}
