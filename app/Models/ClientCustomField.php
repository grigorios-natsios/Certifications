<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCustomField extends Model
{
    protected $fillable = ['organization_id', 'name', 'type', 'options', 'is_required', 'applies_to_all'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'applies_to_all' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(ClientCustomValue::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            CertificateCategory::class,
            'client_custom_field_category',
            'client_custom_field_id',
            'certificate_category_id'
        );
    }

    public function appliesToCategory(int $categoryId): bool
    {
        if ($this->applies_to_all) {
            return true;
        }
        return $this->categories->contains('id', $categoryId);
    }
}
