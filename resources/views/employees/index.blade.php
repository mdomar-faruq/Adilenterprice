@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="page-header mb-4">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1 align-items-center">
                                <li class="breadcrumb-item">
                                    <a href="/"
                                        class="text-decoration-none text-muted d-inline-flex align-items-center">
                                        <i class="bi bi-house-door me-2"></i>
                                        <span>Home</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">
                                    Employee Directory
                                </li>
                            </ol>
                        </nav>
                        <h6 class="h6 fw-bold text-gray-800 mb-0">Manage Employee records, designations, and payroll basics.
                        </h6>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center"
                            id="addNewBtn">
                            <i class="bi bi-person-plus me-2"></i>
                            <span class="fw-bold">New Employee</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-people text-primary h4 mb-0"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Employee</small>
                            <h4 class="fw-bold mb-0" id="totalStaffCount">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="employeeTable">
                        <thead class="bg-light text-uppercase small fw-bold">
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Designation</th>
                                <th>Phone</th>
                                <th>Salary</th>
                                <th>Joining Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="employeeForm">
                    @csrf
                    <input type="hidden" name="employee_id" id="employee_id">

                    <div class="modal-header border-0 p-4 pb-0">
                        <h5 class="fw-bold text-dark" id="modalTitle">Add New Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name*</label>
                                <input type="text" name="name" id="name" class="form-control border-2"
                                    placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Phone Number (Unique)*</label>
                                <input type="text" name="phone" id="phone" class="form-control border-2"
                                    placeholder="017xxxxxxxx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Designation</label>
                                <input type="text" name="designation" id="designation" class="form-control border-2"
                                    placeholder="e.g. Sales Executive">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Monthly Salary (TK)</label>
                                <input type="number" name="salary" id="salary"
                                    class="form-control border-2 fw-bold text-primary" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control border-2"
                                    placeholder="name@company.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Joining Date</label>
                                <input type="date" name="joining_date" id="joining_date" class="form-control border-2"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">NID Number</label>
                                <input type="text" name="nid_number" id="nid_number" class="form-control border-2">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Home Address</label>
                                <textarea name="address" id="address" class="form-control border-2" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn"
                            class="btn btn-primary rounded-pill px-4 shadow fw-bold">Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // 1. Initialize DataTable
            let table = $('#employeeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('employees.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        class: 'fw-bold'
                    },
                    {
                        data: 'designation',
                        name: 'designation'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'salary',
                        name: 'salary'
                    },
                    {
                        data: 'joining_date',
                        name: 'joining_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    }
                ],
                drawCallback: function(settings) {
                    // Update stats card with total from record count
                    $('#totalStaffCount').text(settings._iRecordsTotal);
                }
            });

            // 2. Open Modal for Create (Clear Form)
            $('#addNewBtn').click(function() {
                $('#modalTitle').text('Register New Employee');
                $('#employeeForm')[0].reset();
                $('#employee_id').val(''); // Clear ID
                $('#employeeModal').modal('show');
            });

            // 3. Open Modal for Edit (Fetch Data)
            $(document).on('click', '.editBtn', function() {
                console.log('ok');

                let id = $(this).data('id');
                $.get("/employees/" + id + "/edit", function(data) {
                    $('#modalTitle').text('Edit Employee: ' + data.name);
                    $('#employee_id').val(data.id);
                    $('#name').val(data.name);
                    $('#phone').val(data.phone);
                    $('#designation').val(data.designation);
                    $('#salary').val(data.salary);
                    $('#email').val(data.email);
                    $('#joining_date').val(data.joining_date);
                    $('#nid_number').val(data.nid_number);
                    $('#address').val(data.address);
                    $('#employeeModal').modal('show');
                });
            });

            // 4. Handle Form Submission (Store & Update)
            $('#employeeForm').on('submit', function(e) {
                e.preventDefault();
                let submitBtn = $('#saveBtn');
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

                $.ajax({
                    url: "{{ route('employees.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            $('#employeeModal').modal('hide');
                            table.ajax.reload();
                            Swal.fire('Success!', res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        // Handle 422 Unique/Validation errors
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorList = '';
                            $.each(errors, function(key, value) {
                                errorList += value[0] + '<br>';
                            });
                            Swal.fire('Data Error', errorList, 'error');
                        } else {
                            Swal.fire('Error', 'Something went wrong on the server.', 'error');
                        }
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html('Save Employee');
                    }
                });
            });

            // 5. Delete Logic
            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This employee record will be removed permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/employees/" + id,
                            type: "DELETE",
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
    </script>
@endpush
