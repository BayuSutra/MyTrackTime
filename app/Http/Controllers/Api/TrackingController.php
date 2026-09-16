<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\GPSTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric',
            'battery' => 'nullable|integer|min:0|max:100',
            'altitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari item berdasarkan device_id
        $item = Item::where('item_code', $request->device_id)
            ->orWhere('device_id', $request->device_id)
            ->first();

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found or not registered'
            ], 404);
        }

        // Cek apakah item aktif
        if (!$item->canMonitor()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item is not active or expired'
            ], 403);
        }

        // Simpan tracking data
        $tracking = GPSTracking::create([
            'item_id' => $item->id,
            'device_id' => $request->device_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'battery' => $request->battery,
            'altitude' => $request->altitude,
            'tracking_time' => now(),
        ]);

        // Update device_id di item jika belum ada
        if (!$item->device_id) {
            $item->update(['device_id' => $request->device_id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated',
            'data' => [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'tracking_id' => $tracking->id,
                'timestamp' => $tracking->tracking_time,
            ]
        ]);
    }

    public function getLocation($itemCode)
    {
        $item = Item::where('item_code', $itemCode)->first();

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found'
            ], 404);
        }

        $latestTracking = $item->latestTracking;

        return response()->json([
            'status' => 'success',
            'item' => [
                'id' => $item->item_code,
                'name' => $item->name,
                'status' => $item->status,
            ],
            'location' => $latestTracking ? [
                'latitude' => $latestTracking->latitude,
                'longitude' => $latestTracking->longitude,
                'speed' => $latestTracking->speed,
                'battery' => $latestTracking->battery,
                'time' => $latestTracking->tracking_time,
            ] : null,
        ]);
    }
}