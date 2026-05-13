<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('activity_logs')->where('action', 'certificate_view')->delete();
    }

    public function down(): void
    {
        // Deleted activity-log rows are not restorable.
    }
};
