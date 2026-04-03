@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Content Header Section --}}
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 align-items-center">
                            <li class="breadcrumb-item">
                                <a href="/" class="text-decoration-none text-muted d-inline-flex align-items-center">
                                    <i class="bi bi-house-door me-2"></i><span>Home</span>
                                </a>
                            </li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Orders</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold mb-0">Orders History</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('orders.create') }}"
                        class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center shadow-sm">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        <span class="fw-bold">Add New Orders</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Content Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="ordersTable" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Order No</th>
                                <th>Customer</th>
                                <th class="text-center">Unique Items</th>
                                <th class="text-end">Total Qty</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- View Order Modal --}}
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailContent">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading order data...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('orders.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_no',
                        name: 'order_no'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'items_count',
                        name: 'items_count',
                        className: 'text-center'
                    },
                    {
                        data: 'grand_total',
                        name: 'grand_total',
                        className: 'text-end fw-bold'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_date',
                        name: 'order_date'
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
                    searchPlaceholder: "Search orders...",
                    paginate: {
                        previous: '<i class="bi bi-chevron-left"></i>',
                        next: '<i class="bi bi-chevron-right"></i>'
                    }
                }
            });

            // Handle View Details Click
            $(document).on('click', '.view-details', function() {
                let id = $(this).data('id');
                $('#viewOrderModal').modal('show');
                $('#orderDetailContent').html(
                    '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>'
                    );

                $.get("/orders/" + id, function(data) {
                    $('#orderDetailContent').html(data);
                }).fail(function() {
                    $('#orderDetailContent').html(
                        '<div class="alert alert-danger">Could not load data.</div>');
                });
            });

            // Handle Delete Confirmation
            $(document).on('click', '.delete-orders', function() {
                let id = $(this).data('id');
                let url = "{{ route('orders.destroy', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "All items linked to this order will be removed!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                table.ajax.reload(); // Reload the DataTable
                                Swal.fire('Deleted!', response.success, 'success');
                            },
                            error: function(err) {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
