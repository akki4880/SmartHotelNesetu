@extends('frontend.layouts.index')

@section('title', 'SmartHotel - Complete Hotel Management Solution') 

@section('content')
 <!-- ===================== HERO ===================== -->
  <section class="sh-hero" style="padding:7rem 0 4.5rem">
    <div class="container text-center position-relative">
      <div class="hero-badge"><i class="bi bi-building"></i> About SmartHotel</div>
      <h1>Redefining Hotel Management<br><span class="gradient-text">Since 2009</span></h1>
      <p class="lead mx-auto">We are a team of hospitality technology experts passionate about helping hotels of every size operate smarter, serve better, and grow faster.</p>
    </div>
  </section>

  <!-- ===================== COMPANY INTRO ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <div class="sh-section-tag">Our Story</div>
          <h2 class="sh-section-title">Built by Hoteliers, for Hoteliers</h2>
          <p class="text-muted mt-3">SmartHotel was founded in 2009 by Arjun Mehta and Sunita Rao — two professionals who had spent over a decade working inside the hospitality industry. Frustrated by the complexity, cost, and poor user experience of existing hotel management systems, they set out to build something better.</p>
          <p class="text-muted">Today, SmartHotel is India's fastest-growing hotel management platform, trusted by independent boutique hotels, luxury resorts, and multi-branch chains across 18 states.</p>
          <p class="text-muted">Our platform handles everything from room reservations and guest check-ins to staff payroll and restaurant billing — giving hotel owners complete visibility and control from a single, easy-to-use dashboard.</p>
          <a href="/contact-us" class="btn-sh-primary mt-4"><i class="bi bi-rocket-takeoff"></i> Get Started Today</a>
        </div>
        <div class="col-lg-6">
          <div class="row g-3">
            <div class="col-6">
              <div class="sh-stat-card">
                <div class="stat-icon"><i class="bi bi-globe2"></i></div>
                <div class="stat-value">2,000+</div>
                <div class="stat-label">Hotel Properties</div>
              </div>
            </div>
            <div class="col-6">
              <div class="sh-stat-card">
                <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-value">85,000+</div>
                <div class="stat-label">Rooms Managed Daily</div>
              </div>
            </div>
            <div class="col-6">
              <div class="sh-stat-card">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-value">1.2M+</div>
                <div class="stat-label">Guests Served</div>
              </div>
            </div>
            <div class="col-6">
              <div class="sh-stat-card">
                <div class="stat-icon"><i class="bi bi-award"></i></div>
                <div class="stat-value">15+</div>
                <div class="stat-label">Years of Innovation</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== MISSION & VISION ===================== -->
  <section class="section-pad bg-sh-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="sh-section-title">Mission, Vision &amp; Values</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="sh-mission-card">
            <div class="icon-wrap"><i class="bi bi-bullseye"></i></div>
            <h4>Our Mission</h4>
            <p class="text-muted">To empower every hotel — from a 10-room guesthouse to a 500-room resort — with affordable, intuitive technology that eliminates operational chaos, reduces costs, and creates exceptional guest experiences.</p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="sh-mission-card">
            <div class="icon-wrap"><i class="bi bi-eye"></i></div>
            <h4>Our Vision</h4>
            <p class="text-muted">To become the backbone of South Asian hospitality infrastructure — the single platform that every hotel relies on to manage operations, grow revenue, and build guest loyalty for decades to come.</p>
          </div>
        </div>
        <div class="col-12">
          <div class="sh-mission-card">
            <div class="icon-wrap"><i class="bi bi-lightbulb"></i></div>
            <h4 class="mb-4">Our Values</h4>
            <div class="row g-4">
              <div class="col-sm-6 col-lg-3">
                <h6 class="fw-bold">Simplicity First</h6>
                <p class="text-muted small">Complex operations made simple with intuitive design.</p>
              </div>
              <div class="col-sm-6 col-lg-3">
                <h6 class="fw-bold">Customer Obsession</h6>
                <p class="text-muted small">Every decision starts with what's best for our hotel partners.</p>
              </div>
              <div class="col-sm-6 col-lg-3">
                <h6 class="fw-bold">Continuous Innovation</h6>
                <p class="text-muted small">We ship improvements every week, driven by customer feedback.</p>
              </div>
              <div class="col-sm-6 col-lg-3">
                <h6 class="fw-bold">Reliability</h6>
                <p class="text-muted small">99.9% uptime. When your hotel runs, SmartHotel runs.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== WHY CHOOSE US ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <div class="sh-section-tag">Why SmartHotel</div>
          <h2 class="sh-section-title">Why 2,000+ Hotels Choose Us</h2>
          <ul class="list-unstyled mt-4">
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">Purpose-built for Indian hospitality businesses</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">GST-compliant invoicing and billing</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">Multi-language and multi-currency support</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">24/7 dedicated customer support in Hindi &amp; English</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">Regular free updates with no upgrade fees</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">Cloud-based with offline mode for uninterrupted operations</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">Integrates with 50+ OTAs and booking platforms</span></li>
            <li class="d-flex align-items-start gap-2 mb-3"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="text-muted">GDPR &amp; data protection compliant</span></li>
          </ul>
        </div>
        <div class="col-lg-6">
          <div class="p-4 rounded-4" style="background:#0f172a">
            <p class="text-white-50 small fw-semibold mb-4">Hotel Software Growth Stats</p>
            <div class="mb-4">
              <div class="sh-progress-label"><span class="text-white-50 small">Average Revenue Increase</span><span>28%</span></div>
              <div class="progress" style="height:8px;border-radius:999px;background:rgba(255,255,255,.1)">
                <div class="progress-bar bg-primary" style="width:28%;border-radius:999px"></div>
              </div>
            </div>
            <div class="mb-4">
              <div class="sh-progress-label"><span class="text-white-50 small">Reduction in Manual Tasks</span><span>65%</span></div>
              <div class="progress" style="height:8px;border-radius:999px;background:rgba(255,255,255,.1)">
                <div class="progress-bar bg-primary" style="width:65%;border-radius:999px"></div>
              </div>
            </div>
            <div class="mb-4">
              <div class="sh-progress-label"><span class="text-white-50 small">Customer Retention Rate</span><span>96%</span></div>
              <div class="progress" style="height:8px;border-radius:999px;background:rgba(255,255,255,.1)">
                <div class="progress-bar bg-primary" style="width:96%;border-radius:999px"></div>
              </div>
            </div>
            <div>
              <div class="sh-progress-label"><span class="text-white-50 small">Time Saved on Payroll/Week</span><span>12 hrs</span></div>
              <div class="progress" style="height:8px;border-radius:999px;background:rgba(255,255,255,.1)">
                <div class="progress-bar bg-primary" style="width:70%;border-radius:999px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== EMPLOYEE MODULE HIGHLIGHT ===================== -->
  <section class="section-pad bg-sh-light">
    <div class="container">
      <div class="text-center mb-5">
        <div class="sh-section-tag"><i class="bi bi-person-gear"></i> Staff Management</div>
        <h2 class="sh-section-title">Powerful Employee Management Module</h2>
        <p class="sh-section-sub">Hotel success depends on great staff. SmartHotel gives you all the tools to manage, motivate, and pay your team efficiently.</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(29,78,216,.1);color:var(--sh-primary)"><i class="bi bi-person-badge"></i></div>
            <h5>Staff Profiles &amp; Roles</h5>
            <p>Maintain complete employee records including qualifications, department, contact details, and role assignments.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(29,78,216,.1);color:var(--sh-primary)"><i class="bi bi-clipboard-check"></i></div>
            <h5>Attendance &amp; Scheduling</h5>
            <p>Digital punch-in/out, automated shift scheduling, leave management, and overtime tracking.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(29,78,216,.1);color:var(--sh-primary)"><i class="bi bi-currency-rupee"></i></div>
            <h5>Payroll Processing</h5>
            <p>Auto-calculate salaries, allowances, deductions, and generate pay slips with one click.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== TEAM ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="sh-section-title">Meet Our Leadership</h2>
        <p class="sh-section-sub">Hospitality veterans and technology innovators, united by a passion for the hotel industry.</p>
      </div>
      <div class="row g-4 justify-content-center">
        <div class="col-6 col-lg-3">
          <div class="sh-team-card">
            <div class="team-avatar" style="background:#3b82f6">AM</div>
            <h5>Arjun Mehta</h5>
            <p class="team-role">CEO &amp; Co-Founder</p>
            <p class="team-bio">15 years in hospitality tech. Previously VP at IHG Digital.</p>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="sh-team-card">
            <div class="team-avatar" style="background:#a855f7">SR</div>
            <h5>Sunita Rao</h5>
            <p class="team-role">CTO &amp; Co-Founder</p>
            <p class="team-bio">Ex-Infosys architect. Built hotel PMS systems for Taj Group.</p>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="sh-team-card">
            <div class="team-avatar" style="background:#10b981">VS</div>
            <h5>Vikram Singh</h5>
            <p class="team-role">Head of Sales</p>
            <p class="team-bio">Scaled B2B SaaS from 0 to 500+ clients in the hospitality sector.</p>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="sh-team-card">
            <div class="team-avatar" style="background:#f59e0b">AP</div>
            <h5>Ananya Pillai</h5>
            <p class="team-role">Head of Customer Success</p>
            <p class="team-bio">7 years in hotel operations. Turned 98% of clients into brand advocates.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== CTA ===================== -->
  <section class="sh-cta-section section-pad">
    <div class="container text-center position-relative">
      <h2 class="sh-section-title text-white mb-3">Join 2,000+ Hotels Using SmartHotel</h2>
      <p style="color:rgba(255,255,255,.7);margin-bottom:2rem">Start your free trial today — no credit card, no commitment.</p>
      <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
        <a href="/contact-us" class="btn-sh-white">Start Free Trial</a>
        <a href="/services" class="btn-sh-outline">Explore Services</a>
      </div>
    </div>
  </section>

@endsection 