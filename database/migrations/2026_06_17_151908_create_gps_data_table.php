<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_data', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('altitude', 10, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->integer('battery')->nullable();
            $table->timestamp('tracking_time');
            $table->timestamps();
            
            $table->index('device_id');
            $table->index('tracking_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_data');
    }
};