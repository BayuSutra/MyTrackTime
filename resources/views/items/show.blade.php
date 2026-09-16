@extends('layouts.app')

@section('title', $item->name . ' - GPS Tracking')

@section('content')
<div class="row">
    <!-- Item Info -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <!-- Foto Item -->
                <div class="mb-3">
                    @if($mainPhoto)
                        <img src="{{ asset('storage/' . $mainPhoto->photo_path) }}" 
                             alt="{{ $item->name }}" 
                             class="img-fluid rounded" 
                             style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 12px;">
                    @else
                        <div class="item-icon mx-auto" style="background: {{ $item->icon_color }}; width: 100px; height: 100px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 50px; color: white;">
                            <i class="{{ $item->icon }}"></i>
                        </div>
                    @endif
                </div>
                
                <h5 class="fw-bold mt-2">{{ $item->name }}</h5>
                <small class="text-muted">{{ $item->item_code }}</small>
                
                <div class="mt-3">
                    <span class="badge badge-status 
                        @if($item->canMonitor()) bg-success
                        @elseif($item->payment_status === 'pending') bg-warning text-dark
                        @else bg-secondary
                        @endif" style="font-size: 14px; padding: 8px 20px;">
                        @if($item->canMonitor())
                            <i class="bi bi-check-circle"></i> Active
                        @elseif($item->payment_status === 'pending')
                            <i class="bi bi-clock"></i> Pending Payment
                        @else
                            <i class="bi bi-x-circle"></i> Inactive
                        @endif
                    </span>
                </div>
                
                <hr>
                
                <table class="table table-borderless table-sm text-start">
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst($item->status) }}</td>
                    </tr>
                    <tr>
                        <th>Payment</th>
                        <td>{{ ucfirst($item->payment_status) }}</td>
                    </tr>
                    <tr>
                        <th>Device ID</th>
                        <td><code>{{ $item->device_id ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <th>Activated</th>
                        <td>{{ $item->activated_at ? $item->activated_at->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Expired</th>
                        <td>{{ $item->expired_at ? $item->expired_at->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Total Trackings</th>
                        <td>{{ isset($trackings) ? $trackings->count() : 0 }}</td>
                    </tr>
                </table>
                
                <div class="d-flex gap-2">
                    @if($item->payment_status === 'pending')
                        @php $transaction = $item->transactions()->latest()->first(); @endphp
                        <a href="{{ route('transactions.payment', $transaction->id) }}" class="btn btn-warning flex-grow-1">
                            <i class="bi bi-credit-card"></i> Pay Now
                        </a>
                    @endif
                    <a href="{{ route('items.edit', $item->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="deleteItem({{ $item->id }})" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map & History -->
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-map"></i> Location History</h6>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 350px;"></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-table"></i> Tracking History</h6>
                <small class="text-muted">Last 100 records</small>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
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
                            @if(isset($trackings) && $trackings->count() > 0)
                                @foreach($trackings as $tracking)
                                <tr>
                                    <td>
                                        @php
                                            $time = $tracking->tracking_time;
                                            if ($time instanceof \Carbon\Carbon) {
                                                echo $time->format('Y-m-d H:i:s');
                                            } else {
                                                echo \Carbon\Carbon::parse($time)->format('Y-m-d H:i:s');
                                            }
                                        @endphp
                                    </td>
                                    <td>{{ number_format($tracking->latitude, 6) }}</td>
                                    <td>{{ number_format($tracking->longitude, 6) }}</td>
                                    <td>{{ $tracking->speed ?? '-' }} km/h</td>
                                    <td>
                                        @if($tracking->battery)
                                            {{ $tracking->battery }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-hourglass-split" style="font-size: 32px;"></i>
                                        <p class="mt-2">No tracking data yet</p>
                                        <small>Waiting for GPS data from device</small>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let map;
let pathCoordinates = [];

function initMap() {
    const trackings = @json(isset($trackings) ? $trackings : []);
    
    console.log('Trackings data:', trackings);
    
    if (trackings && trackings.length > 0) {
        const last = trackings[0];
        map = L.map('map').setView([last.latitude, last.longitude], 15);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap',
            subdomains: 'abcd'
        }).addTo(map);
        
        // Draw path
        trackings.forEach(t => {
            pathCoordinates.push([t.latitude, t.longitude]);
        });
        
        // Reverse untuk urutan yang benar
        pathCoordinates.reverse();
        
        L.polyline(pathCoordinates, {
            color: '{{ $item->icon_color }}',
            weight: 3,
            opacity: 0.8
        }).addTo(map);
        
        // Start & end markers
        const first = trackings[trackings.length - 1];
        const lastLoc = trackings[0];
        
        L.marker([first.latitude, first.longitude], {
            icon: L.divIcon({
                html: '<i class="bi bi-play-circle-fill" style="font-size: 24px; color: green;"></i>',
                iconSize: [24, 24],
                className: 'custom-marker'
            })
        }).addTo(map).bindPopup('Start Point');
        
        L.marker([lastLoc.latitude, lastLoc.longitude], {
            icon: L.divIcon({
                html: '<div style="background: {{ $item->icon_color }}; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"></div>',
                iconSize: [16, 16],
                className: 'custom-marker'
            })
        }).addTo(map).bindPopup(`
            <strong>Current Location</strong>
            <br>Speed: ${lastLoc.speed || 0} km/h
            <br>Time: ${lastLoc.tracking_time}
        `);
        
        map.fitBounds(L.polyline(pathCoordinates).getBounds().pad(0.1));
    } else {
        // Jika tidak ada data
        map = L.map('map').setView([-6.208763, 106.845599], 13);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap',
            subdomains: 'abcd'
        }).addTo(map);
        
        L.popup()
            .setLatLng([-6.208763, 106.845599])
            .setContent(`
                <div class="text-center p-3">
                    <i class="bi bi-hourglass-split" style="font-size: 32px;"></i>
                    <h6 class="mt-2">No Tracking Data</h6>
                    <small>Waiting for GPS data from device</small>
                    <br><small class="text-muted">Device ID: {{ $item->device_id ?? '-' }}</small>
                </div>
            `)
            .openOn(map);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing map...');
    initMap();
});

function deleteItem(id) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini? Semua data tracking akan terhapus.')) {
        showLoading();
        
        $.ajax({
            url: '/items/' + id,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    window.location.href = '{{ route('items.index') }}';
                } else {
                    alert('Gagal menghapus item: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                alert('Terjadi kesalahan saat menghapus item.');
            }
        });
    }
}
</script>
@endpush
@endsection