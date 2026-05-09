<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_email_results', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->index();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email');
            $table->enum('status', ['sent', 'failed']);
            $table->text('error')->nullable();
            $table->string('report_recipient');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_email_results');
    }
};
