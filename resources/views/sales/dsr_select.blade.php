@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="bi bi-person-badge text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="fw-bold mb-3">DSR Ledger</h3>

                        <form id="selectionForm">
                            <div class="mb-4 text-start">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Search DSR</label>
                                    {{-- Trigger Modal Button --}}
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                        data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                        <i class="bi bi-plus-circle me-1"></i>Add DSR Opening
                                    </button>
                                </div>
                                <select class="form-select select2-employee" id="employee_id" required>
                                    <option value="">Start typing name...</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ route('dsr_details.ledger', $employee->id) }}">
                                            {{ $employee->name }} ({{ $employee->designation }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" id="viewBtn"
                                class="btn btn-primary w-100 rounded-pill btn-lg shadow-sm">
                                View Statement <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- --- Modal: Add Employee with Opening Balance --- --}}
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form action="{{ route('dsr_opening.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">DSR Opening Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-primary">Select DSR</label>
                            <select class="form-select select2-employee" name="employee_id" id="employee_id" required>
                                <option value="">Start typing name...</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }} ({{ $employee->designation }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-primary">Opening Balance (TK)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white border-0">TK</span>
                                <input type="number" name="opening_balance" class="form-control border-primary"
                                    step="0.01" value="0.00" required>
                            </div>
                            <div class="form-text small">Enter initial debt (e.g., existing loan balance)</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save & Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-employee').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Search for an employee...'
            });

            $('#viewBtn').click(function() {
                let url = $('#employee_id').val();
                if (url) {
                    window.location.href = url;
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Selection Required',
                        text: 'Please select an employee from the list.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        });
    </script>
@endpush
