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
    z-index: 999;
    float: left;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    pointer-events: auto;
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
      z-index: 1000;
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
      &copy; 2025 <strong>ShuleXpert</strong>. All Rights Reserved.
    </p>
    <p class="small mb-0">
      Designed by <a href="#">Emca Techonology
      </a> |
      <a href="mailto:support@yoursite.com">Contact Support</a>
    </p>
  </div>
</footer>
<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
 <script src="{{ asset('vendors/popper.js/dist/umd/popper.min.js') }}"></script>
 <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
 <script src="{{ asset('assets/js/main.js') }}"></script>
 <script src="{{ asset('assets/js/widgets.js') }}"></script>
<!-- ======= Custom Footer End ======= -->
