<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientQrCode extends Model
{
    protected $fillable = ['client_id', 'category_id', 'url', 'image_base64'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(CertificateCategory::class, 'category_id');
    }
}
