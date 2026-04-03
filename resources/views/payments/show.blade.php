@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div>
                <h4 class="fw-bold mb-0">Money Receipt Detail</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Receipts</a></li>
                        <li class="breadcrumb-item active">#{{ $payment->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('payments.index') }}" class="btn btn-white border shadow-sm rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow">
                    <i class="bi bi-printer me-2"></i>Print Receipt
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="bg-primary" style="height: 10px;"></div>

                    <div class="card-body p-5">
                        <div class="row mb-5">
                            <div class="col-sm-6">
                                <h2 class="fw-black text-primary mb-0">VOUCHER</h2>
                                <p class="text-muted tracking-widest small text-uppercase">Payment Confirmation</p>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <h4 class="fw-bold mb-1">#MR-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h4>
                                <p class="text-muted mb-0">Issued on:
                                    {{ date('d M, Y', strtotime($payment->payment_date)) }}</p>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-sm-6">
                                <p class="text-muted small text-uppercase fw-bold mb-2">Customer Details</p>
                                <h5 class="fw-bold mb-1">{{ $payment->customer->name }}</h5>
                                <p class="text-muted mb-1"><i
                                        class="bi bi-telephone me-2"></i>{{ $payment->customer->phone ?? 'N/A' }}</p>
                                <p class="text-muted small mb-0"><i
                                        class="bi bi-geo-alt me-2"></i>{{ $payment->customer->address ?? 'No address provided' }}
                                </p>
                            </div>
                            <div class="col-sm-6 text-sm-end mt-4 mt-sm-0">
                                <p class="text-muted small text-uppercase fw-bold mb-2">Payment Source</p>
                                <p class="h6 fw-bold mb-1">{{ $payment->payment_method }}</p>
                                <p class="text-muted small">Processed by: {{ $payment->user->name ?? 'Admin' }}</p>
                            </div>
                        </div>

                        <div class="rounded-4 border border-2 border-dashed p-4 text-center mb-5 bg-light">
                            <span class="text-muted small text-uppercase fw-bold">Total Amount Received</span>
                            <h1 class="display-3 fw-black text-dark mb-0">${{ number_format($payment->amount, 2) }}</h1>
                        </div>

                        @if ($payment->note)
                            <div class="mb-5">
                                <p class="text-muted small text-uppercase fw-bold mb-2">Reference Note</p>
                                <div class="p-3 rounded-3 bg-light border-start border-4 border-primary">
                                    <p class="mb-0 text-dark fst-italic">"{{ $payment->note }}"</p>
                                </div>
                            </div>
                        @endif

                        <div class="row mt-5 pt-4">
                            <div class="col-6">
                                <div class="border-top pt-3">
                                    <p class="small text-muted mb-0">Received By</p>
                                    <p class="fw-bold text-uppercase mt-4">{{ $payment->user->name ?? 'Authorized Agent' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="border-top pt-3">
                                    <p class="small text-muted mb-0">Customer Acknowledgement</p>
                                    <div style="height: 60px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 d-print-none">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle-fill fs-2"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold">Payment Verified</h5>
                        <p class="text-muted small">This receipt is a valid proof of payment for the associated account
                            balance.</p>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Transaction ID:</span>
                            <span class="fw-bold">TXN-{{ time() }}-{{ $payment->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Method:</span>
                            <span
                                class="fw-bold badge bg-primary-subtle text-primary">{{ $payment->payment_method }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-black {
            font-weight: 900;
        }

        .tracking-widest {
            letter-spacing: 0.2em;
        }

        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            body {
                margin: 1cm;
                background: white !important;
            }

            .d-print-none {
                display: none !important;
            }

            .card {
                border: 1px solid #eee !important;
                box-shadow: none !important;
                width: 100% !important;
            }

            .bg-primary {
                background-color: #0d6efd !important;
                -webkit-print-color-adjust: exact;
            }

            .col-lg-8 {
                width: 100% !important;
            }
        }
    </style>
@endsection
