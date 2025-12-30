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

<footer class="site-footer">
  <div class="container">
    <p class="mb-1">
      &copy; 2025 <strong>ShuleLink</strong>. All Rights Reserved.
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
