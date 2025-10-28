<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCustomField extends Model
{
    protected $fillable = ['organization_id', 'name', 'type', 'options', 'is_required'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];
    
    public function values()
    {
        return $this->hasMany(ClientCustomValue::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
