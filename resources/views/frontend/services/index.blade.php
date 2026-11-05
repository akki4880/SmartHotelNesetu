@extends('frontend.layouts.index')

@section('title', 'SmartHotel - Complete Hotel Management Solution') 

@section('content')
 <!-- ===================== HERO ===================== -->
  <section class="sh-hero" style="padding:7rem 0 4.5rem">
    <div class="container text-center position-relative">
      <div class="hero-badge"><i class="bi bi-grid-3x3-gap"></i> Our Services</div>
      <h1>Everything Your Hotel Needs<br><span class="gradient-text">In One Platform</span></h1>
      <p class="lead mx-auto">From front desk to back office — SmartHotel covers every department with purpose-built tools that work together seamlessly.</p>
    </div>
  </section>

  <!-- ===================== SERVICES LIST ===================== -->
  <section class="section-pad">
    <div class="container">

      <!-- Service 1: Hotel Booking Management -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(59,130,246,.1);color:#3b82f6"><i class="bi bi-door-open-fill"></i></div>
            <h3 class="fw-bold mb-3">Hotel Booking Management</h3>
            <p class="text-muted">Complete end-to-end reservation management with real-time availability, multi-channel bookings, group reservations, and automated confirmation emails.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(59,130,246,.1);color:#3b82f6"><i class="bi bi-door-open"></i> Booking Management</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Real-time room availability calendar</li>
                <li><i class="bi bi-check-circle-fill"></i> Direct website booking engine</li>
                <li><i class="bi bi-check-circle-fill"></i> Group &amp; corporate bookings</li>
                <li><i class="bi bi-check-circle-fill"></i> Waitlist and overbooking protection</li>
                <li><i class="bi bi-check-circle-fill"></i> Automated email confirmations</li>
                <li><i class="bi bi-check-circle-fill"></i> Channel manager (50+ OTAs)</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 2: Front Desk Automation -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center flex-lg-row-reverse">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(168,85,247,.1);color:#a855f7"><i class="bi bi-display-fill"></i></div>
            <h3 class="fw-bold mb-3">Front Desk Automation</h3>
            <p class="text-muted">Speed up check-in and check-out with digital guest registration, ID verification, key card integration, and a paperless front desk workflow.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(168,85,247,.1);color:#a855f7"><i class="bi bi-display"></i> Front Desk</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Digital guest check-in/check-out</li>
                <li><i class="bi bi-check-circle-fill"></i> ID scanning and verification</li>
                <li><i class="bi bi-check-circle-fill"></i> Key card system integration</li>
                <li><i class="bi bi-check-circle-fill"></i> Guest folio management</li>
                <li><i class="bi bi-check-circle-fill"></i> Walk-in reservation handling</li>
                <li><i class="bi bi-check-circle-fill"></i> VIP guest flagging</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 3: Invoice System -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-receipt-cutoff"></i></div>
            <h3 class="fw-bold mb-3">Invoice &amp; Billing System</h3>
            <p class="text-muted">Professional, GST-compliant invoice templates with automated billing, split bills, corporate account management, and one-click invoice sharing.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-receipt"></i> Invoice System</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> GST-compliant invoice generation</li>
                <li><i class="bi bi-check-circle-fill"></i> Multiple payment method tracking</li>
                <li><i class="bi bi-check-circle-fill"></i> Split bill functionality</li>
                <li><i class="bi bi-check-circle-fill"></i> Corporate billing accounts</li>
                <li><i class="bi bi-check-circle-fill"></i> Automatic late fee calculation</li>
                <li><i class="bi bi-check-circle-fill"></i> PDF export and email delivery</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 4: Employee Attendance -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center flex-lg-row-reverse">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-person-check-fill"></i></div>
            <h3 class="fw-bold mb-3">Employee Attendance</h3>
            <p class="text-muted">Biometric and digital attendance tracking with geo-fencing, shift management, overtime calculation, and leave balance management.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-person-check"></i> Attendance</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Biometric device integration</li>
                <li><i class="bi bi-check-circle-fill"></i> Mobile app punch in/out</li>
                <li><i class="bi bi-check-circle-fill"></i> Geo-fencing for remote check</li>
                <li><i class="bi bi-check-circle-fill"></i> Real-time attendance dashboard</li>
                <li><i class="bi bi-check-circle-fill"></i> Leave application &amp; approval</li>
                <li><i class="bi bi-check-circle-fill"></i> Overtime auto-calculation</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 5: Payroll Management -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(20,184,166,.1);color:#14b8a6"><i class="bi bi-currency-rupee"></i></div>
            <h3 class="fw-bold mb-3">Payroll Management</h3>
            <p class="text-muted">Automated salary calculation with configurable allowances, deductions, PF/ESIC compliance, and one-click payslip generation for all staff.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(20,184,166,.1);color:#14b8a6"><i class="bi bi-cash-stack"></i> Payroll</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Auto salary computation</li>
                <li><i class="bi bi-check-circle-fill"></i> PF, ESIC &amp; TDS compliance</li>
                <li><i class="bi bi-check-circle-fill"></i> Bank transfer integration</li>
                <li><i class="bi bi-check-circle-fill"></i> Payslip generation (PDF/SMS)</li>
                <li><i class="bi bi-check-circle-fill"></i> Bonus &amp; incentive management</li>
                <li><i class="bi bi-check-circle-fill"></i> Annual increment tracking</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 6: Staff Scheduling -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center flex-lg-row-reverse">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-calendar3-range-fill"></i></div>
            <h3 class="fw-bold mb-3">Staff Scheduling</h3>
            <p class="text-muted">Drag-and-drop shift scheduling, automatic conflict detection, cross-department coordination, and staff availability tracking.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-calendar3"></i> Scheduling</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Visual drag-and-drop scheduler</li>
                <li><i class="bi bi-check-circle-fill"></i> Conflict &amp; overlap detection</li>
                <li><i class="bi bi-check-circle-fill"></i> Multi-department coordination</li>
                <li><i class="bi bi-check-circle-fill"></i> Shift swap requests</li>
                <li><i class="bi bi-check-circle-fill"></i> WhatsApp schedule notifications</li>
                <li><i class="bi bi-check-circle-fill"></i> Holiday &amp; festival planning</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 7: Reports & Analytics -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(236,72,153,.1);color:#ec4899"><i class="bi bi-graph-up-arrow"></i></div>
            <h3 class="fw-bold mb-3">Reports &amp; Analytics</h3>
            <p class="text-muted">Comprehensive reporting with occupancy trends, revenue analysis, staff performance metrics, and exportable data for strategic decision-making.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(236,72,153,.1);color:#ec4899"><i class="bi bi-bar-chart-line"></i> Analytics</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Daily, weekly &amp; monthly reports</li>
                <li><i class="bi bi-check-circle-fill"></i> Occupancy &amp; RevPAR tracking</li>
                <li><i class="bi bi-check-circle-fill"></i> Revenue breakdown by source</li>
                <li><i class="bi bi-check-circle-fill"></i> Staff performance analytics</li>
                <li><i class="bi bi-check-circle-fill"></i> Custom report builder</li>
                <li><i class="bi bi-check-circle-fill"></i> Excel &amp; PDF export</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Service 8: Multi-Branch Management -->
      <div class="sh-service-block">
        <div class="row g-5 align-items-center flex-lg-row-reverse">
          <div class="col-lg-6">
            <div class="service-icon-wrap" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="bi bi-buildings-fill"></i></div>
            <h3 class="fw-bold mb-3">Multi-Branch Management</h3>
            <p class="text-muted">Centralized control of multiple hotel properties from a single dashboard with unified reporting, staff transfer, and standardized operations.</p>
            <a href="/contact-us" class="text-primary fw-semibold text-decoration-none">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-lg-6">
            <div class="p-4 rounded-4 bg-sh-light border">
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 small fw-bold" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="bi bi-buildings"></i> Multi-Branch</div>
              <ul class="sh-service-block feature-checklist">
                <li><i class="bi bi-check-circle-fill"></i> Unified multi-property dashboard</li>
                <li><i class="bi bi-check-circle-fill"></i> Centralized guest database</li>
                <li><i class="bi bi-check-circle-fill"></i> Cross-property staff transfer</li>
                <li><i class="bi bi-check-circle-fill"></i> Consolidated financial reports</li>
                <li><i class="bi bi-check-circle-fill"></i> Standardized policy templates</li>
                <li><i class="bi bi-check-circle-fill"></i> Branch-wise performance comparison</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ===================== INTEGRATIONS ===================== -->
  <section class="section-pad-sm bg-sh-light">
    <div class="container text-center">
      <h5 class="fw-bold mb-4">Integrates With Your Favorite Tools</h5>
      <div class="d-flex flex-wrap justify-content-center gap-3">
        <div class="d-flex align-items-center gap-2 bg-white border rounded-3 px-4 py-2"><i class="bi bi-wifi text-primary"></i><span class="small fw-semibold">OTA Channels</span></div>
        <div class="d-flex align-items-center gap-2 bg-white border rounded-3 px-4 py-2"><i class="bi bi-credit-card text-primary"></i><span class="small fw-semibold">Payment Gateways</span></div>
        <div class="d-flex align-items-center gap-2 bg-white border rounded-3 px-4 py-2"><i class="bi bi-bell text-primary"></i><span class="small fw-semibold">POS Systems</span></div>
        <div class="d-flex align-items-center gap-2 bg-white border rounded-3 px-4 py-2"><i class="bi bi-whatsapp text-primary"></i><span class="small fw-semibold">WhatsApp API</span></div>
        <div class="d-flex align-items-center gap-2 bg-white border rounded-3 px-4 py-2"><span class="small text-muted">+ 46 more integrations</span></div>
      </div>
    </div>
  </section>

  <!-- ===================== CTA ===================== -->
  <section class="sh-cta-section section-pad">
    <div class="container text-center position-relative">
      <h2 class="sh-section-title text-white mb-3">Ready to Upgrade Your Hotel Operations?</h2>
      <p style="color:rgba(255,255,255,.7);margin-bottom:2rem">Get a personalized demo and see how SmartHotel fits your property's needs.</p>
      <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
        <a href="/contact-us" class="btn-sh-white">Book a Free Demo</a>
        <a href="/pricing" class="btn-sh-outline">View Pricing</a>
      </div>
    </div>
  </section>
@endsection 