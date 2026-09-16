<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_photos', function (Blueprint $table) {
            // Cek apakah kolom item_id ada
            if (!Schema::hasColumn('item_photos', 'item_id')) {
                $table->foreignId('item_id')->constrained()->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_photos', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
};