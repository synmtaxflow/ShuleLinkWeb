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
        // Set subclass info
        const input = document.getElementById('selectedSubclassID');
        const display = document.getElementById('selectedSubclassName');
        if (input) input.value = id;
        if (display) {
            const cn = (typeof className === 'string' && className.length) ? className : '';
            const sn = (typeof name === 'string' && name.length) ? name : '';
            const label = (cn || sn) ? (cn + ' ' + sn).trim().toUpperCase() : '';
            display.textContent = label;
        }

        const selectorEl = document.getElementById('classSelectorModal');
        const regEl = document.getElementById('registrationModal');

        if (!selectorEl || !regEl) return;

        // Function to open the registration modal
        const showRegistrationModal = () => {
             // Small timeout to ensure DOM is settled
             setTimeout(() => {
                try {
                    console.log('Attempting to open registration modal...');
                    
                    // Priority 1: jQuery (Most robust for this setup)
                    if (window.jQuery) {
                        try {
                            console.log('Trying jQuery modal...');
                            jQuery('#registrationModal').modal('show');
                            return; // Success
                        } catch (jqErr) {
                            console.warn('jQuery modal failed, trying Bootstrap 5 native:', jqErr);
                        }
                    }

                    // Priority 2: Bootstrap 5 Native
                    if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                        console.log('Trying Bootstrap 5 native...');
                        // Dispose existing instance if any
                        if (typeof bootstrap.Modal.getInstance === 'function') {
                            const existingInst = bootstrap.Modal.getInstance(regEl);
                            if (existingInst) existingInst.dispose();
                        }
                        
                        const reg = new bootstrap.Modal(regEl, { backdrop: 'static', keyboard: false, focus: true });
                        reg.show();
                        return; // Success
                    }
                    
                    // Priority 3: Manual Fallback
                    console.log('Using manual fallback...');
                    throw new Error('No compatible modal library found');
                    
                } catch (err) {
                     console.error('Modal open failed, forcing manual display:', err);
                     // Last resort fallback
                     regEl.classList.add('show');
                     regEl.style.display = 'block';
                     regEl.setAttribute('aria-hidden', 'false');
                     document.body.classList.add('modal-open');
                     
                     if (!document.querySelector('.modal-backdrop')) {
                         const bd = document.createElement('div');
                         bd.className = 'modal-backdrop show';
                         bd.id = 'regBackdrop';
                         document.body.appendChild(bd);
                     }
                }
             }, 150);
        };

        // Force close selector modal using all available methods
        try {
            if (window.bootstrap && typeof bootstrap.Modal === 'function' && typeof bootstrap.Modal.getInstance === 'function') {
                const inst = bootstrap.Modal.getInstance(selectorEl);
                if (inst) inst.hide();
            }
        } catch (e) { console.warn('BS hide failed', e); }

        try {
            if (window.jQuery) {
                jQuery(selectorEl).modal('hide');
            }
        } catch (e) { console.warn('jQuery hide failed', e); }

        // Manual cleanup just in case
        selectorEl.classList.remove('show');
        selectorEl.style.display = 'none';
        selectorEl.setAttribute('aria-hidden', 'true');
        
        // Remove ANY backdrop immediately to prevent stacking issues
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');

        // Open registration modal after short delay
        setTimeout(showRegistrationModal, 300);
    }
});
</script>

<style>
.small-card { cursor: pointer; transition: all .2s ease; }
.small-card .card-body { padding: .7rem; }
.mall-card .fw-bold { font-size: .95rem; }
.small-card p { margin-bottom: .4rem; }
</style>
