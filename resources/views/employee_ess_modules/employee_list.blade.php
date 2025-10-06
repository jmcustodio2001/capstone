<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">

  <!-- Employee Card Styles -->
  <style>
    .employee-card {
      transition: all 0.3s ease;
      border-radius: 12px;
      overflow: hidden;
      background: transparent !important;
      display: flex;
      flex-direction: column;
      border: 1px solid rgba(0,0,0,0.125);
    }

    .employee-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .employee-card:hover .position-absolute.bg-primary {
      opacity: 1 !important;
    }

    .employee-card .card-header {
      position: relative;
      overflow: hidden;
      flex-shrink: 0;
    }

    .employee-card .card-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .employee-card:hover .card-header::before {
      left: 100%;
    }

    .employee-card .card-body {
      flex-grow: 1;
      background: rgba(255,255,255,0.95) !important;
      display: flex;
      flex-direction: column;
    }

    .employee-card .card-footer {
      flex-shrink: 0;
      background-color: rgba(248,249,250,0.95) !important;
      border-top: 1px solid #dee2e6;
    }

    .employee-card .badge {
      transition: all 0.2s ease;
    }

    .employee-card:hover .badge {
      transform: scale(1.05);
    }

    .employee-card .btn {
      transition: all 0.2s ease;
    }

    .employee-card .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Softer hover colors for buttons */
    .employee-card .btn-outline-info:hover {
      background-color: rgba(13, 202, 240, 0.1) !important;
      border-color: #0dcaf0 !important;
      color: #0dcaf0 !important;
    }

    .employee-card .btn-outline-primary:hover {
      background-color: rgba(13, 110, 253, 0.1) !important;
      border-color: #0d6efd !important;
      color: #0d6efd !important;
    }

    .employee-card .btn-outline-danger:hover {
      background-color: rgba(220, 53, 69, 0.1) !important;
      border-color: #dc3545 !important;
      color: #dc3545 !important;
    }

    @media (max-width: 768px) {
      .employee-card-wrapper {
        margin-bottom: 1rem;
      }

      .employee-card .card-header {
        min-height: 70px;
        padding: 1rem;
      }

      .employee-card .card-header img {
        width: 50px;
        height: 50px;
      }

      .employee-card .card-body {
        padding: 1rem;
      }
    }

    .ip-address.bg-success {
      background-color: #28a745 !important;
    }

    .ip-address.bg-secondary {
      background-color: #6c757d !important;
    }

    .ip-address.bg-warning {
      background-color: #ffc107 !important;
      color: #212529 !important;
    }

    .ip-address.bg-danger {
      background-color: #dc3545 !important;
    }

    /* Ensure proper text visibility */
    .employee-card .text-primary {
      color: #0d6efd !important;
    }

    .employee-card .text-success {
      color: #198754 !important;
    }

    .employee-card .text-warning {
      color: #ffc107 !important;
    }

    .employee-card .text-muted {
      color: #6c757d !important;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Employee Management</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Here's your employee directory.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($errors->has('admin_password'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ $errors->first('admin_password') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Employee List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Directory</h4>
        <div class="d-flex gap-2">
          <input type="text" id="employee-search" class="form-control form-control-sm" placeholder="Search employees..." style="width: 200px;">
          <button class="btn btn-primary" onclick="addEmployeeWithConfirmation()">
            <i class="bi bi-plus-lg me-1"></i> Add Employee
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Employee Grid Layout -->
        <div class="row g-4" id="employee-grid-container">
          @forelse($employees as $index => $employee)
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 employee-card-wrapper">
              <div class="card employee-card h-100 shadow-sm border-0 position-relative">

                <!-- Dynamic Header with Gradient -->
                <div class="card-header border-0 text-white position-relative"
                     style="background: linear-gradient(135deg,
                       {{ ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F'][($index % 8)] }} 0%,
                       {{ ['#FF8E8E', '#6EDDD6', '#67C3D1', '#A8D8C4', '#FFE4B5', '#E6B3E6', '#AAE0D0', '#F9E79F'][($index % 8)] }} 100%);
                       border-radius: 12px 12px 0 0; min-height: 80px; display: flex; align-items: center; padding: 1rem;">

                  <!-- Employee Profile Section -->
                  <div class="d-flex align-items-center w-100">
                    <div class="position-relative me-3">
                      <img src="{{ isset($employee['profile_picture']) && $employee['profile_picture'] ? asset('storage/' . $employee['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')) . '&background=ffffff&color=333333&size=64' }}"
                           class="rounded-circle border-3 border-white shadow-sm"
                           width="64" height="64" alt="Profile"
                           style="object-fit: cover;">

                      <!-- Online Status Indicator -->
                      <span class="position-absolute bottom-0 end-0 badge rounded-pill ip-address"
                            data-employee-id="{{ $employee['employee_id'] ?? '' }}"
                            style="font-size: 0.7em; padding: 2px 6px;">
                        <i class="bi bi-globe me-1"></i><span class="ip-text">Checking...</span>
                      </span>
                    </div>

                    <div class="flex-grow-1">
                      <h5 class="card-title mb-1 fw-bold text-white employee-name">
                        {{ ($employee['first_name'] ?? '') }} {{ ($employee['last_name'] ?? '') }}
                      </h5>
                      <p class="card-text mb-0 text-white-50">
                        <i class="bi bi-person-badge me-1"></i>ID: {{ $employee['employee_id'] ?? 'N/A' }}
                      </p>
                    </div>

                    <!-- Status Badge -->
                    <div class="text-end">
                      @if(($employee['status'] ?? '') == 'Active')
                        <span class="badge bg-success bg-opacity-90 text-white">
                          <i class="bi bi-check-circle me-1"></i>Active
                        </span>
                      @elseif(($employee['status'] ?? '') == 'Inactive')
                        <span class="badge bg-secondary bg-opacity-90 text-white">
                          <i class="bi bi-pause-circle me-1"></i>Inactive
                        </span>
                      @else
                        <span class="badge bg-warning bg-opacity-90 text-dark">
                          <i class="bi bi-exclamation-circle me-1"></i>{{ $employee['status'] ?? 'Unknown' }}
                        </span>
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Card Body with Employee Details -->
                <div class="card-body p-4">
                  <!-- Contact Information -->
                  <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-2">
                      <i class="bi bi-telephone-fill me-2"></i>Contact Information
                    </h6>
                    <div class="small text-muted">
                      <div class="d-flex align-items-center mb-1">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        <span class="text-truncate">{{ $employee['email'] ?? 'N/A' }}</span>
                      </div>
                      <div class="d-flex align-items-center">
                        <i class="bi bi-telephone me-2 text-primary"></i>
                        <span>{{ $employee['phone_number'] ?? 'N/A' }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Position & Department -->
                  <div class="mb-3">
                    <h6 class="fw-bold text-success mb-2">
                      <i class="bi bi-briefcase-fill me-2"></i>Position & Department
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                      <span class="badge bg-info bg-opacity-10 text-info border border-info">
                        <i class="bi bi-person-workspace me-1"></i>{{ $employee['position'] ?? 'N/A' }}
                      </span>
                      <span class="badge bg-success bg-opacity-10 text-success border border-success">
                        <i class="bi bi-building me-1"></i>
                        @switch($employee['department_id'] ?? null)
                          @case(1) Human Resources @break
                          @case(2) Information Technology @break
                          @case(3) Finance @break
                          @case(4) Marketing @break
                          @case(5) Operations @break
                          @case(6) Customer Service @break
                          @default {{ $employee['department_id'] ?? 'Not Assigned' }}
                        @endswitch
                      </span>
                    </div>
                  </div>

                  <!-- Additional Information -->
                  <div class="mb-3">
                    <h6 class="fw-bold text-warning mb-2">
                      <i class="bi bi-info-circle-fill me-2"></i>Additional Details
                    </h6>
                    <div class="small text-muted">
                      <div class="d-flex align-items-center mb-1">
                        <i class="bi bi-geo-alt me-2 text-warning"></i>
                        <span class="text-truncate">{{ $employee['address'] ?? 'N/A' }}</span>
                      </div>
                      <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-event me-2 text-warning"></i>
                        <span>Hired: {{ isset($employee['hire_date']) && $employee['hire_date'] ? \Carbon\Carbon::parse($employee['hire_date'])->format('M d, Y') : 'N/A' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Card Footer with Action Buttons -->
                <div class="card-footer bg-light border-0 p-3">
                  <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-outline-info btn-sm flex-fill"
                            onclick="viewEmployeeDetails('{{ $employee['employee_id'] ?? '' }}', '{{ ($employee['first_name'] ?? '') }} {{ ($employee['last_name'] ?? '') }}', '{{ $employee['email'] ?? '' }}', '{{ $employee['phone_number'] ?? '' }}', '{{ $employee['position'] ?? '' }}', '{{ $employee['department_id'] ?? '' }}', '{{ $employee['address'] ?? '' }}', '{{ $employee['status'] ?? '' }}', '{{ isset($employee['hire_date']) && $employee['hire_date'] ? \Carbon\Carbon::parse($employee['hire_date'])->format('M d, Y') : 'N/A' }}')"
                            title="View Details" data-bs-toggle="tooltip">
                      <i class="bi bi-eye me-1"></i>View
                    </button>
                    <button class="btn btn-outline-primary btn-sm flex-fill"
                            onclick="editEmployeeWithConfirmation('{{ $employee['employee_id'] ?? '' }}', '{{ $employee['first_name'] ?? '' }}', '{{ $employee['last_name'] ?? '' }}', '{{ $employee['email'] ?? '' }}', '{{ $employee['phone_number'] ?? '' }}', '{{ $employee['position'] ?? '' }}', '{{ $employee['department_id'] ?? '' }}', '{{ $employee['address'] ?? '' }}', '{{ $employee['status'] ?? '' }}')"
                            title="Edit Employee" data-bs-toggle="tooltip">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button class="btn btn-outline-danger btn-sm flex-fill"
                            onclick="deleteEmployeeWithConfirmation('{{ $employee['employee_id'] ?? '' }}', '{{ ($employee['first_name'] ?? '') }} {{ ($employee['last_name'] ?? '') }}')"
                            title="Delete Employee" data-bs-toggle="tooltip">
                      <i class="bi bi-trash me-1"></i>Delete
                    </button>
                  </div>
                </div>

                <!-- Hover Effect Overlay -->
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-3 opacity-0 transition-opacity"
                     style="border-radius: 12px; pointer-events: none; transition: opacity 0.3s ease;"></div>
              </div>
            </div>
          @empty
            <div class="col-12">
              <div class="text-center py-5">
                <div class="mb-3">
                  <i class="bi bi-people display-1 text-muted"></i>
                </div>
                <h5 class="text-muted mb-2">No Employees Found</h5>
                <p class="text-muted">There are currently no employees in the system.</p>
                <button class="btn btn-primary" onclick="addEmployeeWithConfirmation()">
                  <i class="bi bi-plus-lg me-2"></i>Add First Employee
                </button>
              </div>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </main>

  <!-- Remove old modals - replaced with SweetAlert -->

  <!-- Enhanced Toast Notifications -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="successToast" class="toast align-items-center text-bg-success border-0 mb-2" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="successToastBody"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0 mb-2" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="errorToastBody"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });

    // Add Employee with Password Confirmation
    function addEmployeeWithConfirmation() {
      Swal.fire({
        title: '🔐 Admin Password Required',
        html: `
          <div class="text-start mb-3">
            <p class="mb-2"><i class="bi bi-shield-check text-primary"></i> <strong>Security Verification</strong></p>
            <p class="text-muted small mb-3">Please enter your admin password to add a new employee. This ensures only authorized personnel can create employee accounts.</p>
          </div>
          <input type="password" id="admin-password-input" class="swal2-input" placeholder="Enter your admin password" style="margin: 0;">
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Verify & Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('admin-password-input').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return password;
        },
        allowOutsideClick: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          const password = result.value;

          // Show loading
          Swal.fire({
            title: 'Verifying Password...',
            html: 'Please wait while we verify your credentials.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
              Swal.showLoading();
            }
          });

            try {
              // Verify password with backend
              const response = await fetch('/admin/verify-password', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: password })
              });

              // Try to parse JSON safely
              let result = null;
              try {
                result = await response.json();
              } catch (parseErr) {
                // Non-JSON response
                throw new Error('Invalid server response during password verification');
              }

              if (response.ok && result && result.success) {
                // Password verified, show add employee form
                showAddEmployeeForm(password);
              } else {
                Swal.fire({
                  title: '❌ Invalid Password',
                  text: (result && result.message) ? result.message : 'The password you entered is incorrect. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#dc3545'
                });
              }
            } catch (error) {
              console.error('Password verification error:', error);
              Swal.fire({
                title: '⚠️ Verification Error',
                text: error.message || 'An error occurred while verifying your password. Please try again.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
              });
            }
        }
      });
    }

    // Search functionality for card layout
    document.getElementById('employee-search').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const cardWrappers = document.querySelectorAll('.employee-card-wrapper');

      cardWrappers.forEach(wrapper => {
        const card = wrapper.querySelector('.employee-card');
        const name = card.querySelector('.employee-name').textContent.toLowerCase();
        const email = card.querySelector('.card-body').textContent.toLowerCase();
        const position = card.querySelector('.badge.bg-info').textContent.toLowerCase();
        const department = card.querySelector('.badge.bg-success').textContent.toLowerCase();

        if (name.includes(searchTerm) ||
            email.includes(searchTerm) ||
            position.includes(searchTerm) ||
            department.includes(searchTerm)) {
          wrapper.style.display = '';
        } else {
          wrapper.style.display = 'none';
        }
      });
    });

    // Edit employee modal
    document.querySelectorAll('.edit-employee-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
        const form = document.getElementById('editEmployeeForm');

        form.action = `/employees/${this.dataset.id}`;
        document.getElementById('edit-first-name').value = this.dataset.firstName;
        document.getElementById('edit-last-name').value = this.dataset.lastName;
        document.getElementById('edit-email').value = this.dataset.email;
        document.getElementById('edit-phone').value = this.dataset.phone;
        document.getElementById('edit-position').value = this.dataset.position;
        document.getElementById('edit-department').value = this.dataset.department;
        document.getElementById('edit-address').value = this.dataset.address;
        document.getElementById('edit-status').value = this.dataset.status;

        modal.show();
      });
    });

    // Delete button event listeners
    document.querySelectorAll('.delete-employee-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const employeeId = this.dataset.employeeId;
        const employeeName = this.dataset.employeeName;

        if (confirm(`Are you sure you want to delete ${employeeName}? This action cannot be undone.`)) {
          // Show loading state
          this.disabled = true;
          this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

          // Create and submit delete form
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/employees/${employeeId}`;

          // Add CSRF token
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = csrfToken;
          form.appendChild(csrfInput);

          // Add DELETE method
          const methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'DELETE';
          form.appendChild(methodInput);

          document.body.appendChild(form);
          form.submit();
        }
      });
    });

    // Toast notifications
    function showSuccessToast(message) {
      document.getElementById('successToastBody').textContent = message;
      new bootstrap.Toast(document.getElementById('successToast')).show();
    }

    function showErrorToast(message) {
      document.getElementById('errorToastBody').textContent = message;
      new bootstrap.Toast(document.getElementById('errorToast')).show();
    }

    function showLoadingToast(message) {
      document.getElementById('successToastBody').textContent = message;
      new bootstrap.Toast(document.getElementById('successToast')).show();
    }

    // Helper function to get department name
    function getDepartmentName(departmentId) {
      const departments = {
        '1': 'Human Resources',
        '2': 'Information Technology',
        '3': 'Finance',
        '4': 'Marketing',
        '5': 'Operations',
        '6': 'Customer Service'
      };
      return departments[departmentId] || 'Not Assigned';
    }

    // Show validation errors if any
    @if($errors->any())
      document.addEventListener('DOMContentLoaded', function() {
        let errorMessage = 'Please fix the following errors:\n';
        @foreach($errors->all() as $error)
          errorMessage += '• {{ $error }}\n';
        @endforeach
        showErrorToast(errorMessage);
      });
    @endif

    // View Employee Details
    function viewEmployeeDetails(id, name, email, phone, position, department, address, status, hireDate) {
      const departmentName = getDepartmentName(department);

      Swal.fire({
        title: `👤 ${name}`,
        html: `
          <div class="text-start">
            <div class="row g-3">
              <div class="col-md-6">
                <strong>Employee ID:</strong><br>
                <span class="text-muted">${id}</span>
              </div>
              <div class="col-md-6">
                <strong>Status:</strong><br>
                <span class="badge bg-${status === 'Active' ? 'success' : 'secondary'}">${status}</span>
              </div>
              <div class="col-md-6">
                <strong>Email:</strong><br>
                <span class="text-muted">${email}</span>
              </div>
              <div class="col-md-6">
                <strong>Phone:</strong><br>
                <span class="text-muted">${phone || 'N/A'}</span>
              </div>
              <div class="col-md-6">
                <strong>Position:</strong><br>
                <span class="text-muted">${position || 'N/A'}</span>
              </div>
              <div class="col-md-6">
                <strong>Department:</strong><br>
                <span class="text-muted">${departmentName}</span>
              </div>
              <div class="col-md-6">
                <strong>Address:</strong><br>
                <span class="text-muted">${address || 'N/A'}</span>
              </div>
              <div class="col-md-6">
                <strong>Hire Date:</strong><br>
                <span class="text-muted">${hireDate}</span>
              </div>
            </div>
          </div>
        `,
        width: '600px',
        confirmButtonText: 'Close',
        confirmButtonColor: '#6c757d'
      });
    }

    // Edit Employee with Password Confirmation
    function editEmployeeWithConfirmation(id, firstName, lastName, email, phone, position, department, address, status) {
      Swal.fire({
        title: '🔐 Admin Password Required',
        html: `
          <div class="text-start mb-3">
            <p class="mb-2"><i class="bi bi-shield-check text-primary"></i> <strong>Security Verification</strong></p>
            <p class="text-muted small mb-3">Please enter your admin password to edit employee information. This ensures only authorized personnel can modify employee data.</p>
          </div>
          <input type="password" id="edit-admin-password" class="swal2-input" placeholder="Enter your admin password" style="margin: 0;">
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Verify & Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('edit-admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return password;
        },
        allowOutsideClick: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          const password = result.value;

          // Show loading
          Swal.fire({
            title: 'Verifying Password...',
            html: 'Please wait while we verify your credentials.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
              Swal.showLoading();
            }
          });

            try {
              // Verify password with backend
              const response = await fetch('/admin/verify-password', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: password })
              });

              let result = null;
              try {
                result = await response.json();
              } catch (parseErr) {
                throw new Error('Invalid server response during password verification');
              }

              if (response.ok && result && result.success) {
                // Password verified, show edit employee form
                showEditEmployeeForm(id, firstName, lastName, email, phone, position, department, address, status, password);
              } else {
                Swal.fire({
                  title: '❌ Invalid Password',
                  text: (result && result.message) ? result.message : 'The password you entered is incorrect. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#dc3545'
                });
              }
            } catch (error) {
              console.error('Password verification error:', error);
              Swal.fire({
                title: '⚠️ Verification Error',
                text: error.message || 'An error occurred while verifying your password. Please try again.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
              });
            }
        }
      });
    }

    // Delete Employee with Password Confirmation
    function deleteEmployeeWithConfirmation(employeeId, employeeName) {
      Swal.fire({
        title: '⚠️ Delete Employee',
        html: `
          <div class="text-start mb-3">
            <p class="mb-2"><i class="bi bi-exclamation-triangle text-warning"></i> <strong>Warning: Irreversible Action</strong></p>
            <p class="text-muted mb-3">You are about to permanently delete <strong>${employeeName}</strong>. This action cannot be undone and will remove all employee data from the system.</p>
            <div class="alert alert-danger small">
              <i class="bi bi-shield-check"></i> Admin password verification required for security.
            </div>
          </div>
          <input type="password" id="delete-admin-password" class="swal2-input" placeholder="Enter your admin password" style="margin: 0;">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Verify & Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('delete-admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return password;
        },
        allowOutsideClick: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          const password = result.value;

          // Show loading
          Swal.fire({
            title: 'Verifying Password...',
            html: 'Please wait while we verify your credentials.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
              Swal.showLoading();
            }
          });

            try {
              // Verify password with backend
              const response = await fetch('/admin/verify-password', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: password })
              });

              let result = null;
              try {
                result = await response.json();
              } catch (parseErr) {
                throw new Error('Invalid server response during password verification');
              }

              if (response.ok && result && result.success) {
                // Password verified, proceed with deletion
                await submitDeleteEmployee(employeeId, employeeName, password);
              } else {
                Swal.fire({
                  title: '❌ Invalid Password',
                  text: (result && result.message) ? result.message : 'The password you entered is incorrect. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#dc3545'
                });
              }
            } catch (error) {
              console.error('Password verification error:', error);
              Swal.fire({
                title: '⚠️ Verification Error',
                text: error.message || 'An error occurred while verifying your password. Please try again.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
              });
            }
        }
      });
    }

    // Password strength checker (guarded: attach only if element exists)
    (function() {
      const pwd = document.getElementById('password');
      if (!pwd) return; // no global password input on this page

      pwd.addEventListener('input', function() {
        const password = this.value;

        // Check length requirement
        const lengthCheck = document.getElementById('length-check');
        if (lengthCheck) {
          if (password.length >= 12) {
            lengthCheck.className = 'text-success';
            lengthCheck.innerHTML = '✓ 12+ characters';
          } else {
            lengthCheck.className = 'text-danger';
            lengthCheck.innerHTML = '✗ 12+ characters';
          }
        }

        // Check uppercase requirement
        const upperCheck = document.getElementById('upper-check');
        if (upperCheck) {
          if (/[A-Z]/.test(password)) {
            upperCheck.className = 'text-success';
            upperCheck.innerHTML = '✓ Uppercase';
          } else {
            upperCheck.className = 'text-danger';
            upperCheck.innerHTML = '✗ Uppercase';
          }
        }

        // Check number requirement
        const numberCheck = document.getElementById('number-check');
        if (numberCheck) {
          if (/\d/.test(password)) {
            numberCheck.className = 'text-success';
            numberCheck.innerHTML = '✓ Number';
          } else {
            numberCheck.className = 'text-danger';
            numberCheck.innerHTML = '✗ Number';
          }
        }

        // Check symbol requirement
        const symbolCheck = document.getElementById('symbol-check');
        if (symbolCheck) {
          if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/\?]/.test(password)) {
            symbolCheck.className = 'text-success';
            symbolCheck.innerHTML = '✓ Symbol';
          } else {
            symbolCheck.className = 'text-danger';
            symbolCheck.innerHTML = '✗ Symbol';
          }
        }
      });
    })();

    // Show Add Employee Form after password verification
    function showAddEmployeeForm(adminPassword) {
      Swal.fire({
        title: '👤 Add New Employee',
        html: `
          <form id="add-employee-form" class="text-start">
              <div class="mb-3">
                <label for="employee_id" class="form-label">Employee ID*</label>
                <input type="text" id="employee_id" name="employee_id" class="form-control" value="{{ $nextEmployeeId }}" readonly>
                <small class="text-muted">Suggested ID: {{ $nextEmployeeId }}</small>
              </div>
              <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Status*</label>
                <select name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">First Name*</label>
                <input type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Last Name*</label>
                <input type="text" name="last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Email*</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
          <label class="form-label fw-bold">Password*</label>
          <input type="password" name="password" id="swal-password" class="form-control" required minlength="12">
          <div id="swal-password-requirements" class="mt-1" style="font-size: 0.95em;">
            <div id="swal-length-check" class="text-danger">✗ 12+ characters</div>
            <div id="swal-upper-check" class="text-danger">✗ Uppercase</div>
            <div id="swal-number-check" class="text-danger">✗ Number</div>
            <div id="swal-symbol-check" class="text-danger">✗ Symbol</div>
          </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Phone Number</label>
                <input type="text" name="phone_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Position</label>
                <input type="text" name="position" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Department</label>
                <select name="department_id" class="form-select">
                  <option value="">Select Department</option>
                  <option value="1">Human Resources</option>
                  <option value="2">Information Technology</option>
                  <option value="3">Finance</option>
                  <option value="4">Marketing</option>
                  <option value="5">Operations</option>
                  <option value="6">Customer Service</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Address</label>
                <input type="text" name="address" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Hire Date</label>
                <input type="date" name="hire_date" class="form-control">
              </div>
            </div>
          </form>
        `,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: '💾 Save Employee',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        didOpen: () => {
          const passwordInput = document.getElementById('swal-password');
          passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            // Length
            const lengthCheck = document.getElementById('swal-length-check');
            if (password.length >= 12) {
              lengthCheck.className = 'text-success';
              lengthCheck.innerHTML = '✓ 12+ characters';
            } else {
              lengthCheck.className = 'text-danger';
              lengthCheck.innerHTML = '✗ 12+ characters';
            }
            // Uppercase
            const upperCheck = document.getElementById('swal-upper-check');
            if (/[A-Z]/.test(password)) {
              upperCheck.className = 'text-success';
              upperCheck.innerHTML = '✓ Uppercase';
            } else {
              upperCheck.className = 'text-danger';
              upperCheck.innerHTML = '✗ Uppercase';
            }
            // Number
            const numberCheck = document.getElementById('swal-number-check');
            if (/\d/.test(password)) {
              numberCheck.className = 'text-success';
              numberCheck.innerHTML = '✓ Number';
            } else {
              numberCheck.className = 'text-danger';
              numberCheck.innerHTML = '✗ Number';
            }
            // Symbol
            const symbolCheck = document.getElementById('swal-symbol-check');
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
              symbolCheck.className = 'text-success';
              symbolCheck.innerHTML = '✓ Symbol';
            } else {
              symbolCheck.className = 'text-danger';
              symbolCheck.innerHTML = '✗ Symbol';
            }
          });
        },
        preConfirm: () => {
          const form = document.getElementById('add-employee-form');
          const formData = new FormData(form);
          const data = {};

          // Validate required fields
          const requiredFields = ['employee_id', 'status', 'first_name', 'last_name', 'email', 'password'];
          const missingFields = [];

          requiredFields.forEach(field => {
            const value = formData.get(field);
            if (!value || !value.trim()) {
              missingFields.push(field.replace('_', ' ').toUpperCase());
            } else {
              data[field] = value.trim();
            }
          });

          // Validate password requirements
          const password = formData.get('password');
          if (password) {
            const errors = [];
            if (password.length < 12) errors.push('at least 12 characters');
            if (!/[A-Z]/.test(password)) errors.push('1 uppercase letter');
            if (!/\d/.test(password)) errors.push('1 number');
            if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) errors.push('1 symbol');

            if (errors.length > 0) {
              Swal.showValidationMessage(`Password missing: ${errors.join(', ')}`);
              return false;
            }
          }

          if (missingFields.length > 0) {
            Swal.showValidationMessage(`Required fields: ${missingFields.join(', ')}`);
            return false;
          }

          // Add optional fields
          ['phone_number', 'position', 'department_id', 'address', 'hire_date'].forEach(field => {
            const value = formData.get(field);
            if (value && value.trim()) {
              data[field] = value.trim();
            }
          });

          return data;
        },
        allowOutsideClick: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          await submitEmployeeForm(result.value, adminPassword);
        }
      });
    }

    // Submit Employee Form
    async function submitEmployeeForm(employeeData, adminPassword) {
      // Show loading
      Swal.fire({
        title: 'Creating Employee...',
        html: 'Please wait while we create the employee account.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const formData = new FormData();
        Object.keys(employeeData).forEach(key => {
          formData.append(key, employeeData[key]);
        });
        formData.append('admin_password', adminPassword);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const response = await fetch('{{ route('employees.store') }}', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          },
          body: formData
        });

        // If server returns validation errors as JSON (422), parse and show them
        if (response.status === 422) {
          let payload = null;
          try {
            payload = await response.json();
          } catch (parseErr) {
            throw new Error('Validation failed, and server response could not be parsed.');
          }

          const errors = payload && payload.errors ? payload.errors : null;
          if (errors) {
            // Build a readable message
            let messages = [];
            Object.keys(errors).forEach(field => {
              const fieldErrors = errors[field];
              if (Array.isArray(fieldErrors)) {
                fieldErrors.forEach(msg => messages.push(msg));
              } else if (typeof fieldErrors === 'string') {
                messages.push(fieldErrors);
              }
            });

            Swal.fire({
              title: '❌ Validation Error',
              html: `<div class="text-start">${messages.map(m => `<div>• ${m}</div>`).join('')}</div>`,
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
            return;
          }
        }

        if (response.ok) {
          Swal.fire({
            title: '✅ Success!',
            text: 'Employee has been created successfully.',
            icon: 'success',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          const errorText = await response.text();
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
      } catch (error) {
        console.error('Employee creation error:', error);
        Swal.fire({
          title: '❌ Creation Failed',
          text: 'An error occurred while creating the employee. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Show Edit Employee Form after password verification
    function showEditEmployeeForm(id, firstName, lastName, email, phone, position, department, address, status, adminPassword) {
      Swal.fire({
        title: '✏️ Edit Employee',
        html: `
          <form id="edit-employee-form" class="text-start">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">First Name*</label>
                <input type="text" name="first_name" class="form-control" value="${firstName}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Last Name*</label>
                <input type="text" name="last_name" class="form-control" value="${lastName}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Email*</label>
                <input type="email" name="email" class="form-control" value="${email}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="${phone || ''}">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Position</label>
                <input type="text" name="position" class="form-control" value="${position || ''}">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Department</label>
                <select name="department_id" class="form-select">
                  <option value="">Select Department</option>
                  <option value="1" ${department == '1' ? 'selected' : ''}>Human Resources</option>
                  <option value="2" ${department == '2' ? 'selected' : ''}>Information Technology</option>
                  <option value="3" ${department == '3' ? 'selected' : ''}>Finance</option>
                  <option value="4" ${department == '4' ? 'selected' : ''}>Marketing</option>
                  <option value="5" ${department == '5' ? 'selected' : ''}>Operations</option>
                  <option value="6" ${department == '6' ? 'selected' : ''}>Customer Service</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Address</label>
                <input type="text" name="address" class="form-control" value="${address || ''}">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Status*</label>
                <select name="status" class="form-select" required>
                  <option value="Active" ${status === 'Active' ? 'selected' : ''}>Active</option>
                  <option value="Inactive" ${status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                </select>
              </div>
            </div>
          </form>
        `,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: '💾 Update Employee',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('edit-employee-form');
          const formData = new FormData(form);
          const data = {};

          // Validate required fields
          const requiredFields = ['first_name', 'last_name', 'email', 'status'];
          const missingFields = [];

          requiredFields.forEach(field => {
            const value = formData.get(field);
            if (!value || !value.trim()) {
              missingFields.push(field.replace('_', ' ').toUpperCase());
            } else {
              data[field] = value.trim();
            }
          });

          if (missingFields.length > 0) {
            Swal.showValidationMessage(`Required fields: ${missingFields.join(', ')}`);
            return false;
          }

          // Add optional fields
          ['phone_number', 'position', 'department_id', 'address'].forEach(field => {
            const value = formData.get(field);
            if (value && value.trim()) {
              data[field] = value.trim();
            }
          });

          return data;
        },
        allowOutsideClick: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          await submitEditEmployeeForm(id, result.value, adminPassword);
        }
      });
    }

    // Submit Edit Employee Form
    async function submitEditEmployeeForm(employeeId, employeeData, adminPassword) {
      // Show loading
      Swal.fire({
        title: 'Updating Employee...',
        html: 'Please wait while we update the employee information.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const formData = new FormData();
        Object.keys(employeeData).forEach(key => {
          formData.append(key, employeeData[key]);
        });
        formData.append('admin_password', adminPassword);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'PUT');

        const response = await fetch(`/employees/${employeeId}`, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          },
          body: formData
        });

        if (response.status === 422) {
          let payload = null;
          try {
            payload = await response.json();
          } catch (parseErr) {
            throw new Error('Validation failed, and server response could not be parsed.');
          }

          const errors = payload && payload.errors ? payload.errors : null;
          if (errors) {
            let messages = [];
            Object.keys(errors).forEach(field => {
              const fieldErrors = errors[field];
              if (Array.isArray(fieldErrors)) {
                fieldErrors.forEach(msg => messages.push(msg));
              } else if (typeof fieldErrors === 'string') {
                messages.push(fieldErrors);
              }
            });

            Swal.fire({
              title: '❌ Validation Error',
              html: `<div class="text-start">${messages.map(m => `<div>• ${m}</div>`).join('')}</div>`,
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
            return;
          }
        }

        if (response.ok) {
          Swal.fire({
            title: '✅ Success!',
            text: 'Employee information has been updated successfully.',
            icon: 'success',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          const errorText = await response.text();
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
      } catch (error) {
        console.error('Employee update error:', error);
        Swal.fire({
          title: '❌ Update Failed',
          text: 'An error occurred while updating the employee. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Submit Delete Employee
    async function submitDeleteEmployee(employeeId, employeeName, adminPassword) {
      // Show loading
      Swal.fire({
        title: 'Deleting Employee...',
        html: `Please wait while we delete ${employeeName} from the system.`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const formData = new FormData();
        formData.append('admin_password', adminPassword);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'DELETE');

        const response = await fetch(`/employees/${employeeId}`, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          },
          body: formData
        });

        if (response.status === 422) {
          let payload = null;
          try {
            payload = await response.json();
          } catch (parseErr) {
            throw new Error('Validation failed, and server response could not be parsed.');
          }

          const errors = payload && payload.errors ? payload.errors : null;
          if (errors) {
            let messages = [];
            Object.keys(errors).forEach(field => {
              const fieldErrors = errors[field];
              if (Array.isArray(fieldErrors)) {
                fieldErrors.forEach(msg => messages.push(msg));
              } else if (typeof fieldErrors === 'string') {
                messages.push(fieldErrors);
              }
            });

            Swal.fire({
              title: '❌ Validation Error',
              html: `<div class="text-start">${messages.map(m => `<div>• ${m}</div>`).join('')}</div>`,
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
            return;
          }
        }

        if (response.ok) {
          Swal.fire({
            title: '✅ Employee Deleted',
            text: `${employeeName} has been successfully removed from the system.`,
            icon: 'success',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          const errorText = await response.text();
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
      } catch (error) {
        console.error('Employee deletion error:', error);
        Swal.fire({
          title: '❌ Deletion Failed',
          text: 'An error occurred while deleting the employee. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Enhanced IP address tracking for employees
    async function updateAllIPAddresses() {
      const ipElements = document.querySelectorAll('.ip-address');

      // Show "Checking..." status briefly
      ipElements.forEach(element => {
        element.className = 'badge bg-warning ip-address';
        const ipText = element.querySelector('.ip-text');
        if (ipText) {
          ipText.textContent = 'Checking...';
        }
      });

      // Collect all employee IDs
      const employeeIds = [];
      ipElements.forEach(element => {
        const employeeId = element.getAttribute('data-employee-id');
        if (employeeId) {
          employeeIds.push(employeeId);
        }
      });

      if (employeeIds.length === 0) {
        console.log('No employee IDs found for IP address check');
        // Set all to N/A if no IDs found
        ipElements.forEach(element => {
          element.className = 'badge bg-secondary ip-address';
          const ipText = element.querySelector('.ip-text');
          if (ipText) {
            ipText.textContent = 'N/A';
          }
        });
        return;
      }

      try {
        console.log('Checking IP addresses for employees:', employeeIds);

        // Get real client IP address first
        let clientIP = 'Unknown';
        try {
          const ipResponse = await fetch('https://api.ipify.org?format=json');
          if (ipResponse.ok) {
            const ipData = await ipResponse.json();
            clientIP = ipData.ip;
          }
        } catch (ipError) {
          console.log('Could not get external IP, using fallback');
          // Try alternative IP service
          try {
            const altResponse = await fetch('https://httpbin.org/ip');
            if (altResponse.ok) {
              const altData = await altResponse.json();
              clientIP = altData.origin;
            }
          } catch (altError) {
            console.log('All IP services failed, using localhost detection');
          }
        }

        // Call API to check IP addresses for all employees
        const response = await fetch('/api/employees/check-ip-addresses', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            employee_ids: employeeIds,
            client_ip: clientIP,
            user_agent: navigator.userAgent,
            timestamp: new Date().toISOString()
          })
        });

        if (!response.ok) {
          const errorText = await response.text();
          console.error('API Error Response:', response.status, errorText);
          console.error('Full response:', response);

          // Set all to Error on API error
          ipElements.forEach(element => {
            element.className = 'badge bg-danger ip-address';
            const ipText = element.querySelector('.ip-text');
            if (ipText) {
              ipText.textContent = 'Error';
            }
          });

          if (typeof showErrorToast === 'function') {
            showErrorToast(`Failed to check IP addresses (${response.status}): ${errorText}`);
          }
          return;
        }

        const data = await response.json();
        console.log('IP address API response:', data);

        if (data.success && data.ip_addresses) {
          let activeCount = 0;

          // Update each IP element
          ipElements.forEach(element => {
            const employeeId = element.getAttribute('data-employee-id');
            const ipAddress = data.ip_addresses[employeeId];
            const ipText = element.querySelector('.ip-text');

            if (ipAddress && ipAddress !== 'N/A') {
              element.className = 'badge bg-success ip-address';
              if (ipText) {
                ipText.textContent = ipAddress;
              }
              activeCount++;
            } else {
              element.className = 'badge bg-secondary ip-address';
              if (ipText) {
                ipText.textContent = 'N/A';
              }
            }
          });
          console.log(`IP addresses updated: ${activeCount} employees with active sessions out of ${employeeIds.length}`);

          // Show success toast with count
          if (typeof showSuccessToast === 'function') {
            showSuccessToast(`IP addresses updated: ${activeCount} employees with active sessions`);
          }

        } else {
          console.error('API returned success=false:', data.message || 'Unknown error');

          // Set all to N/A on API error
          ipElements.forEach(element => {
            element.className = 'badge bg-secondary ip-address';
            const ipText = element.querySelector('.ip-text');
            if (ipText) {
              ipText.textContent = 'N/A';
            }
          });

          if (typeof showErrorToast === 'function') {
            showErrorToast('IP address API error: ' + (data.message || 'Unknown error'));
          }
        }
      } catch (error) {
        console.error('Error checking IP addresses:', error);
        console.error('Error details:', error.message, error.stack);

        // Set all to Error on network error
        ipElements.forEach(element => {
          element.className = 'badge bg-danger ip-address';
          const ipText = element.querySelector('.ip-text');
          if (ipText) {
            ipText.textContent = 'Error';
          }
        });

        if (typeof showErrorToast === 'function') {
          showErrorToast(`Network error checking IP addresses: ${error.message}`);
        }
      }
    }

    // Initialize IP addresses immediately
    function initializeIPAddresses() {
      const ipElements = document.querySelectorAll('.ip-address');

      // Set all to N/A initially
      ipElements.forEach(element => {
        element.className = 'badge bg-secondary ip-address';
        const ipText = element.querySelector('.ip-text');
        if (ipText) {
          ipText.textContent = 'N/A';
        }
      });

      // Then immediately try to get real IP addresses
      setTimeout(() => {
        console.log('Starting IP address update after 0.5 second delay');
        updateAllIPAddresses();
      }, 500); // Wait 0.5 seconds for page to fully load
    }

    // Update IP addresses every 30 seconds for better responsiveness
    function startIPAddressUpdates() {
      initializeIPAddresses(); // Set N/A initially, then check
      setInterval(updateAllIPAddresses, 30000); // Update every 30 seconds
    }

    // Manual refresh button functionality
    function refreshIPAddresses() {
      console.log('Manual refresh of IP addresses requested');
      updateAllIPAddresses();
    }

    // Set IP addresses immediately and start periodic updates
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Employee list page loaded, starting IP address tracking');
      startIPAddressUpdates();

      // Add manual refresh button if needed (optional)
      const headerDiv = document.querySelector('.card-header .d-flex.gap-2');
      if (headerDiv) {
        const refreshButton = document.createElement('button');
        refreshButton.className = 'btn btn-outline-secondary btn-sm';
        refreshButton.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh IP';
        refreshButton.onclick = refreshIPAddresses;
        refreshButton.title = 'Manually refresh IP addresses';
        headerDiv.insertBefore(refreshButton, headerDiv.firstChild);
      }
    });

    // Show success message if any
    @if(session('success'))
      document.addEventListener('DOMContentLoaded', function() {
        showSuccessToast('{{ session('success') }}');
      });
    @endif
  </script>
</body>
</html>
