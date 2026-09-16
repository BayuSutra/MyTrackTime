<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\GPSTracking;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ambil items user
        $items = Item::where('user_id', $user->id)
            ->with(['mainPhoto'])
            ->get();
        
        // Load latest tracking untuk setiap item
        foreach ($items as $item) {
            if ($item->device_id) {
                $latest = DB::table('gps_data')
                    ->where('device_id', $item->device_id)
                    ->latest('tracking_time')
                    ->first();
                
                if ($latest) {
                    $latest->tracking_time = Carbon::parse($latest->tracking_time);
                    $item->latestTracking = $latest;
                }
            }
        }
        
        $hasItems = $items->count() > 0;
        
        // Hitung online items
        $onlineItems = 0;
        if ($hasItems) {
            $deviceIds = $items->pluck('device_id')->filter()->toArray();
            if (!empty($deviceIds)) {
                $onlineItems = DB::table('gps_data')
                    ->whereIn('device_id', $deviceIds)
                    ->where('tracking_time', '>=', now()->subMinutes(5))
                    ->distinct('device_id')
                    ->count('device_id');
            }
        }
        
        // Statistik
        $stats = [
            'total_items' => $items->count(),
            'active_items' => $items->where('status', 'active')->count(),
            'pending_payment' => $items->where('payment_status', 'pending')->count(),
            'online_items' => $onlineItems,
            'total_tracking' => DB::table('gps_data')->count(),
        ];

        // Recent trackings
        $recentTrackings = collect();
        if ($hasItems) {
            $deviceIds = $items->pluck('device_id')->filter()->toArray();
            if (!empty($deviceIds)) {
                $recentTrackings = DB::table('gps_data')
                    ->whereIn('device_id', $deviceIds)
                    ->latest('tracking_time')
                    ->limit(10)
                    ->get();
                
                foreach ($recentTrackings as $tracking) {
                    $tracking->tracking_time = Carbon::parse($tracking->tracking_time);
                    $item = $items->firstWhere('device_id', $tracking->device_id);
                    $tracking->item_name = $item ? $item->name : $tracking->device_id;
                    $tracking->item_id = $item ? $item->id : null;
                    $tracking->item_code = $item ? $item->item_code : null;
                    $tracking->icon = $item ? $item->icon : 'bi-box';
                    $tracking->icon_color = $item ? $item->icon_color : '#4e73df';
                }
            }
        }

        return view('dashboard', compact('items', 'stats', 'recentTrackings', 'hasItems'));
    }

    // ============================================
    // ADMIN DASHBOARD - KHUSUS VERIFIKASI PAYMENT
    // ============================================
    public function adminDashboard()
    {
        // Ambil semua transaksi yang pending (menunggu verifikasi)
        $pendingTransactions = Transaction::where('status', 'processing')
            ->with(['user', 'item'])
            ->latest()
            ->get();

        // Ambil transaksi yang sudah diverifikasi hari ini
        $verifiedToday = Transaction::where('status', 'paid')
            ->whereDate('payment_verified_at', today())
            ->count();

        // Ambil transaksi yang ditolak hari ini
        $rejectedToday = Transaction::where('status', 'failed')
            ->whereDate('updated_at', today())
            ->count();

        // Total transaksi hari ini
        $totalToday = Transaction::whereDate('created_at', today())->count();

        $stats = [
            'pending_count' => $pendingTransactions->count(),
            'verified_today' => $verifiedToday,
            'rejected_today' => $rejectedToday,
            'total_today' => $totalToday,
        ];

        return view('admin.dashboard', compact('pendingTransactions', 'stats'));
    }
}