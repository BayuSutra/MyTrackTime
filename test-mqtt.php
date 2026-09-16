<?php
// gps-simulator-2devices.php
require __DIR__.'/vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

// ============================================================
// KONFIGURASI
// ============================================================
$host = 'raf6fc91.ala.us-east-1.emqxsl.com';
$port = 8883;
$username = 'Admin';
$password = 'Admin';

// ============================================================
// 2 DEVICE ID
// ============================================================
$devices = [
    [
        'id' => 'ITEM-W4GJNN4G',
        'name' => 'Device 1',
        'route' => [
            ['lat' => -6.208763, 'lng' => 106.845599, 'speed' => 0],
            ['lat' => -6.210000, 'lng' => 106.848000, 'speed' => 15],
            ['lat' => -6.212000, 'lng' => 106.850000, 'speed' => 25],
            ['lat' => -6.215000, 'lng' => 106.852000, 'speed' => 35],
            ['lat' => -6.218000, 'lng' => 106.855000, 'speed' => 45],
            ['lat' => -6.220000, 'lng' => 106.858000, 'speed' => 55],
            ['lat' => -6.222000, 'lng' => 106.860000, 'speed' => 60],
            ['lat' => -6.225000, 'lng' => 106.862000, 'speed' => 65],
            ['lat' => -6.228000, 'lng' => 106.865000, 'speed' => 70],
            ['lat' => -6.230000, 'lng' => 106.868000, 'speed' => 55],
        ]
    ],
    [
        'id' => 'ITEM-AQCJBNGI',
        'name' => 'Device 2',
        'route' => [
            ['lat' => -6.250000, 'lng' => 106.900000, 'speed' => 0],
            ['lat' => -6.248000, 'lng' => 106.898000, 'speed' => 20],
            ['lat' => -6.245000, 'lng' => 106.895000, 'speed' => 30],
            ['lat' => -6.242000, 'lng' => 106.892000, 'speed' => 40],
            ['lat' => -6.238000, 'lng' => 106.888000, 'speed' => 50],
            ['lat' => -6.235000, 'lng' => 106.885000, 'speed' => 55],
            ['lat' => -6.232000, 'lng' => 106.882000, 'speed' => 60],
            ['lat' => -6.228000, 'lng' => 106.878000, 'speed' => 65],
            ['lat' => -6.225000, 'lng' => 106.875000, 'speed' => 70],
            ['lat' => -6.222000, 'lng' => 106.872000, 'speed' => 55],
        ]
    ],
];

// ============================================================
// FUNGSI
// ============================================================
function generateGPSData($deviceId, $lat, $lng, $speed) {
    return [
        'device_id' => $deviceId,
        'latitude' => $lat + (rand(-5, 5) / 10000),
        'longitude' => $lng + (rand(-5, 5) / 10000),
        'speed' => $speed + rand(-5, 5),
        'battery' => rand(60, 95),
        'signal' => rand(-70, -40),
        'timestamp' => time() * 1000,
    ];
}

function publishGPSData($client, $deviceId, $data) {
    $topic = "gps/data/" . $deviceId . "/location";
    $payload = json_encode($data);
    $client->publish($topic, $payload, 0);
    echo "📤 " . date('H:i:s') . " | {$deviceId} | ";
    echo "Lat: " . number_format($data['latitude'], 6) . " | ";
    echo "Lng: " . number_format($data['longitude'], 6) . " | ";
    echo "Speed: " . $data['speed'] . " km/h | ";
    echo "Battery: " . $data['battery'] . "%\n";
}

function publishStatus($client, $deviceId, $status) {
    $topic = "gps/data/" . $deviceId . "/status";
    $payload = json_encode([
        'device_id' => $deviceId,
        'status' => $status,
        'battery' => rand(60, 95),
        'signal' => rand(-70, -40),
        'timestamp' => time() * 1000,
    ]);
    $client->publish($topic, $payload, 0);
    echo "📤 Status: {$deviceId} -> {$status}\n";
}

// ============================================================
// KONEKSI MQTT
// ============================================================
echo "==================================================\n";
echo "  🚗 GPS SIMULATOR ESP32 (2 DEVICES)\n";
echo "  MQTT Broker: " . $host . "\n";
echo "  Interval: 2 detik\n";
echo "==================================================\n\n";

echo "📱 Devices:\n";
foreach ($devices as $d) {
    echo "   - {$d['name']}: {$d['id']}\n";
}
echo "\n";

try {
    $client = new MqttClient($host, $port, 'php_simulator_2dev_' . uniqid());
    
    $settings = (new ConnectionSettings())
        ->setUsername($username)
        ->setPassword($password)
        ->setUseTls(true)
        ->setTlsSelfSignedAllowed(true)
        ->setTlsVerifyPeer(false)
        ->setTlsVerifyPeerName(false)
        ->setConnectTimeout(30);
    
    echo "🔌 Connecting to MQTT...\n";
    $client->connect($settings);
    echo "✅ Connected to EMQX!\n\n";
    
    // Subscribe untuk semua device
    foreach ($devices as $device) {
        $deviceId = $device['id'];
        publishStatus($client, $deviceId, 'online');
        
        $client->subscribe('gps/command/' . $deviceId . '/control', function ($topic, $message) use ($deviceId, $client) {
            echo "\n📩 Command received for {$deviceId}: " . $message . "\n";
            $data = json_decode($message, true);
            if (isset($data['command']) && $data['command'] == 'ping') {
                $response = json_encode([
                    'response' => 'pong',
                    'device_id' => $deviceId,
                    'timestamp' => time() * 1000
                ]);
                $client->publish('gps/response/' . $deviceId . '/pong', $response, 0);
                echo "🏓 Pong sent for {$deviceId}\n";
            }
            if (isset($data['command']) && $data['command'] == 'buzzer_on') {
                echo "🔊 Buzzer ON for {$deviceId}\n";
                $response = json_encode([
                    'response' => 'buzzer_on',
                    'device_id' => $deviceId,
                    'timestamp' => time() * 1000
                ]);
                $client->publish('gps/response/' . $deviceId . '/buzzer', $response, 0);
            }
            if (isset($data['command']) && $data['command'] == 'buzzer_off') {
                echo "🔇 Buzzer OFF for {$deviceId}\n";
                $response = json_encode([
                    'response' => 'buzzer_off',
                    'device_id' => $deviceId,
                    'timestamp' => time() * 1000
                ]);
                $client->publish('gps/response/' . $deviceId . '/buzzer', $response, 0);
            }
            if (isset($data['command']) && $data['command'] == 'buzzer_beep') {
                echo "🔊🔊🔊 Buzzer BEEP for {$deviceId}\n";
                $response = json_encode([
                    'response' => 'buzzer_beep',
                    'device_id' => $deviceId,
                    'timestamp' => time() * 1000
                ]);
                $client->publish('gps/response/' . $deviceId . '/buzzer', $response, 0);
            }
        }, 0);
    }
    
    $client->subscribe('gps/command/broadcast', function ($topic, $message) {
        echo "\n📢 Broadcast: " . $message . "\n";
    }, 0);
    
    echo "👂 Listening for commands...\n\n";
    echo "==================================================\n";
    echo "🚀 Simulator running! Press Ctrl+C to stop\n";
    echo "==================================================\n\n";
    
    // ============================================================
    // LOOP UTAMA - Kirim data bergantian
    // ============================================================
    $routeIndex = 0;
    $counter = 0;
    $deviceCount = count($devices);
    $maxRouteSize = 0;
    
    // Cari ukuran route terbesar
    foreach ($devices as $d) {
        if (count($d['route']) > $maxRouteSize) {
            $maxRouteSize = count($d['route']);
        }
    }
    
    while (true) {
        // Kirim data untuk setiap device
        foreach ($devices as $index => $device) {
            $deviceId = $device['id'];
            $route = $device['route'];
            $routeSize = count($route);
            
            // Ambil titik rute (gunakan indeks yang sama untuk semua)
            $pointIndex = $routeIndex % $routeSize;
            $point = $route[$pointIndex];
            
            $data = generateGPSData($deviceId, $point['lat'], $point['lng'], $point['speed']);
            publishGPSData($client, $deviceId, $data);
        }
        
        $counter++;
        if ($counter % 5 == 0) {
            foreach ($devices as $device) {
                publishStatus($client, $device['id'], 'online');
            }
        }
        
        $routeIndex++;
        if ($routeIndex >= $maxRouteSize) {
            $routeIndex = 0;
            echo "\n🔄 All routes loop restart!\n\n";
        }
        
        $client->loopOnce(1);
        sleep(2);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}