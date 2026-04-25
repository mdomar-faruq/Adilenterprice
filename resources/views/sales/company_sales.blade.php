@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('sales.company-sales') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold text-uppercase text-muted">Company</label>
                        <select name="company_id" class="form-select select2" required>
                            <option value="">Search Company...</option>
                            @foreach ($companies as $v)
                                <option value="{{ $v->id }}" {{ $company->id == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-uppercase text-muted">From Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $start }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-uppercase text-muted">To Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $end }}" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Generate Sales Report</button>
                    </div>
                </form>
            </div>
        </div>

        @if (isset($salesData))
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3">
                        <h6 class="small text-uppercase opacity-75">Total Sales Volume</h6>
                        <h2 class="fw-bold mb-0">TK {{ number_format($salesData->sum('subtotal'), 2) }}</h2>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">Detailed Sales: {{ $company->name }}</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Date</th>
                                        <th>Invoice</th>
                                        <th>Product</th>
                                        <th class="text-center">Sold Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end pe-4">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salesData as $item)
                                        <tr>
                                            <td class="ps-4 text-muted small">{{ $item->sale->sale_date }}</td>
                                            <td><span
                                                    class="badge bg-light text-dark border">#{{ $item->sale->invoice_no }}</span>
                                            </td>
                                            <td class="fw-bold text-dark">{{ $item->product->name }}</td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end">TK {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end pe-4 fw-bold text-primary">TK
                                                {{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-dark text-white fw-bold">
                                    <tr>
                                        <td colspan="3" class="text-end ps-4">TOTAL FOR {{ strtoupper($company->name) }}
                                        </td>
                                        <td class="text-center">{{ $salesData->sum('quantity') }}</td>
                                        <td></td>
                                        <td class="text-end pe-4">TK {{ number_format($salesData->sum('subtotal'), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
