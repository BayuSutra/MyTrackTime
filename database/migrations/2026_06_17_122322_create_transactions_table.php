<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('transaction_code')->unique();
            $table->enum('type', ['registration', 'renewal', 'upgrade'])->default('registration');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // bank_transfer, ewallet, qris
            $table->string('payment_channel')->nullable(); // gopay, ovo, bca, etc
            $table->string('payment_code')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'expired', 'refunded'])->default('pending');
            $table->json('payment_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'item_id', 'status']);
            $table->index('transaction_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};