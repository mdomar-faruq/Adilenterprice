@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header mb-4 pt-3">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 align-items-center">
                            <li class="breadcrumb-item"><a href="/" class="text-muted"><i
                                        class="bi bi-house-door me-2"></i>Home</a></li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Sales</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Sales Financial History</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sales.create') }}"
                        class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        <span class="fw-bold">New Sale</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="salesTable" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>SR Name</th> {{-- Replaced Customer --}}
                                <th>Delivery Man</th> {{-- Added --}}
                                <th>Route</th> {{-- Added --}}
                                <th>Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end text-success">Paid</th>
                                <th class="text-end text-danger">Due</th>
                                <th class="text-end">Due Details</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- <div class="modal fade" id="dueCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
            <form id="dueCustomerForm">
                @csrf
                <input type="hidden" name="sale_id" id="modal_sale_id">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi bi-person-plus-fill me-2"></i>
                            Assign Due - Invoice: <span id="modal_invoice_no" class="ms-2 fw-bold text-warning"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="dueTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="min-width: 250px;">Customer</th>
                                        <th style="min-width: 100px;">Due Amount</th>
                                        <th style="min-width: 300px;">Note (Specific Details)</th>
                                        <th class="text-center" style="width: 50px;">
                                            <button type="button" class="btn btn-primary btn-sm rounded-circle addDueRow">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="p-3">
                                            <select name="customer_id[]" class="form-select border-0 bg-light" required>
                                                <option value="">Select Customer</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-3">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-0">$</span>
                                                <input type="number" name="due_amount[]"
                                                    class="form-control border-0 bg-light" step="0.01" placeholder="0.00"
                                                    required>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <textarea name="note[]" class="form-control border-0 bg-light" rows="1" placeholder="Enter transaction note..."
                                                style="resize: none;"></textarea>
                                        </td>
                                        <td class="text-center p-3">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm border-0 removeDueRow">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-white px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-5 fw-bold rounded-pill">
                            <i class="bi bi-check2-circle me-1"></i> Save Financial Records
                        </button>

                        <div class="bg-light p-3 border-top d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted fw-bold text-uppercase small">Invoice Due:</span>
                                <span id="total_invoice_due_display" class="h5 mb-0 ms-2 text-dark">$0.00</span>
                            </div>
                            <div>
                                <span class="text-muted fw-bold text-uppercase small">Assigned:</span>
                                <span id="total_assigned_display" class="h5 mb-0 ms-2 text-primary">$0.00</span>
                            </div>
                            <div class="border-start ps-4">
                                <span class="text-muted fw-bold text-uppercase small">Remaining:</span>
                                <span id="remaining_balance_display"
                                    class="h5 mb-0 ms-2 fw-bold text-success">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div> --}}
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            let table = $('#salesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sales.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'sr_name',
                        name: 'sr.name'
                    }, // Ensure controller joins or loads SR
                    {
                        data: 'delivery_name',
                        name: 'delivery.name'
                    }, // Ensure controller joins or loads Delivery
                    {
                        data: 'route_no',
                        name: 'route_no'
                    },
                    {
                        data: 'sale_date',
                        name: 'sale_date'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        className: 'text-end'
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount',
                        className: 'text-end text-success'
                    },
                    {
                        data: 'due_amount',
                        name: 'due_amount',
                        className: 'text-end fw-bold',
                        render: function(data) {
                            return parseFloat(data) > 0 ?
                                `<span class="text-danger">${parseFloat(data).toFixed(2)}</span>` :
                                `<span class="text-muted">0.00</span>`;
                        }
                    },
                    {
                        data: 'due_details',
                        name: 'due_details',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // SweetAlert Delete Logic remains the same...
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will revert stock and delete the invoice!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/sales/" + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                table.ajax.reload();
                                Swal.fire('Deleted!', 'Record removed.', 'success');
                            }
                        });
                    }
                });
            });
        });
    </script>

    {{-- Due customer  --}}
    {{-- <script>
        $(document).ready(function() {
            // Open Modal and set ID

            $(document).on('click', '.add-due-btn', function() {
                const id = $(this).data('id');
                const invoice = $(this).data('invoice');
                const maxAllowed = $(this).data(
                    'due'); // This is the $remainingToAssign from the controller

                $('#modal_sale_id').val(id);
                $('#modal_invoice_no').text(invoice);

                // Store this value in the modal to check against the sum of inputs
                $('#dueCustomerModal').data('limit', maxAllowed);

                $('#dueCustomerModal').modal('show');
            });

            // Add Row in Modal
            $('.addDueRow').click(function() {
                let newRow = $('#dueTable tbody tr:first').clone();
                newRow.find('input').val('');
                newRow.find('select').val('');
                $('#dueTable tbody').append(newRow);
            });

            // Remove Row
            $(document).on('click', '.removeDueRow', function() {
                if ($('#dueTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                }
            });

            // Form Submit
            $('#dueCustomerForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('sales.due.store') }}", // Create this route
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#dueCustomerModal').modal('hide');
                        Swal.fire('Success', 'Customer dues updated', 'success');
                        $('#salesTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Could not save data', 'error');
                    }
                });
            });
        });
    </script> 

    <script>
        function calculateDueLimits() {
            let invoiceTotalDue = parseFloat($('#modal_invoice_no').data('due-val')) || 0;
            let totalAssigned = 0;

            // Sum up all due amount inputs in the modal
            $('input[name="due_amount[]"]').each(function() {
                let val = parseFloat($(this).val()) || 0;
                totalAssigned += val;
            });

            let remaining = invoiceTotalDue - totalAssigned;

            // Update Displays
            $('#total_invoice_due_display').text(invoiceTotalDue.toFixed(2));
            $('#total_assigned_display').text(totalAssigned.toFixed(2));
            $('#remaining_balance_display').text(remaining.toFixed(2));

            // Visual feedback
            if (remaining < 0) {
                $('#remaining_balance_display').addClass('text-danger').removeClass('text-success');
            } else {
                $('#remaining_balance_display').addClass('text-success').removeClass('text-danger');
            }

            return {
                remaining,
                totalAssigned,
                invoiceTotalDue
            };
        }

        // Trigger calculation on input change
        $(document).on('input', 'input[name="due_amount[]"]', calculateDueLimits);

        // Update Modal Open logic to store the Max Due allowed
        $(document).on('click', '.add-due-btn', function() {
            const id = $(this).data('id');
            const invoice = $(this).data('invoice');
            const dueVal = $(this).data('due'); // Ensure you pass data-due in the Action button

            $('#modal_sale_id').val(id);
            $('#modal_invoice_no').text(invoice).data('due-val', dueVal);

            $('#dueCustomerModal').modal('show');
            calculateDueLimits();
        });
    </script>
    --}}

    {{-- Due customer  --}}
@endpush
