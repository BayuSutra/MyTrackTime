@extends('layouts.app')

@section('title', 'Edit Item - GPS Tracking')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pencil"></i> Edit Item</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('items.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $item->name) }}" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icon</label>
                            <select name="icon" class="form-control @error('icon') is-invalid @enderror">
                                <option value="bi-box" {{ $item->icon == 'bi-box' ? 'selected' : '' }}>📦 Box</option>
                                <option value="bi-car-front" {{ $item->icon == 'bi-car-front' ? 'selected' : '' }}>🚗 Car</option>
                                <option value="bi-bicycle" {{ $item->icon == 'bi-bicycle' ? 'selected' : '' }}>🚲 Bike</option>
                                <option value="bi-truck" {{ $item->icon == 'bi-truck' ? 'selected' : '' }}>🚚 Truck</option>
                                <option value="bi-laptop" {{ $item->icon == 'bi-laptop' ? 'selected' : '' }}>💻 Laptop</option>
                                <option value="bi-phone" {{ $item->icon == 'bi-phone' ? 'selected' : '' }}>📱 Phone</option>
                                <option value="bi-watch" {{ $item->icon == 'bi-watch' ? 'selected' : '' }}>⌚ Watch</option>
                                <option value="bi-suitcase" {{ $item->icon == 'bi-suitcase' ? 'selected' : '' }}>🧳 Suitcase</option>
                                <option value="bi-house" {{ $item->icon == 'bi-house' ? 'selected' : '' }}>🏠 House</option>
                                <option value="bi-building" {{ $item->icon == 'bi-building' ? 'selected' : '' }}>🏢 Building</option>
                                <option value="bi-geo-alt" {{ $item->icon == 'bi-geo-alt' ? 'selected' : '' }}>📍 Location</option>
                                <option value="bi-satellite" {{ $item->icon == 'bi-satellite' ? 'selected' : '' }}>🛰️ Satellite</option>
                            </select>
                            @error('icon')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icon Color</label>
                            <div class="input-group">
                                <input type="color" name="icon_color" class="form-control form-control-color" 
                                       value="{{ old('icon_color', $item->icon_color) }}" style="padding: 3px; width: 60px;">
                                <input type="text" name="icon_color_text" class="form-control" 
                                       value="{{ old('icon_color', $item->icon_color) }}" readonly style="flex: 1;">
                            </div>
                            @error('icon_color')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">
                            <i class="bi bi-save"></i> Update Item
                        </button>
                        <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('input[name="icon_color"]').addEventListener('input', function() {
    document.querySelector('input[name="icon_color_text"]').value = this.value;
});
</script>
@endpush
@endsection