<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_photos', function (Blueprint $table) {
            // Tambahkan kolom photo_path
            if (!Schema::hasColumn('item_photos', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('item_id');
            }
            
            // Tambahkan kolom photo_name
            if (!Schema::hasColumn('item_photos', 'photo_name')) {
                $table->string('photo_name')->nullable()->after('photo_path');
            }
            
            // Tambahkan kolom type
            if (!Schema::hasColumn('item_photos', 'type')) {
                $table->enum('type', ['main', 'thumbnail', 'gallery'])->default('main')->after('photo_name');
            }
            
            // Tambahkan kolom order
            if (!Schema::hasColumn('item_photos', 'order')) {
                $table->integer('order')->default(0)->after('type');
            }
            
            // Tambahkan kolom metadata
            if (!Schema::hasColumn('item_photos', 'metadata')) {
                $table->json('metadata')->nullable()->after('order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_photos', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'photo_name', 'type', 'order', 'metadata']);
        });
    }
};