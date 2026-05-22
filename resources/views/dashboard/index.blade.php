@extends('layouts.app')

@section('content')
    <div class="container py-5">
        {{-- Header Section --}}
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-3 border-bottom">
            <div>
                <h3 class="fw-bold text-dark mb-1">Executive Dashboard</h3>
                <p class="text-muted small mb-0">Real-time overview of core financial metrics, ledger balances, and
                    operational stock metrics.</p>
            </div>
            <div>
                <button id="refreshDashboardBtn"
                    class="btn btn-sm btn-light border rounded-pill px-3 fw-medium text-secondary shadow-sm mb-2 mb-md-0 me-2">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </button>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small fw-medium">
                    <i class="bi bi-clock-history me-1 text-primary"></i> As of <span
                        id="timestampPlaceholder">Loading...</span>
                </span>
            </div>
        </div>

        {{-- Section 1: Financial & Ledger Standing --}}
        <h5 class="text-secondary small fw-bold text-uppercase tracking-wider mb-3">Accounts & Outstanding Balances</h5>
        <div class="row g-4 mb-5">
            {{-- Account Liquid Cash Amount Card --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                                <i class="bi bi-wallet2 fs-4 line-height-1"></i>
                            </div>
                            <span class="text-success small fw-semibold"><i class="bi bi-arrow-up-right me-1"></i>Liquid
                                Cash</span>
                        </div>
                        <h6 class="text-muted small mb-1 fw-medium">Current Account Amount</h6>
                        <h3 class="fw-bold text-dark mb-0 api-field" id="currentAccountAmount">
                            <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                        </h3>
                    </div>
                </div>
            </div>

            {{-- Product Stock Value Card --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                                <i class="bi bi-box-seam fs-4 line-height-1"></i>
                            </div>
                            <span class="text-primary small fw-semibold">Asset Value</span>
                        </div>
                        <h6 class="text-muted small mb-1 fw-medium">Current Product Stock Value</h6>
                        <h3 class="fw-bold text-dark mb-0 api-field" id="currentStockValue">
                            <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                        </h3>
                    </div>
                </div>
            </div>

            {{-- Total DSR Due Card --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                                <i class="bi bi-people fs-4 line-height-1"></i>
                            </div>
                            <span class="text-warning small fw-semibold">Receivable</span>
                        </div>
                        <h6 class="text-muted small mb-1 fw-medium">Total DSR Due</h6>
                        <h3 class="fw-bold text-dark mb-0 api-field" id="totalDsrDue">
                            <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                        </h3>
                    </div>
                </div>
            </div>

            {{-- Company Due Card --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                                <i class="bi bi-building fs-4 line-height-1"></i>
                            </div>
                            <span class="text-danger small fw-semibold">Payable</span>
                        </div>
                        <h6 class="text-muted small mb-1 fw-medium">Current Company Due</h6>
                        <h3 class="fw-bold text-dark mb-0 api-field" id="currentCompanyDue">
                            <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Operational Income & Expenditure --}}
        <h5 class="text-secondary small fw-bold text-uppercase tracking-wider mb-3">Operational Trade Statements</h5>
        <div class="row g-4">
            {{-- Sales Turnover Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-x-start border-success border-4 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success text-white px-3 py-2 rounded-3">
                                <i class="bi bi-graph-up-arrow fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-0 fw-medium">Overall Sales</h6>
                                <h4 class="fw-bold text-dark mb-0 mt-1 api-field" id="overallSales">
                                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-x-start border-info border-4 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info text-white px-3 py-2 rounded-3">
                                <i class="bi bi-cart-check fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-0 fw-medium">Overall Purchase</h6>
                                <h4 class="fw-bold text-dark mb-0 mt-1 api-field" id="overallPurchase">
                                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expense Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-x-start border-warning border-4 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning text-dark px-3 py-2 rounded-3">
                                <i class="bi bi-receipt-cutoff fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-0 fw-medium">Overall Expense</h6>
                                <h4 class="fw-bold text-dark mb-0 mt-1 api-field" id="overallExpense">
                                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sales Damage Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-x-start border-danger border-4 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger text-white px-3 py-2 rounded-3">
                                <i class="bi bi-x-octagon fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-0 fw-medium">Overall Sales Damage</h6>
                                <h4 class="fw-bold text-dark mb-0 mt-1 api-field" id="salesDamageAmount">
                                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .border-x-start {
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
        }

        .tracking-wider {
            letter-spacing: 0.05rem;
        }

        .line-height-1 {
            line-height: 1;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Helper function to handle monetary precision safely on the UI layer
            function formatCurrency(value) {
                let number = parseFloat(value);
                return isNaN(number) ? "0.00 TK" : number.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + " TK";
            }

            // Function dedicated to polling your data API route endpoint
            function fetchDashboardData() {
                // Render micro spinner objects to point fields to a loading action state
                $('.api-field').html(
                    '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>');

                $.ajax({
                    url: "{{ route('dashboardOne.data') }}", // Points directly to your Laravel JSON controller route
                    method: "GET",
                    dataType: "json",
                    success: function(response) {
                        // Unpack metrics objects and display mapped content layout formats safely
                        $('#currentAccountAmount').html(formatCurrency(response.currentAccountAmount));
                        $('#currentStockValue').html(formatCurrency(response.currentStockValue));
                        $('#totalDsrDue').html(formatCurrency(response.totalDsrDue));
                        $('#currentCompanyDue').html(formatCurrency(response.currentCompanyDue));

                        $('#overallSales').html(formatCurrency(response.overallSales));
                        $('#overallPurchase').html(formatCurrency(response.overallPurchase));
                        $('#overallExpense').html(formatCurrency(response.overallExpense));
                        $('#salesDamageAmount').html(formatCurrency(response.salesDamageAmount));

                        // Set the operational refresh timestamp string element
                        $('#timestampPlaceholder').text(response.timestamp || new Date()
                            .toLocaleTimeString());
                    },
                    error: function(xhr, status, error) {
                        console.error("Dashboard Fetch Error: ", error);
                        $('.api-field').html(
                            '<span class="text-danger small fs-6 fw-normal">Error Loading</span>');
                        $('#timestampPlaceholder').text('Failed to sync');
                    }
                });
            }

            // Fire initial API data capture load on DOM mount execution
            fetchDashboardData();

            // Bind click listener handler to refresh element button loop
            $('#refreshDashboardBtn').click(function(e) {
                e.preventDefault();
                fetchDashboardData();
            });
        });
    </script>
@endpush
