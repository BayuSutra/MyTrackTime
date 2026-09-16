@extends('layouts.app')

@section('title', 'Items - GPS Tracking')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="bi bi-boxes"></i> My Items</h4>
    <a href="{{ route('items.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-circle"></i> Add New Item
    </a>
</div>

<div class="row">
    @forelse($items as $item)
    <div class="col-md-6 col-lg-4">
        <div class="card item-card" onclick="window.location.href='{{ route('items.show', $item->id) }}'">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="item-icon" style="background: {{ $item->icon_color }}; width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; color: white;">
                        <i class="{{ $item->icon }}"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="fw-semibold mb-1">{{ $item->name }}</h6>
                        <small class="text-muted">{{ $item->item_code }}</small>
                        <br>
                        <span class="badge badge-status 
                            @if($item->canMonitor()) bg-success
                            @elseif($item->payment_status === 'pending') bg-warning text-dark
                            @else bg-secondary
                            @endif">
                            @if($item->canMonitor())
                                <i class="bi bi-check-circle"></i> Active
                            @elseif($item->payment_status === 'pending')
                                <i class="bi bi-clock"></i> Pending Payment
                            @else
                                <i class="bi bi-x-circle"></i> Inactive
                            @endif
                        </span>
                    </div>
                </div>
                
                @if($item->latestTracking)
                <div class="mt-3 pt-3 border-top">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Location</small>
                            <small class="fw-semibold">
                                {{ number_format($item->latestTracking->latitude, 4) }},
                                {{ number_format($item->latestTracking->longitude, 4) }}
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Last Update</small>
                            <small class="fw-semibold">{{ $item->latestTracking->tracking_time->diffForHumans() }}</small>
                        </div>
                        @if($item->latestTracking->speed)
                        <div class="col-6">
                            <small class="text-muted d-block">Speed</small>
                            <small><i class="bi bi-speedometer2"></i> {{ $item->latestTracking->speed }} km/h</small>
                        </div>
                        @endif
                        @if($item->latestTracking->battery)
                        <div class="col-6">
                            <small class="text-muted d-block">Battery</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-{{ $item->latestTracking->battery > 70 ? 'success' : ($item->latestTracking->battery > 30 ? 'warning' : 'danger') }}" 
                                     style="width: {{ $item->latestTracking->battery }}%"></div>
                            </div>
                            <small>{{ $item->latestTracking->battery }}%</small>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="mt-3 text-center text-muted">
                    <small><i class="bi bi-hourglass-split"></i> Waiting for data...</small>
                </div>
                @endif
                
                <div class="mt-3 d-flex gap-2">
                <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline" 
                    onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this item?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-boxes" style="font-size: 64px; color: #ddd;"></i>
                <h5 class="mt-3">No Items Yet</h5>
                <p class="text-muted">Start tracking your items by adding one.</p>
                <a href="{{ route('items.create') }}" class="btn btn-primary-custom">
                    <i class="bi bi-plus-circle"></i> Add Item
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

@push('scripts')
<script>
function deleteItem(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) {
        return;
    }
    
    // Gunakan fetch atau axios, bukan $.ajax
    fetch(`/items/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect ke halaman items
            window.location.href = '{{ route('items.index') }}';
        } else {
            alert('Gagal menghapus: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus item');
    });
}
</script>
@endpush
@endsection