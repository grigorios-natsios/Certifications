<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('certificate_categories')->cascadeOnDelete();
            $table->text('url');
            $table->mediumText('image_base64');
            $table->timestamps();

            $table->unique(['client_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_qr_codes');
    }
};
