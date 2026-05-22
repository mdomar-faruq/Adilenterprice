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
                                <a href="/" class="text-decoration-none text-muted d-inline-flex align-items-center">
                                    <i class="bi bi-house-door me-2"></i><span>Home</span>
                                </a>
                            </li>
                            <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">Purchase
                                Payments</li>
                        </ol>
                    </nav>
                </div>

                {{-- Right Side: Create Voucher Action Button --}}
                <div class="col-auto">
                    <a href="{{ route('purchase_payments.create') }}"
                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-3 rounded-pill fw-bold">
                        <i class="bi bi-plus-lg me-2"></i>
                        <span>New Payment Voucher</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white rounded-top-4 py-3 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Payment Logs</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="paymentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50" class="text-center">#</th>
                                        <th>Date</th>
                                        <th>Company Name</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount Paid</th>
                                        <th width="80" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- DataTables dynamically injects rows here --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // CSRF Token header mapping setup for secure deletion payloads
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Yajra DataTables matching backend columns payload schema
            let table = $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('purchase_payments.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'payment_date',
                        name: 'payment_date'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    }, // Linked column
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        class: 'text-end fw-bold text-dark'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    }
                ],
                order: [
                    [1, 'desc']
                ], // Default tracking initialization by date row context
                language: {
                    processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Loading logs...'
                },
                dom: "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-md-5'i><'col-md-7'p>>"
            });


            // Unified AJAX deletion listener matrix configuration
            $(document).on('click', '.delete-btn', function() {
                // Read the fully-formed backend route directly from the button element
                let deleteUrl = $(this).data('route');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Reverting this transaction will alter outstanding supplier ledger balances!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl, // No more string interpolation or broken paths!
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message ||
                                        'Payment voucher safely expunged.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null,
                                    false); // Keep current pagination page
                            },
                            error: function(xhr) {
                                let errorReasonMsg = xhr.responseJSON?.message ||
                                    'Failed to clean specific record instances.';
                                Swal.fire('Action Revoked', errorReasonMsg, 'error');
                            }
                        });
                    }
                });
            });
            //
        });
    </script>
@endpush
