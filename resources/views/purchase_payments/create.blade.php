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
                            <li class="breadcrumb-item active fw-semibold text-primary">Payment Voucher</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white rounded-top-4 py-3">
                        <h5 class="mb-0"><i class="bi bi-bank me-2"></i>Company Purchase Payment</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="purchasePaymentForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Company</label>
                                <select name="company_id" id="company_id" class="form-select select2" required>
                                    <option value="">Search Company...</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            data-due="{{ $company->purchases->sum('due_amount') + $company->opening_balance }}">
                                            {{ $company->name }} (Due:
                                            TK{{ number_format($company->purchases->sum('due_amount') + $company->opening_balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="p-3 border rounded-3 text-center">
                                        <small class="text-muted d-block">Current Total Due</small>
                                        <span class="h5 fw-bold text-danger" id="due_display">TK0.00</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 border rounded-3 text-center">
                                        <small class="text-muted d-block">Paying Now</small>
                                        <span class="h5 fw-bold" id="paying_display">TK0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Amount to Pay</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">TK</span>
                                    <input type="number" name="amount" id="amount_input" class="form-control fw-bold"
                                        step="0.01" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Check">Check</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Reference / Note</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Cheque number or transaction ID..."></textarea>
                            </div>

                            <button type="submit" class="btn bg-primary text-white w-100 py-3 rounded-pill fw-bold shadow">
                                Confirm Payment to Company
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // When company selection changes
            $('#company_id').on('change', function() {
                let due = $(this).find(':selected').data('due') || 0;

                $('#due_display').text('TK' + parseFloat(due).toLocaleString());
                $('#amount_input').val(due); // Auto-fill full payment
                $('#paying_display').text('TK' + parseFloat(due).toLocaleString());
            });

            // Live update the "Paying Now" box
            $('#amount_input').on('input', function() {
                let val = parseFloat($(this).val()) || 0;
                $('#paying_display').text('Tk' + val.toLocaleString());
            });

            // Submit Logic
            $('#purchasePaymentForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('purchase_payments.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Successful',
                            text: res.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                    }
                });
            });
        });
    </script>
@endpush
