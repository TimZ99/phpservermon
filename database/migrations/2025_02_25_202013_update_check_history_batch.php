<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('check_history', function (Blueprint $table) {
            $table->renameColumn('batch_id', 'server_checks_batch_id');
            $table->uuid('run_curl_batch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_history', function (Blueprint $table) {
            $table->renameColumn('server_checks_batch_id', 'batch_id');
            $table->dropColumn('run_curl_batch_id');
        });
    }
};
