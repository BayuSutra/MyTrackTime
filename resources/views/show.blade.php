@extends('layouts.app')

@section('title', $device->name . ' - GPS Tracking')

@section('header', 'Device Details: ' . $device->name)

@section('content')
<div class="fade-in">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Device Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Device ID:</th>
                            <td><code>{{ $device->device_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $device->name }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge {{ $device->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($device->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Registered:</th>
                            <td>{{ $device->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Total Tracking:</th>
                            <td>{{ number_format($locations->count()) }} records</td>
                        </tr>
                        <tr>
                            <th>Last Active:</th>
                            <td>
                                @if($locations->first())
                                    {{ $locations->first()->tracking_time->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <canvas id="statsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-map"></i> Location History</h6>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 400px;"></div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-table"></i> Location History Data</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Speed</th>
                                    <th>Battery</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $location)
                                <tr>
                                    <td>{{ $location->tracking_time->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ number_format($location->latitude, 6) }}</td>
                                    <td>{{ number_format($location->longitude, 6) }}</td>
                                    <td>{{ $location->speed ?? '-' }} km/h</td>
                                    <td>
                                        @if($location->battery)
                                            {{ $location->battery }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let map;
let pathCoordinates = [];
let markers = [];

function initMap() {
    const locations = @json($locations);
    
    if (locations.length > 0) {
        const center = [locations[0].latitude, locations[0].longitude];
        map = L.map('map').setView(center, 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Draw path
        locations.forEach(loc => {
            pathCoordinates.push([loc.latitude, loc.longitude]);
        });
        
        // Add polyline
        const polyline = L.polyline(pathCoordinates, {
            color: '#4e73df',
            weight: 3,
            opacity: 0.8
        }).addTo(map);
        
        // Fit bounds
        map.fitBounds(polyline.getBounds());
        
        // Add start marker
        const firstLoc = locations[locations.length - 1];
        const startMarker = L.marker([firstLoc.latitude, firstLoc.longitude], {
            icon: L.divIcon({
                html: '<i class="bi bi-play-circle-fill" style="font-size: 24px; color: green;"></i>',
                iconSize: [24, 24],
                className: 'custom-marker'
            })
        }).addTo(map).bindPopup('<strong>Start Point</strong><br>' + firstLoc.tracking_time);
        
        // Add end marker
        const lastLoc = locations[0];
        const endMarker = L.marker([lastLoc.latitude, lastLoc.longitude], {
            icon: L.divIcon({
                html: '<i class="bi bi-geo-alt-fill" style="font-size: 24px; color: red;"></i>',
                iconSize: [24, 24],
                className: 'custom-marker'
            })
        }).addTo(map).bindPopup(`
            <strong>Current Location</strong><br>
            Speed: ${lastLoc.speed || 0} km/h<br>
            Time: ${lastLoc.tracking_time}
        `);
    }
}

// Chart for statistics
const ctx = document.getElementById('statsChart').getContext('2d');
const chartData = @json($chartData);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(item => item.date),
        datasets: [{
            label: 'Tracking Count',
            data: chartData.map(item => item.total),
            backgroundColor: '#4e73df',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// DataTable
$(document).ready(function() {
    $('#historyTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});

document.addEventListener('DOMContentLoaded', initMap);
</script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
@endpush
@endsection