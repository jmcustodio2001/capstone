    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
    <style>
        .settings-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
        background: #fff;
        }
        .settings-card .card-header {
        background: #f8f9fa;
        font-weight: bold;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #eaeaea;
        }
        .form-control, .form-select {
        border-radius: 8px;
        }
        .btn {
        border-radius: 6px;
        }
    </style>
    </head>
    <body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

     <main id="main-content" class="container py-4" style="margin-top: 3.5rem;">

    <!-- Header -->
    <div class="page-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
            <h2 class="fw-bold mb-1">Admin Settings</h2>
            <p class="text-muted mb-0">Update your admin account settings.</p>
            </div>
        </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="card settings-card">
        <div class="card-header">
        Admin Information
        </div>
        <div class="card-body">
        <form method="POST" action="{{ route('admin.updateSettings') }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Admin ID</label>
                <input type="text" class="form-control" name="id" value="{{ $admin->id }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="{{ $admin->name }}">
            </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ $admin->email }}">
            </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="password" placeholder="Enter new password" required title="Password must be at least 12 characters long and contain at least one uppercase letter, one number, and one symbol">
                <div id="password-feedback" class="mt-2"></div>
                <small class="form-text text-muted">Password must contain: at least 12 characters, 1 uppercase letter, 1 number, 1 symbol</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select class="form-select" name="role">
                <option value="superadmin" {{ $admin->role == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ $admin->role == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="editor" {{ $admin->role == 'editor' ? 'selected' : '' }}>Editor</option>
                </select>
            </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Created At</label>
                <input type="text" class="form-control" value="{{ $admin->created_at }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Updated At</label>
                <input type="text" class="form-control" value="{{ $admin->updated_at }}" readonly>
            </div>
            </div>

            <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="showPasswordModal()">Save Changes</button>
            </div>
        </form>
        </div>
    </div>

    </main>

    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="passwordModalLabel">Enter Your Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="confirmPassword" class="form-label">Your Password</label>
              <input type="password" class="form-control" id="confirmPassword" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="backToSecurity()">Back</button>
            <button type="button" class="btn btn-primary" onclick="confirmPassword()">Confirm</button>
          </div>
        </div>
      </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('new_password');
        const feedbackDiv = document.getElementById('password-feedback');

        function validatePassword(password) {
            const requirements = [
                { regex: /.{12,}/, message: 'At least 12 characters' },
                { regex: /[A-Z]/, message: 'At least one uppercase letter' },
                { regex: /\d/, message: 'At least one number' },
                { regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/, message: 'At least one symbol' }
            ];

            let validCount = 0;
            let feedbackHtml = '<ul class="list-unstyled mb-0">';

            requirements.forEach(req => {
                if (req.regex.test(password)) {
                    feedbackHtml += '<li class="text-success"><i class="bi bi-check-circle-fill"></i> ' + req.message + '</li>';
                    validCount++;
                } else {
                    feedbackHtml += '<li class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + req.message + '</li>';
                }
            });

            feedbackHtml += '</ul>';

            feedbackDiv.innerHTML = feedbackHtml;

            // Change input border color based on validity
            if (validCount === requirements.length) {
                passwordInput.classList.remove('is-invalid');
                passwordInput.classList.add('is-valid');
            } else {
                passwordInput.classList.remove('is-valid');
                passwordInput.classList.add('is-invalid');
            }

            return validCount === requirements.length;
        }

        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
        });

        // Validate on form submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (!validatePassword(passwordInput.value)) {
                e.preventDefault();
                alert('Please ensure your password meets all requirements.');
            }
        });
    });

    function showPasswordModal() {
        const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
        modal.show();
    }

    function confirmPassword() {
        const password = document.getElementById('confirmPassword').value;
        if (!password) {
            alert('Please enter your password.');
            return;
        }
        // Check if password matches the new password field
        const newPassword = document.getElementById('new_password').value;
        if (password !== newPassword) {
            alert('Password does not match the new password.');
            return;
        }
        // Submit the form
        document.querySelector('form').submit();
    }

    </script>
    </body>
    </html>
