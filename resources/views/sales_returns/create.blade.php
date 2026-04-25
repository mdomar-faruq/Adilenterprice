@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 align-items-center">
                            <li class="breadcrumb-item">
                                <a href="/" class="text-decoration-none text-muted d-inline-flex align-items-center">
                                    <i class="bi bi-house-door me-2"></i><span>Home</span>
                                </a>
                            </li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Sales Return</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Add New Sales Return</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sales_returns.index') }}" class="btn btn-gradient rounded-pill text-white px-4">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Back to Index
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form id="salesReturnForm">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">1. Select Customer</label>
                            <select name="customer_id" id="customer_id" class="form-select select2-search" required>
                                <option value="">Search Customer...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only products sold to this customer will appear.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Return Date</label>
                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div id="productSection" style="display:none;">
                        <table class="table table-hover align-middle" id="itemsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 45%">Product</th>
                                    <th style="width: 15%">Goode Qty</th>
                                    <th style="width: 15%">Damage Qty</th>
                                    <th style="width: 15%">Sales Price</th>
                                    <th style="width: 20%">Subtotal</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="addRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
                        </button>

                        <div class="row justify-content-end">
                            <div class="col-md-4">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-uppercase small">Total Return Amount</span>
                                            <h3 class="mb-0 text-primary fw-bold">$ <span id="totalText">0.00</span></h3>
                                        </div>
                                        <input type="hidden" name="total_amount" id="grandTotal" value="0">

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success text-white px-5 rounded-pill shadow-lg">
                                <i class="bi bi-check2-circle me-2"></i>Confirm & Submit Return
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let rowIdx = 0;
        let customerProducts = [];

        // Function to initialize Select2 with Bootstrap 5 theme
        function initSelect2(element) {
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $(element).data('placeholder'),
                allowClear: true,
                dropdownParent: $(element).closest('.modal').length ? $(element).closest('.modal') : $(document)
                    .body
            });
        }

        $(document).ready(function() {
            initSelect2('#customer_id');
        });

        // 1. Customer Change Logic
        $('#customer_id').on('change', function() {
            const customerId = $(this).val();
            if (!customerId) {
                $('#productSection').hide();
                $('#itemsTable tbody').empty();
                return;
            }

            $.get(`/customer/${customerId}/purchased-products`, function(data) {
                customerProducts = data;
                $('#itemsTable tbody').empty();

                if (customerProducts.length > 0) {
                    $('#productSection').show();
                    addRow(); // Start with one row
                } else {
                    $('#productSection').hide();
                    alert("This customer has no previous purchase history.");
                }
                calculate();
            });
        });

        // 2. Add Row Function (Select2 + Max Qty attributes)
        function addRow() {
            let options = `<option value="">Search Product...</option>`;
            customerProducts.forEach(p => {
                options +=
                    `<option value="${p.id}" data-price="${p.sales_price}" data-max="${p.total_bought}">${p.name} (Max: ${p.total_bought})</option>`;
            });

            let html = `
            <tr id="row${rowIdx}" class="animate__animated animate__fadeIn">
                <td>
                    <select name="items[${rowIdx}][product_id]" class="form-select product-select" data-placeholder="Choose Product" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][quantity]" class="form-control good_qty" value="1" min="1">
                    <small class="text-primary fw-bold qty-limit-msg"></small>
                </td>
                 <td>
                    <input type="number" name="items[${rowIdx}][quantity]" class="form-control damage_qty" value="1" min="1">
                    <small class="text-primary fw-bold qty-limit-msg"></small>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="items[${rowIdx}][unit_price]" class="form-control price" step="0.01">
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control subtotal fw-bold text-end" readonly value="0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(${rowIdx})">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </td>
            </tr>`;

            $('#itemsTable tbody').append(html);

            // Initialize Select2 for the new specific row
            const newSelect = $(`#row${rowIdx}`).find('.product-select');
            initSelect2(newSelect);

            rowIdx++;
        }

        // 3. Select2 Selection Logic (Duplicate Check + Price + Qty Limit)
        $(document).on('select2:select', '.product-select', function(e) {
            const currentSelector = $(this);
            const selectedId = currentSelector.val();
            const data = e.params.data.element; // The HTML element of the selected option
            const row = currentSelector.closest('tr');

            // Duplicate Validation
            let isDuplicate = false;
            $('.product-select').not(currentSelector).each(function() {
                if ($(this).val() == selectedId && selectedId !== "") isDuplicate = true;
            });

            if (isDuplicate) {
                alert("This product is already added!");
                currentSelector.val("").trigger('change');
                row.find('.price').val("");
                row.find('.qty').removeAttr('max');
                row.find('.qty-limit-msg').text('');
            } else {
                const price = $(data).data('price') || 0;
                const maxQty = $(data).data('max') || 0;

                row.find('.price').val(price);
                row.find('.qty').attr('max', maxQty);
                row.find('.qty-limit-msg').text(maxQty > 0 ? `Max: ${maxQty}` : '');
            }
            calculate();
        });

        // 4. Quantity Limit Validation on typing
        $(document).on('input', '.qty', function() {
            const input = $(this);
            const maxAllowed = parseFloat(input.attr('max')) || 0;
            const currentVal = parseFloat(input.val()) || 0;

            if (maxAllowed > 0 && currentVal > maxAllowed) {
                alert(`You cannot return more than the ${maxAllowed} units purchased.`);
                input.val(maxAllowed);
            }
            calculate();
        });

        $(document).on('input', '.price', function() {
            calculate();
        });

        // 5. Calculation Logic
        function calculate() {
            let grandTotal = 0;
            $('#itemsTable tbody tr').each(function() {
                let qty = parseFloat($(this).find('.qty').val()) || 0;
                let price = parseFloat($(this).find('.price').val()) || 0;
                let sub = qty * price;
                $(this).find('.subtotal').val(sub.toFixed(2));
                grandTotal += sub;
            });
            $('#totalText').text(grandTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            }));
            $('#grandTotal').val(grandTotal.toFixed(2));
        }

        // 6. Remove Row
        function removeRow(id) {
            $(`#row${id}`).remove();
            calculate();
        }

        // 7. Form Submission
        $('#salesReturnForm').on('submit', function(e) {
            e.preventDefault();
            if ($('#itemsTable tbody tr').length === 0) {
                alert("Your return list is empty!");
                return;
            }

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

            $.post("{{ route('sales_returns.store') }}", $(this).serialize())
                .done(function(res) {
                    if (res.success) window.location.href = "{{ route('sales_returns.index') }}";
                })
                .fail(function(xhr) {
                    alert(xhr.responseJSON?.message || "Server Error. Please try again.");
                    btn.prop('disabled', false).text('Submit Return');
                });
        });
    </script>
@endpush
