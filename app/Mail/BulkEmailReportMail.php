<?php

namespace App\Mail;

use App\Models\BulkEmailResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkEmailReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [30, 60, 120, 300, 600];

    public string $batchId;
    public int $sentCount;
    public int $failedCount;
    /** @var \Illuminate\Support\Collection<\App\Models\BulkEmailResult> */
    public $sentRows;
    /** @var \Illuminate\Support\Collection<\App\Models\BulkEmailResult> */
    public $failedRows;

    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;

        $rows = BulkEmailResult::where('batch_id', $batchId)->get();

        $this->sentRows    = $rows->where('status', 'sent')->values();
        $this->failedRows  = $rows->where('status', 'failed')->values();
        $this->sentCount   = $this->sentRows->count();
        $this->failedCount = $this->failedRows->count();
    }

    public function build()
    {
        $noun = $this->sentCount === 1 ? 'πιστοποιητικό' : 'πιστοποιητικά';

        $subject = $this->failedCount > 0
            ? "Αναφορά αποστολών — {$this->sentCount} επιτυχείς, {$this->failedCount} αποτυχίες"
            : "Αναφορά αποστολών — {$this->sentCount} {$noun}";

        return $this->subject($subject)
            ->view('emails.bulk-email-report');
    }
}
