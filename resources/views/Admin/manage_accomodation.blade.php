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

    // Initialize on page load
    $(document).ready(function() {
        loadBlocks();
        loadRooms();
        loadBeds();
        initAccommodationMenu();
        bindRoomRowHandlers();
        resetBlockForm();
        resetRoomForm();

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
                <button type="button" class="btn btn-sm btn-delete remove-room-row">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        `);
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
                showAlert('error', 'Failed', 'Error saving block. Please try again.');
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

    // Load Blocks
    function loadBlocks() {
        $.ajax({
            url: '/api/accommodation/blocks',
            method: 'GET',
            success: function(response) {
                blocks = response.data || [];
                renderBlocksTable();
                populateRoomBlocks();
            },
            error: function(xhr) {
                console.error('Error loading blocks:', xhr);
                blocks = [];
                renderBlocksTable();
                populateRoomBlocks();
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
            roomsToSave.push({
                roomName: name,
                roomNumber: number,
                capacity: capacity
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
                items: []
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
                    items: []
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
                showAlert('error', 'Failed', 'Error deleting bed. Please try again.');
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
            },
            error: function(xhr) {
                console.error('Error loading beds:', xhr);
                beds = [];
                renderBedsTable();
                renderBlocksTable();
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
