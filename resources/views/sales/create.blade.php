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
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>Back
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
                                                <select name="product_id[]" class="form-select product-select" required>
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
                                                        class="bi bi-x-circle"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill addRow">
                                    <i class="bi bi-plus me-1"></i> Add Item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Billing Summary --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-dark text-white p-3">
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
                            <div class="d-flex justify-content-between mb-4 p-2 bg-light rounded">
                                <span class="fw-bold">Due Amount</span>
                                <span class="fw-bold text-danger" id="dueAmountDisplay">0.00</span>
                                <input type="hidden" name="due_amount" id="dueAmountInput">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow">
                                <i class="bi bi-printer me-2"></i>Save & Print
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
            // --- 1. Select2 ---
            function initSelect2() {
                $('.select2, .product-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
            initSelect2();

            // --- 2. Dynamic Rows ---
            $('.addRow').click(function() {
                let $firstRow = $('#saleTable tbody tr:first');
                $firstRow.find('.product-select').select2('destroy');

                let $newRow = $firstRow.clone();
                $newRow.find('input').val(0);
                $newRow.find('.qty-input').val(1);
                $newRow.find('.line-total').val('0.00');
                $newRow.find('select').val('').trigger('change');

                $('#saleTable tbody').append($newRow);
                initSelect2();
            });

            $(document).on('click', '.removeRow', function() {
                if ($('#saleTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateAll();
                }
            });

            // --- 3. Live Calculations ---
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

                $('#subtotalDisplay').text(subtotal.toFixed(2));
                $('#grandTotalDisplay').text(grandTotal.toFixed(2));
                $('#dueAmountDisplay').text(due.toFixed(2));

                $('#totalAmountInput').val(grandTotal.toFixed(2));
                $('#dueAmountInput').val(due.toFixed(2));
            }

            // --- 4. Form Submit ---
            $('#saleForm').on('submit', function(e) {
                let valid = true;
                $('.qty-input').each(function() {
                    let row = $(this).closest('tr');
                    let stock = parseFloat(row.find('.product-select option:selected').data(
                        'stock')) || 0;
                    let qty = parseFloat($(this).val()) || 0;
                    if (qty > stock) {
                        valid = false;
                        Swal.fire('Stock Out', 'Quantity exceeds stock for ' + row.find(
                            '.product-select option:selected').text(), 'error');
                        return false;
                    }
                });
                if (!valid) e.preventDefault();
            });
        });
    </script>

@endpush
