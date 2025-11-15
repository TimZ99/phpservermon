<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('overall_status')->default('unknown');
            $table->timestamp('overall_status_changed_at')->nullable();
            $table->uuid('last_check_run_id')->nullable();
            $table->timestamp('last_checked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn([
                'overall_status',
                'overall_status_changed_at',
                'last_check_run_id',
                'last_checked_at',
            ]);
        });
    }
};
