<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Security Headers -->
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="X-Frame-Options" content="DENY">
  <meta http-equiv="X-XSS-Protection" content="1; mode=block">
  <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
  <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=()">
  <!-- Content Security Policy -->
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self'; frame-src https://www.google.com;">
  <title>Admin Portal</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Login Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/admin_login-style.css') }}">
  
  <!-- Google reCAPTCHA -->
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  
  <!-- OTP Specific Styles -->
  <style>
    .otp-input {
      transition: all 0.3s ease;
    }
    .otp-input:focus {
      box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
      border-color: var(--jetlouge-primary);
    }
    .otp-icon {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    .timer-warning {
      animation: blink 1s infinite;
    }
    @keyframes blink {
      0%, 50% { opacity: 1; }
      51%, 100% { opacity: 0.5; }
    }
    .form-transition {
      transition: all 0.5s ease-in-out;
    }
    .btn-login:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    .captcha-container {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }
    .privacy-notice {
      background: rgba(102, 126, 234, 0.1);
      border-left: 4px solid var(--jetlouge-primary);
      padding: 12px;
      margin: 15px 0;
      border-radius: 4px;
      font-size: 0.85rem;
    }
    .security-warning {
      background: rgba(255, 193, 7, 0.1);
      border-left: 4px solid #ffc107;
      padding: 10px;
      margin: 10px 0;
      border-radius: 4px;
      font-size: 0.8rem;
    }
    .session-timeout-warning {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 350px;
      display: none;
    }
    .privacy-links {
      font-size: 0.8rem;
      margin-top: 15px;
    }
    .privacy-links a {
      color: var(--jetlouge-primary);
      text-decoration: none;
      margin: 0 8px;
    }
    .privacy-links a:hover {
      text-decoration: underline;
    }
    .privacy-agreement {
      background: rgba(102, 126, 234, 0.05);
      border: 1px solid rgba(102, 126, 234, 0.2);
      padding: 12px;
      border-radius: 6px;
      margin: 15px 0;
    }
    .privacy-agreement .form-check-input:checked {
      background-color: var(--jetlouge-primary);
      border-color: var(--jetlouge-primary);
    }
    .privacy-agreement label a {
      color: var(--jetlouge-primary);
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="row g-0">
            <!-- Left Side - Welcome -->
            <div class="col-lg-6 login-left">
              <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
              </div>

              <div class="logo-container">
                <div class="logo-box">
                  <img src="{{ asset("assets/images/jetlouge_logo.png") }}" alt="Jetlouge Travels">
                </div>
                <h1 class="brand-text">Jetlouge Travels</h1>
                <p class="brand-subtitle">Admin Portal</p>
              </div>

              <h2 class="welcome-text">Welcome Back!</h2>
              <p class="welcome-subtitle">
                Access your travel management dashboard to monitor bookings,
                manage customers, and grow your travel business.
              </p>

              <ul class="feature-list">
                <li>
                  <i class="bi bi-check"></i>
                  <span>Manage bookings & reservations</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Track customer interactions</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Monitor business analytics</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Secure admin access</span>
                </li>
                <li>
                  <i class="bi bi-shield-check"></i>
                  <span>Privacy protected login</span>
                </li>
                <li>
                  <i class="bi bi-lock"></i>
                  <span>Data encryption & security</span>
                </li>
              </ul>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 login-right">
              <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
                Sign In to Your Account
              </h3>
              @if($errors instanceof \Illuminate\Support\ViewErrorBag && $errors->any())
            <div class="alert alert-danger" id="errorAlert">
              {{ $errors->first() }}
            </div>
      @endif
      @if(session('lockout'))
            <div class="alert alert-danger" id="lockoutAlert">
              Account is temporarily locked. Please try again later.
            </div>
      @endif
      @if(session('attempts'))
            <div class="alert alert-warning" id="attemptsAlert">
              Too many failed attempts. Please try again later.
            </div>
      @endif
              <!-- Notification Area -->
              <div id="notificationArea" class="mb-3" style="display: none;">
                <div id="notificationAlert" class="alert" role="alert">
                  <i id="notificationIcon" class="me-2"></i>
                  <span id="notificationMessage"></span>
                </div>
              </div>

              <!-- Login Form -->
              <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm" style="display: block;">
                @csrf
                <input type="hidden" name="force_session_renewal" value="1">
                
                <div class="mb-3">
                  <label for="email" class="form-label fw-semibold">Email Address</label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" required value="{{ old('email') }}">
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="password" class="form-label fw-semibold">Password</label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter your password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <i class="bi bi-eye"></i>
                    </button>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <!-- Privacy Agreement Checkbox -->
                <div class="privacy-agreement">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="privacyAgreement" name="privacy_agreement" value="1" required>
                    <label class="form-check-label" for="privacyAgreement">
                      I agree to the 
                      <a href="#" onclick="showPrivacyPolicy()" class="text-decoration-none">Privacy Policy</a> and 
                      <a href="#" onclick="showTermsOfService()" class="text-decoration-none">Terms of Service</a>
                    </label>
                  </div>
                </div>
                
                <!-- Google reCAPTCHA -->
                <div class="mb-3 captcha-container">
                  @if(config('services.recaptcha.site_key'))
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-theme="light"></div>
                  @else
                    <div class="alert alert-warning">
                      <i class="bi bi-exclamation-triangle me-2"></i>
                      reCAPTCHA not configured. Please set RECAPTCHA_SITE_KEY in your environment file.
                    </div>
                  @endif
                  @error('g-recaptcha-response')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                
                
                <button type="submit" class="btn btn-login mb-3" id="submitBtn">
                  <i class="bi bi-box-arrow-in-right me-2"></i>
                  Sign In
                </button>
                
                <hr class="my-4">
              </form>
              
              <!-- OTP Verification Form -->
              <form id="otpForm" style="display: none;">
                @csrf
                <div class="text-center mb-4">
                  <div class="otp-icon mb-3">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
                  </div>
                  <h4 style="color: var(--jetlouge-primary); font-weight: 700;">Verify Your Identity</h4>
                  <p class="text-muted">We've sent a 6-digit verification code to your email address.</p>
                  <p class="fw-bold" id="otpEmailDisplay"></p>
                </div>

                <div class="mb-3">
                  <label for="otp_code" class="form-label fw-semibold">Enter Verification Code</label>
                  <div class="otp-input-container">
                    <input type="tel" class="form-control otp-input" id="otp_code" name="otp_code"
                           placeholder="000000" maxlength="6" autocomplete="off"
                           inputmode="numeric" pattern="[0-9]*"
                           style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;">
                  </div>
                  <div class="invalid-feedback" id="otpError"></div>
                </div>

                <div class="mb-3 text-center">
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Code expires in <span id="otpTimer" class="fw-bold text-danger">2:00</span>
                  </small>
                </div>

                <button type="submit" class="btn btn-login mb-3" id="verifyOtpButton">
                  <i class="bi bi-check-circle me-2"></i> Verify Code
                </button>

                <div class="text-center mb-3">
                  <button type="button" class="btn btn-outline-secondary" id="resendOtpButton">
                    <i class="bi bi-arrow-clockwise me-2"></i> Resend Code
                  </button>
                </div>

                <div class="text-center">
                  <button type="button" class="btn-forgot" id="backToLoginButton">
                    <i class="bi bi-arrow-left me-1"></i> Back to Login
                  </button>
                </div>

                <hr class="my-4">
              </form>
            </div>
    </div>
  </div>

  <!-- Session timeout is now silent for security - no warning modal needed -->

  <!-- Privacy Policy Modal -->
  <div class="modal fade" id="privacyPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Privacy Policy</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <h6>Data Collection & Usage</h6>
          <p>We collect only necessary information for authentication and system access. This includes:</p>
          <ul>
            <li>Email address for account identification</li>
            <li>Login timestamps for security monitoring</li>
            <li>Session data for maintaining secure access</li>
          </ul>
          
          <h6>Data Protection</h6>
          <p>Your data is protected through:</p>
          <ul>
            <li>Encrypted password storage</li>
            <li>Secure HTTPS connections</li>
            <li>Two-factor authentication (OTP)</li>
            <li>Regular security audits</li>
          </ul>
          
          <h6>Data Retention</h6>
          <p>We retain login data only as long as necessary for security and operational purposes. Inactive accounts are reviewed periodically.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Terms of Service Modal -->
  <div class="modal fade" id="termsOfServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>Terms of Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <h6>Acceptable Use</h6>
          <p>By accessing this system, you agree to:</p>
          <ul>
            <li>Use the system only for authorized business purposes</li>
            <li>Maintain the confidentiality of your login credentials</li>
            <li>Report any security incidents immediately</li>
            <li>Comply with company policies and procedures</li>
          </ul>
          
          <h6>Security Responsibilities</h6>
          <p>Users are responsible for:</p>
          <ul>
            <li>Using strong, unique passwords</li>
            <li>Logging out when finished</li>
            <li>Not sharing account access with others</li>
            <li>Using secure networks and devices</li>
          </ul>
          
          <h6>System Monitoring</h6>
          <p>This system is monitored for security purposes. Unauthorized access attempts will be logged and investigated.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Security Info Modal -->
  <div class="modal fade" id="securityInfoModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Security Information</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <h6>Security Features</h6>
          <ul>
            <li><strong>Two-Factor Authentication:</strong> OTP codes sent to your email</li>
            <li><strong>Account Lockout:</strong> Protection against brute force attacks</li>
            <li><strong>Session Management:</strong> Automatic timeout for inactive sessions</li>
            <li><strong>Encryption:</strong> All data transmitted securely</li>
          </ul>
          
          <h6>Best Practices</h6>
          <ul>
            <li>Always log out when finished</li>
            <li>Use a secure, private network</li>
            <li>Keep your browser updated</li>
            <li>Never share your login credentials</li>
          </ul>
          
          <h6>Report Security Issues</h6>
          <p>If you notice any suspicious activity or security concerns, please contact IT support immediately.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Global Privacy and Security Functions (must be global for onclick handlers)
    function showPrivacyPolicy() {
      const modal = new bootstrap.Modal(document.getElementById('privacyPolicyModal'));
      modal.show();
    }

    function showTermsOfService() {
      const modal = new bootstrap.Modal(document.getElementById('termsOfServiceModal'));
      modal.show();
    }

    function showSecurityInfo() {
      const modal = new bootstrap.Modal(document.getElementById('securityInfoModal'));
      modal.show();
    }

    // Session management is now silent for security - no user interaction needed

    // Global CSRF Token Function
    function getCSRFToken() {
      const metaTag = document.querySelector('meta[name="csrf-token"]');
      return metaTag ? metaTag.getAttribute('content') : null;
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Global variables
      let otpTimer;
      let otpTimeLeft = 120; // 2 minutes in seconds
      let currentUserEmail = '';
      let notificationTimeout;
      let sessionTimeoutTimer;
      let sessionWarningTimer;
      let sessionTimeLeft = 25 * 60; // Default 25 minutes (will be updated from server)

      // Notification system
      function showNotification(message, type = 'info', duration = 5000) {
        const notificationArea = document.getElementById('notificationArea');
        const notificationAlert = document.getElementById('notificationAlert');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationMessage = document.getElementById('notificationMessage');
        
        if (!notificationArea || !notificationAlert || !notificationIcon || !notificationMessage) {
          console.error('Notification elements not found');
          return;
        }
        
        // Clear existing timeout
        if (notificationTimeout) {
          clearTimeout(notificationTimeout);
        }
        
        // Set icon and classes based on type
        notificationAlert.className = 'alert';
        switch(type) {
          case 'success':
            notificationAlert.classList.add('alert-success');
            notificationIcon.className = 'bi bi-check-circle me-2';
            break;
          case 'error':
          case 'danger':
            notificationAlert.classList.add('alert-danger');
            notificationIcon.className = 'bi bi-exclamation-circle me-2';
            break;
          case 'warning':
            notificationAlert.classList.add('alert-warning');
            notificationIcon.className = 'bi bi-exclamation-triangle me-2';
            break;
          case 'info':
          default:
            notificationAlert.classList.add('alert-info');
            notificationIcon.className = 'bi bi-info-circle me-2';
            break;
        }
        
        notificationMessage.textContent = message;
        notificationArea.style.display = 'block';
        
        // Auto-hide after duration
        if (duration > 0) {
          notificationTimeout = setTimeout(() => {
            hideNotification();
          }, duration);
        }
      }
      
      function hideNotification() {
        const notificationArea = document.getElementById('notificationArea');
        if (notificationArea) {
          notificationArea.style.display = 'none';
        }
        if (notificationTimeout) {
          clearTimeout(notificationTimeout);
          notificationTimeout = null;
        }
      }

      // Clean up any old localStorage attempt tracking since we now use server-side tracking
      localStorage.removeItem('loginAttempts');
      localStorage.removeItem('lockoutTime');

      // Set CSRF token for AJAX requests
      const token = document.querySelector('meta[name="csrf-token"]');
      if (token) {
        window.Laravel = {
          csrfToken: token.getAttribute('content')
        };
      }

      // DOM elements
      const loginForm = document.getElementById('adminLoginForm');
      const otpForm = document.getElementById('otpForm');
      const submitBtn = document.getElementById('submitBtn');
      const verifyOtpButton = document.getElementById('verifyOtpButton');
      const resendOtpButton = document.getElementById('resendOtpButton');
      const backToLoginButton = document.getElementById('backToLoginButton');
      const otpInput = document.getElementById('otp_code');
      const otpError = document.getElementById('otpError');
      const otpEmailDisplay = document.getElementById('otpEmailDisplay');
      const otpTimerDisplay = document.getElementById('otpTimer');
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const emailInput = document.getElementById('email');

      // Show error message if there are validation errors
      const errorAlert = document.getElementById('errorAlert');
      if (errorAlert && !errorAlert.classList.contains('d-none')) {
        const errorMessage = errorAlert.textContent.trim();
        if (errorMessage) {
          // Keep the existing Bootstrap alert visible
          errorAlert.style.display = 'block';
        }
      }

      // Password toggle functionality
      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
          const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordInput.setAttribute('type', type);

          const icon = this.querySelector('i');
          icon.classList.toggle('bi-eye');
          icon.classList.toggle('bi-eye-slash');
        });
      }


      // CSRF token handling
      function getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
      }

      async function refreshCSRFTokenIfNeeded() {
        try {
          const response = await fetch('/admin/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          
          if (response.ok) {
            const data = await response.json();
            if (data.csrf_token || data.token) {
              const newToken = data.csrf_token || data.token;
              const metaTag = document.querySelector('meta[name="csrf-token"]');
              if (metaTag) {
                metaTag.setAttribute('content', newToken);
              }
              return newToken;
            }
          }
        } catch (error) {
          console.log('Token refresh failed, using existing token');
        }
        
        return getCSRFToken();
      }

      // OTP Timer functionality
      function startOTPTimer() {
        otpTimeLeft = 120; // Reset to 2 minutes
        updateTimerDisplay();

        otpTimer = setInterval(() => {
          otpTimeLeft--;
          updateTimerDisplay();

          if (otpTimeLeft <= 0) {
            clearInterval(otpTimer);
            showExpiredMessage();
          }
        }, 1000);
      }

      function updateTimerDisplay() {
        const minutes = Math.floor(otpTimeLeft / 60);
        const seconds = otpTimeLeft % 60;
        otpTimerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        // Change color based on time remaining
        if (otpTimeLeft <= 60) {
          otpTimerDisplay.className = 'fw-bold text-danger';
        } else if (otpTimeLeft <= 300) {
          otpTimerDisplay.className = 'fw-bold text-warning';
        } else {
          otpTimerDisplay.className = 'fw-bold text-success';
        }
      }

      function showExpiredMessage() {
        showNotification('Your verification code has expired. Click "Resend Code" to request a new one.', 'warning', 0);
      }

      // Show OTP form
      function showOTPForm(email) {
        currentUserEmail = email;
        loginForm.style.display = 'none';
        otpForm.style.display = 'block';
        otpEmailDisplay.textContent = email;
        otpInput.focus();
        startOTPTimer();

        // Clear any previous errors
        otpError.textContent = '';
        otpInput.classList.remove('is-invalid');
      }

      // Show login form
      function showLoginForm() {
        otpForm.style.display = 'none';
        loginForm.style.display = 'block';

        // Clear OTP form
        otpInput.value = '';
        otpError.textContent = '';
        otpInput.classList.remove('is-invalid');

        // Clear timer
        if (otpTimer) {
          clearInterval(otpTimer);
        }
      }

      // Form validation and submission with CAPTCHA and 2FA
      if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', async function(e) {
          e.preventDefault();

          // Validate form fields
          const email = emailInput.value.trim();
          const password = passwordInput.value.trim();

          if (!email) {
            showNotification('Please enter your email address.', 'warning');
            emailInput.focus();
            return false;
          }

          if (!password) {
            showNotification('Please enter your password.', 'warning');
            passwordInput.focus();
            return false;
          }

          // Email format validation
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(email)) {
            showNotification('Please enter a valid email address.', 'warning');
            emailInput.focus();
            return false;
          }

          // Privacy Agreement validation
          const privacyCheckbox = document.getElementById('privacyAgreement');
          if (!privacyCheckbox.checked) {
            showNotification('Please agree to the Privacy Policy and Terms of Service before proceeding.', 'warning');
            privacyCheckbox.focus();
            return false;
          }

          // CAPTCHA validation
          const recaptchaResponse = grecaptcha.getResponse();
          if (!recaptchaResponse) {
            showNotification('Please complete the CAPTCHA verification.', 'warning');
            return false;
          }

          // Check if CSRF token exists
          const csrfToken = getCSRFToken();
          if (!csrfToken) {
            showNotification('Security token missing. Please refresh the page and try again.', 'error');
            setTimeout(() => {
              window.location.reload();
            }, 2000);
            return false;
          }

          const originalText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Authenticating...';

          try {
            const formData = new FormData(this);
            if (!formData.has('_token')) {
              formData.append('_token', csrfToken);
            }

            const response = await fetch('{{ route("admin.login.submit") }}', {
              method: 'POST',
              body: formData,
              credentials: 'same-origin',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
              }
            });

            const data = await response.json();
            console.log('Login response:', data);

            if (data.success) {
              if (data.step === 'otp_required') {
                // Switch to OTP form without revealing details
                showOTPForm(email);
              } else if (data.step === 'login_complete') {
                // Direct login successful - redirect without notification for security
                window.location.href = data.redirect_url || '{{ route("admin.dashboard") }}';
              }
            } else {
              // Handle server-side error responses
              if (data.step === 'lockout') {
                showLockoutTimer(data.lockout_remaining_seconds || (data.lockout_remaining * 60), data.lockout_count || 1);
              } else {
                let errorText = data.message || 'An unknown error occurred.';
                showNotification('Login Failed: ' + errorText, 'error');

                // Reset CAPTCHA on error
                grecaptcha.reset();
              }
            }
          } catch (error) {
            console.error('Login error:', error);
            showNotification('Connection Error: Unable to connect to the server. Please check your internet connection and try again.', 'error');
            
            // Reset CAPTCHA on error
            grecaptcha.reset();
          } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          }
        });
      }

      // Lockout timer functionality
      let lockoutTimer;
      
      function showLockoutTimer(remainingSeconds, lockoutCount) {
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = Math.floor(remainingSeconds % 60);
        
        // Clear any existing timer
        if (lockoutTimer) {
          clearInterval(lockoutTimer);
        }
        
        // Create lockout message element
        const lockoutDiv = document.createElement('div');
        lockoutDiv.id = 'lockout-message';
        lockoutDiv.className = 'alert alert-danger text-center';
        lockoutDiv.style.position = 'fixed';
        lockoutDiv.style.top = '20px';
        lockoutDiv.style.left = '50%';
        lockoutDiv.style.transform = 'translateX(-50%)';
        lockoutDiv.style.zIndex = '9999';
        lockoutDiv.style.minWidth = '400px';
        lockoutDiv.innerHTML = `
          <h5>Account Temporarily Locked</h5>
          <p>Account temporarily locked due to too many failed attempts.</p>
          <p>Please try again later.</p>
        `;
        
        document.body.appendChild(lockoutDiv);
        
        // Auto-remove lockout message after the lockout period
        setTimeout(() => {
          const lockoutMessage = document.getElementById('lockout-message');
          if (lockoutMessage) {
            lockoutMessage.remove();
          }
          showNotification('You can now try logging in again.', 'info', 5000);
        }, remainingSeconds * 1000);
      }
      
      // Lockout countdown function removed for security

      // OTP form submission
      if (otpForm && verifyOtpButton) {
        otpForm.addEventListener('submit', async function(e) {
          e.preventDefault();

          const otpCode = otpInput.value.trim();
          if (otpCode.length !== 6) {
            showOTPError('Please enter a 6-digit verification code.');
            return;
          }

          const originalText = verifyOtpButton.innerHTML;
          verifyOtpButton.disabled = true;
          verifyOtpButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Verifying...';

          try {
            let csrfToken = getCSRFToken();
            
            const formData = new FormData();
            formData.append('otp_code', otpCode);
            formData.append('_token', csrfToken);

            let response = await fetch('{{ route("admin.verify_otp") }}', {
              method: 'POST',
              body: formData,
              credentials: 'same-origin',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
              }
            });

            // If CSRF error, try refreshing token once
            if (!response.ok && response.status === 419) {
              console.log('CSRF token expired, refreshing...');
              csrfToken = await refreshCSRFTokenIfNeeded();
              
              const retryFormData = new FormData();
              retryFormData.append('otp_code', otpCode);
              retryFormData.append('_token', csrfToken);

              response = await fetch('{{ route("admin.verify_otp") }}', {
                method: 'POST',
                body: retryFormData,
                credentials: 'same-origin',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-CSRF-TOKEN': csrfToken
                }
              });
            }

            const data = await response.json();
            
            if (data.success && data.step === 'login_complete') {
              // Clear timer on successful login
              if (otpTimer) {
                clearInterval(otpTimer);
              }

              // Redirect without notification for security
              window.location.href = data.redirect_url || '{{ route("admin.dashboard") }}';
            } else {
              showOTPError(data.message);
              if (data.remaining_attempts !== undefined) {
                showOTPError(`${data.message} (${data.remaining_attempts} attempts remaining)`);
              }
            }
          } catch (error) {
            console.error('OTP verification error:', error);
            showOTPError('Unable to verify code. Please try again.');
          } finally {
            // Restore button state
            verifyOtpButton.disabled = false;
            verifyOtpButton.innerHTML = originalText;
          }
        });
      }

      // Resend OTP functionality
      function resendOTP() {
        const originalText = resendOtpButton.innerHTML;
        resendOtpButton.disabled = true;
        resendOtpButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sending...';

        const csrfToken = getCSRFToken();
        
        if (!csrfToken) {
          showNotification('Session Error: Please refresh the page and try again.', 'error');
          resendOtpButton.disabled = false;
          resendOtpButton.innerHTML = originalText;
          return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);

        fetch('{{ route("admin.resend_otp") }}', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Restart timer without notification for security
            startOTPTimer();
          } else {
            showNotification('Unable to resend code. Please try again.', 'error');
          }
        })
        .catch(error => {
          console.error('Resend OTP error:', error);
          showNotification('Connection Error: Unable to resend code. Please try again.', 'error');
        })
        .finally(() => {
          resendOtpButton.disabled = false;
          resendOtpButton.innerHTML = originalText;
        });
      }

      // Show OTP error
      function showOTPError(message) {
        otpError.textContent = message;
        otpInput.classList.add('is-invalid');
        otpError.style.display = 'block';
      }

      // Event listeners
      if (resendOtpButton) {
        resendOtpButton.addEventListener('click', resendOTP);
      }

      if (backToLoginButton) {
        backToLoginButton.addEventListener('click', function() {
          showLoginForm();
          hideNotification();
          // Reset CAPTCHA when going back
          grecaptcha.reset();
        });
      }

      // OTP input formatting
      if (otpInput) {
        otpInput.addEventListener('input', function() {
          // Only allow numbers
          this.value = this.value.replace(/[^0-9]/g, '');

          // Clear error when user starts typing
          if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
            otpError.style.display = 'none';
          }

          // Auto-submit when 6 digits are entered
          if (this.value.length === 6) {
            setTimeout(() => {
              otpForm.dispatchEvent(new Event('submit'));
            }, 500);
          }
        });
      }

      // Add floating animation to shapes
      const shapes = document.querySelectorAll('.shape');
      shapes.forEach((shape, index) => {
        shape.style.animationDelay = `${index * 2}s`;
      });

      // Auto-refresh CSRF token every 30 minutes
      setInterval(function() {
        fetch('/admin/csrf-token')
          .then(response => response.json())
          .then(data => {
            const csrfInput = document.querySelector('input[name="_token"]');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfInput && data.token) {
              csrfInput.value = data.token;
            }
            if (csrfMeta && data.token) {
              csrfMeta.setAttribute('content', data.token);
            }
          })
          .catch(error => {
            console.log('CSRF token refresh failed:', error);
            // Silently handle CSRF token refresh failure
          });
      }, 30 * 60 * 1000); // 30 minutes

      // Welcome message removed (was using SweetAlert)

      // Show lockout alert if present from server
      const lockoutAlert = document.getElementById('lockoutAlert');
      if (lockoutAlert && !lockoutAlert.classList.contains('d-none')) {
        const lockoutMessage = lockoutAlert.textContent.trim();
        if (lockoutMessage) {
          // Keep the existing Bootstrap alert visible
          lockoutAlert.style.display = 'block';
        }
      }

      // Show attempts warning if present from server
      const attemptsAlert = document.getElementById('attemptsAlert');
      if (attemptsAlert && !attemptsAlert.classList.contains('d-none')) {
        const attemptsMessage = attemptsAlert.textContent.trim();
        if (attemptsMessage) {
          // Keep the existing Bootstrap alert visible
          attemptsAlert.style.display = 'block';
        }
      }

      // Session Timeout Management (Silent - No Warnings for Security)
      function startSessionTimeout() {
        // Clear any existing timers
        if (sessionTimeoutTimer) clearTimeout(sessionTimeoutTimer);
        
        // Silent logout after configured timeout duration (no warning)
        sessionTimeoutTimer = setTimeout(() => {
          handleSessionTimeout();
        }, sessionTimeLeft * 1000);
      }

      function handleSessionTimeout() {
        showNotification('Session Expired: Your session has expired for security reasons. You will be redirected to the login page.', 'warning');
        setTimeout(() => {
          window.location.href = '{{ route("admin.login") }}';
        }, 3000);
      }

      // Browser Security Check
      function checkBrowserSecurity() {
        const isHTTPS = location.protocol === 'https:';
        const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        
        if (!isHTTPS && !isLocalhost) {
          showNotification('Security Warning: This connection is not secure. Please use HTTPS for safe login.', 'warning', 0);
        }
        
        // Warn about insecure contexts
        if (!window.isSecureContext && !isLocalhost) {
          showNotification('Security Warning: This connection is not secure. Please use HTTPS for safe login.', 'warning', 0);
        }
      }

      // Enhanced Password Security
      function checkPasswordStrength(password) {
        const strength = {
          score: 0,
          feedback: []
        };
        
        if (password.length >= 8) strength.score++;
        else strength.feedback.push('Use at least 8 characters');
        
        if (/[A-Z]/.test(password)) strength.score++;
        else strength.feedback.push('Include uppercase letters');
        
        if (/[a-z]/.test(password)) strength.score++;
        else strength.feedback.push('Include lowercase letters');
        
        if (/[0-9]/.test(password)) strength.score++;
        else strength.feedback.push('Include numbers');
        
        if (/[^A-Za-z0-9]/.test(password)) strength.score++;
        else strength.feedback.push('Include special characters');
        
        return strength;
      }

      // Load timeout settings from server
      async function loadTimeoutSettings() {
        try {
          const response = await fetch('/admin/timeout-settings', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          
          if (response.ok) {
            const data = await response.json();
            if (data.success && data.session_timeout_enabled) {
              // Update session timeout variables (full duration, no warning)
              sessionTimeLeft = data.timeout_duration * 60; // Convert minutes to seconds
              console.log('Silent session timeout enabled:', data.timeout_duration, 'minutes');
              
              // Start session timeout monitoring after successful login
              // This will be called after authentication
            } else {
              console.log('Session timeout disabled or failed to load settings');
            }
          }
        } catch (error) {
          console.warn('Failed to load timeout settings:', error);
        }
      }

      // Initialize security features
      checkBrowserSecurity();
      loadTimeoutSettings();
    });
  </script>
</body>
</html>
