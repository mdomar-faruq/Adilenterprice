@extends('layouts.app')

@include('purchases.create_css')
@section('content')
    <div class="container-fluid ">
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
                    <h2 class="h3 fw-bold mb-0">Add New Purchase</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('purchases.index') }}" class="btn btn-gradient rounded-pill px-4">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Back to Index
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('purchases.store') }}" method="POST">
            @csrf
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    {{-- Master Data Row --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Purchase No</label>
                            <input type="text" name="purchase_no" class="form-control" placeholder="e.g. PUR-1001"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Company / Vendor</label>
                            <select name="company_id" class="form-control select2-basic" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border" id="purchaseTable">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Product</th>
                                    <th style="width: 15%">Qty</th>
                                    <th style="width: 15%">Unit Price</th>
                                    <th style="width: 20%">Subtotal</th>
                                    <th style="width: 10%" class="text-center">
                                        <button type="button"
                                            class="btn btn-success btn-sm addRow rounded-circle shadow-sm">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="product_id[]" class="form-control select2-item" required>
                                            <option value="">Search Product...</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="quantity[]" class="form-control qty text-center"
                                            value="1" min="1"></td>
                                    <td><input type="number" name="unit_price[]" class="form-control price text-end"
                                            step="0.01" placeholder="0.00"></td>
                                    <td><input type="text" class="form-control subtotal text-end bg-light" readonly
                                            value="0.00"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeRow border-0">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Grand Total --}}
                    <div class="row justify-content-end mt-4">
                        <div class="col-md-4">
                            <div class="card border-0 rounded-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-secondary">GRAND TOTAL:</span>
                                        <div class="w-50">
                                            <input type="number" name="total_amount" id="grand_total"
                                                class="form-control form-control-lg border-0 bg-transparent fw-bold text-end text-primary p-0"
                                                value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="card-footer bg-transparent border-top-0 p-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow rounded-pill">
                        <i class="bi bi-save2-fill me-2"></i>Save Purchase
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
            $('.addRow').on('click', function() {
                $('.select2-item').select2('destroy'); // Prevent Select2 clone bugs

                let tr = $('#purchaseTable tbody tr:first').clone();
                tr.find('input').val('');
                tr.find('.qty').val(1);
                tr.find('.subtotal').val('0.00');
                tr.find('select').val('').trigger('change');

                $('#purchaseTable tbody').append(tr);
                initSelect2();
                updateProductOptions();
            });

            // 2. Remove Row
            $('tbody').on('click', '.removeRow', function() {orders
                if ($('#purchaseTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotal();
                    updateProductOptions();
                }
            });

            // 3. Real-time Math
            $('tbody').on('keyup change', '.qty, .price', function() {
                let tr = $(this).closest('tr');
                let qty = parseFloat(tr.find('.qty').val() || 0);
                let price = parseFloat(tr.find('.price').val() || 0);
                let subtotal = qty * price;
                tr.find('.subtotal').val(subtotal.toFixed(2));
                calculateTotal();
            });

            // 4. Grand Total Calculation
            function calculateTotal() {
                let grandTotal = 0;
                $('.subtotal').each(function() {
                    grandTotal += parseFloat($(this).val() || 0);
                });
                $('#grand_total').val(grandTotal.toFixed(2));
            }

            // 5. Duplicate Prevention
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
                initSelect2(); // Refresh UI
            }

            $('tbody').on('change', '.select2-item', function() {
                updateProductOptions();
            });

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
