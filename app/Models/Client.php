<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'organization_id', 'certificate_category_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function certificateCategories()
    {
        return $this->belongsToMany(CertificateCategory::class, 'certificate_category_client')
        ->withTimestamps();
    }
}
