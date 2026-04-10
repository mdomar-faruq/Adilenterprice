@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-pencil-square"></i> Edit Invoice #{{ $sale->invoice_no }}</h4>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="edit-sale-form">
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Sale Date</label>
                        <input type="date" name="sale_date" class="form-control" value="{{ $sale->sale_date }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SR</label>
                        <select name="sr_id" class="form-select select2" required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $sale->sr_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Delivery Man</label>
                        <select name="delivery_id" class="form-select select2" required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $sale->delivery_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Route No</label>
                        <input type="text" name="route_no" class="form-control" value="{{ $sale->route_no }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Invoice Items</h6>
                <button type="button" class="btn btn-sm btn-outline-light" id="add-item-row">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="items-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Product</th>
                            <th style="width: 15%">Unit Price</th>
                            <th style="width: 15%">Qty</th>
                            <th style="width: 20%">Subtotal</th>
                            <th style="width: 10%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $index => $item)
                        <tr>
                            <td>
                                <select name="items[{{$index}}][product_id]" class="form-select select2-products product-select" required>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                            data-price="{{ $product->price }}" 
                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="items[{{$index}}][unit_price]" class="form-control unit-price" value="{{ $item->unit_price }}" required></td>
                            <td><input type="number" name="items[{{$index}}][quantity]" class="form-control quantity" value="{{ $item->quantity }}" required></td>
                            <td><input type="text" class="form-control subtotal" value="{{ number_format($item->subtotal, 2) }}" readonly></td>
                            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-item"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5>Financial Summary</h5>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="{{ $sale->discount }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Grand Total</label>
                            <h3 class="text-primary" id="display_total">{{ number_format($sale->total_amount, 2) }}</h3>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-success fw-bold">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control border-success" value="{{ $sale->paid_amount }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-danger fw-bold">Balance Due</label>
                            <h3 class="text-danger" id="display_due">{{ number_format($sale->due_amount, 2) }}</h3>
                            <input type="hidden" id="hidden_due" value="{{ $sale->due_amount }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm h-100 border-danger">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Customer Due Allocation (Debt List)</h6>
                        <button type="button" class="btn btn-sm btn-light" id="add-customer-due">
                            <i class="bi bi-person-plus"></i> Add Customer
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0" id="due-distribution-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Due Amount</th>
                                    <th>Note</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->customerDues as $idx => $due)
                                <tr>
                                    <td>
                                        <select name="customer_dues[{{$idx}}][customer_id]" class="form-select select2-customers" required>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" {{ $due->customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" name="customer_dues[{{$idx}}][amount]" class="form-control customer-due-input" value="{{ $due->due_amount }}" required></td>
                                    <td><input type="text" name="customer_dues[{{$idx}}][note]" class="form-control" value="{{ $due->note }}"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="p-3 bg-light border-top d-flex justify-content-between">
                            <span class="fw-bold">Total Allocated:</span>
                            <span class="fw-bold h5 mb-0" id="allocated_total">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 pb-5">
            <button type="submit" class="btn btn-primary btn-lg w-100 shadow">Update Invoice & Save Changes</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = {{ $sale->items->count() }};
    let dueIndex = {{ $sale->customerDues->count() }};

    function initSelect2() {
        $('.select2-products').select2({ width: '100%', placeholder: 'Select Product' });
        $('.select2-customers').select2({ width: '100%', placeholder: 'Select Customer' });
    }
    initSelect2();

    // ----------------------------------------------------
    // PRODUCT ITEM LOGIC
    // ----------------------------------------------------
    $('#add-item-row').click(function() {
        let row = `<tr>
            <td><select name="items[${itemIndex}][product_id]" class="form-select select2-products product-select" required>
                <option value=""></option>
                @foreach($products as $p) <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }}</option> @endforeach
            </select></td>
            <td><input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control unit-price" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required></td>
            <td><input type="text" class="form-control subtotal" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-item"><i class="bi bi-trash"></i></button></td>
        </tr>`;
        $('#items-table tbody').append(row);
        initSelect2();
        itemIndex++;
    });

    $(document).on('change', '.product-select', function() {
        let price = $(this).find(':selected').data('price');
        $(this).closest('tr').find('.unit-price').val(price);
        calculateInvoiceTotals();
    });

    $(document).on('input', '.unit-price, .quantity, #discount, #paid_amount', function() {
        calculateInvoiceTotals();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        calculateInvoiceTotals();
    });

    function calculateInvoiceTotals() {
        let grandTotal = 0;
        $('.subtotal').each(function() {
            let tr = $(this).closest('tr');
            let price = parseFloat(tr.find('.unit-price').val()) || 0;
            let qty = parseFloat(tr.find('.quantity').val()) || 0;
            let sub = price * qty;
            $(this).val(sub.toFixed(2));
            grandTotal += sub;
        });

        let disc = parseFloat($('#discount').val()) || 0;
        let final = grandTotal - disc;
        let paid = parseFloat($('#paid_amount').val()) || 0;
        let due = final - paid;

        $('#display_total').text(final.toFixed(2));
        $('#display_due').text(due.toFixed(2));
        $('#hidden_due').val(due.toFixed(2));
        calculateAllocatedTotal();
    }

    // ----------------------------------------------------
    // CUSTOMER DUE LOGIC
    // ----------------------------------------------------
    $('#add-customer-due').click(function() {
        let row = `<tr>
            <td><select name="customer_dues[${dueIndex}][customer_id]" class="form-select select2-customers" required>
                <option value=""></option>
                @foreach($customers as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
            </select></td>
            <td><input type="number" step="0.01" name="customer_dues[${dueIndex}][amount]" class="form-control customer-due-input" required></td>
            <td><input type="text" name="customer_dues[${dueIndex}][note]" class="form-control"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
        </tr>`;
        $('#due-distribution-table tbody').append(row);
        initSelect2();
        dueIndex++;
    });

    $(document).on('input', '.customer-due-input', function() { calculateAllocatedTotal(); });
    $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); calculateAllocatedTotal(); });

    function calculateAllocatedTotal() {
        let sum = 0;
        $('.customer-due-input').each(function() { sum += parseFloat($(this).val()) || 0; });
        $('#allocated_total').text(sum.toFixed(2));

        let target = parseFloat($('#hidden_due').val()) || 0;
        if (Math.abs(sum - target) > 0.01) {
            $('#allocated_total').removeClass('text-success').addClass('text-danger');
        } else {
            $('#allocated_total').removeClass('text-danger').addClass('text-success');
        }
    }

    // Initial Calculation
    calculateInvoiceTotals();
});
</script>
@endpush
@endsection