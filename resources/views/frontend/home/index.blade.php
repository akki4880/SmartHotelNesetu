@extends('frontend.layouts.index')

@section('title', 'SmartHotel - Complete Hotel Management Solution') 

@section('content')
 <!-- ===================== HERO ===================== -->
  <section class="sh-hero">
    <div class="container text-center position-relative">
      <div class="hero-badge">
        <i class="bi bi-lightning-charge-fill"></i> Trusted by 2,000+ Hotels Worldwide
      </div>
      <h1>Complete Smart Hotel<br><span class="gradient-text">Management Solution</span></h1>
      <p class="lead mx-auto">
        Streamline room booking, invoice management, guest profiles, employee payroll, restaurant billing, and housekeeping — all in one powerful platform.
      </p>
      <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
        <a href="contact.html" class="btn-sh-primary"><i class="bi bi-rocket-takeoff"></i> Start Free Trial</a>
        <a href="contact.html" class="btn-sh-outline"><i class="bi bi-camera-video"></i> Book Demo</a>
      </div>
      <ul class="hero-check-list justify-content-center">
        <li><i class="bi bi-check-circle-fill"></i> No credit card required</li>
        <li><i class="bi bi-check-circle-fill"></i> 14-day free trial</li>
        <li><i class="bi bi-check-circle-fill"></i> 24/7 support</li>
      </ul>
    </div>
  </section>

  <!-- ===================== DASHBOARD PREVIEW ===================== -->
  <section class="bg-sh-dark py-5">
    <div class="container">
      <div class="sh-dashboard-preview">
        <div class="inner">
          <div class="sh-dash-topbar">
            <div class="sh-dash-dots d-flex align-items-center gap-1">
              <span style="background:#ef4444"></span>
              <span style="background:#f59e0b"></span>
              <span style="background:#10b981"></span>
              <span class="ms-2" style="font-size:.72rem;color:rgba(255,255,255,.35)">SmartHotel Dashboard</span>
            </div>
            <span style="font-size:.7rem;color:rgba(255,255,255,.25)">v2.1.0</span>
          </div>
          <div class="sh-dash-content">
            <div class="sh-dash-sidebar d-none d-sm-block">
              <div class="sh-dash-nav-item active">Dashboard</div>
              <div class="sh-dash-nav-item">Bookings</div>
              <div class="sh-dash-nav-item">Rooms</div>
              <div class="sh-dash-nav-item">Guests</div>
              <div class="sh-dash-nav-item">Staff</div>
              <div class="sh-dash-nav-item">Billing</div>
              <div class="sh-dash-nav-item">Reports</div>
            </div>
            <div class="sh-dash-main">
              <div class="sh-dash-stat-grid">
                <div class="sh-dash-stat">
                  <div class="val text-success">124/150</div>
                  <div class="lbl">Occupied Rooms</div>
                </div>
                <div class="sh-dash-stat">
                  <div class="val" style="color:#60a5fa">28</div>
                  <div class="lbl">Today's Bookings</div>
                </div>
                <div class="sh-dash-stat">
                  <div class="val text-warning">₹1.8L</div>
                  <div class="lbl">Revenue Today</div>
                </div>
                <div class="sh-dash-stat">
                  <div class="val" style="color:#c084fc">42</div>
                  <div class="lbl">Active Staff</div>
                </div>
              </div>
              <div class="sh-dash-bars">
                <div style="font-size:.7rem;color:rgba(255,255,255,.4);margin-bottom:.5rem">Occupancy This Week</div>
                <div class="sh-dash-bar-row">
                  <div class="sh-dash-bar" style="height:65%"></div>
                  <div class="sh-dash-bar" style="height:78%"></div>
                  <div class="sh-dash-bar" style="height:82%"></div>
                  <div class="sh-dash-bar" style="height:70%"></div>
                  <div class="sh-dash-bar" style="height:88%"></div>
                  <div class="sh-dash-bar" style="height:95%;background:rgba(59,130,246,0.85)"></div>
                  <div class="sh-dash-bar" style="height:83%"></div>
                </div>
                <div class="sh-dash-days">
                  <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== STATS ===================== -->
  <section class="bg-sh-light section-pad-sm">
    <div class="container">
      <div class="row g-4">
        <div class="col-6 col-lg-3">
          <div class="sh-stat-card">
            <div class="stat-icon"><i class="bi bi-globe"></i></div>
            <div class="stat-value">2,000+</div>
            <div class="stat-label">Hotels Using SmartHotel</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="sh-stat-card">
            <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
            <div class="stat-value">98.7%</div>
            <div class="stat-label">Customer Satisfaction</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="sh-stat-card">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value">₹500Cr+</div>
            <div class="stat-label">Revenue Processed Monthly</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="sh-stat-card">
            <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
            <div class="stat-value">99.9%</div>
            <div class="stat-label">Uptime Guarantee</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== FEATURES ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="text-center mb-5">
        <div class="sh-section-tag"><i class="bi bi-stars"></i> Everything You Need</div>
        <h2 class="sh-section-title">Powerful Features for Modern Hotels</h2>
        <p class="sh-section-sub">From a boutique guesthouse to a large hotel chain — SmartHotel scales with your business.</p>
      </div>
      <div class="row g-4">
        <!-- Feature Cards -->
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="bi bi-door-open"></i></div>
            <h5>Room Booking</h5>
            <p>Real-time availability, instant booking confirmations, and automated room assignment across all channels.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(168,85,247,.12);color:#a855f7"><i class="bi bi-receipt"></i></div>
            <h5>Invoice Templates</h5>
            <p>Professional, customizable invoice templates with automated billing and multi-currency support.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(16,185,129,.12);color:#10b981"><i class="bi bi-people"></i></div>
            <h5>Guest Management</h5>
            <p>Complete guest profiles, preferences, loyalty tracking, and personalized experience management.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="bi bi-person-gear"></i></div>
            <h5>Employee Management</h5>
            <p>Streamline HR operations, track attendance, manage shifts, and process payroll seamlessly.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="bi bi-cup-hot"></i></div>
            <h5>Restaurant Billing</h5>
            <p>Integrated POS for your restaurant, bar, and room service with automatic guest-room posting.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(20,184,166,.12);color:#14b8a6"><i class="bi bi-stars"></i></div>
            <h5>Housekeeping</h5>
            <p>Digital task assignments, real-time room status updates, and automated housekeeping workflows.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(99,102,241,.12);color:#6366f1"><i class="bi bi-bar-chart-line"></i></div>
            <h5>Analytics Dashboard</h5>
            <p>Deep insights into occupancy, revenue, staff performance, and operational efficiency metrics.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="sh-card">
            <div class="sh-card-icon" style="background:rgba(236,72,153,.12);color:#ec4899"><i class="bi bi-shield-lock"></i></div>
            <h5>Security &amp; Access</h5>
            <p>Role-based access control, audit logs, and data encryption to protect your hotel operations.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== TESTIMONIALS ===================== -->
  <section class="section-pad bg-sh-light">
    <div class="container">
      <div class="text-center mb-5">
        <div class="sh-section-tag"><i class="bi bi-star-fill"></i> Customer Stories</div>
        <h2 class="sh-section-title">Loved by Hotel Managers</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="sh-testimonial">
            <div class="stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            <blockquote>"SmartHotel completely transformed how we manage our 150-room property. Bookings are up 30% and our team spends 40% less time on admin tasks."</blockquote>
            <div class="author">
              <div class="avatar" style="background:#3b82f6">RS</div>
              <div>
                <p class="author-name">Rajesh Sharma</p>
                <p class="author-role">General Manager, The Grand Palace</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="sh-testimonial">
            <div class="stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            <blockquote>"The employee management and payroll features saved us 20 hours per month. The invoice templates are beautiful and our guests love the professional billing."</blockquote>
            <div class="author">
              <div class="avatar" style="background:#a855f7">PN</div>
              <div>
                <p class="author-name">Priya Nair</p>
                <p class="author-role">Owner, Serenity Boutique Hotel</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="sh-testimonial">
            <div class="stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            <blockquote>"We manage 5 branches through SmartHotel. The centralized analytics and multi-property support make decision-making so much easier for our growing chain."</blockquote>
            <div class="author">
              <div class="avatar" style="background:#10b981">MA</div>
              <div>
                <p class="author-name">Mohammed Ali</p>
                <p class="author-role">Operations Director, Star Resorts</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== PRICING PREVIEW ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="text-center mb-5">
        <div class="sh-section-tag">Simple Pricing</div>
        <h2 class="sh-section-title">Plans for Every Hotel Size</h2>
        <p class="sh-section-sub">Start free. Upgrade as you grow. No hidden fees.</p>
      </div>
      <div class="row g-4 justify-content-center">
        <div class="col-md-4">
          <div class="sh-pricing-card text-start">
            <div class="plan-icon"><i class="bi bi-lightning-charge"></i></div>
            <div class="plan-name">Starter</div>
            <div class="plan-price">₹1,999<span>/mo</span></div>
            <div class="plan-rooms"><span class="badge-pill">Up to 25 rooms</span></div>
            <a href="/Pricing" class="btn-sh-primary w-100 d-block text-center mt-3">Get Started <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="sh-pricing-card popular text-start">
            <div class="popular-badge">Most Popular — Recommended</div>
            <div class="plan-icon active"><i class="bi bi-star"></i></div>
            <div class="plan-name">Professional</div>
            <div class="plan-price">₹4,999<span>/mo</span></div>
            <div class="plan-rooms"><span class="badge-pill">Up to 100 rooms</span></div>
            <a href="/Pricing" class="btn-sh-primary w-100 d-block text-center mt-3">Get Started <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="sh-pricing-card text-start">
            <div class="plan-icon"><i class="bi bi-building"></i></div>
            <div class="plan-name">Enterprise</div>
            <div class="plan-price" style="font-size:2rem">Custom</div>
            <div class="plan-rooms"><span class="badge-pill">Unlimited rooms</span></div>
            <a href="contact.html" class="btn-sh-primary w-100 d-block text-center mt-3">Contact Sales <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
      <div class="text-center mt-4">
        <a href="/Pricing" class="text-primary fw-semibold text-decoration-none">View full pricing details <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- ===================== FAQ ===================== -->
  <section class="section-pad bg-sh-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="sh-section-title">Frequently Asked Questions</h2>
        <p class="sh-section-sub">Everything you need to know about SmartHotel.</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="accordion sh-faq" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">How long does it take to set up SmartHotel?</button></h2>
              <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Most hotels are up and running within 24–48 hours. Our onboarding team handles the entire setup — from importing existing data to training your staff.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Can SmartHotel integrate with OTAs like Booking.com and Expedia?</button></h2>
              <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes! SmartHotel integrates with 50+ OTA platforms including Booking.com, Expedia, MakeMyTrip, and Airbnb through our built-in channel manager.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Is my data safe and secure?</button></h2>
              <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Absolutely. We use bank-grade AES-256 encryption, daily backups, and host on secure cloud infrastructure with a 99.9% uptime guarantee.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Do you offer a free trial?</button></h2>
              <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes! Start a 14-day free trial with no credit card required. Access all features during the trial period with full support from our team.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">Can I manage multiple hotel branches?</button></h2>
              <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes, our Professional and Enterprise plans support multi-branch management with consolidated reporting and centralized staff management.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== CTA BANNER ===================== -->
  <section class="sh-cta-section section-pad">
    <div class="container text-center position-relative">
      <h2 class="sh-section-title text-white mb-3">Ready to Transform Your Hotel Operations?</h2>
      <p style="color:rgba(255,255,255,.7);font-size:1.1rem;margin-bottom:2rem">Join 2,000+ hotels already using SmartHotel. Start your 14-day free trial today.</p>
      <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
        <a href="/contact-us" class="btn-sh-white">Start Free Trial</a>
        <a href="/contact-us" class="btn-sh-outline">Talk to Sales</a>
      </div>
    </div>
  </section> 
@endsection 