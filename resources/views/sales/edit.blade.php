@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header mb-4 pt-3">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Edit Sale: {{ $sale->invoice_no }}</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sales.index') }}" class="btn btn-gradient rounded-pill text-white px-4">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Back to Index
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="saleForm">
            @csrf
            @method('PUT')
            <div class="row">
                {{-- Left Column --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Customer</label>
                                    {{-- Basic Select2 for Customer --}}
                                    <select name="customer_id" class="form-select select2-basic" required>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Sale Date</label>
                                    <input type="date" name="sale_date" class="form-control"
                                        value="{{ $sale->sale_date }}">
                                </div>
                            </div>

                            <div class="table-responsive" style="overflow: visible;">
                                <table class="table table-hover align-middle" id="saleTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 45%">Product</th>
                                            <th style="width: 20%">Price</th>
                                            <th style="width: 15%">Qty</th>
                                            <th style="width: 20%">Total</th>
                                            <th class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm addRow">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sale->items as $item)
                                            <tr>
                                                <td>
                                                    <select name="product_id[]" class="form-select product-select" required>
                                                        <option value="">Search Product...</option>
                                                        @foreach ($products as $product)
                                                            @php
                                                                $currentInvoiceQty =
                                                                    $sale->items
                                                                        ->where('product_id', $product->id)
                                                                        ->first()->quantity ?? 0;
                                                                $availableForThisSale =
                                                                    $product->stock + $currentInvoiceQty;
                                                            @endphp
                                                            <option value="{{ $product->id }}"
                                                                {{ $item->product_id == $product->id ? 'selected' : '' }}
                                                                data-price="{{ $product->sale_price }}"
                                                                data-stock="{{ $availableForThisSale }}">
                                                                {{ $product->name }} (Avail: {{ $availableForThisSale }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" name="price[]"
                                                        class="form-control price-input text-end"
                                                        value="{{ $item->unit_price }}" step="0.01"></td>
                                                <td><input type="number" name="qty[]"
                                                        class="form-control qty-input text-center"
                                                        value="{{ $item->quantity }}" min="1"></td>
                                                <td><input type="text" class="form-control line-total text-end"
                                                        value="{{ number_format($item->subtotal, 2, '.', '') }}" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm removeRow">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column (Summary) --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold" id="subtotalDisplay">0.00</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Discount</label>
                                <input type="number" name="discount" id="globalDiscount" class="form-control text-end"
                                    value="{{ $sale->discount }}" step="0.01">
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="fw-bold">Grand Total</h5>
                                <h5 class="fw-bold text-primary" id="grandTotalDisplay">0.00</h5>
                                <input type="hidden" name="total_amount" id="totalAmountInput">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-success">Paid Amount</label>
                                <input type="number" name="paid_amount" id="paidAmount"
                                    class="form-control text-end border-success" value="{{ $sale->paid_amount }}"
                                    step="0.01" readonly>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold text-danger">Due Balance</span>
                                <span class="h5 fw-bold text-danger" id="dueAmountDisplay">0.00</span>
                                <input type="hidden" name="due_amount" id="dueAmountInput" readonly>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-3 shadow">Update Sale</button>
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

            // --- 1. Initialization Functions ---
            function initSelect2(element = null) {
                let target = element ? element : $('.product-select, .select2-basic');
                target.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Search...',
                    allowClear: true
                });
            }

            // --- 2. Calculation Logic ---
            function calculateTotals() {
                let subtotal = 0;

                $('.line-total').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });

                let discount = parseFloat($('#globalDiscount').val()) || 0;
                let grandTotal = subtotal - discount;
                let paid = parseFloat($('#paidAmount').val()) || 0;
                let due = grandTotal - paid;

                $('#subtotalDisplay').text(subtotal.toFixed(2));
                $('#grandTotalDisplay').text(grandTotal.toFixed(2));
                $('#totalAmountInput').val(grandTotal.toFixed(2));
                $('#dueAmountDisplay').text(due.toFixed(2));
                $('#dueAmountInput').val(due.toFixed(2));
            }

            // --- 3. Product Selection Logic ---
            $(document).on('select2:select', '.product-select', function(e) {
                let data = e.params.data.element;
                let price = $(data).data('price') || 0;
                let tr = $(this).closest('tr');

                tr.find('.price-input').val(price);
                updateLineTotal(tr);
            });

            $(document).on('input', '.qty-input, .price-input, #globalDiscount, #paidAmount', function() {
                let tr = $(this).closest('tr');
                if (tr.length) updateLineTotal(tr);
                calculateTotals();
            });

            function updateLineTotal(tr) {
                let qty = parseFloat(tr.find('.qty-input').val()) || 0;
                let price = parseFloat(tr.find('.price-input').val()) || 0;
                let total = qty * price;
                tr.find('.line-total').val(total.toFixed(2));
            }

            // --- 4. Dynamic Row Handling ---
            $('.addRow').on('click', function() {
                let firstRow = $('#saleTable tbody tr:first');

                // Destroy Select2 before cloning
                firstRow.find('.product-select').select2('destroy');

                let newRow = firstRow.clone();

                // Reset values
                newRow.find('input').val('');
                newRow.find('.qty-input').val(1);
                newRow.find('.line-total').val('0.00');
                newRow.find('select').val('').trigger('change');

                // Append and Re-init
                $('#saleTable tbody').append(newRow);
                initSelect2(firstRow.find('.product-select'));
                initSelect2(newRow.find('.product-select'));

                calculateTotals();
            });

            $(document).on('click', '.removeRow', function() {
                if ($('#saleTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotals();
                } else {
                    alert("At least one item is required.");
                }
            });

            // Initialize on Load
            initSelect2();
            calculateTotals();
        });
    </script>
@endpush
