    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Settings</title>
    <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .settings-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
        background: #fff;
        margin-bottom: 2rem;
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
        .profile-picture-container {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 1rem;
        }
        .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e9ecef;
        }
        .profile-picture-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        cursor: pointer;
        }
        .profile-picture-container:hover .profile-picture-overlay {
        opacity: 1;
        }
        .login-history-item {
        border-left: 3px solid #007bff;
        padding-left: 1rem;
        margin-bottom: 1rem;
        }
        .login-history-item.current {
        border-left-color: #28a745;
        background-color: #f8fff9;
        }
        .device-icon {
        font-size: 1.2rem;
        margin-right: 0.5rem;
        }

        /* Sidebar responsive styles */
        @media (min-width: 768px) {
            #main-content {
                margin-left: var(--sidebar-width, 280px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            #main-content.expanded {
                margin-left: 0;
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            #main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    </head>
    <body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

     <main id="main-content" class="container py-4" style="margin-top: 3.5rem; padding-left: 2rem;">

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

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Profile Picture Section -->
    <div class="card settings-card">
        <div class="card-header">
        <i class="bi bi-person-circle me-2"></i>Profile Picture
        </div>
        <div class="card-body text-center">
        <form method="POST" action="{{ route('admin.updateProfilePicture') }}" enctype="multipart/form-data" id="profilePictureForm">
            @csrf
            @method('PUT')

            <div class="profile-picture-container">
                <img src="{{ $admin->profile_picture ? asset('storage/profile_pictures/' . $admin->profile_picture) : asset('images/default-avatar.png') }}"
                     alt="Profile Picture" class="profile-picture" id="profilePreview"
                     onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                <div class="profile-picture-overlay" onclick="document.getElementById('profilePictureInput').click()">
                    <i class="bi bi-camera-fill text-white" style="font-size: 1.5rem;"></i>
                </div>
            </div>

            <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" style="display: none;" onchange="previewProfilePicture(this)">

            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('profilePictureInput').click()">
                    <i class="bi bi-upload me-1"></i>Choose Picture
                </button>
                <button type="button" class="btn btn-primary btn-sm ms-2" id="uploadBtn" style="display: none;" onclick="verifyAndSubmit('profilePictureForm', 'upload profile picture')">
                    <i class="bi bi-check-lg me-1"></i>Upload
                </button>
            </div>

            <small class="text-muted d-block mt-2">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
        </form>
        </div>
    </div>

    <!-- Admin Information Section -->
    <div class="card settings-card">
        <div class="card-header">
        <i class="bi bi-person-gear me-2"></i>Admin Information
        </div>
        <div class="card-body">
        <form method="POST" action="{{ route('admin.updateSettings') }}" id="adminInfoForm">
            @csrf
            @method('PUT')

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Admin ID</label>
                <input type="text" class="form-control" name="id" value="{{ $admin->id }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="{{ $admin->name }}" required>
            </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ $admin->email }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="{{ ucfirst($admin->role) }}" readonly>
            </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Created At</label>
                <input type="text" class="form-control" value="{{ $admin->created_at->format('M d, Y h:i A') }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Updated</label>
                <input type="text" class="form-control" value="{{ $admin->updated_at->format('M d, Y h:i A') }}" readonly>
            </div>
            </div>

            <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="verifyAndSubmit('adminInfoForm', 'update admin information')">
                <i class="bi bi-check-lg me-1"></i>Update Information
            </button>
            </div>
        </form>
        </div>
    </div>

    <!-- Password Management Section -->
    <div class="card settings-card">
        <div class="card-header">
        <i class="bi bi-shield-lock me-2"></i>Password Management
        </div>
        <div class="card-body">
        <form method="POST" action="{{ route('admin.updatePassword') }}" id="passwordForm">
            @csrf
            @method('PUT')

            <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter your current password" required>
            </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="password" placeholder="Enter new password" required>
                <div id="password-feedback" class="mt-2"></div>
                <small class="form-text text-muted">Password must contain: at least 12 characters, 1 uppercase letter, 1 number, 1 symbol</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="password_confirmation" placeholder="Confirm new password" required>
                <div id="confirm-feedback" class="mt-2"></div>
            </div>
            </div>

            <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-warning" onclick="verifyAndSubmit('passwordForm', 'update password')">
                <i class="bi bi-key me-1"></i>Update Password
            </button>
            </div>
        </form>
        </div>
    </div>

    <!-- Login History Section -->
    <div class="card settings-card">
        <div class="card-header">
        <i class="bi bi-clock-history me-2"></i>Login History & Devices
        </div>
        <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
                <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                <h6 class="mt-2 mb-1">Last Login</h6>
                <p class="text-muted mb-0">{{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y h:i A') : 'Never' }}</p>
            </div>
            </div>
            <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
                <i class="bi bi-geo-alt text-success" style="font-size: 2rem;"></i>
                <h6 class="mt-2 mb-1">Last IP Address</h6>
                <p class="text-muted mb-0">{{ $admin->last_login_ip ?? 'Unknown' }}</p>
            </div>
            </div>
            <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
                <i class="bi bi-browser-chrome text-info" style="font-size: 2rem;"></i>
                <h6 class="mt-2 mb-1">Last Browser</h6>
                <p class="text-muted mb-0">{{ $admin->last_user_agent ?? 'Unknown' }}</p>
            </div>
            </div>
        </div>

        <h6 class="mb-3">Recent Login Sessions</h6>
        <div class="login-history">
            @if(isset($loginHistory) && $loginHistory->count() > 0)
                @foreach($loginHistory as $index => $login)
                <div class="login-history-item {{ $index === 0 ? 'current' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                        @if(str_contains(strtolower($login->user_agent ?? ''), 'mobile'))
                            <i class="bi bi-phone device-icon text-primary"></i>
                        @elseif(str_contains(strtolower($login->user_agent ?? ''), 'tablet'))
                            <i class="bi bi-tablet device-icon text-info"></i>
                        @else
                            <i class="bi bi-laptop device-icon text-secondary"></i>
                        @endif
                        <strong>{{ $login->ip_address }}</strong>
                        @if($index === 0)
                            <span class="badge bg-success ms-2">Current Session</span>
                        @endif
                        </div>
                        <p class="text-muted mb-1">
                        <i class="bi bi-clock me-1"></i>{{ $login->created_at->format('M d, Y h:i A') }}
                        </p>
                        <p class="text-muted mb-0 small">
                        <i class="bi bi-browser-chrome me-1"></i>{{ $login->user_agent ?? 'Unknown Browser' }}
                        </p>
                    </div>
                    <div class="text-end">
                        @if($index !== 0)
                        <button class="btn btn-outline-danger btn-sm" onclick="verifyAndRevokeSession('{{ $login->id }}')"
                                title="Revoke this session">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        @endif
                    </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-4">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No login history available</p>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Security Actions</h6>
                <small class="text-muted">Manage your account security</small>
            </div>
            <div>
                <button class="btn btn-outline-warning btn-sm me-2" onclick="verifyAndLogoutAllDevices()">
                <i class="bi bi-power me-1"></i>Logout All Devices
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="refreshLoginHistory()">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
            </div>
        </div>
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
    // Password verification and SweetAlert functions
    async function verifyPassword() {
        const result = await Swal.fire({
            title: 'Security Verification',
            text: 'Please enter your current password to continue',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            showCancelButton: true,
            confirmButtonText: 'Verify',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Password is required!'
                }
            }
        });

        // Return null if user cancelled or dismissed the dialog
        if (!result.isConfirmed) {
            return null;
        }

        return result.value;
    }

    async function verifyAndSubmit(formId, action) {
        const password = await verifyPassword();
        if (password) {
            // Verify password with server
            try {
                const response = await fetch('/admin/verify-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ password: password })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Confirmed!',
                        text: `Proceeding to ${action}...`,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        document.getElementById(formId).submit();
                    });
                } else {
                    Swal.fire({
                        title: 'Invalid Password',
                        text: 'The password you entered is incorrect.',
                        icon: 'error'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to verify password. Please try again.',
                    icon: 'error'
                });
            }
        }
    }

    async function verifyAndRevokeSession(sessionId) {
        const password = await verifyPassword();
        if (!password) {
            return; // User cancelled or didn't enter password
        }

        try {
            const response = await fetch('/admin/verify-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: password })
            });

            const result = await response.json();

            if (result.success) {
                revokeSession(sessionId);
            } else {
                Swal.fire({
                    title: 'Invalid Password',
                    text: 'The password you entered is incorrect.',
                    icon: 'error'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'Failed to verify password. Please try again.',
                icon: 'error'
            });
        }
    }

    async function verifyAndLogoutAllDevices() {
        const password = await verifyPassword();
        if (!password) {
            return; // User cancelled or didn't enter password
        }

        try {
            const response = await fetch('/admin/verify-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: password })
            });

            const result = await response.json();

            if (result.success) {
                logoutAllDevices();
            } else {
                Swal.fire({
                    title: 'Invalid Password',
                    text: 'The password you entered is incorrect.',
                    icon: 'error'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'Failed to verify password. Please try again.',
                icon: 'error'
            });
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        const feedbackDiv = document.getElementById('password-feedback');
        const confirmFeedbackDiv = document.getElementById('confirm-feedback');

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

            if (feedbackDiv) {
                feedbackDiv.innerHTML = feedbackHtml;

                // Change input border color based on validity
                if (validCount === requirements.length) {
                    passwordInput.classList.remove('is-invalid');
                    passwordInput.classList.add('is-valid');
                } else {
                    passwordInput.classList.remove('is-valid');
                    passwordInput.classList.add('is-invalid');
                }
            }

            return validCount === requirements.length;
        }

        function validatePasswordConfirmation() {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;

            if (confirmPassword === '') {
                confirmFeedbackDiv.innerHTML = '';
                confirmInput.classList.remove('is-valid', 'is-invalid');
                return false;
            }

            if (password === confirmPassword) {
                confirmFeedbackDiv.innerHTML = '<small class="text-success"><i class="bi bi-check-circle-fill"></i> Passwords match</small>';
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid');
                return true;
            } else {
                confirmFeedbackDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle-fill"></i> Passwords do not match</small>';
                confirmInput.classList.remove('is-valid');
                confirmInput.classList.add('is-invalid');
                return false;
            }
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                validatePassword(this.value);
                if (confirmInput.value) {
                    validatePasswordConfirmation();
                }
            });
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', validatePasswordConfirmation);
        }

        // Validate password form on submit
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const isPasswordValid = validatePassword(passwordInput.value);
                const isConfirmValid = validatePasswordConfirmation();

                if (!isPasswordValid || !isConfirmValid) {
                    e.preventDefault();
                    alert('Please ensure your password meets all requirements and confirmation matches.');
                }
            });
        }
    });

    // Profile picture preview function
    function previewProfilePicture(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                input.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF)');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
                document.getElementById('uploadBtn').style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    }

    // Login history management functions
    function revokeSession(sessionId) {
        Swal.fire({
            title: 'Revoke Session?',
            text: 'Are you sure you want to revoke this login session?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, revoke it!'
        }).then((result) => {
            if (result.isConfirmed) {
            fetch(`/admin/revoke-session/${sessionId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Session has been revoked successfully.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to revoke session: ' + (data.message || 'Unknown error'),
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while revoking the session',
                    icon: 'error'
                });
            });
            }
        });
    }

    function logoutAllDevices() {
        Swal.fire({
            title: 'Logout All Devices?',
            text: 'Are you sure you want to logout from all devices? You will need to login again.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, logout all!'
        }).then((result) => {
            if (result.isConfirmed) {
            fetch('/admin/logout-all-devices', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Successfully logged out from all devices',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = '/admin/login';
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to logout from all devices: ' + (data.message || 'Unknown error'),
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while logging out from all devices',
                    icon: 'error'
                });
            });
            }
        });
    }

    function refreshLoginHistory() {
        location.reload();
    }

    // Legacy functions for backward compatibility
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

    function backToSecurity() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
        modal.hide();
    }

    // Initialize sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        console.log('Initializing sidebar state:', { sidebarCollapsed, sidebar: !!sidebar, mainContent: !!mainContent });

        if (sidebar && mainContent) {
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                sidebar.style.transform = 'translateX(-100%)';
                mainContent.classList.add('expanded');
                console.log('Applied collapsed state');
            } else {
                sidebar.classList.remove('collapsed');
                sidebar.style.transform = 'translateX(0)';
                mainContent.classList.remove('expanded');
                console.log('Applied expanded state');
            }
        }
    });

    </script>
    </body>
    </html>
