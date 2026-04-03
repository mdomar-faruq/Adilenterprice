@extends('layouts.app')

@section('style')
    <style>
        /* Reuse your professional styling */
        .btn-gradient {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
            color: white;
        }

        .card {
            border-radius: 12px;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        /* Dark Mode specific tweaks for labels */
        [data-bs-theme="dark"] .form-label {
            color: #dee2e6;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="page-header mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h3 fw-bold mb-0">New Customer Entry</h2>
                        <p class="text-muted small">Register a new client for Sales & Work Orders</p>
                    </div>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-list-ul me-2"></i>Customer List
                    </a>
                </div>

                <div class="card shadow-sm border-0">
                    <form action="{{ route('customers.store') }}" method="POST">
                        @csrf
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Customer Name --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Full Name / Company Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-person"></i></span>
                                        <input type="text" name="name" class="form-control border-start-0"
                                            placeholder="Enter name" required>
                                    </div>
                                </div>

                                {{-- Contact Details --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" class="form-control border-start-0"
                                            placeholder="+1 234 567 890">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control border-start-0"
                                            placeholder="customer@example.com">
                                    </div>
                                </div>

                                {{-- Financial Info --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Credit Limit</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">TK</span>
                                        <input type="number" name="credit_limit" class="form-control border-start-0"
                                            step="0.01" value="0.00">
                                    </div>
                                    <div class="form-text small">Maximum debt allowed for this customer.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Opening Balance</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">TK</span>
                                        <input type="number" name="opening_balance" class="form-control border-start-0"
                                            step="0.01" value="0.00">
                                    </div>
                                    <div class="form-text small">Current balance (if any) at start.</div>
                                </div>

                                {{-- Address --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Billing Address</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Full street address, City, Country"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-top-0 p-4 text-end">
                            <button type="reset" class="btn btn-link text-decoration-none text-muted me-3">Clear
                                Form</button>
                            <button type="submit" class="btn btn-gradient px-5 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-check2-circle me-2"></i>Save Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
