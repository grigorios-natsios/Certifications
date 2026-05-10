<?php
namespace App\Models;

use App\Services\CertificatePdfStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateCategory extends Model
{
    use HasFactory;

    protected $fillable = ['organization_id', 'name', 'slug', 'html_template'];

    protected static function booted(): void
    {
        static::deleting(function (CertificateCategory $category) {
            app(CertificatePdfStore::class)->deleteAllForCategory($category);
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'certificate_category_client')
        ->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'category_id');
    }

}
