@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('sales.sr-sales') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Select Sales Representative (SR)</label>
                        <select name="sr_id" class="form-select select2" required>
                            <option value="">Search SR...</option>
                            @foreach ($srs as $sr)
                                <option value="{{ $sr->id }}" {{ $selectedSr->id == $sr->id ? 'selected' : '' }}>
                                    {{ $sr->name }} | {{ $sr->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">From Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">To Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">View SR Performance</button>
                    </div>
                </form>
            </div>
        </div>

        @if (isset($sales))
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3">
                        <small class="opacity-75 text-uppercase fw-bold">Total Invoiced</small>
                        <h3 class="fw-bold mb-0">TK {{ number_format($totalSalesAmount, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-success text-white p-3">
                        <small class="opacity-75 text-uppercase fw-bold">Total Collected</small>
                        <h3 class="fw-bold mb-0">TK {{ number_format($totalCollection, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-danger text-white p-3">
                        <small class="opacity-75 text-uppercase fw-bold">Total Outstanding (Due)</small>
                        <h3 class="fw-bold mb-0">TK {{ number_format($totalDue, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold">Sales Log: {{ $selectedSr->name }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-uppercase">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Invoice No</th>
                                <th>SR Sales Man</th>
                                <th class="text-end">Bill Amount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end pe-4">Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td class="ps-4 small text-muted">{{ $sale->sale_date }}</td>
                                    <td>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="text-decoration-none">
                                            <span class="fw-bold text-primary">#{{ $sale->invoice_no }}</span>
                                            <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $sale->sr->name ?? 'Walking SR' }}</div>
                                        <small class="text-muted">{{ $sale->sr->designation ?? '' }}</small>
                                    </td>
                                    <td class="text-end fw-bold">TK {{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="text-end text-success">TK {{ number_format($sale->paid_amount, 2) }}</td>
                                    <td class="text-end pe-4 text-danger">TK {{ number_format($sale->due_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">No sales records found for this SR.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
