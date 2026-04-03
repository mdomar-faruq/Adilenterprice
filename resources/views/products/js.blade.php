@push('scripts')
    <script>
        //datatable product index
        $(document).ready(function() {
            var table = $('#productDataTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('products.index') }}",
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-4"Bf>rtip',
                buttons: [{
                        text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel All',
                        className: 'btn btn-excel btn-sm rounded shadow-sm',
                        action: function(e, dt, node, config) {
                            // Optional: Show a simple alert so user knows it started
                            const originalText = $(node).html();
                            $(node).html(
                                '<span class="spinner-border spinner-border-sm me-1"></span> Exporting...'
                            );
                            $(node).addClass('disabled');

                            // Trigger the download
                            window.location.href = "{{ route('products.export') }}";

                            // Reset button after a few seconds
                            setTimeout(() => {
                                $(node).html(originalText);
                                $(node).removeClass('disabled');
                            }, 3000);
                        }
                    }, // Added missing comma here
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                        className: 'btn btn-pdf btn-sm rounded shadow-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3],
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer me-1"></i> Print',
                        className: 'btn btn-print btn-sm rounded shadow-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3],
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ],
                columns: [{
                        data: 'id',
                        name: 'id'
                    }, // 1
                    {
                        data: 'name',
                        name: 'name'
                    }, // 2
                    {
                        data: 'unit',
                        name: 'unit'
                    }, // 3
                    {
                        data: 'purchase_price',
                        name: 'purchase_price'
                    }, // 4
                    {
                        data: 'percent',
                        name: 'percent'
                    }, // 5
                    {
                        data: 'sale_price',
                        name: 'sale_price'
                    }, // 6
                    {
                        data: 'stock',
                        name: 'stock'
                    }, // 7
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    } // 8
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search products...",
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });
        });
    </script>

    <script>
        //calculate percent
        $(document).ready(function() {
            // Function to calculate sales price
            function calculateSalesPrice(modalId) {
                let purchase = parseFloat($(modalId + ' [name="purchase_price"]').val()) || 0;
                let margin = parseFloat($(modalId + ' [name="percent"]').val()) || 0;

                // Calculation: Purchase + (Purchase * Margin / 100)
                let salesPrice = purchase + (purchase * (margin / 100));

                // Update the Sales Price field (fixed to 2 decimal places)
                $(modalId + ' [name="sale_price"]').val(salesPrice.toFixed(2));
            }

            // Listen for changes in Add Modal
            $('#addModal [name="purchase_price"], #addModal [name="percent"]').on('input', function() {
                calculateSalesPrice('#addModal');
            });

            // Listen for changes in Edit Modal
            $('#editModal [name="purchase_price"], #editModal [name="percent"]').on('input', function() {
                calculateSalesPrice('#editModal');
            });
        });
    </script>

    <script>
        //edit product
        function fillEditModal(product) {
            // 1. Fill Text and Number inputs
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_purchase_price').value = product.purchase_price;
            document.getElementById('edit_percent').value = product.percent;
            document.getElementById('edit_sale_price').value = product.sale_price;

            // 2. Set the Dropdown value for Unit
            // This finds the <option> that matches the product's unit_id
            document.getElementById('edit_unit_id').value = product.unit_id;

            // 3. Set the Form Action URL
            document.getElementById('editForm').action = '/products/' + product.id;
        }
    </script>

    <script>
        //delete
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endpush
