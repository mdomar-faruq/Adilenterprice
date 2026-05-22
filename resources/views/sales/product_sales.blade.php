@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Filter Header -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('sales.product-sales') }}" method="GET" id="report-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">To Date</label>
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
            <div
                class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="mb-0 fw-bold tf2 text-dark">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Product Sales Performance
                </h5>

                <!-- New Live Dynamic Search Bar and Print Actions Container -->
                <div class="d-flex align-items-center gap-2 no-print ms-md-auto w-100 w-md-auto">
                    <div class="input-group input-group-sm rounded-2" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="productLiveSearch" class="form-control border-start-0 ps-0"
                            placeholder="Quick search product name...">
                    </div>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm fw-bold px-3">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>

            <!-- Custom Print Header Details -->
            <div class="d-none d-print-block mb-4 text-center p-4 border-bottom bg-light">
                <h2 class="fw-bold mb-1 text-dark">Adil Enterprise</h2>
                <h4 class="text-muted mb-2">Product Sales Performance Report</h4>
                <span class="badge border border-secondary text-dark px-3 py-1 bg-white font-monospace">
                    Reporting Period: {{ date('d M, Y', strtotime($start_date)) }} to
                    {{ date('d M, Y', strtotime($end_date)) }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="salesTable">
                    <thead class="table-light text-muted small text-uppercase border-bottom">
                        <tr>
                            <th class="ps-4 py-3">Product Name</th>
                            <th class="text-center py-3">Sold Qty</th>
                            <th class="text-center text-danger py-3">Dmg Qty</th>
                            <th class="text-center py-3">Net Qty</th>
                            <th class="text-end py-3">Sold Rev.</th>
                            <th class="text-end text-danger py-3">Dmg Loss</th>
                            <th class="text-end pe-4 fw-bold py-3 text-dark bg-light">Net Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        @forelse($report as $item)
                            <tr class="product-row">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark searchable-name">{{ $item->name }}</span>
                                    <div class="extra-small text-muted font-monospace">
                                        Avg Price: {{ number_format($item->avg_price, 2) }}
                                    </div>
                                </td>
                                <td class="text-center row-sold-qty" data-val="{{ $item->sold_qty }}">
                                    {{ number_format($item->sold_qty) }}
                                </td>
                                <td class="text-center text-danger row-dmg-qty" data-val="{{ $item->damage_qty }}">
                                    {{ number_format($item->damage_qty) }}
                                </td>
                                <td class="text-center fw-bold row-net-qty" data-val="{{ $item->net_qty }}">
                                    {{ number_format($item->net_qty) }}
                                </td>
                                <td class="text-end row-sold-rev" data-val="{{ $item->sold_revenue }}">
                                    {{ number_format($item->sold_revenue, 2) }}
                                </td>
                                <td class="text-end text-danger row-dmg-loss" data-val="{{ $item->damage_revenue }}">
                                    ({{ number_format($item->damage_revenue, 2) }})
                                </td>
                                <td class="text-end pe-4 fw-bold bg-light row-net-rev data-val"
                                    data-val="{{ $item->net_revenue }}"
                                    class="{{ $item->net_revenue < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($item->net_revenue, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-placeholder-row">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x display-6 d-block mb-2"></i> No records found for this
                                    period.
                                </td>
                            </tr>
                        @endforelse
                        <!-- Live Search Fallback Node -->
                        <tr id="noMatchesRow" style="display: none;">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-search display-6 d-block mb-2"></i> No products match your local search
                                terms.
                            </td>
                        </tr>
                    </tbody>
                    @if ($report->isNotEmpty())
                        <tfoot class="bg-dark text-white fw-bold table-group-divider align-middle">
                            <tr>
                                <td class="ps-4 py-3">GRAND TOTAL</td>
                                <td class="text-center py-3" id="totalSoldQty">
                                    {{ number_format($report->sum('sold_qty')) }}</td>
                                <td class="text-center py-3 text-danger-subtle" id="totalDmgQty">
                                    {{ number_format($report->sum('damage_qty')) }}</td>
                                <td class="text-center py-3 bg-secondary text-white" id="totalNetQty">
                                    {{ number_format($report->sum('net_qty')) }}</td>
                                <td class="text-end py-3" id="totalSoldRev">
                                    {{ number_format($report->sum('sold_revenue'), 2) }}</td>
                                <td class="text-end py-3 text-warning" id="totalDmgLoss">
                                    ({{ number_format($report->sum('damage_revenue'), 2) }})</td>
                                <td class="text-end pe-4 bg-primary text-white" style="font-size: 1.05rem;"
                                    id="totalNetRev">
                                    {{ number_format($report->sum('net_revenue'), 2) }}
                                </td>
                            </tr>
                            </footer>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <style>
        .w-md-auto {
            @media (min-width: 768px) {
                width: auto !important;
            }
        }

        .text-danger-subtle {
            color: #ff808b !important;
        }

        @media print {
            body * {
                visibility: hidden;
                background: none !important;
            }

            .printable-area,
            .printable-area * {
                visibility: visible;
            }

            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0 !important;
                padding: 0 !important;
            }

            .table {
                width: 100% !important;
            }

            .no-print,
            .tf2 {
                display: none !important;
            }

            .bg-dark {
                background-color: #212529 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .bg-secondary {
                background-color: #6c757d !important;
            }

            .bg-primary {
                background-color: #0d6efd !important;
            }

            .text-danger {
                color: #dc3545 !important;
            }

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
            // SWAL loading indicator for the controller database form submittal
            $('#report-form').on('submit', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Calculating...',
                        html: 'Fetching sales and damage data.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            });

            // Live Filter & Real-time Dynamic Footnote Recalculation Engine
            $('#productLiveSearch').on('keyup', function() {
                let term = $(this).val().toLowerCase().trim();
                let visibleRowsCount = 0;

                // Cache metrics counters
                let totalSoldQty = 0,
                    totalDmgQty = 0,
                    totalNetQty = 0,
                    totalSoldRev = 0,
                    totalDmgLoss = 0,
                    totalNetRev = 0;

                $('.product-row').each(function() {
                    let productName = $(this).find('.searchable-name').text().toLowerCase();

                    if (productName.indexOf(term) > -1) {
                        $(this).show();
                        visibleRowsCount++;

                        // Aggregate row numerical values directly using saved data parameters
                        totalSoldQty += parseFloat($(this).find('.row-sold-qty').data('val')) || 0;
                        totalDmgQty += parseFloat($(this).find('.row-dmg-qty').data('val')) || 0;
                        totalNetQty += parseFloat($(this).find('.row-net-qty').data('val')) || 0;
                        totalSoldRev += parseFloat($(this).find('.row-sold-rev').data('val')) || 0;
                        totalDmgLoss += parseFloat($(this).find('.row-dmg-loss').data('val')) || 0;
                        totalNetRev += parseFloat($(this).find('.row-net-rev').data('val')) || 0;
                    } else {
                        $(this).hide();
                    }
                });

                // Display local empty messaging indicator if nothing matches
                if (visibleRowsCount === 0 && $('.product-row').length > 0) {
                    $('#noMatchesRow').show();
                    $('tfoot').hide();
                } else {
                    $('#noMatchesRow').hide();
                    $('tfoot').show();
                }

                // Push synchronized updates directly to the report table footer DOM nodes
                $('#totalSoldQty').text(Math.round(totalSoldQty).toLocaleString());
                $('#totalDmgQty').text(Math.round(totalDmgQty).toLocaleString());
                $('#totalNetQty').text(Math.round(totalNetQty).toLocaleString());

                $('#totalSoldRev').text(totalSoldRev.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalDmgLoss').text('(' + totalDmgLoss.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ')');
                $('#totalNetRev').text(totalNetRev.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            });
        });
    </script>
@endpush
