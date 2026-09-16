@extends('layouts.app')

@section('title', 'Transactions - GPS Tracking')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="bi bi-credit-card"></i> Transactions</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                    <tr>
                        <td><code>{{ $transaction->transaction_code }}</code></td>
                        <td>{{ $transaction->item->name }}</td>
                        <td>{{ ucfirst($transaction->type) }}</td>
                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($transaction->payment_method)
                                {{ ucfirst($transaction->payment_method) }}
                                <br><small class="text-muted">{{ $transaction->payment_channel }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if($transaction->status === 'paid') bg-success
                                @elseif($transaction->status === 'pending') bg-warning text-dark
                                @elseif($transaction->status === 'expired') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $transaction->created_at->format('d M Y H:i') }}</small>
                        </td>
                        <td>
                            @if($transaction->status === 'pending' && !$transaction->isExpired())
                                <a href="{{ route('transactions.payment', $transaction->id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-credit-card"></i> Pay
                                </a>
                            @endif
                            @if($transaction->status === 'paid')
                                <span class="text-success"><i class="bi bi-check-circle"></i> Completed</span>
                            @endif
                            @if($transaction->status === 'expired')
                                <span class="text-danger"><i class="bi bi-x-circle"></i> Expired</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-credit-card" style="font-size: 32px;"></i>
                            <p class="mt-2">No transactions yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection