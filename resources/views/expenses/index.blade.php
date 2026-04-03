@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div>
                <h2 class="fw-bold text-dark mb-1">Expense Management</h2>
                <p class="text-muted small">Track and manage your company operational costs</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#createExpenseModal">
                <i class="bi bi-plus-lg me-2"></i>Record New Expense
            </button>
        </div>

        <div class="row mb-4 d-print-none">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75 text-uppercase fw-bold">Monthly Expenses</small>
                                <h3 class="fw-bold mb-0 mt-1">TK {{ number_format($monthlyTotal ?? 0, 2) }}</h3>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                <i class="bi bi-graph-down-arrow h4 mb-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive p-4">
                    <table class="table table-hover align-middle w-100" id="expenseTable">
                        <thead class="bg-light text-uppercase small fw-bold">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Note</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createExpenseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="expenseForm">
                    @csrf
                    <div class="modal-header border-0 p-4 pb-0">
                        <h5 class="fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Add New Expense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Expense Category</label>
                                <select name="expense_category_id" class="form-select border-2 py-2" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Amount (TK)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-2 fw-bold">Tk</span>
                                    <input type="number" name="amount" class="form-control border-2 fw-bold text-primary"
                                        step="0.01" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Date</label>
                                <input type="date" name="expense_date" class="form-control border-2"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Payment Method</label>
                                <select name="payment_method" class="form-select border-2">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank Transfer</option>
                                    <option value="bKash/Nagad">Mobile Banking</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Note / Remarks</label>
                                <textarea name="note" class="form-control border-2" rows="2" placeholder="e.g. Office electricity bill..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow fw-bold">Save
                            Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            let table = $('#expenseTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('expenses.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'expense_date',
                        name: 'expense_date'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'amount',
                        name: 'amount',

                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    }
                ],
                language: {
                    searchPlaceholder: "Search expenses...",
                    search: ""
                }
            });

            // Handle AJAX Form Submission
            $('#expenseForm').on('submit', function(e) {
                e.preventDefault();

                // Disable button to prevent double clicks
                let submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.ajax({
                    url: "{{ route('expenses.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            $('#createExpenseModal').modal('hide');
                            $('#expenseForm')[0].reset();
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = 'Something went wrong!';
                        if (errors) errorMsg = Object.values(errors)[0][0];

                        Swal.fire('Error!', errorMsg, 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html('Save Transaction');
                    }
                });
            });

            // Handle Delete (Bonus Logic)
            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This expense will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/expenses/" + id,
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', 'Expense has been removed.',
                                    'success');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
