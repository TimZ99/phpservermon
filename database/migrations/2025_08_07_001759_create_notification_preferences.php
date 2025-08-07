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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('event');
            $table->string('channel');
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'event', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            // Drop the unique index and foreign key before dropping the table
            $table->dropUnique(['user_id', 'event', 'channel']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('notification_preferences');
    }
};
