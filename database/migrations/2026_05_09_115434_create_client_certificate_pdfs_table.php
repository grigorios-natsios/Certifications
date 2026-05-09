<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_certificate_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('certificate_categories')->cascadeOnDelete();
            $table->string('path');                       // relative to storage/app/public
            $table->string('public_url')->nullable();     // slug-based route URL
            $table->string('fingerprint', 32)->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['client_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_certificate_pdfs');
    }
};
