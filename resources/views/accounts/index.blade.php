@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="fw-bold mb-1">Cash Flow & Accounts</h2>
                <p class="text-muted">Manage your starting capital and real-time cash balance.</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#openingModal">
                    <i class="bi bi-pencil-square me-2"></i>Edit Opening Balance
                </button>
            </div>
        </div>

        {{-- Main Balance Card --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 bg-gradient-primary text-white"
                    style="background: linear-gradient(45deg, #0d6efd, #0dcaf0);">
                    <div class="card-body p-5 text-center">
                        <h6 class="text-uppercase fw-bold opacity-75">Net Available Balance (Cash in Hand)</h6>
                        <h1 class="display-3 fw-bold mb-0">TK {{ number_format($currentBalance, 2) }}</h1>
                        <div class="mt-3">
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">Real-time Calculation</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Incoming Section --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-success"><i class="bi bi-arrow-down-left-circle me-2"></i>Incoming
                            (Cash In)</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span>Opening Balance</span>
                                <span class="fw-bold">TK {{ number_format($opening, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span>Sales Receive</span>
                                <span class="fw-bold"> TK {{ number_format($sales, 2) }}</span>
                            </li>
                            {{-- <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span>Due Sales</span>
                                <span class="fw-bold text-danger">- TK {{ number_format($dueSales, 2) }}</span>
                            </li> --}}
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Outgoing Section --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-arrow-up-right-circle me-2"></i>Outgoing (Cash
                            Out)</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            {{-- <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span>Product Purchases</span>
                                <span class="fw-bold">TK {{ number_format($purchases, 2) }}</span>
                            </li> --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span>General Expenses</span>
                                <span class="fw-bold">TK {{ number_format($expenses, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span>Purchase Payment (Company)</span>
                                <span class="fw-bold"> TK {{ number_format($paymentVouchers, 2) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Opening Balance Modal --}}
    <div class="modal fade" id="openingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('accounts.updateOpening') }}" method="POST"
                class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="fw-bold mb-0">Edit Opening Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold">Enter Initial Cash Amount</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">TK</span>
                        <input type="number" name="opening_balance" class="form-control form-control-lg bg-light border-0"
                            value="{{ $opening }}" step="0.01" required>
                    </div>
                    <p class="text-muted small mt-2">This is the cash you had before starting the digital records.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Balance</button>
                </div>
            </form>
        </div>
    </div>
@endsection
