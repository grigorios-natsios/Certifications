<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_certificate_pdfs', function (Blueprint $table) {
            $table->string('bulk_filename')->nullable()->after('public_url');
        });
    }

    public function down(): void
    {
        Schema::table('client_certificate_pdfs', function (Blueprint $table) {
            $table->dropColumn('bulk_filename');
        });
    }
};
