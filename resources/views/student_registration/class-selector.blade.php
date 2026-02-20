<!-- Subclass Selector Modal -->
<div class="modal fade" id="classSelectorModal" tabindex="-1" aria-hidden="true" 
    onclick="if(event.target===this){ var m=document.getElementById('classSelectorModal'); if(m){m.style.display='none';m.classList.remove('show');m.setAttribute('aria-hidden','true');} document.body.classList.remove('modal-open'); document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove()); }">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 95vw;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: white; border-bottom: 1px solid #e9ecef; color: #212529;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-building"></i> Select Class to Register Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" 
                    onclick="var m=document.getElementById('classSelectorModal'); if(m){m.style.display='none';m.classList.remove('show');m.setAttribute('aria-hidden','true');} document.body.classList.remove('modal-open'); document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());"></button>
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
    // Make loadSubclasses globally accessible so it can be called from manage_student.blade.php
    window.loadSubclasses = function() {
        const container = document.getElementById('classesContainer');
        if (!container) {
            console.error('classesContainer not found');
            return;
        }

        // Avoid double loading if already loaded
        if (container.dataset.loaded === 'true' && container.querySelectorAll('.small-card').length > 0) {
            console.log('Classes already loaded, skipping fetch');
            return;
        }

        // Show spinner
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading classes...</p>
            </div>
        `;

        const url = '{{ route("get_subclasses_with_stats") }}';
        console.log('Fetching subclasses from:', url);

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) throw new Error('HTTP status ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('Subclasses data received:', data);
                if (data.success && data.subclasses && data.subclasses.length > 0) {
                    displaySubclasses(data.subclasses);
                    container.dataset.loaded = 'true';
                } else if (data.success) {
                    showError('No classes found for your school.');
                    container.dataset.loaded = 'true';
                } else {
                    showError(data.message || 'Unknown error');
                }
            })
            .catch(err => {
                console.error('Fetch error in loadSubclasses:', err);
                showError(err.message || 'Failed to load classes. Please check your connection.');
            });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const classSelectorModal = document.getElementById('classSelectorModal');
        if (classSelectorModal) {
            classSelectorModal.addEventListener('show.bs.modal', window.loadSubclasses);
            // Support for jQuery-based show
            if (window.jQuery) {
                $(classSelectorModal).on('show.bs.modal', window.loadSubclasses);
            }
        }

        // Search functionality
        const searchInput = document.getElementById('classSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('#classesContainer .col-lg-3');
                cards.forEach(cardCol => {
                    const card = cardCol.querySelector('.small-card');
                    const cname = (card.getAttribute('data-subclass-name') || '').toLowerCase();
                    const pclass = (card.getAttribute('data-class-name') || '').toLowerCase();
                    const text = cname + ' ' + pclass;
                    cardCol.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    });

    function showError(message) {
        const container = document.getElementById('classesContainer');
        if (container) {
            container.innerHTML = `<div class="col-12"><div class="alert alert-danger">${message}</div></div>`;
        }
    }

    function displaySubclasses(subclasses) {
        const container = document.getElementById('classesContainer');
        if (!container) return;
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
                <button class="btn btn-sm btn-outline-success w-100 add-btn" 
                    data-subclass-id="${s.subclassID}" 
                    data-subclass-name="${s.subclass_name}" 
                    data-class-name="${s.class_name}">Add Student</button>
            `;

            card.appendChild(body);
            col.appendChild(card);
            container.appendChild(col);
        });

        // Add click handlers for buttons
        document.querySelectorAll('.add-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.getAttribute('data-subclass-id');
                const sname = this.getAttribute('data-subclass-name');
                const cname = this.getAttribute('data-class-name');
                openRegistrationModal(id, sname, cname);
            });
        });

        // Add click handlers for cards
        document.querySelectorAll('.small-card').forEach(card => {
            card.addEventListener('click', function() {
                const id = this.getAttribute('data-subclass-id');
                const sname = this.getAttribute('data-subclass-name');
                const cname = this.getAttribute('data-class-name');
                openRegistrationModal(id, sname, cname);
            });
            card.addEventListener('mouseenter', function() { this.style.transform = 'translateY(-4px)'; this.style.boxShadow = '0 0.5rem 1rem rgba(0,0,0,0.1)'; });
            card.addEventListener('mouseleave', function() { this.style.transform = ''; this.style.boxShadow = ''; });
        });
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
                    // Priority 1: jQuery
                    if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                        jQuery('#registrationModal').modal('show');
                        return;
                    }

                    // Priority 2: Bootstrap 5
                    if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                        const reg = new bootstrap.Modal(regEl, { backdrop: 'static', keyboard: false });
                        reg.show();
                        return;
                    }
                } catch (err) {
                     console.error('Modal open failed:', err);
                     regEl.classList.add('show');
                     regEl.style.display = 'block';
                }
             }, 150);
        };

        // Force close selector modal using all available methods
        try {
            if (window.bootstrap && typeof bootstrap.Modal === 'function' && typeof bootstrap.Modal.getInstance === 'function') {
                const inst = bootstrap.Modal.getInstance(selectorEl);
                if (inst) inst.hide();
            }
            if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                jQuery(selectorEl).modal('hide');
            }
        } catch (e) { console.warn('Modal hide failed', e); }

        // Manual cleanup
        selectorEl.classList.remove('show');
        selectorEl.style.display = 'none';
        selectorEl.setAttribute('aria-hidden', 'true');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');

        // Open registration modal after short delay
        setTimeout(showRegistrationModal, 300);
    }
</script>

<style>
.small-card { cursor: pointer; transition: all .2s ease; }
.small-card .card-body { padding: .7rem; }
.mall-card .fw-bold { font-size: .95rem; }
.small-card p { margin-bottom: .4rem; }
</style>
