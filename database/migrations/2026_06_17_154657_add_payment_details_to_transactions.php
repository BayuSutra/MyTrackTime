<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tambahkan kolom untuk payment real
            if (!Schema::hasColumn('transactions', 'payment_account')) {
                $table->string('payment_account')->nullable()->after('payment_channel');
            }
            if (!Schema::hasColumn('transactions', 'payment_account_name')) {
                $table->string('payment_account_name')->nullable()->after('payment_account');
            }
            if (!Schema::hasColumn('transactions', 'payment_receipt')) {
                $table->string('payment_receipt')->nullable()->after('payment_data');
            }
            if (!Schema::hasColumn('transactions', 'payment_verified_by')) {
                $table->foreignId('payment_verified_by')->nullable()->constrained('users')->after('payment_receipt');
            }
            if (!Schema::hasColumn('transactions', 'payment_verified_at')) {
                $table->timestamp('payment_verified_at')->nullable()->after('payment_verified_by');
            }
            if (!Schema::hasColumn('transactions', 'payment_expired_at')) {
                $table->timestamp('payment_expired_at')->nullable()->after('expired_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_account',
                'payment_account_name',
                'payment_receipt',
                'payment_verified_by',
                'payment_verified_at',
                'payment_expired_at'
            ]);
        });
    }
};