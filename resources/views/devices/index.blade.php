@extends('layouts.app')

@section('title', 'Devices - GPS Tracking')

@section('header', 'Device Management')

@section('content')
<div class="fade-in">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-device-hdd"></i> All Devices</h6>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                <i class="bi bi-plus-circle"></i> Add New Device
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="devicesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Device ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Last Location</th>
                            <th>Last Update</th>
                            <th>Battery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td>{{ $device->id }}</td>
                            <td><code>{{ $device->device_id }}</code></td>
                            <td>{{ $device->name }}</td>
                            <td>
                                <span class="badge {{ $device->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="bi {{ $device->status == 'active' ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                    {{ ucfirst($device->status) }}
                                </span>
                            </td>
                            <td>
                                @if($device->latestLocation)
                                    <small>
                                        {{ number_format($device->latestLocation->latitude, 6) }},
                                        {{ number_format($device->latestLocation->longitude, 6) }}
                                    </small>
                                @else
                                    <span class="text-muted">No data</span>
                                @endif
                            </td>
                            <td>
                                @if($device->latestLocation)
                                    <small>{{ $device->latestLocation->tracking_time->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($device->latestLocation && $device->latestLocation->battery)
                                    <div class="progress" style="height: 25px; width: 80px;">
                                        <div class="progress-bar bg-{{ $device->latestLocation->battery > 50 ? 'success' : ($device->latestLocation->battery > 20 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $device->latestLocation->battery }}%">
                                            {{ $device->latestLocation->battery }}%
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('devices.show', $device->id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-warning" onclick="editDevice({{ $device->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteDevice({{ $device->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Device -->
<div class="modal fade" id="addDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addDeviceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Device ID *</label>
                        <input type="text" name="device_id" class="form-control" required placeholder="ESP32_001">
                        <small class="text-muted">Unique identifier for the device</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Device Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="GPS Tracker 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Device -->
<div class="modal fade" id="editDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDeviceForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Device Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#devicesTable').DataTable({
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});

// Add Device
$('#addDeviceForm').on('submit', function(e) {
    e.preventDefault();
    showLoading();
    
    $.ajax({
        url: '{{ route("devices.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            hideLoading();
            $('#addDeviceModal').modal('hide');
            showAlert('success', 'Device added successfully');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = Object.values(errors).flat().join('\n');
                showAlert('error', errorMessage);
            } else {
                showAlert('error', 'Failed to add device');
            }
        }
    });
});

// Edit Device
window.editDevice = function(id) {
    showLoading();
    $.ajax({
        url: `/devices/${id}/edit`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            $('#edit_id').val(response.id);
            $('#edit_name').val(response.name);
            $('#edit_status').val(response.status);
            $('#editDeviceModal').modal('show');
        },
        error: function() {
            hideLoading();
            showAlert('error', 'Failed to load device data');
        }
    });
}

// Update Device
$('#editDeviceForm').on('submit', function(e) {
    e.preventDefault();
    showLoading();
    
    let id = $('#edit_id').val();
    
    $.ajax({
        url: `/devices/${id}`,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            hideLoading();
            $('#editDeviceModal').modal('hide');
            showAlert('success', 'Device updated successfully');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            hideLoading();
            showAlert('error', 'Failed to update device');
        }
    });
});

// Delete Device
window.deleteDevice = function(id) {
    if (confirm('Are you sure you want to delete this device? All tracking data will be removed.')) {
        showLoading();
        $.ajax({
            url: `/devices/${id}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                hideLoading();
                showAlert('success', 'Device deleted successfully');
                setTimeout(() => location.reload(), 1500);
            },
            error: function() {
                hideLoading();
                showAlert('error', 'Failed to delete device');
            }
        });
    }
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.content').prepend(alertHtml);
    setTimeout(() => $('.alert').fadeOut(), 3000);
}
</script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
@endpush
@endsection