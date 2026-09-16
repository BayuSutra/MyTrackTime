@extends('layouts.app')

@section('title', 'Pending Payments - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="bi bi-clock-history"></i> Pending Payments</h4>
    <span class="badge bg-warning">{{ $transactions->count() }} pending</span>
</div>

<div class="card">
    <div class="card-body">
        @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>User</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <code>{{ $transaction->transaction_code }}</code>
                            <br><small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                        </td>
                        <td>{{ $transaction->user->name }}</td>
                        <td>{{ $transaction->item->name }}</td>
                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                        <td>
                            {{ ucfirst($transaction->payment_method) }}
                            <br><small class="text-muted">{{ $transaction->payment_channel }}</small>
                        </td>
                        <td>
                            @if($transaction->payment_receipt)
                                <a href="{{ asset('storage/' . $transaction->payment_receipt) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.payments.verify', $transaction->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle"></i> Verifikasi
                                </button>
                            </form>
                            <form action="{{ route('admin.payments.reject', $transaction->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin tolak pembayaran ini?')">
                                    <i class="bi bi-x-circle"></i> Tolak
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-check-circle" style="font-size: 48px; color: #4caf50;"></i>
            <h5 class="mt-3">Tidak Ada Pembayaran Tertunda</h5>
            <p class="text-muted">Semua pembayaran sudah diproses.</p>
        </div>
        @endif
    </div>
</div>
@endsection