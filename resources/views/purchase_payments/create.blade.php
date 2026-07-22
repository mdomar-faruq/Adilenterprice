@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="page-header mb-4">
            <div class="row align-items-center">
                {{-- Left Side: Breadcrumbs --}}
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 align-items-center">
                            <li class="breadcrumb-item">
                                <a href="/"
                                    class="text-decoration-none text-body-secondary d-inline-flex align-items-center">
                                    <i class="bi bi-house-door me-2"></i><span>Home</span>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('purchase_payments.index') }}"
                                    class="text-decoration-none text-body-secondary">Purchase Payments</a>
                            </li>
                            <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">Create
                            </li>
                        </ol>
                    </nav>
                </div>

                {{-- Right Side: Back to Index Action Button --}}
                <div class="col-auto">
                    <a href="{{ route('purchase_payments.index') }}"
                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-3 rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i>
                        <span>Back to Index</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 payment-form-card">
                    <div class="card-header rounded-top-4 py-3 border-0 payment-form-header">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-bank me-2"></i>Company Purchase Payment</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="purchasePaymentForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Company</label>
                                <select name="company_id" id="company_id" class="form-select select2" required>
                                    <option value="">Search Company...</option>
                                    @foreach ($companies as $company)
                                        @php
                                            $dueValue = (float) $company->calculated_due;
                                        @endphp
                                        <option value="{{ $company->id }}" data-due="{{ $dueValue }}">
                                            {{ $company->name }}
                                            @if ($dueValue < 0)
                                                (Advance Balance: TK {{ number_format(abs($dueValue), 2) }})
                                            @else
                                                (Due: TK {{ number_format($dueValue, 2) }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-3 border rounded-3 text-center bg-body-tertiary" id="due_container">
                                        <small class="text-body-secondary d-block" id="due_label">Current Total Due</small>
                                        <span class="h5 fw-bold text-danger" id="due_display">TK 0.00</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-3 border rounded-3 text-center bg-body-tertiary">
                                        <small class="text-body-secondary d-block">Paying Now</small>
                                        <span class="h5 fw-bold text-primary" id="paying_display">TK 0.00</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-3 text-center bg-body-tertiary">
                                        <small class="text-body-secondary d-block">New Bal. After Payment</small>
                                        <span class="h5 fw-bold text-secondary" id="remaining_display">TK 0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Amount to Pay</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">TK</span>
                                    <input type="number" name="amount" id="amount_input" class="form-control fw-bold"
                                        step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label small fw-bold">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Payment Method</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Check">Check</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Reference / Note</label>
                                <textarea name="note" class="form-control" rows="2"
                                    placeholder="Cheque number, reference details or transaction identity codes..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                                Confirm Payment to Company
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        .payment-form-card {
            overflow: hidden;
        }

        .payment-form-header {
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.14), rgba(var(--bs-info-rgb), 0.10));
            color: var(--bs-body-emphasis);
            border-bottom: 1px solid var(--bs-border-color);
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            min-height: 48px !important;
            background-color: #ffffff !important;
            color: #111111 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.8rem !important;
            box-shadow: none !important;
        }

        .select2-container--bootstrap-5 .select2-selection__rendered {
            color: #111111 !important;
            line-height: 46px !important;
            padding-left: 14px !important;
            background: transparent !important;
        }

        .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 46px !important;
        }

        .select2-container--bootstrap-5 .select2-selection__arrow b {
            border-color: #111111 transparent transparent transparent !important;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection--single {
            border-color: #4e73df !important;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.18) !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown,
        .select2-container--bootstrap-5 .select2-results__options,
        .select2-container--bootstrap-5 .select2-search__field {
            background-color: #ffffff !important;
            color: #111111 !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.9rem !important;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.22);
        }

        .select2-container--bootstrap-5 .select2-results__option {
            color: #111111 !important;
            padding: 0.7rem 0.9rem !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--selected,
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #e9ecef !important;
            color: #111111 !important;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 0.55rem !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__options,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
            background-color: #1f2328 !important;
            color: #ffffff !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__arrow b {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--selected,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #2b3035 !important;
            color: #ffffff !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Global tracker variable instances for balance parsing operations
            let standardCompanyDueBalance = 0;

            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }

            // Function helper calculation matrix layout generator 
            function updateVoucherDisplayBalances(paymentInputAmount) {
                // Formatting functions for currency output display values
                let formatCurrency = (val) => 'TK ' + parseFloat(val).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                $('#paying_display').text(formatCurrency(paymentInputAmount));

                // Calculate the final theoretical outcome: Current Due - What is being paid right now
                let postPaymentBalanceResult = standardCompanyDueBalance - paymentInputAmount;

                if (postPaymentBalanceResult < 0) {
                    $('#remaining_display').removeClass('text-danger text-secondary').addClass('text-success');
                    $('#remaining_display').text(formatCurrency(Math.abs(postPaymentBalanceResult)) + ' (Advance)');
                } else if (postPaymentBalanceResult > 0) {
                    $('#remaining_display').removeClass('text-success text-secondary').addClass('text-danger');
                    $('#remaining_display').text(formatCurrency(postPaymentBalanceResult));
                } else {
                    $('#remaining_display').removeClass('text-success text-danger').addClass('text-secondary');
                    $('#remaining_display').text(formatCurrency(0));
                }
            }

            // Trigger mapping updates upon dropdown mutations
            $('#company_id').on('change', function() {
                let selectedOption = $(this).find(':selected');

                if (!selectedOption.val()) {
                    standardCompanyDueBalance = 0;
                    $('#due_display').text('TK 0.00');
                    $('#amount_input').val('');
                    updateVoucherDisplayBalances(0);
                    return;
                }

                // Get raw unformatted decimal parameter from selection data array profiles
                standardCompanyDueBalance = parseFloat(selectedOption.data('due')) || 0;
                let formatCurrency = (val) => 'TK ' + parseFloat(val).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // UI adjustments for overpayment scenario values
                if (standardCompanyDueBalance < 0) {
                    $('#due_label').text('Advance Credit Available');
                    $('#due_container').removeClass('bg-body-tertiary').addClass('bg-success-subtle');
                    $('#due_display').removeClass('text-danger').addClass('text-success').text(
                        formatCurrency(Math.abs(standardCompanyDueBalance)));

                    // For safety, don't auto-fill payment input field values if they have a credit balance profile
                    $('#amount_input').val('');
                    updateVoucherDisplayBalances(0);
                } else {
                    $('#due_label').text('Current Total Due');
                    $('#due_container').removeClass('bg-success-subtle').addClass('bg-body-tertiary');
                    $('#due_display').removeClass('text-success').addClass('text-danger').text(
                        formatCurrency(standardCompanyDueBalance));

                    // Set default auto-fill options for positive balance due configurations
                    let roundedDueValue = Math.max(0, standardCompanyDueBalance).toFixed(2);
                    $('#amount_input').val(roundedDueValue);
                    updateVoucherDisplayBalances(roundedDueValue);
                }
            });

            // Handle typing and real-time numeric entry state mutations
            $('#amount_input').on('input', function() {
                let currentEnteredPaymentValue = parseFloat($(this).val()) || 0;
                updateVoucherDisplayBalances(currentEnteredPaymentValue);
            });

            // Master operational store procedure execution via ajax payload transmission hooks
            $('#purchasePaymentForm').on('submit', function(e) {
                e.preventDefault();

                let formActionAmountValue = parseFloat($('#amount_input').val()) || 0;
                if (formActionAmountValue <= 0) {
                    Swal.fire('Validation Warning', 'Payment total value input metric must exceed 0.00',
                        'warning');
                    return;
                }

                $.ajax({
                    url: "{{ route('purchase_payments.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Processed',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let fallbackErrorMessage =
                            'An internal system processing drop block context triggered.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            fallbackErrorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire('Transaction Dropped', fallbackErrorMessage, 'error');
                    }
                });
            });
        });
    </script>
@endpush
