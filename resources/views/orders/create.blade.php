@extends('layouts.app')
@include('orders.css')

@section('content')
    <div class="container-fluid">
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 align-items-center">
                            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted"><i
                                        class="bi bi-house-door me-2"></i>Home</a></li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Orders</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold mb-0">Create Customer Order</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('orders.index') }}" class="btn text-white btn-gradient rounded-pill px-4">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Back to Index
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ORDER NO</label>
                            <input type="text" name="order_no" class="form-control" value="ORD-{{ date('ymdhi') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">CUSTOMER</label>
                            <select name="customer_id" class="form-control select2-basic" required>
                                <option value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ORDER DATE</label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <table class="table table-bordered align-middle" id="orderTable">
                        <thead>
                            <tr>
                                <th style="width: 70%">Product</th>
                                <th style="width: 20%">Quantity</th>
                                <th style="width: 10%" class="text-center">
                                    <button type="button" class="btn btn-success btn-sm addRow rounded-circle">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="product_id[]" class="form-control select2-item" required>
                                        <option value="">Select Product...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantity[]" class="form-control text-center" value="1"
                                        min="1">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm removeRow border-0">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent border-top-0 p-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow rounded-pill">
                        <i class="bi bi-save2-fill me-2"></i>Confirm Production
                        Order
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            initSelect2();

            // 1. Add New Row
            $(document).on('click', '.addRow', function() {
                $('.select2-item').select2('destroy'); // Destroy to clone clean HTML
                let row = $('#orderTable tbody tr:first').clone();

                row.find('input').val(1);
                row.find('select').val('').trigger('change');
                $('#orderTable tbody').append(row);

                initSelect2();
                updateProductOptions(); // Refresh disabled states
            });

            // 2. Remove Row
            $(document).on('click', '.removeRow', function() {
                if ($('#orderTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    updateProductOptions(); // Refresh disabled states
                }
            });

            // 3. On Product Change
            $(document).on('change', '.select2-item', function() {
                updateProductOptions();
            });

            // Main Function: Disable already selected products
            function updateProductOptions() {
                let selectedProducts = [];

                // Get all currently selected values
                $('.select2-item').each(function() {
                    let val = $(this).val();
                    if (val) selectedProducts.push(val);
                });

                // Loop through every select and disable options present in 'selectedProducts'
                $('.select2-item').each(function() {
                    let currentSelect = $(this);
                    let currentVal = currentSelect.val();

                    currentSelect.find('option').each(function() {
                        let optionVal = $(this).val();

                        if (optionVal != "") {
                            // Disable if selected elsewhere, but keep it enabled for the current select it belongs to
                            if (selectedProducts.includes(optionVal) && optionVal != currentVal) {
                                $(this).attr('disabled', true);
                            } else {
                                $(this).attr('disabled', false);
                            }
                        }
                    });

                    // Trigger Select2 to visually update the list
                    currentSelect.select2({
                        placeholder: "Search Product...",
                        width: '100%'
                    });
                });
            }

            function initSelect2() {
                $('.select2-item').select2({
                    placeholder: "Search Product...",
                    width: '100%'
                });
                $('.select2-basic').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endpush
