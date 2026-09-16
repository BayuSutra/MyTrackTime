<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Item;

class CheckPaymentStatus
{
    public function handle(Request $request, Closure $next)
    {
        $itemId = $request->route('id') ?? $request->route('item');
        
        if ($itemId) {
            $item = Item::find($itemId);
            
            if ($item && $item->payment_status !== 'paid') {
                return redirect()->route('transactions.payment', $item->transactions()->latest()->first())
                    ->with('error', 'Silakan lakukan pembayaran terlebih dahulu.');
            }
        }

        return $next($request);
    }
}