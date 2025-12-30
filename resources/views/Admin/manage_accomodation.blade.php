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

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .accommodation-container {
        padding: 30px;
    }

    .accommodation-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .accommodation-header h2 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .accommodation-header h2 i {
        font-size: 2rem;
    }

    .accommodation-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .accommodation-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .card-title i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        outline: none;
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

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        border-bottom: none;
    }

    .modal-header .close {
        color: white;
        opacity: 0.9;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
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
</style>

<div class="container-fluid accommodation-container">
    <div class="accommodation-header">
        <h2>
            <i class="fa fa-bed"></i>
            Accommodation Management
        </h2>
    </div>

    <!-- Assign Blocks Widget -->
    <div class="accommodation-card">
        <div class="card-title">
            <span>
                <i class="fa fa-building"></i>
                Accommodation Blocks
            </span>
            <button class="btn btn-primary-custom" onclick="showAddBlockModal()">
                <i class="fa fa-plus"></i> Add Block
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Block Name</th>
                        <th>Location</th>
                        <th>Number of Rooms</th>
                        <th>Block Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="blocksTableBody">
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-building"></i>
                                <p>No blocks added yet. Click "Add Block" to add a new block.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Assign Rooms Widget -->
    <div class="accommodation-card">
        <div class="card-title">
            <span>
                <i class="fa fa-door-open"></i>
                Rooms in Blocks
            </span>
            <button class="btn btn-primary-custom" onclick="showAddRoomModal()">
                <i class="fa fa-plus"></i> Add Room
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Block</th>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Items Inside</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="roomsTableBody">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-door-open"></i>
                                <p>No rooms added yet. Click "Add Room" to add a new room.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Beds Management Widget -->
    <div class="accommodation-card">
        <div class="card-title">
            <span>
                <i class="fa fa-bed"></i>
                Beds Management
            </span>
            <button class="btn btn-primary-custom" onclick="showAddBedModal()">
                <i class="fa fa-plus"></i> Add Bed
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Bed Number</th>
                        <th>Block</th>
                        <th>Room</th>
                        <th>Mattress</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bedsTableBody">
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-bed"></i>
                                <p>No beds added yet. Click "Add Bed" to add a new bed.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Block Modal -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockModalTitle">
                    <i class="fa fa-building"></i> Add New Block
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="blockForm">
                    <input type="hidden" id="blockID">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Block Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="blockName" required placeholder="e.g., Block A, Block B">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="blockLocation" required placeholder="e.g., East Wing">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Block Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="blockType" required onchange="toggleBlockTypeOptions()">
                                <option value="">Select Type</option>
                                <option value="with_rooms">With Rooms (Block has rooms)</option>
                                <option value="without_rooms">Without Rooms (Block is beds only, like a hall)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="blockStatus" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group" id="blockItemsSection" style="display: none;">
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
                            <small class="text-muted">Note: Beds for blocks without rooms should be added separately in the Beds Management section.</small>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="blockDescription" rows="3" placeholder="Additional description about the block..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBlockBtn" onclick="saveBlock()">
                    <i class="fa fa-save"></i> <span id="saveBlockBtnText">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalTitle">
                    <i class="fa fa-door-open"></i> Add New Room
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="roomForm">
                    <input type="hidden" id="roomID">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Select Block <span class="text-danger">*</span></label>
                            <select class="form-control" id="roomBlockID" required onchange="checkBlockType()">
                                <option value="">Select Block</option>
                            </select>
                            <small class="text-muted" id="blockTypeHint" style="display: none;"></small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Room Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roomName" required placeholder="e.g., Room 101, Room 102">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roomNumber" required placeholder="e.g., 101, 102">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Capacity (Number of people) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="roomCapacity" required min="1" placeholder="e.g., 4">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Items Inside the Room</label>
                            <div class="checkbox-group" id="roomItemsList">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="roomItem_table" value="table" onchange="toggleItemQuantity('roomItem_table', 'roomItemQty_table')">
                                    <label for="roomItem_table">Tables</label>
                                    <div class="item-quantity" id="roomItemQty_table" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="roomTableQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="roomItem_chair" value="chair" onchange="toggleItemQuantity('roomItem_chair', 'roomItemQty_chair')">
                                    <label for="roomItem_chair">Chairs</label>
                                    <div class="item-quantity" id="roomItemQty_chair" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="roomChairQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="roomItem_cabinet" value="cabinet" onchange="toggleItemQuantity('roomItem_cabinet', 'roomItemQty_cabinet')">
                                    <label for="roomItem_cabinet">Cabinets</label>
                                    <div class="item-quantity" id="roomItemQty_cabinet" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="roomCabinetQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="roomItem_wardrobe" value="wardrobe" onchange="toggleItemQuantity('roomItem_wardrobe', 'roomItemQty_wardrobe')">
                                    <label for="roomItem_wardrobe">Wardrobes</label>
                                    <div class="item-quantity" id="roomItemQty_wardrobe" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="roomWardrobeQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="roomItem_other" value="other" onchange="toggleItemQuantity('roomItem_other', 'roomItemQty_other')">
                                    <label for="roomItem_other">Other Items</label>
                                    <div class="item-quantity" id="roomItemQty_other" style="display: none;">
                                        <label>Quantity:</label>
                                        <input type="number" id="roomOtherQuantity" min="1" value="1" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Select items available in the room and specify their quantities. Beds should be added separately in the Beds Management section.</small>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="roomStatus" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="roomDescription" rows="3" placeholder="Additional description about the room..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveRoomBtn" onclick="saveRoom()">
                    <i class="fa fa-save"></i> <span id="saveRoomBtnText">Save</span>
                </button>
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

@include('includes.footer')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

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

    // Initialize on page load
    $(document).ready(function() {
        loadBlocks();
        loadRooms();
        loadBeds();
        
        // Show/hide mattress info based on selection
        $('#bedHasMattress').on('change', function() {
            toggleMattressInfo();
        });
    });

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
        
        if (block && block.blockType === 'without_rooms') {
            hint.text('⚠️ This block has no rooms. The entire block is beds only.');
            hint.css('color', '#ffc107');
            hint.show();
            $('#roomBlockID').val('');
            alert('This block does not have rooms. Please select a different block or add beds directly to this block.');
        } else {
            hint.hide();
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
        $('#blockID').val('');
        $('#blockForm')[0].reset();
        $('#blockItemsSection').hide();
        $('#blockItemsList input[type="checkbox"]').prop('checked', false);
        $('#blockItemsList .item-quantity').hide();
        $('#blockModalTitle').html('<i class="fa fa-building"></i> Add New Block');
        $('#saveBlockBtnText').text('Save');
        $('#blockModal').modal('show');
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

        $('#blockModalTitle').html('<i class="fa fa-edit"></i> Edit Block');
        $('#saveBlockBtnText').text('Update');
        $('#blockModal').modal('show');
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
            description: $('#blockDescription').val(),
            items: items
        };

        // AJAX call to save block
        $.ajax({
            url: '/api/accommodation/blocks',
            method: blockData.blockID ? 'PUT' : 'POST',
            data: blockData,
            success: function(response) {
                loadBlocks();
                $('#blockModal').modal('hide');
                alert('Block saved successfully!');
            },
            error: function(xhr) {
                console.error('Error saving block:', xhr);
                alert('Error saving block. Please try again.');
            }
        });
    }

    // Delete Block
    function deleteBlock(blockID) {
        if (!confirm('Are you sure you want to delete this block? This will also delete all rooms and beds in this block.')) {
            return;
        }

        $.ajax({
            url: `/api/accommodation/blocks/${blockID}`,
            method: 'DELETE',
            success: function(response) {
                loadBlocks();
                loadRooms();
                loadBeds();
                alert('Block deleted successfully!');
            },
            error: function(xhr) {
                console.error('Error deleting block:', xhr);
                alert('Error deleting block. Please try again.');
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
            },
            error: function(xhr) {
                console.error('Error loading blocks:', xhr);
                blocks = [];
                renderBlocksTable();
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
                    <td colspan="6" class="text-center">
                        <div class="empty-state">
                            <i class="fa fa-building"></i>
                            <p>No blocks added yet. Click "Add Block" to add a new block.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        blocks.forEach(block => {
            const roomCount = rooms.filter(r => r.blockID == block.blockID).length;
            const blockTypeText = block.blockType === 'with_rooms' ? 'With Rooms' : 'Without Rooms (Hall)';
            const statusBadge = block.status === 'Active' 
                ? '<span class="badge badge-success">Active</span>' 
                : '<span class="badge badge-danger">Inactive</span>';

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
                    <td>${blockTypeText}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick="editBlock(${block.blockID})" title="Edit">
                                <i class="fa fa-edit"></i>
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
        if (blocks.length === 0) {
            alert('Please add a block first before adding a room!');
            return;
        }

        $('#roomID').val('');
        $('#roomForm')[0].reset();
        $('#roomItemsList input[type="checkbox"]').prop('checked', false);
        $('#roomItemsList .item-quantity').hide();
        $('#blockTypeHint').hide();

        // Populate block dropdown
        const blockSelect = $('#roomBlockID');
        blockSelect.empty();
        blockSelect.append('<option value="">Select Block</option>');
        blocks.filter(b => b.status === 'Active' && b.blockType === 'with_rooms').forEach(block => {
            blockSelect.append(`<option value="${block.blockID}">${block.blockName}</option>`);
        });

        $('#roomModalTitle').html('<i class="fa fa-door-open"></i> Add New Room');
        $('#saveRoomBtnText').text('Save');
        $('#roomModal').modal('show');
    }

    // Show Edit Room Modal
    function editRoom(roomID) {
        const room = rooms.find(r => r.roomID == roomID);
        if (!room) return;

        $('#roomID').val(room.roomID);
        $('#roomName').val(room.roomName);
        $('#roomNumber').val(room.roomNumber);
        $('#roomCapacity').val(room.capacity);
        $('#roomStatus').val(room.status);
        $('#roomDescription').val(room.description || '');

        // Populate block dropdown
        const blockSelect = $('#roomBlockID');
        blockSelect.empty();
        blockSelect.append('<option value="">Select Block</option>');
        blocks.filter(b => b.status === 'Active' && b.blockType === 'with_rooms').forEach(block => {
            blockSelect.append(`<option value="${block.blockID}" ${block.blockID == room.blockID ? 'selected' : ''}>${block.blockName}</option>`);
        });

        checkBlockType();

        // Load room items if any
        if (room.items && room.items.length > 0) {
            room.items.forEach(item => {
                const checkboxId = `roomItem_${item.itemType}`;
                const quantityId = `roomItemQty_${item.itemType}`;
                $(`#${checkboxId}`).prop('checked', true);
                $(`#${quantityId}`).show();
                const quantityInput = $(`#room${item.itemType.charAt(0).toUpperCase() + item.itemType.slice(1)}Quantity`);
                quantityInput.val(item.quantity);
            });
        }

        $('#roomModalTitle').html('<i class="fa fa-edit"></i> Edit Room');
        $('#saveRoomBtnText').text('Update');
        $('#roomModal').modal('show');
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
            alert('You cannot add a room to a block that has no rooms!');
            return;
        }

        const items = [];
        const itemTypes = ['table', 'chair', 'cabinet', 'wardrobe', 'other'];
        
        itemTypes.forEach(itemType => {
            const checkbox = $(`#roomItem_${itemType}`);
            if (checkbox.is(':checked')) {
                const quantityInput = $(`#room${itemType.charAt(0).toUpperCase() + itemType.slice(1)}Quantity`);
                items.push({
                    itemType: itemType,
                    quantity: parseInt(quantityInput.val()) || 1
                });
            }
        });

        const roomData = {
            roomID: $('#roomID').val() || null,
            blockID: parseInt(blockID),
            roomName: $('#roomName').val(),
            roomNumber: $('#roomNumber').val(),
            capacity: parseInt($('#roomCapacity').val()),
            status: $('#roomStatus').val(),
            description: $('#roomDescription').val(),
            items: items
        };

        // AJAX call to save room
        $.ajax({
            url: '/api/accommodation/rooms',
            method: roomData.roomID ? 'PUT' : 'POST',
            data: roomData,
            success: function(response) {
                loadRooms();
                loadBlocks(); // Refresh to update room count
                $('#roomModal').modal('hide');
                alert('Room saved successfully!');
            },
            error: function(xhr) {
                console.error('Error saving room:', xhr);
                alert('Error saving room. Please try again.');
            }
        });
    }

    // Delete Room
    function deleteRoom(roomID) {
        if (!confirm('Are you sure you want to delete this room?')) {
            return;
        }

        $.ajax({
            url: `/api/accommodation/rooms/${roomID}`,
            method: 'DELETE',
            success: function(response) {
                loadRooms();
                loadBlocks(); // Refresh to update room count
                alert('Room deleted successfully!');
            },
            error: function(xhr) {
                console.error('Error deleting room:', xhr);
                alert('Error deleting room. Please try again.');
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
            },
            error: function(xhr) {
                console.error('Error loading rooms:', xhr);
                rooms = [];
                renderRoomsTable();
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
            alert('Please add a block first before adding a bed!');
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
            alert('This block has rooms. Please select a room for this bed.');
            return;
        }

        // Validate: if block has no rooms, room should be empty
        if (block && block.blockType === 'without_rooms' && roomID) {
            alert('This block has no rooms. Please leave the room field empty.');
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
                alert('Bed saved successfully!');
            },
            error: function(xhr) {
                console.error('Error saving bed:', xhr);
                alert('Error saving bed. Please try again.');
            }
        });
    }

    // Delete Bed
    function deleteBed(bedID) {
        if (!confirm('Are you sure you want to delete this bed?')) {
            return;
        }

        $.ajax({
            url: `/api/accommodation/beds/${bedID}`,
            method: 'DELETE',
            success: function(response) {
                loadBeds();
                alert('Bed deleted successfully!');
            },
            error: function(xhr) {
                console.error('Error deleting bed:', xhr);
                alert('Error deleting bed. Please try again.');
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
            },
            error: function(xhr) {
                console.error('Error loading beds:', xhr);
                beds = [];
                renderBedsTable();
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
</script>
