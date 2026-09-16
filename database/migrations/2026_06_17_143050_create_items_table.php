<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_code')->unique();
            $table->string('device_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('bi-box');
            $table->string('icon_color', 7)->default('#4e73df');
            $table->enum('status', ['pending', 'active', 'inactive', 'maintenance'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->decimal('price', 15, 2)->default(50000);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status', 'payment_status']);
            $table->index('item_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};