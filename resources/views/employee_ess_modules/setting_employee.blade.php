<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Employee Settings</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    .settings-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      background: #fff;
      overflow: hidden;
    }
    .settings-card .card-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      font-weight: 600;
      padding: 1.2rem 1.8rem;
      border-bottom: 1px solid #eaeaea;
      font-size: 1.2rem;
      color: #2c3e50;
    }
    .form-control, .form-select {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      border: 1px solid #ddd;
      transition: all 0.3s;
    }
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
      border-color: #4299e1;
    }
    .btn {
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: all 0.2s;
    }
    .btn-primary {
      background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
      border: none;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
    }
    .profile-img-preview {
      max-width: 120px;
      border-radius: 50%;
      margin-bottom: 10px;
      border: 3px solid #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: #4a5568;
    }
    .settings-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    .settings-field {
      margin-bottom: 1.2rem;
    }
    .settings-section {
      background: #fafbfc;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #4299e1;
    }
    .settings-section-title {
      font-weight: 600;
      margin-bottom: 1.2rem;
      color: #2c3e50;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
    }
    .settings-section-title i {
      margin-right: 0.5rem;
      color: #4299e1;
    }
    .readonly-field {
      background-color: #f8f9fa;
      color: #6c757d;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<main id="main-content" class="container py-4 main-content-transition" style="margin-top: 3.5rem; max-width: 1000px;">
<style>
  /* Sidebar toggle responsive fix */
  .main-content-transition {
    transition: margin-left 0.3s;
    margin-left: 270px;
  }
  .sidebar-collapsed ~ #main-content,
  .sidebar-collapsed #main-content,
  .main-content-collapsed {
    margin-left: 80px !important;
  }
</style>
<script>
// Sidebar toggle fix: listen for sidebar toggle and adjust main content margin
document.addEventListener('DOMContentLoaded', function() {
  // ...existing code...

  // Sidebar toggle logic
  // Assumes sidebar has id 'employee-sidebar' and toggle button has id 'sidebar-toggle-btn'
  const sidebar = document.getElementById('employee-sidebar') || document.querySelector('.employee-sidebar');
  const mainContent = document.getElementById('main-content');
  const toggleBtn = document.getElementById('sidebar-toggle-btn') || document.querySelector('.sidebar-toggle-btn');

  function updateMainContentMargin() {
    if (sidebar && sidebar.classList.contains('collapsed')) {
      mainContent.classList.add('main-content-collapsed');
    } else {
      mainContent.classList.remove('main-content-collapsed');
    }
  }

  if (toggleBtn && sidebar && mainContent) {
    toggleBtn.addEventListener('click', function() {
      setTimeout(updateMainContentMargin, 10); // Wait for sidebar class to update
    });
    // Initial state
    updateMainContentMargin();
  }
});
</script>

  <!-- Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">Employee Settings</h2>
          <p class="text-muted mb-0">Update your employee profile and account settings.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
      // Show success message and update sidebar status
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
          });
        }
        
        // Update sidebar status to match saved status
        const currentStatus = '{{ $employee->status ?? 'Active' }}';
        console.log('Page loaded with saved status:', currentStatus);
        
        // Update sidebar badge to reflect saved status
        setTimeout(function() {
          const sidebarStatusBadge = document.getElementById('sidebar-status-badge');
          if (sidebarStatusBadge) {
            sidebarStatusBadge.textContent = currentStatus;
            sidebarStatusBadge.className = 'badge mt-2';
            switch(currentStatus) {
              case 'Active':
                sidebarStatusBadge.classList.add('bg-success', 'text-white');
                break;
              case 'Inactive':
                sidebarStatusBadge.classList.add('bg-secondary', 'text-white');
                break;
              case 'On Leave':
                sidebarStatusBadge.classList.add('bg-warning', 'text-dark');
                break;
              default:
                sidebarStatusBadge.classList.add('bg-info', 'text-white');
            }
            sidebarStatusBadge.style.fontSize = '0.85rem';
          }
        }, 100);
      });
    </script>
  @endif

  @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      @foreach($errors->all() as $error)
        {{ $error }}<br>
      @endforeach
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <!-- Settings Form -->
  <div class="card settings-card">
    <div class="card-header">
      <i class="bi bi-person-gear me-2"></i>Employee Information
    </div>
    <div class="card-body p-4">
      <form method="POST" action="{{ route('employee.settings.fix') }}" enctype="multipart/form-data" id="settings-form">
        @csrf

        <!-- Basic Information Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-info-circle"></i>Basic Information
          </div>
          <div class="settings-grid">
            <div class="settings-field">
              <label class="form-label">Employee ID</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->employee_id }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Position</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->position }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->first_name }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->last_name }}" readonly>
            </div>
          </div>
        </div>

        <!-- Contact Information Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-telephone"></i>Contact Information
          </div>
          <div class="settings-grid">
            <div class="settings-field">
              <label class="form-label">Email</label>
              <input type="email" class="form-control readonly-field" value="{{ $employee->email }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Phone Number</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->phone_number }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Address</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->address }}" readonly>
            </div>
          </div>
        </div>

        <!-- Employment Details Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-briefcase"></i>Employment Details
          </div>
          <div class="settings-grid">
            <div class="settings-field">
              <label class="form-label">Hire Date</label>
              <input type="date" class="form-control readonly-field" value="{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : '' }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Department</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->department_id }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Employment Status</label>
              <select class="form-select" name="status" id="employment-status" required>
                <option value="Active" {{ ($employee->status ?? 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ ($employee->status ?? 'Active') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="On Leave" {{ ($employee->status ?? 'Active') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
              </select>
              <small class="form-text text-muted">Current status: <strong>{{ $employee->status ?? 'Active' }}</strong></small>
            </div>

            <div class="settings-field">
              <label class="form-label">Online Status</label>
              <div class="d-flex align-items-center">
                <span id="online-status" class="badge bg-secondary me-2">
                  <i class="bi bi-circle-fill me-1"></i>Checking...
                </span>
                <small class="text-muted">Real-time connection status</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Security Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-shield-lock"></i>Password Security
          </div>
          <div class="settings-grid">
            <div class="settings-field">
              <label class="form-label">New Password</label>
              <input type="password" class="form-control" id="new_password" name="password" placeholder="Enter new password" title="Password must be at least 12 characters long and contain at least one uppercase letter, one number, and one symbol">
              <div id="password-feedback" class="mt-2"></div>
              <small class="form-text text-muted">Leave blank to keep current password. If entering new password, it must contain: at least 12 characters, 1 uppercase letter, 1 number, 1 symbol</small>
            </div>

            <div class="settings-field">
              <label class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="confirm_password" name="password_confirmation" placeholder="Confirm new password">
              <div id="confirm-password-feedback" class="mt-2"></div>
              <small class="form-text text-muted">Re-enter your new password to confirm</small>
            </div>
          </div>
        </div>

        <!-- Profile Picture Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-image"></i>Profile Picture
          </div>
          <div class="d-flex align-items-center flex-wrap">
            @if($employee->profile_picture)
              <img src="{{ asset('storage/'.$employee->profile_picture) }}" alt="Profile Picture" class="profile-img-preview me-4" id="current-profile-picture">
            @else
              <div class="profile-img-preview me-4 d-flex align-items-center justify-content-center bg-light" id="no-profile-picture">
                <i class="bi bi-person-circle" style="font-size: 4rem; color: #6c757d;"></i>
              </div>
            @endif
            <div class="flex-grow-1">
              <input type="file" class="form-control" name="profile_picture" id="profile-picture-input" accept="image/*">
              <small class="form-text text-muted">Recommended: Square image, at least 200x200 pixels (JPEG, PNG, JPG, GIF - Max: 2MB)</small>
            </div>
          </div>
        </div>


        <!-- System Information Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-database"></i>System Information
          </div>
          <div class="settings-grid">
            <div class="settings-field">
              <label class="form-label">Created At</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->format('Y-m-d') : '' }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Updated At</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->format('Y-m-d') : '' }}" readonly>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
          <button type="reset" class="btn btn-light me-3">Reset</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>


</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  console.log('Employee settings script loaded');
  console.log('Swal available:', typeof Swal);
  console.log('DOM fully loaded');

  // Password validation setup
  const passwordInput = document.getElementById('new_password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const feedbackDiv = document.getElementById('password-feedback');
  const confirmFeedbackDiv = document.getElementById('confirm-password-feedback');

  function validatePassword(password) {
    if (!password.trim()) {
      feedbackDiv.innerHTML = '';
      passwordInput.classList.remove('is-valid', 'is-invalid');
      return true; // Allow empty password (keep current)
    }

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

  if (passwordInput) {
    passwordInput.addEventListener('input', function() {
      validatePassword(this.value);
    });
  }

  // Confirm password validation
  function validateConfirmPassword() {
    const newPassword = passwordInput ? passwordInput.value : '';
    const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

    if (!newPassword.trim()) {
      confirmFeedbackDiv.innerHTML = '';
      confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
      return true;
    }

    if (confirmPassword === newPassword) {
      confirmFeedbackDiv.innerHTML = '<div class="text-success"><i class="bi bi-check-circle-fill"></i> Passwords match</div>';
      confirmPasswordInput.classList.remove('is-invalid');
      confirmPasswordInput.classList.add('is-valid');
      return true;
    } else {
      confirmFeedbackDiv.innerHTML = '<div class="text-danger"><i class="bi bi-x-circle-fill"></i> Passwords do not match</div>';
      confirmPasswordInput.classList.remove('is-valid');
      confirmPasswordInput.classList.add('is-invalid');
      return false;
    }
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function() {
      validateConfirmPassword();
    });
  }

  // Profile picture preview
  const profilePictureInput = document.getElementById('profile-picture-input');
  if (profilePictureInput) {
    profilePictureInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const currentImg = document.getElementById('current-profile-picture');
          const noProfileDiv = document.getElementById('no-profile-picture');

          if (currentImg) {
            currentImg.src = e.target.result;
          } else if (noProfileDiv) {
            noProfileDiv.innerHTML = '<img src="' + e.target.result + '" alt="Profile Picture" class="profile-img-preview" style="max-width: 120px; border-radius: 50%;">';
          }
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Dynamic Online Status - checks actual connectivity
  function updateOnlineStatus() {
    const statusElement = document.getElementById('online-status');
    
    if (navigator.onLine) {
      // Check if we can actually reach the server
      fetch('/employee/ping', { 
        method: 'GET',
        cache: 'no-cache',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (response.ok) {
          statusElement.className = 'badge bg-success me-2';
          statusElement.innerHTML = '<i class="bi bi-circle-fill me-1"></i>Online';
        } else {
          throw new Error('Server not reachable');
        }
      })
      .catch(() => {
        statusElement.className = 'badge bg-warning me-2';
        statusElement.innerHTML = '<i class="bi bi-circle-fill me-1"></i>Connection Issues';
      });
    } else {
      statusElement.className = 'badge bg-danger me-2';
      statusElement.innerHTML = '<i class="bi bi-circle-fill me-1"></i>Offline';
    }
  }

  // Set status immediately and update every 30 seconds
  updateOnlineStatus();
  setInterval(updateOnlineStatus, 30000);

  // Listen for online/offline events
  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);

  // Employment Status Change Handler
  const employmentStatusSelect = document.getElementById('employment-status');
  if (employmentStatusSelect) {
    console.log('Employment status select found:', employmentStatusSelect);
    console.log('Current value:', employmentStatusSelect.value);
    
    employmentStatusSelect.addEventListener('change', function() {
      console.log('Status changed to:', this.value);
      updateSidebarStatus(this.value);
    });
  } else {
    console.error('Employment status select not found!');
  }


  // Function to update sidebar status badge
  function updateSidebarStatus(newStatus) {
    const sidebarStatusBadge = document.getElementById('sidebar-status-badge');
    if (sidebarStatusBadge) {
      // Update text
      sidebarStatusBadge.textContent = newStatus;
      
      // Update badge color based on status
      sidebarStatusBadge.className = 'badge mt-2';
      switch(newStatus) {
        case 'Active':
          sidebarStatusBadge.classList.add('bg-success', 'text-white');
          break;
        case 'Inactive':
          sidebarStatusBadge.classList.add('bg-secondary', 'text-white');
          break;
        case 'On Leave':
          sidebarStatusBadge.classList.add('bg-warning', 'text-dark');
          break;
        default:
          sidebarStatusBadge.classList.add('bg-info', 'text-white');
      }
      sidebarStatusBadge.style.fontSize = '0.85rem';
    }
  }

  // Form submission handler to show success message
  const settingsForm = document.getElementById('settings-form');
  if (settingsForm) {
    settingsForm.addEventListener('submit', function(e) {
      const employmentStatus = employmentStatusSelect ? employmentStatusSelect.value : null;
      
      console.log('Form submitting with status:', employmentStatus);
      console.log('Form action:', settingsForm.action);
      console.log('Form method:', settingsForm.method);
      
      // Ensure the status field has a value
      if (employmentStatusSelect && !employmentStatusSelect.value) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Error!',
            text: 'Please select an employment status.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        } else {
          alert('Please select an employment status.');
        }
        return false;
      }
      
      // Show loading message
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Updating Settings...',
          text: 'Please wait while we save your changes.',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
            Swal.showLoading();
          }
        });
      }
      
      // Let the form submit normally
      return true;
    });
  }

});
</script>
</body>
</html>
