<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;


class Organization extends Model
{
   use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phones',
        'email',
        'hours',
        'website_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
    ];

    protected $casts = [
        'phones' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function clientCustomFields()
    {
        return $this->hasMany(\App\Models\ClientCustomField::class);
    }

}
