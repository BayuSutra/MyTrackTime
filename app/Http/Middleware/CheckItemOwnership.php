<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Item;

class CheckItemOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $itemId = $request->route('id') ?? $request->route('item');
        
        if ($itemId) {
            $item = Item::find($itemId);
            
            if (!$item) {
                abort(404, 'Item not found');
            }
            
            if ($item->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
                abort(403, 'You do not own this item');
            }
        }

        return $next($request);
    }
}