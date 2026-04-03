@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-gray-800">Return Details: <span class="text-primary">{{ $return->return_no }}</span>
            </h2>
            <div>
                <a href="{{ route('sales_returns.index') }}" class="btn btn-outline-secondary border rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </a>
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 ms-2">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold border-bottom pb-3 mb-3">General Information</h5>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-bold">Customer</label>
                            <p class="fw-bold mb-0 text-dark">{{ $return->customer->name }}</p>
                            <p class="text-muted small">{{ $return->customer->email ?? 'No Email' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-bold">Return Date</label>
                            <p class="fw-bold mb-0">{{ date('d M, Y', strtotime($return->return_date)) }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-bold">Total Refund Value</label>
                            <p class="fw-bold mb-0 text-success h4">${{ number_format($return->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <label class="text-muted small text-uppercase fw-bold">Remarks</label>
                            <p class="text-muted italic">{{ $return->remarks ?? 'No remarks provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Returned Items</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($return->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $item->product->name }}</span>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end">Grand Total:</td>
                                        <td class="text-end text-success">${{ number_format($return->total_amount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {

            .btn,
            .page-header,
            nav {
                display: none !important;
            }

            .card {
                border: none !important;
                shadow: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
@endsection
