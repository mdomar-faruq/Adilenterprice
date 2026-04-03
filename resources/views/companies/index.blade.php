@extends('layouts.app')

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
                                    Company
                                </li>
                            </ol>
                        </nav>
                        <h2 class="h3 fw-bold text-gray-800 mb-0">Company Information</h2>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-gradient text-white rounded-pill px-4 py-2 d-flex align-items-center"
                            id="btn_add">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            <span class="fw-bold">New Company</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table id="CompanyDataTable" class="table table-hover align-middle w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-secondary">#</th>
                                <th class="text-secondary">Company Name</th>
                                <th class="text-secondary">Email</th>
                                <th class="text-secondary">Phone</th>
                                <th class="text-secondary">Address</th>
                                <th class="text-secondary">Opening Bal</th>
                                 <th class="text-secondary">Ledger</th>
                                <th class="text-secondary text-center">Actions</th>
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

    <div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header btn-gradient text-white">
                    <h5 class="modal-title" id="modalTitle">Company Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="companyForm" action="{{ route('companies.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="company_id" id="company_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Enter company name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="company@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control"
                                    placeholder="+1 234...">
                            </div>

                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Office Address</label>
                            <textarea name="address" id="address" class="form-control" rows="3" placeholder="Street, City, Country"></textarea>
                        </div>
                        <div class="mb-0">
                           <label class="form-label fw-semibold">Opening Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">TK</span>
                                    <input type="number" name="opening_balance" id="opening_balance"
                                        class="form-control border-start-0 rounded-end-3" step="0.01" value="0.00">
                                </div>
                            <div class="form-text small">Current balance (if any) at start.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4">
                        <button type="submit"
                            class="btn btn-gradient text-white w-100 rounded-pill py-2 fw-bold shadow-sm">Confirm
                            & Save
                            Company</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@include('companies.js')
