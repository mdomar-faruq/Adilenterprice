@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-0 text-body">Distribution Overview</h1>
                <p class="text-secondary">Real-time Sales and Purchase data.</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary-subtle text-primary p-3 rounded">
                                <i class="bi bi-box-seam fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-secondary mb-1">Total Stock</h6>
                                <h4 class="mb-0 text-body">12,450</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success-subtle text-success p-3 rounded">
                                <i class="bi bi-truck fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-secondary mb-1">On Delivery</h6>
                                <h4 class="mb-0 text-body">48</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0 text-body">Recent Shipments</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-secondary">Tracking ID</th>
                            <th class="text-secondary">Destination</th>
                            <th class="text-secondary">Status</th>
                            <th class="text-secondary text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-body fw-bold">#TRK-8821</td>
                            <td class="text-body">Warehouse - Dhaka</td>
                            <td><span class="badge bg-info-subtle text-info">In Transit</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary">View</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
