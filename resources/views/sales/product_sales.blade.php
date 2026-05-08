@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Filter Header -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('sales.product-sales') }}" method="GET" id="report-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">To Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-search me-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area">
            <div class="card-header  py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold tf2"><i class="bi bi-graph-up-arrow me-2 text-primary tf2"></i>Product Sales
                    Performance</h5>
                <button onclick="window.print()" class="btn btn-primary text-white border btn-sm no-print">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>

            <div class="d-none d-print-block mb-4 text-center">
                <h2 class="fw-bold">Adil Enterprise</h2>
                <h4>Product Sales Performance Report</h4>
                <p>Reporting Period: {{ $start_date }} to {{ $end_date }}</p>
                <hr>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Product Name</th>
                            <th class="text-center">Sold Qty</th>
                            <th class="text-center text-danger">Dmg Qty</th>
                            <th class="text-center">Net Qty</th>
                            <th class="text-end">Sold Rev.</th>
                            <th class="text-end text-danger">Dmg Loss</th>
                            <th class="text-end pe-4 fw-bold">Net Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ $item->name }}</span>
                                    <div class="extra-small text-muted">Avg Price: {{ number_format($item->avg_price, 2) }}
                                    </div>
                                </td>
                                <td class="text-center">{{ number_format($item->sold_qty) }}</td>
                                <td class="text-center text-danger">{{ number_format($item->damage_qty) }}</td>
                                <td class="text-center fw-bold">{{ number_format($item->net_qty) }}</td>
                                <td class="text-end">{{ number_format($item->sold_revenue, 2) }}</td>
                                <td class="text-end text-danger">({{ number_format($item->damage_revenue, 2) }})</td>
                                <td
                                    class="text-end pe-4 fw-bold {{ $item->net_revenue < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($item->net_revenue, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No records found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($report->isNotEmpty())
                        <tfoot class=" text-white">
                            <tr>
                                <td class="ps-4">GRAND TOTAL</td>
                                <td class="text-center">{{ number_format($report->sum('sold_qty')) }}</td>
                                <td class="text-center">{{ number_format($report->sum('damage_qty')) }}</td>
                                <td class="text-center bg-secondary">{{ number_format($report->sum('net_qty')) }}</td>
                                <td class="text-end">{{ number_format($report->sum('sold_revenue'), 2) }}</td>
                                <td class="text-end text-warning">{{ number_format($report->sum('damage_revenue'), 2) }}
                                </td>
                                <td class="text-end pe-4 text-info" style="font-size: 1.1rem;">
                                    {{ number_format($report->sum('net_revenue'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <style>
        @media print {

            /* 1. Hide everything on the page */
            body * {
                visibility: hidden;
                background: none !important;
            }

            /* 2. Show only the specific card and its children */
            .printable-area,
            .printable-area * {
                visibility: visible;
            }

            /* 3. Position the printable area at the very top-left of the page */
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: 1px solid #000 !important;
                /* Visual border for paper */
            }

            /* 4. Formatting adjustments for the table */
            .table {
                width: 100% !important;
            }

            .no-print {
                display: none !important;
            }

            .tf2 {
                display: none !important;
            }

            .bg-dark {
                background-color: #333 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                /* Ensures colors print in Chrome/Safari */
            }

            .text-danger {
                color: #dc3545 !important;
            }

            /* Remove margins/padding from container for a cleaner look */
            .container-fluid,
            .py-4 {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // SWAL loading indicator
            $('#report-form').on('submit', function() {
                Swal.fire({
                    title: 'Calculating...',
                    html: 'Fetching sales and damage data.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });
        });
    </script>
@endpush
