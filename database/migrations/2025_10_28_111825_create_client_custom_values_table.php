<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('custom_field_id')->constrained('client_custom_fields')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_custom_values');
    }
};
