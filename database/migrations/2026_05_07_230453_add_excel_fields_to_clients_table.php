<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('lastname')->nullable()->after('name');
            $table->string('external_id')->nullable()->after('id');
            $table->string('url_slug')->nullable()->after('email');

            $table->index(['organization_id', 'external_id']);
            $table->index(['organization_id', 'url_slug']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'external_id']);
            $table->dropIndex(['organization_id', 'url_slug']);
            $table->dropColumn(['lastname', 'external_id', 'url_slug']);
        });
    }
};
