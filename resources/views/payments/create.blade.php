@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold text-primary">Money Receipt</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('payments.index') }}" class="btn btn-gradient text-white rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Back Index
                        </a>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold text-dark">DSR Payment Collection</h4>
                        <p class="text-muted small mb-0">Manage debt collection and automatic invoice distribution.</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary-subtle text-primary p-2 px-3">
                            <i class="bi bi-calendar3 me-1"></i> {{ date('D, M d, Y') }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('payments.store') }}" method="POST" id="payment-form">
                    @csrf
                    <div class="row g-4">

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3">1. Payment Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Select Customer</label>
                                            <select name="customer_id" id="customer_id" class="form-select select2"
                                                required>
                                                <option value="">-- Search Customer --</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Auto-Distribute Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white border-primary"><i
                                                        class="bi bi-magic"></i></span>
                                                <input type="number" id="auto_distribute_amount"
                                                    class="form-control border-primary"
                                                    placeholder="Enter total amount to split">
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

                            <div class="card border-0 shadow-sm overflow-hidden" id="due-container" style="display:none;">
                                <div class="card-header py-3 border-0">
                                    <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-clock-history me-2"></i>Outstanding
                                        Invoices</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="due-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Invoice #</th>
                                                <th>Remaining</th>
                                                <th>Note</th>
                                                <th style="width: 180px;" class="pe-4 text-end">Paying Now</th>
                                            </tr>
                                        </thead>
                                        <tbody id="due-tbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                                <div class="card-body p-4 text-center">
                                    <div class="mb-4">
                                        <div class="display-6 fw-bold text-dark" id="display_total_collection">0.00</div>
                                        <p class="text-muted text-uppercase small ls-1">Total Collection</p>
                                    </div>

                                    <div class=" rounded p-3 mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Selected Invoices</span>
                                            <span class="fw-bold small" id="count_selected">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">Remaining Balance</span>
                                            <span class="fw-bold small text-danger"
                                                id="display_remaining_balance">0.00</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold d-block text-start">General Note</label>
                                        <textarea name="note" class="form-control" rows="3" placeholder="Add payment memo..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">
                                        Confirm Payment
                                    </button>

                                    <button type="reset"
                                        class="btn btn-link btn-sm text-decoration-none mt-3 text-muted">Reset
                                        Form</button>
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

        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
        }

        .pay-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .pay-input {
            text-align: right;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // Initialize Select2 if exists
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            $('#customer_id').change(function() {
                let customerId = $(this).val();
                if (!customerId) {
                    $('#due-container').hide();
                    resetSummary();
                    return;
                }

                // Start loading UI
                $('#due-tbody').html(
                    '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>'
                );
                $('#due-container').show();

                $.get(`/payments/pending-dues/${customerId}`, function(dues) {
                    let rows = '';
                    if (dues.length === 0) {
                        rows =
                            '<tr><td colspan="4" class="text-center py-5 text-muted">No pending dues found.</td></tr>';
                    } else {
                        dues.forEach(due => {
                            let balance = due.due_amount - due.paid_amount;
                            rows += `
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">#${due.sale.invoice_no}</div>
                                <div class="text-muted extra-small">${new Date(due.created_at).toLocaleDateString()}</div>
                            </td>
                            <td><span class="badge bg-danger-subtle text-danger px-2">${balance.toFixed(2)}</span></td>
                            <td><div class="small text-truncate" style="max-width: 150px;" title="${due.note || ''}">${due.note || '-'}</div></td>
                            <td class="pe-4 text-end">
                                <input type="number" name="amounts[${due.id}]" 
                                    class="form-control form-control-sm pay-input ms-auto" 
                                    step="0.01" max="${balance}" 
                                    data-max="${balance}" placeholder="0.00">
                            </td>
                        </tr>`;
                        });
                    }
                    $('#due-tbody').html(rows);
                    calculateSummary();
                });
            });

            // Auto-Distribution Tool
            $('#auto_distribute_amount').on('input', function() {
                let totalToDistribute = parseFloat($(this).val()) || 0;
                $('.pay-input').val('');

                $('.pay-input').each(function() {
                    let maxCanPay = parseFloat($(this).data('max'));
                    if (totalToDistribute > 0) {
                        if (totalToDistribute >= maxCanPay) {
                            $(this).val(maxCanPay.toFixed(2));
                            totalToDistribute -= maxCanPay;
                        } else {
                            $(this).val(totalToDistribute.toFixed(2));
                            totalToDistribute = 0;
                        }
                    }
                });
                calculateSummary();
            });

            $(document).on('input', '.pay-input', function() {
                let max = parseFloat($(this).data('max'));
                if (parseFloat($(this).val()) > max) $(this).val(max);
                calculateSummary();
            });

            function calculateSummary() {
                let total = 0;
                let count = 0;
                let totalPossible = 0;

                $('.pay-input').each(function() {
                    let val = parseFloat($(this).val()) || 0;
                    let max = parseFloat($(this).data('max')) || 0;
                    total += val;
                    totalPossible += max;
                    if (val > 0) count++;
                });

                $('#display_total_collection').text(total.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#count_selected').text(count);
                $('#display_remaining_balance').text((totalPossible - total).toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
            }

            function resetSummary() {
                $('#display_total_collection').text('0.00');
                $('#count_selected').text('0');
                $('#display_remaining_balance').text('0.00');
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true
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
        });
    </script>
@endpush
