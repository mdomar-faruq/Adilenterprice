@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <!-- Navigation Header Options -->
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
            <a href="{{ route('reports.all_dsr_due', ['start_date' => $start_date ?? '', 'end_date' => $end_date ?? '']) }}"
                class="btn btn-outline-secondary fw-bold border theme-back-link">
                <i class="bi bi-arrow-left me-2"></i>Back to Statement Sheets
            </a>
            <button onclick="window.print()" class="btn btn-primary fw-bold">
                <i class="bi bi-printer me-2"></i>Print Ledger Log
            </button>
        </div>

        <!-- Printable Account History Record Card -->
        <div class="card border-0 shadow-sm overflow-hidden printable-ledger-card ledger-shell">

            <!-- Institutional Statement Branding Header -->
            <div class="p-4 text-center border-bottom ledger-branding">
                <h2 class="fw-bold mb-1 text-body-emphasis">Adil Enterprise</h2>
                <h4 class="fw-bold text-primary mb-2">Individual Account Statement Ledger</h4>

                <div class="mt-3">
                    <span class="badge border border-secondary px-3 py-2 theme-badge font-monospace fs-7">
                        Statement Window:
                        @if (!empty($start_date) || !empty($end_date))
                            {{ !empty($start_date) ? date('d M, Y', strtotime($start_date)) : 'Inception' }} to
                            {{ !empty($end_date) ? date('d M, Y', strtotime($end_date)) : date('d M, Y') }}
                        @else
                            Complete Historic Lifetime Summary
                        @endif
                    </span>
                </div>
            </div>

            <!-- Identity Metrics Profiler Strip -->
            <div class="card-body border-bottom p-4 ledger-meta-strip">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-uppercase font-monospace text-body-secondary d-block small">Account Holder
                            Name</small>
                        <h4 class="fw-bold text-body-emphasis mb-0">{{ $customer->name ?? $employee->name }}</h4>
                        <span class="text-body-secondary extra-small">Registry Master Reference Code:
                            #{{ $customer->id ?? $employee->id }}</span>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <small class="text-uppercase font-monospace text-body-secondary d-block small">Contact Identity
                            Information</small>
                        <div class="fw-bold text-body-emphasis fs-5">
                            <i
                                class="bi bi-telephone me-1 text-body-secondary"></i>{{ $customer->phone ?? ($employee->phone ?? 'Unavailable') }}
                        </div>
                        <small class="text-body-secondary">Generated Data Log Stamp: {{ date('d M, Y h:i A') }}</small>
                    </div>
                </div>
            </div>

            <!-- Transaction Logs Data Matrix Table Grid -->
            <div class="table-responsive ledger-table-wrap">
                <table class="table table-striped align-middle mb-0 ledger-table">
                    <thead class="small text-uppercase font-monospace border-bottom ledger-table-head">
                        <tr>
                            <th class="ps-4 py-3" style="width: 15%;">Posting Date</th>
                            <th class="py-3" style="width: 35%;">Transaction Reference Identity</th>
                            <th class="text-end py-3 text-danger" style="width: 15%;">Debit (+)</th>
                            <th class="text-end py-3 text-success" style="width: 15%;">Credit (-)</th>
                            <th class="text-end pe-4 py-3 text-body-emphasis ledger-balance-head" style="width: 20%;">
                                Running Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="border-0 font-monospace ledger-body">
                        @forelse($ledger as $item)
                            <tr>
                                <td class="ps-4 text-body-secondary">{{ date('d M, Y', strtotime($item->date)) }}</td>
                                <td>
                                    <span
                                        class="fw-bold text-body-emphasis d-block font-sans-serif">{{ $item->reference }}</span>
                                    <span class="badge border theme-badge extra-small py-0 px-1">{{ $item->type }}</span>
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    {{ $item->debit > 0 ? number_format($item->debit, 2) : '-' }}
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    {{ $item->credit > 0 ? number_format($item->credit, 2) : '-' }}
                                </td>
                                @php
                                    // Fallback mapping matching both your custom balance variable names
                                    $itemBalance = isset($item->balance) ? $item->balance : $item->running_balance ?? 0;
                                @endphp
                                <td class="text-end pe-4 fw-bold {{ $itemBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($itemBalance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-body-secondary font-sans-serif">
                                    <i class="bi bi-folder-x display-6 d-block mb-2 text-body-secondary"></i>
                                    No ledger items matched inside this window timeframe.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .ledger-shell,
        .ledger-branding,
        .ledger-meta-strip,
        .ledger-table-wrap,
        .ledger-table-head,
        .ledger-body {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .ledger-branding,
        .ledger-meta-strip,
        .ledger-table-wrap,
        .ledger-table-head,
        .theme-badge,
        .theme-back-link {
            border-color: var(--bs-border-color) !important;
        }

        .ledger-table-head th {
            background: var(--bs-tertiary-bg);
            color: var(--bs-emphasis-color);
        }

        .ledger-table tbody tr:hover {
            background-color: var(--bs-tertiary-bg);
        }

        .theme-badge {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .theme-back-link {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .ledger-balance-head {
            background: var(--bs-tertiary-bg);
        }

        /* Utility font reset classes for running logs style variations */
        .font-sans-serif {
            font-family: system-ui, -apple-system, sans-serif !important;
        }

        .extra-small {
            font-size: 0.72rem;
        }

        .fs-7 {
            font-size: 0.85rem;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #111111 !important;
            }

            body * {
                visibility: hidden;
                background: none !important;
                color: #111111 !important;
            }

            .printable-ledger-card,
            .printable-ledger-card * {
                visibility: visible !important;
                background: #ffffff !important;
                color: #111111 !important;
            }

            .printable-ledger-card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .ledger-branding,
            .ledger-meta-strip,
            .ledger-table-wrap,
            .ledger-table-head,
            .ledger-body,
            .theme-badge {
                background: #ffffff !important;
                color: #111111 !important;
            }

            .ledger-table-head th,
            .table td,
            .table th {
                padding: 12px 10px !important;
                border-bottom: 1px solid #dee2e6 !important;
                color: #111111 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection
