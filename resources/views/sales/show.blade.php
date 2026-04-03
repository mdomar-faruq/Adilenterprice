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
                    <h4 class="company-name">ADIL ENTERPRISE</h4>
                    <p class="company-details">
                        Industrial Area, Road 04, Dhaka<br>
                        Phone: +880 1700-000000<br>
                        Email: info@adilenterprise.com
                    </p>
                </div>
            </div>

            <div class="row info-row mb-4">
                <div class="col-6">
                    <div class="info-box">
                        <span class="info-label">BILLED TO</span>
                        <h5 class="customer-name">{{ $sale->customer->name }}</h5>
                        <p class="customer-details">
                            {{ $sale->customer->address }}<br>
                            {{ $sale->customer->phone }}
                        </p>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="info-box">
                        <span class="info-label">DATE</span>
                        <p class="fw-bold">{{ date('d-M-Y', strtotime($sale->sale_date)) }}</p>
                        <span class="info-label">PAYMENT STATUS</span>
                        <p class="fw-bold {{ $sale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $sale->due_amount > 0 ? 'DUE / PARTIAL' : 'PAID' }}
                        </p>
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
                    @if ($sale->remarks)
                        <p class="info-label">REMARKS</p>
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
                    <div class="col-4"></div>
                    <div class="col-4">
                        <div class="signature-line">Authorized Signature</div>
                    </div>
                </div>
                <p class="mt-4 small text-muted">Thank you for choosing Adil Enterprise!</p>
            </div>
        </div>
    </div>

    <style>
        /* CSS for Screen and Print */
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
            /* A4 Width */
            min-height: 297mm;
            /* A4 Height */
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-title {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -2px;
        }

        .invoice-id {
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: -10px;
        }

        .company-name {
            font-weight: 800;
            text-transform: uppercase;
        }

        .company-details {
            font-size: 0.85rem;
            color: #6c757d;
            line-height: 1.4;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #adb5bd;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .invoice-table th {
            background: #f8f9fa;
            border-top: 2px solid #343a40;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 8px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .invoice-table td {
            padding: 12px 8px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
            vertical-align: top;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            font-size: 0.8rem;
            padding-top: 5px;
        }

        /* Critical Print Rules */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: none;
                -webkit-print-color-adjust: exact !important;
            }

            nav,
            aside,
            .navbar,
            .d-print-none,
            footer,
            header {
                display: none !important;
            }

            .invoice-wrapper {
                background: none;
                padding: 0;
                margin: 0;
            }

            .invoice-container {
                box-shadow: none;
                margin: 0;
                padding: 15mm;
                width: 100%;
                min-height: auto;
            }

            /* Repeat Table Header on every page */
            .invoice-table thead {
                display: table-header-group;
            }

            .invoice-table tr {
                page-break-inside: avoid;
            }

            .summary-section {
                page-break-inside: avoid;
            }
        }
    </style>
@endsection
