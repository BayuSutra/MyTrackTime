@extends('layouts.app')

@section('title', 'Add Item - GPS Tracking')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Item</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required placeholder="Enter item name">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" placeholder="Describe your item">{{ old('description') }}</textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icon</label>
                            <select name="icon" class="form-control @error('icon') is-invalid @enderror">
                                <option value="bi-box">📦 Box</option>
                                <option value="bi-car-front">🚗 Car</option>
                                <option value="bi-bicycle">🚲 Bike</option>
                                <option value="bi-truck">🚚 Truck</option>
                                <option value="bi-laptop">💻 Laptop</option>
                                <option value="bi-phone">📱 Phone</option>
                                <option value="bi-watch">⌚ Watch</option>
                                <option value="bi-suitcase">🧳 Suitcase</option>
                                <option value="bi-house">🏠 House</option>
                                <option value="bi-building">🏢 Building</option>
                                <option value="bi-geo-alt">📍 Location</option>
                                <option value="bi-satellite">🛰️ Satellite</option>
                            </select>
                            @error('icon')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icon Color</label>
                            <div class="input-group">
                                <input type="color" name="icon_color" class="form-control form-control-color" 
                                       value="{{ old('icon_color', '#4e73df') }}" style="padding: 3px; width: 60px;">
                                <input type="text" name="icon_color_text" class="form-control" 
                                       value="{{ old('icon_color', '#4e73df') }}" readonly style="flex: 1;">
                            </div>
                            @error('icon_color')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" 
                               accept="image/*">
                        <small class="text-muted">Max 2MB. JPG, PNG, JPEG</small>
                        @error('photo')
                            <br><small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Payment Required</strong><br>
                        After adding item, you need to complete payment to activate tracking.
                        <br><small>Price: <strong>Rp 50.000</strong> / month</small>
                    </div>
                    
                    <!-- Hidden settings -->
                    <input type="hidden" name="settings" value='{"notification":true,"alert_radius":100}'>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">
                            <i class="bi bi-save"></i> Add Item & Continue to Payment
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
// Auto update color text
document.querySelector('input[name="icon_color"]').addEventListener('input', function() {
    document.querySelector('input[name="icon_color_text"]').value = this.value;
});
</script>
@endpush
@endsection