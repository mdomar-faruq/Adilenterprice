@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header with Button Group Filters --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-0">Business Analytics</h2>
                <p class="text-muted mb-0">Real-time data insights</p>
            </div>

            <div class="btn-group  shadow-sm rounded-pill p-1" role="group">
                <input type="radio" class="btn-check" name="period" id="daily" value="daily">
                <label class="btn btn-outline-primary rounded-pill border-0 px-4" for="daily">Today</label>

                <input type="radio" class="btn-check" name="period" id="weekly" value="weekly" checked>
                <label class="btn btn-outline-primary rounded-pill border-0 px-4" for="weekly">Weekly</label>

                <input type="radio" class="btn-check" name="period" id="monthly" value="monthly">
                <label class="btn btn-outline-primary rounded-pill border-0 px-4" for="monthly">Monthly</label>

                <input type="radio" class="btn-check" name="period" id="yearly" value="yearly">
                <label class="btn btn-outline-primary rounded-pill border-0 px-4" for="yearly">Yearly</label>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2"><i class="bi bi-graph-up-arrow fs-3"></i></div>
                        <h6 class="text-muted small fw-bold text-uppercase">Sales</h6>
                        <h4 class="fw-bold mb-0">TK <span id="sales-val">0.00</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <div class="text-success mb-2"><i class="bi bi-cart-check fs-3"></i></div>
                        <h6 class="text-muted small fw-bold text-uppercase">Purchases</h6>
                        <h4 class="fw-bold mb-0">TK <span id="purchases-val">0.00</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <div class="text-danger mb-2"><i class="bi bi-wallet2 fs-3"></i></div>
                        <h6 class="text-muted small fw-bold text-uppercase">Expenses</h6>
                        <h4 class="fw-bold mb-0">TK <span id="expenses-val">0.00</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2"><i class="bi bi-people fs-3"></i></div>
                        <h6 class="text-muted small fw-bold text-uppercase">Customers</h6>
                        <h4 class="fw-bold mb-0"><span id="customers-val">0</span></h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Card --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Revenue vs Expense Flow</h5>
                    <div id="loader" class="spinner-border spinner-border-sm text-primary d-none"></div>
                </div>
                <div style="height: 400px;">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let myChart;
        const ctx = document.getElementById('mainChart').getContext('2d');

        function loadData(period) {
            $('#loader').removeClass('d-none');
            $.get(`/dashboard/data/${period}`, function(res) {
                // Update Card Numbers
                $('#sales-val').text(res.cards.sales);
                $('#purchases-val').text(res.cards.purchases);
                $('#expenses-val').text(res.cards.expenses);
                $('#customers-val').text(res.cards.customers);

                // Update Chart
                if (myChart) myChart.destroy();
                myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: res.chart.labels,
                        datasets: [{
                                label: 'Sales',
                                data: res.chart.sales,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Expenses',
                                data: res.chart.expenses,
                                borderColor: '#dc3545',
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                $('#loader').addClass('d-none');
            });
        }

        $(document).ready(function() {
            loadData('weekly'); // Default
            $('input[name="period"]').on('change', function() {
                loadData($(this).val());
            });
        });
    </script>
@endpush
