@push('scripts')
    <script>
        $(document).ready(function() {
            // CSRF Header for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            var table = $('#CompanyDataTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('companies.index') }}", // Ensure this route is correct in web.php
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'fw-bold text-dark'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'opening_balance',
                        name: 'opening_balance'
                    },
                    {
                        data: 'ledger',
                        name: 'ledger',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ]
            });

            // Show Add Modal
            $('#btn_add').click(function() {
                $('#company_id').val('');
                $('#companyForm').trigger("reset");
                $('#modalTitle').text('Add New Company');
                $('#companyModal').modal('show');
            });

            // Show Edit Modal (using delegation for dynamically loaded buttons)
            $('body').on('click', '.editBtn', function() {
                $('#modalTitle').text('Edit Company');
                $('#company_id').val($(this).data('id'));
                $('#name').val($(this).data('name'));
                $('#email').val($(this).data('email'));
                $('#phone').val($(this).data('phone'));
                $('#address').val($(this).data('address'));
                $('#opening_balance').val($(this).data('opening_balance'));
                $('#companyModal').modal('show');
            });
        });
    </script>
@endpush
