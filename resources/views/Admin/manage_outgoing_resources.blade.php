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

    .outgoing-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .outgoing-menu .list-group-item.active {
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
    .outgoing-resources-body {
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
                <h1>Outgoing Resources</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Outgoing Resources Management (Rasilimali Zilizotoka / Zilizotumika)</strong>
        </div>
        <div class="card-body outgoing-resources-body">
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
            <div class="form-loading" id="outgoingLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="form-progress"></div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group outgoing-menu">
                        <a class="list-group-item active" data-target="#section-record-outgoing">
                            <i class="fa fa-upload"></i> Record Outgoing Resource
                        </a>
                        <a class="list-group-item" data-target="#section-view-outgoing">
                            <i class="fa fa-list"></i> View Outgoing Resources
                        </a>
                        <a class="list-group-item" data-target="#section-outgoing-report">
                            <i class="fa fa-bar-chart"></i> Report
                        </a>
                    </div>
                    <div class="card border-primary-custom mt-3">
        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Quantity must not exceed available stock.<br>
                                - Outgoing records reduce resource quantity.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div id="section-record-outgoing" class="outgoing-section">
                        <div class="section-title">Record Outgoing Resource</div>
                        <form method="POST" action="{{ route('outgoing_resources.store') }}" class="js-show-loading">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="outgoing_resource">Select Resource</label>
                                <select class="form-select" id="outgoing_resource" name="resourceID" required>
                                    <option value="">-- Select Resource --</option>
                                    @foreach(($resources ?? []) as $resource)
                                        <option
                                            value="{{ $resource->resourceID }}"
                                            data-requires="{{ $resource->requires_quantity ? 1 : 0 }}"
                                            data-requires-price="{{ $resource->requires_price ? 1 : 0 }}"
                                            data-price="{{ $resource->unit_price }}"
                                            data-available="{{ $resource->quantity ?? 0 }}"
                                        >
                                            {{ $resource->resource_name }} ({{ $resource->resource_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="outgoing_date">Date</label>
                                <input type="date" class="form-control" id="outgoing_date" name="outgoing_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="outgoing_type">Outgoing Type</label>
                                <select class="form-select" id="outgoing_type" name="outgoing_type" required>
                                    <option value="permanent">Permanent (Imeondoka kabisa)</option>
                                    <option value="temporary">Temporary (Imeazimwa / Imekodishwa)</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="outgoing_quantity">Quantity</label>
                                <input type="number" class="form-control" id="outgoing_quantity" name="quantity" min="0" value="0">
                                <small class="muted-help" id="available_note">Available: 0</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="outgoing_unit_price">Price (Per Unit)</label>
                                <input type="number" class="form-control" id="outgoing_unit_price" name="unit_price" min="0" step="0.01" value="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="outgoing_total_price">Total Price</label>
                                <input type="number" class="form-control" id="outgoing_total_price" readonly value="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="outgoing_description">Description (Used for what?)</label>
                                <textarea class="form-control" id="outgoing_description" name="description" rows="3" required></textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-check"></i> Record Outgoing
                            </button>
                        </form>
                    </div>

                    <div id="section-view-outgoing" class="outgoing-section d-none">
                        <div class="section-title">Outgoing Resources List</div>
                        @if(isset($outgoingRecords) && $outgoingRecords->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>Resource</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outgoingRecords as $record)
                                            @php
                                                $resource = ($resources ?? collect())->firstWhere('resourceID', $record->resourceID);
                                            @endphp
                                            <tr>
                                                <td>{{ $record->outgoing_date }}</td>
                                                <td>{{ $resource->resource_name ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($record->outgoing_type ?? 'permanent') }}</td>
                                                <td>
                                                    @if($record->outgoing_type === 'temporary')
                                                        @php
                                                            $returnedQty = $record->returned_quantity ?? 0;
                                                            $totalQty = $record->quantity ?? 0;
                                                            $remainingQty = max(0, $totalQty - $returnedQty);
                                                        @endphp
                                                        {{ $record->is_returned ? 'Returned' : 'Pending Return' }}
                                                        @if(!$record->is_returned && $totalQty)
                                                            <br><small class="muted-help">Remaining: {{ $remainingQty }}</small>
                                                        @endif
                                                    @else
                                                        Permanent
                                                    @endif
                                                </td>
                                                <td>{{ $record->quantity ?? 'N/A' }}</td>
                                                <td>{{ $record->total_price > 0 ? number_format($record->total_price, 0) : 'N/A' }}</td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-toggle="modal"
                                                        data-target="#editOutgoingModal"
                                                        data-id="{{ $record->outgoing_resourceID }}"
                                                        data-resource="{{ $record->resourceID }}"
                                                        data-date="{{ $record->outgoing_date }}"
                                                        data-type="{{ $record->outgoing_type }}"
                                                        data-returned="{{ $record->is_returned ? 1 : 0 }}"
                                                        data-returned-qty="{{ $record->returned_quantity ?? 0 }}"
                                                        data-qty="{{ $record->quantity }}"
                                                        data-price="{{ $record->unit_price }}"
                                                        data-desc="{{ $record->description }}"
                                                    >
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                    @if($record->outgoing_type === 'temporary' && !$record->is_returned)
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success"
                                                            data-toggle="modal"
                                                            data-target="#returnOutgoingModal"
                                                            data-id="{{ $record->outgoing_resourceID }}"
                                                            data-resource="{{ $resource->resource_name ?? 'N/A' }}"
                                                            data-qty="{{ $record->quantity ?? 0 }}"
                                                            data-returned-qty="{{ $record->returned_quantity ?? 0 }}"
                                                        >
                                                            <i class="fa fa-undo"></i> Return
                                                        </button>
                                                    @endif
                                                    <form method="POST" action="{{ route('outgoing_resources.delete') }}" style="display:inline-block;" class="js-show-loading">
                                                        @csrf
                                                        <input type="hidden" name="outgoing_resourceID" value="{{ $record->outgoing_resourceID }}">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">
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
                            <div class="alert alert-info">No outgoing resources recorded.</div>
                        @endif
                    </div>

                    <div id="section-outgoing-report" class="outgoing-section d-none">
                        <div class="section-title">Resources Report</div>
                        @if(isset($resources) && $resources->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Resource</th>
                                            <th>Current Quantity</th>
                                            <th>Total Outgoing</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resources as $resource)
                                            @php
                                                $summary = ($outgoingSummary ?? collect())->get($resource->resourceID);
                                                $outQty = $summary ? ($summary->total_quantity ?? 0) : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $resource->resource_name }}</td>
                                                <td>{{ $resource->requires_quantity ? ($resource->quantity ?? 0) : 'N/A' }}</td>
                                                <td>{{ $resource->requires_quantity ? $outQty : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No resources found.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- Return Outgoing Modal -->
<div class="modal fade" id="returnOutgoingModal" tabindex="-1" role="dialog" aria-labelledby="returnOutgoingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="returnOutgoingModalLabel"><i class="fa fa-undo"></i> Confirm Return</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('outgoing_resources.return') }}" class="js-show-loading">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="outgoing_resourceID" id="return_outgoing_id">
                    <div class="form-group mb-3">
                        <label>Resource</label>
                        <input type="text" class="form-control" id="return_resource_name" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label>Quantity Returned</label>
                        <input type="number" class="form-control" id="return_resource_qty" name="return_quantity" min="0" value="0">
                        <small class="muted-help" id="return_remaining_note">Remaining: 0</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="return_date">Return Date</label>
                        <input type="date" class="form-control" id="return_date" name="returned_at" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="return_description">Return Description</label>
                        <textarea class="form-control" id="return_description" name="return_description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-check"></i> Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Outgoing Modal -->
<div class="modal fade" id="editOutgoingModal" tabindex="-1" role="dialog" aria-labelledby="editOutgoingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editOutgoingModalLabel"><i class="fa fa-pencil"></i> Update Outgoing</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('outgoing_resources.update') }}" class="js-show-loading">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="outgoing_resourceID" id="edit_outgoing_id">
                    <div class="form-group mb-3">
                        <label for="edit_outgoing_resource">Resource</label>
                        <select class="form-select" id="edit_outgoing_resource" name="resourceID" required disabled>
                            @foreach(($resources ?? []) as $resource)
                                <option value="{{ $resource->resourceID }}">{{ $resource->resource_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_outgoing_date">Date</label>
                        <input type="date" class="form-control" id="edit_outgoing_date" name="outgoing_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_outgoing_type">Outgoing Type</label>
                        <select class="form-select" id="edit_outgoing_type" name="outgoing_type" required>
                            <option value="permanent">Permanent (Imeondoka kabisa)</option>
                            <option value="temporary">Temporary (Imeazimwa / Imekodishwa)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_outgoing_quantity">Quantity</label>
                        <input type="number" class="form-control" id="edit_outgoing_quantity" name="quantity" min="0">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_outgoing_unit_price">Price (Per Unit)</label>
                        <input type="number" class="form-control" id="edit_outgoing_unit_price" name="unit_price" min="0" step="0.01">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_outgoing_description">Description</label>
                        <textarea class="form-control" id="edit_outgoing_description" name="description" rows="3" required></textarea>
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
        const loadingBar = document.getElementById('outgoingLoading');
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

        const menuItems = document.querySelectorAll('.outgoing-menu .list-group-item');
        const sections = document.querySelectorAll('.outgoing-section');

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

        const outgoingResource = document.getElementById('outgoing_resource');
        const outgoingQty = document.getElementById('outgoing_quantity');
        const outgoingUnitPrice = document.getElementById('outgoing_unit_price');
        const outgoingTotal = document.getElementById('outgoing_total_price');
        const availableNote = document.getElementById('available_note');

        function updateOutgoingTotal() {
            if (!outgoingResource) return;
            const selected = outgoingResource.options[outgoingResource.selectedIndex];
            if (!selected) return;
            const requiresQty = selected.getAttribute('data-requires') === '1';
            const requiresPrice = selected.getAttribute('data-requires-price') === '1';
            const unitPrice = parseFloat(outgoingUnitPrice.value || 0);
            const qty = parseFloat(outgoingQty.value || 0);
            const available = parseFloat(selected.getAttribute('data-available') || 0);
            outgoingQty.disabled = !requiresQty;
            outgoingUnitPrice.disabled = !requiresPrice;
            if (!requiresQty) {
                outgoingQty.value = 0;
            }
            if (!requiresPrice) {
                outgoingUnitPrice.value = 0;
            }
            outgoingTotal.value = (requiresPrice ? (requiresQty ? unitPrice * qty : unitPrice) : 0).toFixed(2);
            if (availableNote) {
                availableNote.textContent = 'Available: ' + available;
            }
        }

        if (outgoingResource) {
            outgoingResource.addEventListener('change', () => {
                const selected = outgoingResource.options[outgoingResource.selectedIndex];
                if (!selected) return;
                const price = selected.getAttribute('data-price') || '0';
                outgoingUnitPrice.value = price;
                updateOutgoingTotal();
            });
        }
        if (outgoingQty) outgoingQty.addEventListener('input', updateOutgoingTotal);
        if (outgoingUnitPrice) outgoingUnitPrice.addEventListener('input', updateOutgoingTotal);

        const editButtons = document.querySelectorAll('[data-target="#editOutgoingModal"]');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const isReturned = this.getAttribute('data-returned') === '1';
                if (isReturned) {
                    alert('Returned records cannot be edited.');
                    return;
                }
                document.getElementById('edit_outgoing_id').value = this.getAttribute('data-id');
                document.getElementById('edit_outgoing_resource').value = this.getAttribute('data-resource');
                document.getElementById('edit_outgoing_date').value = this.getAttribute('data-date');
                document.getElementById('edit_outgoing_type').value = this.getAttribute('data-type') || 'permanent';
                document.getElementById('edit_outgoing_quantity').value = this.getAttribute('data-qty') || 0;
                document.getElementById('edit_outgoing_unit_price').value = this.getAttribute('data-price') || 0;
                document.getElementById('edit_outgoing_description').value = this.getAttribute('data-desc') || '';
            });
        });

        const returnButtons = document.querySelectorAll('[data-target="#returnOutgoingModal"]');
        returnButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const totalQty = parseFloat(this.getAttribute('data-qty') || 0);
                const returnedQty = parseFloat(this.getAttribute('data-returned-qty') || 0);
                const remaining = Math.max(0, totalQty - returnedQty);
                document.getElementById('return_outgoing_id').value = this.getAttribute('data-id');
                document.getElementById('return_resource_name').value = this.getAttribute('data-resource');
                document.getElementById('return_resource_qty').value = remaining;
                document.getElementById('return_resource_qty').max = remaining;
                const remainingNote = document.getElementById('return_remaining_note');
                if (remainingNote) {
                    remainingNote.textContent = 'Remaining: ' + remaining;
                }
                document.getElementById('return_description').value = '';
            });
        });
    })();
</script>
