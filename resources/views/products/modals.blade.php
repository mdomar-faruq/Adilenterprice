{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('products.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            {{-- <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold m-0">Create New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> --}}
            <div class="modal-header btn-gradient text-white">
                <h5 class="modal-title" id="modalTitle">Create New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Company</label>
                    <select name="company_id" class="form-control select2-basic" required>
                        <option value="">Select Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted text-uppercase">Product Name</label>
                    <input type="text" name="name" class="form-control border-secondary-subtle"
                        placeholder="Enter product name" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                    <select name="unit_id" class="form-select border-secondary-subtle" required>
                        <option value="">Select Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ $unit->id == 1 ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Purchase</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-secondary-subtle small">TK</span>
                            <input type="number" step="0.01" name="purchase_price" value="0"
                                class="form-control border-secondary-subtle" onclick="this.select()" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Margin</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="percent" value="0"
                                class="form-control border-secondary-subtle" required>
                            <span class="input-group-text bg-light border-secondary-subtle small">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Sales</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-secondary-subtle small">Tk</span>
                            <input type="number" step="0.01" name="sale_price" value="0"
                                class="form-control border-secondary-subtle" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 p-4">
                <button type="submit"
                    class="btn btn-gradient text-white w-100 rounded-pill py-2 fw-bold shadow-sm">Confirm
                    & Save
                    Product</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editForm" method="POST" class="modal-content border-0 shadow-lg">
            @csrf @method('PUT')
            <div class="modal-header btn-gradient text-white">
                <h5 class="modal-title" id="modalTitle">Update Product Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Company</label>
                    <select name="company_id" id="edit_company_id" class="form-control select2-basic" required>
                        <option value="">Select Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted text-uppercase">Product Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control border-secondary-subtle"
                        required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                    <select name="unit_id" id="edit_unit_id" class="form-select border-secondary-subtle" required>
                        <option value="">Select Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-primary text-uppercase">Opening Stock (Initial)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary-subtle border-secondary-subtle"><i
                                class="bi bi-box-seam"></i></span>
                        <input type="number" step="0.01" name="opening_stock" id="edit_opening_stock"
                            class="form-control border-secondary-subtle fw-bold" required>
                    </div>
                    <div class="form-text text-danger small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Changing this will update the current available stock.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Purchase</label>
                        <input type="number" step="0.01" name="purchase_price" id="edit_purchase_price"
                            class="form-control border-secondary-subtle" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Margin (%)</label>
                        <input type="number" step="0.01" name="percent" id="edit_percent"
                            class="form-control border-secondary-subtle" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Sales Price</label>
                        <input type="number" step="0.01" name="sale_price" id="edit_sale_price"
                            class="form-control border-secondary-subtle" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 p-4">
                <button type="submit"
                    class="btn btn-gradient text-white w-100 rounded-pill py-2 fw-bold shadow-sm">Update
                    Changes</button>
            </div>
        </form>
    </div>
</div>
