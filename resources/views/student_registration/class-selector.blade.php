<!-- Subclass Selector Modal -->
<div class="modal fade" id="classSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 95vw;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: white; border-bottom: 1px solid #e9ecef; color: #212529;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-building"></i> Select Class to Register Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-2">
                    <input type="search" id="classSearch" class="form-control form-control-sm" placeholder="Search class or stream...">
                </div>
                <div class="row g-2" id="classesContainer">
                    <!-- Classes will be loaded here via JavaScript -->
                    <div class="col-12 text-center">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelectorModal = document.getElementById('classSelectorModal');
    if (classSelectorModal) {
        classSelectorModal.addEventListener('show.bs.modal', loadSubclasses);
    }

    function loadSubclasses() {
        const url = '{{ route("get_subclasses_with_stats") }}';
        console.log('Fetching subclasses from:', url);

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) throw new Error('HTTP status ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('Data:', data);
                if (data.success && data.subclasses && data.subclasses.length > 0) {
                    displaySubclasses(data.subclasses);
                } else if (data.success) {
                    showError('No classes found for your school.');
                } else {
                    showError(data.message || 'Unknown error');
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                showError(err.message || 'Failed to load classes');
            });
    }

    function displaySubclasses(subclasses) {
        const container = document.getElementById('classesContainer');
        container.innerHTML = '';

        subclasses.forEach(s => {
            const col = document.createElement('div');
            col.className = 'col-lg-3 col-md-4 col-sm-6 mb-2';

            const card = document.createElement('div');
            card.className = 'card small-card h-100';
            card.setAttribute('data-subclass-id', s.subclassID);
            card.setAttribute('data-subclass-name', s.subclass_name);
            card.setAttribute('data-class-name', s.class_name);

            const body = document.createElement('div');
            body.className = 'card-body p-3 text-center';

            body.innerHTML = `
                <div style="font-size:2.2rem;color:#212529;margin-bottom:.5rem;"><i class="bi bi-people-fill"></i></div>
                <h6 class="fw-bold mb-1">${s.class_name}</h6>
                <p class="text-muted small mb-2">${s.subclass_name}</p>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="badge bg-info">${s.total_students} Total</span>
                    <span class="badge bg-primary">${s.male_count} M</span>
                    <span class="badge bg-danger">${s.female_count} F</span>
                </div>
                <button class="btn btn-sm btn-outline-success w-100 add-btn" data-subclass-id="${s.subclassID}" data-subclass-name="${s.subclass_name}" data-class-name="${s.class_name}">Add Student</button>
            `;

            card.appendChild(body);
            col.appendChild(card);
            container.appendChild(col);
        });

        // Add handlers
        document.querySelectorAll('.add-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.getAttribute('data-subclass-id');
                const sname = this.getAttribute('data-subclass-name');
                const cname = this.getAttribute('data-class-name');
                openRegistrationModal(id, sname, cname);
            });
        });

        document.querySelectorAll('.small-card').forEach(card => {
            card.addEventListener('click', function() {
                const id = this.getAttribute('data-subclass-id');
                const sname = this.getAttribute('data-subclass-name');
                const cname = this.getAttribute('data-class-name') || (this.querySelector('.fw-bold')?.textContent || '');
                openRegistrationModal(id, sname, cname);
            });

            card.addEventListener('mouseenter', function() { this.style.transform = 'translateY(-4px)'; this.style.boxShadow = '0 0.5rem 1rem rgba(0,0,0,0.1)'; });
            card.addEventListener('mouseleave', function() { this.style.transform = ''; this.style.boxShadow = ''; });
        });

        // Wire search
        const search = document.getElementById('classSearch');
        if (search) {
            search.oninput = function() {
                const q = this.value.trim().toLowerCase();
                document.querySelectorAll('#classesContainer .small-card').forEach(card => {
                    const cname = (card.getAttribute('data-subclass-name') || '').toLowerCase();
                    const pclass = (card.querySelector('.fw-bold')?.textContent || '').toLowerCase();
                    const text = cname + ' ' + pclass;
                    card.parentElement.style.display = text.includes(q) ? '' : 'none';
                });
            };
        }
    }

    function showError(msg) {
        document.getElementById('classesContainer').innerHTML = `<div class="col-12 alert alert-danger">${msg}</div>`;
    }

    function openRegistrationModal(id, name, className) {
        // Set subclass info before closing selector
        const input = document.getElementById('selectedSubclassID');
        const display = document.getElementById('selectedSubclassName');
        if (input) input.value = id;
        if (display) {
            const cn = (typeof className === 'string' && className.length) ? className : '';
            const sn = (typeof name === 'string' && name.length) ? name : '';
            const label = (cn || sn) ? (cn + ' ' + sn).trim().toUpperCase() : '';
            display.textContent = label;
        }

        // Fully destroy/close the class selector modal (remove from DOM and cleanup)
        try {
            const selectorEl = document.getElementById('classSelectorModal');
            // Remove all modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            // Hide and detach selector modal
            if (selectorEl) {
                if (window.bootstrap && typeof bootstrap.Modal === 'function' && typeof bootstrap.Modal.getInstance === 'function') {
                    const inst = bootstrap.Modal.getInstance(selectorEl);
                    if (inst) inst.hide();
                } else if (window.jQuery) {
                    try { jQuery('#classSelectorModal').modal('hide'); } catch (e) { /* ignore */ }
                }
                selectorEl.classList.add('d-none');
                selectorEl.style.display = 'none';
            }
        } catch (err) {
            console.warn('Error closing selector modal:', err);
        }

        // Now open registration modal with a small delay to ensure selector is fully hidden
        setTimeout(() => {
            try {
                const regEl = document.getElementById('registrationModal');
                if (!regEl) return;

                // Add modal-open back to body for new modal
                document.body.classList.add('modal-open');
                regEl.classList.remove('d-none');
                regEl.style.display = 'block';
                regEl.setAttribute('aria-hidden', 'false');

                // Use bootstrap if available
                if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                    const reg = new bootstrap.Modal(regEl, { backdrop: 'static', keyboard: false });
                    reg.show();
                } else if (window.jQuery) {
                    try { jQuery('#registrationModal').modal('show'); } catch (e) { /* ignore */ }
                } else {
                    // Fallback: create backdrop and show modal
                    const bd = document.createElement('div');
                    bd.className = 'modal-backdrop show';
                    bd.id = 'regBackdrop';
                    document.body.appendChild(bd);
                    regEl.classList.add('show');
                    regEl.style.zIndex = 1060;
                }

                // Focus first input after modal is shown
                setTimeout(() => {
                    const first = regEl.querySelector('input[name="first_name"]');
                    if (first) first.focus();
                }, 100);
            } catch (err) {
                console.error('Failed to open registration modal:', err);
            }
        }, 200);
    }
});
</script>

<style>
.small-card { cursor: pointer; transition: all .2s ease; }
.small-card .card-body { padding: .7rem; }
.mall-card .fw-bold { font-size: .95rem; }
.small-card p { margin-bottom: .4rem; }
</style>
