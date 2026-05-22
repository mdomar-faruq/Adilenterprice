@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <!-- Navigation Header Options -->
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
            <a href="{{ route('reports.all_dsr_due', ['start_date' => $start_date ?? '', 'end_date' => $end_date ?? '']) }}"
                class="btn btn-light border fw-bold text-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Statement Sheets
            </a>
            <button onclick="window.print()" class="btn btn-primary fw-bold">
                <i class="bi bi-printer me-2"></i>Print Ledger Log
            </button>
        </div>

        <!-- Printable Account History Record Card -->
        <div class="card border-0 shadow-sm overflow-hidden printable-ledger-card">

            <!-- Institutional Statement Branding Header -->
            <div class="p-4 text-center border-bottom bg-light">
                <h2 class="fw-bold mb-1 text-dark">Adil Enterprise</h2>
                <h4 class="fw-bold text-primary mb-2">Individual Account Statement Ledger</h4>

                <div class="mt-3">
                    <span class="badge border border-secondary text-dark px-3 py-2 bg-white font-monospace fs-7">
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
            <div class="card-body bg-white border-bottom p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-uppercase font-monospace text-muted d-block small">Account Holder Name</small>
                        <h4 class="fw-bold text-dark mb-0">{{ $customer->name ?? $employee->name }}</h4>
                        <span class="text-muted extra-small">Registry Master Reference Code:
                            #{{ $customer->id ?? $employee->id }}</span>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <small class="text-uppercase font-monospace text-muted d-block small">Contact Identity
                            Information</small>
                        <div class="fw-bold text-dark fs-5">
                            <i
                                class="bi bi-telephone me-1 text-muted"></i>{{ $customer->phone ?? ($employee->phone ?? 'Unavailable') }}
                        </div>
                        <small class="text-muted">Generated Data Log Stamp: {{ date('d M, Y h:i A') }}</small>
                    </div>
                </div>
            </div>

            <!-- Transaction Logs Data Matrix Table Grid -->
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light text-secondary small text-uppercase font-monospace border-bottom">
                        <tr>
                            <th class="ps-4 py-3" style="width: 15%;">Posting Date</th>
                            <th class="py-3" style="width: 35%;">Transaction Reference Identity</th>
                            <th class="text-end py-3 text-danger" style="width: 15%;">Debit (+)</th>
                            <th class="text-end py-3 text-success" style="width: 15%;">Credit (-)</th>
                            <th class="text-end pe-4 py-3 text-dark bg-light-subtle" style="width: 20%;">Running Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="border-0 font-monospace">
                        @forelse($ledger as $item)
                            <tr>
                                <td class="ps-4 text-secondary">{{ date('d M, Y', strtotime($item->date)) }}</td>
                                <td>
                                    <span class="fw-bold text-dark d-block font-sans-serif">{{ $item->reference }}</span>
                                    <span
                                        class="badge border bg-white text-dark extra-small py-0 px-1">{{ $item->type }}</span>
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
                                <td colspan="5" class="text-center py-5 text-muted font-sans-serif">
                                    <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary"></i>
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
            body * {
                visibility: hidden;
                background: none !important;
            }

            .printable-ledger-card,
            .printable-ledger-card * {
                visibility: visible;
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

            .no-print {
                display: none !important;
            }

            .table td,
            .table th {
                padding: 12px 10px !important;
                border-bottom: 1px solid #dee2e6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection
