@extends('frontend.layouts.index')

@section('title', 'SmartHotel - Complete Hotel Management Solution') 

@section('content')
 <!-- ===================== HERO ===================== -->
  <section class="sh-hero" style="padding:7rem 0 4.5rem">
    <div class="container text-center position-relative">
      <div class="hero-badge"><i class="bi bi-tag"></i> Simple, Transparent Pricing</div>
      <h1>Plans That Grow With You</h1>
      <p class="lead mx-auto">No hidden fees. No lock-in contracts. Start your 14-day free trial and pay only when you love it.</p>
      <!-- Billing Toggle -->
      <div class="sh-billing-toggle mt-4">
        <div class="toggle-option active" id="monthlyBtn">Monthly</div>
        <div class="toggle-option" id="yearlyBtn">Yearly <span class="save-badge">Save 16%</span></div>
      </div>
    </div>
  </section>

  <!-- ===================== PRICING CARDS ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="row g-4 align-items-stretch">

        <!-- Starter -->
        <div class="col-md-4">
          <div class="sh-pricing-card">
            <div class="plan-icon"><i class="bi bi-lightning-charge"></i></div>
            <div class="plan-name">Starter</div>
            <!-- Monthly Price -->
            <div class="plan-price price-monthly">₹1,999<span>/month</span></div>
            <!-- Yearly Price -->
            <div class="plan-price price-yearly">₹19,990<span>/year</span></div>
            <div class="yearly-save" style="font-size:.8rem;color:#10b981;font-weight:600;margin-bottom:.25rem">Save ₹3,998/year</div>
            <p class="plan-tagline">Perfect for small guesthouses and boutique properties</p>
            <div class="plan-rooms">
              <span class="badge-pill">Up to 25 rooms</span>
              <span class="badge-pill">Up to 15 employees</span>
            </div>
            <a href="/contact-us" class="btn-sh-primary d-block text-center mb-4">Start Free Trial <i class="bi bi-arrow-right"></i></a>
            <ul class="feature-list">
              <li><i class="bi bi-check-circle-fill text-success"></i> 25 Room Management</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> 15 Employee Profiles</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> 5 Invoice Templates</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Basic Reports</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Email Support (48hr)</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Channel Manager (5 OTAs)</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> Restaurant Billing</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> Payroll Management</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> Multi-Branch Support</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> Custom Branding</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> API Access</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> Dedicated Account Manager</li>
            </ul>
          </div>
        </div>

        <!-- Professional (Popular) -->
        <div class="col-md-4">
          <div class="sh-pricing-card popular">
            <div class="popular-badge">Most Popular — Recommended</div>
            <div class="plan-icon active"><i class="bi bi-star-fill"></i></div>
            <div class="plan-name">Professional</div>
            <div class="plan-price price-monthly">₹4,999<span>/month</span></div>
            <div class="plan-price price-yearly">₹49,990<span>/year</span></div>
            <div class="yearly-save" style="font-size:.8rem;color:#10b981;font-weight:600;margin-bottom:.25rem">Save ₹9,998/year</div>
            <p class="plan-tagline">The complete solution for growing hotels and resorts</p>
            <div class="plan-rooms">
              <span class="badge-pill">Up to 100 rooms</span>
              <span class="badge-pill">Up to 75 employees</span>
            </div>
            <a href="/contact-us" class="btn-sh-primary d-block text-center mb-4">Start Free Trial <i class="bi bi-arrow-right"></i></a>
            <ul class="feature-list">
              <li><i class="bi bi-check-circle-fill text-success"></i> 100 Room Management</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> 75 Employee Profiles</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Unlimited Invoice Templates</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Advanced Analytics</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Priority Support (4hr)</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Channel Manager (25 OTAs)</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Restaurant Billing</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Payroll Management</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Multi-Branch (up to 3)</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Custom Branding</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> API Access</li>
              <li class="excluded"><i class="bi bi-x-circle text-muted"></i> Dedicated Account Manager</li>
            </ul>
          </div>
        </div>

        <!-- Enterprise -->
        <div class="col-md-4">
          <div class="sh-pricing-card">
            <div class="plan-icon"><i class="bi bi-building"></i></div>
            <div class="plan-name">Enterprise</div>
            <div class="plan-price price-monthly" style="font-size:2.25rem">Custom</div>
            <div class="plan-price price-yearly" style="font-size:2.25rem">Custom</div>
            <div class="yearly-save" style="font-size:.8rem;color:#10b981;font-weight:600;margin-bottom:.25rem">Contact for multi-year discounts</div>
            <p class="plan-tagline">Custom solutions for large hotel chains and groups</p>
            <div class="plan-rooms">
              <span class="badge-pill">Unlimited rooms</span>
              <span class="badge-pill">Unlimited employees</span>
            </div>
            <a href="/contact-us" class="btn-sh-primary d-block text-center mb-4">Contact Sales <i class="bi bi-arrow-right"></i></a>
            <ul class="feature-list">
              <li><i class="bi bi-check-circle-fill text-success"></i> Unlimited Rooms</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Unlimited Employees</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Unlimited Invoice Templates</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Enterprise Analytics &amp; BI</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> 24/7 Phone &amp; Chat Support</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Channel Manager (50+ OTAs)</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Restaurant Billing</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Payroll Management</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Unlimited Multi-Branch</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Custom Branding &amp; White-label</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Full API Access</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Dedicated Account Manager</li>
            </ul>
          </div>
        </div>

      </div>
      <p class="text-center text-muted mt-4">All plans include a <strong>14-day free trial</strong> — no credit card required. Need something custom? <a href="/contact-us" class="text-primary fw-semibold">Talk to our sales team</a></p>
    </div>
  </section>

  <!-- ===================== COMPARISON TABLE ===================== -->
  <section class="section-pad bg-sh-light">
    <div class="container">
      <h2 class="sh-section-title text-center mb-5">Full Feature Comparison</h2>
      <div class="sh-compare-table">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Feature</th>
                <th class="text-center">Starter</th>
                <th class="text-center highlight">Professional</th>
                <th class="text-center">Enterprise</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Rooms Supported</td><td class="text-center">25</td><td class="text-center highlight">100</td><td class="text-center">Unlimited</td></tr>
              <tr><td>Employees</td><td class="text-center">15</td><td class="text-center highlight">75</td><td class="text-center">Unlimited</td></tr>
              <tr><td>Invoice Templates</td><td class="text-center">5</td><td class="text-center highlight">Unlimited</td><td class="text-center">Unlimited</td></tr>
              <tr><td>Booking Channels (OTAs)</td><td class="text-center">5</td><td class="text-center highlight">25</td><td class="text-center">50+</td></tr>
              <tr><td>Restaurant POS</td><td class="text-center text-muted">—</td><td class="text-center highlight"><i class="bi bi-check-lg text-success"></i></td><td class="text-center"><i class="bi bi-check-lg text-success"></i></td></tr>
              <tr><td>Payroll Management</td><td class="text-center text-muted">—</td><td class="text-center highlight"><i class="bi bi-check-lg text-success"></i></td><td class="text-center"><i class="bi bi-check-lg text-success"></i></td></tr>
              <tr><td>Housekeeping Module</td><td class="text-center"><i class="bi bi-check-lg text-success"></i></td><td class="text-center highlight"><i class="bi bi-check-lg text-success"></i></td><td class="text-center"><i class="bi bi-check-lg text-success"></i></td></tr>
              <tr><td>Multi-Branch</td><td class="text-center text-muted">—</td><td class="text-center highlight">3 branches</td><td class="text-center">Unlimited</td></tr>
              <tr><td>Analytics &amp; Reports</td><td class="text-center">Basic</td><td class="text-center highlight">Advanced</td><td class="text-center">Enterprise BI</td></tr>
              <tr><td>Custom Branding</td><td class="text-center text-muted">—</td><td class="text-center highlight"><i class="bi bi-check-lg text-success"></i></td><td class="text-center">White-label</td></tr>
              <tr><td>API Access</td><td class="text-center text-muted">—</td><td class="text-center highlight text-muted">—</td><td class="text-center"><i class="bi bi-check-lg text-success"></i></td></tr>
              <tr><td>Support</td><td class="text-center">Email (48hr)</td><td class="text-center highlight">Priority (4hr)</td><td class="text-center">24/7 Phone</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== PRICING FAQ ===================== -->
  <section class="section-pad">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="sh-section-title">Pricing FAQ</h2>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="accordion sh-faq" id="pricingFaq">
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pf1">Can I switch plans later?</button></h2>
              <div id="pf1" class="accordion-collapse collapse show" data-bs-parent="#pricingFaq">
                <div class="accordion-body">Yes! You can upgrade or downgrade your plan at any time. Upgrades take effect immediately; downgrades take effect at the end of your current billing cycle.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf2">Is there a setup fee?</button></h2>
              <div id="pf2" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                <div class="accordion-body">No setup fees, ever. All plans include free onboarding, data migration from your existing system, and staff training.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf3">What payment methods do you accept?</button></h2>
              <div id="pf3" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                <div class="accordion-body">We accept all major credit/debit cards, UPI, NEFT/RTGS, and bank transfers. GST invoices are provided for all payments.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf4">What happens after the 14-day trial?</button></h2>
              <div id="pf4" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                <div class="accordion-body">At the end of your free trial, choose a plan and enter payment details. If you don't upgrade, your account is paused — no data is deleted for 30 days.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf5">Do you offer discounts for multi-year plans?</button></h2>
              <div id="pf5" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                <div class="accordion-body">Yes! Annual plans save you 2 months (over 16% off). Multi-year plans receive additional custom discounts — contact our sales team.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== CTA ===================== -->
  <section class="sh-cta-section section-pad">
    <div class="container text-center position-relative">
      <h2 class="sh-section-title text-white mb-3">Start Your Free Trial Today</h2>
      <p style="color:rgba(255,255,255,.7);margin-bottom:2rem;font-size:1.1rem">14 days free. No credit card. Full access to all features.</p>
      <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
        <a href="/contact-us" class="btn-sh-white">Start Free Trial</a>
        <a href="/contact-us" class="btn-sh-outline">Schedule a Demo</a>
      </div>
    </div>
  </section>
@endsection 