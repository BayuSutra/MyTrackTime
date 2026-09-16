@extends('layouts.app')

@section('title', 'Reports - GPS Tracking')

@section('header', 'Tracking Reports')

@section('content')
<div class="fade-in">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filter Reports</h6>
        </div>
        <div class="card-body">
            <form id="reportForm">
                <div class="row">
                    <div class="col-md-3">
                        <label>Device</label>
                        <select name="device_id" id="device_id" class="form-control">
                            <option value="">All Devices</option>
                            @foreach($devices ?? [] as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->device_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ now()->subDays(7)->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="bi bi-search"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm mt-4" id="reportResult" style="display: none;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-file-text"></i> Report Result</h6>
            <button class="btn btn-sm btn-success" id="exportExcel">
                <i class="bi bi-file-excel"></i> Export Excel
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3" id="summaryStats"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="reportTable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Device</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Speed</th>
                            <th>Battery</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let dataTable;
    
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
    
    $('#exportExcel').on('click', function() {
        exportToExcel();
    });
});

function generateReport() {
    showLoading();
    
    $.ajax({
        url: '{{ route("reports.generate") }}',
        method: 'POST',
        data: {
            device_id: $('#device_id').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            hideLoading();
            displayReport(response);
        },
        error: function() {
            hideLoading();
            showAlert('error', 'Failed to generate report');
        }
    });
}

function displayReport(data) {
    $('#reportResult').show();
    
    // Display summary stats
    let statsHtml = `
        <div class="col-md-3">
            <div class="alert alert-info">
                <small>Total Records</small>
                <h4>${data.total_records}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="alert alert-success">
                <small>Unique Devices</small>
                <h4>${data.unique_devices}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="alert alert-warning">
                <small>Avg Speed</small>
                <h4>${data.avg_speed} km/h</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="alert alert-danger">
                <small>Date Range</small>
                <h4>${data.date_range}</h4>
            </div>
        </div>
    `;
    $('#summaryStats').html(statsHtml);
    
    // Display table
    let tableHtml = '';
    data.data.forEach(item => {
        tableHtml += `
            <tr>
                <td>${item.tracking_time}</td>
                <td>${item.device_name}</td>
                <td>${item.latitude}</td>
                <td>${item.longitude}</td>
                <td>${item.speed || '-'} km/h</td>
                <td>${item.battery || '-'}%</td>
            </tr>
        `;
    });
    $('#reportBody').html(tableHtml);
    
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#reportTable')) {
        $('#reportTable').DataTable().destroy();
    }
    
    $('#reportTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
}

function exportToExcel() {
    const table = $('#reportTable').DataTable();
    const data = table.rows().data().toArray();
    
    let csv = [];
    csv.push(['Time', 'Device', 'Latitude', 'Longitude', 'Speed', 'Battery'].join(','));
    
    data.forEach(row => {
        csv.push(row);
    });
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `gps_report_${new Date().toISOString()}.csv`;
    a.click();
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
@endpush
@endsection