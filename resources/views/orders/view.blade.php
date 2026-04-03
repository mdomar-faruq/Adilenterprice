<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-muted text-uppercase small fw-bold">Customer Info</h6>
            <p class="mb-0"><strong>Name:</strong> {{ $order->customer->name ?? 'N/A' }}</p>
            <p class="mb-0"><strong>Order No:</strong> {{ $order->order_no }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <h6 class="text-muted text-uppercase small fw-bold">Order Timing</h6>
            <p class="mb-0"><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d M, Y') }}
            </p>
            <p class="mb-0"><strong>Status:</strong>
                <span class="badge {{ $order->status == 'pending' ? 'bg-warning text-dark' : 'bg-success' }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-light">
                <tr>
                    <th style="width: 50px">#</th>
                    <th>Product Name</th>
                    <th class="text-center">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach ($order->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
                        <td class="text-center">{{ $item->qty }}</td>
                    </tr>
                    @php $totalQty += $item->qty; @endphp
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-end">Total Sum:</td>
                    <td class="text-center text-primary">{{ $totalQty }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
