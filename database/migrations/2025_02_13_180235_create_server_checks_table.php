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
        Schema::create('check_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id');
            $table->uuid('batch_id')->nullable();
            $table->string('name');
            $table->string('status');
            $table->string('message');
            $table->json('check_settings')->default(json_encode([]));
            $table->timestamps();
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->json('check_settings')->default(json_encode([]));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_history');
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('check_settings');
        });
    }
};
