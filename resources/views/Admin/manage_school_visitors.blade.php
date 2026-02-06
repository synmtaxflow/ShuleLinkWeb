@php($watchmanOnly = $watchmanOnly ?? false)
@php($todayRoute = $watchmanOnly ? route('watchman.school_visitors.today') : route('admin.school_visitors.today'))
@php($storeRoute = $watchmanOnly ? route('watchman.school_visitors.store') : route('admin.school_visitors.store'))

@if(!$watchmanOnly)
@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif
@else
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .section-title { font-weight: 600; margin-bottom: 12px; }
    .visitor-tabs .nav-link { cursor: pointer; color: #940000; border-radius: 0; }
    .visitor-tabs .nav-link.active { background: #fff5f5; color: #940000; font-weight: 600; border-color: #940000; }
    .signature-box {
        border: 1px dashed rgba(148, 0, 0, 0.4);
        height: 70px;
        width: 100%;
        background: #fff;
        touch-action: none;
        user-select: none;
        cursor: crosshair;
    }
    .signature-actions .btn {
        padding: 2px 8px;
        font-size: 0.75rem;
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
    .watchman-only .content {
        padding: 0 12px 16px;
    }
    .watchman-only .card {
        margin-bottom: 16px;
    }
    .watchman-only .table-responsive {
        overflow-x: auto;
    }
    .watchman-only .table {
        min-width: 720px;
    }
    @media (max-width: 768px) {
        .breadcrumbs {
            padding: 0 12px;
        }
        .page-title h1 {
            font-size: 1.1rem;
        }
        .card-body {
            padding: 12px;
        }
        .visitor-tabs .nav-link {
            font-size: 0.9rem;
            padding: 8px 10px;
        }
        .signature-box {
            height: 60px;
        }
        .btn {
            font-size: 0.9rem;
        }
    }
</style>

<div class="{{ $watchmanOnly ? 'watchman-only' : '' }}">
<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>{{ $watchmanOnly ? 'Wageni wa Shule' : 'School Visitors' }}</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
            <strong>{{ $watchmanOnly ? 'Usajili wa Wageni wa Shule' : 'School Visitors Management' }}</strong>
            @if($watchmanOnly)
                <a href="{{ route('logout') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-sign-out"></i> Toka
                </a>
            @endif
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs visitor-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link active" data-target="#section-record-visitors" href="#">
                        <i class="fa fa-pencil-square-o"></i> {{ $watchmanOnly ? 'Sajili Mgeni wa Shule' : 'Record School Visitor' }}
                    </a>
                </li>
                @unless($watchmanOnly)
                    <li class="nav-item">
                        <a class="nav-link" data-target="#section-view-visitors" href="#">
                            <i class="fa fa-list"></i> View / Manage Visitors
                        </a>
                    </li>
                @endunless
            </ul>
            <div>
                <div id="section-record-visitors" class="visitor-section">
                        <div class="section-title">{{ $watchmanOnly ? 'Sajili Wageni (Leo)' : 'Record Visitors (Today)' }}</div>
                        <div class="form-loading" id="recordLoading">
                            <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> {{ $watchmanOnly ? 'Inapakia...' : 'Loading...' }}</span>
                            <div class="form-progress"></div>
                        </div>
                        <form id="visitorForm">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="visitorTable">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>{{ $watchmanOnly ? 'Tarehe' : 'Date' }}</th>
                                            <th>{{ $watchmanOnly ? 'Jina' : 'Name' }}</th>
                                            <th>{{ $watchmanOnly ? 'Simu / Barua pepe' : 'Phone / Email' }}</th>
                                            <th>{{ $watchmanOnly ? 'Kazi / Taasisi' : 'Occupation / Institution' }}</th>
                                            <th>{{ $watchmanOnly ? 'Sababu ya Ziara' : 'Reason for Visit' }}</th>
                                    <th>{{ $watchmanOnly ? 'Sahihi' : 'Signature' }}</th>
                                    <th>{{ $watchmanOnly ? 'Kitendo' : 'Action' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="visitorTableBody">
                                        <tr>
                                    <td colspan="7" class="text-center text-muted">{{ $watchmanOnly ? 'Inapakia...' : 'Loading...' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <button type="button" class="btn btn-light" id="addVisitorRow">
                                    <i class="fa fa-plus"></i> {{ $watchmanOnly ? 'Ongeza Mstari' : 'Add Row' }}
                                </button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="fa fa-save"></i> {{ $watchmanOnly ? 'Hifadhi Wageni' : 'Save Visitors' }}
                                </button>
                            </div>
                        </form>
                </div>
                @unless($watchmanOnly)
                <div id="section-view-visitors" class="visitor-section" style="display:none;">
                        <div class="section-title">View / Manage Visitors</div>
                    <div class="form-loading" id="viewLoading">
                        <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                        <div class="form-progress"></div>
                    </div>
                    <form id="visitorFilterForm" class="mb-3">
                        <div class="form-row align-items-center">
                            <div class="col-md-3 mb-2">
                                <input type="date" class="form-control" name="date_from" id="visitorDateFrom">
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="date" class="form-control" name="date_to" id="visitorDateTo">
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fa fa-search"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button class="btn btn-light w-100" type="button" id="selectAllBtn">
                                    Select All
                                </button>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button class="btn btn-primary-custom w-100" type="button" id="openSmsModal">
                                    <i class="fa fa-envelope"></i> Send SMS
                                </button>
                            </div>
                        </div>
                        <div class="text-muted mt-1" id="smsLimitInfo">SMS limit: 200 / month</div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th><input type="checkbox" id="selectAllVisitors"></th>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Phone / Email</th>
                                    <th>Occupation</th>
                                    <th>Reason</th>
                                    <th>Signature</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="visitorListBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No data loaded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted">
                        Selected recipients: <span id="smsRecipientCountInline">0</span>
                    </div>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>
</div>

@unless($watchmanOnly)
<div class="modal fade" id="editVisitorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Visitor</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editVisitorForm">
                <div class="modal-body">
                    <input type="hidden" name="visitorID" id="editVisitorID">
                    <div class="form-group mb-2">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" id="editVisitorName" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Phone / Email</label>
                        <input type="text" class="form-control" name="contact" id="editVisitorContact">
                    </div>
                    <div class="form-group mb-2">
                        <label>Occupation / Institution</label>
                        <input type="text" class="form-control" name="occupation" id="editVisitorOccupation">
                    </div>
                    <div class="form-group mb-2">
                        <label>Reason</label>
                        <input type="text" class="form-control" name="reason" id="editVisitorReason">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="smsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send SMS to Visitors</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="smsForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Message (max 163 chars, no emojis)</label>
                        <textarea class="form-control" name="message" id="smsMessage" rows="3" maxlength="163" required>{{ $schoolName ?? 'School' }}: </textarea>
                        <small class="text-muted">Characters: <span id="smsCharCount">0</span>/163</small>
                    </div>
                    <div class="alert alert-info">
                        Selected recipients: <span id="smsRecipientCount">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Send SMS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endunless

@unless($watchmanOnly)
    @include('includes.footer')
@endunless

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const watchmanOnly = @json($watchmanOnly);
    const labels = watchmanOnly ? {
        saved: 'Imehifadhiwa',
        successTitle: 'Imefanikiwa',
        failedTitle: 'Imeshindikana',
        failedSave: 'Imeshindikana kuhifadhi wageni.',
        loading: 'Inapakia...'
    } : {
        saved: 'Saved',
        successTitle: 'Success',
        failedTitle: 'Failed',
        failedSave: 'Failed to save visitors.',
        loading: 'Loading...'
    };
    const menuLinks = document.querySelectorAll('.visitor-tabs .nav-link');
    const sections = document.querySelectorAll('.visitor-section');

    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');
            sections.forEach(section => section.style.display = 'none');
            const target = document.querySelector(link.getAttribute('data-target'));
            if (target) target.style.display = 'block';
            if (target && target.id === 'section-record-visitors') {
                loadTodayVisitors();
            }
            if (target && target.id === 'section-view-visitors') {
                loadVisitorList();
            }
        });
    });

    function initSignatureCanvas(canvas, hiddenInput) {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let drawing = false;

        const resizeCanvas = () => {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            canvas.dataset.blank = canvas.toDataURL('image/png');
        };
        resizeCanvas();

        const getPos = (e) => {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: clientX - rect.left, y: clientY - rect.top };
        };

        const startDraw = (e) => {
            drawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        };

        const draw = (e) => {
            if (!drawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 1.5;
            ctx.lineCap = 'round';
            ctx.stroke();
            e.preventDefault();
        };

        const stopDraw = () => {
            drawing = false;
            if (hiddenInput) {
                hiddenInput.value = canvas.toDataURL('image/png');
            }
        };

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);

        return {
            clear: () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            },
            toData: () => canvas.toDataURL('image/png')
        };
    }

    function wireSignatureRow(row) {
        const canvas = row.querySelector('[data-signature]');
        const hiddenInput = row.querySelector('input[type="hidden"][name="signature[]"]');
        initSignatureCanvas(canvas, hiddenInput);
    }

    function createEditableRow() {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="date" class="form-control" value="{{ date('Y-m-d') }}" readonly></td>
            <td><input type="text" class="form-control" name="name[]"></td>
            <td><input type="text" class="form-control" name="contact[]" value="255"></td>
            <td><input type="text" class="form-control" name="occupation[]"></td>
            <td><input type="text" class="form-control" name="reason[]"></td>
            <td>
                <canvas class="signature-box" data-signature></canvas>
                <input type="hidden" name="signature[]">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;
        return row;
    }

    function renderTodayVisitors(items) {
        const tbody = document.getElementById('visitorTableBody');
        tbody.innerHTML = '';
        if (!items || items.length === 0) {
            const row = createEditableRow();
            tbody.appendChild(row);
            wireSignatureRow(row);
            return;
        }
        items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="date" class="form-control" value="${item.visitDate || ''}" readonly></td>
                <td><input type="text" class="form-control" value="${item.name || ''}" readonly></td>
                <td><input type="text" class="form-control" value="${item.contact || ''}" readonly></td>
                <td><input type="text" class="form-control" value="${item.occupation || ''}" readonly></td>
                <td><input type="text" class="form-control" value="${item.reason || ''}" readonly></td>
                <td>
                    ${item.signature ? `<img src="${item.signature}" alt="Signature" style="max-height:50px;">` : 'N/A'}
                </td>
                <td><span class="text-muted">${labels.saved}</span></td>
            `;
            tbody.appendChild(row);
        });
        const newRow = createEditableRow();
        tbody.appendChild(newRow);
        wireSignatureRow(newRow);
    }

    function loadTodayVisitors() {
        const loading = document.getElementById('recordLoading');
        if (loading) loading.style.display = 'flex';
        fetch(`{{ $todayRoute }}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderTodayVisitors(data.data);
            } else {
                renderTodayVisitors([]);
            }
        })
        .catch(() => renderTodayVisitors([]))
        .finally(() => {
            if (loading) loading.style.display = 'none';
        });
    }

    document.getElementById('addVisitorRow').addEventListener('click', () => {
        const tbody = document.getElementById('visitorTableBody');
        const row = createEditableRow();
        tbody.appendChild(row);
        wireSignatureRow(row);
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('tr');
            if (row) {
                row.remove();
            }
        }
    });

    document.getElementById('visitorForm').addEventListener('submit', (e) => {
        e.preventDefault();
        document.querySelectorAll('[data-signature]').forEach(canvas => {
            const hidden = canvas.parentElement.querySelector('input[type="hidden"][name="signature[]"]');
            if (!hidden) return;
            const data = canvas.toDataURL('image/png');
            hidden.value = data !== canvas.dataset.blank ? data : '';
        });
        const formData = new FormData(e.target);
        const loading = document.getElementById('recordLoading');
        if (loading) loading.style.display = 'flex';
        fetch(`{{ $storeRoute }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: labels.successTitle, text: data.message, confirmButtonColor: '#940000' });
                loadTodayVisitors();
            } else {
                Swal.fire({ icon: 'error', title: labels.failedTitle, text: data.message || labels.failedSave, confirmButtonColor: '#940000' });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: labels.failedTitle, text: labels.failedSave, confirmButtonColor: '#940000' });
        })
        .finally(() => {
            if (loading) loading.style.display = 'none';
        });
    });

    function renderVisitorList(items) {
        const tbody = document.getElementById('visitorListBody');
        tbody.innerHTML = '';
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No visitors found.</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(item => `
            <tr data-visitor-id="${item.visitorID}">
                <td><input type="checkbox" class="visitor-checkbox" value="${item.visitorID}"></td>
                <td>${item.visitDate || ''}</td>
                <td>${item.name || ''}</td>
                <td>${item.contact || ''}</td>
                <td>${item.occupation || ''}</td>
                <td>${item.reason || ''}</td>
                <td>${item.signature ? `<img src="${item.signature}" alt="Signature" style="max-height:40px;">` : 'N/A'}</td>
                <td>
                    <button class="btn btn-sm btn-light edit-visitor">Edit</button>
                    <button class="btn btn-sm btn-danger delete-visitor">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function loadVisitorList() {
        const loading = document.getElementById('viewLoading');
        if (loading) loading.style.display = 'flex';
        const form = document.getElementById('visitorFilterForm');
        const params = new URLSearchParams(new FormData(form));
        fetch(`{{ route('admin.school_visitors.list') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderVisitorList(data.data);
                if (typeof data.used !== 'undefined') {
                    document.getElementById('smsLimitInfo').textContent = `SMS limit: ${data.used} / 200 (this month)`;
                }
            } else {
                renderVisitorList([]);
            }
        })
        .catch(() => renderVisitorList([]))
        .finally(() => {
            if (loading) loading.style.display = 'none';
        });
    }

    function openModal(modalId) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
                instance.show();
                return;
            }
            const instance = new bootstrap.Modal(modalEl);
            instance.show();
            return;
        }
        if (window.$ && $.fn && $.fn.modal) {
            $(modalEl).modal('show');
            return;
        }
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        document.body.classList.add('modal-open');
    }

    function closeModal(modalId) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
                instance.hide();
                return;
            }
            const instance = new bootstrap.Modal(modalEl);
            instance.hide();
            return;
        }
        if (window.$ && $.fn && $.fn.modal) {
            $(modalEl).modal('hide');
            return;
        }
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.removeAttribute('aria-modal');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
        document.body.classList.remove('modal-open');
    }

    const visitorFilterForm = document.getElementById('visitorFilterForm');
    if (visitorFilterForm) {
        visitorFilterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            loadVisitorList();
        });
    }

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('edit-visitor')) {
            const row = e.target.closest('tr');
            document.getElementById('editVisitorID').value = row.dataset.visitorId;
            document.getElementById('editVisitorName').value = row.children[2].textContent.trim();
            document.getElementById('editVisitorContact').value = row.children[3].textContent.trim();
            document.getElementById('editVisitorOccupation').value = row.children[4].textContent.trim();
            document.getElementById('editVisitorReason').value = row.children[5].textContent.trim();
            openModal('editVisitorModal');
        }
        if (e.target.classList.contains('delete-visitor')) {
            const row = e.target.closest('tr');
            const visitorID = row.dataset.visitorId;
            Swal.fire({
                icon: 'warning',
                title: 'Delete visitor?',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Delete'
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(`{{ route('admin.school_visitors.delete') }}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: new URLSearchParams({ visitorID })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, confirmButtonColor: '#940000' });
                        loadVisitorList();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Delete failed.', confirmButtonColor: '#940000' });
                    }
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Delete failed.', confirmButtonColor: '#940000' }));
            });
        }
    });

    const editVisitorForm = document.getElementById('editVisitorForm');
    if (editVisitorForm) {
        editVisitorForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            fetch(`{{ route('admin.school_visitors.update') }}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Updated', text: data.message, confirmButtonColor: '#940000' });
                    closeModal('editVisitorModal');
                    loadVisitorList();
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Update failed.', confirmButtonColor: '#940000' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Update failed.', confirmButtonColor: '#940000' }));
        });
    }

    const selectAllVisitors = document.getElementById('selectAllVisitors');
    if (selectAllVisitors) {
        selectAllVisitors.addEventListener('change', (e) => {
            document.querySelectorAll('.visitor-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
            });
            const count = document.querySelectorAll('.visitor-checkbox:checked').length;
            const smsRecipientCount = document.getElementById('smsRecipientCount');
            const smsRecipientCountInline = document.getElementById('smsRecipientCountInline');
            if (smsRecipientCount) smsRecipientCount.textContent = count;
            if (smsRecipientCountInline) smsRecipientCountInline.textContent = count;
        });
    }

    const openSmsModal = document.getElementById('openSmsModal');
    if (openSmsModal) {
        openSmsModal.addEventListener('click', (e) => {
            e.preventDefault();
            const selected = document.querySelectorAll('.visitor-checkbox:checked');
            const smsRecipientCount = document.getElementById('smsRecipientCount');
            const smsRecipientCountInline = document.getElementById('smsRecipientCountInline');
            if (smsRecipientCount) smsRecipientCount.textContent = selected.length;
            if (smsRecipientCountInline) smsRecipientCountInline.textContent = selected.length;
            const defaultPrefix = '{{ $schoolName ?? 'School' }}: ';
            const smsMessage = document.getElementById('smsMessage');
            const smsCharCount = document.getElementById('smsCharCount');
            if (smsMessage) smsMessage.value = defaultPrefix;
            if (smsCharCount) smsCharCount.textContent = defaultPrefix.length;
            openModal('smsModal');
        });
    }

    const selectAllBtn = document.getElementById('selectAllBtn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            const checkboxes = document.querySelectorAll('.visitor-checkbox');
            const allSelected = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => { cb.checked = !allSelected; });
            const selectAllVisitorsBox = document.getElementById('selectAllVisitors');
            if (selectAllVisitorsBox) selectAllVisitorsBox.checked = !allSelected;
            const count = document.querySelectorAll('.visitor-checkbox:checked').length;
            const smsRecipientCount = document.getElementById('smsRecipientCount');
            const smsRecipientCountInline = document.getElementById('smsRecipientCountInline');
            if (smsRecipientCount) smsRecipientCount.textContent = count;
            if (smsRecipientCountInline) smsRecipientCountInline.textContent = count;
        });
    }

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('visitor-checkbox')) {
            const total = document.querySelectorAll('.visitor-checkbox').length;
            const checked = document.querySelectorAll('.visitor-checkbox:checked').length;
            document.getElementById('selectAllVisitors').checked = total > 0 && checked === total;
            document.getElementById('smsRecipientCount').textContent = checked;
            document.getElementById('smsRecipientCountInline').textContent = checked;
        }
    });

    const smsMessage = document.getElementById('smsMessage');
    if (smsMessage) {
        smsMessage.addEventListener('input', (e) => {
            const smsCharCount = document.getElementById('smsCharCount');
            if (smsCharCount) smsCharCount.textContent = e.target.value.length;
        });
    }

    const smsForm = document.getElementById('smsForm');
    if (smsForm) {
        smsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const selected = Array.from(document.querySelectorAll('.visitor-checkbox:checked')).map(cb => cb.value);
            if (selected.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Select recipients', text: 'Please select at least one visitor.', confirmButtonColor: '#940000' });
                return;
            }
            const message = document.getElementById('smsMessage').value.trim();
            Swal.fire({ icon: 'info', title: 'Sending...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch(`{{ route('admin.school_visitors.sms') }}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message, visitor_ids: selected }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Sent', text: data.message, confirmButtonColor: '#940000' });
                    closeModal('smsModal');
                    loadVisitorList();
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'SMS failed.', confirmButtonColor: '#940000' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'SMS failed.', confirmButtonColor: '#940000' }));
        });
    }

    loadTodayVisitors();
</script>
