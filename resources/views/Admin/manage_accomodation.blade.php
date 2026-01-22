@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
    }

    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
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

    .accommodation-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .accommodation-menu .list-group-item.active {
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

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 12px;
    }

    .btn-primary-custom i {
        margin-right: 8px;
    }

    .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
    }

    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .badge-success {
        background-color: var(--success-color);
        color: white;
    }

    .badge-danger {
        background-color: var(--danger-color);
        color: white;
    }

    .badge-warning {
        background-color: var(--warning-color);
        color: #212529;
    }

    .badge-info {
        background-color: var(--info-color);
        color: white;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-sm {
        padding: 5px 12px;
        font-size: 0.875rem;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background-color: var(--info-color);
        color: white;
    }

    .btn-edit:hover {
        background-color: #138496;
    }

    .btn-delete {
        background-color: var(--danger-color);
        color: white;
    }

    .btn-delete:hover {
        background-color: #c82333;
    }

    .btn-view {
        background-color: var(--success-color);
        color: white;
    }

    .btn-view:hover {
        background-color: #218838;
    }

    .item-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .item-badge {
        background-color: #e9ecef;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .item-badge i {
        color: var(--primary-color);
    }

    .mattress-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 5px;
    }

    .mattress-yes {
        background-color: #d4edda;
        color: #155724;
    }

    .mattress-no {
        background-color: #fff3cd;
        color: #856404;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 6px;
        display: block;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 200px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #f8f9fa;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-item label {
        margin: 0;
        cursor: pointer;
        font-weight: normal;
    }

    .item-quantity {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .item-quantity input {
        width: 60px;
        padding: 4px 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        text-align: center;
    }

    .hidden {
        display: none !important;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1.1rem;
        margin: 0;
    }

    .alert-info {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .alert-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .bed-map-block {
        border: 1px solid #e9ecef;
        padding: 12px;
        margin-bottom: 12px;
        background: #fafafa;
    }
    .bed-map-title {
        font-weight: 600;
        margin-bottom: 8px;
    }
    .bed-map-room {
        margin-bottom: 10px;
        padding: 8px;
        background: #ffffff;
        border: 1px solid #eee;
    }
    .bed-map-room-title {
        font-weight: 600;
        margin-bottom: 6px;
        font-size: 0.95rem;
    }
    .bed-pill {
        display: inline-block;
        padding: 6px 10px;
        margin: 4px;
        border-radius: 12px;
        font-size: 0.85rem;
        cursor: pointer;
        border: 1px solid transparent;
    }
    .bed-pill.available {
        background: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }
    .bed-pill.occupied {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    .bed-pill.maintenance {
        background: #d1ecf1;
        color: #0c5460;
        border-color: #bee5eb;
    }
    .bed-pill.inactive {
        background: #e2e3e5;
        color: #6c757d;
        border-color: #d6d8db;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage Accommodation</h1>
    </div>
        </div>
        </div>
    </div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Accommodation Management</strong>
        </div>
        <div class="card-body">
            <div class="form-loading" id="accommodationLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="form-progress"></div>
                            </div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group accommodation-menu">
                        <a class="list-group-item active" data-target="#section-register-block">
                            <i class="fa fa-building"></i> Register Block
                        </a>
                        <a class="list-group-item" data-target="#section-view-block">
                            <i class="fa fa-eye"></i> View Blocks
                        </a>
                        <a class="list-group-item" data-target="#section-register-room">
                            <i class="fa fa-door-open"></i> Register Room
                        </a>
                        <a class="list-group-item" data-target="#section-register-bed">
                            <i class="fa fa-bed"></i> Register Beds
                        </a>
                        <a class="list-group-item" data-target="#section-view-bed">
                            <i class="fa fa-th-large"></i> View Beds
                        </a>
        </div>
                    <div class="card border-primary-custom mt-3">
                        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Register blocks before rooms.<br>
                                - Only blocks with rooms appear in room registration.<br>
                                - View blocks shows total rooms and beds.
    </div>
        </div>
    </div>
</div>

                <div class="col-md-8">
                    <div id="section-register-block" class="accommodation-section">
                        <div class="section-title">Register Block</div>
                <form id="blockForm">
                    <input type="hidden" id="blockID">
                    <div class="row">
                                <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Block Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="blockName" required placeholder="e.g., Block A, Block B">
                        </div>
                                <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="blockLocation" required placeholder="e.g., East Wing">
                        </div>
                                <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Block Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="blockType" required onchange="toggleBlockTypeOptions()">
                                <option value="">Select Type</option>
                                <option value="with_rooms">With Rooms (Block has rooms)</option>
                                <option value="without_rooms">Without Rooms (Block is beds only, like a hall)</option>
                            </select>
                        </div>
                                <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="blockStatus" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">Sex Needed <span class="text-danger">*</span></label>
                                    <select class="form-control" id="blockSex" required>
                                        <option value="">Select</option>
                                        <option value="Male">Male (Boys)</option>
                                        <option value="Female">Female (Girls)</option>
                                        <option value="Mixed">Mixed</option>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group mb-3" id="blockItemsSection" style="display: none;">
                            <label class="form-label">Block Items (For blocks without rooms)</label>
                            <div class="checkbox-group" id="blockItemsList">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="blockItem_table" value="table" onchange="toggleItemQuantity('blockItem_table', 'blockItemQty_table')">
                                    <label for="blockItem_table">Tables</label>
                                    <div class="item-quantity" id="blockItemQty_table" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="blockTableQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="blockItem_chair" value="chair" onchange="toggleItemQuantity('blockItem_chair', 'blockItemQty_chair')">
                                    <label for="blockItem_chair">Chairs</label>
                                    <div class="item-quantity" id="blockItemQty_chair" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="blockChairQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="blockItem_cabinet" value="cabinet" onchange="toggleItemQuantity('blockItem_cabinet', 'blockItemQty_cabinet')">
                                    <label for="blockItem_cabinet">Cabinets</label>
                                    <div class="item-quantity" id="blockItemQty_cabinet" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="blockCabinetQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="blockItem_other" value="other" onchange="toggleItemQuantity('blockItem_other', 'blockItemQty_other')">
                                    <label for="blockItem_other">Other Items</label>
                                    <div class="item-quantity" id="blockItemQty_other" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="blockOtherQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                            </div>
                                    <small class="text-muted">Beds for blocks without rooms should be added separately.</small>
                        </div>
                                <div class="col-md-12 form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="blockDescription" rows="3" placeholder="Additional description about the block..."></textarea>
                        </div>
                    </div>
                            <div class="d-flex flex-wrap">
                                <button type="button" class="btn btn-primary-custom mr-2" id="saveBlockBtn" onclick="saveBlock()">
                    <i class="fa fa-save"></i> <span id="saveBlockBtnText">Save</span>
                </button>
                                <button type="button" class="btn btn-secondary" id="resetBlockBtn">
                                    <i class="fa fa-refresh"></i> Reset
                </button>
            </div>
                        </form>
        </div>

                    <div id="section-view-block" class="accommodation-section d-none">
                        <div class="section-title">View Blocks</div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>Block Name</th>
                                        <th>Location</th>
                                        <th>Rooms</th>
                                        <th>Beds</th>
                                        <th>Block Type</th>
                                        <th>Sex</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="blocksTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="empty-state">
                                                <i class="fa fa-building"></i>
                                                <p>No blocks added yet. Use Register Block to add a new block.</p>
    </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
</div>
            </div>

                    <div id="section-register-room" class="accommodation-section d-none">
                        <div class="section-title">Register Room</div>
                <form id="roomForm">
                            <div class="form-group mb-3">
                            <label class="form-label">Select Block <span class="text-danger">*</span></label>
                                <select class="form-control" id="roomBlockID" required>
                                <option value="">Select Block</option>
                            </select>
                                <small class="text-muted d-none" id="blockTypeHint"></small>
                        </div>
                            <div id="roomRows"></div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-secondary" id="addRoomRowBtn">
                                    <i class="fa fa-plus"></i> Add Another Room
                                </button>
                        </div>
                            <div class="d-flex flex-wrap">
                                <button type="button" class="btn btn-primary-custom mr-2" id="saveRoomBtn" onclick="saveRoom()">
                                    <i class="fa fa-save"></i> Save Rooms
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetRoomBtn">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                        </div>
                </form>
                        </div>

                    <div id="section-register-bed" class="accommodation-section d-none">
                        <div class="section-title">Register Beds</div>
                        <form id="bedRegisterForm">
                            <div class="form-group mb-3">
                                <label class="form-label">Select Room (Blocks with Rooms)</label>
                                <select class="form-control" id="bedRoomSelect">
                                    <option value="">Select Room</option>
                                </select>
                                    </div>
                            <div class="text-muted mb-2">OR</div>
                            <div class="form-group mb-3">
                                <label class="form-label">Select Block (Hall / No Rooms)</label>
                                <select class="form-control" id="bedHallBlockID">
                                    <option value="">Select Hall Block</option>
                            </select>
                        </div>
                            <div id="bedRows"></div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-secondary" id="addBedRowBtn">
                                    <i class="fa fa-plus"></i> Add Another Bed
                                </button>
                        </div>
                            <div class="d-flex flex-wrap">
                                <button type="button" class="btn btn-primary-custom mr-2" id="saveBedsBtn" onclick="saveBeds()">
                                    <i class="fa fa-save"></i> Save Beds
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetBedsBtn">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                    </div>
                </form>
            </div>

                    <div id="section-view-bed" class="accommodation-section d-none">
                        <div class="section-title">View Beds</div>
                        <div id="bedMapContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Bed Modal -->
<div class="modal fade" id="bedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bedModalTitle">
                    <i class="fa fa-bed"></i> Add New Bed
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bedForm">
                    <input type="hidden" id="bedID">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Select Block <span class="text-danger">*</span></label>
                            <select class="form-control" id="bedBlockID" required onchange="loadRoomsForBed()">
                                <option value="">Select Block</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Select Room (Optional)</label>
                            <select class="form-control" id="bedRoomID">
                                <option value="">Select Room (Leave empty if bed is in block/hall)</option>
                            </select>
                            <small class="text-muted">Leave empty if the bed is directly in the block (for blocks without rooms like halls)</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Bed Number</label>
                            <input type="text" class="form-control" id="bedNumber" placeholder="e.g., BED-001, BED-002">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Has Mattress? <span class="text-danger">*</span></label>
                            <select class="form-control" id="bedHasMattress" required>
                                <option value="Yes">Yes - Bed has mattress</option>
                                <option value="No">No - Student must bring their own mattress</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="alert-info" id="mattressInfoYes" style="display: none;">
                                <i class="fa fa-info-circle"></i> <strong>Bed has mattress:</strong> This bed comes with a mattress. Students do not need to bring their own.
                            </div>
                            <div class="alert-warning" id="mattressInfoNo" style="display: none;">
                                <i class="fa fa-exclamation-triangle"></i> <strong>No mattress:</strong> This bed does not have a mattress. <strong>Students must bring their own mattress.</strong>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="bedStatus" required>
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="bedDescription" rows="3" placeholder="Additional description about the bed..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBedBtn" onclick="saveBed()">
                    <i class="fa fa-save"></i> <span id="saveBedBtnText">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bed Detail / Assignment Modal -->
<div class="modal fade" id="bedDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-info-circle"></i> Bed Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="bedDetailInfo" class="mb-3"></div>
                <div id="bedAssignWarning" class="alert alert-warning d-none"></div>

                <div id="bedOccupiedSection" class="d-none">
                    <div class="section-title">Student in Bed</div>
                    <div id="bedStudentInfo"></div>
                </div>

                <div id="bedMoveSection" class="d-none">
                    <div class="section-title">Move / Swap Student</div>
                    <div class="form-group mb-2">
                        <label class="form-label">Select Target Bed</label>
                        <select class="form-control" id="bedMoveTarget">
                            <option value="">Select Bed</option>
                        </select>
                        <small class="text-muted">Select an available bed to move, or occupied bed to swap.</small>
                    </div>
                    <button type="button" class="btn btn-primary-custom" id="bedMoveBtn">
                        <i class="fa fa-exchange"></i> Move / Swap
                    </button>
                </div>
                <div id="bedAssignSection" class="d-none">
                    <div class="section-title">Assign Student</div>
                    <div class="form-group mb-2">
                        <label class="form-label">Search Student (Active)</label>
                        <input type="text" class="form-control" id="bedStudentSearch" placeholder="Search by name or admission number">
                    </div>
                    <div id="bedStudentResults"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger d-none" id="bedRemoveStudentBtn">
                    <i class="fa fa-user-times"></i> Remove Student
                </button>
                <button type="button" class="btn btn-info" id="bedEditBtn">
                    <i class="fa fa-edit"></i> Edit Bed
                </button>
                <button type="button" class="btn btn-danger" id="bedDeleteBtn">
                    <i class="fa fa-trash"></i> Delete Bed
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global variables
    let blocks = [];
    let rooms = [];
    let beds = [];
    let bedAssignments = [];

    // Initialize on page load
    $(document).ready(function() {
        loadBlocks();
        loadRooms();
        loadBeds();
        loadBedAssignments();
        initAccommodationMenu();
        bindRoomRowHandlers();
        bindBedRowHandlers();
        resetBlockForm();
        resetRoomForm();
        resetBedRegisterForm();

        $('#resetBlockBtn').on('click', function() {
            resetBlockForm();
        });
        $('#resetRoomBtn').on('click', function() {
            resetRoomForm();
        });
        $('#addRoomRowBtn').on('click', function() {
            addRoomRow();
        });
        $('#roomBlockID').on('change', function() {
            checkBlockType();
        });
        $('#addBedRowBtn').on('click', function() {
            addBedRow();
        });
        $('#resetBedsBtn').on('click', function() {
            resetBedRegisterForm();
        });
        $('#bedRoomSelect').on('change', function() {
            if ($(this).val()) {
                $('#bedHallBlockID').val('');
            }
        });
        $('#bedHallBlockID').on('change', function() {
            if ($(this).val()) {
                $('#bedRoomSelect').val('');
            }
        });
        $('#bedMapContainer').on('click', '.bed-pill', function() {
            const bedId = $(this).data('bed-id');
            openBedDetail(bedId);
        });
        $('#bedStudentSearch').on('input', function() {
            const query = $(this).val().trim();
            searchStudentsForBed(query);
        });
        $('#bedRemoveStudentBtn').on('click', function() {
            const bedId = $('#bedDetailModal').data('bed-id');
            if (bedId) {
                removeStudentFromBed(bedId);
            }
        });
        $('#bedEditBtn').on('click', function() {
            const bedId = $('#bedDetailModal').data('bed-id');
            if (bedId) {
                $('#bedDetailModal').modal('hide');
                editBed(bedId);
            }
        });
        $('#bedDeleteBtn').on('click', function() {
            const bedId = $('#bedDetailModal').data('bed-id');
            if (bedId) {
                deleteBed(bedId);
            }
        });
        $('#bedMoveBtn').on('click', function() {
            const bedId = $('#bedDetailModal').data('bed-id');
            const targetBedId = $('#bedMoveTarget').val();
            if (!bedId || !targetBedId) {
                showAlert('warning', 'Missing Data', 'Select a target bed first.');
                return;
            }
            moveStudentToBed(bedId, targetBedId);
        });
        
        // Show/hide mattress info based on selection
        $('#bedHasMattress').on('change', function() {
            toggleMattressInfo();
        });
    });

    function initAccommodationMenu() {
        $('.accommodation-menu .list-group-item').on('click', function() {
            $('.accommodation-menu .list-group-item').removeClass('active');
            $(this).addClass('active');
            const target = $(this).data('target');
            showAccommodationSection(target);
        });
    }

    function showAccommodationSection(target) {
        $('.accommodation-section').addClass('d-none');
        $(target).removeClass('d-none');
    }

    function showAlert(icon, title, text) {
        if (typeof Swal === 'undefined') {
            alert(text);
            return;
        }
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonColor: '#940000'
        });
    }

    function setAccommodationLoading(isLoading) {
        const loadingBar = $('#accommodationLoading');
        if (!loadingBar.length) return;
        if (isLoading) {
            loadingBar.css('display', 'flex');
        } else {
            loadingBar.hide();
        }
    }

    // Toggle mattress information
    function toggleMattressInfo() {
        const hasMattress = $('#bedHasMattress').val();
        $('#mattressInfoYes').toggle(hasMattress === 'Yes');
        $('#mattressInfoNo').toggle(hasMattress === 'No');
    }

    // Toggle block type options
    function toggleBlockTypeOptions() {
        const blockType = $('#blockType').val();
        const blockItemsSection = $('#blockItemsSection');
        
        if (blockType === 'without_rooms') {
            blockItemsSection.show();
        } else {
            blockItemsSection.hide();
            // Clear all checkboxes and quantities
            $('#blockItemsList input[type="checkbox"]').prop('checked', false);
            $('#blockItemsList .item-quantity').hide();
        }
    }

    // Toggle item quantity input
    function toggleItemQuantity(checkboxId, quantityDivId) {
        const checkbox = $('#' + checkboxId);
        const quantityDiv = $('#' + quantityDivId);
        
        if (checkbox.is(':checked')) {
            quantityDiv.show();
        } else {
            quantityDiv.hide();
            quantityDiv.find('input').val(1);
        }
    }

    // Check block type when selecting block for room
    function checkBlockType() {
        const blockID = $('#roomBlockID').val();
        const block = blocks.find(b => b.blockID == blockID);
        const hint = $('#blockTypeHint');
        const rowsContainer = $('#roomRows');
        const addBtn = $('#addRoomRowBtn');

        if (!blockID) {
            hint.addClass('d-none');
            rowsContainer.show();
            addBtn.prop('disabled', false);
            return;
        }
        
        if (block && block.blockType === 'without_rooms') {
            hint.text('⚠️ This block has no rooms. It is beds only (hall type).');
            hint.removeClass('d-none').css('color', '#ffc107');
            rowsContainer.hide();
            addBtn.prop('disabled', true);
        } else {
            hint.addClass('d-none');
            rowsContainer.show();
            addBtn.prop('disabled', false);
        }
    }

    function resetBlockForm() {
        const form = $('#blockForm')[0];
        if (form) {
            form.reset();
        }
        $('#blockID').val('');
        $('#blockItemsSection').hide();
        $('#blockItemsList input[type="checkbox"]').prop('checked', false);
        $('#blockItemsList .item-quantity').hide();
        $('#saveBlockBtnText').text('Save');
    }

    function bindRoomRowHandlers() {
        $('#roomRows').on('click', '.remove-room-row', function() {
            if ($('#roomRows .room-row').length <= 1) {
                showAlert('warning', 'Not allowed', 'At least one room row is required.');
                return;
            }
            $(this).closest('.room-row').remove();
        });
        $('#roomRows').on('change', '.room-item-check', function() {
            const rowItem = $(this).closest('.room-item');
            const qtyContainer = rowItem.find('.room-item-qty');
            if ($(this).is(':checked')) {
                qtyContainer.removeClass('d-none');
            } else {
                qtyContainer.addClass('d-none');
                qtyContainer.find('input').val(1);
            }
        });
    }

    function resetRoomForm() {
        const form = $('#roomForm')[0];
        if (form) {
            form.reset();
        }
        $('#roomForm').data('edit-room-id', '');
        populateRoomBlocks();
        $('#roomRows').empty();
        addRoomRow();
        checkBlockType();
    }

    function bindBedRowHandlers() {
        $('#bedRows').on('click', '.remove-bed-row', function() {
            if ($('#bedRows .bed-row').length <= 1) {
                showAlert('warning', 'Not allowed', 'At least one bed row is required.');
                return;
            }
            $(this).closest('.bed-row').remove();
        });
    }

    function resetBedRegisterForm() {
        const form = $('#bedRegisterForm')[0];
        if (form) {
            form.reset();
        }
        $('#bedRows').empty();
        addBedRow();
        populateBedRegisterOptions();
    }

    function addBedRow(value = '') {
        const rowId = Date.now() + Math.floor(Math.random() * 1000);
        const bedNumber = value || '';
        $('#bedRows').append(`
            <div class="bed-row border p-2 mb-2" data-row-id="${rowId}">
                <div class="row">
                    <div class="col-md-8 form-group mb-2">
                        <label class="form-label">Bed Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bed-number" value="${bedNumber}" placeholder="e.g., BED-001" required>
                    </div>
                    <div class="col-md-4 form-group mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-delete remove-bed-row">
                            <i class="fa fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `);
    }

    function populateBedRegisterOptions() {
        const hallSelect = $('#bedHallBlockID');
        const roomSelect = $('#bedRoomSelect');
        hallSelect.empty().append('<option value="">Select Hall Block</option>');
        roomSelect.empty().append('<option value="">Select Room</option>');

        const hallBlocks = blocks.filter(b => b.status === 'Active' && b.blockType === 'without_rooms');
        hallBlocks.forEach(block => {
            hallSelect.append(`<option value="${block.blockID}">${block.blockName}</option>`);
        });

        const activeRooms = rooms.filter(r => r.status === 'Active');
        activeRooms.forEach(room => {
            const block = blocks.find(b => b.blockID == room.blockID);
            const blockName = block ? block.blockName : 'Unknown Block';
            roomSelect.append(`<option value="${room.roomID}">${room.roomName} (${room.roomNumber}) - ${blockName}</option>`);
        });
    }

    function addRoomRow(values = {}) {
        const rowId = Date.now() + Math.floor(Math.random() * 1000);
        const roomName = values.roomName || '';
        const roomNumber = values.roomNumber || '';
        const capacity = values.capacity || 4;

        $('#roomRows').append(`
            <div class="room-row border p-2 mb-2" data-row-id="${rowId}">
                <div class="row">
                    <div class="col-md-5 form-group mb-2">
                        <label class="form-label">Room Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control room-name" value="${roomName}" placeholder="e.g., Room A, Room B" required>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="form-label">Room Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control room-number" value="${roomNumber}" placeholder="e.g., 101, 102" required>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control room-capacity" value="${capacity}" min="1">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label class="form-label">Items Inside the Room</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item room-item">
                            <input type="checkbox" class="room-item-check" data-type="table">
                            <label>Tables</label>
                            <div class="item-quantity room-item-qty d-none">
                                <label>Quantity:</label>
                                <input type="number" class="form-control room-item-qty-input" value="1" min="1">
                            </div>
                        </div>
                        <div class="checkbox-item room-item">
                            <input type="checkbox" class="room-item-check" data-type="chair">
                            <label>Chairs</label>
                            <div class="item-quantity room-item-qty d-none">
                                <label>Quantity:</label>
                                <input type="number" class="form-control room-item-qty-input" value="1" min="1">
                            </div>
                        </div>
                        <div class="checkbox-item room-item">
                            <input type="checkbox" class="room-item-check" data-type="cabinet">
                            <label>Cabinets</label>
                            <div class="item-quantity room-item-qty d-none">
                                <label>Quantity:</label>
                                <input type="number" class="form-control room-item-qty-input" value="1" min="1">
                            </div>
                        </div>
                        <div class="checkbox-item room-item">
                            <input type="checkbox" class="room-item-check" data-type="wardrobe">
                            <label>Wardrobes</label>
                            <div class="item-quantity room-item-qty d-none">
                                <label>Quantity:</label>
                                <input type="number" class="form-control room-item-qty-input" value="1" min="1">
                            </div>
                        </div>
                        <div class="checkbox-item room-item">
                            <input type="checkbox" class="room-item-check" data-type="other">
                            <label>Other Items</label>
                            <div class="item-quantity room-item-qty d-none">
                                <label>Quantity:</label>
                                <input type="number" class="form-control room-item-qty-input" value="1" min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-delete remove-room-row">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        `);
    }

    function populateRoomRowItems(row, items) {
        if (!items || !items.length) {
            return;
        }
        items.forEach(item => {
            const checkbox = row.find(`.room-item-check[data-type="${item.itemType}"]`);
            if (!checkbox.length) {
                return;
            }
            checkbox.prop('checked', true);
            const qtyContainer = checkbox.closest('.room-item').find('.room-item-qty');
            qtyContainer.removeClass('d-none');
            qtyContainer.find('input').val(item.quantity || 1);
        });
    }

    function populateRoomBlocks() {
        const blockSelect = $('#roomBlockID');
        blockSelect.empty();
        blockSelect.append('<option value="">Select Block</option>');
        const availableBlocks = blocks.filter(b => b.status === 'Active' && b.blockType === 'with_rooms');
        availableBlocks.forEach(block => {
            blockSelect.append(`<option value="${block.blockID}">${block.blockName}</option>`);
        });
        if (availableBlocks.length === 0) {
            blockSelect.prop('disabled', true);
            const hint = $('#blockTypeHint');
            hint.text('No blocks with rooms available. Please register a block with rooms first.');
            hint.removeClass('d-none').css('color', '#dc3545');
        } else {
            blockSelect.prop('disabled', false);
            $('#blockTypeHint').addClass('d-none');
        }
    }

    // Load rooms for bed assignment
    function loadRoomsForBed() {
        const blockID = $('#bedBlockID').val();
        const roomSelect = $('#bedRoomID');
        
        roomSelect.empty();
        roomSelect.append('<option value="">Select Room (Leave empty if bed is in block/hall)</option>');
        
        if (blockID) {
            const block = blocks.find(b => b.blockID == blockID);
            if (block && block.blockType === 'with_rooms') {
                const blockRooms = rooms.filter(r => r.blockID == blockID && r.status === 'Active');
                blockRooms.forEach(room => {
                    roomSelect.append(`<option value="${room.roomID}">${room.roomName} (${room.roomNumber})</option>`);
                });
            } else {
                // Block without rooms - show message
                roomSelect.append('<option value="">This block has no rooms (hall type)</option>');
            }
        }
    }

    // Show Add Block Modal
    function showAddBlockModal() {
        resetBlockForm();
        $('.accommodation-menu .list-group-item').removeClass('active');
        $('.accommodation-menu .list-group-item[data-target="#section-register-block"]').addClass('active');
        showAccommodationSection('#section-register-block');
    }

    // Show Edit Block Modal
    function editBlock(blockID) {
        const block = blocks.find(b => b.blockID == blockID);
        if (!block) return;

        $('#blockID').val(block.blockID);
        $('#blockName').val(block.blockName);
        $('#blockLocation').val(block.location);
        $('#blockType').val(block.blockType);
        $('#blockStatus').val(block.status);
        $('#blockSex').val(block.blockSex || block.sex || '');
        $('#blockDescription').val(block.description || '');

        toggleBlockTypeOptions();

        // Load block items if any
        if (block.items && block.items.length > 0) {
            block.items.forEach(item => {
                const checkboxId = `blockItem_${item.itemType}`;
                const quantityId = `blockItemQty_${item.itemType}`;
                $(`#${checkboxId}`).prop('checked', true);
                $(`#${quantityId}`).show();
                $(`#block${item.itemType.charAt(0).toUpperCase() + item.itemType.slice(1)}Quantity`).val(item.quantity);
            });
        }

        $('#saveBlockBtnText').text('Update');
        $('.accommodation-menu .list-group-item').removeClass('active');
        $('.accommodation-menu .list-group-item[data-target="#section-register-block"]').addClass('active');
        showAccommodationSection('#section-register-block');
    }

    // Save Block
    function saveBlock() {
        const form = $('#blockForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const blockType = $('#blockType').val();
        const items = [];

        // Collect items if block type is without_rooms
        if (blockType === 'without_rooms') {
            const itemTypes = ['table', 'chair', 'cabinet', 'other'];
            itemTypes.forEach(itemType => {
                const checkbox = $(`#blockItem_${itemType}`);
                if (checkbox.is(':checked')) {
                    const quantityInput = $(`#block${itemType.charAt(0).toUpperCase() + itemType.slice(1)}Quantity`);
                    items.push({
                        itemType: itemType,
                        quantity: parseInt(quantityInput.val()) || 1
                    });
                }
            });
        }

        const blockData = {
            blockID: $('#blockID').val() || null,
            blockName: $('#blockName').val(),
            location: $('#blockLocation').val(),
            blockType: blockType,
            status: $('#blockStatus').val(),
            blockSex: $('#blockSex').val(),
            sex: $('#blockSex').val(),
            description: $('#blockDescription').val(),
            items: items
        };

        // AJAX call to save block
        const saveBtn = $('#saveBlockBtn');
        saveBtn.prop('disabled', true);
        setAccommodationLoading(true);
        $.ajax({
            url: '/api/accommodation/blocks',
            method: blockData.blockID ? 'PUT' : 'POST',
            data: blockData,
            success: function(response) {
                loadBlocks();
                showAlert('success', 'Success', 'Block saved successfully!');
                resetBlockForm();
            },
            error: function(xhr) {
                console.error('Error saving block:', xhr);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error saving block. Please try again.';
                showAlert('error', 'Failed', msg);
            },
            complete: function() {
                saveBtn.prop('disabled', false);
                setAccommodationLoading(false);
            }
        });
    }

    // Delete Block
    function deleteBlock(blockID) {
        if (typeof Swal === 'undefined') {
        if (!confirm('Are you sure you want to delete this block? This will also delete all rooms and beds in this block.')) {
            return;
            }
            return performBlockDelete(blockID);
        }
        Swal.fire({
            icon: 'warning',
            title: 'Delete Block?',
            text: 'This will also delete all rooms and beds in this block.',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete'
        }).then(result => {
            if (result.isConfirmed) {
                performBlockDelete(blockID);
            }
        });
    }

    function performBlockDelete(blockID) {
        $.ajax({
            url: `/api/accommodation/blocks/${blockID}`,
            method: 'DELETE',
            success: function(response) {
                loadBlocks();
                loadRooms();
                loadBeds();
                showAlert('success', 'Deleted', 'Block deleted successfully!');
            },
            error: function(xhr) {
                console.error('Error deleting block:', xhr);
                showAlert('error', 'Failed', 'Error deleting block. Please try again.');
            }
        });
    }

    function removeStudentsFromBlock(blockID) {
        if (typeof Swal === 'undefined') {
            return performRemoveStudentsFromBlock(blockID);
        }
        Swal.fire({
            icon: 'warning',
            title: 'Remove All Students?',
            text: 'This will remove all students from beds in this block.',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove'
        }).then(result => {
            if (result.isConfirmed) {
                performRemoveStudentsFromBlock(blockID);
            }
        });
    }

    function performRemoveStudentsFromBlock(blockID) {
        $.ajax({
            url: `/api/accommodation/blocks/${blockID}/remove-students`,
            method: 'POST',
            success: function(response) {
                showAlert('success', 'Success', response.message || 'Students removed from block.');
                loadBeds();
                loadBedAssignments();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error removing students.';
                showAlert('error', 'Failed', msg);
            }
        });
    }

    // Load Blocks
    function loadBlocks() {
        $.ajax({
            url: '/api/accommodation/blocks',
            method: 'GET',
            success: function(response) {
                blocks = response.data || [];
                renderBlocksTable();
                populateRoomBlocks();
                populateBedRegisterOptions();
                renderBedMap();
            },
            error: function(xhr) {
                console.error('Error loading blocks:', xhr);
                blocks = [];
                renderBlocksTable();
                populateRoomBlocks();
                populateBedRegisterOptions();
                renderBedMap();
            }
        });
    }

    // Render Blocks Table
    function renderBlocksTable() {
        const tbody = $('#blocksTableBody');
        tbody.empty();

        if (blocks.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="empty-state">
                            <i class="fa fa-building"></i>
                            <p>No blocks added yet. Use Register Block to add a new block.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        blocks.forEach(block => {
            const roomCount = rooms.filter(r => r.blockID == block.blockID).length;
            const bedCount = beds.filter(bed => bed.blockID == block.blockID).length;
            const blockTypeText = block.blockType === 'with_rooms' ? 'With Rooms' : 'Without Rooms (Hall)';
            const statusBadge = block.status === 'Active' 
                ? '<span class="badge badge-success">Active</span>' 
                : '<span class="badge badge-danger">Inactive</span>';
            const sexLabel = block.blockSex || block.sex || 'N/A';

            let itemsHtml = '';
            if (block.items && block.items.length > 0) {
                itemsHtml = '<div class="item-list">';
                block.items.forEach(item => {
                    const itemNames = {
                        table: 'Tables',
                        chair: 'Chairs',
                        cabinet: 'Cabinets',
                        other: 'Other Items'
                    };
                    itemsHtml += `<span class="item-badge"><i class="fa fa-check"></i> ${itemNames[item.itemType]}: ${item.quantity}</span>`;
                });
                itemsHtml += '</div>';
            }

            tbody.append(`
                <tr>
                    <td><strong>${block.blockName}</strong>${itemsHtml}</td>
                    <td>${block.location}</td>
                    <td>${roomCount}</td>
                    <td>${bedCount}</td>
                    <td>${blockTypeText}</td>
                    <td>${sexLabel}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick="editBlock(${block.blockID})" title="Edit">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-view" onclick="removeStudentsFromBlock(${block.blockID})" title="Remove Students">
                                <i class="fa fa-user-times"></i>
                            </button>
                            <button class="btn btn-sm btn-delete" onclick="deleteBlock(${block.blockID})" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    // Show Add Room Modal
    function showAddRoomModal() {
        resetRoomForm();
        $('.accommodation-menu .list-group-item').removeClass('active');
        $('.accommodation-menu .list-group-item[data-target="#section-register-room"]').addClass('active');
        showAccommodationSection('#section-register-room');
    }

    // Show Edit Room Modal
    function editRoom(roomID) {
        const room = rooms.find(r => r.roomID == roomID);
        if (!room) return;
        $('#roomForm').data('edit-room-id', room.roomID);
        populateRoomBlocks();
        $('#roomBlockID').val(room.blockID);
        $('#roomRows').empty();
        addRoomRow({
            roomName: room.roomName,
            roomNumber: room.roomNumber,
            capacity: room.capacity
        });
        const roomRow = $('#roomRows .room-row').last();
        populateRoomRowItems(roomRow, room.items || []);
        checkBlockType();
        $('.accommodation-menu .list-group-item').removeClass('active');
        $('.accommodation-menu .list-group-item[data-target="#section-register-room"]').addClass('active');
        showAccommodationSection('#section-register-room');
    }

    // Save Room
    function saveRoom() {
        const form = $('#roomForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const blockID = $('#roomBlockID').val();
        const block = blocks.find(b => b.blockID == blockID);

        if (block && block.blockType === 'without_rooms') {
            showAlert('warning', 'Invalid Block', 'You cannot add a room to a block that has no rooms!');
            return;
        }
        const rows = $('#roomRows .room-row');
        const roomsToSave = [];
        rows.each(function() {
            const name = $(this).find('.room-name').val().trim();
            const number = $(this).find('.room-number').val().trim();
            const capacity = parseInt($(this).find('.room-capacity').val()) || 1;
            if (!name || !number) {
                return;
            }
        const items = [];
            $(this).find('.room-item-check:checked').each(function() {
                const itemType = $(this).data('type');
                const qtyInput = $(this).closest('.room-item').find('.room-item-qty-input');
                const qtyValue = parseInt(qtyInput.val()) || 1;
                items.push({
                    itemType: itemType,
                    quantity: qtyValue
                });
            });
            roomsToSave.push({
                roomName: name,
                roomNumber: number,
                capacity: capacity,
                items: items
            });
        });

        if (roomsToSave.length === 0) {
            showAlert('warning', 'Missing Data', 'Please fill in at least one room name and number.');
            return;
        }

        const editRoomId = $('#roomForm').data('edit-room-id');
        const saveBtn = $('#saveRoomBtn');
        saveBtn.prop('disabled', true);
        setAccommodationLoading(true);

        if (editRoomId) {
        const roomData = {
                roomID: editRoomId,
            blockID: parseInt(blockID),
                roomName: roomsToSave[0].roomName,
                roomNumber: roomsToSave[0].roomNumber,
                capacity: roomsToSave[0].capacity,
                status: 'Active',
                description: '',
                items: roomsToSave[0].items || []
            };
        $.ajax({
            url: '/api/accommodation/rooms',
                method: 'PUT',
                data: roomData
            }).done(function() {
                loadRooms();
                loadBlocks();
                resetRoomForm();
                showAlert('success', 'Success', 'Room updated successfully!');
            }).fail(function(xhr) {
                console.error('Error saving room:', xhr);
                showAlert('error', 'Failed', 'Error saving room. Please try again.');
            }).always(function() {
                saveBtn.prop('disabled', false);
                setAccommodationLoading(false);
            });
            return;
        }

        const requests = roomsToSave.map(roomItem => {
            return $.ajax({
                url: '/api/accommodation/rooms',
                method: 'POST',
                data: {
                    blockID: parseInt(blockID),
                    roomName: roomItem.roomName,
                    roomNumber: roomItem.roomNumber,
                    capacity: roomItem.capacity,
                    status: 'Active',
                    description: '',
                    items: roomItem.items || []
                }
            });
        });

        $.when.apply($, requests)
            .done(function() {
                loadRooms();
                loadBlocks();
                resetRoomForm();
                showAlert('success', 'Success', 'Rooms saved successfully!');
            })
            .fail(function(xhr) {
                console.error('Error saving room:', xhr);
                showAlert('error', 'Failed', 'Error saving rooms. Please try again.');
            })
            .always(function() {
                saveBtn.prop('disabled', false);
                setAccommodationLoading(false);
            });
    }

    // Delete Room
    function deleteRoom(roomID) {
        if (typeof Swal === 'undefined') {
        if (!confirm('Are you sure you want to delete this room?')) {
            return;
            }
            return performRoomDelete(roomID);
        }
        Swal.fire({
            icon: 'warning',
            title: 'Delete Room?',
            text: 'Are you sure you want to delete this room?',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete'
        }).then(result => {
            if (result.isConfirmed) {
                performRoomDelete(roomID);
            }
        });
    }

    function performRoomDelete(roomID) {
        $.ajax({
            url: `/api/accommodation/rooms/${roomID}`,
            method: 'DELETE',
            success: function(response) {
                loadRooms();
                loadBlocks(); // Refresh to update room count
                showAlert('success', 'Deleted', 'Room deleted successfully!');
            },
            error: function(xhr) {
                console.error('Error deleting room:', xhr);
                showAlert('error', 'Failed', 'Error deleting room. Please try again.');
            }
        });
    }

    // Load Rooms
    function loadRooms() {
        $.ajax({
            url: '/api/accommodation/rooms',
            method: 'GET',
            success: function(response) {
                rooms = response.data || [];
                renderRoomsTable();
                populateBedRegisterOptions();
                renderBedMap();
            },
            error: function(xhr) {
                console.error('Error loading rooms:', xhr);
                rooms = [];
                renderRoomsTable();
                populateBedRegisterOptions();
                renderBedMap();
            }
        });
    }

    // Render Rooms Table
    function renderRoomsTable() {
        const tbody = $('#roomsTableBody');
        tbody.empty();

        if (rooms.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="empty-state">
                            <i class="fa fa-door-open"></i>
                            <p>No rooms added yet. Click "Add Room" to add a new room.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        rooms.forEach(room => {
            const block = blocks.find(b => b.blockID == room.blockID);
            const blockName = block ? block.blockName : 'N/A';
            const statusBadge = room.status === 'Active' 
                ? '<span class="badge badge-success">Active</span>' 
                : '<span class="badge badge-danger">Inactive</span>';

            let itemsHtml = '';
            if (room.items && room.items.length > 0) {
                itemsHtml = '<div class="item-list">';
                room.items.forEach(item => {
                    const itemNames = {
                        table: 'Tables',
                        chair: 'Chairs',
                        cabinet: 'Cabinets',
                        wardrobe: 'Wardrobes',
                        other: 'Other Items'
                    };
                    itemsHtml += `<span class="item-badge"><i class="fa fa-check"></i> ${itemNames[item.itemType]}: ${item.quantity}</span>`;
                });
                itemsHtml += '</div>';
            } else {
                itemsHtml = '<span class="text-muted">No items</span>';
            }

            tbody.append(`
                <tr>
                    <td><strong>${room.roomName}</strong></td>
                    <td>${blockName}</td>
                    <td>${room.roomNumber}</td>
                    <td>${room.capacity} people</td>
                    <td>${itemsHtml}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick="editRoom(${room.roomID})" title="Edit">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-delete" onclick="deleteRoom(${room.roomID})" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    // Show Add Bed Modal
    function showAddBedModal() {
        if (blocks.length === 0) {
            showAlert('warning', 'No Block', 'Please add a block first before adding a bed!');
            return;
        }

        $('#bedID').val('');
        $('#bedForm')[0].reset();
        $('#bedRoomID').empty().append('<option value="">Select Room (Leave empty if bed is in block/hall)</option>');
        toggleMattressInfo();

        // Populate block dropdown
        const blockSelect = $('#bedBlockID');
        blockSelect.empty();
        blockSelect.append('<option value="">Select Block</option>');
        blocks.filter(b => b.status === 'Active').forEach(block => {
            blockSelect.append(`<option value="${block.blockID}">${block.blockName} (${block.blockType === 'with_rooms' ? 'With Rooms' : 'Hall'})</option>`);
        });

        $('#bedModalTitle').html('<i class="fa fa-bed"></i> Add New Bed');
        $('#saveBedBtnText').text('Save');
        $('#bedModal').modal('show');
    }

    // Show Edit Bed Modal
    function editBed(bedID) {
        const bed = beds.find(b => b.bedID == bedID);
        if (!bed) return;

        $('#bedID').val(bed.bedID);
        $('#bedNumber').val(bed.bedNumber || '');
        $('#bedHasMattress').val(bed.hasMattress);
        $('#bedStatus').val(bed.status);
        $('#bedDescription').val(bed.description || '');

        // Populate block dropdown
        const blockSelect = $('#bedBlockID');
        blockSelect.empty();
        blockSelect.append('<option value="">Select Block</option>');
        blocks.filter(b => b.status === 'Active').forEach(block => {
            blockSelect.append(`<option value="${block.blockID}" ${block.blockID == bed.blockID ? 'selected' : ''}>${block.blockName} (${block.blockType === 'with_rooms' ? 'With Rooms' : 'Hall'})</option>`);
        });

        loadRoomsForBed();
        if (bed.roomID) {
            $('#bedRoomID').val(bed.roomID);
        }

        toggleMattressInfo();

        $('#bedModalTitle').html('<i class="fa fa-edit"></i> Edit Bed');
        $('#saveBedBtnText').text('Update');
        $('#bedModal').modal('show');
    }

    // Save Bed
    function saveBed() {
        const form = $('#bedForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const blockID = $('#bedBlockID').val();
        const roomID = $('#bedRoomID').val() || null;
        const block = blocks.find(b => b.blockID == blockID);

        // Validate: if block has rooms, room must be selected
        if (block && block.blockType === 'with_rooms' && !roomID) {
            showAlert('warning', 'Room Required', 'This block has rooms. Please select a room for this bed.');
            return;
        }

        // Validate: if block has no rooms, room should be empty
        if (block && block.blockType === 'without_rooms' && roomID) {
            showAlert('warning', 'Room Not Needed', 'This block has no rooms. Please leave the room field empty.');
            return;
        }

        const bedData = {
            bedID: $('#bedID').val() || null,
            blockID: parseInt(blockID),
            roomID: roomID ? parseInt(roomID) : null,
            bedNumber: $('#bedNumber').val() || null,
            hasMattress: $('#bedHasMattress').val(),
            status: $('#bedStatus').val(),
            description: $('#bedDescription').val()
        };

        // AJAX call to save bed
        $.ajax({
            url: '/api/accommodation/beds',
            method: bedData.bedID ? 'PUT' : 'POST',
            data: bedData,
            success: function(response) {
                loadBeds();
                $('#bedModal').modal('hide');
                showAlert('success', 'Success', 'Bed saved successfully!');
            },
            error: function(xhr) {
                console.error('Error saving bed:', xhr);
                showAlert('error', 'Failed', 'Error saving bed. Please try again.');
            }
        });
    }

    function saveBeds() {
        const roomID = $('#bedRoomSelect').val();
        const hallBlockID = $('#bedHallBlockID').val();

        if (!roomID && !hallBlockID) {
            showAlert('warning', 'Missing Selection', 'Select a room or a hall block first.');
            return;
        }

        let blockID = hallBlockID;
        let resolvedRoomID = roomID || null;
        if (roomID) {
            const room = rooms.find(r => r.roomID == roomID);
            blockID = room ? room.blockID : null;
        }

        if (!blockID) {
            showAlert('error', 'Invalid Selection', 'Unable to resolve block for the selected room.');
            return;
        }

        const rows = $('#bedRows .bed-row');
        const bedsToSave = [];
        rows.each(function() {
            const bedNumber = $(this).find('.bed-number').val().trim();
            if (!bedNumber) {
                return;
            }
            bedsToSave.push({
                bedNumber: bedNumber
            });
        });

        if (bedsToSave.length === 0) {
            showAlert('warning', 'Missing Data', 'Please enter at least one bed number.');
            return;
        }

        const saveBtn = $('#saveBedsBtn');
        saveBtn.prop('disabled', true);
        setAccommodationLoading(true);

        const requests = bedsToSave.map(item => {
            return $.ajax({
                url: '/api/accommodation/beds',
                method: 'POST',
                data: {
                    blockID: parseInt(blockID),
                    roomID: resolvedRoomID ? parseInt(resolvedRoomID) : null,
                    bedNumber: item.bedNumber,
                    hasMattress: 'Yes',
                    status: 'Available'
                }
            });
        });

        $.when.apply($, requests)
            .done(function() {
                loadBeds();
                resetBedRegisterForm();
                showAlert('success', 'Success', 'Beds saved successfully!');
            })
            .fail(function(xhr) {
                console.error('Error saving beds:', xhr);
                showAlert('error', 'Failed', 'Error saving beds. Please try again.');
            })
            .always(function() {
                saveBtn.prop('disabled', false);
                setAccommodationLoading(false);
        });
    }

    // Delete Bed
    function deleteBed(bedID) {
        if (typeof Swal === 'undefined') {
        if (!confirm('Are you sure you want to delete this bed?')) {
            return;
            }
            return performBedDelete(bedID);
        }
        Swal.fire({
            icon: 'warning',
            title: 'Delete Bed?',
            text: 'Are you sure you want to delete this bed?',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete'
        }).then(result => {
            if (result.isConfirmed) {
                performBedDelete(bedID);
            }
        });
    }

    function performBedDelete(bedID) {
        $.ajax({
            url: `/api/accommodation/beds/${bedID}`,
            method: 'DELETE',
            success: function(response) {
                loadBeds();
                showAlert('success', 'Deleted', 'Bed deleted successfully!');
            },
            error: function(xhr) {
                console.error('Error deleting bed:', xhr);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error deleting bed. Please try again.';
                showAlert('error', 'Failed', msg);
            }
        });
    }

    // Load Beds
    function loadBeds() {
        $.ajax({
            url: '/api/accommodation/beds',
            method: 'GET',
            success: function(response) {
                beds = response.data || [];
                renderBedsTable();
                renderBlocksTable();
                renderBedMap();
            },
            error: function(xhr) {
                console.error('Error loading beds:', xhr);
                beds = [];
                renderBedsTable();
                renderBlocksTable();
                renderBedMap();
            }
        });
    }

    function loadBedAssignments() {
        $.ajax({
            url: '/api/accommodation/bed-assignments',
            method: 'GET',
            success: function(response) {
                bedAssignments = response.data || [];
                renderBedMap();
            },
            error: function(xhr) {
                console.error('Error loading bed assignments:', xhr);
                bedAssignments = [];
                renderBedMap();
            }
        });
    }

    // Render Beds Table
    function renderBedsTable() {
        const tbody = $('#bedsTableBody');
        tbody.empty();

        if (beds.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="empty-state">
                            <i class="fa fa-bed"></i>
                            <p>No beds added yet. Click "Add Bed" to add a new bed.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        beds.forEach(bed => {
            const block = blocks.find(b => b.blockID == bed.blockID);
            const blockName = block ? block.blockName : 'N/A';
            
            let roomName = 'N/A';
            if (bed.roomID) {
                const room = rooms.find(r => r.roomID == bed.roomID);
                roomName = room ? room.roomName : 'N/A';
            } else {
                roomName = '<span class="text-muted">Direct in Block (Hall)</span>';
            }

            const mattressBadge = bed.hasMattress === 'Yes' 
                ? '<span class="mattress-badge mattress-yes"><i class="fa fa-check-circle"></i> Has Mattress</span>'
                : '<span class="mattress-badge mattress-no"><i class="fa fa-exclamation-triangle"></i> No Mattress - Student Must Bring</span>';

            const statusBadges = {
                'Available': '<span class="badge badge-success">Available</span>',
                'Occupied': '<span class="badge badge-warning">Occupied</span>',
                'Maintenance': '<span class="badge badge-info">Maintenance</span>',
                'Inactive': '<span class="badge badge-danger">Inactive</span>'
            };

            tbody.append(`
                <tr>
                    <td><strong>${bed.bedNumber || 'N/A'}</strong></td>
                    <td>${blockName}</td>
                    <td>${roomName}</td>
                    <td>${mattressBadge}</td>
                    <td>${statusBadges[bed.status] || bed.status}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick="editBed(${bed.bedID})" title="Edit">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-delete" onclick="deleteBed(${bed.bedID})" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function renderBedMap() {
        const container = $('#bedMapContainer');
        if (!container.length) {
            return;
        }
        container.empty();

        if (blocks.length === 0 || beds.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="fa fa-bed"></i>
                    <p>No beds found. Register beds first.</p>
                </div>
            `);
            return;
        }

        const assignmentMap = {};
        bedAssignments.forEach(assign => {
            assignmentMap[assign.bedID] = assign;
        });

        blocks.forEach(block => {
            const blockBeds = beds.filter(b => b.blockID == block.blockID);
            if (blockBeds.length === 0) {
                return;
            }
            let blockHtml = `
                <div class="bed-map-block">
                    <div class="bed-map-title">${block.blockName} (${block.blockType === 'with_rooms' ? 'With Rooms' : 'Hall'})</div>
            `;

            if (block.blockType === 'with_rooms') {
                const blockRooms = rooms.filter(r => r.blockID == block.blockID);
                blockRooms.forEach(room => {
                    const roomBeds = blockBeds.filter(b => b.roomID == room.roomID);
                    if (roomBeds.length === 0) {
                        return;
                    }
                    blockHtml += `
                        <div class="bed-map-room">
                            <div class="bed-map-room-title">${room.roomName} (${room.roomNumber})</div>
                            <div>
                    `;
                    roomBeds.forEach(bed => {
                        const assignment = assignmentMap[bed.bedID];
                        const statusClass = assignment ? 'occupied' : (bed.status || '').toLowerCase();
                        const pillLabel = bed.bedNumber || `Bed ${bed.bedID}`;
                        blockHtml += `<span class="bed-pill ${statusClass}" data-bed-id="${bed.bedID}">${pillLabel}</span>`;
                    });
                    blockHtml += `</div></div>`;
                });
            } else {
                blockHtml += `<div>`;
                blockBeds.forEach(bed => {
                    const assignment = assignmentMap[bed.bedID];
                    const statusClass = assignment ? 'occupied' : (bed.status || '').toLowerCase();
                    const pillLabel = bed.bedNumber || `Bed ${bed.bedID}`;
                    blockHtml += `<span class="bed-pill ${statusClass}" data-bed-id="${bed.bedID}">${pillLabel}</span>`;
                });
                blockHtml += `</div>`;
            }

            blockHtml += `</div>`;
            container.append(blockHtml);
        });
    }

    function openBedDetail(bedId) {
        const bed = beds.find(b => b.bedID == bedId);
        if (!bed) {
            showAlert('error', 'Not found', 'Bed not found.');
            return;
        }
        const block = blocks.find(b => b.blockID == bed.blockID);
        const room = bed.roomID ? rooms.find(r => r.roomID == bed.roomID) : null;
        const assignment = bedAssignments.find(a => a.bedID == bedId);

        $('#bedDetailModal').data('bed-id', bedId);

        const bedLabel = bed.bedNumber || `Bed ${bed.bedID}`;
        const blockName = block ? block.blockName : 'N/A';
        const roomName = room ? `${room.roomName} (${room.roomNumber})` : 'Direct in Block (Hall)';
        const statusLabel = bed.status || 'Available';
        const blockSex = block && (block.blockSex || block.sex) ? (block.blockSex || block.sex) : 'N/A';

        $('#bedDetailInfo').html(`
            <div><strong>Bed:</strong> ${bedLabel}</div>
            <div><strong>Block:</strong> ${blockName}</div>
            <div><strong>Room:</strong> ${roomName}</div>
            <div><strong>Block Sex:</strong> ${blockSex}</div>
            <div><strong>Status:</strong> ${statusLabel}</div>
        `);

        $('#bedAssignWarning').addClass('d-none');
        $('#bedAssignSection').addClass('d-none');
        $('#bedOccupiedSection').addClass('d-none');
        $('#bedMoveSection').addClass('d-none');
        $('#bedRemoveStudentBtn').addClass('d-none');

        if (assignment) {
            $('#bedOccupiedSection').removeClass('d-none');
            $('#bedStudentInfo').html(`
                <div><strong>Name:</strong> ${assignment.studentName}</div>
                <div><strong>Class:</strong> ${assignment.className}</div>
                <div><strong>Gender:</strong> ${assignment.studentGender || 'N/A'}</div>
                <div><strong>Parent Phone:</strong> ${assignment.parentPhone || 'N/A'}</div>
            `);
            $('#bedRemoveStudentBtn').removeClass('d-none');
            $('#bedMoveSection').removeClass('d-none');
            populateMoveTargets(bedId, assignment.studentGender || '');
            $('#bedDeleteBtn').prop('disabled', true);
        } else {
            $('#bedDeleteBtn').prop('disabled', false);
            if (statusLabel !== 'Available') {
                $('#bedAssignWarning')
                    .removeClass('d-none')
                    .text('This bed is not available for assignment.');
            } else {
                $('#bedAssignSection').removeClass('d-none');
                $('#bedStudentSearch').val('');
                $('#bedStudentResults').empty();
            }
        }

        $('#bedDetailModal').modal('show');
    }

    function searchStudentsForBed(query) {
        const results = $('#bedStudentResults');
        results.empty();
        if (!query || query.length < 2) {
            return;
        }
        $.ajax({
            url: `/api/accommodation/students?search=${encodeURIComponent(query)}`,
            method: 'GET',
            success: function(response) {
                const data = response.data || [];
                if (data.length === 0) {
                    results.html('<div class="text-muted">No students found.</div>');
                    return;
                }
                let html = '<div class="list-group">';
                data.forEach(student => {
                    html += `
                        <button type="button" class="list-group-item list-group-item-action bed-assign-item" data-student-id="${student.studentID}">
                            <strong>${student.studentName}</strong> (${student.admissionNumber || 'N/A'}) - ${student.className}
                        </button>
                    `;
                });
                html += '</div>';
                results.html(html);
                $('.bed-assign-item').on('click', function() {
                    const studentId = $(this).data('student-id');
                    assignStudentToBed(studentId);
                });
            },
            error: function(xhr) {
                console.error('Error searching students:', xhr);
                results.html('<div class="text-danger">Error searching students.</div>');
            }
        });
    }

    function assignStudentToBed(studentId) {
        const bedId = $('#bedDetailModal').data('bed-id');
        if (!bedId) {
            showAlert('error', 'Not found', 'Bed not selected.');
            return;
        }
        $.ajax({
            url: '/api/accommodation/beds/assign',
            method: 'POST',
            data: {
                bedID: bedId,
                studentID: studentId
            },
            success: function(response) {
                showAlert('success', 'Assigned', response.message || 'Student assigned to bed.');
                $('#bedDetailModal').modal('hide');
                loadBeds();
                loadBedAssignments();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error assigning student.';
                showAlert('error', 'Failed', msg);
            }
        });
    }

    function populateMoveTargets(currentBedId, studentGender) {
        const targetSelect = $('#bedMoveTarget');
        targetSelect.empty().append('<option value="">Select Bed</option>');
        const gender = (studentGender || '').toLowerCase();

        beds.forEach(bed => {
            if (bed.bedID == currentBedId) {
                return;
            }
            const block = blocks.find(b => b.blockID == bed.blockID);
            if (block && (block.blockSex || block.sex)) {
                const blockSex = (block.blockSex || block.sex || '').toLowerCase();
                if (blockSex !== 'mixed' && gender && blockSex !== gender) {
                    return;
                }
                if (blockSex !== 'mixed' && !gender) {
                    return;
                }
            }
            const room = bed.roomID ? rooms.find(r => r.roomID == bed.roomID) : null;
            const bedLabel = bed.bedNumber || `Bed ${bed.bedID}`;
            const roomLabel = room ? `${room.roomName} (${room.roomNumber})` : 'Hall';
            const blockName = block ? block.blockName : 'Unknown Block';
            const assignment = bedAssignments.find(a => a.bedID == bed.bedID);
            const statusLabel = assignment ? 'Occupied' : (bed.status || 'Available');
            targetSelect.append(`<option value="${bed.bedID}">${bedLabel} - ${roomLabel} - ${blockName} [${statusLabel}]</option>`);
        });
    }

    function moveStudentToBed(fromBedId, toBedId) {
        $.ajax({
            url: '/api/accommodation/beds/move',
            method: 'POST',
            data: {
                fromBedID: fromBedId,
                toBedID: toBedId
            },
            success: function(response) {
                showAlert('success', 'Success', response.message || 'Student moved successfully.');
                $('#bedDetailModal').modal('hide');
                loadBeds();
                loadBedAssignments();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error moving student.';
                showAlert('error', 'Failed', msg);
            }
        });
    }

    function removeStudentFromBed(bedId) {
        if (typeof Swal === 'undefined') {
            return performRemoveStudent(bedId);
        }
        Swal.fire({
            icon: 'warning',
            title: 'Remove Student?',
            text: 'Are you sure you want to remove this student from the bed?',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove'
        }).then(result => {
            if (result.isConfirmed) {
                performRemoveStudent(bedId);
            }
        });
    }

    function performRemoveStudent(bedId) {
        $.ajax({
            url: '/api/accommodation/beds/release',
            method: 'POST',
            data: { bedID: bedId },
            success: function(response) {
                showAlert('success', 'Removed', response.message || 'Student removed from bed.');
                $('#bedDetailModal').modal('hide');
                loadBeds();
                loadBedAssignments();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error removing student.';
                showAlert('error', 'Failed', msg);
            }
        });
    }
</script>
