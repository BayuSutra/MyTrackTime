<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\MQTTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommandController extends Controller
{
    protected $mqttService;

    public function __construct(MQTTService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    public function sendCommand(Request $request)
    {
        try {
            $request->validate([
                'device_id' => 'required|string',
                'command' => 'required|string',
            ]);

            $device = Item::where('device_id', $request->device_id)->first();
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            if (!$device->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device is not active'
                ], 403);
            }

            $result = $this->mqttService->sendCommand(
                $request->device_id,
                $request->command
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Command sent successfully' : 'Failed to send command - MQTT not connected',
                'device_id' => $request->device_id,
                'command' => $request->command,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Send command error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeviceStatus($deviceId)
    {
        try {
            $device = Item::where('device_id', $deviceId)->first();
            
            if (!$device) {
                return response()->json([
                    'status' => 'unknown',
                    'message' => 'Device not found'
                ], 404);
            }

            $hasRecentData = $device->trackings()
                ->where('tracking_time', '>=', now()->subMinutes(5))
                ->exists();

            return response()->json([
                'status' => $hasRecentData ? 'online' : 'offline',
                'device_id' => $deviceId,
                'device_name' => $device->name,
                'last_seen' => $device->trackings()
                    ->latest('tracking_time')
                    ->first()
                    ?->tracking_time,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}