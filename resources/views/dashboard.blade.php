@extends('layouts.app')

@section('header', 'Dashboard Overview')
@section('subheader', 'Pantau perangkat Anda secara real-time')

@section('content')
<!-- Stats -->
<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="number">{{ $stats['total_items'] ?? 0 }}</div>
                    <div class="label">Total Devices</div>
                </div>
                <div class="icon"><i class="bi bi-device-hdd"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="number">{{ $stats['online_items'] ?? 0 }}</div>
                    <div class="label">Online Now</div>
                </div>
                <div class="icon"><i class="bi bi-wifi"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="number">{{ number_format($stats['total_tracking'] ?? 0) }}</div>
                    <div class="label">Total Tracking</div>
                </div>
                <div class="icon"><i class="bi bi-geo-alt"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="number">{{ $stats['pending_payment'] ?? 0 }}</div>
                    <div class="label">Pending Payment</div>
                </div>
                <div class="icon"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Map & Control Panel -->
<div class="row g-3">
    <!-- Map -->
    <div class="col-lg-8">
        <div class="stat-card p-0 overflow-hidden">
            <div id="map"></div>
        </div>
    </div>
    
    <!-- Control Panel -->
    <div class="col-lg-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold" style="font-size: 14px;">
                    <i class="bi bi-joystick me-2" style="color: var(--primary);"></i>
                    Device Control
                </h6>
                <span class="badge bg-success" id="mqttStatus">
                    <i class="bi bi-check-circle"></i> Connected
                </span>
            </div>
            
            <!-- Pilih Device dengan Gambar -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 13px;">Select Device</label>
                <select class="form-select form-select-sm" id="selectedDevice" onchange="updateDeviceInfo()">
                    <option value="">-- Pilih Device --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->device_id }}" 
                                data-name="{{ $item->name }}" 
                                data-id="{{ $item->id }}"
                                data-icon="{{ $item->icon }}"
                                data-icon-color="{{ $item->icon_color }}"
                                data-photo="{{ $item->mainPhoto->photo_path ?? '' }}">
                            {{ $item->name }} ({{ $item->device_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Device Info dengan Gambar -->
            <div id="deviceInfo" class="mb-3 p-3 bg-light rounded" style="display: none;">
                <div class="d-flex align-items-center gap-3">
                    <!-- Foto Item -->
                    <div id="devicePhoto" class="flex-shrink-0" style="width: 64px; height: 64px; border-radius: 12px; overflow: hidden; background: #e5e7eb; display: flex; align-items: center; justify-content: center; border: 2px solid #e5e7eb;">
                        <img id="devicePhotoImg" src="" alt="Device Photo" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <i id="devicePhotoIcon" class="bi bi-box" style="font-size: 28px; color: #9ca3af;"></i>
                    </div>
                    
                    <!-- Info Device -->
                    <div class="flex-grow-1">
                        <div class="fw-semibold" id="deviceName">-</div>
                        <div style="font-size: 12px; color: #6b7280;">
                            <span id="deviceIdDisplay">-</span>
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-secondary" id="deviceStatus">Unknown</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- Control Buttons - Hanya Buzzer -->
            <div class="row g-2">
                <!-- Buzzer Control -->
                <div class="col-12">
                    <div class="card bg-light border-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-megaphone fs-5 me-2" style="color: #f59e0b;"></i>
                                <span class="fw-semibold">Buzzer Control</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-success" onclick="sendCommand('buzzer_on')">
                                    <i class="bi bi-play-fill"></i> ON
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="sendCommand('buzzer_off')">
                                    <i class="bi bi-stop-fill"></i> OFF
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="sendCommand('buzzer_beep')">
                                    <i class="bi bi-clock"></i> BEEP
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- Command Log -->
            <div class="mt-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted fw-semibold">Command Log</small>
                    <button class="btn btn-sm btn-outline-secondary" onclick="clearLog()">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <div id="commandLog" class="mt-1" style="max-height: 120px; overflow-y: auto; font-size: 12px; background: #f8fafc; border-radius: 6px; padding: 6px;">
                    <div class="text-muted text-center">Waiting for commands...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Trackings -->
@if(isset($recentTrackings) && $recentTrackings->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold" style="font-size: 14px;">
                    <i class="bi bi-clock-history me-2" style="color: var(--primary);"></i>
                    Recent Tracking Activity
                </h6>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2" style="border-radius: 20px; font-weight: 500; font-size: 11px;">
                    {{ $recentTrackings->count() }} records
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size: 13px; margin-bottom: 0;">
                    <thead>
                        <tr style="color: var(--text-secondary); font-weight: 500; border-color: var(--border-color);">
                            <th>Device</th>
                            <th>Location</th>
                            <th>Speed</th>
                            <th>Battery</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTrackings as $tracking)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $tracking->icon_color ?? '#4e73df' }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                        <i class="{{ $tracking->icon ?? 'bi-box' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 13px;">{{ $tracking->item_name ?? $tracking->device_id }}</div>
                                        <div style="font-size: 10px; color: var(--text-secondary);">{{ $tracking->device_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="font-size: 11px; background: var(--border-color); padding: 2px 8px; border-radius: 4px;">
                                    {{ number_format($tracking->latitude, 4) }}, {{ number_format($tracking->longitude, 4) }}
                                </code>
                            </td>
                            <td>
                                <span class="fw-semibold" style="color: var(--primary);">
                                    {{ $tracking->speed ?? 0 }} km/h
                                </span>
                            </td>
                            <td>
                                @php $batt = $tracking->battery ?? 0; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 60px; height: 4px; background: var(--border-color); border-radius: 10px;">
                                        <div class="progress-bar" style="width: {{ $batt }}%; background: {{ $batt > 70 ? 'var(--success)' : ($batt > 30 ? 'var(--warning)' : 'var(--danger)') }}; border-radius: 10px;"></div>
                                    </div>
                                    <span style="font-size: 11px; font-weight: 500; min-width: 28px;">{{ $batt }}%</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px;">
                                    @php
                                        $time = $tracking->tracking_time ?? now();
                                        if ($time instanceof \Carbon\Carbon) {
                                            echo $time->format('H:i:s');
                                        } else {
                                            echo \Carbon\Carbon::parse($time)->format('H:i:s');
                                        }
                                    @endphp
                                </div>
                                <div style="font-size: 10px; color: var(--text-secondary);">
                                    @php
                                        if ($time instanceof \Carbon\Carbon) {
                                            echo $time->diffForHumans();
                                        } else {
                                            echo \Carbon\Carbon::parse($time)->diffForHumans();
                                        }
                                    @endphp
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
// ============================================
//  DEVICE CONTROL
// ============================================
let selectedDevice = null;

function updateDeviceInfo() {
    const select = document.getElementById('selectedDevice');
    const info = document.getElementById('deviceInfo');
    const deviceName = document.getElementById('deviceName');
    const deviceIdDisplay = document.getElementById('deviceIdDisplay');
    const deviceStatus = document.getElementById('deviceStatus');
    const photoImg = document.getElementById('devicePhotoImg');
    const photoIcon = document.getElementById('devicePhotoIcon');
    const photoContainer = document.getElementById('devicePhoto');
    
    if (select.value) {
        selectedDevice = select.value;
        const option = select.options[select.selectedIndex];
        deviceName.textContent = option.dataset.name;
        deviceIdDisplay.textContent = select.value;
        deviceStatus.textContent = 'Online';
        deviceStatus.className = 'badge bg-success';
        
        const photoPath = option.dataset.photo;
        const iconClass = option.dataset.icon || 'bi-box';
        const iconColor = option.dataset.iconColor || '#4e73df';
        
        if (photoPath && photoPath !== '') {
            photoImg.src = '/storage/' + photoPath;
            photoImg.style.display = 'block';
            photoIcon.style.display = 'none';
            photoContainer.style.borderColor = iconColor;
        } else {
            photoImg.style.display = 'none';
            photoIcon.className = iconClass;
            photoIcon.style.display = 'block';
            photoIcon.style.color = iconColor;
            photoContainer.style.borderColor = iconColor;
        }
        
        info.style.display = 'block';
    } else {
        selectedDevice = null;
        info.style.display = 'none';
    }
}

function sendCommand(command) {
    if (!selectedDevice) {
        alert('Please select a device first!');
        return;
    }
    
    addLog('⏳', command, 'Sending...');
    
    fetch('/api/v1/mqtt/send-command', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            device_id: selectedDevice,
            command: command
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            addLog('✅', command, 'Sent successfully');
        } else {
            addLog('❌', command, 'Failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Command error:', error);
        addLog('❌', command, 'Error: ' + error.message);
    });
}

function addLog(icon, command, status) {
    const log = document.getElementById('commandLog');
    const time = new Date().toLocaleTimeString();
    
    const placeholder = log.querySelector('.text-muted');
    if (placeholder) {
        log.innerHTML = '';
    }
    
    const entry = document.createElement('div');
    entry.className = 'd-flex justify-content-between align-items-start border-bottom py-1';
    entry.style.fontSize = '12px';
    entry.innerHTML = `
        <span>${icon} <strong>${command}</strong></span>
        <span class="${status.includes('Success') ? 'text-success' : status.includes('Failed') ? 'text-danger' : 'text-muted'}" style="font-size: 11px;">${status}</span>
        <small class="text-muted" style="font-size: 10px;">${time}</small>
    `;
    log.prepend(entry);
    
    while (log.children.length > 20) {
        log.removeChild(log.lastChild);
    }
}

function clearLog() {
    const log = document.getElementById('commandLog');
    log.innerHTML = '<div class="text-muted text-center py-2" style="font-size: 12px;">Waiting for commands...</div>';
}

// Auto refresh device status every 10 seconds
setInterval(() => {
    if (selectedDevice) {
        fetch(`/api/v1/mqtt/device/${selectedDevice}/status`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            const status = document.getElementById('deviceStatus');
            if (data.status === 'online') {
                status.textContent = 'Online';
                status.className = 'badge bg-success';
            } else if (data.status === 'offline') {
                status.textContent = 'Offline';
                status.className = 'badge bg-danger';
            }
        })
        .catch(() => {});
    }
}, 10000);
</script>

<!-- ============================================
     MAP INITIALIZATION
     ============================================ -->
<script>
let map;
let markers = {};

function initMap() {
    map = L.map('map').setView([-2.548926, 118.014863], 5);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);
    
    @if(isset($items) && $items->count() > 0)
        @foreach($items as $item)
            @if($item->latestTracking)
                (function() {
                    const lat = {{ $item->latestTracking->latitude }};
                    const lng = {{ $item->latestTracking->longitude }};
                    
                    const icon = L.divIcon({
                        html: `<div style="background: {{ $item->icon_color }}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"></div>`,
                        iconSize: [12, 12],
                        className: 'custom-marker'
                    });
                    
                    const marker = L.marker([lat, lng], { icon })
                        .addTo(map)
                        .bindPopup(`
                            <div style="padding: 4px 2px;">
                                <strong>{{ $item->name }}</strong>
                                <br><small style="color: #94a3b8;">{{ $item->device_id }}</small>
                                <hr style="margin: 6px 0;">
                                <small>📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                                <br><small>⚡ Speed: {{ $item->latestTracking->speed ?? 0 }} km/h</small>
                                <br><small>🔋 Battery: {{ $item->latestTracking->battery ?? 0 }}%</small>
                            </div>
                        `);
                    
                    markers['{{ $item->id }}'] = marker;
                })();
            @endif
        @endforeach
        
        const markerKeys = Object.keys(markers);
        if (markerKeys.length > 0) {
            const group = L.featureGroup(Object.values(markers));
            map.fitBounds(group.getBounds().pad(0.2));
        }
    @endif
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initMap, 300);
});

window.addEventListener('resize', function() {
    if (window.map) setTimeout(() => window.map.invalidateSize(), 300);
});
</script>
@endpush
@endsection