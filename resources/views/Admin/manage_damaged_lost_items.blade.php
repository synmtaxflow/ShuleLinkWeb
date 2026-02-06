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

    .damaged-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .damaged-menu .list-group-item.active {
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
    .damaged-body {
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
                <h1>Damaged / Lost Items</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Damaged / Lost Items Management (Vifaa Vilivyoharibika / Kupotea)</strong>
        </div>
        <div class="card-body damaged-body">
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
            <div class="form-loading" id="damagedLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="form-progress"></div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group damaged-menu">
                        <a class="list-group-item active" data-target="#section-record-damaged">
                            <i class="fa fa-warning"></i> Record Damaged / Lost
                        </a>
                        <a class="list-group-item" data-target="#section-view-damaged">
                            <i class="fa fa-list"></i> View Records
                        </a>
                        <a class="list-group-item" data-target="#section-report-damaged">
                            <i class="fa fa-bar-chart"></i> Report
                        </a>
                    </div>
                    <div class="card border-primary-custom mt-3">
        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Quantity must not exceed available stock.<br>
                                - Records reduce resource quantity.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div id="section-record-damaged" class="damaged-section">
                        <div class="section-title">Record Damaged / Lost Items</div>
                        <form method="POST" action="{{ route('damaged_lost.store') }}" class="js-show-loading">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="dl_resource">Select Item</label>
                                <select class="form-select" id="dl_resource" name="resourceID" required>
                                    <option value="">-- Select Item --</option>
                                    @foreach(($resources ?? []) as $resource)
                                        <option
                                            value="{{ $resource->resourceID }}"
                                            data-requires="{{ $resource->requires_quantity ? 1 : 0 }}"
                                            data-available="{{ $resource->quantity ?? 0 }}"
                                        >
                                            {{ $resource->resource_name }} ({{ $resource->resource_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="dl_date">Date</label>
                                <input type="date" class="form-control" id="dl_date" name="record_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="dl_type">Type</label>
                                <select class="form-select" id="dl_type" name="record_type" required>
                                    <option value="damaged">Damaged (Imeharibika)</option>
                                    <option value="lost">Lost (Imepotea)</option>
                                    <option value="used_up">Used up (Imetumika ikaisha)</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="dl_quantity">Quantity</label>
                                <input type="number" class="form-control" id="dl_quantity" name="quantity" min="0" value="0">
                                <small class="muted-help" id="dl_available">Available: 0</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="dl_description">Description</label>
                                <textarea class="form-control" id="dl_description" name="description" rows="3" required></textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-check"></i> Save Record
                            </button>
                        </form>
                    </div>

                    <div id="section-view-damaged" class="damaged-section d-none">
                        <div class="section-title">Damaged / Lost Records</div>
                        @if(isset($records) && $records->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>Item</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            @php
                                                $resource = ($resources ?? collect())->firstWhere('resourceID', $record->resourceID);
                                            @endphp
                                            <tr>
                                                <td>{{ $record->record_date }}</td>
                                                <td>{{ $resource->resource_name ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($record->record_type) }}</td>
                                                <td>{{ $record->quantity ?? 'N/A' }}</td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-toggle="modal"
                                                        data-target="#editDamagedModal"
                                                        data-id="{{ $record->damaged_lostID }}"
                                                        data-resource="{{ $record->resourceID }}"
                                                        data-date="{{ $record->record_date }}"
                                                        data-type="{{ $record->record_type }}"
                                                        data-qty="{{ $record->quantity }}"
                                                        data-desc="{{ $record->description }}"
                                                    >
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('damaged_lost.delete') }}" style="display:inline-block;" class="js-show-loading">
                                                        @csrf
                                                        <input type="hidden" name="damaged_lostID" value="{{ $record->damaged_lostID }}">
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
                            <div class="alert alert-info">No damaged/lost records found.</div>
                        @endif
                    </div>

                    <div id="section-report-damaged" class="damaged-section d-none">
                        <div class="section-title">Resources Report</div>
                        @if(isset($resources) && $resources->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Item</th>
                                            <th>Current Quantity</th>
                                            <th>Total Damaged</th>
                                            <th>Total Lost</th>
                                            <th>Total Used Up</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resources as $resource)
                                            @php
                                                $row = ($summary ?? collect())->get($resource->resourceID);
                                                $damagedQty = $row ? ($row->total_damaged ?? 0) : 0;
                                                $lostQty = $row ? ($row->total_lost ?? 0) : 0;
                                                $usedUpQty = $row ? ($row->total_used_up ?? 0) : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $resource->resource_name }}</td>
                                                <td>{{ $resource->requires_quantity ? ($resource->quantity ?? 0) : 'N/A' }}</td>
                                                <td>{{ $resource->requires_quantity ? $damagedQty : 'N/A' }}</td>
                                                <td>{{ $resource->requires_quantity ? $lostQty : 'N/A' }}</td>
                                                <td>{{ $resource->requires_quantity ? $usedUpQty : 'N/A' }}</td>
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

<!-- Edit Modal -->
<div class="modal fade" id="editDamagedModal" tabindex="-1" role="dialog" aria-labelledby="editDamagedModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editDamagedModalLabel"><i class="fa fa-pencil"></i> Update Record</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('damaged_lost.update') }}" class="js-show-loading">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="damaged_lostID" id="edit_dl_id">
                    <div class="form-group mb-3">
                        <label for="edit_dl_resource">Item</label>
                        <select class="form-select" id="edit_dl_resource" name="resourceID" required disabled>
                            @foreach(($resources ?? []) as $resource)
                                <option value="{{ $resource->resourceID }}">{{ $resource->resource_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_dl_date">Date</label>
                        <input type="date" class="form-control" id="edit_dl_date" name="record_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_dl_type">Type</label>
                        <select class="form-select" id="edit_dl_type" name="record_type" required>
                            <option value="damaged">Damaged (Imeharibika)</option>
                            <option value="lost">Lost (Imepotea)</option>
                            <option value="used_up">Used up (Imetumika ikaisha)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_dl_quantity">Quantity</label>
                        <input type="number" class="form-control" id="edit_dl_quantity" name="quantity" min="0">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_dl_description">Description</label>
                        <textarea class="form-control" id="edit_dl_description" name="description" rows="3" required></textarea>
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
        const loadingBar = document.getElementById('damagedLoading');
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

        const menuItems = document.querySelectorAll('.damaged-menu .list-group-item');
        const sections = document.querySelectorAll('.damaged-section');

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

        const dlResource = document.getElementById('dl_resource');
        const dlQty = document.getElementById('dl_quantity');
        const dlAvailable = document.getElementById('dl_available');

        function updateAvailable() {
            if (!dlResource) return;
            const selected = dlResource.options[dlResource.selectedIndex];
            if (!selected) return;
            const requiresQty = selected.getAttribute('data-requires') === '1';
            const available = parseFloat(selected.getAttribute('data-available') || 0);
            dlQty.disabled = !requiresQty;
            if (!requiresQty) {
                dlQty.value = 0;
            }
            if (dlAvailable) {
                dlAvailable.textContent = 'Available: ' + available;
            }
        }

        if (dlResource) {
            dlResource.addEventListener('change', updateAvailable);
        }

        const editButtons = document.querySelectorAll('[data-target="#editDamagedModal"]');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_dl_id').value = this.getAttribute('data-id');
                document.getElementById('edit_dl_resource').value = this.getAttribute('data-resource');
                document.getElementById('edit_dl_date').value = this.getAttribute('data-date');
                document.getElementById('edit_dl_type').value = this.getAttribute('data-type');
                document.getElementById('edit_dl_quantity').value = this.getAttribute('data-qty') || 0;
                document.getElementById('edit_dl_description').value = this.getAttribute('data-desc') || '';
            });
        });
    })();
</script>
