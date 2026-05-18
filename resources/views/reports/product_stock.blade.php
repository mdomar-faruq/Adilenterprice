@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Filter Card (Hidden during printing) -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('reports.product_stock') }}" method="GET" id="report-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-lg"
                                value="{{ $start_date }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-lg"
                                value="{{ $end_date }}" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                <i class="bi bi-funnel-fill me-2"></i>Generate Statement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Printable Report Wrapper Card -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area">
            <div class="card-header py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="no-print">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-boxes me-2 text-primary"></i>Product Stock Statement
                    </h5>
                    <small class="text-muted no-print">Real-time ledger matching transaction adjustments</small>
                </div>

                <!-- Contextual Table Action Controls -->
                <div class="d-flex align-items-center gap-2 no-print ms-md-auto w-100 w-md-auto">
                    <div class="input-group input-group-sm rounded-2" style="max-width: 280px;">
                        <span class="input-group-text  border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="tableSearch" class="form-control border-start-0 ps-0"
                            placeholder="Filter product name or ID...">
                    </div>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm fw-bold px-3">
                        <i class="bi bi-printer me-2"></i>Print Statement
                    </button>
                </div>
            </div>

            <!-- Dynamic Invoice/Report Branding Block (Displays on physical paper prints only) -->
            <div class="d-none d-print-block p-4 text-center border-bottom">
                <h2 class="fw-bold mb-1 ">Adil Enterprise</h2>
                <h5 class="text-muted mb-3">Product Stock & Performance Log</h5>
                <span class="badge border border-secondary px-3 py-2 ">
                    Statement Window: {{ date('d M, Y', strtotime($start_date)) }} to
                    {{ date('d M, Y', strtotime($end_date)) }}
                </span>
            </div>

            <!-- Table Display Interface -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="stockReportTable">
                    <thead class=" text-muted small text-uppercase border-bottom">
                        <tr>
                            <th class="ps-4 py-3" style="width: 30%">Product / Code</th>
                            <th class="text-center py-3">Opening Balance</th>
                            <th class="text-center py-3 text-success">(+) Purchased</th>
                            <th class="text-center py-3 text-primary">(-) Sold</th>
                            <th class="text-center py-3 text-danger">(-) Damaged</th>
                            <th class="text-center py-3 pe-4 fw-bold">Final Stock</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        @forelse($report as $item)
                            <tr class="product-row">
                                <td class="ps-4 py-3">
                                    <span class="fw-bold d-block  searchable-name">{{ $item->name }}</span>
                                    <small class="text-muted font-monospace searchable-sku">ID: #{{ $item->id }}</small>
                                </td>
                                <td class="text-center fw-semibold  row-opening">
                                    {{ number_format($item->opening_balance) }}</td>
                                <td class="text-center text-success fw-semibold row-purchased">
                                    +{{ number_format($item->purchased_qty) }}</td>
                                <td class="text-center text-primary fw-semibold row-sold">
                                    -{{ number_format($item->sold_qty) }}</td>
                                <td class="text-center text-danger fw-semibold row-damaged">
                                    -{{ number_format($item->damaged_qty) }}</td>
                                <td class="text-center pe-4 fw-bold  row-final">
                                    {{ number_format($item->final_stock) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x display-6 d-block mb-3 text-muted"></i>
                                    No activity logged inside this specific date selection parameters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($report->isNotEmpty())
                        <tfoot class=" fw-bold table-group-divider align-middle" id="tableTotalsFooter">
                            <tr>
                                <td class="ps-4 py-3">SUMMARY TOTALS</td>
                                <td class="text-center" id="totalOpening">
                                    {{ number_format($report->sum('opening_balance')) }}</td>
                                <td class="text-center text-success-light" id="totalPurchased">
                                    +{{ number_format($report->sum('purchased_qty')) }}</td>
                                <td class="text-center text-info-light" id="totalSold">
                                    -{{ number_format($report->sum('sold_qty')) }}</td>
                                <td class="text-center text-danger-light" id="totalDamaged">
                                    -{{ number_format($report->sum('damaged_qty')) }}</td>
                                <td class="text-center pe-4 bg-secondary text-white" style="font-size: 1.05rem;"
                                    id="totalFinal">
                                    {{ number_format($report->sum('final_stock')) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <style>
        /* Display parameters for custom screen styles */
        .text-success-light {
            color: #a3cfbb;
        }

        .text-info-light {
            color: #6ea8fe;
        }

        .text-danger-light {
            color: #ea868f;
        }

        .w-md-auto {
            @media (min-width: 768px) {
                width: auto !important;
            }
        }

        /* Clean printing engine structure */
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
                box-shadow: none !important;
            }

            .no-print {
                display: none !important;
            }

            .table td,
            .table th {
                padding: 10px 6px !important;
                border-bottom: 1px solid #dee2e6 !important;
            }

            /* Forces modern browsers to retain background values on print outputs */
            .bg-dark {
                background-color: #212529 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .bg-secondary {
                background-color: #495057 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-light {
                background-color: #f8f9fa !important;
                color: #212529 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // 1. Loading UI state transitions on form processing
            $('#report-form').on('submit', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Processing Report Data...',
                        html: 'Compiling balance points and verifying item changes.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            });

            // 2. Real-time Reactive Client Side Table Filtering
            $('#tableSearch').on('keyup', function() {
                let keyword = $(this).val().toLowerCase().trim();
                let visibleRows = 0;

                // Accumulators for calculating dynamic filtered totals
                let sumOpening = 0,
                    sumPurchased = 0,
                    sumSold = 0,
                    sumDamaged = 0,
                    sumFinal = 0;

                $('.product-row').each(function() {
                    let productName = $(this).find('.searchable-name').text().toLowerCase();
                    let productSku = $(this).find('.searchable-sku').text().toLowerCase();

                    if (productName.indexOf(keyword) > -1 || productSku.indexOf(keyword) > -1) {
                        $(this).show();
                        visibleRows++;

                        // Parse out formatted string numerical values safely
                        sumOpening += parseFloat($(this).find('.row-opening').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                        sumPurchased += parseFloat($(this).find('.row-purchased').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                        sumSold += parseFloat($(this).find('.row-sold').text().replace(/[^0-9.-]/g,
                            '')) || 0;
                        sumDamaged += parseFloat($(this).find('.row-damaged').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                        sumFinal += parseFloat($(this).find('.row-final').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                    } else {
                        $(this).hide();
                    }
                });

                // Update total counters with new filtered math calculations
                if (keyword !== "") {
                    $('#totalOpening').text(Number(sumOpening).toLocaleString());
                    $('#totalPurchased').text('+' + Number(sumPurchased).toLocaleString());
                    $('#totalSold').text('-' + Number(sumSold).toLocaleString());
                    $('#totalDamaged').text('-' + Number(sumDamaged).toLocaleString());
                    $('#totalFinal').text(Number(sumFinal).toLocaleString());
                } else {
                    // Revert back to master PHP values if empty
                    $('#totalOpening').text("{{ number_format($report->sum('opening_balance')) }}");
                    $('#totalPurchased').text("+{{ number_format($report->sum('purchased_qty')) }}");
                    $('#totalSold').text("-{{ number_format($report->sum('sold_qty')) }}");
                    $('#totalDamaged').text("-{{ number_format($report->sum('damaged_qty')) }}");
                    $('#totalFinal').text("{{ number_format($report->sum('final_stock')) }}");
                }
            });
        });
    </script>
@endpush
