<!-- ===================== FOOTER ===================== -->
  <footer class="sh-footer">
    <div class="container">
      <div class="row g-5">
        <!-- Brand -->
        <div class="col-lg-4">
          <div class="footer-brand d-flex align-items-center gap-2 mb-3">
            <div class="brand-icon ms-3"><img src="{{asset('assets/img/logo.png')}}" style="width: 100px;" alt="" srcset=""></div>
            <!-- <span class="footer-brand-name">Smart<span>Hotel</span></span> -->
          </div>
          <p class="footer-desc">The complete hotel management software trusted by 2,000+ properties worldwide. Manage bookings, staff, billing and more.</p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-link" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="#" class="social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
          </div>
        </div>
        <!-- Quick Links -->
        <div class="col-6 col-lg-2">
          <h6>Quick Links</h6>
          <ul class="footer-links">
            <li><a href="/">Home</a></li>
            <li><a href="/about-us">About Us</a></li>
            <li><a href="/services">Services</a></li>
            <li><a href="/Pricing">Pricing</a></li>
            <li><a href="/contact-us">Contact</a></li>
          </ul>
        </div>
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
        <!-- Contact -->
        <div class="col-lg-3">
          <h6>Contact</h6>
          <div class="footer-contact-item">
            <i class="bi bi-geo-alt-fill"></i>
            <span>42 Tech Park, Andheri East,<br>Mumbai, MH 400069</span>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-telephone-fill"></i>
            <a href="tel:+919819494880">+91 9819494880</a>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-envelope-fill"></i>
            <a href="mailto:support@smarthotel.com">support@smarthotel.com</a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <p style="color:rgba(255,255,255,.35)" class="mb-0">&copy; 2025 SmartHotel Technologies Pvt. Ltd. All rights reserved.</p>
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