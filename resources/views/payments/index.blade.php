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
                            <li class="breadcrumb-item active fw-semibold text-primary">Money Receipt</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Money Receipt History</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('payments.create') }}"
                        class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        <span class="fw-bold">New Money Receipt</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="payments-table">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Invoice #</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th class="text-end">Actions</th>
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
            let table = $('#payments-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('payments.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'payment_date'
                    },
                    {
                        data: 'customer',
                        name: 'customer.name'
                    },
                    {
                        data: 'invoice_no',
                        name: 'dueRecord.sale.invoice_no'
                    },
                    {
                        data: 'method',
                        name: 'payment_method'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
                ],
                order: [
                    [1, 'desc']
                ] // Order by Date column
            });

            // Reuse the same Delete AJAX logic from before
            $(document).on('click', '.delete-payment', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Payment?',
                    text: "This reverses the balance for this customer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/payments/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                Swal.fire('Deleted!', res.message, 'success');
                                table.draw(); // Refresh table without reloading page
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
