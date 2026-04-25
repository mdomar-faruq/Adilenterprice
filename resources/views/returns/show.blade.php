@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0 text-dark">Return Details</h2>
                <p class="text-muted">Reference: <span class="badge bg-dark">RET-{{ $return->id }}</span></p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('returns.index') }}" class="btn btn-light border rounded-pill px-4">Back to List</a>
                <button onclick="window.print()" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Transaction Info</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Type:</span>
                            <span class="badge {{ $return->type == 'sales_return' ? 'bg-success' : 'bg-danger' }}">
                                {{ strtoupper(str_replace('_', ' ', $return->type)) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Date:</span>
                            <span class="fw-bold">{{ date('d M, Y', strtotime($return->return_date)) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span>DSR:</span>
                            <span class="fw-bold text-primary">{{ $return->dsr->name ?? 'Direct' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Financial Summary</h6>
                        <h1 class="display-5 fw-bold text-primary mb-0">TK {{ number_format($return->total_amount, 2) }}
                        </h1>
                        <p class="text-muted mt-2 mb-0"><strong>Remarks:</strong>
                            {{ $return->remarks ?? 'No specific notes.' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product Name</th>
                                    <th class="text-center">Good Qty</th>
                                    <th class="text-center">Damage Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($return->items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $item->product->name }}</div>
                                            <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-light text-dark border px-3 rounded-pill">{{ $item->good_qty }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-light text-danger border px-3 rounded-pill">{{ $item->damage_qty }}</span>
                                        </td>
                                        <td class="text-end">TK {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold text-primary">TK
                                            {{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
