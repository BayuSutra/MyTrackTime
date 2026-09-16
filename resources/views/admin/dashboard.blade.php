@extends('layouts.app')

@section('title', 'Admin Dashboard - Verifikasi Pembayaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="bi bi-shield-lock"></i> Admin Dashboard</h4>
    <div>
        <span class="badge bg-primary">Administrator</span>
        <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Online</span>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #f6c23e;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Menunggu Verifikasi</div>
                    <div class="stat-value">{{ $stats['pending_count'] ?? 0 }}</div>
                    <small class="text-muted">Perlu dicek</small>
                </div>
                <div class="stat-icon"><i class="bi bi-clock-history" style="color: #f6c23e;"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #1cc88a;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Diverifikasi Hari Ini</div>
                    <div class="stat-value">{{ $stats['verified_today'] ?? 0 }}</div>
                    <small class="text-muted">Berhasil</small>
                </div>
                <div class="stat-icon"><i class="bi bi-check-circle" style="color: #1cc88a;"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #e74a3b;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Ditolak Hari Ini</div>
                    <div class="stat-value">{{ $stats['rejected_today'] ?? 0 }}</div>
                    <small class="text-muted">Ditolak</small>
                </div>
                <div class="stat-icon"><i class="bi bi-x-circle" style="color: #e74a3b;"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #4e73df;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Total Transaksi Hari Ini</div>
                    <div class="stat-value">{{ $stats['total_today'] ?? 0 }}</div>
                    <small class="text-muted">Semua transaksi</small>
                </div>
                <div class="stat-icon"><i class="bi bi-credit-card" style="color: #4e73df;"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Payments List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Daftar Pembayaran Menunggu Verifikasi</h6>
        <span class="badge bg-warning">{{ $pendingTransactions->count() ?? 0 }} Pending</span>
    </div>
    <div class="card-body">
        @if($pendingTransactions->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>User</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Metode</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingTransactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <small>{{ $transaction->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <strong>{{ $transaction->user->name }}</strong>
                            <br><small class="text-muted">{{ $transaction->user->email }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="item-icon" style="background: {{ $transaction->item->icon_color }}; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; color: white;">
                                    <i class="{{ $transaction->item->icon }}"></i>
                                </div>
                                <div class="ms-2">
                                    <strong>{{ $transaction->item->name }}</strong>
                                    <br><small class="text-muted">{{ $transaction->item->item_code }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($transaction->payment_method) }}</span>
                            <br><small>{{ $transaction->payment_channel }}</small>
                        </td>
                        <td>
                            @if($transaction->payment_receipt)
                                <a href="{{ asset('storage/' . $transaction->payment_receipt) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.payments.verify', $transaction->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" 
                                            onclick="return confirm('Yakin verifikasi pembayaran ini? Item akan aktif.')">
                                        <i class="bi bi-check-circle"></i> Verifikasi
                                    </button>
                                </form>
                                <form action="{{ route('admin.payments.reject', $transaction->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Yakin tolak pembayaran ini?')">
                                        <i class="bi bi-x-circle"></i> Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 64px; color: #1cc88a;"></i>
            <h5 class="mt-3">Semua Pembayaran Sudah Diverifikasi</h5>
            <p class="text-muted">Tidak ada pembayaran yang menunggu verifikasi saat ini.</p>
            <div class="mt-3">
                <span class="badge bg-success p-2">✅ Sistem dalam keadaan baik</span>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Recent Verified Payments -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-check-circle"></i> Riwayat Verifikasi Terakhir</h6>
    </div>
    <div class="card-body">
        @php
            $recentVerified = App\Models\Transaction::where('status', 'paid')
                ->with(['user', 'item'])
                ->latest('payment_verified_at')
                ->limit(10)
                ->get();
        @endphp
        
        @if($recentVerified->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentVerified as $transaction)
                    <tr>
                        <td>
                            <small>{{ $transaction->payment_verified_at ? $transaction->payment_verified_at->format('d/m/Y H:i') : '-' }}</small>
                        </td>
                        <td>{{ $transaction->user->name }}</td>
                        <td>{{ $transaction->item->name }}</td>
                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Verified
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-3">Belum ada riwayat verifikasi</p>
        @endif
    </div>
</div>

<!-- Quick Info -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h6>Total Items Aktif</h6>
                        <h3 class="text-success">{{ App\Models\Item::where('status', 'active')->count() }}</h3>
                    </div>
                    <div class="col-md-4">
                        <h6>Total Users</h6>
                        <h3 class="text-primary">{{ App\Models\User::count() }}</h3>
                    </div>
                    <div class="col-md-4">
                        <h6>Total Tracking Data</h6>
                        <h3 class="text-info">{{ App\Models\GPSTracking::count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection