@section('style')
    <style>
        /* =========================================
           1. LIGHT MODE FOCUS (Default / Root)
        ========================================= */
        .form-control:focus {
            border-color: #4e73df;
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        /* Select2 Light Focus */
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #4e73df !important;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25) !important;
        }

        /* Readonly Light */
        .form-control[readonly] {
            background-color: #f8f9fa !important;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
        }

        /* =========================================
           2. DARK MODE FOCUS ([data-bs-theme="dark"])
        ========================================= */
        [data-bs-theme="dark"] .form-control:focus {
            border-color: #3756b5;
            /* Slightly deeper blue for dark contrast */
            background-color: #2b3035;
            /* Bootstrap dark input bg */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            color: #fff;
        }

        /* Select2 Dark Focus */
        [data-bs-theme="dark"] .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3756b5 !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        /* Readonly Dark Fix */
        [data-bs-theme="dark"] .form-control[readonly] {
            background-color: #212529 !important;
            /* Darker background */
            border-color: #373b3e !important;
            color: #adb5bd !important;
            /* Lighter gray text for readability */
            cursor: not-allowed;
        }

        /* =========================================
           3. SELECT2 COMPONENT THEMING
        ========================================= */
        /* Light Mode (Default) */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
        }

        /* Dark Mode Select2 Colors */
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
            color: #fff;
        }

        [data-bs-theme="dark"] .select2-search__field {
            background-color: #343a40 !important;
            color: #fff !important;
            border: 1px solid #495057 !important;
        }

        /* =========================================
           4. UI ENHANCEMENTS
        ========================================= */
        .btn-gradient {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
            color: white;
        }

        #grand_total {
            font-weight: 800;
            font-size: 1.25rem;
        }
    </style>
@endsection
