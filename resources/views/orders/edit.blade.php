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
                            <li class="breadcrumb-item active fw-semibold text-primary">Edit Order</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold mb-0">Modify Order: {{ $order->order_no }}</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('orders.index') }}" class="btn text-white btn-gradient rounded-pill px-4">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Back to Index
                    </a>
                </div>
            </div>
        </div>

        {{-- Use PUT method for Updates --}}
        <form action="{{ route('orders.update', $order->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ORDER NO</label>
                            <input type="text" name="order_no" class="form-control" value="{{ $order->order_no }}"
                                readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">CUSTOMER</label>
                            <select name="customer_id" class="form-control select2-basic" required>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ $order->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ORDER DATE</label>
                            <input type="date" name="order_date" class="form-control" value="{{ $order->order_date }}">
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
                            {{-- Loop through existing order items --}}
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <select name="product_id[]" class="form-control select2-item" required>
                                            <option value="">Select Product...</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" class="form-control text-center"
                                            value="{{ $item->qty }}" min="1">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeRow border-0">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent border-top-0 p-4 text-end">
                    <button type="submit" class="btn btn-info btn-lg px-5 shadow rounded-pill text-white">
                        <i class="bi bi-check-circle-fill me-2"></i>Update Production Order
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

{{-- Copy the same JS from your Create blade here --}}
@push('scripts')
    <script>
        $(document).ready(function() {
            initSelect2();
            updateProductOptions(); // Initialize disabled states on load

            $(document).on('click', '.addRow', function() {
                $('.select2-item').select2('destroy');
                let row = $('#orderTable tbody tr:first').clone();
                row.find('input').val(1);
                row.find('select').val('').trigger('change');
                $('#orderTable tbody').append(row);
                initSelect2();
                updateProductOptions();
            });

            $(document).on('click', '.removeRow', function() {
                if ($('#orderTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    updateProductOptions();
                }
            });

            $(document).on('change', '.select2-item', function() {
                updateProductOptions();
            });

            function updateProductOptions() {
                let selectedProducts = [];
                $('.select2-item').each(function() {
                    let val = $(this).val();
                    if (val) selectedProducts.push(val);
                });

                $('.select2-item').each(function() {
                    let currentSelect = $(this);
                    let currentVal = currentSelect.val();

                    currentSelect.find('option').each(function() {
                        let optionVal = $(this).val();
                        if (optionVal != "") {
                            if (selectedProducts.includes(optionVal) && optionVal != currentVal) {
                                $(this).attr('disabled', true);
                            } else {
                                $(this).attr('disabled', false);
                            }
                        }
                    });
                    currentSelect.select2({
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
