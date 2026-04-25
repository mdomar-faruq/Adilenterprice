@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="fw-bold mb-4">Profit & Loss Statement</h2>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-4" action="{{ route('accounts.profit-loss') }}" method="GET">
                        <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-dark w-100">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Income Statement</h5>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Sales (Revenue)</span>
                            <span class="fw-bold text-success">+ TK {{ number_format($totalSales, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Cost of Goods Sold (COGS)</span>
                            <span class="fw-bold text-danger">- TK {{ number_format($totalCost, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Gross Profit</span>
                            <span class="fw-bold text-primary">TK {{ number_format($grossProfit, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Operating Expenses</span>
                            <span class="fw-bold text-danger">- TK {{ number_format($totalExpenses, 2) }}</span>
                        </div>
                        <hr style="border-top: 2px solid #000;">
                        <div class="d-flex justify-content-between">
                            <h4 class="fw-bold">Net Profit</h4>
                            <h4 class="fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                TK {{ number_format($netProfit, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                    <div class="card-body p-4 text-center">
                        <h6 class="text-muted text-uppercase small">Profit Margin</h6>
                        @php
                            $margin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;
                        @endphp
                        <h1 class="display-4 fw-bold mt-3">{{ number_format($margin, 1) }}%</h1>
                        <p class="text-muted">Net profit percentage based on total revenue.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
