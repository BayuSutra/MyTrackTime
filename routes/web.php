<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// ============================================
// GUEST ROUTES (TIDAK PERLU LOGIN)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

// ============================================
// AUTH ROUTES (HARUS LOGIN)
// ============================================
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::post('/payments/{id}/verify', [PaymentController::class, 'verifyPayment'])->name('payments.verify');
        Route::post('/payments/{id}/reject', [PaymentController::class, 'rejectPayment'])->name('payments.reject');
    });
    
    // ============================================
    // ITEMS ROUTES (LENGKAP)
    // ============================================
    Route::resource('items', ItemController::class);
    
    // Tambahan route untuk activate
    Route::post('/items/{id}/activate', [ItemController::class, 'activate'])->name('items.activate');
    
    // ============================================
    // TRANSACTIONS ROUTES
    // ============================================
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}/payment', [TransactionController::class, 'payment'])->name('transactions.payment');
    Route::post('/transactions/{id}/process', [TransactionController::class, 'processPayment'])->name('transactions.process');
    Route::post('/transactions/{id}/simulate', [TransactionController::class, 'paymentSimulation'])->name('transactions.simulate');
    
    // ============================================
    // PAYMENT ROUTES
    // ============================================
    Route::get('/payment/{transactionId}', [PaymentController::class, 'showPayment'])->name('payment.show');
    Route::post('/payment/{transactionId}/upload', [PaymentController::class, 'uploadReceipt'])->name('payment.upload');
    Route::get('/payment/configs', [PaymentController::class, 'getPaymentConfigs'])->name('payment.configs');
    
    // Root
    Route::get('/', function () {
        if (auth()->user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        return redirect('/dashboard');
    });
});