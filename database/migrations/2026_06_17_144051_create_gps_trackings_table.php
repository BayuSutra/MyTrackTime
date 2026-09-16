<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('device_id')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('altitude', 10, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 10, 2)->nullable();
            $table->integer('battery')->nullable();
            $table->integer('signal_strength')->nullable();
            $table->string('address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('tracking_time');
            $table->timestamps();
            
            $table->index(['item_id', 'tracking_time']);
            $table->index(['latitude', 'longitude']);
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_trackings');
    }
};