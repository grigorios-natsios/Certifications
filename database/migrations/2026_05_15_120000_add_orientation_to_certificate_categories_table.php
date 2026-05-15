<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('certificate_categories', 'orientation')) {
            Schema::table('certificate_categories', function (Blueprint $table) {
                $table->string('orientation', 16)->default('portrait')->after('html_template');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('certificate_categories', 'orientation')) {
            Schema::table('certificate_categories', function (Blueprint $table) {
                $table->dropColumn('orientation');
            });
        }
    }
};
