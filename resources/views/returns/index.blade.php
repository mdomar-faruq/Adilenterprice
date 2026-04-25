@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
                            <li class="breadcrumb-item active fw-semibold text-primary">Return</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold text-gray-800 mb-0">Return Good and Damage Product</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('returns.create') }}" class="btn btn-gradient text-white rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>New Return
                    </a>
                </div>
            </div>
        </div>

        {{-- <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Product Returns</h2>
            <a href="{{ route('returns.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>New Return Entry
            </a>
        </div> --}}

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="returnsTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>DSR</th>
                                <th>Total Amount</th>
                                <th width="100">Action</th>
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
        $(function() {
            $('#returnsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('returns.index') }}",
                columns: [{
                        data: 'return_date',
                        name: 'return_date'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'dsr_id',
                        name: 'dsr_id'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        className: 'text-end fw-bold'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });


        function deleteReturn(id) {
            Swal.fire({
                title: 'Reverse this return?',
                text: "Stock and Damage Stock will be updated automatically!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete & Reverse!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
@endpush
