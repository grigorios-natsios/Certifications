<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const ACTION_PDF_DOWNLOAD = 'pdf_download';
    public const ACTION_EMAIL_BATCH  = 'email_batch';

    public const ACTIONS = [
        self::ACTION_PDF_DOWNLOAD => 'Λήψη PDF',
        self::ACTION_EMAIL_BATCH  => 'Αποστολή email',
    ];

    protected $fillable = [
        'organization_id',
        'action',
        'user_id',
        'client_id',
        'client_name',
        'client_email',
        'subject',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    /**
     * Capture a public/admin action. Safe to call from controllers, jobs, or
     * Livewire actions — silently swallows write errors so logging never breaks
     * the user-visible flow.
     */
    public static function record(string $action, array $attributes): void
    {
        try {
            self::create(array_merge(['action' => $action], $attributes));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ActivityLog write failed', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
