<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    // Konfigurasi Payment
    private $paymentConfigs = [
        'bca' => [
            'name' => 'Bank BCA',
            'account_number' => '1234567890',
            'account_name' => 'PT GPS Tracking Indonesia',
            'type' => 'bank_transfer',
            'icon' => 'bi-bank'
        ],
        'bni' => [
            'name' => 'Bank BNI',
            'account_number' => '9876543210',
            'account_name' => 'PT GPS Tracking Indonesia',
            'type' => 'bank_transfer',
            'icon' => 'bi-bank'
        ],
        'gopay' => [
            'name' => 'GoPay',
            'account_number' => '089515563894',
            'account_name' => 'GPS Tracking',
            'type' => 'ewallet',
            'icon' => 'bi-wallet2'
        ],
        'ovo' => [
            'name' => 'OVO',
            'account_number' => '089515563894',
            'account_name' => 'GPS Tracking',
            'type' => 'ewallet',
            'icon' => 'bi-wallet2'
        ],
        'dana' => [
            'name' => 'DANA',
            'account_number' => '089515563894',
            'account_name' => 'GPS Tracking',
            'type' => 'ewallet',
            'icon' => 'bi-wallet2'
        ],
        'qris' => [
            'name' => 'QRIS',
            'account_number' => 'QRIS-STATIC-CODE',
            'account_name' => 'GPS Tracking',
            'type' => 'qris',
            'icon' => 'bi-qr-code'
        ],
    ];

    public function showPayment($transactionId)
    {
        $transaction = Transaction::where('user_id', Auth::id())
            ->with('item')
            ->findOrFail($transactionId);

        if ($transaction->payment_expired_at && $transaction->payment_expired_at < now()) {
            $transaction->update(['status' => 'expired']);
            return redirect()->route('transactions.index')
                ->with('error', 'Waktu pembayaran telah habis. Silakan daftar ulang item.');
        }

        if ($transaction->status === 'paid') {
            return redirect()->route('dashboard')
                ->with('success', 'Item sudah aktif!');
        }

        $paymentConfigs = $this->paymentConfigs;

        return view('payment.index', compact('transaction', 'paymentConfigs'));
    }

    public function uploadReceipt(Request $request, $transactionId)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_channel' => 'required|string',
            'payment_account' => 'required|string',
            'payment_receipt' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $transaction = Transaction::where('user_id', Auth::id())
            ->findOrFail($transactionId);

        if ($transaction->payment_expired_at && $transaction->payment_expired_at < now()) {
            return back()->with('error', 'Waktu pembayaran telah habis!');
        }

        // Upload bukti pembayaran
        $file = $request->file('payment_receipt');
        $path = $file->store('payment_receipts', 'public');

        $transaction->update([
            'payment_method' => $request->payment_method,
            'payment_channel' => $request->payment_channel,
            'payment_account' => $request->payment_account,
            'payment_receipt' => $path,
            'status' => 'processing',
            'payment_data' => [
                'method' => $request->payment_method,
                'channel' => $request->payment_channel,
                'account' => $request->payment_account,
                'uploaded_at' => now()->toDateTimeString(),
                'amount' => $transaction->amount,
            ],
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Bukti pembayaran berhasil diupload! Menunggu verifikasi admin.');
    }

    // Admin: Verifikasi Pembayaran
    public function verifyPayment($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);

        if ($transaction->status === 'paid') {
            return back()->with('info', 'Pembayaran sudah diverifikasi sebelumnya.');
        }

        $transaction->update([
            'status' => 'paid',
            'payment_date' => now(),
            'payment_verified_by' => Auth::id(),
            'payment_verified_at' => now(),
        ]);

        // Aktifkan item
        $item = Item::find($transaction->item_id);
        if ($item) {
            $item->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'activated_at' => now(),
                'expired_at' => now()->addMonths(1),
            ]);
        }

        // Redirect ke admin dashboard
        return redirect()->route('admin.dashboard')
            ->with('success', '✅ Pembayaran berhasil diverifikasi! Item aktif.');
    }

    // Admin: Tolak Pembayaran
    public function rejectPayment($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);

        $transaction->update([
            'status' => 'failed',
            'payment_data' => array_merge($transaction->payment_data ?? [], [
                'rejected_at' => now()->toDateTimeString(),
                'rejected_by' => Auth::id(),
            ]),
        ]);

        // Redirect ke admin dashboard
        return redirect()->route('admin.dashboard')
            ->with('warning', '❌ Pembayaran ditolak.');
    }

    // Admin: Lihat daftar pembayaran pending (opsional)
    public function pendingPayments()
    {
        $transactions = Transaction::where('status', 'processing')
            ->with('user', 'item')
            ->latest()
            ->get();

        return view('admin.payments', compact('transactions'));
    }

    public function getPaymentConfigs()
    {
        return response()->json($this->paymentConfigs);
    }
}