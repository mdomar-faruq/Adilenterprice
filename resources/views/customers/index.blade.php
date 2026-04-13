@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted"><i
                                    class="bi bi-house-door me-1"></i> Home</a></li>
                        <li class="breadcrumb-item active fw-semibold text-primary">Customers</li>
                    </ol>
                </nav>
                <h2 class="h3 fw-bold text-dark mb-0">Customer Management</h2>
            </div>
            <div class="col-auto">
                <button class="btn btn-gradient text-white rounded-pill px-4 shadow-sm d-flex align-items-center"
                    onclick="addCustomer()">
                    <i class="bi bi-plus-circle-fill me-2"></i>
                    <span class="fw-bold">New Customer</span>
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="customerTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contact</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                                    Limit</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                                    Opening Bal</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Status</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Ledger</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0" id="modalTitle">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="customerForm">
                    @csrf
                    <input type="hidden" name="customer_id" id="customer_id">
                    <div class="modal-body px-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Customer Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                    class="form-control form-control-lg rounded-3" placeholder="Enter full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control rounded-3"
                                    placeholder="Phone number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="email" class="form-control rounded-3"
                                    placeholder="email@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Credit Limit</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">TK</span>
                                    <input type="number" name="credit_limit" id="credit_limit"
                                        class="form-control border-start-0 rounded-end-3" step="0.01" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Opening Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">TK</span>
                                    <input type="number" name="opening_balance" id="opening_balance"
                                        class="form-control border-start-0 rounded-end-3" step="0.01" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" id="address" class="form-control rounded-3" rows="2"
                                    placeholder="Street, City, Country"></textarea>
                            </div>
                            <div class="col-md-12 py-2">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                        checked>
                                    <label class="form-check-label fw-semibold ms-2" for="is_active">Account
                                        Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" id="saveBtn">Save
                            Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let table;

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#customerTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('customers.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        class: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        class: 'fw-bold text-dark'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'credit_limit',
                        class: 'text-end',
                        render: $.fn.dataTable.render.number(',', '.', 2, 'TK')
                    },
                    {
                        data: 'opening_balance',
                        class: 'text-end',
                        render: $.fn.dataTable.render.number(',', '.', 2, 'TK')
                    },
                    {
                        data: 'is_active',
                        name: 'status',
                        class: 'text-center',
                        render: function(data) {
                            return data == 1 ?
                                '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>' :
                                '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Inactive</span>';
                        }
                    },
                    {
                        data: 'ledger',
                        name: 'ledger',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
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
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // Submit Form
            $('#customerForm').on('submit', function(e) {
                e.preventDefault();
                let saveBtn = $('#saveBtn');
                saveBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.ajax({
                    url: "{{ route('customers.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#customerModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Something went wrong';
                        if (xhr.status === 422) {
                            msg = Object.values(xhr.responseJSON.errors)[0][0];
                        }
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() {
                        saveBtn.prop('disabled', false).text('Save Record');
                    }
                });
            });

            // Edit Button Click
            $(document).on('click', '.edit-customer', function() {
                let id = $(this).data('id');
                $('#customer_id').val(id);
                $('#modalTitle').text('Edit Customer Details');
                $('#saveBtn').text('Update Record');

                $.get("{{ url('customers') }}/" + id + "/edit", function(data) {
                    $('#name').val(data.name);
                    $('#phone').val(data.phone);
                    $('#email').val(data.email);
                    $('#credit_limit').val(data.credit_limit);
                    $('#opening_balance').val(data.opening_balance);
                    $('#address').val(data.address);
                    $('#is_active').prop('checked', data.is_active == 1);
                    $('#customerModal').modal('show');
                });
            });

            // Delete Button Click
            $(document).on('click', '.delete-customer', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Customer?',
                    text: "This will remove all transaction history!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('customers') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', res.message, 'success');
                            }
                        });
                    }
                });
            });
        });

        function addCustomer() {
            $('#customerForm')[0].reset();
            $('#customer_id').val('');
            $('#modalTitle').text('Add New Customer');
            $('#saveBtn').text('Save Record');
            $('#customerModal').modal('show');
        }
    </script>
@endpush

<style>
    .bg-success-subtle {
        background-color: #d1e7dd !important;
    }

    .bg-danger-subtle {
        background-color: #f8d7da !important;
    }

    .custom-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }

    table.dataTable thead th {
        border-bottom: 1px solid #f0f0f0 !important;
    }

    .modal-content {
        border-radius: 1.25rem;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        border-color: #86b7fe;
    }
</style>
