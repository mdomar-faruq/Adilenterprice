@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-none d-print-block mb-4 text-center">
            <h2 class="fw-bold text-uppercase border-bottom pb-2">DSR Account Statement</h2>
            <h4 class="mt-2">DSR: <span class="text-primary">{{ $customer->name }}</span></h4>
            <p class="text-muted small">Generated on: {{ date('d M, Y') }}</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div>
                <h2 class="fw-bold mb-1">DSR Ledger</h2>
                <p class="text-muted">Statement for <strong>{{ $customer->name }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary rounded-pill shadow-sm px-4">
                    <i class="bi bi-printer me-2"></i>Print Statement
                </button>
                <a href="{{ route('dsr.ledger') }}" class="btn btn-light border rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 d-print-none">
                <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4">
                    <div class="card-body bg-primary text-white p-4 text-center">
                        <small class=" text-uppercase fw-bold">Current Outstanding Balance</small>
                        <h2 class="fw-bold mb-0">
                            TK {{ number_format($ledger->last()->balance ?? $customer->opening_balance, 2) }}
                        </h2>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Initial Debt:</span>
                        <span class="fw-bold">TK {{ number_format($customer->opening_balance, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Total Transactions:</span>
                        <span class="fw-bold">{{ $ledger->count() }}</span>
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
                                    <th class="text-end pe-4 border-start">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-warning-subtle fw-semibold">
                                    <td class="ps-4 text-muted">---</td>
                                    <td><span class="badge bg-warning text-dark">START</span></td>
                                    <td>Opening Balance</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end pe-4 fw-bold border-start">
                                        Tk {{ number_format($customer->opening_balance, 2) }}
                                    </td>
                                </tr>

                                @foreach ($ledger as $row)
                                    <tr>
                                        <td class="ps-4">{{ date('d M, Y', strtotime($row->date)) }}</td>
                                        <td>
                                            <span
                                                class="small fw-bold {{ $row->type == 'Invoice' ? 'text-danger' : 'text-success' }}">
                                                {{ strtoupper($row->type) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $row->reference }}</td>
                                        <td class="text-end {{ $row->debit > 0 ? 'text-danger fw-bold' : '' }}">
                                            {{ $row->debit > 0 ? number_format($row->debit, 2) : '-' }}
                                        </td>
                                        <td class="text-end {{ $row->credit > 0 ? 'text-success fw-bold' : '' }}">
                                            {{ $row->credit > 0 ? number_format($row->credit, 2) : '-' }}
                                        </td>
                                        <td class="text-end pe-4 fw-bold border-start">
                                            TK {{ number_format($row->balance, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light d-none d-print-table-footer">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold py-3">Closing Balance:</td>
                                    <td class="text-end pe-4 fw-bold py-3">
                                        TK {{ number_format($ledger->last()->balance ?? $customer->opening_balance, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Printing Improvements */
        @page {
            size: auto;
            margin: 15mm;
        }

        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
            }

            .bg-dark {
                background-color: #333 !important;
                color: white !important;
            }

            .table-warning-subtle {
                background-color: #fff3cd !important;
            }

            .border-start {
                border-left: 2px solid #000 !important;
            }

            /* Hide UI junk */
            .d-print-none,
            .btn,
            .sidebar,
            nav,
            header {
                display: none !important;
            }

            .col-lg-9 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .table-warning-subtle {
            background-color: #fff9e6;
        }
    </style>
@endsection
