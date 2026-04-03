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
                            <li class="breadcrumb-item active fw-semibold text-primary">Money Receipt</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2"></i>New Money Receipt</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="mrForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Customer</label>
                                <select name="customer_id" id="customer_select" class="form-select select2" required>
                                    <option value="">Search Customer...</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" data-due="{{ $c->sales->sum('due_amount') }}">
                                            {{ $c->name }} (Due: TK{{ number_format($c->sales->sum('due_amount'), 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3 text-center">
                                        <small class="text-muted d-block">Current Total Due</small>
                                        <span class="h5 fw-bold text-danger" id="display_due">$0.00</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-primary-subtle rounded-3 text-center">
                                        <small class="text-muted d-block">Paying Now</small>
                                        <span class="h5 fw-bold text-primary" id="display_paying">$0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Amount Received</label>
                                <input type="number" name="amount" id="amount_input"
                                    class="form-control form-control-lg fw-bold" step="0.01" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Check">Check</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Note / Reference</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="e.g. Check #12345"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                Confirm Money Receipt
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

            // When customer is selected, show their due amount
            $('#customer_select').on('change', function() {
                let due = $(this).find(':selected').data('due') || 0;
                $('#display_due').text('TK' + parseFloat(due).toLocaleString());
                $('#amount_input').val(due); // Default to full payment
                $('#display_paying').text('TK' + parseFloat(due).toLocaleString());
            });

            // Sync "Paying Now" display
            $('#amount_input').on('input', function() {
                let val = parseFloat($(this).val()) || 0;
                $('#display_paying').text('TK' + val.toLocaleString());
            });

            $('#mrForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('payments.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire('Success', res.message, 'success').then(() => {
                            window.location.href = "{{ route('payments.index') }}";
                        });
                    }
                });
            });
        });

        
    </script>
@endpush
