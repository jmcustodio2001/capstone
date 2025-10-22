<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Employee Portal - Jetlouge Travels Admin</title>
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
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels">
          </div>
          <h1 class="brand-text">Jetlouge Travels</h1>
          <p class="brand-subtitle">Employee Portal</p>
        </div>

        <h2 class="welcome-text">Welcome Back!</h2>
        <p class="welcome-subtitle">
          Access your travel management dashboard to monitor bookings,
          manage customers, and grow your travel business.
        </p>

        <ul class="feature-list">
          <li><i class="bi bi-check"></i> Manage bookings & reservations</li>
          <li><i class="bi bi-check"></i> Track customer interactions</li>
          <li><i class="bi bi-check"></i> Monitor business analytics</li>
          <li><i class="bi bi-check"></i> Secure employee access</li>
        </ul>
      </div>

      <!-- Right Side - Login Form -->
      <div class="col-lg-6 login-right">
        <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
          Sign In to Your Account
        </h3>

  <!-- Login Form -->
  <form method="POST" action="{{ route('employee.login.submit') }}" id="loginForm" autocomplete="off" style="display: block;">
  <input type="hidden" name="force_session_renewal" value="1">

          @csrf

          @if (session('errors') && session('errors')->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach (session('errors')->all() as $error)
                  @if (stripos($error, 'csrf token mismatch') === false)
                    <li>{{ $error }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="{{ old('email') }}"
                required
              >
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="invalid-feedback" id="emailError" style="display: none;"></div>
            </div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
              >
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="invalid-feedback" id="passwordError" style="display: none;"></div>
            </div>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember" value="1">
            <label class="form-check-label" for="rememberMe">
              <i class="bi bi-person-check me-1"></i>Remember me
            </label>
          </div>

          <!-- Google reCAPTCHA -->
          <div class="mb-3 d-flex justify-content-center">
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" id="recaptcha-widget"></div>
          </div>
          
          <!-- CAPTCHA error display -->
          <div class="alert alert-warning" id="captchaError" style="display: none;"></div>

          <!-- Success notification display -->
          <div class="alert alert-success" id="successMessage" style="display: none;"></div>

          <!-- General login error display -->
          <div class="alert alert-danger" id="loginError" style="display: none;"></div>
          
          <button type="submit" class="btn btn-login mb-3" id="loginButton">
            <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
          </button>

          <div class="text-center">
            <a href="{{ route('employee.forgot_password') }}" class="btn-forgot">Forgot your password?</a>
          </div>

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
              Code expires in <span id="otpTimer" class="fw-bold text-danger">10:00</span>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Global variables
      let otpTimer;
      let otpTimeLeft = 600; // 10 minutes in seconds
      let currentUserEmail = '';

      // Server-side attempt tracking - no client-side localStorage needed

      // DOM elements
      const loginForm = document.getElementById('loginForm');
      const otpForm = document.getElementById('otpForm');
      const loginButton = document.getElementById('loginButton');
      const verifyOtpButton = document.getElementById('verifyOtpButton');
      const resendOtpButton = document.getElementById('resendOtpButton');
      const backToLoginButton = document.getElementById('backToLoginButton');
      const otpInput = document.getElementById('otp_code');
      const otpError = document.getElementById('otpError');
      const otpEmailDisplay = document.getElementById('otpEmailDisplay');
      const otpTimerDisplay = document.getElementById('otpTimer');
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');

      // Password toggle functionality
      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });

      // Remember Me functionality with visual feedback
      const rememberMeCheckbox = document.getElementById('rememberMe');
      const rememberMeLabel = document.querySelector('label[for="rememberMe"]');

      rememberMeCheckbox.addEventListener('change', function() {
        if (this.checked) {
          rememberMeLabel.style.color = 'var(--jetlouge-primary)';
          rememberMeLabel.style.fontWeight = '600';
          rememberMeLabel.setAttribute('title', 'You will stay logged in for 30 days');
        } else {
          rememberMeLabel.style.color = '';
          rememberMeLabel.style.fontWeight = '';
          rememberMeLabel.removeAttribute('title');
        }
      });

      // Simplified CSRF token handling - no complex refresh
      function getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
      }

      // Simple token refresh only when needed (for 419 errors)
      async function refreshCSRFTokenIfNeeded() {
        try {
          const response = await fetch('/employee/csrf-token', {
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
              // Update meta tag
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
        
        // Always return existing token as fallback
        return getCSRFToken();
      }

      // OTP Timer functionality
      function startOTPTimer() {
        otpTimeLeft = 600; // Reset to 10 minutes
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
        const requestNew = confirm('Your verification code has expired. Please request a new one.\n\nClick OK to request a new code.');
        if (requestNew) {
          resendOTP();
        }
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

      // Server-side attempt tracking - no client-side variables needed

      // Lockout timer functionality
      let lockoutTimer;
      
      function showLockoutTimer(remainingSeconds, lockoutCount) {
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        
        // Clear any existing timer
        if (lockoutTimer) {
          clearInterval(lockoutTimer);
        }
        
        // Show lockout message
        alert('Account temporarily locked due to too many failed attempts.\n\nPlease try again later.');
        
        // Auto-unlock after lockout period
        setTimeout(() => {
          alert('You can now try logging in again.');
        }, remainingSeconds * 1000);
      }
      
      // Lockout countdown function removed for security

      // CAPTCHA validation function
      function validateCaptcha() {
        const captchaResponse = grecaptcha.getResponse();
        if (!captchaResponse) {
          showCaptchaError('Please complete the CAPTCHA verification before proceeding.');
          return false;
        }
        return true;
      }

      // Function to show CAPTCHA errors
      function showCaptchaError(message) {
        const captchaErrorDiv = document.getElementById('captchaError');
        captchaErrorDiv.textContent = message;
        captchaErrorDiv.style.display = 'block';
        
        // Hide after 5 seconds
        setTimeout(() => {
          captchaErrorDiv.style.display = 'none';
        }, 5000);
      }

      // Function to show success messages
      function showSuccessMessage(message) {
        const successDiv = document.getElementById('successMessage');
        successDiv.textContent = message;
        successDiv.style.display = 'block';
        
        // Hide after 3 seconds
        setTimeout(() => {
          successDiv.style.display = 'none';
        }, 3000);
      }

      // Function to show login errors inline
      function showLoginError(message) {
        const loginErrorDiv = document.getElementById('loginError');
        loginErrorDiv.textContent = message;
        loginErrorDiv.style.display = 'block';
        
        // Hide after 5 seconds
        setTimeout(() => {
          loginErrorDiv.style.display = 'none';
        }, 5000);
      }

      // Function to clear all error displays
      function clearLoginErrors() {
        document.getElementById('loginError').style.display = 'none';
        document.getElementById('emailError').style.display = 'none';
        document.getElementById('passwordError').style.display = 'none';
        document.getElementById('captchaError').style.display = 'none';
        document.getElementById('successMessage').style.display = 'none';
        
        // Remove invalid classes
        document.getElementById('email').classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');
      }

      // Reset CAPTCHA function
      function resetCaptcha() {
        if (typeof grecaptcha !== 'undefined') {
          grecaptcha.reset();
        }
      }

      // Login form submission
      loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Clear previous errors
        clearLoginErrors();

        // Validate CAPTCHA first
        if (!validateCaptcha()) {
          return;
        }

        const submitButton = loginButton;
        const originalText = submitButton.innerHTML;

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Authenticating...';

        try {
          // Use existing token first, no pre-refresh
          const csrfToken = getCSRFToken();
          
          if (!csrfToken) {
            throw new Error('No CSRF token available');
          }

          const formData = new FormData(this);
          // Don't override existing _token, just ensure it's there
          if (!formData.has('_token')) {
            formData.append('_token', csrfToken);
          }
          
          // Add CAPTCHA response
          const captchaResponse = grecaptcha.getResponse();
          formData.append('g-recaptcha-response', captchaResponse);

          const response = await fetch('{{ route("employee.login.submit") }}', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken
            }
          });

          const data = await response.json();
          console.log('Login response:', data); // Debug logging

          if (data.success) {
            if (data.step === 'otp_required') {
              // Show success message and switch to OTP form
              let message = data.message;

              // If dev_otp is provided, show it clearly
              if (data.dev_otp) {
                message = `Verification code sent to your email. Use code: ${data.dev_otp}`;
              }

              showSuccessMessage(message);
              showOTPForm(document.getElementById('email').value);
            } else if (data.step === 'login_complete') {
              // Direct login successful (if OTP is not required)
              window.location.href = data.redirect_url;
            }
          } else {
            // Handle server-side error responses
            if (data.step === 'lockout') {
              showLockoutTimer(data.lockout_remaining_seconds || (data.lockout_remaining * 60), data.lockout_count || 1);
            } else {
              // Reset CAPTCHA on error
              resetCaptcha();
              
              // Show error message inline
              let errorText = data.message || 'An unknown error occurred.';
              
              // Check if it's an email or password specific error
              if (errorText.toLowerCase().includes('email') || errorText.toLowerCase().includes('account') || errorText.toLowerCase().includes('no account found')) {
                document.getElementById('email').classList.add('is-invalid');
                document.getElementById('emailError').textContent = errorText;
                document.getElementById('emailError').style.display = 'block';
              } else if (errorText.toLowerCase().includes('password') || 
                         errorText.toLowerCase().includes('credential') || 
                         errorText.toLowerCase().includes('incorrect') || 
                         errorText.toLowerCase().includes('wrong') ||
                         errorText.toLowerCase().includes('invalid') ||
                         errorText.toLowerCase().includes('failed') ||
                         errorText.toLowerCase().includes('login')) {
                // Most login failures are password-related, show in password field
                document.getElementById('password').classList.add('is-invalid');
                document.getElementById('passwordError').textContent = errorText;
                document.getElementById('passwordError').style.display = 'block';
              } else {
                showLoginError(errorText);
              }
            }
          }
        } catch (error) {
          console.error('Login error:', error);
          // Reset CAPTCHA on connection error
          resetCaptcha();
          
          showLoginError('Unable to connect to the server. Please check your internet connection and try again.');
        } finally {
          // Restore button state
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        }
      });

      // OTP form submission
      otpForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const otpCode = otpInput.value.trim();
        if (otpCode.length !== 6) {
          showOTPError('Please enter a 6-digit verification code.');
          return;
        }

        const submitButton = verifyOtpButton;
        const originalText = submitButton.innerHTML;

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Verifying...';

        try {
          // Try with existing token first
          let csrfToken = getCSRFToken();
          
          // Create form data
          const formData = new FormData();
          formData.append('otp_code', otpCode);
          formData.append('_token', csrfToken);

          let response = await fetch('{{ route("employee.verify_otp") }}', {
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
            
            // Retry with new token
            const retryFormData = new FormData();
            retryFormData.append('otp_code', otpCode);
            retryFormData.append('_token', csrfToken);

            response = await fetch('{{ route("employee.verify_otp") }}', {
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

            // Redirect directly without showing success message
            window.location.href = data.redirect_url;
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
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        }
      });

      // Resend OTP - simplified
      function resendOTP() {
        const submitButton = resendOtpButton;
        const originalText = submitButton.innerHTML;

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sending...';

        // Use existing token
        const csrfToken = getCSRFToken();
        
        if (!csrfToken) {
          alert('Session Error\n\nPlease refresh the page and try again.');
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
          return;
        }

        // Create form data for resend request
        const formData = new FormData();
        formData.append('_token', csrfToken);

        fetch('{{ route("employee.resend_otp") }}', {
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
            alert('Code Resent!\n\n' + data.message);

            // Restart timer
            startOTPTimer();
          } else {
            alert('Resend Failed\n\n' + data.message);
          }
        })
        .catch(error => {
          console.error('Resend OTP error:', error);
          alert('Connection Error\n\nUnable to resend code. Please try again.');
        })
        .finally(() => {
          // Restore button state
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        });
      }

      // Show OTP error
      function showOTPError(message) {
        otpError.textContent = message;
        otpInput.classList.add('is-invalid');
        otpError.style.display = 'block';
      }

      // Event listeners
      resendOtpButton.addEventListener('click', resendOTP);

      backToLoginButton.addEventListener('click', function() {
        const goBack = confirm('Cancel Login?\n\nAre you sure you want to go back to the login form?');
        if (goBack) {
          // Reset CAPTCHA when going back to login
          resetCaptcha();
          showLoginForm();
        }
      });

      // OTP input formatting
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

      // Initialize page on load
      window.addEventListener('load', () => {
        // Page loaded successfully
        console.log('Employee login page loaded');
      });

      // Floating shapes animation
      const shapes = document.querySelectorAll('.shape');
      shapes.forEach((shape, index) => {
        shape.style.animationDelay = `${index * 2}s`;
      });
    });
  </script>
</body>
</html>
