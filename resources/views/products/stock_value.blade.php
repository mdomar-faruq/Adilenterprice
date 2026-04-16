@extends('layouts.app')

@section('content')
    <style>
        /* Custom Modal Design */
        .modal-content-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.03);
            transition: 0.3s;
        }

        .btn-details {
            transition: all 0.3s ease;
        }

        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
        }
    </style>

    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="fw-bold text-dark mb-1">Inventory Valuation</h2>
                <p class="text-muted small">Comprehensive stock value breakdown by company</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-white text-primary border border-primary-subtle rounded-pill px-4 py-2 shadow-sm">
                    <i class="bi bi-building me-1"></i> {{ $companyStockValues->count() }} Companies
                </span>
            </div>
        </div>

        {{-- Quick Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="small text-white-50 text-uppercase fw-bold mb-1">Total Warehouse Value</p>
                                <h3 class="fw-bold mb-0">TK {{ number_format($companyStockValues->sum('stock_value'), 2) }}
                                </h3>
                            </div>
                            <i class="bi bi-wallet2 fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="small text-muted text-uppercase fw-bold mb-1">Total Stock Quantity</p>
                                <h3 class="fw-bold mb-0">{{ number_format($companyStockValues->sum('total_qty')) }}</h3>
                            </div>
                            <i class="bi bi-box-seam fs-1 text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3">Company Details</th>
                            <th class="text-center py-3">Catalog Size</th>
                            <th class="text-center py-3">Total Inventory</th>
                            <th class="text-end py-3">Valuation</th>
                            <th class="text-center pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companyStockValues as $report)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-3 p-2 me-3">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">{{ $report['company_name'] }}</h6>
                                            <span class="text-muted x-small">Active Inventory</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ $report['total_items'] }}
                                        SKUs</span>
                                </td>
                                <td class="text-center fw-semibold text-dark">
                                    {{ number_format($report['total_qty'], 2) }}
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    <span class="text-muted small fw-normal">TK</span>
                                    {{ number_format($report['stock_value'], 2) }}
                                </td>
                                <td class="text-center pe-4">
                                    <button
                                        class="btn btn-sm btn-details btn-primary rounded-pill px-3 view-details shadow-sm"
                                        data-company="{{ $report['company_name'] }}"
                                        data-products='@json($report['products_list'])'>
                                        <i class="bi bi-search me-1"></i> Full Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Professional Modal --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-glass border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 p-4">
                    <div>
                        <h4 class="fw-bold mb-1" id="modalCompanyName">Inventory Details</h4>
                        <p class="text-muted small mb-0">List of all products and their current market value</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4">Product Name</th>
                                    <th class="text-center">Unit Price</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-end pe-4">Line Total</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody" class="border-top-0">
                                {{-- Data via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-4 rounded-bottom-4">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">Total Company Value:</h5>
                        <h4 class="fw-bold text-primary mb-0" id="modalFinalTotal">TK 0.00</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.view-details', function() {
            const companyName = $(this).data('company');
            const products = $(this).data('products');
            let rows = '';
            let grandTotal = 0;

            $('#modalCompanyName').html(`<i class="bi bi-building text-primary me-2"></i> ${companyName}`);

            products.forEach(product => {
                const productTotal = product.stock * product.purchase_price;
                grandTotal += productTotal;

                rows += `
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark">${product.name}</div>
                        <span class="text-muted x-small">Purchase Rate</span>
                    </td>
                    <td class="text-center text-muted">TK ${parseFloat(product.purchase_price).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border fw-semibold">${parseFloat(product.stock)}</span>
                    </td>
                    <td class="text-end pe-4 fw-bold text-dark">TK ${productTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                </tr>`;
            });

            $('#productTableBody').html(rows);
            $('#modalFinalTotal').text('TK ' + grandTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            }));
            $('#detailsModal').modal('show');
        });
    </script>
@endpush
