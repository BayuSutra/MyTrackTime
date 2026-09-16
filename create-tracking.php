<?php
// create-tracking.php - Jalankan dengan: php artisan tinker < create-tracking.php

$item = App\Models\Item::find(1);

if (!$item) {
    echo "❌ Item tidak ditemukan!\n";
    exit;
}

echo "📱 Item: {$item->name}\n";
echo "🆔 Device ID: {$item->device_id}\n\n";

// Hapus data lama
$deleted = App\Models\GPSTracking::where('device_id', $item->device_id)->delete();
echo "🗑️ Data lama dihapus: {$deleted} data\n\n";

// Data lokasi (25 titik)
$locations = [
    ['lat' => -6.208763, 'lng' => 106.845599, 'speed' => 0],
    ['lat' => -6.210000, 'lng' => 106.848000, 'speed' => 10],
    ['lat' => -6.212000, 'lng' => 106.850000, 'speed' => 15],
    ['lat' => -6.215000, 'lng' => 106.852000, 'speed' => 20],
    ['lat' => -6.218000, 'lng' => 106.855000, 'speed' => 25],
    ['lat' => -6.220000, 'lng' => 106.858000, 'speed' => 30],
    ['lat' => -6.222000, 'lng' => 106.860000, 'speed' => 35],
    ['lat' => -6.225000, 'lng' => 106.862000, 'speed' => 40],
    ['lat' => -6.228000, 'lng' => 106.865000, 'speed' => 45],
    ['lat' => -6.230000, 'lng' => 106.868000, 'speed' => 50],
    ['lat' => -6.232000, 'lng' => 106.870000, 'speed' => 55],
    ['lat' => -6.235000, 'lng' => 106.872000, 'speed' => 60],
    ['lat' => -6.238000, 'lng' => 106.875000, 'speed' => 65],
    ['lat' => -6.240000, 'lng' => 106.878000, 'speed' => 60],
    ['lat' => -6.242000, 'lng' => 106.880000, 'speed' => 55],
    ['lat' => -6.245000, 'lng' => 106.882000, 'speed' => 50],
    ['lat' => -6.248000, 'lng' => 106.885000, 'speed' => 45],
    ['lat' => -6.250000, 'lng' => 106.888000, 'speed' => 40],
    ['lat' => -6.252000, 'lng' => 106.890000, 'speed' => 35],
    ['lat' => -6.255000, 'lng' => 106.892000, 'speed' => 30],
    ['lat' => -6.258000, 'lng' => 106.895000, 'speed' => 25],
    ['lat' => -6.260000, 'lng' => 106.898000, 'speed' => 20],
    ['lat' => -6.262000, 'lng' => 106.900000, 'speed' => 15],
    ['lat' => -6.265000, 'lng' => 106.902000, 'speed' => 10],
    ['lat' => -6.268000, 'lng' => 106.905000, 'speed' => 5],
];

$count = 0;
foreach ($locations as $index => $loc) {
    $time = now()->subMinutes((count($locations) - $index) * 2);
    $battery = max(30, 100 - ($index * 2.8));
    
    App\Models\GPSTracking::create([
        'device_id' => $item->device_id,
        'latitude' => $loc['lat'],
        'longitude' => $loc['lng'],
        'speed' => $loc['speed'],
        'battery' => round($battery),
        'tracking_time' => $time,
    ]);
    $count++;
}

echo "✅ Berhasil menambahkan {$count} data tracking\n\n";

$total = App\Models\GPSTracking::where('device_id', $item->device_id)->count();
echo "Total data di database: {$total}\n\n";

echo "🎯 Buka dashboard: http://localhost:8000/dashboard\n";