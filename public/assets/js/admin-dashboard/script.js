/* =========================================
   SmartHotel Admin Dashboard - JavaScript
   ========================================= */

(function () {
  'use strict';

  /* ---- Current date display ---- */
  function setCurrentDate() {
    var el = document.getElementById('currentDate');
    if (!el) return;
    var now = new Date();
    var opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    el.textContent = now.toLocaleDateString('en-IN', opts);
  }

  setCurrentDate();

  /* ---- Sidebar toggle ---- */
  var sidebar    = document.getElementById('sidebar');
  var overlay    = document.getElementById('sidebarOverlay');
  var toggleBtn  = document.getElementById('sidebarToggle');
  var closeBtn   = document.getElementById('sidebarClose');

  function openSidebar() {
    if (sidebar)  sidebar.classList.add('open');
    if (overlay)  overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (sidebar)  sidebar.classList.remove('open');
    if (overlay)  overlay.classList.remove('show');
    document.body.style.overflow = '';
  }

  if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
  if (closeBtn)  closeBtn.addEventListener('click',  closeSidebar);
  if (overlay)   overlay.addEventListener('click',   closeSidebar);

  /* ---- Sidebar nav active link ---- */
  document.querySelectorAll('.sidebar-item[data-page]').forEach(function (item) {
    item.addEventListener('click', function () {
      document.querySelectorAll('.sidebar-item').forEach(function (i) { i.classList.remove('active'); });
      this.classList.add('active');
      /* Close sidebar on mobile after nav */
      if (window.innerWidth < 992) closeSidebar();
    });
  });

  /* ---- Notification dropdown dismiss ---- */
  var notifBtn = document.getElementById('notifBtn');
  if (notifBtn) {
    notifBtn.addEventListener('click', function () {
      var dot = this.querySelector('.notif-dot');
      if (dot) {
        setTimeout(function () { if (dot) dot.style.display = 'none'; }, 800);
      }
    });
  }

  /* ---- Booking modal: populate on open ---- */
  var bookingModal = document.getElementById('bookingModal');
  if (bookingModal) {
    bookingModal.addEventListener('show.bs.modal', function (e) {
      var btn = e.relatedTarget;
      if (!btn) return;
      var name    = btn.getAttribute('data-name')    || '';
      var room    = btn.getAttribute('data-room')    || '';
      var checkin = btn.getAttribute('data-checkin') || '';
      var checkout= btn.getAttribute('data-checkout')|| '';
      var status  = btn.getAttribute('data-status')  || '';
      var amount  = btn.getAttribute('data-amount')  || '';

      document.getElementById('modal-guest-name').textContent    = name;
      document.getElementById('modal-room').textContent          = room;
      document.getElementById('modal-checkin').textContent       = checkin;
      document.getElementById('modal-checkout').textContent      = checkout;
      document.getElementById('modal-amount').textContent        = amount;
      document.getElementById('modal-status').textContent        = status;
      document.getElementById('modal-status').className          = 'badge-status ' + status.toLowerCase().replace(/\s+/, '-');
    });
  }

  /* ---- New Booking form submit ---- */
  var newBookingForm = document.getElementById('newBookingForm');
  if (newBookingForm) {
    newBookingForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var modal = bootstrap.Modal.getInstance(document.getElementById('newBookingModal'));
      if (modal) modal.hide();
      showToast('Booking created successfully!', 'success');
      newBookingForm.reset();
    });
  }

  /* ---- Toast ---- */
  function showToast(msg, type) {
    var toastEl = document.getElementById('mainToast');
    var toastMsg = document.getElementById('toastMsg');
    var toastIcon = document.getElementById('toastIcon');
    if (!toastEl || !toastMsg) return;

    toastMsg.textContent = msg;
    if (toastIcon) {
      toastIcon.className = 'bi me-2 ' + (type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger');
    }

    var toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
  }

  /* ---- Revenue bar chart interaction ---- */
  document.querySelectorAll('.rev-bar').forEach(function (bar) {
    bar.addEventListener('mouseenter', function () {
      var val = this.getAttribute('data-val');
      var tip = document.getElementById('revTooltip');
      if (tip && val) tip.textContent = '₹' + val;
    });
  });

  /* ---- Search filter on tables ---- */
  function setupTableSearch(inputId, tableId) {
    var input = document.getElementById(inputId);
    var table = document.getElementById(tableId);
    if (!input || !table) return;
    input.addEventListener('input', function () {
      var q = this.value.trim().toLowerCase();
      Array.from(table.querySelectorAll('tbody tr')).forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  setupTableSearch('bookingSearch', 'bookingsTable');
  setupTableSearch('customerSearch', 'customersTable');

  /* ---- Room status filter ---- */
  document.querySelectorAll('[data-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var parent = this.closest('[data-filter-group]');
      if (parent) {
        parent.querySelectorAll('[data-filter]').forEach(function (b) { b.classList.remove('active', 'btn-primary', 'btn-sh-primary'); b.classList.add('btn-sh-ghost'); });
      }
      this.classList.remove('btn-sh-ghost');
      this.classList.add('active', 'btn-sh-primary');

      var filter = this.getAttribute('data-filter');
      var target = this.getAttribute('data-target');
      var table = document.getElementById(target);
      if (!table) return;

      Array.from(table.querySelectorAll('tbody tr')).forEach(function (row) {
        if (filter === 'all') {
          row.style.display = '';
        } else {
          var badge = row.querySelector('.badge-status');
          row.style.display = (badge && badge.classList.contains(filter)) ? '' : 'none';
        }
      });
    });
  });

  /* ---- Refresh data button animation ---- */
  document.querySelectorAll('[data-refresh]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var icon = this.querySelector('i');
      if (!icon) return;
      icon.style.transition = 'transform 0.5s';
      icon.style.transform = 'rotate(360deg)';
      setTimeout(function () {
        icon.style.transition = '';
        icon.style.transform = '';
      }, 600);
      showToast('Data refreshed!', 'success');
    });
  });

  /* ---- Window resize: close sidebar ---- */
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 992) closeSidebar();
  });

})();
