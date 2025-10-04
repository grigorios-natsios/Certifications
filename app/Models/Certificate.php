<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'html_content', 'json_content', 'organization_id',];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
