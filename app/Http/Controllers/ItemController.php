<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\Transaction;
use App\Models\GPSTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::where('user_id', Auth::id())
            ->with(['mainPhoto'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Load latest tracking untuk setiap item
        foreach ($items as $item) {
            if ($item->device_id) {
                $item->latestTracking = GPSTracking::where('device_id', $item->device_id)
                    ->latest('tracking_time')
                    ->first();
            }
        }
        
        return view('items.index', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'icon_color' => 'nullable|string|max:7',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $itemCode = 'ITEM-' . strtoupper(Str::random(8));

        $item = Item::create([
            'user_id' => Auth::id(),
            'item_code' => $itemCode,
            'device_id' => $itemCode,
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon ?? 'bi-box',
            'icon_color' => $request->icon_color ?? '#4e73df',
            'status' => 'pending',
            'payment_status' => 'pending',
            'price' => 50000,
            'settings' => json_encode([
                'notification' => true,
                'alert_radius' => 100
            ]),
        ]);

        // Upload photo jika ada
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $path = $photo->store('items', 'public');
            
            ItemPhoto::create([
                'item_id' => $item->id,
                'photo_path' => $path,
                'photo_name' => $photo->getClientOriginalName(),
                'type' => 'main',
                'order' => 0,
            ]);
        }

        // Buat transaksi
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'transaction_code' => 'TRX-' . strtoupper(Str::random(12)),
            'type' => 'registration',
            'amount' => 50000,
            'status' => 'pending',
            'expired_at' => now()->addHours(24),
            'notes' => 'Pendaftaran item: ' . $request->name,
        ]);

        return redirect()->route('transactions.payment', $transaction->id)
            ->with('success', 'Item berhasil ditambahkan! Silakan lakukan pembayaran.');
    }

    public function show($id)
    {
        $item = Item::where('user_id', Auth::id())
            ->with(['photos']) // Load semua photos
            ->findOrFail($id);
        
        // Ambil main photo
        $mainPhoto = $item->photos()->where('type', 'main')->first();
        
        // Ambil tracking data via device_id
        $trackings = collect();
        if ($item->device_id) {
            $trackings = GPSTracking::where('device_id', $item->device_id)
                ->latest('tracking_time')
                ->limit(100)
                ->get();
        }
        
        // Data untuk chart
        $chartData = collect();
        if ($item->device_id && $trackings->count() > 0) {
            $chartData = GPSTracking::where('device_id', $item->device_id)
                ->selectRaw('DATE(tracking_time) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get();
        }
        
        return view('items.show', compact('item', 'trackings', 'chartData', 'mainPhoto'));
    }


    public function edit($id)
    {
        $item = Item::where('user_id', Auth::id())->findOrFail($id);
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'icon_color' => 'nullable|string|max:7',
        ]);

        $item->update($request->only(['name', 'description', 'icon', 'icon_color']));

        return redirect()->route('items.index')->with('success', 'Item updated!');
    }

    public function destroy($id)
{
    try {
        $item = Item::where('user_id', Auth::id())->findOrFail($id);
        
        // Hapus foto terkait
        foreach ($item->photos as $photo) {
            if ($photo->photo_path && file_exists(storage_path('app/public/' . $photo->photo_path))) {
                unlink(storage_path('app/public/' . $photo->photo_path));
            }
            $photo->delete();
        }
        
        // Hapus tracking data
        GPSTracking::where('device_id', $item->device_id)->delete();
        
        // Hapus transaksi terkait
        Transaction::where('item_id', $item->id)->delete();
        
        // Hapus item
        $item->delete();
        
        // Redirect ke halaman items dengan pesan sukses
        return redirect()->route('items.index')
            ->with('success', 'Item berhasil dihapus!');
        
    } catch (\Exception $e) {
        return redirect()->route('items.index')
            ->with('error', 'Gagal menghapus item: ' . $e->getMessage());
    }
}

    public function activate($id)
    {
        $item = Item::where('user_id', Auth::id())->findOrFail($id);
        
        if (!$item->canMonitor()) {
            return back()->with('error', 'Item belum aktif. Silakan bayar dulu.');
        }

        return redirect()->route('items.show', $id)->with('success', 'Item siap digunakan!');
    }
}