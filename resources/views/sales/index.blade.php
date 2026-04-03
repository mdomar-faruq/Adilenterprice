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
                                <th>Customer</th>
                                <th>Date</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end text-success">Paid</th>
                                <th class="text-end text-danger">Due</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // --- 1. Catch Laravel Flash Messages ---
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "{{ session('error') }}"
                });
            @endif

            // --- 2. Initialize DataTable ---
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
                        data: 'customer_name',
                        name: 'customer_name'
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
                                `<span class="text-danger">${data}</span>` :
                                `<span class="text-muted">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            let payBtn = '';
                            // Only show Pay button if there is a debt
                            if (parseFloat(row.due_amount) > 0) {
                                payBtn = `<button class="btn btn-sm btn-success pay-now-btn me-1" 
                                data-id="${row.id}" 
                                data-invoice="${row.invoice_no}" 
                                data-due="${row.due_amount}"
                                title="Add Payment">
                                <i class="bi bi-cash-stack"></i>
                              </button>`;
                            }
                            // 'data' here contains your existing Edit/Delete buttons from the Controller
                            return `<div class="d-flex justify-content-center">${payBtn}${data}</div>`;
                        }
                    }
                ],
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // --- 3. SweetAlert Delete Confirmation ---
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will revert stock and delete the invoice!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/sales/" + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire(
                                    'Deleted!',
                                    'The sale record has been removed.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong: ' + xhr.responseText,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
