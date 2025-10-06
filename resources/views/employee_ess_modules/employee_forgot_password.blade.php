  q<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Forgot Password - Employee Portal</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- Login Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/admin_login-style.css') }}">

  <!-- Forgot Password Specific Styles -->
  <style>
    .otp-input {
      transition: all 0.3s ease;
    }
    .otp-input:focus {
      box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
      border-color: var(--jetlouge-primary);
    }
    .forgot-icon {
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
    .btn-forgot:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    .password-strength {
      font-size: 0.875rem;
      margin-top: 0.5rem;
    }
    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #198754; }
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

        <h2 class="welcome-text">Password Recovery</h2>
        <p class="welcome-subtitle">
          Don't worry! It happens to the best of us. Enter your email address 
          and we'll send you a verification code to reset your password.
        </p>

        <ul class="feature-list">
          <li><i class="bi bi-shield-check"></i> Secure password recovery</li>
          <li><i class="bi bi-envelope-check"></i> Email verification required</li>
          <li><i class="bi bi-key"></i> Create a new strong password</li>
          <li><i class="bi bi-clock-history"></i> Quick and easy process</li>
        </ul>
      </div>

      <!-- Right Side - Forgot Password Forms -->
      <div class="col-lg-6 login-right">
        <!-- Step 1: Email Form -->
        <form method="POST" id="emailForm" autocomplete="off" style="display: block;">
          @csrf
          <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
            <i class="bi bi-key me-2"></i>Reset Your Password
          </h3>

          <div class="text-center mb-4">
            <div class="forgot-icon mb-3">
              <i class="bi bi-envelope-exclamation" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
            </div>
            <p class="text-muted">Enter your email address and we'll send you a verification code.</p>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Enter your registered email"
                required
              >
            </div>
          </div>

          <button type="submit" class="btn btn-login mb-3" id="sendCodeButton">
            <i class="bi bi-send me-2"></i> Send Verification Code
          </button>

          <div class="text-center">
            <a href="{{ route('employee.login') }}" class="btn-forgot">
              <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
          </div>

          <hr class="my-4">
        </form>

        <!-- Step 2: OTP Verification Form -->
        <form id="otpForm" style="display: none;">
          @csrf
          <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
            <i class="bi bi-shield-check me-2"></i>Verify Your Identity
          </h3>

          <div class="text-center mb-4">
            <div class="forgot-icon mb-3">
              <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
            </div>
            <p class="text-muted">We've sent a 6-digit verification code to:</p>
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
            <button type="button" class="btn-forgot" id="backToEmailButton">
              <i class="bi bi-arrow-left me-1"></i> Change Email
            </button>
          </div>

          <hr class="my-4">
        </form>

        <!-- Step 3: New Password Form -->
        <form id="passwordForm" style="display: none;">
          @csrf
          <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
            <i class="bi bi-key me-2"></i>Create New Password
          </h3>

          <div class="text-center mb-4">
            <div class="forgot-icon mb-3">
              <i class="bi bi-key" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
            </div>
            <p class="text-muted">Create a strong password for your account.</p>
          </div>

          <div class="mb-3">
            <label for="new_password" class="form-label fw-semibold">New Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input
                type="password"
                class="form-control"
                id="new_password"
                name="new_password"
                placeholder="Enter new password"
                required
              >
              <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="password-strength" id="passwordStrength"></div>
          </div>

          <div class="mb-3">
            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm new password"
                required
              >
              <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="invalid-feedback" id="confirmPasswordError"></div>
          </div>

          <div class="mb-3">
            <small class="text-muted">
              <i class="bi bi-info-circle me-1"></i>
              Password must be at least 8 characters with uppercase, lowercase, number, and special character.
            </small>
          </div>

          <button type="submit" class="btn btn-login mb-3" id="resetPasswordButton">
            <i class="bi bi-check-circle me-2"></i> Reset Password
          </button>

          <div class="text-center">
            <a href="{{ route('employee.login') }}" class="btn-forgot">
              <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
          </div>

          <hr class="my-4">
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Global variables
      let otpTimer;
      let otpTimeLeft = 600; // 10 minutes in seconds
      let currentUserEmail = '';
      let otpToken = '';

      // DOM elements
      const emailForm = document.getElementById('emailForm');
      const otpForm = document.getElementById('otpForm');
      const passwordForm = document.getElementById('passwordForm');
      const sendCodeButton = document.getElementById('sendCodeButton');
      const verifyOtpButton = document.getElementById('verifyOtpButton');
      const resendOtpButton = document.getElementById('resendOtpButton');
      const resetPasswordButton = document.getElementById('resetPasswordButton');
      const backToEmailButton = document.getElementById('backToEmailButton');
      const otpInput = document.getElementById('otp_code');
      const otpError = document.getElementById('otpError');
      const otpEmailDisplay = document.getElementById('otpEmailDisplay');
      const otpTimerDisplay = document.getElementById('otpTimer');
      const newPasswordInput = document.getElementById('new_password');
      const confirmPasswordInput = document.getElementById('confirm_password');
      const passwordStrength = document.getElementById('passwordStrength');
      const confirmPasswordError = document.getElementById('confirmPasswordError');
      const toggleNewPassword = document.getElementById('toggleNewPassword');
      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

      // Password toggle functionality
      toggleNewPassword.addEventListener('click', function() {
        const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        newPasswordInput.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });

      toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });

      // Password strength checker
      function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = [];

        if (password.length >= 8) strength++;
        else feedback.push('At least 8 characters');

        if (/[a-z]/.test(password)) strength++;
        else feedback.push('Lowercase letter');

        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('Uppercase letter');

        if (/[0-9]/.test(password)) strength++;
        else feedback.push('Number');

        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else feedback.push('Special character');

        return { strength, feedback };
      }

      // Update password strength display
      newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const result = checkPasswordStrength(password);
        
        let strengthText = '';
        let strengthClass = '';

        if (password.length === 0) {
          strengthText = '';
        } else if (result.strength < 3) {
          strengthText = `Weak password. Missing: ${result.feedback.join(', ')}`;
          strengthClass = 'strength-weak';
        } else if (result.strength < 5) {
          strengthText = `Medium strength. Missing: ${result.feedback.join(', ')}`;
          strengthClass = 'strength-medium';
        } else {
          strengthText = 'Strong password!';
          strengthClass = 'strength-strong';
        }

        passwordStrength.textContent = strengthText;
        passwordStrength.className = `password-strength ${strengthClass}`;
      });

      // Confirm password validation
      confirmPasswordInput.addEventListener('input', function() {
        const password = newPasswordInput.value;
        const confirmPassword = this.value;

        if (confirmPassword && password !== confirmPassword) {
          this.classList.add('is-invalid');
          confirmPasswordError.textContent = 'Passwords do not match';
          confirmPasswordError.style.display = 'block';
        } else {
          this.classList.remove('is-invalid');
          confirmPasswordError.style.display = 'none';
        }
      });

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

        if (otpTimeLeft <= 60) {
          otpTimerDisplay.className = 'fw-bold text-danger';
        } else if (otpTimeLeft <= 300) {
          otpTimerDisplay.className = 'fw-bold text-warning';
        } else {
          otpTimerDisplay.className = 'fw-bold text-success';
        }
      }

      function showExpiredMessage() {
        Swal.fire({
          icon: 'warning',
          title: 'Code Expired',
          text: 'Your verification code has expired. Please request a new one.',
          confirmButtonText: 'Request New Code',
          confirmButtonColor: '#667eea'
        }).then((result) => {
          if (result.isConfirmed) {
            resendOTP();
          }
        });
      }

      // Show forms
      function showEmailForm() {
        emailForm.style.display = 'block';
        otpForm.style.display = 'none';
        passwordForm.style.display = 'none';
        if (otpTimer) clearInterval(otpTimer);
      }

      function showOTPForm(email) {
        currentUserEmail = email;
        emailForm.style.display = 'none';
        otpForm.style.display = 'block';
        passwordForm.style.display = 'none';
        otpEmailDisplay.textContent = email;
        otpInput.focus();
        startOTPTimer();
        otpError.textContent = '';
        otpInput.classList.remove('is-invalid');
      }

      function showPasswordForm() {
        emailForm.style.display = 'none';
        otpForm.style.display = 'none';
        passwordForm.style.display = 'block';
        newPasswordInput.focus();
        if (otpTimer) clearInterval(otpTimer);
      }

      // Step 1: Send verification code
      emailForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const submitButton = sendCodeButton;
        const originalText = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sending Code...';

        const formData = new FormData();
        formData.append('email', email);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("employee.forgot_password.send_code") }}', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            let message = data.message;
            if (data.dev_otp) {
              message = `Verification code sent! Use code: ${data.dev_otp}`;
            }

            Swal.fire({
              icon: 'success',
              title: 'Code Sent!',
              text: message,
              timer: 3000,
              showConfirmButton: false
            });
            showOTPForm(email);
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message,
              confirmButtonColor: '#667eea'
            });
          }
        })
        .catch(error => {
          console.error('Send code error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to send verification code. Please try again.',
            confirmButtonColor: '#667eea'
          });
        })
        .finally(() => {
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        });
      });

      // Step 2: Verify OTP
      otpForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const otpCode = otpInput.value.trim();
        if (otpCode.length !== 6) {
          showOTPError('Please enter a 6-digit verification code.');
          return;
        }

        const submitButton = verifyOtpButton;
        const originalText = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Verifying...';

        const formData = new FormData();
        formData.append('otp_code', otpCode);
        formData.append('email', currentUserEmail);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("employee.forgot_password.verify_otp") }}', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            otpToken = data.token;
            Swal.fire({
              icon: 'success',
              title: 'Verified!',
              text: 'Code verified successfully. You can now reset your password.',
              timer: 2000,
              showConfirmButton: false
            });
            showPasswordForm();
          } else {
            showOTPError(data.message);
          }
        })
        .catch(error => {
          console.error('OTP verification error:', error);
          showOTPError('Unable to verify code. Please try again.');
        })
        .finally(() => {
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        });
      });

      // Step 3: Reset password
      passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Validate passwords
        if (newPassword !== confirmPassword) {
          confirmPasswordInput.classList.add('is-invalid');
          confirmPasswordError.textContent = 'Passwords do not match';
          confirmPasswordError.style.display = 'block';
          return;
        }

        const passwordCheck = checkPasswordStrength(newPassword);
        if (passwordCheck.strength < 5) {
          Swal.fire({
            icon: 'error',
            title: 'Weak Password',
            text: `Password must include: ${passwordCheck.feedback.join(', ')}`,
            confirmButtonColor: '#667eea'
          });
          return;
        }

        const submitButton = resetPasswordButton;
        const originalText = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Resetting...';

        const formData = new FormData();
        formData.append('email', currentUserEmail);
        formData.append('token', otpToken);
        formData.append('password', newPassword);
        formData.append('password_confirmation', confirmPassword);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("employee.forgot_password.reset") }}', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Password Reset Successfully!',
              text: 'Your password has been reset. You can now login with your new password.',
              confirmButtonText: 'Go to Login',
              confirmButtonColor: '#667eea'
            }).then(() => {
              window.location.href = '{{ route("employee.login") }}';
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Reset Failed',
              text: data.message,
              confirmButtonColor: '#667eea'
            });
          }
        })
        .catch(error => {
          console.error('Password reset error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to reset password. Please try again.',
            confirmButtonColor: '#667eea'
          });
        })
        .finally(() => {
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        });
      });

      // Resend OTP
      function resendOTP() {
        const submitButton = resendOtpButton;
        const originalText = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sending...';

        const formData = new FormData();
        formData.append('email', currentUserEmail);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("employee.forgot_password.resend_code") }}', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Code Resent!',
              text: data.message,
              timer: 2000,
              showConfirmButton: false
            });
            startOTPTimer();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Resend Failed',
              text: data.message,
              confirmButtonColor: '#667eea'
            });
          }
        })
        .catch(error => {
          console.error('Resend OTP error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to resend code. Please try again.',
            confirmButtonColor: '#667eea'
          });
        })
        .finally(() => {
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

      backToEmailButton.addEventListener('click', function() {
        Swal.fire({
          title: 'Change Email?',
          text: 'Are you sure you want to go back and use a different email?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, change email',
          cancelButtonText: 'Continue with OTP',
          confirmButtonColor: '#667eea'
        }).then((result) => {
          if (result.isConfirmed) {
            showEmailForm();
          }
        });
      });

      // OTP input formatting
      otpInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');

        if (this.classList.contains('is-invalid')) {
          this.classList.remove('is-invalid');
          otpError.style.display = 'none';
        }

        if (this.value.length === 6) {
          setTimeout(() => {
            otpForm.dispatchEvent(new Event('submit'));
          }, 500);
        }
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
