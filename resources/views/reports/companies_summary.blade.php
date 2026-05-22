@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Filter Card Box Header Layer -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('reports.company_summary') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Analysis Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Analysis End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-funnel me-2"></i>Filter Summary
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Output Overview Panel Table -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area">
            <div
                class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3 no-print">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-building me-2 text-primary"></i>All Companies Balances Summary
                    </h5>
                </div>

                <div class="d-flex align-items-center gap-2 no-print ms-md-auto w-100 w-md-auto">
                    <div class="input-group input-group-sm rounded-2" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="companyQuickSearch" class="form-control border-start-0 ps-0"
                            placeholder="Filter company names...">
                    </div>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold px-3">
                        <i class="bi bi-printer me-2"></i>Print Summary
                    </button>
                </div>
            </div>

            <!-- Print View Context Branding Header Details -->
            <div class="d-none d-print-block text-center p-4 border-bottom bg-light">
                <h2 class="fw-bold mb-1 text-dark">Adil Enterprise</h2>
                <h4 class="text-muted mb-2">Companies Account Summary</h4>
                <span class="badge border border-secondary text-dark px-3 py-1 bg-white font-monospace">
                    Reporting Range Window: {{ date('d M, Y', strtotime($startDate)) }} to
                    {{ date('d M, Y', strtotime($endDate)) }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="summaryReportTable">
                    <thead class="table-light text-muted small text-uppercase border-bottom">
                        <tr>
                            <th class="ps-4 py-3">Company</th>
                            <th class="text-end py-3 text-secondary">Opening Bal <small
                                    class="text-muted font-monospace">({{ date('d-m-Y', strtotime($startDate)) }})</small>
                            </th>
                            <th class="text-end py-3 text-danger">Total Purchases (+)</th>
                            <th class="text-end py-3 text-success">Total Payments (-)</th>
                            <th class="text-end py-3 text-dark bg-light fw-bold pe-4">Closing Balance</th>
                            <th class="text-center py-3 no-print" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        @forelse($reportData as $row)
                            <tr class="company-summary-row">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark target-company-name">{{ $row->name }}</span>
                                </td>
                                <td class="text-end font-monospace metrics-opening" data-val="{{ $row->opening }}">
                                    {{ number_format($row->opening, 2) }}
                                </td>
                                <td class="text-end font-monospace text-danger metrics-purchases"
                                    data-val="{{ $row->purchases }}">
                                    {{ number_format($row->purchases, 2) }}
                                </td>
                                <td class="text-end font-monospace text-success metrics-payments"
                                    data-val="{{ $row->payments }}">
                                    {{ number_format($row->payments, 2) }}
                                </td>
                                <td class="text-end font-monospace fw-bold pe-4 bg-light metrics-balance {{ $row->balance >= 0 ? 'text-dark' : 'text-primary' }}"
                                    data-val="{{ $row->balance }}">
                                    {{ number_format($row->balance, 2) }}
                                </td>
                                <td class="text-center no-print">
                                    <!-- Securely carries forward your exact date filter strings directly into individual item page views -->
                                    <a href="{{ route('reports.company_ledger', ['id' => $row->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                        class="btn btn-sm btn-outline-primary fw-bold px-3 py-1">
                                        <i class="bi bi-journal-text me-1"></i>Ledger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-building-exclamation display-6 d-block mb-2"></i> No vendor profiles
                                    available to evaluate in database records.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="emptySearchFallbackRow" style="display: none;">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-search display-6 d-block mb-2"></i> No recorded company name profiles match
                                your search filters.
                            </td>
                        </tr>
                    </tbody>
                    @if ($reportData->isNotEmpty())
                        <tfoot class=" text-white fw-bold border-top align-middle">
                            <tr>
                                <td class="ps-4 py-3">GRAND TOTALS</td>
                                <td class="text-end py-3 font-monospace" id="totalOpening">
                                    {{ number_format($reportData->sum('opening'), 2) }}</td>
                                <td class="text-end py-3 text-danger-subtle font-monospace" id="totalPurchases">
                                    {{ number_format($reportData->sum('purchases'), 2) }}</td>
                                <td class="text-end py-3 text-success-subtle font-monospace" id="totalPayments">
                                    {{ number_format($reportData->sum('payments'), 2) }}</td>
                                <td class="text-end pe-4 bg-secondary font-monospace" id="totalBalance"
                                    style="font-size: 1.05rem;">{{ number_format($reportData->sum('balance'), 2) }}</td>
                                <td class="no-print bg-dark"></td>
                            </tr>
                        </tfoot>
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

        .text-success-subtle {
            color: #87e0b1 !important;
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

            .no-print {
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
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Client-Side Real-Time Filter Engine
            $('#companyQuickSearch').on('keyup', function() {
                let text = $(this).val().toLowerCase().trim();
                let matchedCount = 0;

                let aggOpening = 0,
                    aggPurchases = 0,
                    aggPayments = 0,
                    aggBalance = 0;

                $('.company-summary-row').each(function() {
                    let companyName = $(this).find('.target-company-name').text().toLowerCase();

                    if (companyName.indexOf(text) > -1) {
                        $(this).show();
                        matchedCount++;

                        // Track mathematical calculations for active filtered selections
                        aggOpening += parseFloat($(this).find('.metrics-opening').data('val')) || 0;
                        aggPurchases += parseFloat($(this).find('.metrics-purchases').data(
                            'val')) || 0;
                        aggPayments += parseFloat($(this).find('.metrics-payments').data('val')) ||
                            0;
                        aggBalance += parseFloat($(this).find('.metrics-balance').data('val')) || 0;
                    } else {
                        $(this).hide();
                    }
                });

                // Toggle visibility vectors based on search match limits
                if (matchedCount === 0 && $('.company-summary-row').length > 0) {
                    $('#emptySearchFallbackRow').show();
                    $('tfoot').hide();
                } else {
                    $('#emptySearchFallbackRow').hide();
                    $('tfoot').show();
                }

                // Print updated aggregated values on interface labels dynamically 
                $('#totalOpening').text(aggOpening.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalPurchases').text(aggPurchases.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalPayments').text(aggPayments.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalBalance').text(aggBalance.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            });
        });
    </script>
@endpush
