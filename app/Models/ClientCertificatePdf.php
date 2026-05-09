<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCertificatePdf extends Model
{
    protected $fillable = [
        'client_id',
        'category_id',
        'path',
        'public_url',
        'fingerprint',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(CertificateCategory::class, 'category_id');
    }

    public function absolutePath(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->path));
    }

    public function fileExists(): bool
    {
        return is_file($this->absolutePath());
    }
}
