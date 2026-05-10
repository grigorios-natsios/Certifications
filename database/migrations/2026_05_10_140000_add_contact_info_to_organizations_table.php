<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('address')->nullable()->after('name');
            $table->json('phones')->nullable()->after('address');
            $table->string('email')->nullable()->after('phones');
            $table->string('hours')->nullable()->after('email');
            $table->string('website_url')->nullable()->after('hours');
            $table->string('facebook_url')->nullable()->after('website_url');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('youtube_url')->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'phones',
                'email',
                'hours',
                'website_url',
                'facebook_url',
                'instagram_url',
                'youtube_url',
            ]);
        });
    }
};
