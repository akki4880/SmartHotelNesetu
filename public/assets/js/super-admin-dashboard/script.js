/* =========================================
   SmartHotel Super Admin Dashboard — JS
   ========================================= */
(function () {
  'use strict';

  /* ── Date ── */
  var dateEl = document.getElementById('saDate');
  if (dateEl) {
    var now = new Date();
    dateEl.textContent = now.toLocaleDateString('en-IN', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
  }

  /* ── Sidebar ── */
  var sidebar   = document.getElementById('saSidebar');
  var overlay   = document.getElementById('saOverlay');
  var toggleBtn = document.getElementById('saToggle');
  var closeBtn  = document.getElementById('saClose');

  function openSidebar()  { sidebar && sidebar.classList.add('open'); overlay && overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
  function closeSidebar() { sidebar && sidebar.classList.remove('open'); overlay && overlay.classList.remove('show'); document.body.style.overflow = ''; }

  if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
  if (closeBtn)  closeBtn.addEventListener('click',  closeSidebar);
  if (overlay)   overlay.addEventListener('click',   closeSidebar);
  window.addEventListener('resize', function () { if (window.innerWidth >= 992) closeSidebar(); });

  /* ── Nav items ── */
  document.querySelectorAll('.s-item[data-page]').forEach(function (item) {
    item.addEventListener('click', function () {
      document.querySelectorAll('.s-item').forEach(function (i) { i.classList.remove('active'); });
      this.classList.add('active');
      if (window.innerWidth < 992) closeSidebar();
    });
  });

  /* ── Notification dot clear ── */
  var nBtn = document.getElementById('notifBtn');
  if (nBtn) nBtn.addEventListener('click', function () {
    var dot = this.querySelector('.ni-dot');
    setTimeout(function () { if (dot) dot.style.display = 'none'; }, 800);
  });

  /* ── Table search ── */
  function setupSearch(inputId, tableId) {
    var inp = document.getElementById(inputId);
    var tbl = document.getElementById(tableId);
    if (!inp || !tbl) return;
    inp.addEventListener('input', function () {
      var q = this.value.trim().toLowerCase();
      Array.from(tbl.querySelectorAll('tbody tr')).forEach(function (r) {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
  setupSearch('hotelSearch', 'hotelsTable');
  setupSearch('paySearch',   'paymentsTable');

  /* ── Status filter ── */
  document.querySelectorAll('[data-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var grp = this.closest('[data-group]');
      if (grp) grp.querySelectorAll('[data-filter]').forEach(function (b) { b.classList.remove('btn-sa-primary','active'); b.classList.add('btn-sa-ghost'); });
      this.classList.remove('btn-sa-ghost'); this.classList.add('btn-sa-primary','active');
      var f = this.getAttribute('data-filter');
      var t = document.getElementById(this.getAttribute('data-target'));
      if (!t) return;
      Array.from(t.querySelectorAll('tbody tr')).forEach(function (r) {
        if (f === 'all') { r.style.display = ''; return; }
        var badge = r.querySelector('.b-status');
        r.style.display = (badge && badge.classList.contains(f)) ? '' : 'none';
      });
    });
  });

  /* ── Refresh ── */
  document.querySelectorAll('[data-refresh]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var ic = this.querySelector('i');
      if (ic) { ic.style.transition = 'transform .5s'; ic.style.transform = 'rotate(360deg)'; setTimeout(function () { ic.style.transition = ''; ic.style.transform = ''; }, 600); }
      showToast('Data refreshed successfully!', 'success');
    });
  });

  /* ── Add Hotel form ── */
  var addHotelForm = document.getElementById('addHotelForm');
  if (addHotelForm) {
    addHotelForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var modal = bootstrap.Modal.getInstance(document.getElementById('addHotelModal'));
      if (modal) modal.hide();
      showToast('Hotel added successfully!', 'success');
      this.reset();
    });
  }

  /* ── Hotel detail modal ── */
  var hotelDetailModal = document.getElementById('hotelDetailModal');
  if (hotelDetailModal) {
    hotelDetailModal.addEventListener('show.bs.modal', function (e) {
      var btn = e.relatedTarget;
      if (!btn) return;
      ['name','owner','plan','rooms','status','city'].forEach(function (k) {
        var el = document.getElementById('hd-' + k);
        if (el) el.textContent = btn.getAttribute('data-' + k) || '—';
      });
    });
  }

  /* ── Toast ── */
  function showToast(msg, type) {
    var el = document.getElementById('saToast');
    var msgEl = document.getElementById('saToastMsg');
    var icEl  = document.getElementById('saToastIc');
    if (!el || !msgEl) return;
    msgEl.textContent = msg;
    if (icEl) icEl.className = 'bi me-2 ' + (type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger');
    new bootstrap.Toast(el, { delay: 3500 }).show();
  }

  /* ── Chart bar hover label ── */
  document.querySelectorAll('.chart-bar').forEach(function (bar) {
    bar.title = bar.getAttribute('data-label') || '';
  });

  /* ── Animate KPI numbers on load ── */
  function animateCount(el) {
    var raw = el.getAttribute('data-count');
    if (!raw) return;
    var isRupee = raw.startsWith('₹');
    var isMixed = raw.includes('L') || raw.includes('K') || raw.includes('%');
    if (isMixed) { el.textContent = raw; return; }
    var target = parseFloat(raw.replace(/[^\d.]/g, ''));
    var prefix = isRupee ? '₹' : '';
    var suffix = raw.includes('%') ? '%' : '';
    var start = 0; var duration = 900; var step = (target / duration) * 16;
    var interval = setInterval(function () {
      start += step;
      if (start >= target) { start = target; clearInterval(interval); }
      el.textContent = prefix + Math.floor(start).toLocaleString('en-IN') + suffix;
    }, 16);
  }

  document.querySelectorAll('.kpi-val[data-count]').forEach(animateCount);

})();
