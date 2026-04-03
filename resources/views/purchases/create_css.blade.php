@section('style')
    <style>
        /* 1. Base Design & Buttons */
        .btn-gradient {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
            color: white;
            transition: all 0.2s;
        }

        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: white;
        }

        /* 2. Focus States - Light Mode */
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        /* 3. Readonly Styles - Light Mode */
        .form-control[readonly] {
            background-color: #f8f9fa !important;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 1;
        }

        /* 4. DARK MODE OVERRIDES (Using data-bs-theme) */
        [data-bs-theme="dark"] .card {
            background-color: #2b3035;
            border-color: #373b3e;
        }

        [data-bs-theme="dark"] .form-control:focus {
            border-color: #3756b5;
            background-color: #2b3035;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        [data-bs-theme="dark"] .form-control[readonly] {
            background-color: #212529 !important;
            border-color: #373b3e !important;
            color: #adb5bd !important;
        }

        /* 5. Select2 Theme Fixes */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 12px !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-selection--single {
            background-color: #2b3035 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .select2-dropdown {
            background-color: #212529;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .select2-search__field {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        /* 6. Grand Total Highlight */
        #grand_total {
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
    </style>
@endsection
