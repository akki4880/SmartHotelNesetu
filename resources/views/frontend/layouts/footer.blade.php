<!-- ===================== FOOTER ===================== -->
  <footer class="sh-footer">
    <div class="container">
      <div class="row g-5">
        <!-- Brand -->
        <div class="col-lg-4">
          <div class="footer-brand d-flex align-items-center gap-2 mb-3">
            <div class="brand-icon ms-3"><img src="{{asset('assets/img/logo.png')}}" style="width: 60px;" alt="" srcset=""></div>
            <!-- <span class="footer-brand-name">Smart<span>Hotel</span></span> -->
          </div>
          <p class="footer-desc">The complete hotel management software trusted by 2,000+ properties worldwide. Manage bookings, staff, billing and more.</p>
          <div class="social-links"> 
            <a href="#" class="social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="#" class="social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a> 
          </div>
        </div> 
        <div class="col-lg-5"></div>
        <!-- Policies -->
        <div class="col-6 col-lg-3">
          <h6>Policies</h6>
          <ul class="footer-links">
            <li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
            <li><a href="{{ route('terms-and-conditions') }}">Terms &amp; Conditions</a></li>
            <li><a href="{{ route('data-protection-policy') }}">Data Protection Policy</a></li>
            <li><a href="{{ route('cancellation-refund-policy') }}">Cancellation &amp; Refund Policy</a></li>
            <li><a href="{{ route('disclaimer') }}">Disclaimer</a></li>
            <li><a href="{{ route('cookie-policy') }}">Cookie Policy</a></li>
          </ul>
        </div> 
      </div>
    </div>
    <div class="footer-bottom">
      <div class="text-center">
        <p style="color:rgba(255,255,255,.35)" class="mb-0">&copy; 2026 SmartHotel Powered by Netsetu.</p>
      </div>
    </div>
  </footer>

  <!-- ===================== FLOATING DEMO BUTTON ===================== -->
  <a href="tel:+918369911029" class="sh-float-btn" title="Call: +91 83699 11029" aria-label="Call SmartHotel">
    <i class="bi bi-telephone-fill"></i>
  </a>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{asset('frontend/assets/js/script.js')}}"></script>