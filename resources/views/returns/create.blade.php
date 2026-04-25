@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Return</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">New Return</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('returns.index') }}" class="btn btn-gradient text-white rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>Back To Index
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('returns.store') }}" method="POST" id="returnForm">
            @csrf

            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Return Type</label>
                                    <select name="type" id="returnType"
                                        class="form-select border-primary fw-bold shadow-sm" required>
                                        <option value="sales_return" >SALES RETURN (Stock IN)</option>
                                        <option value="purchase_return" selected>PURCHASE RETURN (Stock OUT)</option>
                                    </select>
                                    <small class="text-muted">Changes product prices automatically</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-muted">DSR (Optonal)</label>
                                    <select name="dsr_id" class="form-select select2-js">
                                        <option value="">Select DSR</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Return Date</label>
                                    <input type="date" name="return_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Products List</h5>

                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="itemsTable">
                                    <thead>
                                        <tr class="text-uppercase text-xs">
                                            <th style="min-width: 300px;" class="ps-4">Product Name</th>
                                            <th style="width: 130px;" class="text-center">Good Qty</th>
                                            <th style="width: 130px;" class="text-center">Damage Qty</th>
                                            <th style="width: 150px;" class="text-end">Unit Price</th>
                                            <th style="width: 180px;" class="text-end pe-4">Subtotal</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemRows">
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill m-2" onclick="addRow()">
                                    <i class="bi bi-plus-lg me-1"></i>Add Product
                                </button>
                            </div>
                        </div>

                        <div class="card-footer border-top-0 p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-7 col-md-6 mb-4 mb-md-0">
                                    <div class="d-flex align-items-start">
                                        <div class= "p-2 rounded-circle shadow-sm me-3">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label fw-bold text-dark small mb-1">Transaction
                                                Remarks</label>
                                            <textarea name="remarks" class="form-control border-0 shadow-sm" rows="2" style="resize: none;"
                                                placeholder="Enter any specific details about this return..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-5 col-md-6">
                                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                        <div class="card-body p-0">
                                            <div class="p-3 d-flex justify-content-between align-items-center">
                                                <span class="text-muted fw-medium">Grand Total</span>
                                                <h3 class="fw-bold text-primary mb-0">
                                                    <small class="fs-6 fw-normal text-dark me-1">TK</small><span
                                                        id="grandTotalDisplay">0.00</span>
                                                </h3>
                                            </div>

                                            <div class="p-2 pt-0">
                                                <input type="hidden" name="total_amount" id="totalAmountInput"
                                                    value="0">
                                                <button type="submit"
                                                    class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-cloud-check-fill me-2"></i>
                                                    Confirm & Save Return
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-center text-muted small mt-2 mb-0">
                                        <i class="bi bi-info-circle me-1"></i> Stock will be adjusted automatically upon
                                        saving.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let rowCount = 0;

        $(document).ready(function() {
            // Init Global Select2
            $('.select2-js').select2({
                theme: 'bootstrap-5'
            });

            // Add initial row
            addRow();

            // Listen for Return Type changes to update ALL prices in the table
            $('#returnType').on('change', function() {
                $('.product-select').each(function() {
                    if ($(this).val()) {
                        let id = $(this).closest('tr').attr('id').split('_')[1];
                        updateRowPrice($(this), id);
                    }
                });
            });
        });

        // Ensure rowCount is incremented BEFORE creating the string
        function addRow() {
            rowCount++;
            const html = `
    <tr id="row_${rowCount}" class="item-row">
        <td>
            <select name="items[${rowCount}][product_id]" class="form-select select2-item product-select" required onchange="handleProductSelection(this, ${rowCount})">
                <option value="">Search Product...</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" data-sell="{{ $p->sale_price }}" data-buy="{{ $p->purchase_price }}">
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[${rowCount}][good_qty]" class="form-control text-center qty-input" value="0" min="0" oninput="calculateRow(${rowCount})"></td>
        <td><input type="number" name="items[${rowCount}][damage_qty]" class="form-control text-center qty-input" value="0" min="0" oninput="calculateRow(${rowCount})"></td>
        <td><input type="number" name="items[${rowCount}][price]" class="form-control text-end price-input fw-bold" step="0.01" value="0" oninput="calculateRow(${rowCount})"></td>
        <td class="pe-4"><input type="text" class="form-control text-end subtotal-input fw-bold" value="0.00" readonly></td>
        <td class="pe-3">
            <button type="button" class="btn btn-outline-danger border-0 btn-sm" onclick="removeRow(${rowCount})">
                <i class="bi bi-trash3 text-danger"></i>
            </button>
        </td>
    </tr>`;
            $('#itemRows').append(html);

            // Re-initialize Select2 for the new row
            $(`#row_${rowCount} .select2-item`).select2({
                theme: 'bootstrap-5'
            });
        }

        function handleProductSelection(select, id) {
            let productId = $(select).val();
            let isDuplicate = false;

            // Check for duplicates
            $('.product-select').not(select).each(function() {
                if ($(this).val() === productId && productId !== "") isDuplicate = true;
            });

            if (isDuplicate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Duplicate',
                    text: 'You have already added this product to the list.',
                    confirmButtonColor: '#0d6efd'
                });
                $(select).val('').trigger('change.select2');
                return;
            }

            // Apply Price
            updateRowPrice($(select), id);
        }

        function updateRowPrice(selectElement, id) {
            const type = $('#returnType').val();
            const selected = selectElement.find(':selected');

            const sellPrice = parseFloat(selected.data('sell')) || 0;
            const buyPrice = parseFloat(selected.data('buy')) || 0;

            // Auto-select price based on Return Type
            const targetPrice = (type === 'sales_return') ? sellPrice : buyPrice;

            $(`#row_${id} .price-input`).val(targetPrice.toFixed(2));
            calculateRow(id);
        }

        function calculateRow(id) {
            const row = $(`#row_${id}`);
            const good = parseFloat(row.find('input[name*="good_qty"]').val()) || 0;
            const damage = parseFloat(row.find('input[name*="damage_qty"]').val()) || 0;
            const price = parseFloat(row.find('.price-input').val()) || 0;

            // Total Qty * Price = Subtotal
            const subtotal = (good + damage) * price;
            row.find('.subtotal-input').val(subtotal.toFixed(2));

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            $('.subtotal-input').each(function() {
                grandTotal += parseFloat($(this).val()) || 0;
            });
            $('#grandTotalDisplay').text(grandTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            }));
            $('#totalAmountInput').val(grandTotal.toFixed(2));
        }

        function removeRow(id) {
            if ($('#itemRows tr').length > 1) {
                $(`#row_${id}`).remove();
                calculateGrandTotal();
            } else {
                Swal.fire({
                    icon: 'info',
                    text: 'You need at least one item row.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }
    </script>

    <style>
        .text-xs {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .qty-input {
            min-width: 100px;
        }

        .price-input {
            min-width: 120px;
            color: #0d6efd;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
        }

        .item-row:hover {
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {

            .btn-primary,
            .btn-light {
                width: 100%;
            }

            .qty-input {
                min-width: 80px;
            }
        }
    </style>
@endpush
