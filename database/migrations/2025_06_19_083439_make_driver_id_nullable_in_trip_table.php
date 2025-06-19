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
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->unsignedBigInteger('driver_id')->nullable()->change();
            $table->foreign('driver_id')
                  ->references('id')
                  ->on('drivers')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->unsignedBigInteger('driver_id')->nullable(false)->change();
            $table->foreign('driver_id')
                ->references('id')
                ->on('drivers');
        });
    }
};
