@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('products.damage') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <h2 class="fw-bold mb-0">Damage Stock Report</h2>
                        <p class="text-muted mb-0">Analyze product loss and damage trends</p>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">From Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">To Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase small fw-bold opacity-75">Total Estimated Loss</h6>
                        <h1 class="display-5 fw-bold mb-0">TK {{ number_format($summary->sum('total_loss'), 2) }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase small fw-bold opacity-75">Total Damage Units</h6>
                        <h1 class="display-5 fw-bold mb-0">{{ $summary->sum('total_qty') }} <small
                                class="fs-6">Items</small></h1>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Damage Item Breakdown</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Product</th>
                                    <th>Return Type</th>
                                    <th class="text-center">Damage Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-4">Loss Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($damageItems as $item)
                                    <tr>
                                        <td class="ps-4">{{ $item->returnHeader->return_date }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $item->product->name }}</span>
                                            <div class="small text-muted">Ref: RET-{{ $item->returnHeader->id }}</div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $item->returnHeader->type == 'sales_return' ? 'bg-info' : 'bg-warning text-dark' }}">
                                                {{ strtoupper(str_replace('_', ' ', $item->returnHeader->type)) }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold text-danger">{{ $item->damage_qty }}</td>
                                        <td class="text-end">TK {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold">TK
                                            {{ number_format($item->damage_qty * $item->unit_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No damage records found for
                                            this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
