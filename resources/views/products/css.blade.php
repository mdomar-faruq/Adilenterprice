@section('style')
    <style>
        /* Modern Card & Table Reset */
        .product-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            background-color: var(--bs-card-bg);
        }

        [data-bs-theme="dark"] .product-card {
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Typography - Making text "Normal" and clean */
        .table thead th {
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--bs-secondary-color);
            border-top: none;
        }

        .table tbody td {
            font-weight: 400;
            /* Normal text weight */
            color: var(--bs-body-color);
            padding: 1rem 0.75rem;
        }

        /* Search Bar refinement */
        .dataTables_filter input {
            border-radius: 8px !important;
            padding: 7px 12px !important;
            border: 1px solid var(--bs-border-color) !important;
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
            width: 250px !important;
            outline: none;
        }

        /* Buttons Grouping */
        .dt-buttons {
            gap: 5px;
        }

        .dt-buttons .btn {
            font-weight: 500 !important;
            border: none !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.2s ease;
        }

        /* Custom Colors for Exports */
        .btn-excel {
            background-color: #1d6f42 !important;
            color: #fff !important;
        }

        .btn-pdf {
            background-color: #f40f02 !important;
            color: #fff !important;
        }

        .btn-print {
            background-color: #55acee !important;
            color: #fff !important;
        }

        /* Mobile specific spacing */
        @media (max-width: 768px) {
            .dataTables_filter input {
                width: 100% !important;
                margin-top: 10px;
            }

            .dt-buttons {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
@endsection
