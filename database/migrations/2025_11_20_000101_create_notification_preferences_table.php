<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('server_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('check_name')->nullable();
            $table->string('channel');
            $table->boolean('enabled')->default(true);
            $table->timestamp('muted_until')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'server_id', 'check_name', 'channel'], 'notification_preferences_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
