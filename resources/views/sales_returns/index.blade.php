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
                            <li class="breadcrumb-item active fw-semibold text-primary">Sales Returns</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Manage customer returns</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('sales_returns.create') }}"
                        class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        <span class="fw-bold">New Sales Return</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="salesReturnTable">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Date</th>
                                <th>Return No</th>
                                <th>Customer</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Setup CSRF for all Ajax calls
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#salesReturnTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sales_returns.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'return_date',
                        name: 'return_date'
                    },
                    {
                        data: 'return_no',
                        name: 'return_no',
                        class: 'fw-bold text-primary'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer.name' // Matches the join/relationship
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        class: 'text-end fw-bold text-success'
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

            // Delete Logic
            $(document).on('click', '.delete-return', function() {
                let id = $(this).data('id');
                // Use the route helper but replace the placeholder
                let url = "{{ route('sales_returns.destroy', ':id') }}";
                url = url.replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Stock will be reversed and customer balance will be restored!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    table.ajax.reload();
                                    Swal.fire('Deleted!', res.message, 'success');
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = xhr.responseJSON ? xhr.responseJSON
                                    .message : "Something went wrong";
                                Swal.fire('Failed!', errorMsg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
