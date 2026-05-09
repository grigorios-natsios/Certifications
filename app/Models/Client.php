<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'url_slug',
        'external_id',
        'organization_id',
        'certificate_category_id',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->lastname ?? '').' '.($this->name ?? ''));
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function certificateCategories()
    {
        return $this->belongsToMany(CertificateCategory::class, 'certificate_category_client')
        ->withTimestamps();
    }

    public function customValues()
    {
        return $this->hasMany(ClientCustomValue::class);
    }

    public function customFieldValue(int $fieldId, ?int $categoryId = null)
    {
        $q = $this->customValues()->where('custom_field_id', $fieldId);

        if ($categoryId !== null) {
            // Prefer the value scoped to this category; fall back to a legacy
            // (NULL category) value if no scoped value exists.
            $scoped = (clone $q)->where('certificate_category_id', $categoryId)->first();
            return $scoped ?? (clone $q)->whereNull('certificate_category_id')->first();
        }

        return $q->first();
    }

    public function certificatePdfs()
    {
        return $this->hasMany(ClientCertificatePdf::class);
    }
}
