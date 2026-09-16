<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\GPSData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GPSApiController extends Controller
{
    // Endpoint untuk ESP32 mengirim data GPS
    public function updateLocation(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'battery' => 'nullable|integer|min:0|max:100',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Cari device
        $device = Device::where('device_id', $request->device_id)->first();
        
        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }
        
        // Simpan data GPS
        try {
            $gpsData = GPSData::create([
                'device_id' => $device->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'speed' => $request->speed,
                'battery' => $request->battery,
                'altitude' => $request->altitude,
                'accuracy' => $request->accuracy,
                'tracking_time' => now(),
            ]);
            
            // Update status device menjadi active
            if ($device->status != 'active') {
                $device->update(['status' => 'active']);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Location updated successfully',
                'data' => [
                    'id' => $gpsData->id,
                    'tracking_time' => $gpsData->tracking_time
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Endpoint untuk mendapatkan data device
    public function getDeviceInfo($device_id)
    {
        $device = Device::where('device_id', $device_id)->first();
        
        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }
        
        $latestLocation = $device->latestLocation;
        
        return response()->json([
            'status' => 'success',
            'device' => [
                'id' => $device->device_id,
                'name' => $device->name,
                'status' => $device->status,
                'registered_at' => $device->created_at
            ],
            'latest_location' => $latestLocation ? [
                'latitude' => $latestLocation->latitude,
                'longitude' => $latestLocation->longitude,
                'speed' => $latestLocation->speed,
                'battery' => $latestLocation->battery,
                'tracking_time' => $latestLocation->tracking_time
            ] : null
        ]);
    }
    
    // Endpoint untuk multiple devices
    public function getAllDevices()
    {
        $devices = Device::with('latestLocation')->get();
        
        return response()->json([
            'status' => 'success',
            'total' => $devices->count(),
            'devices' => $devices->map(function($device) {
                return [
                    'id' => $device->device_id,
                    'name' => $device->name,
                    'status' => $device->status,
                    'last_location' => $device->latestLocation ? [
                        'lat' => $device->latestLocation->latitude,
                        'lng' => $device->latestLocation->longitude,
                        'time' => $device->latestLocation->tracking_time
                    ] : null
                ];
            })
        ]);
    }
}