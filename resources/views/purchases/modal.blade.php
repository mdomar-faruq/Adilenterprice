<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Details: <span id="modal_pur_no"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6"><strong>Company:</strong> <span id="modal_company"></span></div>
                    <div class="col-6 text-end"><strong>Date:</strong> <span id="modal_date"></span></div>
                </div>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="purchase_items_body"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Grand Total:</th>
                            <th id="modal_total" class="text-end"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
