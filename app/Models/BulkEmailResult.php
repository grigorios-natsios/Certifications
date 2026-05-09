<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkEmailResult extends Model
{
    protected $fillable = [
        'batch_id',
        'client_id',
        'name',
        'email',
        'status',
        'error',
        'report_recipient',
    ];
}
