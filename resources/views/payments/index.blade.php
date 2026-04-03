@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="page-header mb-4 pt-3">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 align-items-center">
                            <li class="breadcrumb-item"><a href="/" class="text-muted"><i
                                        class="bi bi-house-door me-2"></i>Home</a></li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Money Receipts</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">History of all customer payments and collections.</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('payments.create') }}"
                        class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        <span class="fw-bold">New Money Receipts</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <table class="table table-hover align-middle w-100" id="receiptTable">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#receiptTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('payments.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    class: 'text-center',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'payment_date',
                    name: 'payment_date'
                },
                {
                    data: 'customer_name',
                    name: 'customer.name'
                }, // Links to filterColumn in backend
                {
                    data: 'payment_method',
                    name: 'payment_method'
                },
                {
                    data: 'amount',
                    name: 'amount',
                    class: 'text-end fw-bold text-primary',
                    render: function(data) {
                        return 'TK' + data;
                    } // Adds dollar sign on frontend
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
            ] // Default sort by Date
        });

        $(document).on('click', '.delete-payment', function() {
            let id = $(this).data('id');
            let url = "{{ route('payments.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the receipt and increase the customer's due balance!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                border: 'none'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Deleted!', res.message, 'success');
                                $('#receiptTable').DataTable().ajax.reload(); // Refresh table
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush
