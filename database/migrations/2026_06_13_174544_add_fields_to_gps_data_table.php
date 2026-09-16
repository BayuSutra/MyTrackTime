<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gps_data', function (Blueprint $table) {
            $table->decimal('altitude', 10, 2)->nullable()->after('speed');
            $table->decimal('accuracy', 10, 2)->nullable()->after('altitude');
        });
    }

    public function down(): void
    {
        Schema::table('gps_data', function (Blueprint $table) {
            $table->dropColumn(['altitude', 'accuracy']);
        });
    }
};