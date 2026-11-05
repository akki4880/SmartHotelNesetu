/* =========================================
   SmartHotel - Main JavaScript
   ========================================= */

(function () {
  'use strict';

  /* ---- Navbar scroll effect ---- */
  var navbar = document.getElementById('shNavbar');
  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 30) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }

  /* ---- Active nav link ---- */
  (function setActiveNav() {
    var path = window.location.pathname;
    var filename = path.split('/').pop() || 'index.html';
    var navLinks = document.querySelectorAll('.sh-navbar .nav-link');
    navLinks.forEach(function (link) {
      var href = link.getAttribute('href');
      if (
        href === filename ||
        (filename === '' && href === 'index.html') ||
        (filename === 'index.html' && href === 'index.html')
      ) {
        link.classList.add('active');
      }
    });
  })();

  /* ---- Pricing toggle (Pricing page) ---- */
  var monthlyBtn = document.getElementById('monthlyBtn');
  var yearlyBtn = document.getElementById('yearlyBtn');
  var monthlyPrices = document.querySelectorAll('.price-monthly');
  var yearlyPrices = document.querySelectorAll('.price-yearly');
  var yearlySave = document.querySelectorAll('.yearly-save');

  function showMonthly() {
    monthlyPrices.forEach(function (el) { el.style.display = ''; });
    yearlyPrices.forEach(function (el) { el.style.display = 'none'; });
    yearlySave.forEach(function (el) { el.style.display = 'none'; });
    if (monthlyBtn) { monthlyBtn.classList.add('active'); }
    if (yearlyBtn) { yearlyBtn.classList.remove('active'); }
  }

  function showYearly() {
    monthlyPrices.forEach(function (el) { el.style.display = 'none'; });
    yearlyPrices.forEach(function (el) { el.style.display = ''; });
    yearlySave.forEach(function (el) { el.style.display = 'inline'; });
    if (yearlyBtn) { yearlyBtn.classList.add('active'); }
    if (monthlyBtn) { monthlyBtn.classList.remove('active'); }
  }

  if (monthlyBtn) { monthlyBtn.addEventListener('click', showMonthly); }
  if (yearlyBtn) { yearlyBtn.addEventListener('click', showYearly); }

  /* Initialise yearly prices as hidden */
  if (yearlyPrices.length) {
    yearlyPrices.forEach(function (el) { el.style.display = 'none'; });
    yearlySave.forEach(function (el) { el.style.display = 'none'; });
  }

  /* ---- Contact form submit ---- */
  // var contactForm = document.getElementById('shContactForm');
  // var formSuccess = document.getElementById('formSuccess');

  // if (contactForm) {
  //   contactForm.addEventListener('submit', function (e) {
  //     e.preventDefault();
  //     /* Basic validation */
  //     var valid = true;
  //     var required = contactForm.querySelectorAll('[required]');
  //     required.forEach(function (input) {
  //       if (!input.value.trim()) {
  //         input.classList.add('is-invalid');
  //         valid = false;
  //       } else {
  //         input.classList.remove('is-invalid');
  //       }
  //     });
  //     if (!valid) return;

  //     /* Show success */
  //     contactForm.style.display = 'none';
  //     if (formSuccess) { formSuccess.style.display = 'block'; }
  //   });

  //   /* Remove invalid class on input */
  //   contactForm.querySelectorAll('input, textarea, select').forEach(function (field) {
  //     field.addEventListener('input', function () {
  //       field.classList.remove('is-invalid');
  //     });
  //   });
  // }

  // /* Reset form button */
  // var resetBtn = document.getElementById('resetFormBtn');
  // if (resetBtn) {
  //   resetBtn.addEventListener('click', function () {
  //     if (contactForm) {
  //       contactForm.reset();
  //       contactForm.style.display = '';
  //     }
  //     if (formSuccess) { formSuccess.style.display = 'none'; }
  //   });
  // }

  /* ---- Smooth scroll for anchor links ---- */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ---- Animate elements on scroll (simple Intersection Observer) ---- */
  if ('IntersectionObserver' in window) {
    var fadeEls = document.querySelectorAll('.fade-up');
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    fadeEls.forEach(function (el) { observer.observe(el); });
  }

})();
