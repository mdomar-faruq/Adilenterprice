@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-2">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Invoice #{{ $sale->invoice_no }}
            </h4>
            <a href="{{ route('sales.index') }}" class="btn btn-gradient text-white border rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Back to Index
            </a>
        </div>

        <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="edit-sale-form">
            @csrf
            @method('PUT')

            {{-- Meta Data Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold">Sale Date</label>
                            <input type="date" name="sale_date" class="form-control rounded-3"
                                value="{{ $sale->sale_date }}" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold">SR</label>
                            <select name="sr_id" class="form-select select2 rounded-3" required>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $sale->sr_id == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold">Delivery Man</label>
                            <select name="delivery_id" class="form-select select2 rounded-3" required>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ $sale->delivery_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold">Route No</label>
                            <input type="text" name="route_no" class="form-control rounded-3"
                                value="{{ $sale->route_no }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3 border-0">
                    <h6 class="fw-bold mb-0">Invoice Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="items-table">
                            <thead class="">
                                <tr class="small text-uppercase text-muted">
                                    <th style="min-width: 250px;" class="ps-4">Product</th>
                                    <th style="min-width: 120px;">Unit Price</th>
                                    <th style="min-width: 100px;">Qty</th>
                                    <th style="min-width: 150px;">Subtotal</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale->items as $index => $item)
                                    <tr>
                                        <td class="ps-4">
                                            <select name="items[{{ $index }}][product_id]"
                                                class="form-select select2-products product-select" required>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-price="{{ $product->sale_price }}"
                                                        data-stock="{{ $product->stock + $item->quantity }}"
                                                        {{-- Add this --}}
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (Available:
                                                        {{ $product->stock + $item->quantity }})
                                                        {{-- Optional: Show stock name --}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text border-end-0">TK</span>
                                                <input type="number" step="0.01"
                                                    name="items[{{ $index }}][unit_price]"
                                                    class="form-control unit-price border-start-0"
                                                    value="{{ $item->unit_price }}" required>
                                            </div>
                                        </td>
                                        <td><input type="number" name="items[{{ $index }}][quantity]"
                                                class="form-control quantity" value="{{ $item->quantity }}" required></td>
                                        <td><input type="text" class="form-control subtotal fw-bold"
                                                value="{{ number_format($item->subtotal, 2) }}" readonly></td>
                                        <td class="text-center pe-4">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm border-0 remove-item"><i
                                                    class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" id="add-item-row">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
                        </button>
                    </div>
                </div>
            </div>

            {{-- Bottom Summary Section --}}
            <div class="row g-4">
                <div class="col-md-7">
                    {{-- Left blank or for internal notes --}}
                </div>
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0">Financial Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Total Discount</span>
                                <div class="w-50">
                                    <input type="number" step="0.01" name="discount" id="discount"
                                        class="form-control text-end" value="{{ $sale->discount }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Grand Total</span>
                                <h4 class="text-primary fw-bold mb-0">TK <span
                                        id="display_total">{{ number_format($sale->total_amount, 2) }}</span></h4>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-success fw-bold">Paid Amount</span>
                                <div class="w-50">
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount"
                                        class="form-control border-success text-end fw-bold"
                                        value="{{ $sale->paid_amount }}">
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-danger fw-bold">Balance Due</span>
                                <h4 class="text-danger fw-bold mb-0">TK <span
                                        id="display_due">{{ number_format($sale->due_amount, 2) }}</span></h4>
                            </div>
                            <input type="hidden" name="due_amount" id="hidden_due" value="{{ $sale->due_amount }}">
                        </div>
                        <div class="p-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Update Invoice
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
            let itemIndex = {{ $sale->items->count() }};

            function initSelect2() {
                $('.select2-products, .select2').select2({
                    width: '100%',
                    placeholder: 'Select Product',
                    theme: 'bootstrap-5'
                });
            }
            initSelect2();

            // 1. Add Dynamic Row
            $('#add-item-row').click(function() {
                let row = `<tr>
            <td class="ps-4">
                <select name="items[${itemIndex}][product_id]" class="form-select select2-products product-select" required>
                    <option value=""></option>
                    @foreach ($products as $p) 
                        <option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->stock }}">
                            {{ $p->name }} (Qty: {{ $p->stock }})
                        </option> 
                    @endforeach
                </select>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text border-end-0">TK</span>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control unit-price border-start-0" required>
                </div>
            </td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required min="1"></td>
            <td><input type="text" class="form-control subtotal fw-bold" readonly value="0.00"></td>
            <td class="text-center pe-4"><button type="button" class="btn btn-outline-danger btn-sm border-0 remove-item"><i class="bi bi-trash"></i></button></td>
        </tr>`;
                $('#items-table tbody').append(row);
                initSelect2();
                itemIndex++;
            });

            // 2. Product Change (Duplicate Check + Price Load + Stock Close Fix)
            $(document).on('change', '.product-select', function() {
                let currentSelect = $(this);
                let productId = currentSelect.val();
                let isDuplicate = false;

                if (productId) {
                    $('.product-select').not(currentSelect).each(function() {
                        if ($(this).val() == productId) {
                            isDuplicate = true;
                            return false;
                        }
                    });
                }

                if (isDuplicate) {
                    currentSelect.select2('close'); // Fix: Close box

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate Entry',
                            text: 'This product is already in the list!',
                        });
                    }, 100);

                    currentSelect.val(null).trigger('change.select2');
                    currentSelect.closest('tr').find('.unit-price').val('');
                } else {
                    let price = currentSelect.find(':selected').data('price') || 0;
                    currentSelect.closest('tr').find('.unit-price').val(price);
                }
                calculateInvoiceTotals();
            });

            // 3. Stock Quantity Check (Real-time)
            $(document).on('input', '.quantity', function() {
                let qty = parseFloat($(this).val()) || 0;
                let stock = parseFloat($(this).closest('tr').find('.product-select option:selected').data(
                    'stock')) || 0;

                // Note: For 'Edit', you might want to add existing item qty back to stock logic 
                // but usually, data-stock from controller handles current state.

                if (qty > stock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Insufficient Stock',
                        text: `Only ${stock} units available in stock.`,
                    });
                    $(this).val(stock); // Auto-correct to max stock
                }
                calculateInvoiceTotals();
            });

            $(document).on('input', '.unit-price, #discount, #paid_amount', function() {
                calculateInvoiceTotals();
            });

            $(document).on('click', '.remove-item', function() {
                if ($('#items-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateInvoiceTotals();
                }
            });

            function calculateInvoiceTotals() {
                let subtotalAccumulator = 0;
                $('.quantity').each(function() {
                    let tr = $(this).closest('tr');
                    let price = parseFloat(tr.find('.unit-price').val()) || 0;
                    let qty = parseFloat($(this).val()) || 0;
                    let sub = price * qty;
                    tr.find('.subtotal').val(sub.toFixed(2));
                    subtotalAccumulator += sub;
                });

                let disc = parseFloat($('#discount').val()) || 0;
                let final = subtotalAccumulator - disc;
                let paid = parseFloat($('#paid_amount').val()) || 0;
                let due = final - paid;

                $('#display_total').text(final.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#display_due').text(due.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#hidden_due').val(due.toFixed(2));
            }

            // 4. Final Validation on Form Submit
            $('#edit-sale-form').on('submit', function(e) {
                let valid = true;
                $('.quantity').each(function() {
                    let qty = parseFloat($(this).val()) || 0;
                    let stock = parseFloat($(this).closest('tr').find(
                        '.product-select option:selected').data('stock')) || 0;
                    if (qty > stock || qty <= 0) {
                        valid = false;
                        return false;
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    Swal.fire('Error', 'Please check stock quantities before submitting.', 'error');
                }
            });
        });
    </script>
@endpush
