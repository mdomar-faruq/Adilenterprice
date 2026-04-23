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
                            <li class="breadcrumb-item active fw-semibold text-primary">Sales</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">New Invoice</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sales.index') }}" class="btn btn-gradient text-white rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>Back To Index
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
            @csrf
            <div class="row">
                {{-- Left Column: Details and Items --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            {{-- Assignment Section --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Delivery Man</label>
                                    <select name="delivery_id" class="form-select select2" required>
                                        <option value="">Select Delivery Man</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sales Rep (SR)</label>
                                    <select name="sr_id" class="form-select select2" required>
                                        <option value="">Select SR</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Route No / Name</label>
                                    <input type="text" name="route_no" class="form-control" placeholder="e.g. Dhaka-A1"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Sale Date</label>
                                    <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Remarks</label>
                                    <input type="text" name="remarks" class="form-control"
                                        placeholder="Optional notes...">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle" id="saleTable">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th style="width: 40%">Product</th>
                                            <th style="width: 20%">Price</th>
                                            <th style="width: 15%">Qty</th>
                                            <th style="width: 20%">Total</th>
                                            <th class="text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="product_id[]"
                                                    class="form-select select2-products product-select" required>
                                                    <option value="" data-price="0" data-stock="0">Select Product
                                                    </option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-price="{{ $product->sale_price }}"
                                                            data-stock="{{ $product->stock }}">
                                                            {{ $product->name }} ({{ $product->stock }})
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
                                                <button type="button" class="btn btn-link text-danger removeRow"><i
                                                        class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill addRow">
                                    <i class="bi bi-plus me-1"></i> Add Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Billing Summary --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white p-3">
                            <h5 class="mb-0 fw-bold">Billing Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold" id="subtotalDisplay">0.00</span>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Discount</label>
                                <input type="number" name="discount" id="globalDiscount"
                                    class="form-control form-control-sm text-end" value="0" step="0.01">
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-top pt-2">
                                <h5 class="fw-bold">Grand Total</h5>
                                <h5 class="fw-bold text-primary" id="grandTotalDisplay">0.00</h5>
                                <input type="hidden" name="total_amount" id="totalAmountInput">
                            </div>

                            <div class="mb-3">
                                <label class="small fw-bold text-success">Paid Amount</label>
                                <input type="number" name="paid_amount" id="paidAmount"
                                    class="form-control text-end fw-bold" value="0" step="0.01">
                            </div>
                            <div class="d-flex justify-content-between mb-4 p-2  rounded">
                                <span class="fw-bold">Due Amount</span>
                                <span class="fw-bold text-danger" id="dueAmountDisplay">0.00</span>
                                <input type="hidden" name="due_amount" id="dueAmountInput">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow">
                                <i class="bi bi-printer me-2"></i>Save Invoice
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
            // --- 1. Select2 Initialization ---
            function initSelect2() {
                $('.select2, .product-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Select Product'
                });
            }
            initSelect2();

            // --- 2. Dynamic Rows ---
            $('.addRow').click(function() {
                let $firstRow = $('#saleTable tbody tr:first');

                // Destroy Select2 on the clone source to prevent ID conflicts
                $firstRow.find('.product-select').select2('destroy');

                let $newRow = $firstRow.clone();

                // Reset values in the new row
                $newRow.find('input').val(0);
                $newRow.find('.qty-input').val(1);
                $newRow.find('.line-total').val('0.00');
                $newRow.find('select').val('');

                $('#saleTable tbody').append($newRow);

                // Re-initialize Select2 for everyone
                initSelect2();
            });

            $(document).on('click', '.removeRow', function() {
                if ($('#saleTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateAll();
                }
            });

            // --- 3. Live Calculations & Duplicate Product Check ---
            $(document).on('change', '.product-select', function() {
                let currentSelect = $(this);
                let productId = currentSelect.val();
                let isDuplicate = false;

                if (productId) {
                    // Check against all other rows
                    $('.product-select').not(currentSelect).each(function() {
                        if ($(this).val() == productId) {
                            isDuplicate = true;
                            return false;
                        }
                    });
                }

                if (isDuplicate) {
                    // FIX: Close the dropdown immediately
                    currentSelect.select2('close');

                    // Reset values first
                    currentSelect.val('').trigger('change.select2');
                    currentSelect.closest('tr').find('.price-input').val('0.00');
                    currentSelect.closest('tr').find('.line-total').val('0.00');

                    // Small delay to ensure the UI has processed the close command
                    setTimeout(function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate Product',
                            text: 'This product is already in the list!',
                            confirmButtonColor: '#0d6efd'
                        });
                    }, 100);

                } else {
                    // Load Price logic
                    let selected = currentSelect.find(':selected');
                    let price = parseFloat(selected.data('price')) || 0;
                    currentSelect.closest('tr').find('.price-input').val(price.toFixed(2));
                }

                calculateAll();
            });

            // Re-calculate on input change
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

                $('#subtotalDisplay').text(subtotal.toFixed(2));
                $('#grandTotalDisplay').text(grandTotal.toFixed(2));
                $('#dueAmountDisplay').text(due.toFixed(2));

                $('#totalAmountInput').val(grandTotal.toFixed(2));
                $('#dueAmountInput').val(due.toFixed(2));
            }

            // --- 4. Stock Validation on Submit ---
            $('#saleForm').on('submit', function(e) {
                let valid = true;
                $('.qty-input').each(function() {
                    let row = $(this).closest('tr');
                    let stock = parseFloat(row.find('.product-select option:selected').data(
                        'stock')) || 0;
                    let qty = parseFloat($(this).val()) || 0;

                    if (qty > stock) {
                        valid = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Stock Out',
                            text: 'Quantity exceeds stock for ' + row.find(
                                '.product-select option:selected').text()
                        });
                        return false;
                    }
                });
                if (!valid) e.preventDefault();
            });
        });

        // --- 5. Real-time Stock Check on Input ---
        $(document).on('input change', '.qty-input', function() {
            let $input = $(this);
            let qty = parseFloat($input.val()) || 0;
            let $row = $input.closest('tr');
            let $selectedOption = $row.find('.product-select option:selected');

            // Get stock from data attribute
            let stock = parseFloat($selectedOption.data('stock')) || 0;
            let productName = $selectedOption.text().split('(')[0].trim(); // Gets name without stock count

            // Check if product is selected first
            if ($row.find('.product-select').val() === "") {
                if (qty > 0) {
                    $input.val(1);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Please select a product first',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
                return;
            }

            if (qty > stock) {
                // Visual feedback: briefly turn border red
                $input.addClass('is-invalid');

                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit Exceeded',
                    html: `You requested <b>${qty}</b> but only <b>${stock}</b> units of <b>${productName}</b> are available.`,
                    confirmButtonColor: '#0d6efd'
                });

                // Force value to maximum stock
                $input.val(stock);

                // Remove red border after a second
                setTimeout(() => {
                    $input.removeClass('is-invalid');
                }, 1500);

                // Recalculate totals since we changed the value
                calculateAll();
            } else {
                $input.removeClass('is-invalid');
            }
        });


        //same employee / duplicate employee 
        // --- Check Same Employee (Delivery Man vs SR) ---
        $(document).on('change', 'select[name="delivery_id"], select[name="sr_id"]', function() {
            let deliveryId = $('select[name="delivery_id"]').val();
            let srId = $('select[name="sr_id"]').val();
            let currentSelect = $(this);

            // Only compare if both have values selected
            if (deliveryId && srId && deliveryId === srId) {

                // 1. Force Select2 to close
                currentSelect.select2('close');

                // 2. Reset the value
                currentSelect.val('').trigger('change.select2');

                // 3. Show alert with a tiny delay
                setTimeout(function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Selection',
                        text: 'The Delivery Man and Sales Rep cannot be the same person!',
                        confirmButtonColor: '#0d6efd'
                    });
                }, 100);
            }
        });
    </script>
@endpush
