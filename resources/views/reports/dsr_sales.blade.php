@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Filter Header Box -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form action="{{ route('reports.dsr_sales') }}" method="GET" id="report-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Select DSR / Employee</label>
                            <select name="dsr_id" class="form-select">
                                <option value="">-- All Representatives --</option>
                                @foreach ($dsr_list as $dsr)
                                    <option value="{{ $dsr->id }}" {{ $dsr_id == $dsr->id ? 'selected' : '' }}>
                                        {{ $dsr->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">From Date</label>
                            <input type="date" name="start_date" id="report_start_date" class="form-control"
                                value="{{ $start_date }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">To Date</label>
                            <input type="date" name="end_date" id="report_end_date" class="form-control"
                                value="{{ $end_date }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-funnel me-2"></i>Filter Statements
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ledger Table Output -->
        <div class="card border-0 shadow-sm overflow-hidden printable-area">
            <div
                class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-person-badge me-2 text-primary"></i>DSR Performance Sales Ledger
                    </h5>
                </div>

                <div class="d-flex align-items-center gap-2 no-print ms-md-auto w-100 w-md-auto">
                    <div class="input-group input-group-sm rounded-2" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="dsrLiveSearch" class="form-control border-start-0 ps-0"
                            placeholder="Quick search employee name...">
                    </div>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm fw-bold px-3">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>

            <!-- Print Brand Header -->
            <div class="d-none d-print-block text-center p-4 border-bottom bg-light">
                <h2 class="fw-bold mb-1 text-dark">Adil Enterprise</h2>
                <h4 class="text-muted mb-2">DSR Sales Performance Report</h4>
                <span class="badge border border-secondary text-dark px-3 py-1 bg-white font-monospace">
                    Period: {{ date('d M, Y', strtotime($start_date)) }} to {{ date('d M, Y', strtotime($end_date)) }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase border-bottom">
                        <tr>
                            <th class="ps-4 py-3">DSR / Employee Name</th>
                            <th class="text-center py-3">Invoiced</th>
                            <th class="text-center py-3">Sold Qty</th>
                            <th class="text-center text-danger py-3">Dmg Qty</th>
                            <th class="text-center py-3">Net Qty</th>
                            <th class="text-end py-3 text-danger">Discounts</th>
                            <th class="text-end py-3 text-warning">Extra Amount</th>
                            <th class="text-end py-3 text-dark bg-light fw-bold">Net Revenue</th>
                            <th class="text-center py-3 no-print" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        @forelse($report as $item)
                            @php $net_qty = $item->total_sold - $item->total_damage; @endphp
                            <tr class="dsr-row">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark searchable-name">{{ $item->dsr_name }}</span>
                                </td>
                                <td class="text-center row-orders" data-val="{{ $item->total_orders }}">
                                    {{ number_format($item->total_orders) }}
                                </td>
                                <td class="text-center row-sold" data-val="{{ $item->total_sold }}">
                                    {{ number_format($item->total_sold) }}
                                </td>
                                <td class="text-center text-danger row-dmg" data-val="{{ $item->total_damage }}">
                                    {{ number_format($item->total_damage) }}
                                </td>
                                <td class="text-center fw-bold row-netqty" data-val="{{ $net_qty }}">
                                    {{ number_format($net_qty) }}
                                </td>
                                <td class="text-end text-danger row-discount" data-val="{{ $item->total_discount }}">
                                    {{ number_format($item->total_discount, 2) }}
                                </td>
                                <td class="text-end text-warning row-extradsr" data-val="{{ $item->extra_dsr }}">
                                    {{ number_format($item->extra_dsr, 2) }}
                                </td>
                                <td class="text-end fw-bold text-success bg-light row-netrev"
                                    data-val="{{ $item->net_revenue }}">
                                    {{ number_format($item->net_revenue, 2) }}
                                </td>
                                <td class="text-center no-print">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-info px-2 py-1 btn-view-details fw-bold"
                                        data-id="{{ $item->delivery_id }}" data-name="{{ $item->dsr_name }}">
                                        <i class="bi bi-eye me-1"></i>Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x display-6 d-block mb-2"></i> No active records found for this
                                    period.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noDsrMatches" style="display: none;">
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-search display-6 d-block mb-2"></i> No employees match your search term.
                            </td>
                        </tr>
                    </tbody>
                    @if ($report->isNotEmpty())
                        <tfoot class="bg-dark text-white fw-bold table-group-divider align-middle">
                            <tr>
                                <td class="ps-4 py-3">GRAND TOTAL</td>
                                <td class="text-center py-3" id="footOrders">
                                    {{ number_format($report->sum('total_orders')) }}</td>
                                <td class="text-center py-3" id="footSold">
                                    {{ number_format($report->sum('total_sold')) }}</td>
                                <td class="text-center py-3 text-danger-subtle" id="footDmg">
                                    {{ number_format($report->sum('total_damage')) }}</td>
                                <td class="text-center py-3 bg-secondary" id="footNetQty">
                                    {{ number_format($report->sum('total_sold') - $report->sum('total_damage')) }}</td>
                                <td class="text-end py-3 text-danger" id="footDiscount">
                                    {{ number_format($report->sum('total_discount'), 2) }}</td>
                                <td class="text-end py-3 text-warning" id="footExtradsr">
                                    {{ number_format($report->sum('extra_dsr'), 2) }}</td>
                                <td class="text-end bg-success text-white" style="font-size: 1.05rem;" id="footNetRev">
                                    {{ number_format($report->sum('net_revenue'), 2) }}</td>
                                <td class="no-print bg-dark"></td>
                            </tr>
                            </footer>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Product Details Modal Layer Box -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="detailsModalLabel">
                        <i class="bi bi-box-seam me-2"></i>Sold Product Breakdown
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="text-secondary small d-block text-uppercase fw-bold">Representative</span>
                            <h4 id="modalDsrName" class="fw-bold text-dark mb-0">---</h4>
                        </div>
                        <div class="text-end">
                            <span class="text-secondary small d-block text-uppercase fw-bold">Statement Window</span>
                            <span id="modalDateRange"
                                class="badge bg-light text-dark border font-monospace px-3 py-2">---</span>
                        </div>
                    </div>

                    <!-- Live Product Search Bar -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="modalProductSearch" class="form-control border-start-0 ps-0"
                                placeholder="Search by product name or company...">
                        </div>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-striped align-middle mb-0" id="modalTable">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-3 py-2">Company Name</th>
                                    <th class="py-2">Product Name</th>
                                    <th class="text-center py-2">Net Qty</th>
                                    <th class="text-end pe-3 py-2">Line total</th>
                                </tr>
                            </thead>
                            <tbody id="modalTableBody">
                                <!-- Loaded dynamically via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Close</button>
                </div>
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

            .bg-success {
                background-color: #198754 !important;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // SWAL Form Submission Spinner
            $('#report-form').on('submit', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Analyzing Field Logs...',
                        html: 'Fetching sales data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            });

            // Live Filter & UI Footnote Metric Dynamic Aggregator
            $('#dsrLiveSearch').on('keyup', function() {
                let text = $(this).val().toLowerCase().trim();
                let matches = 0;

                let tOrders = 0,
                    tSold = 0,
                    tDmg = 0,
                    tNetQty = 0,
                    tDiscount = 0,
                    tExtradsr = 0,
                    tNetRev = 0;

                $('.dsr-row').each(function() {
                    let name = $(this).find('.searchable-name').text().toLowerCase();

                    if (name.indexOf(text) > -1) {
                        $(this).show();
                        matches++;

                        tOrders += parseFloat($(this).find('.row-orders').data('val')) || 0;
                        tSold += parseFloat($(this).find('.row-sold').data('val')) || 0;
                        tDmg += parseFloat($(this).find('.row-dmg').data('val')) || 0;
                        tNetQty += parseFloat($(this).find('.row-netqty').data('val')) || 0;
                        tDiscount += parseFloat($(this).find('.row-discount').data('val')) || 0;
                        tExtradsr += parseFloat($(this).find('.row-extradsr').data('val')) || 0;
                        tNetRev += parseFloat($(this).find('.row-netrev').data('val')) || 0;
                    } else {
                        $(this).hide();
                    }
                });

                if (matches === 0 && $('.dsr-row').length > 0) {
                    $('#noDsrMatches').show();
                    $('tfoot').hide();
                } else {
                    $('#noDsrMatches').hide();
                    $('tfoot').show();
                }

                $('#footOrders').text(Math.round(tOrders).toLocaleString());
                $('#footSold').text(Math.round(tSold).toLocaleString());
                $('#footDmg').text(Math.round(tDmg).toLocaleString());
                $('#footNetQty').text(Math.round(tNetQty).toLocaleString());
                $('#footDiscount').text(tDiscount.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#footExtradsr').text(tExtradsr.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#footNetRev').text(tNetRev.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            });

            // Modal Live Product Search Feature
            $('#modalProductSearch').on('keyup', function() {
                let text = $(this).val().toLowerCase().trim();
                let matches = 0;

                // Loop through table body items loaded from AJAX response context
                $('#modalTableBody tr').each(function() {
                    // Skip the "no records/no matches" row tags if they are evaluated
                    if ($(this).hasClass('no-search-row')) return;

                    let rowContent = $(this).text().toLowerCase();

                    if (rowContent.indexOf(text) > -1) {
                        $(this).show();
                        matches++;
                    } else {
                        $(this).hide();
                    }
                });

                // Display fallback alert row if search configuration results yield blank layouts
                if (matches === 0 && $('#modalTableBody tr').not('.no-search-row').length > 0) {
                    if ($('#noProductMatches').length === 0) {
                        $('#modalTableBody').append(`
                            <tr id="noProductMatches" class="no-search-row">
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-search d-block mb-1"></i> No matching products found.
                                </td>
                            </tr>
                        `);
                    } else {
                        $('#noProductMatches').show();
                    }
                } else {
                    $('#noProductMatches').hide();
                }
            });

            // Intercept Action Click Event for Fetching Details Async
            $('.btn-view-details').on('click', function() {
                let dsrId = $(this).data('id');
                let dsrName = $(this).data('name');
                let startDate = $('#report_start_date').val();
                let endDate = $('#report_end_date').val();

                // Clear previous search queries and reset modal states
                $('#modalProductSearch').val('');
                $('#modalDsrName').text(dsrName);
                $('#modalDateRange').text(startDate + ' to ' + endDate);
                $('#modalTableBody').html(
                    `<tr class="no-search-row"><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading items...</td></tr>`
                );

                // Show modal container box
                let targetModal = new bootstrap.Modal(document.getElementById('detailsModal'));
                targetModal.show();

                // Call AJAX Data Dispatcher
                $.ajax({
                    url: "{{ route('reports.get_dsr_sales_details') }}",
                    type: "GET",
                    data: {
                        dsr_id: dsrId,
                        start_date: startDate,
                        end_date: endDate
                    },
                    dataType: "json",
                    success: function(response) {
                        let rowsHtml = '';

                        if (response.length === 0) {
                            rowsHtml =
                                `<tr class="no-search-row"><td colspan="4" class="text-center py-4 text-muted">No items found for this record profile.</td></tr>`;
                        } else {
                            response.forEach(function(item) {
                                let companyText = item.company ? item.company :
                                    'Unassigned General Brand';
                                rowsHtml += `
                                    <tr>
                                        <td class="ps-3"><span class="badge bg-secondary-subtle text-secondary fw-bold border">${companyText}</span></td>
                                        <td class="fw-bold text-dark">${item.product_name}</td>
                                        <td class="text-center">${parseInt(item.units_sold).toLocaleString()}</td>
                                        <td class="text-end pe-3 font-monospace fw-bold">${parseFloat(item.line_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>`;
                            });
                        }
                        $('#modalTableBody').html(rowsHtml);
                    },
                    error: function() {
                        $('#modalTableBody').html(
                            `<tr class="no-search-row"><td colspan="4" class="text-center py-4 text-danger fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Failed to fetch sales ledger details.</td></tr>`
                        );
                    }
                });
            });
        });
    </script>
@endpush
