@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <!-- Filter Management Header Wrapper Box -->
        <div class="card border-0 shadow-sm mb-4 no-print theme-filter-card">
            <div class="card-body p-4">
                <form action="{{ route('reports.company_ledger', $company->id) }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-body-secondary">Statement Start Date</label>
                            <input type="date" name="start_date" class="form-control theme-input"
                                value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-body-secondary">Statement End Date</label>
                            <input type="date" name="end_date" class="form-control theme-input"
                                value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-calendar-range me-2"></i>Generate Ledger
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Data Output Sheet Ledger Table -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area theme-ledger-card">
            <div class="card-header theme-card-header py-3 border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-body-emphasis">Company Account Ledger Account</h5>
                    <span class="text-body-secondary small fw-bold">Company Profile: <b
                            class="text-primary">{{ $company->name }}</b></span>
                </div>
                <button type="button" onclick="window.print()"
                    class="btn btn-outline-secondary btn-sm px-3 no-print fw-bold">
                    <i class="bi bi-printer me-2"></i>Print Ledger
                </button>
            </div>

            <!-- Print View Dynamic Branding Header Info -->
            <div class="d-none d-print-block text-center p-3 border-bottom theme-print-header">
                <h3 class="fw-bold mb-1">Adil Enterprise</h3>
                <p class="text-body-secondary mb-0">Company Ledger: {{ $company->name }}</p>
                <small class="font-monospace text-body-secondary">Filtered Window:
                    {{ date('d M, Y', strtotime($startDate)) }} to
                    {{ date('d M, Y', strtotime($endDate)) }}</small>
            </div>

            <div class="table-responsive theme-table-wrap">
                <table class="table table-hover align-middle mb-0 theme-ledger-table">
                    <thead class="theme-table-head small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3" style="width: 15%;">Date</th>
                            <th style="width: 25%;">Transaction Reference</th>
                            <th class="text-center" style="width: 15%;">Type</th>
                            <th class="text-end" style="width: 15%;">Debit (Paid)</th>
                            <th class="text-end" style="width: 15%;">Credit (Purchased)</th>
                            <th class="text-end pe-4" style="width: 15%;">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="theme-table-body">
                        <!-- Important: Top Consolidated Balance Row -->
                        <tr class="theme-opening-row">
                            <td class="ps-4 text-body-secondary font-monospace">{{ date('d-m-Y', strtotime($startDate)) }}
                            </td>
                            <td colspan="2" class="fw-bold text-body-secondary italic">
                                <i class="bi bi-arrow-right-short me-1"></i> Opening Balance brought forward
                            </td>
                            <td class="text-end text-body-secondary">-</td>
                            <td class="text-end text-body-secondary">-</td>
                            <td class="text-end pe-4 fw-bold text-body-emphasis">
                                {{ number_format($historicalOpeningBalance, 2) }}
                            </td>
                        </tr>

                        <!-- Main Dynamic Transaction Mapping Iteration Loop -->
                        @forelse($filteredLedger as $row)
                            <tr>
                                <td class="ps-4 font-monospace text-body-secondary">
                                    {{ date('d-m-Y', strtotime($row->date)) }}</td>
                                <td>
                                    @if ($row->reference)
                                        <span
                                            class="fw-bold text-body-emphasis font-monospace">{{ $row->reference }}</span>
                                    @else
                                        <span class="text-body-secondary italic small text-uppercase">Payment
                                            Voucher</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($row->type == 'Purchase')
                                        <span class="badge bg-danger-subtle text-danger border px-2 py-1">Purchase
                                            Inv</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border px-2 py-1">Payment
                                            Doc</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace text-success fw-bold">
                                    {{ $row->debit > 0 ? number_format($row->debit, 2) : '-' }}
                                </td>
                                <td class="text-end font-monospace text-danger fw-bold">
                                    {{ $row->credit > 0 ? number_format($row->credit, 2) : '-' }}
                                </td>
                                <td
                                    class="text-end pe-4 font-monospace fw-bold {{ $row->balance >= 0 ? 'text-body-emphasis' : 'text-primary' }}">
                                    {{ number_format($row->balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-body-secondary">
                                    <i class="bi bi-folder-x display-6 d-block mb-1"></i> No active postings within this
                                    designated timeframe window.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .theme-filter-card,
        .theme-ledger-card,
        .theme-card-header,
        .theme-print-header,
        .theme-table-wrap,
        .theme-table-head,
        .theme-table-body,
        .theme-opening-row {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .theme-filter-card,
        .theme-card-header,
        .theme-print-header,
        .theme-ledger-card,
        .theme-table-head th,
        .theme-table-body td,
        .theme-opening-row {
            border-color: var(--bs-border-color) !important;
        }

        .theme-input {
            background: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        .theme-input:focus {
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0 0 0.15rem rgba(var(--bs-primary-rgb), 0.18) !important;
            background: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
        }

        .theme-table-head th {
            background: var(--bs-tertiary-bg) !important;
            color: var(--bs-emphasis-color) !important;
        }

        .theme-table-body tr:hover {
            background: var(--bs-tertiary-bg);
        }

        .theme-opening-row {
            background: var(--bs-tertiary-bg);
        }

        .italic {
            font-style: italic;
        }

        @page {
            size: A4;
            margin: 8mm;
            orientation: portrait;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #111111 !important;
                margin: 0 !important;
                font-size: 10.2pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden !important;
            }

            .printable-area,
            .printable-area *,
            .theme-print-header,
            .theme-print-header * {
                visibility: visible !important;
            }

            .container,
            .container-fluid {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .printable-area {
                position: relative !important;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0;
                box-shadow: none !important;
            }

            .theme-card-header,
            .theme-filter-card {
                display: none !important;
            }

            .theme-table-wrap,
            .theme-table-head,
            .theme-table-body,
            .theme-opening-row {
                background: #ffffff !important;
                color: #111111 !important;
            }

            .theme-ledger-table {
                table-layout: fixed;
                width: 100%;
                font-size: 9.7pt !important;
            }

            .theme-table-head th,
            .theme-table-body td,
            .theme-opening-row td {
                border: 1px solid #dee2e6 !important;
                background: #ffffff !important;
                color: #111111 !important;
                font-size: 9.7pt !important;
                padding: 6px 8px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .theme-table-head {
                display: table-header-group;
            }

            .theme-print-header {
                background: #ffffff !important;
                color: #111111 !important;
                border-bottom: 1px solid #dee2e6 !important;
                margin-bottom: 8px;
            }

            .theme-print-header h3,
            .theme-print-header p,
            .theme-print-header small {
                color: #111111 !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection
