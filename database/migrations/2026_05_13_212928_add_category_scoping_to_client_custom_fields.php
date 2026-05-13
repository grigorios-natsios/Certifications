<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('client_custom_fields', 'applies_to_all')) {
            Schema::table('client_custom_fields', function (Blueprint $table) {
                $table->boolean('applies_to_all')->default(true)->after('options');
            });
        }

        if (! Schema::hasTable('client_custom_field_category')) {
            Schema::create('client_custom_field_category', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('client_custom_field_id');
                $table->unsignedBigInteger('certificate_category_id');

                $table->foreign('client_custom_field_id', 'ccfc_field_fk')
                    ->references('id')->on('client_custom_fields')
                    ->cascadeOnDelete();

                $table->foreign('certificate_category_id', 'ccfc_category_fk')
                    ->references('id')->on('certificate_categories')
                    ->cascadeOnDelete();

                $table->unique(
                    ['client_custom_field_id', 'certificate_category_id'],
                    'ccfc_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_custom_field_category');

        if (Schema::hasColumn('client_custom_fields', 'applies_to_all')) {
            Schema::table('client_custom_fields', function (Blueprint $table) {
                $table->dropColumn('applies_to_all');
            });
        }
    }
};
