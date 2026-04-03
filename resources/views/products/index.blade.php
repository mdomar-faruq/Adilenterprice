@extends('layouts.app')

@include('products.css')

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
                                    Product
                                </li>
                            </ol>
                        </nav>
                        <h2 class="h3 fw-bold text-gray-800 mb-0">Product Information</h2>
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            <span class="fw-bold">New Product</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card product-card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table id="productDataTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr class="small">
                                <th>#CODE</th>
                                <th>NAME</th>
                                <th>UNIT</th>
                                <th>PURCHASE</th>
                                <th>MARGIN</th>
                                <th>SALE</th>
                                <th>STOCK</th>
                                <th class="text-center">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            {{-- DataTables handled --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('products.modals')
@endsection

@include('products.js')
