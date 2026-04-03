@section('style')
    <style>
        .btn-gradient {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
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
    </style>
@endsection
