@extends('layouts.app')

@section('content')
    <div class="container py-5">
        {{-- Top Header Section Dashboard Wrapper --}}
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h3 class="fw-bold text-dark mb-1">DSR Opening Balances</h3>
                <p class="text-muted small mb-0">Manage and update starting ledger credit entries for field representatives.
                </p>
            </div>
        </div>

        {{-- Master Records Table Card wrapper --}}
        <div class="card border-0 shadow rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">DSR Name</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">Designation</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">Contact Phone</th>
                                <th class="py-3 text-end text-secondary small fw-bold text-uppercase">Opening Balance</th>
                                <th class="pe-4 py-3 text-center text-secondary small fw-bold text-uppercase"
                                    style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $employee->name }}</td>
                                    <td>
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">
                                            {{ $employee->designation ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $employee->phone ?? '—' }}</td>
                                    <td
                                        class="text-end fw-bold {{ $employee->opening_balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($employee->opening_balance, 2) }} TK
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3 edit-balance-btn"
                                            data-id="{{ $employee->id }}" data-name="{{ $employee->name }}"
                                            data-balance="{{ $employee->opening_balance }}">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-people display-6 d-block mb-3 text-secondary"></i>
                                        No employee profiles found in the database directory.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Add / Edit Employee Opening Balance --}}
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form action="{{ route('dsr.opening_store') }}" method="POST" id="openingBalanceForm">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold" id="modalTitle">DSR Opening Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        {{-- Employee Pick Dropdown Input Row --}}
                        <div class="mb-4" id="employeeSelectGroup">
                            <label class="form-label small fw-bold text-primary">Select DSR</label>
                            <select class="form-select select2-employee" name="employee_id" id="employee_id" required>
                                <option value="">Start typing name...</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }} ({{ $employee->designation ?? 'DSR' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Readonly View showing active target name when in operational Edit Mode --}}
                        <div class="mb-4 d-none" id="employeeStaticGroup">
                            <label class="form-label small text-muted d-block mb-1">DSR Target Name</label>
                            <h5 class="fw-bold text-dark mb-0" id="staticEmployeeName">John Doe</h5>
                            <input type="hidden" name="action_type" id="action_type" value="create">
                        </div>

                        {{-- Balance Numeric Allocation input row --}}
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-primary">Opening Balance (TK)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white border-0 fw-bold">TK</span>
                                <input type="number" name="opening_balance" id="opening_balance"
                                    class="form-control border-primary fw-bold text-dark" step="0.01" value="0.00"
                                    min="0" required>
                            </div>
                            <div class="form-text small mt-2">
                                Enter initial ledger debt (e.g., existing loan balance, forward collection deficits).
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // 1. Initialize Select2 Dropdown search container
            $('.select2-employee').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addEmployeeModal'),
                placeholder: 'Search for an employee...'
            });

            // 2. Intercept Inline Table 'Edit' Clicks to populate modal data state
            $('.edit-balance-btn').click(function() {
                let empId = $(this).data('id');
                let empName = $(this).data('name');
                let empBalance = parseFloat($(this).data('balance')).toFixed(2);

                $('#modalTitle').text('Modify Balance Window');
                $('#employeeSelectGroup').addClass('d-none');
                $('#employeeStaticGroup').removeClass('d-none');
                $('#staticEmployeeName').text(empName);

                $('#employee_id').val(empId).trigger('change');
                $('#opening_balance').val(empBalance);

                $('#addEmployeeModal').modal('show');
            });

            // 3. Reset modal properties when hidden/canceled
            $('#addEmployeeModal').on('hidden.bs.modal', function() {
                $('#modalTitle').text('DSR Opening Entry');
                $('#employeeSelectGroup').removeClass('d-none');
                $('#employeeStaticGroup').addClass('d-none');
                $('#employee_id').val('').trigger('change');
                $('#opening_balance').val('0.00');
            });

            // 4. Form Submit Loading Indicator Interceptor
            $('#openingBalanceForm').on('submit', function() {
                // Closes open bootstrap modal smoothly
                $('#addEmployeeModal').modal('hide');

                // Displays nice SweetAlert Processing Loading dialog state
                Swal.fire({
                    title: 'Saving Balance...',
                    text: 'Please wait while we update the ledger files.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });

            // 5. Global Session Flash Message Capturer (Triggers on redirect reload)
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{!! session('success') !!}",
                    confirmButtonColor: '#0d6efd',
                    timer: 3500
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: "{!! implode('\\n', $errors->all()) !!}",
                    confirmButtonColor: '#dc3545'
                });
            @endif
        });
    </script>
@endpush
