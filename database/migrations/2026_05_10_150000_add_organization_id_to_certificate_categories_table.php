<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('certificate_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
        });

        $firstOrgId = DB::table('organizations')->orderBy('id')->value('id');
        if ($firstOrgId) {
            DB::table('certificate_categories')
                ->whereNull('organization_id')
                ->update(['organization_id' => $firstOrgId]);
        }

        Schema::table('certificate_categories', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')->on('organizations')
                ->cascadeOnDelete();
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_categories', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
