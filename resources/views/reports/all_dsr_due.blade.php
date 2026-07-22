@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <!-- Date Range Selection Header (Hidden during Printing) -->
        <div class="card border-0 shadow-sm mb-4 no-print theme-filter-card">
            <div class="card-body p-4">
                <form action="{{ route('reports.all_dsr_due') }}" method="GET" id="report-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-body-secondary">Filter From Date (Optional)</label>
                            <input type="date" name="start_date" class="form-control theme-input"
                                value="{{ $start_date }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-body-secondary">Filter To Date (Optional)</label>
                            <input type="date" name="end_date" class="form-control theme-input"
                                value="{{ $end_date }}">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-filter-square me-2"></i>Filter Report
                            </button>
                            @if ($start_date || $end_date)
                                <a href="{{ route('reports.all_dsr_due') }}"
                                    class="btn btn-outline-secondary fw-bold">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content Ledger Card -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area report-sheet">
            <div
                class="card-header bg-body-tertiary py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-0 fw-bold text-body-emphasis">
                        <i class="bi bi-person-lines-fill me-2 text-primary"></i>DSR Outstanding Due Statement
                    </h5>
                    <small class="text-body-secondary no-print">Summary ledger balances tracking collective account
                        balances</small>
                </div>

                <!-- Contextual Search Bar Controls -->
                <div class="d-flex align-items-center gap-2 no-print ms-md-auto w-100 w-md-auto">
                    <div class="input-group input-group-sm rounded-2 theme-search-box" style="max-width: 280px;">
                        <span class="input-group-text border-end-0 text-body-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" id="employeeSearch" class="form-control border-start-0 ps-0 theme-input"
                            placeholder="Search name or phone number...">
                    </div>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm fw-bold px-3">
                        <i class="bi bi-printer me-2"></i>Print Statement
                    </button>
                </div>
            </div>

            <!-- Custom Print Branding Header (Visible ONLY on Physical Paper Prints) -->
            <div class="d-none d-print-block p-4 text-center border-bottom bg-body-tertiary">
                <h2 class="fw-bold mb-1 text-body-emphasis">Adil Enterprise</h2>
                <h5 class="text-body-secondary mb-2">Master DSR Due Balance Sheet</h5>
                <span
                    class="badge border border-secondary text-body-emphasis px-3 py-1 bg-body text-body-emphasis font-monospace">
                    As of: {{ date('d M, Y H:i A') }}
                    @if ($start_date || $end_date)
                        (Data Range: {{ date('d M, Y', strtotime($start_date)) }} to
                        {{ date('d M, Y', strtotime($end_date)) }})
                    @endif
                </span>
            </div>

            <!-- Table Structure Container -->
            <div class="table-responsive report-table-wrapper">
                <table class="table table-hover align-middle mb-0 report-dsr-table">
                    <thead class="report-table-head small text-uppercase border-bottom">
                        <tr>
                            <th class="ps-4 py-3" style="width: 8%;">Emp ID</th>
                            <th class="py-3" style="width: 22%;">Employee Identity</th>
                            <th class="text-end py-3" style="width: 14%;">Opening Balance <br><small
                                    class="text-lowercase text-body-secondary fw-normal">(Up to
                                    {{ $start_date ? date('d M', strtotime($start_date)) : 'Start' }})</small></th>
                            <th class="text-end py-3 text-danger" style="width: 14%;">(+) Period Invoiced <br><small
                                    class="text-lowercase text-body-secondary fw-normal">(Selected Window)</small></th>
                            <th class="text-end py-3 text-success" style="width: 14%;">(-) Period Paid <br><small
                                    class="text-lowercase text-body-secondary fw-normal">(Selected Window)</small></th>
                            <th class="text-end py-3 fw-bold text-body-emphasis bg-body-tertiary" style="width: 14%;">Net
                                Current Due
                                <br><small class="text-lowercase text-body-secondary fw-normal">(Accumulated)</small>
                            </th>
                            <th class="text-center py-3 pe-4 no-print" style="width: 14%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        @forelse($employees as $emp)
                            <tr class="employee-row">
                                <td class="ps-4 font-monospace text-body-secondary">#{{ $emp->id }}</td>
                                <td>
                                    <span
                                        class="fw-bold d-block text-body-emphasis searchable-name">{{ $emp->name }}</span>
                                    <small class="text-body-secondary searchable-phone"><i
                                            class="bi bi-telephone me-1"></i>{{ $emp->phone ?? 'N/A' }}</small>
                                </td>
                                <td class="text-end fw-semibold text-body-secondary row-opening">
                                    {{ number_format($emp->dynamic_opening_balance, 2) }}
                                </td>
                                <td class="text-end text-danger fw-semibold row-invoiced">
                                    +{{ number_format($emp->current_invoiced, 2) }}
                                </td>
                                <td class="text-end text-success fw-semibold row-paid">
                                    -{{ number_format($emp->current_paid, 2) }}
                                </td>
                                <td
                                    class="text-end fw-bold bg-body-tertiary row-due {{ $emp->net_current_due > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($emp->net_current_due, 2) }}
                                </td>
                                <td class="text-center pe-4 no-print">
                                    <a href="{{ route('reports.dsr_ledger', ['employee' => $emp->id, 'start_date' => $start_date, 'end_date' => $end_date]) }}"
                                        class="btn btn-sm btn-outline-primary fw-bold px-2 py-1">
                                        <i class="bi bi-journal-text me-1"></i>Ledger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-person-x display-6 d-block mb-2"></i> No employee entries found in
                                    database registry.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($employees->isNotEmpty())
                        <tfoot class="report-summary fw-bold table-group-divider align-middle" id="tableSummaryFooter">
                            <tr>
                                <td class="ps-4 py-3" colspan="2">AGGREGATE ACCOUNT TOTALS</td>
                                <td class="text-end" id="totalOpening">
                                    {{ number_format($employees->sum('dynamic_opening_balance'), 2) }}
                                </td>
                                <td class="text-end text-danger-light" id="totalInvoiced">
                                    +{{ number_format($employees->sum('current_invoiced'), 2) }}
                                </td>
                                <td class="text-end text-success-light" id="totalPaid">
                                    -{{ number_format($employees->sum('current_paid'), 2) }}
                                </td>
                                <td class="text-end" style="font-size: 1.05rem;" id="totalDue">
                                    {{ number_format($employees->sum('net_current_due'), 2) }}
                                </td>
                                <td class="no-print border-0"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <style>
        .theme-filter-card,
        .report-sheet {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .theme-filter-card {
            border: 1px solid var(--bs-border-color) !important;
        }

        .theme-search-box .input-group-text,
        .theme-input,
        .theme-search-box .form-control {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        .theme-input:focus,
        .theme-search-box .form-control:focus {
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0 0 0.15rem rgba(var(--bs-primary-rgb), 0.18) !important;
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
        }

        .report-table-wrapper {
            border-top: 1px solid var(--bs-border-color);
        }

        .report-dsr-table thead th,
        .report-dsr-table tbody td,
        .report-dsr-table tbody tr {
            border-color: var(--bs-border-color);
        }

        .report-table-head th {
            background: var(--bs-tertiary-bg);
            color: var(--bs-emphasis-color);
        }

        .report-dsr-table tbody tr:hover {
            background-color: var(--bs-tertiary-bg);
        }

        .row-due {
            background: var(--bs-tertiary-bg);
        }

        .text-success-light {
            color: var(--bs-success-text-emphasis, #198754);
        }

        .text-danger-light {
            color: var(--bs-danger-text-emphasis, #dc3545);
        }

        .report-summary {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #ffffff;
        }

        .report-summary td {
            border-color: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        @media (min-width: 768px) {
            .w-md-auto {
                width: auto !important;
            }
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

            .table td,
            .table th {
                padding: 10px 8px !important;
                border-bottom: 1px solid #dee2e6 !important;
            }

            .report-summary {
                background: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Client-Side Live Search Box Filtering Logic
            $('#employeeSearch').on('keyup', function() {
                let val = $(this).val().toLowerCase().trim();

                let sumOpening = 0,
                    sumInvoiced = 0,
                    sumPaid = 0,
                    sumDue = 0;

                $('.employee-row').each(function() {
                    let name = $(this).find('.searchable-name').text().toLowerCase();
                    let phone = $(this).find('.searchable-phone').text().toLowerCase();

                    if (name.indexOf(val) > -1 || phone.indexOf(val) > -1) {
                        $(this).show();

                        // Dynamic sum parsing engine
                        sumOpening += parseFloat($(this).find('.row-opening').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                        sumInvoiced += parseFloat($(this).find('.row-invoiced').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                        sumPaid += parseFloat($(this).find('.row-paid').text().replace(/[^0-9.-]/g,
                            '')) || 0;
                        sumDue += parseFloat($(this).find('.row-due').text().replace(/[^0-9.-]/g,
                            '')) || 0;
                    } else {
                        $(this).hide();
                    }
                });

                // Update running aggregate totals conditionally
                if (val !== "") {
                    $('#totalOpening').text(Number(sumOpening).toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    }));
                    $('#totalInvoiced').text('+' + Number(sumInvoiced).toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    }));
                    $('#totalPaid').text('-' + Number(sumPaid).toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    }));
                    $('#totalDue').text(Number(sumDue).toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    }));
                } else {
                    // Fallback back onto dynamic financial PHP totals
                    $('#totalOpening').text(
                        "{{ number_format($employees->sum('dynamic_opening_balance'), 2) }}");
                    $('#totalInvoiced').text(
                        "+{{ number_format($employees->sum('current_invoiced'), 2) }}");
                    $('#totalPaid').text("-{{ number_format($employees->sum('current_paid'), 2) }}");
                    $('#totalDue').text("{{ number_format($employees->sum('net_current_due'), 2) }}");
                }
            });
        });
    </script>
@endpush
