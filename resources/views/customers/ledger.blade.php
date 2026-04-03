@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-none d-print-block mb-4 text-center">
            <h2 class="fw-bold text-uppercase border-bottom pb-2">Customer Account Statement</h2>
            <h4 class="mt-2">Customer: <span class="text-primary">{{ $customer->name }}</span></h4>
            <p class="text-muted small">Generated on: {{ date('d M, Y') }}</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div>
                <h2 class="fw-bold text-dark mb-1">Customer Ledger</h2>
                <p class="text-muted">Statement for <strong>{{ $customer->name }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary rounded-pill shadow-sm px-4">
                    <i class="bi bi-printer me-2"></i>Print Table
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-light border rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 d-print-none">
                <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4">
                    <div class="card-body bg-primary text-white p-4 text-center">
                        <small class="text-white-50 text-uppercase fw-bold">Outstanding Balance</small>
                        <h2 class="fw-bold mb-0">
                            TK{{ number_format($ledger->last()->balance ?? $customer->opening_balance, 2) }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-print-12">
                <div class="card border-0 shadow-sm border-print-none shadow-print-none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="ledgerTable">
                            <thead class="bg-dark text-white text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th class="text-end">Debit (+)</th>
                                    <th class="text-end">Credit (-)</th>
                                    <th class="text-end pe-4">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-light fw-semibold">
                                    <td class="ps-4">---</td>
                                    <td colspan="2">Opening Balance</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end pe-4">Tk{{ number_format($customer->opening_balance, 2) }}</td>
                                </tr>
                                @foreach ($ledger as $row)
                                    <tr>
                                        <td class="ps-4">{{ date('d M, Y', strtotime($row->date)) }}</td>
                                        <td>{{ $row->type }}</td>
                                        <td class="text-muted small">{{ $row->reference ?? 'REF-' . $row->id }}</td>
                                        <td class="text-end {{ $row->debit > 0 ? 'text-danger fw-bold' : '' }}">
                                            {{ $row->debit > 0 ? number_format($row->debit, 2) : '-' }}
                                        </td>
                                        <td class="text-end {{ $row->credit > 0 ? 'text-success fw-bold' : '' }}">
                                            {{ $row->credit > 0 ? number_format($row->credit, 2) : '-' }}
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-dark">
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
        /* 1. Remove Browser Header (URL/Date) and Footer (Page Number) */
        @page {
            size: auto;
            margin: 10mm;
            /* Minimal margin for a clean look */
        }

        @media print {

            /* 2. Reset layout for full-page table width */
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
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

            /* 3. Make Table look professional on paper */
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                border: 1px solid #000 !important;
            }

            .table thead th {
                background-color: #000 !important;
                color: #fff !important;
                border: 1px solid #000 !important;
            }

            .table td {
                border: 1px solid #dee2e6 !important;
                color: #000 !important;
            }

            /* 4. Strictly hide everything else */
            .d-print-none,
            .btn,
            .sidebar,
            nav,
            header,
            footer,
            .main-footer {
                display: none !important;
            }

            /* Ensure text colors show up */
            .text-danger {
                color: #dc3545 !important;
            }

            .text-success {
                color: #198754 !important;
            }
        }
    </style>
@endsection
