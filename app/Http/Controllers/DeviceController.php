<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\GPSData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('latestLocation')->orderBy('created_at', 'desc')->get();
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|unique:devices|max:50',
            'name' => 'required|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $device = Device::create($request->all());

        if ($request->ajax()) {
            return response()->json(['success' => true, 'device' => $device]);
        }

        return redirect()->route('devices.index')->with('success', 'Device added successfully');
    }

    public function show($id)
    {
        $device = Device::with('gpsData')->findOrFail($id);
        $locations = $device->gpsData()->latest('tracking_time')->limit(100)->get();

        // Data untuk chart
        $chartData = $device->gpsData()
            ->select(DB::raw('DATE(tracking_time) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return view('devices.show', compact('device', 'locations', 'chartData'));
    }

    public function edit($id)
    {
        $device = Device::findOrFail($id);
        return view('devices.edit', compact('device'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $device = Device::findOrFail($id);
        $device->update($request->only(['name', 'status']));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'device' => $device]);
        }

        return redirect()->route('devices.index')->with('success', 'Device updated successfully');
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return response()->json(['success' => true]);
    }

    public function getLocationHistory($id)
    {
        $device = Device::findOrFail($id);
        $locations = $device->gpsData()
            ->latest('tracking_time')
            ->limit(50)
            ->get();

        return response()->json([
            'device' => $device,
            'locations' => $locations
        ]);
    }

    // Add this method for reports
    public function generateReport(Request $request)
    {
        $query = GPSData::with('device');

        if ($request->device_id) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->start_date) {
            $query->whereDate('tracking_time', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('tracking_time', '<=', $request->end_date);
        }

        $data = $query->orderBy('tracking_time', 'desc')->get();

        return response()->json([
            'total_records' => $data->count(),
            'unique_devices' => $data->pluck('device_id')->unique()->count(),
            'avg_speed' => round($data->avg('speed'), 2),
            'date_range' => $request->start_date . ' to ' . $request->end_date,
            'data' => $data->map(function ($item) {
                return [
                    'tracking_time' => $item->tracking_time->format('Y-m-d H:i:s'),
                    'device_name' => $item->device->name,
                    'latitude' => number_format($item->latitude, 6),
                    'longitude' => number_format($item->longitude, 6),
                    'speed' => $item->speed,
                    'battery' => $item->battery,
                ];
            })
        ]);
    }

    public function reports()
    {
        $devices = Device::all();
        return view('reports', compact('devices'));
    }
}

