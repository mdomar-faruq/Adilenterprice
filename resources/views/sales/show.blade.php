@extends('layouts.app')

@section('content')
    <div class="container py-4 d-print-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Sales
            </a>
            <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-printer-fill me-2"></i> Print Invoice
            </button>
        </div>
    </div>

    <div class="invoice-wrapper">
        <div class="invoice-container">
            <div class="row mb-4">
                <div class="col-7">
                    <h1 class="invoice-title">INVOICE</h1>
                    <p class="invoice-id">#{{ $sale->invoice_no }}</p>
                </div>
                <div class="col-5 text-end">
                    <h4 class="company-name text-primary">ADIL ENTERPRISE</h4>
                    <p class="company-details">
                        Industrial Area, Road 04, Dhaka<br>
                        Phone: +880 1700-000000<br>
                        Email: info@adilenterprise.com
                    </p>
                </div>
            </div>

            <div class="row info-row mb-4">
                <div class="col-4">
                    <div class="info-box">
                        <span class="info-label">BILLED TO</span>
                        <h5 class="customer-name text-uppercase">General Customer</h5>
                        <p class="customer-details">
                            Cash Sale / Walk-in<br>
                            Counter Transaction
                        </p>
                    </div>
                </div>

                <div class="col-4">
                    <div class="info-box border-start ps-3 border-primary">
                        <span class="info-label">SERVICE DETAILS</span>
                        <div class="d-flex flex-column gap-1 mt-2">
                            <p class="mb-0 small"><strong>SR:</strong> <span
                                    class="text-dark">{{ $sale->sr->name ?? 'N/A' }}</span></p>
                            <p class="mb-0 small"><strong>Delivery:</strong> <span
                                    class="text-dark">{{ $sale->delivery->name ?? 'N/A' }}</span></p>
                            <p class="mb-0 small"><strong>Route No:</strong> <span
                                    class="badge bg-secondary">{{ $sale->route_no ?? 'N/A' }}</span></p>
                        </div>
                    </div>
                </div>

                <div class="col-4 text-end">
                    <div class="info-box">
                        <span class="info-label">DATE & STATUS</span>
                        <p class="fw-bold mb-1 text-dark">{{ date('d-M-Y', strtotime($sale->sale_date)) }}</p>
                        <span class="badge {{ $sale->due_amount > 0 ? 'bg-danger' : 'bg-success' }} px-3 py-2">
                            {{ $sale->due_amount > 0 ? 'UNPAID / PARTIAL' : 'PAID IN FULL' }}
                        </span>
                    </div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 55%">Product Description</th>
                        <th style="width: 15%" class="text-center">Price</th>
                        <th style="width: 10%" class="text-center">Qty</th>
                        <th style="width: 15%" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mt-4 summary-section">
                <div class="col-7">
                    @if ($sale->customerDues->count() > 0)
                        <span class="info-label">CUSTOMER DUE ALLOCATION</span>
                        <table class="table table-sm table-bordered mt-2" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Customer Name</th>
                                    <th class="text-end">Due Amount</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale->customerDues as $due)
                                    <tr>
                                        <td>{{ $due->customer->name }}</td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($due->due_amount, 2) }}
                                        </td>
                                        <td class="text-muted">{{ $due->note ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if ($sale->remarks)
                        <p class="info-label mt-3">REMARKS</p>
                        <p class="small text-muted">{{ $sale->remarks }}</p>
                    @endif
                </div>

                <div class="col-5">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Subtotal:</td>
                            <td class="text-end">{{ number_format($sale->total_amount + $sale->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Discount:</td>
                            <td class="text-end text-danger">-{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr class="border-top border-dark fw-bold">
                            <td class="fs-5">Grand Total:</td>
                            <td class="text-end fs-5 text-primary">{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-success fw-bold">Paid:</td>
                            <td class="text-end text-success fw-bold">{{ number_format($sale->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-danger fw-bold">Balance Due:</td>
                            <td class="text-end text-danger fw-bold h5">{{ number_format($sale->due_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="invoice-footer text-center mt-5 pt-5">
                <div class="row">
                    <div class="col-4">
                        <div class="signature-line">Customer Signature</div>
                    </div>
                    <div class="col-4">
                        <div class="signature-line">SR / Delivery Signature</div>
                    </div>
                    <div class="col-4">
                        <div class="signature-line">Authorized Signature</div>
                    </div>
                </div>
                <p class="mt-4 small text-muted">Generated on {{ date('d-M-Y H:i A') }}</p>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary-color: #0d6efd;
            --border-color: #dee2e6;
        }

        .invoice-wrapper {
            background-color: #f8f9fa;
            padding: 20px 0;
        }

        .invoice-container {
            background: #fff;
            width: 210mm;
            min-height: 270mm;
            margin: 0 auto;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .info-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .invoice-table th {
            background: #f8f9fa;
            border-top: 2px solid #333;
            border-bottom: 1px solid #ccc;
            padding: 8px;
            font-size: 0.8rem;
        }

        .invoice-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            font-size: 0.75rem;
            padding-top: 5px;
        }

        @media print {
            body {
                background: white;
            }

            .d-print-none {
                display: none !important;
            }

            .invoice-wrapper {
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                border: none;
                width: 100%;
                padding: 10mm;
            }

            .invoice-table thead {
                display: table-header-group;
            }
        }

        @media print {

            /* Hide everything by default */
            body * {
                visibility: hidden;
            }

            /* Show only the invoice wrapper and its children */
            .invoice-wrapper,
            .invoice-wrapper * {
                visibility: visible;
            }

            /* Position the invoice at the very top-left of the printed page */
            .invoice-wrapper {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
                padding: 0;
                margin: 0;
            }

            .invoice-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 10mm !important;
            }

            /* Force background colors/images to print (Chrome/Safari) */
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Remove any browser-added headers/footers (date, URL, etc) */
            @page {
                margin: 5mm;
            }
        }
    </style>
@endsection
