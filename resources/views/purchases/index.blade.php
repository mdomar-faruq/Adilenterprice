@extends('layouts.app')

@include('purchases.css')

@section('content')
    <div class="container-fluid">
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
                                    Purchases
                                </li>
                            </ol>
                        </nav>
                        <h2 class="h3 fw-bold text-gray-800 mb-0">Purchases History</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('purchases.create') }}"
                            class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            <span class="fw-bold">Add New Purchase</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle purchase-datatable w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Purchase No</th>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Total Amount</th>
                                <th class="text-end text-success">Paid</th>
                                <th class="text-end text-danger">Due</th>
                                <th class="all">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('purchases.modal')
    @endsection

    @push('scripts')
        <script>
            $(function() {
                $('.purchase-datatable').DataTable({
                    responsive: true, // Crucial for mobile
                    processing: true,
                    serverSide: true,
                    autoWidth: false, // Prevents layout calculation errors
                    ajax: "{{ route('purchases.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'purchase_no',
                            name: 'purchase_no'
                        },
                        {
                            data: 'purchase_date',
                            name: 'purchase_date'
                        },
                        {
                            data: 'company.name',
                            name: 'company.name'
                        },
                        {
                            data: 'total_amount',
                            name: 'total_amount',
                            render: $.fn.dataTable.render.number(',', '.', 2)
                        },
                        {
                            data: 'paid_amount',
                            name: 'paid_amount',
                            className: 'text-end text-success'
                        },
                        {
                            data: 'due_amount',
                            name: 'due_amount',
                            className: 'text-end text-danger',

                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    // Responsive child row styling
                    columnDefs: [{
                            responsivePriority: 1,
                            targets: 1
                        }, // Purchase No stays visible
                        {
                            responsivePriority: 2,
                            targets: -1
                        } // Action stays visible
                    ]
                });
            });
        </script>

        <script>
            $(document).on('click', '.view-details', function() {
                let id = $(this).data('id');

                $.get("/purchases/" + id, function(data) {
                    // Fill Header
                    $('#modal_pur_no').text(data.purchase_no);
                    $('#modal_company').text(data.company.name);
                    $('#modal_date').text(data.purchase_date);
                    $('#modal_total').text('TK' + data.total_amount);

                    // Fill Items Table
                    let rows = '';
                    data.items.forEach(item => {
                        rows += `<tr>
                <td>${item.product.name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit_price}</td>
                <td class="text-end">${item.subtotal}</td>
            </tr>`;
                    });

                    $('#purchase_items_body').html(rows);
                    $('#viewModal').modal('show');
                });
            });
        </script>

        {{-- Sweet alart --}}
        <script>
            $(document).ready(function() {

                // Use delegated event for dynamically generated buttons
                $(document).on('click', '.delete-purchase', function(e) {
                    e.preventDefault();

                    let id = $(this).data('id');
                    let url = "{{ route('purchases.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete the purchase record!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!',
                        background: document.documentElement.getAttribute('data-bs-theme') === 'dark' ?
                            '#2b3035' : '#fff',
                        color: document.documentElement.getAttribute('data-bs-theme') === 'dark' ?
                            '#fff' : '#000'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}' // Essential for Laravel security
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        Swal.fire('Deleted!', response.message, 'success');
                                        // Reload the DataTable (change #purchase-table to your table ID)
                                        $('.purchase-datatable').DataTable().ajax.reload();
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error!', 'Could not delete the record.',
                                        'error');
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
