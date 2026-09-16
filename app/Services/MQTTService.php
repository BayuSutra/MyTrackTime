<?php

namespace App\Services;

use App\Models\GPSTracking;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MQTTService
{
    use ConsoleLogger;

    protected ?MqttClient $client = null;
    protected bool $isConnected = false;
    protected int $reconnectAttempts = 0;
    protected int $maxReconnectAttempts = 10;
    protected bool $shouldStop = false;

    protected array $topics = [
        'gps_data'     => 'gps/data/+/location',
        'gps_status'   => 'gps/data/+/status',
        'gps_response' => 'gps/response/#',
    ];

    public function __construct()
    {
        // Default: console mode aktif untuk listener
        $this->setConsoleMode(true);
    }

    public function connect(): bool
    {
        try {
            $host = config('mqtt.connections.default.host');
            $port = (int) config('mqtt.connections.default.port');
            $username = config('mqtt.connections.default.username');
            $password = config('mqtt.connections.default.password');
            $clientId = config(
                'mqtt.connections.default.client_id',
                'laravel_gps_' . uniqid()
            );

            $this->client = new MqttClient($host, $port, $clientId);

            $settings = (new ConnectionSettings())
                ->setUsername($username)
                ->setPassword($password)
                ->setUseTls(true)
                ->setTlsVerifyPeer(false)
                ->setTlsVerifyPeerName(false)
                ->setTlsSelfSignedAllowed(true)
                ->setConnectTimeout(30);

            $this->client->connect($settings);
            $this->isConnected = true;
            $this->reconnectAttempts = 0;

            Log::info('MQTT Connected to EMQX Broker');
            $this->logSuccess('MQTT Connected');
            return true;

        } catch (\Exception $e) {
            $this->isConnected = false;
            $this->client = null;
            Log::error('MQTT Connection Error: ' . $e->getMessage());
            $this->logError('Connection failed: ' . $e->getMessage());
            return false;
        }
    }

    public function ensureConnected(): bool
    {
        if ($this->isConnected && $this->client !== null) {
            return true;
        }

        if ($this->reconnectAttempts >= $this->maxReconnectAttempts) {
            Log::error('Max reconnect attempts reached');
            $this->logError('Max reconnect attempts reached');
            return false;
        }

        $this->reconnectAttempts++;
        $this->log("🔄 Reconnect attempt {$this->reconnectAttempts}/{$this->maxReconnectAttempts}");
        return $this->connect();
    }

    public function listen(): void
    {
        $this->shouldStop = false;
        $this->setConsoleMode(true);
        $host = config('mqtt.connections.default.host');
        $port = (int) config('mqtt.connections.default.port');

        echo "========================================\n";
        echo "MQTT LISTENER STARTED\n";
        echo "========================================\n";
        echo "Broker: {$host}:{$port}\n";
        echo "========================================\n";

        while (!$this->shouldStop) {
            try {
                echo "Connecting...\n";
                
                if (!$this->connect()) {
                    echo "Retry in 10 seconds...\n";
                    sleep(10);
                    continue;
                }

                echo "Listening for GPS data...\n";

                $this->client->subscribe(
                    $this->topics['gps_data'],
                    function (string $topic, string $message) {
                        $this->handleLocationData($topic, $message);
                    },
                    0
                );

                $this->client->subscribe(
                    $this->topics['gps_status'],
                    function (string $topic, string $message) {
                        $this->handleStatusData($topic, $message);
                    },
                    0
                );

                $this->client->subscribe(
                    $this->topics['gps_response'],
                    function (string $topic, string $message) {
                        echo "Response: {$topic} -> {$message}\n";
                    },
                    0
                );

                while (!$this->shouldStop) {
                    try {
                        $this->client->loopOnce(1);
                    } catch (\Exception $e) {
                        Log::warning('MQTT loop error: ' . $e->getMessage());
                        echo "⚠️ Loop error: " . $e->getMessage() . "\n";
                        break;
                    }
                }

                $this->isConnected = false;
                $this->client = null;
                echo "🔄 Connection lost, reconnecting...\n";

            } catch (\Exception $e) {
                $this->isConnected = false;
                $this->client = null;
                Log::error('MQTT Error: ' . $e->getMessage());
                echo "❌ Error: " . $e->getMessage() . "\n";
                echo "Retry in 10 seconds...\n";
                sleep(10);
            }
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
        if ($this->client) {
            try {
                $this->client->disconnect();
            } catch (\Exception $e) {
                // Ignore
            }
        }
        echo "🛑 MQTT Listener stopped\n";
    }

    public function sendCommand($deviceId, $command): bool
    {
        try {
            // Matikan console mode agar tidak mengganggu response API
            $this->setConsoleMode(false);

            if (!$this->ensureConnected()) {
                Log::warning("Cannot send command: MQTT not connected");
                return false;
            }

            $topic = "gps/command/" . $deviceId . "/control";
            $message = json_encode([
                'command' => $command,
                'timestamp' => now()->toISOString(),
            ]);

            $result = $this->client->publish($topic, $message, 0);

            Log::info("Command sent", [
                'device_id' => $deviceId,
                'command' => $command,
                'topic' => $topic,
                'result' => $result
            ]);

            // Kembalikan console mode untuk listener
            $this->setConsoleMode(true);

            return true;

        } catch (\Exception $e) {
            $this->isConnected = false;
            $this->client = null;
            Log::error("Failed to send command: " . $e->getMessage());
            // Kembalikan console mode untuk listener
            $this->setConsoleMode(true);
            return false;
        }
    }

    protected function handleLocationData(string $topic, string $message): void
    {
        try {
            $data = json_decode($message, true);

            if (!$data) {
                $this->log("⚠️ Invalid JSON: {$message}");
                return;
            }

            $deviceId = $this->extractDeviceId($topic);

            $this->logInfo("GPS {$deviceId}");
            $this->log("   Lat: " . ($data['latitude'] ?? '-'));
            $this->log("   Lng: " . ($data['longitude'] ?? '-'));

            $item = Item::where('device_id', $deviceId)->first();

            if (!$item) {
                $this->logWarning("Device tidak terdaftar: {$deviceId}");
                return;
            }

            $tracking = GPSTracking::create([
                'item_id'       => $item->id,
                'device_id'     => $deviceId,
                'latitude'      => $data['latitude'] ?? null,
                'longitude'     => $data['longitude'] ?? null,
                'speed'         => $data['speed'] ?? null,
                'battery'       => $data['battery'] ?? null,
                'tracking_time' => now(),
            ]);

            $item->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);

            $this->logSuccess("GPS data saved (ID: {$tracking->id})");
            
        } catch (\Exception $e) {
            Log::error('GPS Handler Error', [
                'message' => $e->getMessage(),
            ]);
            $this->logError("Error: " . $e->getMessage());
        }
    }

    protected function handleStatusData(string $topic, string $message): void
    {
        try {
            $data = json_decode($message, true);
            $deviceId = $this->extractDeviceId($topic);

            if (!$data) {
                $status = trim($message);
                $this->logInfo("Status {$deviceId}: {$status}");
                
                $item = Item::where('device_id', $deviceId)->first();
                if ($item) {
                    $finalStatus = $this->mapStatus($status);
                    $item->update([
                        'status' => $finalStatus,
                        'updated_at' => now(),
                    ]);
                    $this->logSuccess("Status updated for {$deviceId} -> {$finalStatus}");
                }
                return;
            }

            $status = $data['status'] ?? 'unknown';
            $this->logInfo("Status {$deviceId}: {$status}");

            $item = Item::where('device_id', $deviceId)->first();
            
            if ($item) {
                $finalStatus = $this->mapStatus($status);
                $item->update([
                    'status' => $finalStatus,
                    'updated_at' => now(),
                ]);
                $this->logSuccess("Status updated for {$deviceId} -> {$finalStatus}");
            } else {
                $this->logWarning("Device tidak terdaftar: {$deviceId}");
            }
            
        } catch (\Exception $e) {
            Log::error('Status Handler Error', [
                'message' => $e->getMessage(),
            ]);
            $this->logError("Error: " . $e->getMessage());
        }
    }

    protected function mapStatus(string $status): string
    {
        $statusMap = [
            'online' => 'active',
            'offline' => 'inactive',
            'searching' => 'inactive',
            'signal_lost' => 'inactive',
            'active' => 'active',
            'inactive' => 'inactive',
            'pending' => 'pending',
            'maintenance' => 'maintenance',
        ];

        $validStatus = ['pending', 'active', 'inactive', 'maintenance'];
        $finalStatus = $statusMap[$status] ?? 'active';
        
        return in_array($finalStatus, $validStatus) ? $finalStatus : 'active';
    }

    protected function extractDeviceId(string $topic): ?string
    {
        $parts = explode('/', $topic);
        return $parts[2] ?? null;
    }

    protected function log(string $message): void
    {
        if ($this->isConsoleMode) {
            echo $message . "\n";
        }
    }

    protected function logSuccess(string $message): void
    {
        $this->log("✅ " . $message);
    }

    protected function logError(string $message): void
    {
        $this->log("❌ " . $message);
    }

    protected function logWarning(string $message): void
    {
        $this->log("⚠️ " . $message);
    }

    protected function logInfo(string $message): void
    {
        $this->log("📩 " . $message);
    }
}