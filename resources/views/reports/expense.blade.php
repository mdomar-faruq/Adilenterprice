@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <!-- Filters Control Header Card (Hidden during Printing) -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('reports.expense') }}" method="GET" id="report-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Expense Category</label>
                            <select name="category" class="form-select">
                                <option value="">-- All Categories --</option>
                                @foreach ($available_categories as $cat)
                                    <option value="{{ $cat }}" {{ $category_search == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">To Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-filter-square me-2"></i>Generate Report
                            </button>
                            @if ($start_date || $end_date || $category_search)
                                <a href="{{ route('reports.expense') }}"
                                    class="btn btn-light border fw-bold text-secondary">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expense Ledger Card Table Wrapper -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area">
            <div
                class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-wallet2 me-2 text-primary"></i>Business Expense Ledger Statement
                    </h5>
                    <small class="text-muted no-print">Itemized business expenditure listings and operational debits</small>
                </div>

                <!-- Live Secondary Search Input -->
                <div class="d-flex align-items-center gap-2 no-print ms-md-auto w-100 w-md-auto">
                    <div class="input-group input-group-sm rounded-2" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="expenseLiveSearch" class="form-control border-start-0 ps-0"
                            placeholder="Quick search method, note...">
                    </div>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm fw-bold px-3">
                        <i class="bi bi-printer me-2"></i>Print Statement
                    </button>
                </div>
            </div>

            <!-- Custom Print Header Details (Visible on print sheets only) -->
            <div class="d-none d-print-block p-4 text-center border-bottom bg-light">
                <h2 class="fw-bold mb-1 text-dark">Adil Enterprise</h2>
                <h5 class="text-muted mb-2">Operational Expense Statement</h5>
                <span class="badge border border-secondary text-dark px-3 py-1 bg-white font-monospace">
                    Generated: {{ date('d M, Y H:i A') }} <br>
                    Data Window: {{ date('d M, Y', strtotime($start_date)) }} to {{ date('d M, Y', strtotime($end_date)) }}
                    @if ($category_search)
                        | Category Scope: "{{ $category_search }}"
                    @endif
                </span>
            </div>

            <!-- Table Structure -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase border-bottom">
                        <tr>
                            <th class="ps-4 py-3" style="width: 12%;">Date</th>
                            <th class="py-3" style="width: 20%;">Category</th>
                            <th class="py-3" style="width: 18%;">Payment Method</th>
                            <th class="py-3">Expense Note / Description</th>
                            <th class="text-end py-3 pe-4 text-dark bg-light" style="width: 15%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        @forelse($expenses as $exp)
                            <tr class="expense-row">
                                <td class="ps-4 font-monospace text-secondary">
                                    {{ date('d-m-Y', strtotime($exp->expense_date)) }}
                                </td>
                                <td>
                                    <span
                                        class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold text-uppercase font-monospace searchable-category">
                                        {{ $exp->category_name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 small text-capitalize searchable-method">
                                        {{ str_replace('_', ' ', $exp->payment_method ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="text-secondary small text-wrap searchable-note">
                                    {{ $exp->note ?? 'No expense notes or summary text captured.' }}
                                </td>
                                <td class="text-end pe-4 fw-bold text-danger bg-light row-amount">
                                    {{ number_format($exp->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x display-6 d-block mb-2"></i> No matching operational
                                    expenses discovered inside this date window.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($expenses->isNotEmpty())
                        <tfoot class="bg-dark text-white fw-bold table-group-divider align-middle">
                            <tr>
                                <td class="ps-4 py-3" colspan="4">TOTAL OPERATIONAL EXPENDITURE</td>
                                <td class="text-end pe-4 bg-danger text-white" style="font-size: 1.05rem;"
                                    id="totalExpenseSum">
                                    {{ number_format($expenses->sum('amount'), 2) }}
                                </td>
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

            .bg-dark {
                background-color: #212529 !important;
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
            // Live Search input to quickly isolate items on screen
            $('#expenseLiveSearch').on('keyup', function() {
                let val = $(this).val().toLowerCase().trim();
                let collectiveSum = 0;

                $('.expense-row').each(function() {
                    let cat = $(this).find('.searchable-category').text().toLowerCase();
                    let method = $(this).find('.searchable-method').text().toLowerCase();
                    let note = $(this).find('.searchable-note').text().toLowerCase();

                    if (cat.indexOf(val) > -1 || method.indexOf(val) > -1 || note.indexOf(val) > -
                        1) {
                        $(this).show();
                        collectiveSum += parseFloat($(this).find('.row-amount').text().replace(
                            /[^0-9.-]/g, '')) || 0;
                    } else {
                        $(this).hide();
                    }
                });

                // Update totals display dynamically based on visibility matching states
                if (val !== "") {
                    $('#totalExpenseSum').text(Number(collectiveSum).toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    }));
                } else {
                    $('#totalExpenseSum').text("{{ number_format($expenses->sum('amount'), 2) }}");
                }
            });
        });
    </script>
@endpush
