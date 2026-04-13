@extends('layouts.app')
@include('purchases.edit_css')
@section('content')
    <div class="container-fluid py-4">

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
                            <li class="breadcrumb-item active fw-semibold text-primary">Purchases</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Create New Purchase</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('purchases.index') }}"
                        class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center shadow-sm">
                        <i class="bi bi-arrow-left-circle-fill me-2 fs-5"></i>
                        <span class="fw-bold">Back to Index</span>
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            {{-- Master Info Section --}}
                            <div class="row g-3 mb-5 p-3 rounded-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Purchase Number</label>
                                    <input type="text" name="purchase_no" class="form-control" placeholder="PUR-1001"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Company / Supplier</label>
                                    <select name="company_id" class="form-control select2-basic" required>
                                        <option value="">Choose Supplier...</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Purchase Date</label>
                                    <input type="date" name="purchase_date" class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                            </div>



                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="purchaseTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 40%">Product Description</th>
                                            <th style="width: 15%">Quantity</th>
                                            <th style="width: 15%">Purchase Price</th>
                                            <th style="width: 20%">Total</th>
                                            <th style="width: 10%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="item-row">
                                            <td>
                                                <select name="product_id[]" class="form-control select2-item" required>
                                                    <option value="">Search Product...</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-price="{{ $product->purchase_price }}">
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control qty text-center"
                                                    value="1" min="1">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-transparent border-end-0">TK</span>
                                                    <input type="number" name="unit_price[]"
                                                        class="form-control price text-end border-start-0" step="0.01"
                                                        placeholder="0.00">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control subtotal text-end fw-bold"
                                                    readonly value="0.00">
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm border-0 removeRow">
                                                    <i class="bi bi-trash3 fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                {{-- Items Section --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 addRow">
                                        <i class="bi bi-plus-circle me-1"></i> Add Product
                                    </button>
                                </div>

                            </div>

                            {{-- Calculation Summary --}}
                            <div class="row justify-content-end mt-4">
                                <div class="col-md-4">
                                    {{-- Grand Total Card --}}
                                    <div class="card  border-0 rounded-4 mb-3 shadow-sm">
                                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                                            <span class="text-uppercase small fw-bold">Grand Total</span>
                                            <h3 class="mb-0 fw-bold">
                                                <input type="number" name="total_amount" id="grand_total"
                                                    class="form-control border-0 bg-transparent text-end fw-bold p-0"
                                                    readonly value="0.00"
                                                    style="font-size: 1.5rem; pointer-events: none; width: 150px;">
                                            </h3>
                                        </div>
                                    </div>

                                    {{-- Submit Button (Full Width in this column) --}}
                                    <button type="submit"
                                        class="btn btn-primary text-white btn-lg rounded-pill w-100 py-2 shadow-sm fw-bold">
                                        <i class="bi bi-check2-circle me-2"></i> Save Purchase
                                    </button>
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
        $(document).ready(function() {
            function initSelect2() {
                $('.select2-item').select2({
                    placeholder: "Search Product...",
                    width: '100%'
                });
                $('.select2-basic').select2({
                    width: '100%'
                });
            }
            initSelect2();

            // 1. Add Product Button (Outside table header)
            $('.addRow').on('click', function() {
                let $tableBody = $('#purchaseTable tbody');
                let $firstRow = $tableBody.find('tr:first');

                // Clone
                let $newRow = $firstRow.clone();

                // Clean Select2 and inputs
                $newRow.find('.select2-container').remove();
                $newRow.find('select').removeClass('select2-hidden-accessible').removeAttr(
                    'data-select2-id');
                $newRow.find('input').val('');
                $newRow.find('.qty').val(1);
                $newRow.find('.subtotal').val('0.00');
                $newRow.find('select').val('').trigger('change');

                $tableBody.append($newRow);
                initSelect2();
                updateProductOptions();
            });

            // 2. Remove Row
            $('tbody').on('click', '.removeRow', function() {
                if ($('#purchaseTable tbody tr').length > 1) {
                    $(this).closest('tr').fadeOut(200, function() {
                        $(this).remove();
                        calculateTotal();
                        updateProductOptions();
                    });
                }
            });

            // 3. Auto Price & Math
            $('tbody').on('change', '.select2-item', function() {
                let $tr = $(this).closest('tr');
                let price = $(this).find(':selected').data('price') || 0;
                $tr.find('.price').val(price);
                $tr.find('.qty').trigger('keyup');
                updateProductOptions();
            });

            $('tbody').on('keyup change', '.qty, .price', function() {
                let $tr = $(this).closest('tr');
                let qty = parseFloat($tr.find('.qty').val() || 0);
                let price = parseFloat($tr.find('.price').val() || 0);
                let subtotal = qty * price;
                $tr.find('.subtotal').val(subtotal.toFixed(2));
                calculateTotal();
            });

            // 4. Grand Total
            function calculateTotal() {
                let grandTotal = 0;
                $('.subtotal').each(function() {
                    grandTotal += parseFloat($(this).val() || 0);
                });
                $('#grand_total').val(grandTotal.toFixed(2));
            }

            // 5. Duplicate Selection Filter
            function updateProductOptions() {
                let selected = [];
                $('.select2-item').each(function() {
                    if ($(this).val()) selected.push($(this).val());
                });

                $('.select2-item').each(function() {
                    let current = $(this).val();
                    $(this).find('option').each(function() {
                        let val = $(this).val();
                        if (val && selected.includes(val) && val !== current) {
                            $(this).attr('disabled', true);
                        } else {
                            $(this).attr('disabled', false);
                        }
                    });
                });
            }
        });
    </script>
@endpush
