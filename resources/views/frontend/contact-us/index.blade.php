@extends('frontend.layouts.index')

@section('title', 'SmartHotel - Complete Hotel Management Solution')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- ===================== HERO ===================== -->
<section class="sh-hero" style="padding:7rem 0 4.5rem">
  <div class="container text-center position-relative">
    <div class="hero-badge"><i class="bi bi-chat-dots"></i> Get In Touch</div>
    <h1>We're Here to Help</h1>
    <p class="lead mx-auto">Have a question or want to book a demo? Reach out to our team and we'll respond within 2 hours during business hours.</p>
  </div>
</section>

<!-- ===================== CONTACT SECTION ===================== -->
<section class="section-pad">
  <div class="container">
    <div class="row g-5">

      <!-- Contact Info -->
      <div class="col-lg-5">
        <h2 class="sh-section-title mb-2">Contact Information</h2>
        <p class="text-muted mb-4">Our hospitality experts are available Monday–Saturday, 9am–7pm IST.</p>

        <div class="d-flex flex-column gap-3">
          <div class="sh-contact-info-card">
            <div class="icon-wrap"><i class="bi bi-geo-alt-fill"></i></div>
            <div>
              <div class="info-title">Company Address</div>
              <div class="info-value">SmartHotel Technologies Pvt. Ltd.<br>42, Tech Park, Andheri East<br>Mumbai, Maharashtra – 400069</div>
            </div>
          </div>
          <div class="sh-contact-info-card">
            <div class="icon-wrap"><i class="bi bi-telephone-fill"></i></div>
            <div>
              <div class="info-title">Phone</div>
              <div class="info-value"><a href="tel:+911234567890">+91 12345 67890</a><br><small class="text-muted">Mon–Sat, 9am–7pm IST</small></div>
            </div>
          </div>
          <div class="sh-contact-info-card">
            <div class="icon-wrap"><i class="bi bi-envelope-fill"></i></div>
            <div>
              <div class="info-title">Support Email</div>
              <div class="info-value"><a href="mailto:support@smarthotel.com">support@smarthotel.com</a><br><small class="text-muted">Response within 24 hours</small></div>
            </div>
          </div>
          <div class="sh-contact-info-card">
            <div class="icon-wrap"><i class="bi bi-whatsapp"></i></div>
            <div>
              <div class="info-title">WhatsApp Support</div>
              <div class="info-value"><a href="https://wa.me/911234567890">+91 12345 67890</a><br><small class="text-muted">Chat with us instantly</small></div>
            </div>
          </div>
          <div class="sh-contact-info-card">
            <div class="icon-wrap"><i class="bi bi-clock-fill"></i></div>
            <div>
              <div class="info-title">Business Hours</div>
              <div class="info-value">
                Mon – Fri: 9:00 AM – 7:00 PM IST<br>
                Saturday: 10:00 AM – 5:00 PM IST<br>
                <small class="text-muted">Enterprise: 24/7 support</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="col-lg-7">
        <div class="sh-contact-form">
          <!-- Form -->
          <div id="contactFormWrap">
            <h3 class="fw-bold mb-1">Send Us a Message</h3>
            <p class="text-muted small mb-4">Fill in the form below and we'll be in touch soon.</p>
            <form id="shContactForm" novalidate>
              <div class="row g-3">
                <div class="col-sm-6">
                  <label class="form-label" for="fname">Full Name <span class="text-danger">*</span></label>
                  <input type="text" id="fname" name="name" class="form-control" placeholder="Rahul Sharma" required />
                  <div class="invalid-feedback">Please enter your name.</div>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="femail">Email Address <span class="text-danger">*</span></label>
                  <input type="email" id="femail" name="email" class="form-control" placeholder="rahul@hotel.com" required />
                  <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="fphone">Phone Number</label>
                  <input type="tel" id="fphone" name="phone" class="form-control" placeholder="+91 98765 43210" />
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="fcompany">Hotel / Company Name</label>
                  <input type="text" id="fcompany" name="company" class="form-control" placeholder="The Grand Palace Hotel" />
                </div>
                <div class="col-12">
                  <label class="form-label" for="fsubject">Subject <span class="text-danger">*</span></label>
                  <select id="fsubject" name="subject" class="form-select" required>
                    <option value="">Select a subject...</option>
                    <option>General Inquiry</option>
                    <option>Book a Demo</option>
                    <option>Technical Support</option>
                    <option>Billing &amp; Pricing</option>
                    <option>Partnership Opportunity</option>
                    <option>Other</option>
                  </select>
                  <div class="invalid-feedback">Please select a subject.</div>
                </div>
                <div class="col-12">
                  <label class="form-label" for="fmessage">Message <span class="text-danger">*</span></label>
                  <textarea id="fmessage" name="message" class="form-control" rows="5" placeholder="Tell us about your hotel and how SmartHotel can help you..." required></textarea>
                  <div class="invalid-feedback">Please enter your message.</div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn-sh-primary w-100 justify-content-center">
                    <i class="bi bi-send"></i> Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
          <!-- Success State -->
          <div id="formSuccess" class="sh-alert-success" style="display:none">
            <div class="success-icon"><i class="bi bi-check-lg"></i></div>
            <h4 class="fw-bold mb-2">Message Sent!</h4>
            <p class="text-muted mb-3">Thank you for reaching out. Our team will get back to you within 2 hours during business hours.</p>
            <button id="resetFormBtn" class="btn-sh-primary">Send Another Message</button>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ===================== MAP PLACEHOLDER ===================== -->
<section class="section-pad-sm bg-sh-light">
  <div class="container">
    <div class="sh-map-placeholder">
      <div class="map-inner">
        <div class="map-pin"><i class="bi bi-geo-alt-fill"></i></div>
        <h6 class="fw-bold mb-1">SmartHotel Technologies HQ</h6>
        <p class="text-muted small mb-2">42 Tech Park, Andheri East, Mumbai – 400069</p>
        <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold small text-decoration-none">Open in Google Maps <i class="bi bi-box-arrow-up-right"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ===================== SUPPORT FAQ ===================== -->
<section class="section-pad">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="sh-section-title">Support FAQ</h2>
      <p class="sh-section-sub">Quick answers to common support questions.</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="accordion sh-faq" id="supportFaq">
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sf1">How quickly do you respond to support requests?</button></h2>
            <div id="sf1" class="accordion-collapse collapse show" data-bs-parent="#supportFaq">
              <div class="accordion-body">Starter plan: within 48 hours by email. Professional plan: within 4 hours via email/chat. Enterprise: 24/7 phone and chat support.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sf2">Can I request a personalized demo?</button></h2>
            <div id="sf2" class="accordion-collapse collapse" data-bs-parent="#supportFaq">
              <div class="accordion-body">Absolutely! Fill out the contact form and select 'Book Demo'. Our team will reach out within 2 hours during business hours.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sf3">Do you offer on-site training for hotel staff?</button></h2>
            <div id="sf3" class="accordion-collapse collapse" data-bs-parent="#supportFaq">
              <div class="accordion-body">Yes, for Professional and Enterprise plans we offer on-site training. Starter plan users get access to our full video tutorial library and documentation.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sf4">What languages does your support team speak?</button></h2>
            <div id="sf4" class="accordion-collapse collapse" data-bs-parent="#supportFaq">
              <div class="accordion-body">Our support team speaks Hindi, English, Marathi, Tamil, and Telugu. We are expanding to more Indian languages in 2025.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- ✅ Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- ✅ jQuery + Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
  var contactForm = document.getElementById('shContactForm');
  var formSuccess = document.getElementById('formSuccess');

  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();

      let valid = true;

      // ✅ Validate required fields
      var required = contactForm.querySelectorAll('[required]');
      required.forEach(function(input) {
        if (!input.value.trim()) {
          input.classList.add('is-invalid');
          valid = false;
        } else {
          input.classList.remove('is-invalid');
        }
      });

      if (!valid) {
        toastr.error('Please fill all required fields');
        return;
      }

      // ✅ Disable button
      let btn = contactForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = 'Sending...';

      // ✅ Prepare form data
      let formData = new FormData(contactForm);

      // ✅ AJAX using fetch
      fetch("{{ route('contact.request') }}", {
          method: "POST",
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) throw response;
          return response.json();
        })
        .then(data => {

          toastr.success(data.message);

          // ✅ Reset form
          contactForm.reset();

          // ✅ Show success UI
          contactForm.style.display = 'none';
          if (formSuccess) formSuccess.style.display = 'block';

        })
        .catch(async (error) => {

          if (error.status === 422) {
            let err = await error.json();

            Object.keys(err.errors).forEach(function(key) {
              let field = contactForm.querySelector('[name="' + key + '"]');
              if (field) field.classList.add('is-invalid');

              toastr.error(err.errors[key][0]);
            });

          } else {
            toastr.error('Something went wrong. Please try again later.');
          }

        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-send"></i> Send Message';
        });
    });

    // ✅ Remove invalid class on typing
    contactForm.querySelectorAll('input, textarea, select').forEach(function(field) {
      field.addEventListener('input', function() {
        field.classList.remove('is-invalid');
      });
    });
  }

  // ✅ Reset button
  var resetBtn = document.getElementById('resetFormBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      if (contactForm) {
        contactForm.reset();
        contactForm.style.display = '';
      }
      if (formSuccess) formSuccess.style.display = 'none';
    });
  }
</script>
@endsection