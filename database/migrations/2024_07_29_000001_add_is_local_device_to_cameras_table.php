<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            // Marks a camera as "use the browser device camera directly" (no IP/stream needed)
            $table->boolean('is_local_device')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn('is_local_device');
        });
    }
};
