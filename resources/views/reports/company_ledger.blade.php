@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <!-- Filter Management Header Wrapper Box -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('reports.company_ledger', $company->id) }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Statement Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Statement End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
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
        <div class="card border-0 shadow-sm overflow-hidden printable-area">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">Company Account Ledger Account</h5>
                    <span class="text-muted small fw-bold">Company Profile: <b
                            class="text-primary">{{ $company->name }}</b></span>
                </div>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 no-print fw-bold">
                    <i class="bi bi-printer me-2"></i>Print Ledger
                </button>
            </div>

            <!-- Print View Dynamic Branding Header Info -->
            <div class="d-none d-print-block text-center p-3 border-bottom bg-light">
                <h3 class="fw-bold mb-1">Adil Enterprise</h3>
                <p class="text-muted mb-0">Company Ledger: {{ $company->name }}</p>
                <small class="font-monospace text-secondary">Filtered Window: {{ date('d M, Y', strtotime($startDate)) }} to
                    {{ date('d M, Y', strtotime($endDate)) }}</small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3" style="width: 15%;">Date</th>
                            <th style="width: 25%;">Transaction Reference</th>
                            <th class="text-center" style="width: 15%;">Type</th>
                            <th class="text-end" style="width: 15%;">Debit (Paid)</th>
                            <th class="text-end" style="width: 15%;">Credit (Purchased)</th>
                            <th class="text-end pe-4" style="width: 15%;">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Important: Top Consolidated Balance Row -->
                        <tr class="table-info-subtle bg-light">
                            <td class="ps-4 text-muted font-monospace">{{ date('d-m-Y', strtotime($startDate)) }}</td>
                            <td colspan="2" class="fw-bold text-secondary italic">
                                <i class="bi bi-arrow-right-short me-1"></i> Opening Balance brought forward
                            </td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-end pe-4 fw-bold text-dark">
                                {{ number_format($historicalOpeningBalance, 2) }}
                            </td>
                        </tr>

                        <!-- Main Dynamic Transaction Mapping Iteration Loop -->
                        @forelse($filteredLedger as $row)
                            <tr>
                                <td class="ps-4 font-monospace">{{ date('d-m-Y', strtotime($row->date)) }}</td>
                                <td>
                                    @if ($row->reference)
                                        <span class="fw-bold text-dark font-monospace">{{ $row->reference }}</span>
                                    @else
                                        <span class="text-muted italic small text-uppercase">Payment
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
                                    class="text-end pe-4 font-monospace fw-bold {{ $row->balance >= 0 ? 'text-dark' : 'text-primary' }}">
                                    {{ number_format($row->balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
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
        .table-info-subtle {
            background-color: #f1f7fc !important;
        }

        .italic {
            font-style: italic;
        }

        @media print {
            body * {
                visibility: hidden;
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
                margin: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection
