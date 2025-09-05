<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Employee Settings</title>
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

<main id="main-content" class="container py-4" style="margin-top: 30px;">

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

  <!-- Settings Form -->
  <div class="card settings-card">
    <div class="card-header">
      <i class="bi bi-person-gear me-2"></i>Employee Information
    </div>
    <div class="card-body p-4">
      <form method="POST" action="/employee/settings" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="employee_password" id="employee-password">

        <!-- Basic Information Section -->
        <div class="settings-section">
          <div class="settings-section-title">
            <i class="bi bi-info-circle"></i>Basic Information
          </div>
          <div class="settings-grid">
            <div class="settings-field">
              <label class="form-label">Employee ID</label>
              <input type="text" class="form-control readonly-field" name="employee_id" value="{{ $employee->employee_id }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Position</label>
              <input type="text" class="form-control readonly-field" name="position" value="{{ $employee->position }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control readonly-field" name="first_name" value="{{ $employee->first_name }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control readonly-field" name="last_name" value="{{ $employee->last_name }}" readonly>
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
              <input type="email" class="form-control readonly-field" name="email" value="{{ $employee->email }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Phone Number</label>
              <input type="text" class="form-control readonly-field" name="phone_number" value="{{ $employee->phone_number }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Address</label>
              <input type="text" class="form-control readonly-field" name="address" value="{{ $employee->address }}" readonly>
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
              <input type="date" class="form-control readonly-field" name="hire_date" value="{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : '' }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Department</label>
              <input type="text" class="form-control" name="department_id" value="{{ $employee->department_id }}">
            </div>

            <div class="settings-field">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="Active" {{ $employee->status == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $employee->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="On Leave" {{ $employee->status == 'On Leave' ? 'selected' : '' }}>On Leave</option>
              </select>
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
              <img src="{{ asset('storage/'.$employee->profile_picture) }}" alt="Profile Picture" class="profile-img-preview me-4">
            @endif
            <div class="flex-grow-1">
              <input type="file" class="form-control" name="profile_picture">
              <small class="form-text text-muted">Recommended: Square image, at least 200x200 pixels</small>
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
              <input type="text" class="form-control readonly-field" value="{{ $employee->created_at }}" readonly>
            </div>

            <div class="settings-field">
              <label class="form-label">Updated At</label>
              <input type="text" class="form-control readonly-field" value="{{ $employee->updated_at }}" readonly>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
          <button type="reset" class="btn btn-light me-3">Reset</button>
          <button type="button" class="btn btn-primary" id="save-changes-btn">Save Changes</button>
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

  // Handle save changes button click with password verification
  const form = document.querySelector('form');
  const saveBtn = document.getElementById('save-changes-btn');
  console.log('Form found:', form);
  console.log('Save button found:', saveBtn);
  console.log('Form method:', form ? form.method : 'Form not found');
  console.log('Form action:', form ? form.action : 'Form not found');

  if (!form || !saveBtn) {
    console.error('Form or save button not found! Cannot attach event listener.');
    return;
  }

  saveBtn.addEventListener('click', function(e) {
    console.log('Save changes button clicked');
    console.log('Event target:', e.target);
    console.log('Event type:', e.type);

    // Validate new password if provided
    const newPassword = passwordInput ? passwordInput.value : '';
    if (newPassword.trim() && !validatePassword(newPassword)) {
      alert('Please ensure your new password meets all requirements.');
      return;
    }

    // Validate confirm password if new password is provided
    if (newPassword.trim() && !validateConfirmPassword()) {
      alert('Please ensure your confirm password matches the new password.');
      return;
    }

    // Check if SweetAlert is available
    if (typeof Swal === 'undefined') {
      console.error('SweetAlert not available! Cannot show popup.');
      alert('SweetAlert library not loaded. Please refresh the page.');
      return;
    }

    // Show SweetAlert password verification popup
    console.log('About to show SweetAlert password popup');
    Swal.fire({
      title: 'Enter Password',
      text: 'Please enter your password to confirm saving these changes.',
      input: 'password',
      inputPlaceholder: 'Enter your password',
      inputAttributes: {
        autocapitalize: 'off',
        autocorrect: 'off'
      },
      showCancelButton: true,
      confirmButtonText: 'Verify',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#4299e1',
      cancelButtonColor: '#6c757d',
      preConfirm: async (password) => {
        console.log('Password entered:', password ? 'Yes' : 'No');
        if (!password.trim()) {
          console.log('Password validation failed: empty password');
          Swal.showValidationMessage('Password is required');
          return false;
        }

        try {
          console.log('Starting password verification AJAX request');
          const csrfToken = document.querySelector('meta[name="csrf-token"]');
          console.log('CSRF token found:', csrfToken ? 'Yes' : 'No');
          console.log('CSRF token value:', csrfToken ? csrfToken.getAttribute('content') : 'N/A');

          // Verify password with backend
          const response = await fetch('/employee/verify-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
            },
            body: JSON.stringify({ password: password })
          });

          console.log('AJAX response status:', response.status);
          console.log('AJAX response ok:', response.ok);

          const result = await response.json();
          console.log('AJAX response result:', result);

          if (!result.success) {
            console.log('Password verification failed:', result.message);
            throw new Error(result.message || 'The password you entered is incorrect.');
          }

          console.log('Password verification successful');
          return password;
        } catch (error) {
          console.log('Password verification error:', error.message);
          Swal.showValidationMessage(error.message || 'An error occurred while verifying your password. Please try again.');
          return false;
        }
      }
    }).then((result) => {
      console.log('SweetAlert password popup result:', result);
      console.log('Result isConfirmed:', result.isConfirmed);
      console.log('Result value:', result.value);

      if (result.isConfirmed && result.value) {
        const password = result.value;
        console.log('Password confirmed, setting hidden field');
        document.getElementById('employee-password').value = password;

        // Show confirmation dialog with enhanced styling
        console.log('About to show confirmation dialog');
        Swal.fire({
          title: 'Save Changes?',
          html: '<div class="text-center"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i><br><br>Your password has been verified successfully.<br><strong>Do you want to save these changes to your profile?</strong></div>',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#28a745',
          cancelButtonColor: '#dc3545',
          confirmButtonText: '<i class="bi bi-check-lg"></i> Yes, Save Changes!',
          cancelButtonText: '<i class="bi bi-x-lg"></i> Cancel',
          customClass: {
            popup: 'swal2-popup-custom',
            confirmButton: 'btn btn-success px-4',
            cancelButton: 'btn btn-danger px-4'
          },
          buttonsStyling: false,
          allowOutsideClick: false,
          allowEscapeKey: false
        }).then((confirmResult) => {
          console.log('Confirmation dialog result:', confirmResult);
          console.log('Confirmation isConfirmed:', confirmResult.isConfirmed);

          if (confirmResult.isConfirmed) {
            console.log('User confirmed, submitting form');
            
            // Show loading state
            Swal.fire({
              title: 'Saving Changes...',
              html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Please wait while we update your profile information.</div>',
              allowOutsideClick: false,
              allowEscapeKey: false,
              showConfirmButton: false,
              customClass: {
                popup: 'swal2-popup-custom'
              }
            });
            
            // Submit the form
            const submitBtn = document.getElementById('save-changes-btn');
            console.log('Submit button found:', submitBtn);
            if (submitBtn) {
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
              console.log('Form submitting...');
              
              // Add a small delay to show the loading state
              setTimeout(() => {
                form.submit();
              }, 500);
            } else {
              console.error('Submit button not found!');
              Swal.fire({
                title: 'Error!',
                text: 'Unable to save changes. Please refresh the page and try again.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
              });
            }
          } else {
            console.log('User cancelled confirmation');
            // Show cancellation message
            Swal.fire({
              title: 'Changes Not Saved',
              text: 'Your profile changes have been cancelled.',
              icon: 'info',
              confirmButtonColor: '#6c757d',
              timer: 2000,
              timerProgressBar: true
            });
          }
        });
      } else {
        console.log('Password popup was cancelled or failed');
      }
    });
  });
});
</script>
</body>
</html>
