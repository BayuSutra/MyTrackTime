@extends('layouts.app')

@section('title', 'Payment - GPS Tracking')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment Details</h6>
            </div>
            <div class="card-body">
                <!-- Item Info -->
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <div class="item-icon" style="background: {{ $transaction->item->icon_color }}; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                            <i class="{{ $transaction->item->icon }}"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 fw-bold">{{ $transaction->item->name }}</h6>
                            <small class="text-muted">{{ $transaction->item->item_code }}</small>
                        </div>
                        <div class="ms-auto text-end">
                            <small class="text-muted">Amount</small>
                            <h5 class="fw-bold text-primary">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <h6 class="fw-bold mb-3"><i class="bi bi-wallet2"></i> Pilih Metode Pembayaran</h6>

                <div class="row g-3 mb-4">
                    @foreach($paymentConfigs as $key => $method)
                    <div class="col-md-4">
                        <div class="payment-method-card p-3 border rounded text-center" 
                             data-method="{{ $method['type'] }}"
                             data-channel="{{ $key }}"
                             data-account="{{ $method['account_number'] }}"
                             data-name="{{ $method['account_name'] }}"
                             onclick="selectPayment('{{ $key }}')"
                             style="cursor: pointer; transition: all 0.3s;">
                            <i class="{{ $method['icon'] }}" style="font-size: 32px; color: #4e73df;"></i>
                            <h6 class="mt-2 mb-0">{{ $method['name'] }}</h6>
                            <small class="text-muted">{{ $method['account_number'] }}</small>
                            <br>
                            <span class="badge bg-light text-dark mt-1">{{ ucfirst($method['type']) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Payment Form -->
                <form action="{{ route('payment.upload', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Metode Pembayaran</label>
                                <input type="text" name="payment_method" id="selected_method" class="form-control" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Channel</label>
                                <input type="text" name="payment_channel" id="selected_channel" class="form-control" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Rekening / E-Wallet Tujuan</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                            <input type="text" name="payment_account" id="selected_account" class="form-control" readonly required>
                        </div>
                        <small class="text-muted">Transfer ke nomor di atas sesuai metode yang dipilih</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Pemilik Rekening</label>
                        <input type="text" name="payment_account_name" id="selected_name" class="form-control" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload Bukti Transfer <span class="text-danger">*</span></label>
                        <input type="file" name="payment_receipt" class="form-control @error('payment_receipt') is-invalid @enderror" 
                               accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                        @error('payment_receipt')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-clock-history"></i>
                        <strong>Waktu Pembayaran:</strong> 
                        @if($transaction->payment_expired_at)
                            {{ $transaction->payment_expired_at->format('d M Y H:i') }}
                            ({{ $transaction->payment_expired_at->diffForHumans() }})
                        @else
                            {{ now()->addHours(24)->format('d M Y H:i') }}
                            (24 jam)
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success-custom flex-grow-1">
                            <i class="bi bi-upload"></i> Upload Bukti Pembayaran
                        </button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Simulasi Payment (Testing) -->
                <div class="text-center">
                    <p class="text-muted">Untuk Testing, gunakan simulasi pembayaran</p>
                    <form action="{{ route('transactions.simulate', $transaction->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-play-circle"></i> Simulasi Pembayaran (Testing)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-method-card:hover {
    border-color: #4e73df !important;
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.2);
    transform: translateY(-3px);
}

.payment-method-card.selected {
    border-color: #4e73df !important;
    background: #f8f9fc;
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
}
</style>

@push('scripts')
<script>
function selectPayment(channel) {
    // Reset semua card
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('selected');
        card.style.borderColor = '#dee2e6';
    });
    
    // Select card
    const card = document.querySelector(`.payment-method-card[data-channel="${channel}"]`);
    card.classList.add('selected');
    card.style.borderColor = '#4e73df';
    
    // Set form values
    document.getElementById('selected_method').value = card.dataset.method;
    document.getElementById('selected_channel').value = channel;
    document.getElementById('selected_account').value = card.dataset.account;
    document.getElementById('selected_name').value = card.dataset.name;
}

// Select default method
document.addEventListener('DOMContentLoaded', function() {
    const firstCard = document.querySelector('.payment-method-card');
    if (firstCard) {
        selectPayment(firstCard.dataset.channel);
    }
});
</script>
@endpush
@endsection