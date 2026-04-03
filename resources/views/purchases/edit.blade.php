@extends('layouts.app')
@include('purchases.edit_css')
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
                            <li class="breadcrumb-item active fw-semibold text-primary">Purchases</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Edit Purchase</h2>
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

        <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    {{-- Top Fields --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Purchase Reference No</label>
                            <input type="text" name="purchase_no" class="form-control fw-bold"
                                value="{{ $purchase->purchase_no }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Company / Vendor</label>
                            <select name="company_id" class="form-control" required>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ $purchase->company_id == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control"
                                value="{{ $purchase->purchase_date }}" required>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="purchaseTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 40%;">Product</th>
                                    <th style="width: 15%;">Qty</th>
                                    <th style="width: 15%;">Unit Price</th>
                                    <th style="width: 20%;">Subtotal</th>
                                    <th style="width: 10%; text-align: center;">
                                        <button type="button" class="btn btn-success btn-sm addRow rounded-circle">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->items as $item)
                                    <tr>
                                        <td>
                                            <select name="product_id[]" class="form-control select2-item" required>
                                                <option value="">Search Product...</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control qty"
                                                value="{{ $item->quantity }}" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="unit_price[]" class="form-control price"
                                                value="{{ $item->unit_price }}" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control subtotal"
                                                value="{{ number_format($item->subtotal, 2, '.', '') }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm removeRow border-0">
                                                <i class="bi bi-trash-fill fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Grand Total Section --}}
                    <div class="row justify-content-end mt-4">
                        <div class="col-md-4">
                            <div class="card border-0 rounded-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-secondary">GRAND TOTAL:</span>
                                        <div class="w-50">
                                            <input type="number" name="total_amount" id="grand_total"
                                                class="form-control form-control-lg border-0 bg-transparent fw-bold text-end text-primary p-0"
                                                value="{{ $purchase->total_amount }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end border-top-0 py-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> Update Purchase
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // 1. Initialize
            initSelect2();
            updateProductOptions();
            calculateGrandTotal();

            // 2. Add Row
            $('.addRow').on('click', function() {
                // Destroy Select2 to ensure clean cloning
                $('.select2-item').select2('destroy');

                let tr = $('tbody tr:first').clone();
                tr.find('input').val('');
                tr.find('.qty').val(1);
                tr.find('.subtotal').val('0.00');
                tr.find('select').val('').trigger('change');

                $('tbody').append(tr);
                initSelect2();
                updateProductOptions();
            });

            // 3. Remove Row
            $('tbody').on('click', '.removeRow', function() {
                if ($('tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateGrandTotal();
                    updateProductOptions();
                } else {
                    alert("At least one product is required.");
                }
            });

            // 4. Calculations Logic
            $('tbody').on('keyup change', '.qty, .price', function() {
                let tr = $(this).closest('tr');
                let qty = parseFloat(tr.find('.qty').val() || 0);
                let price = parseFloat(tr.find('.price').val() || 0);
                let subtotal = qty * price;
                tr.find('.subtotal').val(subtotal.toFixed(2));
                calculateGrandTotal();
            });

            // 5. Prevent Duplicate Products
            function updateProductOptions() {
                let selected = [];
                $('.select2-item').each(function() {
                    if ($(this).val()) selected.push($(this).val());
                });

                $('.select2-item').each(function() {
                    let current = $(this).val();
                    $(this).find('option').each(function() {
                        let optVal = $(this).val();
                        if (optVal !== "" && selected.includes(optVal) && optVal !== current) {
                            $(this).attr('disabled', true);
                        } else {
                            $(this).attr('disabled', false);
                        }
                    });
                });

                // Refresh Select2 view
                $('.select2-item').select2({
                    placeholder: "Search Product...",
                    width: '100%'
                });
            }

            $('tbody').on('change', '.select2-item', function() {
                updateProductOptions();
            });

            function calculateGrandTotal() {
                let total = 0;
                $('.subtotal').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#grand_total').val(total.toFixed(2));
            }

            function initSelect2() {
                $('.select2-item').select2({
                    placeholder: "Search for a product...",
                    width: '100%'
                });
                $('.select2-basic').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endpush
