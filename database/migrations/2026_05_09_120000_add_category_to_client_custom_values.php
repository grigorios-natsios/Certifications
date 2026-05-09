<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client_custom_values', function (Blueprint $table) {
            $table->foreignId('certificate_category_id')
                ->nullable()
                ->after('custom_field_id')
                ->constrained('certificate_categories')
                ->cascadeOnDelete();
        });

        // Best-effort backfill: clients with exactly one attached category get their
        // existing values scoped to that category. Clients with multiple categories
        // are left NULL (legacy fallback) and will be properly scoped on next import.
        DB::statement("
            UPDATE client_custom_values cv
            INNER JOIN (
                SELECT client_id, MIN(certificate_category_id) AS cat_id
                FROM certificate_category_client
                GROUP BY client_id
                HAVING COUNT(*) = 1
            ) ccc ON ccc.client_id = cv.client_id
            SET cv.certificate_category_id = ccc.cat_id
            WHERE cv.certificate_category_id IS NULL
        ");

        Schema::table('client_custom_values', function (Blueprint $table) {
            $table->unique(
                ['client_id', 'custom_field_id', 'certificate_category_id'],
                'ccv_unique_per_cert'
            );
        });
    }

    public function down(): void
    {
        Schema::table('client_custom_values', function (Blueprint $table) {
            $table->dropUnique('ccv_unique_per_cert');
            $table->dropConstrainedForeignId('certificate_category_id');
        });
    }
};
