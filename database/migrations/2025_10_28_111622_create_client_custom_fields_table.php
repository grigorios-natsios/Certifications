<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name'); // π.χ. "ΑΦΜ"
            $table->string('type')->default('text'); // text, number, date, select, κλπ
            $table->json('options')->nullable(); // για dropdowns ή checkboxes
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_custom_fields');
    }
};
