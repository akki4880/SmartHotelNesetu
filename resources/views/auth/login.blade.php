<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="SmartHotel Login - Sign in to your hotel management dashboard." />
  <title>Login - SmartHotel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}" />
  <style>
    body { background: var(--sh-secondary); min-height: 100vh; display: flex; flex-direction: column; }

    .login-page {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      position: relative;
      overflow: hidden;
    }

    .login-page::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 15% 40%, rgba(29, 78, 216, 0.25) 0%, transparent 55%),
        radial-gradient(ellipse at 85% 70%, rgba(6, 182, 212, 0.1) 0%, transparent 50%);
      pointer-events: none;
    }

    .login-wrap {
      display: flex;
      width: 100%;
      max-width: 960px;
      min-height: 580px;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(0, 0, 0, 0.5);
      position: relative;
      z-index: 1;
    }

    /* Left Panel */
    .login-panel-left {
      flex: 1;
      background: linear-gradient(145deg, #0f172a, #1e3a5f 60%, #1d4ed8);
      padding: 3rem 2.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }

    .login-panel-left::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: rgba(59, 130, 246, 0.12);
      pointer-events: none;
    }

    .login-panel-left::after {
      content: '';
      position: absolute;
      bottom: -60px;
      left: -40px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(6, 182, 212, 0.08);
      pointer-events: none;
    }

    .login-panel-left .brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
    }

    .login-panel-left .brand .icon {
      width: 42px;
      height: 42px;
      background: var(--sh-primary);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      color: #fff;
      flex-shrink: 0;
    }

    .login-panel-left .brand-name {
      font-size: 1.375rem;
      font-weight: 800;
      color: #fff;
    }

    .login-panel-left .brand-name span { color: #93c5fd; }

    .login-panel-left .panel-body { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 2rem 0; }

    .login-panel-left h2 {
      font-size: 1.875rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.25;
      margin-bottom: 1rem;
    }

    .login-panel-left p {
      color: rgba(255,255,255,0.65);
      font-size: 0.9rem;
      line-height: 1.7;
      margin-bottom: 2rem;
    }

    .login-feature-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .login-feature-list li {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      color: rgba(255,255,255,0.7);
      font-size: 0.875rem;
      padding: 0.5rem 0;
      border-bottom: 1px solid rgba(255,255,255,0.07);
    }

    .login-feature-list li:last-child { border-bottom: none; }

    .login-feature-list li .fi {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: rgba(59, 130, 246, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      color: #60a5fa;
      flex-shrink: 0;
    }

    .login-panel-left .panel-footer-text {
      font-size: 0.78rem;
      color: rgba(255,255,255,0.3);
    }

    /* Right Panel (Form) */
    .login-panel-right {
      width: 420px;
      background: #fff;
      padding: 3rem 2.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-panel-right h3 {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--sh-text);
      margin-bottom: 0.25rem;
    }

    .login-panel-right .sub {
      font-size: 0.875rem;
      color: var(--sh-text-muted);
      margin-bottom: 2rem;
    }

    .login-form-group { margin-bottom: 1.125rem; }

    .login-form-group label {
      display: block;
      font-size: 0.825rem;
      font-weight: 600;
      color: var(--sh-text);
      margin-bottom: 0.4rem;
    }

    .login-input-wrap { position: relative; }

    .login-input-wrap .input-icon {
      position: absolute;
      left: 0.875rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--sh-text-muted);
      font-size: 1rem;
      pointer-events: none;
    }

    .login-input-wrap input {
      width: 100%;
      padding: 0.7rem 0.875rem 0.7rem 2.5rem;
      border: 1.5px solid var(--sh-border);
      border-radius: var(--sh-radius-sm);
      font-size: 0.9rem;
      color: var(--sh-text);
      background: var(--sh-bg);
      transition: var(--sh-transition);
      outline: none;
      font-family: inherit;
    }

    .login-input-wrap input:focus {
      border-color: var(--sh-primary);
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
      background: #fff;
    }

    .login-input-wrap input.is-invalid {
      border-color: var(--sh-danger);
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .login-input-wrap .toggle-pw {
      position: absolute;
      right: 0.875rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--sh-text-muted);
      cursor: pointer;
      font-size: 1rem;
      padding: 0;
      transition: color 0.2s;
    }

    .login-input-wrap .toggle-pw:hover { color: var(--sh-primary); }

    .invalid-msg {
      font-size: 0.78rem;
      color: var(--sh-danger);
      margin-top: 0.3rem;
      display: none;
    }

    .invalid-msg.show { display: block; }

    .login-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
    }

    .login-options .remember {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.8rem;
      color: var(--sh-text-muted);
      cursor: pointer;
    }

    .login-options .remember input[type="checkbox"] {
      accent-color: var(--sh-primary);
      width: 14px;
      height: 14px;
    }

    .login-options a {
      font-size: 0.8rem;
      color: var(--sh-primary);
      text-decoration: none;
      font-weight: 600;
    }

    .login-options a:hover { text-decoration: underline; }

    .btn-login {
      width: 100%;
      padding: 0.8rem;
      background: var(--sh-primary);
      color: #fff;
      font-weight: 700;
      font-size: 0.9375rem;
      border: none;
      border-radius: var(--sh-radius-sm);
      cursor: pointer;
      transition: var(--sh-transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-family: inherit;
    }

    .btn-login:hover {
      background: var(--sh-primary-dark);
      box-shadow: 0 6px 20px rgba(29, 78, 216, 0.35);
      transform: translateY(-1px);
    }

    .btn-login:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .login-divider {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 1.25rem 0;
      color: var(--sh-text-muted);
      font-size: 0.78rem;
    }

    .login-divider::before,
    .login-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--sh-border);
    }

    .btn-demo {
      width: 100%;
      padding: 0.75rem;
      background: var(--sh-bg);
      border: 1.5px solid var(--sh-border);
      border-radius: var(--sh-radius-sm);
      color: var(--sh-text);
      font-weight: 600;
      font-size: 0.875rem;
      cursor: pointer;
      transition: var(--sh-transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      text-decoration: none;
      font-family: inherit;
    }

    .btn-demo:hover {
      border-color: var(--sh-primary);
      background: rgba(29, 78, 216, 0.04);
      color: var(--sh-primary);
    }

    .login-footer {
      text-align: center;
      font-size: 0.8rem;
      color: var(--sh-text-muted);
      margin-top: 1.5rem;
    }

    .login-footer a {
      color: var(--sh-primary);
      font-weight: 600;
      text-decoration: none;
    }

    .login-footer a:hover { text-decoration: underline; }

    .login-error-alert {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: var(--sh-radius-sm);
      padding: 0.75rem 1rem;
      font-size: 0.85rem;
      color: #dc2626;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    /* Responsive: hide left panel on small screens */
    @media (max-width: 767px) {
      .login-panel-left { display: none; }
      .login-panel-right { width: 100%; max-width: 420px; border-radius: 20px; }
      .login-wrap { max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
    }

    /* Navbar override for auth pages */
    .sh-navbar-auth {
      background-color: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
  </style>
</head>
<body>

  <!-- ===================== NAVBAR ===================== -->
  <nav class="navbar sh-navbar sh-navbar-auth sticky-top" id="shNavbar">
    <div class="container d-flex align-items-center justify-content-between">
      <a class="navbar-brand" href="index.html">
        <div class="brand-icon"><i class="bi bi-building text-white"></i></div>
        Smart<span class="brand-accent">Hotel</span>
      </a>
      <div class="d-flex align-items-center gap-3">
        <span style="color:rgba(255,255,255,.5);font-size:.85rem">Don't have an account?</span>
        <a href="contact.html" class="btn-nav-trial">Start Free Trial</a>
      </div>
    </div>
  </nav>

  <!-- ===================== LOGIN PAGE ===================== -->
  <main class="login-page">
    <div class="login-wrap">

      <!-- Left Promo Panel -->
      <div class="login-panel-left d-none d-md-flex">
        <a href="index.html" class="brand">
          <div class="icon"><i class="bi bi-building"></i></div>
          <span class="brand-name">Smart<span>Hotel</span></span>
        </a>
        <div class="panel-body">
          <h2>Manage Your Hotel with Confidence</h2>
          <p>Access your complete hotel management dashboard — bookings, staff, billing, and analytics — all from one powerful platform.</p>
          <ul class="login-feature-list">
            <li><div class="fi"><i class="bi bi-door-open"></i></div> Real-time room booking &amp; availability</li>
            <li><div class="fi"><i class="bi bi-people"></i></div> Employee attendance &amp; payroll</li>
            <li><div class="fi"><i class="bi bi-receipt"></i></div> GST-compliant invoice system</li>
            <li><div class="fi"><i class="bi bi-bar-chart-line"></i></div> Revenue &amp; occupancy analytics</li>
            <li><div class="fi"><i class="bi bi-buildings"></i></div> Multi-branch management</li>
          </ul>
        </div>
        <p class="panel-footer-text">&copy; 2025 SmartHotel Technologies Pvt. Ltd.</p>
      </div>

      <!-- Right Form Panel -->
      <div class="login-panel-right">
        <h3>Welcome back</h3>
        <p class="sub">Sign in to your SmartHotel dashboard</p>

        <!-- Error alert (hidden by default) --> 
         @error('email')  
        <div class="login-error-alert" id="loginError">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span id="loginErrorMsg">{{ $message }}</span> 
        </div>
        @enderror
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <!-- Email -->
          <div class="login-form-group">
            <label for="loginEmail">Email Address</label>
            <div class="login-input-wrap">
              <i class="bi bi-envelope input-icon"></i>
              <input type="email" id="loginEmail" name="email" placeholder="you@hotel.com" autocomplete="email" required />
             
            </div>
            <div class="invalid-msg" id="emailErr">Please enter a valid email address.</div>
          </div>

          <!-- Password -->
          <div class="login-form-group">
            <label for="loginPassword">Password</label>
            <div class="login-input-wrap">
              <i class="bi bi-lock input-icon"></i>
              <input type="password" id="loginPassword" name="password" placeholder="Enter your password" autocomplete="current-password" required />
                @error('password')
                    <span class="text-danger"><b>{{ $message }}</b></span>
                @enderror
              <button type="button" class="toggle-pw" id="togglePw" title="Show/hide password" aria-label="Toggle password visibility">
                <i class="bi bi-eye" id="pwIcon"></i>
              </button>
            </div>
            <div class="invalid-msg" id="pwErr">Password is required.</div>
          </div>

          <!-- Remember me / Forgot password --> 
          @if (Route::has('password.request'))
          <div class="login-options">
            <label class="remember">
              <input type="checkbox" id="rememberMe" /> Remember me
            </label>
            <a href="{{ route('password.request') }}">Forgot password?</a>
          </div>
          @endif
          <!-- Submit -->
          <button type="submit" class="btn-login" id="loginBtn">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
          </button>
        </form>

        <div class="login-divider">or</div>

        <!-- Demo access -->
        <button type="button" class="btn-demo" id="demoBtn">
          <i class="bi bi-play-circle"></i> Continue with Demo Account
        </button>

        <div class="login-footer">
          New to SmartHotel? <a href="contact.html">Start your free 14-day trial</a>
        </div>
      </div>

    </div>
  </main>

  <!-- Floating Call Button -->
  <a href="tel:+911234567890" class="sh-float-btn" title="Call Demo: +91 12345 67890" aria-label="Call Demo">
    <i class="bi bi-telephone-fill"></i>
  </a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('frontend/assets/js/script.js') }}"></script>
  <script>
    (function () {
      var form = document.getElementById('loginForm');
      var emailInput = document.getElementById('loginEmail');
      var pwInput = document.getElementById('loginPassword');
      var emailErr = document.getElementById('emailErr');
      var pwErr = document.getElementById('pwErr');
      var loginError = document.getElementById('loginError');
      var loginBtn = document.getElementById('loginBtn');
      var togglePw = document.getElementById('togglePw');
      var pwIcon = document.getElementById('pwIcon');
      var demoBtn = document.getElementById('demoBtn');

      /* Toggle password visibility */
      togglePw.addEventListener('click', function () {
        if (pwInput.type === 'password') {
          pwInput.type = 'text';
          pwIcon.className = 'bi bi-eye-slash';
        } else {
          pwInput.type = 'password';
          pwIcon.className = 'bi bi-eye';
        }
      });

      /* Clear errors on input */
      emailInput.addEventListener('input', function () {
        emailInput.classList.remove('is-invalid');
        emailErr.classList.remove('show');
        loginError.style.display = 'none';
      });

      pwInput.addEventListener('input', function () {
        pwInput.classList.remove('is-invalid');
        pwErr.classList.remove('show');
        loginError.style.display = 'none';
      });

      /* Form submit */
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var valid = true;

        /* Validate email */
        var emailVal = emailInput.value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal || !emailRegex.test(emailVal)) {
          emailInput.classList.add('is-invalid');
          emailErr.classList.add('show');
          valid = false;
        }

        /* Validate password */
        if (!pwInput.value) {
          pwInput.classList.add('is-invalid');
          pwErr.classList.add('show');
          valid = false;
        }

        if (!valid) return;

        /* Show loading state */
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Signing in...';

        /* Simulate auth check */
        setTimeout(function () {
          loginBtn.disabled = false;
          loginBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Sign In';
          /* Show error for demo */
          loginError.style.display = 'flex';
          document.getElementById('loginErrorMsg').textContent = 'Invalid email or password. Try the demo account instead.';
        }, 1400);
      });

      /* Demo account fill */
      demoBtn.addEventListener('click', function () {
        emailInput.value = 'demo@smarthotel.com';
        pwInput.value = 'Demo@1234';
        emailInput.classList.remove('is-invalid');
        pwInput.classList.remove('is-invalid');
        emailErr.classList.remove('show');
        pwErr.classList.remove('show');
        loginError.style.display = 'none';

        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Signing in...';
        setTimeout(function () {
          loginBtn.disabled = false;
          loginBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Sign In';
          loginError.style.display = 'flex';
          document.getElementById('loginErrorMsg').textContent = 'Demo login is for preview only. Contact us to start your free trial.';
        }, 1200);
      });
    })();
  </script>
</body>
</html>
