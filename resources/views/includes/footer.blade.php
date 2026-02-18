<!-- ======= Custom Footer Start ======= -->
<style>
  /* Reserve space for fixed footer to avoid overlapping content */
  body { padding-bottom: 50px; }

  .site-footer {
    position: fixed;
    left: 280px; /* Start after sidebar width */
    right: 0;
    bottom: 0;
    width: calc(100% - 280px); /* Full width minus sidebar */
    background-color: #ffffff;
    color: #333;
    text-align: center;
    border-top: 2px solid #940000;
    font-size: 13px;
    padding: 8px 0;
    margin: 0;
    z-index: 1030 !important; /* Below modals but above content */
    float: left;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    pointer-events: auto;
  }
  
  /* Ensure modals are always above footer */
  .modal {
    z-index: 1050 !important; /* Bootstrap default but ensure it's higher than footer */
  }
  
  .modal-backdrop {
    z-index: 1040 !important; /* Bootstrap default - below modal but above footer */
  }
  
  /* Ensure modal dialogs are above footer */
  .modal-dialog {
    z-index: 1050 !important;
  }
  
  /* Ensure modal content is above footer */
  .modal-content {
    z-index: 1051 !important;
  }
  
  /* SweetAlert2 modals should also be above footer */
  .swal2-container {
    z-index: 1055 !important;
  }
  
  .swal2-popup {
    z-index: 1056 !important;
  }
  
  /* Ensure widgets/charts don't overlap footer */
  canvas,
  .flot-container,
  .chart-container,
  [id*="chart"],
  [id*="Chart"],
  [id*="graph"],
  [id*="Graph"],
  .widget,
  .chart-wrapper {
    position: relative !important;
    z-index: 1 !important;
    margin-bottom: 60px !important; /* Add space at bottom for footer */
  }
  
  /* Ensure content containers have padding bottom to avoid footer overlap */
  .right-panel,
  .content-wrapper,
  .main-content,
  .container-fluid {
    padding-bottom: 60px !important;
  }

  /* Ensure footer is below sidebar - sidebar should be above */
  aside.left-panel {
    position: relative;
    z-index: 1000 !important;
  }

  /* Ensure right-panel content is also above footer */
  .right-panel {
    position: relative;
    /* z-index: 1000; */
  }

  /* When sidebar is collapsed (open class) */
  .open aside.left-panel {
    width: 70px;
  }

  .open .site-footer {
    left: 70px;
    width: calc(100% - 70px);
  }

  /* On mobile when sidebar is collapsed/absolute */
  @media (max-width: 991px) {
    aside.left-panel {
      z-index: 1001 !important;
    }
    .site-footer {
      left: 0;
      width: 100%;
      z-index: 1030;
    }

    .open .site-footer {
      left: 70px;
      width: calc(100% - 70px);
    }
  }


  .site-footer a {
    color: #940000;
    text-decoration: none;
    transition: color 0.3s;
    font-weight: 600;
  }

  .site-footer a:hover {
    text-decoration: underline;
    color: #b30000;
  }

  .site-footer strong {
    color: #940000;
  }

  @media (max-width: 576px) {
    body { padding-bottom: 45px; }
    .site-footer {
      font-size: 12px;
      padding: 6px 5px;
    }
  }
</style>

<!-- Universal Modal Close Handler - Fixes close button issues for all modals -->
<script>
(function() {
    'use strict';
    
    // Function to close modal - works with both Bootstrap 4 and 5
    function closeModal(modalElement) {
        if (!modalElement) return;
        
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                var modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                    return;
                } else {
                    var newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                    return;
                }
            } catch (e) {
                // Continue to next method
            }
        }
        
        // Try Bootstrap 4 / jQuery
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            try {
                jQuery(modalElement).modal('hide');
                return;
            } catch (e) {
                // Continue to manual fallback
            }
        }
        
        // Manual fallback - ensure modal closes
        try {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.removeAttribute('aria-modal');
            
            // Clean up body classes and styles
            document.body.classList.remove('modal-open');
            var bodyStyle = window.getComputedStyle(document.body);
            if (!bodyStyle.overflow || bodyStyle.overflow === 'hidden') {
                document.body.style.overflow = '';
            }
            document.body.style.paddingRight = '';
            
            // Remove all backdrops
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(backdrop) {
                backdrop.remove();
            });
            
            // Also remove any backdrop that might be a child of modal
            var modalBackdrops = modalElement.querySelectorAll('.modal-backdrop');
            modalBackdrops.forEach(function(backdrop) {
                backdrop.remove();
            });
        } catch (e) {
            console.warn('Manual modal close failed:', e);
        }
    }
    
    // Handle all close button types
    function handleCloseClick(e) {
        var target = e.target;
        var closeBtn = null;
        
        // Check if clicked element is a close button or inside one
        var isCloseButton = function(el) {
            if (!el) return false;
            return el.matches && (
                el.matches('[data-bs-dismiss="modal"]') ||
                el.matches('[data-dismiss="modal"]') ||
                el.matches('.btn-close') ||
                el.matches('.close') ||
                el.matches('button.close') ||
                (el.tagName === 'SPAN' && (el.textContent === '×' || el.textContent === '✕') && el.closest('.close'))
            );
        };
        
        // Find the actual close button
        if (isCloseButton(target)) {
            closeBtn = target;
        } else if (target.closest) {
            closeBtn = target.closest('[data-bs-dismiss="modal"]') ||
                      target.closest('[data-dismiss="modal"]') ||
                      target.closest('.btn-close') ||
                      target.closest('.close') ||
                      target.closest('button.close');
        }
        
        if (closeBtn) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Find the modal
            var modal = closeBtn.closest('.modal');
            if (!modal) {
                // Try to find modal by ID if close button has data-target
                var targetId = closeBtn.getAttribute('data-target') || 
                              closeBtn.getAttribute('data-bs-target') ||
                              closeBtn.getAttribute('href');
                if (targetId) {
                    // Remove # if present
                    targetId = targetId.replace('#', '');
                    modal = document.getElementById(targetId) || document.querySelector('[id="' + targetId + '"]');
                }
            }
            
            if (modal) {
                closeModal(modal);
            }
            return false;
        }
    }
    
    // Initialize handlers
    function initModalCloseHandlers() {
        // Remove any existing handlers to avoid duplicates
        document.removeEventListener('click', handleCloseClick, true);
        
        // Add new handler with capture phase
        document.addEventListener('click', handleCloseClick, true);
        
        // Also handle Escape key
        function handleEscape(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                var openModals = document.querySelectorAll('.modal.show, .modal[aria-hidden="false"], .modal:not([style*="display: none"])');
                if (openModals.length > 0) {
                    var lastModal = openModals[openModals.length - 1];
                    closeModal(lastModal);
                }
            }
        }
        
        document.removeEventListener('keydown', handleEscape);
        document.addEventListener('keydown', handleEscape);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModalCloseHandlers);
    } else {
        initModalCloseHandlers();
    }
    
    // Re-initialize after AJAX loads (for dynamic modals)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxComplete(function() {
            setTimeout(initModalCloseHandlers, 100);
        });
        
        // Also re-initialize after modals are shown dynamically
        jQuery(document).on('shown.bs.modal', '.modal', function() {
            setTimeout(initModalCloseHandlers, 50);
        });
    }
    
    // Also handle when modals are dynamically added
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (node.classList.contains('modal') || node.querySelector('.modal'))) {
                            shouldReinit = true;
                        }
                    });
                }
            });
            if (shouldReinit) {
                setTimeout(initModalCloseHandlers, 50);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
</script>

<footer class="site-footer">
  <div class="container">
    <p class="mb-1">
      &copy; 2026 <strong>ShuleXpert</strong>. All Rights Reserved.
    </p>
    <p class="small mb-0">
      Powered by: <a href="#">EmCa Techonologies LTD
      </a> |
      <a href="mailto:support@yoursite.com">Contact Support</a>
    </p>
  </div>
</footer>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;

    // Universal AJAX Form Handler for SGPM and other modules
    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();

        // Show loading state
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', response.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        if (Array.isArray(xhr.responseJSON.errors)) {
                            errorMsg = xhr.responseJSON.errors.join('<br>');
                        } else {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg
                });
            },
            complete: function() {
                $submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    // Confirmation for Delete buttons
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $form = $btn.closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If it's an AJAX form, trigger submit, otherwise submit directly
                if ($form.hasClass('ajax-form')) {
                    $form.submit();
                } else {
                    // Manually submit non-ajax forms via DOM method
                    $form[0].submit();
                }
            }
        });
    });
});
</script>

 <script src="{{ asset('assets/js/main.js') }}"></script>

 <script src="{{ asset('assets/js/widgets.js') }}"></script>
<!-- ======= Custom Footer End ======= -->
