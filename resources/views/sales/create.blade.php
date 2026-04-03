@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Header Section --}}
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 align-items-center">
                            <li class="breadcrumb-item">
                                <a href="/" class="text-decoration-none text-muted d-inline-flex align-items-center">
                                    <i class="bi bi-house-door me-2"></i><span>Home</span>
                                </a>
                            </li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Sales</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Add New Sales</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sales.index') }}" class="btn btn-gradient rounded-pill text-white px-4">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Back to Index
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
            @csrf
            <div class="row">
                {{-- Left Column --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Customer</label>
                                    <select name="customer_id" class="form-select select2" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Sale Date</label>
                                    <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="saleTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 40%">Product</th>
                                            <th style="width: 20%">Price</th>
                                            <th style="width: 15%">Qty</th>
                                            <th style="width: 20%">Total</th>
                                            <th class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm addRow"><i
                                                        class="bi bi-plus-lg"></i></button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="product_id[]" class="form-select product-select" required>
                                                    <option value="" data-price="0" data-stock="0">Select Product
                                                    </option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-price="{{ $product->sale_price }}"
                                                            data-stock="{{ $product->stock }}">
                                                            {{ $product->name }} (Stock: {{ $product->stock }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="price[]"
                                                    class="form-control price-input text-end" value="0.00" step="0.01">
                                            </td>
                                            <td><input type="number" name="qty[]"
                                                    class="form-control qty-input text-center" value="1"
                                                    min="1"></td>
                                            <td><input type="text" class="form-control line-total text-end"
                                                    value="0.00" readonly></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm removeRow"><i
                                                        class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white p-3">
                            <h5 class="mb-0 fw-bold">Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span class="fw-bold" id="subtotalDisplay">0.00</span>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Discount ($)</label>
                                <input type="number" name="discount" id="globalDiscount" class="form-control text-end"
                                    value="0" step="0.01">
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <h5 class="fw-bold">Total</h5>
                                <h5 class="fw-bold text-primary" id="grandTotalDisplay">0.00</h5>
                                <input type="hidden" name="total_amount" id="totalAmountInput">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow">
                                <i class="bi bi-check-circle-fill me-2"></i>Complete Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // --- 1. Initialize Select2 Function ---
            function initSelect2(element = null) {
                const target = element ? element : $('.product-select, .select2');

                target.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Search...',
                    allowClear: true
                });
            }

            // Run on page load
            initSelect2();

            // --- 2. Dynamic Row Logic ---
            $('.addRow').click(function() {
                // Get the first row
                let $firstRow = $('#saleTable tbody tr:first');

                // Important: Destroy Select2 on the row before cloning to avoid ID conflicts
                $firstRow.find('.product-select').select2('destroy');

                // Clone the row
                let $newRow = $firstRow.clone();

                // Reset values in the new row
                $newRow.find('input').val(0);
                $newRow.find('.qty-input').val(1);
                $newRow.find('select').val('').trigger('change');

                // Append to table
                $('#saleTable tbody').append($newRow);

                // Re-initialize Select2 for BOTH the original row and the new row
                initSelect2();
            });

            $(document).on('click', '.removeRow', function() {
                if ($('#saleTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateAll();
                } else {
                    Swal.fire('Notice', 'At least one item is required.', 'info');
                }
            });

            // --- 3. Calculations ---
            $(document).on('change', '.product-select', function() {
                let selected = $(this).find(':selected');
                let price = parseFloat(selected.data('price')) || 0;

                $(this).closest('tr').find('.price-input').val(price.toFixed(2));
                calculateAll();
            });

            $(document).on('input', '.price-input, .qty-input, #globalDiscount, #paidAmount', function() {
                calculateAll();
            });

            function calculateAll() {
                let subtotal = 0;
                $('#saleTable tbody tr').each(function() {
                    let price = parseFloat($(this).find('.price-input').val()) || 0;
                    let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    let total = price * qty;
                    $(this).find('.line-total').val(total.toFixed(2));
                    subtotal += total;
                });

                let discount = parseFloat($('#globalDiscount').val()) || 0;
                let grandTotal = Math.max(0, subtotal - discount);
                let paid = parseFloat($('#paidAmount').val()) || 0;
                let due = grandTotal - paid;

                $('#subtotalDisplay').text(subtotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#grandTotalDisplay').text(grandTotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#dueAmountDisplay').text(due.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));

                $('#totalAmountInput').val(grandTotal.toFixed(2));
                $('#dueAmountInput').val(due.toFixed(2));
            }

            // --- 4. Submit & Stock Validation ---
            $('#saleForm').on('submit', function(e) {
                let valid = true;

                $('.qty-input').each(function() {
                    let row = $(this).closest('tr');
                    let stock = parseFloat(row.find('.product-select option:selected').data(
                        'stock')) || 0;
                    let qty = parseFloat($(this).val()) || 0;
                    let productName = row.find('.product-select option:selected').text();

                    if (qty > stock) {
                        valid = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Stock Out',
                            text: `Insufficient stock for ${productName}. Available: ${stock}`
                        });
                        return false; // Break loop
                    }
                });

                if (!valid) e.preventDefault();
            });
        });
    </script>
@endpush
