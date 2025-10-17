<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'svg_path'];

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'category_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

}
