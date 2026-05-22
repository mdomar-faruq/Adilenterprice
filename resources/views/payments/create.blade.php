@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                {{-- Header Section --}}
                <div class="page-header mb-4">
                    <div class="row align-items-center">
                        {{-- Left Side: Breadcrumbs --}}
                        <div class="col">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 align-items-center">
                                    <li class="breadcrumb-item">
                                        <a href="/"
                                            class="text-decoration-none text-muted d-inline-flex align-items-center">
                                            <i class="bi bi-house-door me-2"></i><span>Home</span>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('payments.index') }}" class="text-decoration-none text-muted">DSR
                                            Payment Collection</a>
                                    </li>
                                    <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">
                                        Create
                                    </li>
                                </ol>
                            </nav>
                        </div>

                        {{-- Right Side: Back to Index Action Button --}}
                        <div class="col-auto">
                            <a href="{{ route('payments.index') }}"
                                class="btn btn-primary d-inline-flex align-items-center shadow-sm px-3 rounded-pill">
                                <i class="bi bi-arrow-left me-2"></i>
                                <span>Back to Index</span>
                            </a>
                        </div>
                    </div>
                </div>


                <form action="{{ route('payments.store') }}" method="POST" id="payment-form">
                    @csrf
                    <div class="row g-4">
                        <!-- Left Column: Inputs and Tables -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-uppercase small ls-1 text-primary">1.DSR Payment Collection
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Select DSR / Customer</label>
                                            <select name="customer_id" id="customer_id" class="form-select select2"
                                                required>
                                                <option value="">-- Search DSR --</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }} |
                                                        {{ $customer->designation }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Total Received Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white border-primary"><i
                                                        class="bi bi-currency-dollar"></i></span>
                                                <input type="number" id="auto_distribute_amount" name="total_received"
                                                    class="form-control border-primary fw-bold" step="0.01"
                                                    placeholder="Enter cash received" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Payment Method</label>
                                            <select name="payment_method" class="form-select" required>
                                                <option value="Cash">💵 Cash</option>
                                                <option value="Bank">🏦 Bank Transfer</option>
                                                <option value="BKash">📱 BKash / Mobile</option>
                                                <option value="Cheque">✍️ Cheque</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Payment Date</label>
                                            <input type="date" name="payment_date" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Reference/Trx No.</label>
                                            <input type="text" name="transaction_no" class="form-control"
                                                placeholder="Optional">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advance Payment Alert (Hidden by default) -->
                            <div id="advance-wrapper" style="display:none;" class="mb-4">
                                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-0">
                                    <i class="bi bi-piggy-bank-fill fs-3 me-3"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Advance Payment Detected</h6>
                                        <p class="small mb-2 text-dark">This amount exceeds total dues and will be saved to
                                            the customer's wallet.</p>
                                        <input type="number" name="advance_amount" id="advance_amount"
                                            class="form-control form-control-sm w-50 bg-white fw-bold text-success"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoices Table -->
                            <div class="card border-0 shadow-sm overflow-hidden" id="due-container" style="display:none;">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check me-2"></i>Outstanding
                                        Invoices & Opening Balance</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="due-table">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-4">Reference</th>
                                                <th>Remaining</th>
                                                <th style="width: 180px;" class="pe-4 text-end">Paying Now</th>
                                            </tr>
                                        </thead>
                                        <tbody id="due-tbody">
                                            <!-- Dynamic Content -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Summary Card -->
                        <div class="col-lg-4">
                            <div class="card border-1 shadow-sm" style="top: 20px;">
                                <div class="card-body p-4 text-center">
                                    <div class="mb-4">
                                        <div class="display-6 fw-bold text-dark" id="display_total_collection">0.00</div>
                                        <p class="text-muted text-uppercase small ls-1">Total Collection</p>
                                    </div>

                                    <div class="bg-light rounded p-3 mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Invoice Count</span>
                                            <span class="fw-bold small" id="count_selected">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">Post-Payment Due</span>
                                            <span class="fw-bold small text-danger"
                                                id="display_remaining_balance">0.00</span>
                                        </div>
                                    </div>

                                    <div class="mb-3 text-start">
                                        <label class="form-label small fw-bold">General Note</label>
                                        <textarea name="note" class="form-control" rows="3" placeholder="Add payment memo..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold py-3">
                                        Confirm & Process
                                    </button>

                                    <button type="reset"
                                        class="btn btn-link btn-sm text-decoration-none mt-3 text-muted"
                                        onclick="location.reload()">Reset Form</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .ls-1 {
            letter-spacing: 1px;
        }

        .pay-input {
            text-align: right;
            font-weight: bold;
            color: #0d6efd;
            border-color: #dee2e6;
        }

        .pay-input:focus {
            background-color: #f8f9ff;
        }

        .opening-row {
            background-color: #f0f7ff;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // 1. Initialize Select2
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            // 2. Load Dues when Customer is selected
            // $('#customer_id').change(function() {
            //     let customerId = $(this).val();
            //     if (!customerId) {
            //         $('#due-container, #advance-wrapper').hide();
            //         return;
            //     }

            //     $('#due-tbody').html(
            //         '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>'
            //     );
            //     $('#due-container').show();

            //     $.get(`/payments/pending-dues/${customerId}`, function(dues) {
            //         let rows = '';
            //         if (dues.length === 0) {
            //             rows =
            //                 '<tr><td colspan="3" class="text-center py-5 text-muted">No pending dues found.</td></tr>';
            //         } else {
            //             dues.forEach(due => {
            //                 let balance = (due.due_amount - (due.paid_amount || 0));
            //                 let isOpening = due.id === 'opening';
            //                 let inputName = isOpening ? `opening_balance_pay` :
            //                     `amounts[${due.id}]`;

            //                 rows += `
        //             <tr class="${isOpening ? 'opening-row' : ''}">
        //                 <td class="ps-4">
        //                     <div class="fw-bold ${isOpening ? 'text-primary' : 'text-dark'}">
        //                         ${isOpening ? '<i class="bi bi-info-circle me-1"></i>' : '#'} ${due.invoice_no}
        //                     </div>
        //                     <div class="text-muted extra-small">${new Date(due.created_at).toLocaleDateString()}</div>
        //                 </td>
        //                 <td><span class="badge ${isOpening ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'} px-2">${balance.toFixed(2)}</span></td>
        //                 <td class="pe-4 text-end">
        //                     <input type="number" name="${inputName}" 
        //                         class="form-control form-control-sm pay-input ms-auto" 
        //                         step="0.01" data-max="${balance}" placeholder="0.00">
        //                 </td>
        //             </tr>`;
            //             });
            //         }
            //         $('#due-tbody').html(rows);
            //         // Trigger auto-distribute if amount already exists
            //         $('#auto_distribute_amount').trigger('input');
            //     });
            // });

            // 2. Load Dues when Customer is selected
            $('#customer_id').change(function() {
                let customerId = $(this).val();
                if (!customerId) {
                    $('#due-container, #advance-wrapper').hide();
                    return;
                }

                $('#due-tbody').html(
                    '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>'
                );
                $('#due-container').show();

                $.get(`/payments/pending-dues/${customerId}`, function(dues) {
                    let rows = '';

                    // Filter out opening balance rows if they are empty or fully paid
                    let visibleDues = dues.filter(due => {
                        let balance = (due.due_amount - (due.paid_amount || 0));
                        if (due.id === 'opening' && balance <= 0) {
                            return false; // Skip this item entirely
                        }
                        return true; // Keep everything else
                    });

                    if (visibleDues.length === 0) {
                        rows =
                            '<tr><td colspan="3" class="text-center py-5 text-muted">No pending dues found.</td></tr>';
                    } else {
                        visibleDues.forEach(due => {
                            let balance = (due.due_amount - (due.paid_amount || 0));
                            let isOpening = due.id === 'opening';
                            let inputName = isOpening ? `opening_balance_pay` :
                                `amounts[${due.id}]`;

                            rows += `
                        <tr class="${isOpening ? 'opening-row' : ''}">
                            <td class="ps-4">
                                <div class="fw-bold ${isOpening ? 'text-primary' : 'text-dark'}">
                                    ${isOpening ? '<i class="bi bi-info-circle me-1"></i>' : '#'} ${due.invoice_no}
                                </div>
                                <div class="text-muted extra-small">${new Date(due.created_at).toLocaleDateString()}</div>
                            </td>
                            <td><span class="badge ${isOpening ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'} px-2">${balance.toFixed(2)}</span></td>
                            <td class="pe-4 text-end">
                                <input type="number" name="${inputName}" 
                                    class="form-control form-control-sm pay-input ms-auto" 
                                    step="0.01" data-max="${balance}" placeholder="0.00">
                            </td>
                        </tr>`;
                        });
                    }
                    $('#due-tbody').html(rows);
                    // Trigger auto-distribute if amount already exists
                    $('#auto_distribute_amount').trigger('input');
                });
            });

            // 3. Auto-Distribution and Advance Logic
            $('#auto_distribute_amount').on('input', function() {
                let totalReceived = parseFloat($(this).val()) || 0;
                let remainingToDistribute = totalReceived;

                // Reset all inputs
                $('.pay-input').val('');

                // Step 1: Distribute through the table (Opening Balance -> Oldest Invoice -> Newest)
                $('.pay-input').each(function() {
                    let maxCanPay = parseFloat($(this).data('max'));
                    if (remainingToDistribute > 0) {
                        if (remainingToDistribute >= maxCanPay) {
                            $(this).val(maxCanPay.toFixed(2));
                            remainingToDistribute -= maxCanPay;
                        } else {
                            $(this).val(remainingToDistribute.toFixed(2));
                            remainingToDistribute = 0;
                        }
                    }
                });

                // Step 2: Handle Advance Payment (Remaining balance after all dues)
                if (remainingToDistribute > 0.01) {
                    $('#advance_amount').val(remainingToDistribute.toFixed(2));
                    $('#advance-wrapper').fadeIn();
                } else {
                    $('#advance_amount').val('0.00');
                    $('#advance-wrapper').fadeOut();
                }

                calculateSummary();
            });

            // 4. Manual Adjustment inside table
            $(document).on('input', '.pay-input', function() {
                let max = parseFloat($(this).data('max'));
                if (parseFloat($(this).val()) > max) $(this).val(max);
                calculateSummary();
            });

            function calculateSummary() {
                let invoiceTotal = 0;
                let count = 0;
                let totalPossibleDues = 0;
                let advance = parseFloat($('#advance_amount').val()) || 0;

                $('.pay-input').each(function() {
                    let val = parseFloat($(this).val()) || 0;
                    let max = parseFloat($(this).data('max')) || 0;
                    invoiceTotal += val;
                    totalPossibleDues += max;
                    if (val > 0) count++;
                });

                let grandTotal = invoiceTotal + advance;
                $('#display_total_collection').text(grandTotal.toFixed(2));
                $('#count_selected').text(count);
                $('#display_remaining_balance').text((totalPossibleDues - invoiceTotal).toFixed(2));
            }
        });
    </script>

    <script>
        $(document).ready(function() {

            // --- 1. Handle Form Submission with Confirmation ---
            $('#payment-form').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let total = $('#display_total_collection').text();

                Swal.fire({
                    title: 'Confirm Payment?',
                    text: "You are about to collect " + total +
                        " TK. This will update the customer ledger.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Process It!',
                    cancelButtonText: 'Review'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Please wait while we update the records.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });

            // --- 2. Listen for Laravel Session Success/Error Messages ---
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#d33'
                });
            @endif

            // --- 3. Dynamic Alert for Advance Payment ---
            $('#auto_distribute_amount').on('input', function() {
                // ... existing logic ...
                if (remainingToDistribute > 0.01) {
                    // Small toast notification when advance is detected
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });

                    // We only show this toast once when it switches from 0 to advance
                    if ($('#advance_amount').val() == "0.00") {
                        Toast.fire({
                            icon: 'info',
                            title: 'Excess amount will be saved as Advance'
                        });
                    }

                    $('#advance_amount').val(remainingToDistribute.toFixed(2));
                    $('#advance-wrapper').fadeIn();
                } else {
                    $('#advance_amount').val('0.00');
                    $('#advance-wrapper').fadeOut();
                }
                calculateSummary();
            });
        });
    </script>
@endpush
