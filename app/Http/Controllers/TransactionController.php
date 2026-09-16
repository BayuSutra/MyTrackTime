<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with('item')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('transactions.index', compact('transactions'));
    }

    public function payment($id)
    {
        // Redirect ke PaymentController
        return redirect()->route('payment.show', $id);
    }

    public function processPayment(Request $request, $id)
    {
        // Redirect ke PaymentController
        return redirect()->route('payment.upload', $id);
    }

    public function paymentSimulation(Request $request, $id)
    {
        // Simulasi payment (untuk testing)
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        
        $transaction->update([
            'payment_method' => 'simulation',
            'payment_channel' => 'simulation',
            'payment_code' => 'SIM-' . strtoupper(uniqid()),
            'payment_date' => now(),
            'status' => 'paid',
            'payment_data' => [
                'method' => 'simulation',
                'channel' => 'simulation',
                'paid_at' => now(),
                'simulation' => true,
            ],
        ]);

        $item = Item::find($transaction->item_id);
        $item->update([
            'status' => 'active',
            'payment_status' => 'paid',
            'activated_at' => now(),
            'expired_at' => now()->addMonths(1),
        ]);

        return redirect()->route('dashboard')
            ->with('success', '✅ Pembayaran simulasi berhasil! (Mode Testing)');
    }
}