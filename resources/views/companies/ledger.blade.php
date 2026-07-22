@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-none d-print-block mb-4 text-center">
            <h2 class="fw-bold text-uppercase border-bottom pb-2">Company Account Statement</h2>
            <h4 class="mt-2">Company: <span class="text-primary">{{ $company->name }}</span></h4>
            <p class="text-muted small">Generated on: {{ date('d M, Y') }}</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div>
                <h2 class="fw-bold mb-1 text-body-emphasis">Company Ledger</h2>
                <p class="text-body-secondary mb-0">Statement for <strong>{{ $company->name }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" onclick="window.print()"
                    class="btn btn-primary text-white rounded-pill shadow-sm px-4">
                    <i class="bi bi-printer me-2"></i>Print Ledger
                </button>
                <a href="{{ route('companies.index') }}" class="btn btn-primary border rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 d-print-none">
                <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4 ledger-side-card">
                    <div class="card-body bg-primary text-white p-4 text-center">
                        <small class="text-white-50 text-uppercase fw-bold">Total Payable Balance</small>
                        <h2 class="fw-bold mb-0">
                            TK{{ number_format($ledger->last()->balance ?? $company->opening_balance, 2) }}</h2>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-3 ledger-info-card">
                    <h6 class="fw-bold small text-uppercase text-body-secondary mb-3">Company Details</h6>
                    <p class="mb-1 small text-body-emphasis"><strong>Phone:</strong> {{ $company->phone ?? 'N/A' }}</p>
                    <p class="mb-1 small text-body-emphasis"><strong>Email:</strong> {{ $company->email ?? 'N/A' }}</p>
                    <p class="mb-0 small text-body-emphasis"><strong>Address:</strong> {{ $company->address ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="col-lg-9 col-print-12">
                <div class="card border-0 shadow-sm border-print-none shadow-print-none rounded-4 ledger-shell-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 ledger-table" id="ledgerTable">
                            <thead class="ledger-table-head text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th class="text-end">Debit (-)</th>
                                    <th class="text-end">Credit (+)</th>
                                    <th class="text-end pe-4">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="ledger-table-body">
                                <tr class="fw-semibold ledger-opening-row">
                                    <td class="ps-4">---</td>
                                    <td colspan="2">Initial Opening Balance</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end pe-4">TK{{ number_format($company->opening_balance, 2) }}</td>
                                </tr>

                                @foreach ($ledger as $row)
                                    <tr>
                                        <td class="ps-4">{{ date('d M, Y', strtotime($row->date)) }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $row->type == 'Purchase' ? 'bg-secondary' : 'bg-success' }} rounded-pill px-3">
                                                {{ $row->type }}
                                            </span>
                                        </td>
                                        <td class="text-body-secondary small fw-bold">
                                            {{ $row->reference ?? 'PAY-' . $row->id }}
                                        </td>
                                        <td class="text-end {{ $row->debit > 0 ? 'text-danger fw-bold' : '' }}">
                                            {{ $row->debit > 0 ? number_format($row->debit, 2) : '-' }}
                                        </td>
                                        <td class="text-end {{ $row->credit > 0 ? 'text-success fw-bold' : '' }}">
                                            {{ $row->credit > 0 ? number_format($row->credit, 2) : '-' }}
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-body-emphasis">
                                            TK{{ number_format($row->balance, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .ledger-info-card,
        .ledger-shell-card,
        .ledger-table-head,
        .ledger-table-body,
        .ledger-opening-row {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .ledger-info-card,
        .ledger-shell-card,
        .ledger-table-head th,
        .ledger-table td,
        .ledger-table th,
        .ledger-opening-row {
            border-color: var(--bs-border-color) !important;
        }

        .ledger-table-head {
            background: var(--bs-tertiary-bg) !important;
        }

        .ledger-table-head th {
            color: var(--bs-emphasis-color) !important;
            background: var(--bs-tertiary-bg) !important;
        }

        .ledger-table tbody tr:hover {
            background: var(--bs-tertiary-bg);
        }

        .ledger-opening-row {
            background: var(--bs-tertiary-bg);
        }

        @page {
            size: auto;
            margin: 10mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #111111 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body *,
            .ledger-info-card,
            .ledger-shell-card,
            .ledger-table,
            .ledger-table-head,
            .ledger-table-body,
            .ledger-opening-row {
                color: #111111 !important;
                background: #ffffff !important;
            }

            .container-fluid {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            .col-lg-9 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            .table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .table thead th {
                background: #f8f9fa !important;
                color: #111111 !important;
                border: 1px solid #dee2e6 !important;
            }

            .table td,
            .table th {
                border: 1px solid #dee2e6 !important;
                color: #111111 !important;
            }

            .d-print-none,
            .btn,
            .sidebar,
            nav,
            header,
            footer {
                display: none !important;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .text-success {
                color: #198754 !important;
            }
        }
    </style>
@endsection
