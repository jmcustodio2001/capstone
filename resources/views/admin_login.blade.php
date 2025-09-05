<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Portal - Jetlouge Travels Admin</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css" rel="stylesheet">
  <!-- Login Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/admin_login-style.css') }}">
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
              </ul>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 login-right">
              <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
                Sign In to Your Account
              </h3>
              @if($errors instanceof \Illuminate\Support\ViewErrorBag && $errors->any())
            <div class="alert alert-danger d-none" id="errorAlert">
              {{ $errors->first() }}
              </div>
             @endif
              <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm">
                @csrf
                  <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                      </span>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="{{ old('email') }}">
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                      </span>
                      <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-login mb-3" id="submitBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                  </button>
                  <hr class="my-4">
                </form>
            </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Set CSRF token for AJAX requests
      const token = document.querySelector('meta[name="csrf-token"]');
      if (token) {
        window.Laravel = {
          csrfToken: token.getAttribute('content')
        };
      }

      // Show error message with SweetAlert if there are validation errors
      const errorAlert = document.getElementById('errorAlert');
      if (errorAlert && !errorAlert.classList.contains('d-none')) {
        const errorMessage = errorAlert.textContent.trim();
        if (errorMessage) {
          Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: errorMessage,
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545',
            showClass: {
              popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
              popup: 'animate__animated animate__fadeOutUp'
            }
          });
        }
      }

      // Password toggle functionality
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');

      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
          const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordInput.setAttribute('type', type);

          const icon = this.querySelector('i');
          icon.classList.toggle('bi-eye');
          icon.classList.toggle('bi-eye-slash');
        });
      }

      // Form validation and submission with SweetAlert
      const loginForm = document.getElementById('adminLoginForm');
      const submitBtn = document.getElementById('submitBtn');
      const emailInput = document.getElementById('email');
      
      if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Validate form fields
          const email = emailInput.value.trim();
          const password = passwordInput.value.trim();

          if (!email) {
            Swal.fire({
              icon: 'warning',
              title: 'Email Required',
              text: 'Please enter your email address.',
              confirmButtonColor: '#ffc107'
            });
            emailInput.focus();
            return false;
          }

          if (!password) {
            Swal.fire({
              icon: 'warning',
              title: 'Password Required',
              text: 'Please enter your password.',
              confirmButtonColor: '#ffc107'
            });
            passwordInput.focus();
            return false;
          }

          // Email format validation
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(email)) {
            Swal.fire({
              icon: 'warning',
              title: 'Invalid Email',
              text: 'Please enter a valid email address.',
              confirmButtonColor: '#ffc107'
            });
            emailInput.focus();
            return false;
          }

          // Check if CSRF token exists
          const csrfToken = loginForm.querySelector('input[name="_token"]');
          if (!csrfToken || !csrfToken.value) {
            Swal.fire({
              icon: 'error',
              title: 'Security Error',
              text: 'Security token missing. Please refresh the page and try again.',
              confirmButtonColor: '#dc3545'
            }).then(() => {
              window.location.reload();
            });
            return false;
          }

          // Show loading SweetAlert
          Swal.fire({
            title: 'Signing In...',
            text: 'Please wait while we verify your credentials.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          // Update button state
          const originalText = submitBtn.innerHTML;
          submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Signing In...';
          submitBtn.disabled = true;

          // Submit the form
          setTimeout(() => {
            loginForm.submit();
          }, 500);

          // Re-enable button after a delay in case of errors
          setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
          }, 10000);
        });
      }

      // Add floating animation to shapes
      const shapes = document.querySelectorAll('.shape');
      shapes.forEach((shape, index) => {
        shape.style.animationDelay = `${index * 2}s`;
      });

      // Auto-refresh CSRF token every 30 minutes
      setInterval(function() {
        fetch('/csrf-token')
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
            // Show SweetAlert for CSRF refresh failure
            Swal.fire({
              icon: 'warning',
              title: 'Session Warning',
              text: 'Unable to refresh security token. Please refresh the page if you encounter issues.',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 5000,
              timerProgressBar: true
            });
          });
      }, 30 * 60 * 1000); // 30 minutes

      // Welcome message on page load (optional)
      setTimeout(() => {
        Swal.fire({
          title: 'Welcome to Jetlouge Travels',
          text: 'Admin Portal - Please sign in to continue',
          icon: 'info',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          showClass: {
            popup: 'animate__animated animate__slideInRight'
          },
          hideClass: {
            popup: 'animate__animated animate__slideOutRight'
          }
        });
      }, 1000);
    });
  </script>
</body>
</html>
