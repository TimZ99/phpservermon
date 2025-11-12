<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the deprecated preferences table if it exists
        Schema::dropIfExists('notification_preferences');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: original creation migration has been neutralized; this is irreversible in practice.
    }
};
