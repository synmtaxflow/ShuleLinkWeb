@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }

    .resources-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .resources-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
    }
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
    }
    .muted-help {
        color: #6c757d;
        font-size: 0.9rem;
    }
    .incoming-resources-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Incoming Resources</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Incoming Resources Management (Rasilimali Zinazoingia / Zilizopokelewa)</strong>
        </div>
        <div class="card-body incoming-resources-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-loading" id="resourcesLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="form-progress"></div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group resources-menu">
                        <a class="list-group-item active" data-target="#section-add-resources">
                            <i class="fa fa-plus-circle"></i> Add New Resources
                        </a>
                        <a class="list-group-item" data-target="#section-view-resources">
                            <i class="fa fa-list"></i> View Resources
                        </a>
                        <a class="list-group-item" data-target="#section-record-incoming">
                            <i class="fa fa-download"></i> Record Incoming Resources
                        </a>
                    </div>
                    <div class="card border-primary-custom mt-3">
        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Quantity required only for quantity-based resources.<br>
                                - Incoming resources increase stock quantity.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div id="section-add-resources" class="resources-section">
                        <div class="section-title">Add New Resources</div>
                        <form method="POST" action="{{ route('school_resources.store') }}" class="js-show-loading">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered" id="resourcesTable">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Resource Name</th>
                                            <th>Type</th>
                                            <th>Requires Quantity</th>
                                            <th>Quantity</th>
                                            <th>Requires Price</th>
                                            <th>Price (Per Unit)</th>
                                            <th>Total Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="resource-row">
                                            <td><input type="text" class="form-control" name="resources[0][resource_name]" required></td>
                                            <td>
                                                <select class="form-select" name="resources[0][resource_type]" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Physical">Physical</option>
                                                    <option value="Learning">Learning</option>
                                                    <option value="ICT">ICT</option>
                                                    <option value="Utility">Utility</option>
                                                    <option value="Consumable">Consumable</option>
                                                    <option value="Financial">Financial</option>
                                                    <option value="Human">Human</option>
                                                    <option value="Laboratory Apparatus">Laboratory Apparatus</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select requires-qty" name="resources[0][requires_quantity]">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control qty-input" name="resources[0][quantity]" min="0" value="0"></td>
                                            <td>
                                                <select class="form-select requires-price" name="resources[0][requires_price]">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control price-input" name="resources[0][unit_price]" min="0" step="0.01" value="0"></td>
                                            <td><input type="number" class="form-control total-input" readonly value="0"></td>
                                            <td><button type="button" class="btn btn-sm btn-danger remove-row">Remove</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-light mb-3" id="addResourceRow">
                                <i class="fa fa-plus"></i> Add Another Resource
                            </button>
                            <div>
                                <button class="btn btn-primary-custom" type="submit">
                                    <i class="fa fa-save"></i> Save Resources
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="section-view-resources" class="resources-section d-none">
                        <div class="section-title">Resources List</div>
                        @if(isset($resources) && $resources->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resources as $resource)
                                            <tr>
                                                <td>{{ $resource->resource_name }}</td>
                                                <td>{{ $resource->resource_type }}</td>
                                                <td>{{ $resource->requires_quantity ? ($resource->quantity ?? 0) : 'N/A' }}</td>
                                                <td>{{ $resource->requires_price ? number_format($resource->unit_price, 0) : 'N/A' }}</td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-toggle="modal"
                                                        data-target="#editResourceModal"
                                                        data-id="{{ $resource->resourceID }}"
                                                        data-name="{{ $resource->resource_name }}"
                                                        data-type="{{ $resource->resource_type }}"
                                                        data-requires="{{ $resource->requires_quantity ? 1 : 0 }}"
                                                        data-requires-price="{{ $resource->requires_price ? 1 : 0 }}"
                                                        data-qty="{{ $resource->quantity }}"
                                                        data-price="{{ $resource->unit_price }}"
                                                    >
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('school_resources.delete') }}" style="display:inline-block;" class="js-show-loading">
                                                        @csrf
                                                        <input type="hidden" name="resourceID" value="{{ $resource->resourceID }}">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this resource?')">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No resources found.</div>
                        @endif
                    </div>

                    <div id="section-record-incoming" class="resources-section d-none">
                        <div class="section-title">Record Incoming Resources</div>
                        <form method="POST" action="{{ route('incoming_resources.store') }}" class="js-show-loading">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="incoming_resource">Select Resource</label>
                                <select class="form-select" id="incoming_resource" name="resourceID" required>
                                    <option value="">-- Select Resource --</option>
                                    @foreach(($resources ?? []) as $resource)
                                        <option
                                            value="{{ $resource->resourceID }}"
                                            data-requires="{{ $resource->requires_quantity ? 1 : 0 }}"
                                            data-requires-price="{{ $resource->requires_price ? 1 : 0 }}"
                                            data-price="{{ $resource->unit_price }}"
                                        >
                                            {{ $resource->resource_name }} ({{ $resource->resource_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="received_date">Date Received</label>
                                <input type="date" class="form-control" id="received_date" name="received_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="incoming_quantity">Quantity</label>
                                <input type="number" class="form-control" id="incoming_quantity" name="quantity" min="0" value="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="incoming_unit_price">Price (Per Unit)</label>
                                <input type="number" class="form-control" id="incoming_unit_price" name="unit_price" min="0" step="0.01" value="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="incoming_total_price">Total Price</label>
                                <input type="number" class="form-control" id="incoming_total_price" readonly value="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="incoming_note">Note</label>
                                <textarea class="form-control" id="incoming_note" name="note" rows="3"></textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-check"></i> Record Incoming
                            </button>
                        </form>

                        @if(isset($incomingRecords) && $incomingRecords->count() > 0)
                            <div class="section-title mt-4">Recent Incoming</div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>Resource</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incomingRecords as $record)
                                            @php
                                                $resource = ($resources ?? collect())->firstWhere('resourceID', $record->resourceID);
                                            @endphp
                                            <tr>
                                                <td>{{ $record->received_date }}</td>
                                                <td>{{ $resource->resource_name ?? 'N/A' }}</td>
                                                <td>{{ $record->quantity ?? 'N/A' }}</td>
                                                <td>{{ $record->total_price > 0 ? number_format($record->total_price, 0) : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- Edit Resource Modal -->
<div class="modal fade" id="editResourceModal" tabindex="-1" role="dialog" aria-labelledby="editResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editResourceModalLabel"><i class="fa fa-pencil"></i> Update Resource</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school_resources.update') }}" class="js-show-loading">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="resourceID" id="edit_resource_id">
                    <div class="form-group mb-3">
                        <label for="edit_resource_name">Resource Name</label>
                        <input type="text" class="form-control" id="edit_resource_name" name="resource_name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_resource_type">Type</label>
                        <select class="form-select" id="edit_resource_type" name="resource_type" required>
                            <option value="Physical">Physical</option>
                            <option value="Learning">Learning</option>
                            <option value="ICT">ICT</option>
                            <option value="Utility">Utility</option>
                            <option value="Consumable">Consumable</option>
                            <option value="Financial">Financial</option>
                            <option value="Human">Human</option>
                            <option value="Laboratory Apparatus">Laboratory Apparatus</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_requires_quantity">Requires Quantity</label>
                        <select class="form-select" id="edit_requires_quantity" name="requires_quantity">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_requires_price">Requires Price</label>
                        <select class="form-select" id="edit_requires_price" name="requires_price">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_quantity">Quantity</label>
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_unit_price">Price (Per Unit)</label>
                        <input type="number" class="form-control" id="edit_unit_price" name="unit_price" min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const loadingBar = document.getElementById('resourcesLoading');
        const loadingForms = document.querySelectorAll('.js-show-loading');
        if (loadingForms.length) {
            loadingForms.forEach(form => {
                form.addEventListener('submit', () => {
                    if (loadingBar) {
                        loadingBar.style.display = 'flex';
                    }
                });
            });
        }

        const menuItems = document.querySelectorAll('.resources-menu .list-group-item');
        const sections = document.querySelectorAll('.resources-section');

        function showSection(targetId) {
            sections.forEach(section => {
                if (section.id === targetId) {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
        }

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const target = this.getAttribute('data-target');
                if (target) {
                    showSection(target.replace('#', ''));
                }
            });
        });

        const resourcesTable = document.getElementById('resourcesTable');
        const addResourceRow = document.getElementById('addResourceRow');

        function updateRowTotal(row) {
            const requiresQty = row.querySelector('.requires-qty').value === '1';
            const requiresPrice = row.querySelector('.requires-price').value === '1';
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const totalInput = row.querySelector('.total-input');
            const qty = parseFloat(qtyInput.value || 0);
            const price = parseFloat(priceInput.value || 0);
            const total = requiresPrice ? (requiresQty ? qty * price : price) : 0;
            totalInput.value = total.toFixed(2);
            qtyInput.disabled = !requiresQty;
            priceInput.disabled = !requiresPrice;
            if (!requiresQty) {
                qtyInput.value = 0;
            }
            if (!requiresPrice) {
                priceInput.value = 0;
            }
        }

        function bindRowEvents(row) {
            row.querySelector('.requires-qty').addEventListener('change', () => updateRowTotal(row));
            row.querySelector('.requires-price').addEventListener('change', () => updateRowTotal(row));
            row.querySelector('.qty-input').addEventListener('input', () => updateRowTotal(row));
            row.querySelector('.price-input').addEventListener('input', () => updateRowTotal(row));
            row.querySelector('.remove-row').addEventListener('click', () => {
                if (resourcesTable.querySelectorAll('tbody tr').length > 1) {
                    row.remove();
                }
            });
            updateRowTotal(row);
        }

        if (resourcesTable) {
            resourcesTable.querySelectorAll('tbody tr').forEach(bindRowEvents);
        }

        if (addResourceRow) {
            addResourceRow.addEventListener('click', () => {
                const tbody = resourcesTable.querySelector('tbody');
                const index = tbody.querySelectorAll('tr').length;
                const row = document.createElement('tr');
                row.className = 'resource-row';
                row.innerHTML = `
                    <td><input type="text" class="form-control" name="resources[${index}][resource_name]" required></td>
                    <td>
                        <select class="form-select" name="resources[${index}][resource_type]" required>
                            <option value="">Select Type</option>
                            <option value="Physical">Physical</option>
                            <option value="Learning">Learning</option>
                            <option value="ICT">ICT</option>
                            <option value="Utility">Utility</option>
                            <option value="Consumable">Consumable</option>
                            <option value="Financial">Financial</option>
                            <option value="Human">Human</option>
                            <option value="Laboratory Apparatus">Laboratory Apparatus</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select requires-qty" name="resources[${index}][requires_quantity]">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control qty-input" name="resources[${index}][quantity]" min="0" value="0"></td>
                    <td>
                        <select class="form-select requires-price" name="resources[${index}][requires_price]">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control price-input" name="resources[${index}][unit_price]" min="0" step="0.01" value="0"></td>
                    <td><input type="number" class="form-control total-input" readonly value="0"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">Remove</button></td>
                `;
                tbody.appendChild(row);
                bindRowEvents(row);
            });
        }

        const incomingResource = document.getElementById('incoming_resource');
        const incomingQty = document.getElementById('incoming_quantity');
        const incomingUnitPrice = document.getElementById('incoming_unit_price');
        const incomingTotal = document.getElementById('incoming_total_price');

        function updateIncomingTotal() {
            if (!incomingResource) return;
            const selected = incomingResource.options[incomingResource.selectedIndex];
            if (!selected) return;
            const requiresQty = selected.getAttribute('data-requires') === '1';
            const requiresPrice = selected.getAttribute('data-requires-price') === '1';
            const unitPrice = parseFloat(incomingUnitPrice.value || 0);
            const qty = parseFloat(incomingQty.value || 0);
            incomingQty.disabled = !requiresQty;
            incomingUnitPrice.disabled = !requiresPrice;
            if (!requiresQty) {
                incomingQty.value = 0;
            }
            if (!requiresPrice) {
                incomingUnitPrice.value = 0;
            }
            incomingTotal.value = (requiresPrice ? (requiresQty ? unitPrice * qty : unitPrice) : 0).toFixed(2);
        }

        if (incomingResource) {
            incomingResource.addEventListener('change', () => {
                const selected = incomingResource.options[incomingResource.selectedIndex];
                if (!selected) return;
                const price = selected.getAttribute('data-price') || '0';
                incomingUnitPrice.value = price;
                updateIncomingTotal();
            });
        }
        if (incomingQty) incomingQty.addEventListener('input', updateIncomingTotal);
        if (incomingUnitPrice) incomingUnitPrice.addEventListener('input', updateIncomingTotal);

        const editButtons = document.querySelectorAll('[data-target="#editResourceModal"]');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_resource_id').value = this.getAttribute('data-id');
                document.getElementById('edit_resource_name').value = this.getAttribute('data-name') || '';
                document.getElementById('edit_resource_type').value = this.getAttribute('data-type') || '';
                document.getElementById('edit_requires_quantity').value = this.getAttribute('data-requires') || '1';
                document.getElementById('edit_requires_price').value = this.getAttribute('data-requires-price') || '1';
                document.getElementById('edit_quantity').value = this.getAttribute('data-qty') || 0;
                document.getElementById('edit_unit_price').value = this.getAttribute('data-price') || 0;
            });
        });
    })();
</script>
