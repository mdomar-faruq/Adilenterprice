<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdilEnterprice</title>
    <link href="{{ asset('css/bootstrap_5_3.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap_icon_5_3.css') }}" rel="stylesheet">

    {{-- //datatable use cdn --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    {{-- //select 2 4.1 min --}}
    <link href="{{ asset('css/select2_4_1.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2_bootstrap_5_theme.css') }}" rel="stylesheet">


    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bs-body-bg);
            transition: background-color 0.3s ease;
        }

        .navbar {
            border-bottom: 1px solid rgba(0, 0, 0, .1);
            backdrop-filter: blur(10px);
        }

        [data-bs-theme="dark"] .navbar {
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
        }

        .theme-toggle-btn {
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .theme-toggle-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] .theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }



        <style>.page-header {
            background: #ffffff;
            padding: 1.5rem 0;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            /* Sleeker separator */
            font-size: 1.2rem;
            line-height: 1;
            vertical-align: middle;
        }

        .btn-gradient {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
            color: white;
        }

        .text-muted-custom {
            color: #858796;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }
    </style>
    @yield('style')
</head>

<body>

    <header class="navbar navbar-expand-lg sticky-top bg-body-tertiary">
        <div class="container-xl">
            <a class="navbar-brand fw-bold text-primary" href="/">
                ADILENTERPRICE
            </a>

            <div class="d-flex align-items-center order-lg-last ms-2">
                <div class="theme-toggle-btn me-2" id="themeToggle">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>

                <button class="navbar-toggler ms-2 border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="/"><i class="bi bi-house-door me-1"></i> Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-box-seam me-1"></i> Purchase
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item" href="/companies">Company</a></li>
                            <li><a class="dropdown-item" href="/purchases">Purchase</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-box-seam me-1"></i> Sales
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            {{-- <li><a class="dropdown-item" href="/orders">Orders</a></li> --}}
                            <li><a class="dropdown-item" href="/sales">Sales</a></li>
                            {{-- <li><a class="dropdown-item" href="/returns">Return</a></li> --}}

                            <li><a class="dropdown-item" href="/sales/sr-sales">SR Sales Report</a></li>
                            <li><a class="dropdown-item" href="/sales/company-sales">Company Sales Report</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/returns">
                            <!-- Settings icon with text -->
                            <i class="bi bi-box-seam me-1"></i> Return
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-map me-1"></i> Inventory
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item" href="/products">Product</a></li>
                            <li><a class="dropdown-item" href="/products/stock_value_report">Stock Value</a></li>
                            <li><a class="dropdown-item" href="/products/damage_report">Product Damage</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-graph-up"></i> Finance

                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item" href="/dsr/ledger">DSR Ledger</a></li>
                            <li><a class="dropdown-item" href="/payments/create">Money Receipt</a></li>
                            <li><a class="dropdown-item" href="/purchase_payments/create">Payment Voucher</a></li>
                            <li><a class="dropdown-item" href="/expenses">Expenses</a></li>
                            <li><a class="dropdown-item" href="/accounts">Accounts</a></li>
                            <li><a class="dropdown-item" href="/accounts/profit-loss-report">Profit & Loss
                                    Statement</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <!-- Settings icon with text -->
                            <i class="bi bi-gear me-1"></i> Settings
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item" href="/employees">Employees</a></li>
                        </ul>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-map me-1"></i> Inventory</a>
                    </li> --}}
                </ul>
            </div>
        </div>
    </header>

    <main class="container-xl py-4">
        {{-- Global Session Messages --}}
        @if (session('success') || session('error'))
            <div class="row justify-content-center">
                <div class="col-md-8">
                    {{-- Paste the Alert Code Block here --}}
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="{{ asset('js/bootstrap_5_3.js') }}"></script>
    {{-- cdn datatable --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    {{-- //Select2 4.1 --}}
    <script src="{{ asset('js/select2_4_1.js') }}"></script>

    {{-- Sweetalart --}}
    <script src="{{ asset('js/sweetalart.js') }}"></script>


    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        // Check for saved theme in localStorage
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.replace('bi-moon-stars', 'bi-sun');
            } else {
                themeIcon.classList.replace('bi-sun', 'bi-moon-stars');
            }
        }
    </script>


    {{-- //Session Message  --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find all alerts
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(function(alert) {
                // Wait 3 seconds, then hide
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 3000);
            });
        });
    </script>



    @stack('scripts')

</body>

</html>
