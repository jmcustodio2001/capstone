<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
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
          <button class="btn btn-primary" id="addEmployeeBtn">
            <i class="bi bi-plus-lg me-1"></i> Add Employee
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Contact</th>
                <th class="fw-bold">Position</th>
                <th class="fw-bold">Department</th>
                <th class="fw-bold">Address</th>
                <th class="fw-bold">Hire Date</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="employee-table-body">
              @forelse($employees as $index => $employee)
                <tr class="employee-row">
                  <td>{{ $employee->employee_id }}</td>
                  <td class="employee-name">
                    <div class="d-flex align-items-center">
                      <img src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->first_name . ' ' . $employee->last_name) }}"
                           class="rounded-circle me-2" width="32" height="32" alt="Profile">
                      <div>
                        <div class="fw-semibold">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                        <small class="text-muted">{{ $employee->employee_id }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div>
                      <i class="bi bi-envelope me-1"></i>{{ $employee->email }}
                    </div>
                    <div class="mt-1">
                      <i class="bi bi-telephone me-1"></i>{{ $employee->phone_number }}
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-info bg-opacity-10 text-info">{{ $employee->position }}</span>
                  </td>
                  <td>
                    @switch($employee->department_id)
                      @case(1) Human Resources @break
                      @case(2) Information Technology @break
                      @case(3) Finance @break
                      @case(4) Marketing @break
                      @case(5) Operations @break
                      @case(6) Customer Service @break
                      @default {{ $employee->department_id ?? 'Not Assigned' }}
                    @endswitch
                  </td>
                  <td>{{ $employee->address ?? 'N/A' }}</td>
                  <td>{{ \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') }}</td>
                  <td>
                    @if($employee->status == 'Active')
                      <span class="badge bg-success">Active</span>
                    @elseif($employee->status == 'Inactive')
                      <span class="badge bg-secondary">Inactive</span>
                    @else
                      <span class="badge bg-warning">{{ $employee->status }}</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button class="btn btn-outline-primary btn-sm edit-employee-btn"
                              data-id="{{ $employee->employee_id }}"
                              data-first-name="{{ $employee->first_name }}"
                              data-last-name="{{ $employee->last_name }}"
                              data-email="{{ $employee->email }}"
                              data-phone="{{ $employee->phone_number }}"
                              data-position="{{ $employee->position }}"
                              data-department="{{ $employee->department_id }}"
                              data-address="{{ $employee->address }}"
                              data-status="{{ $employee->status }}"
                              title="Edit Employee">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <a href="{{ route('employees.show', $employee->employee_id) }}"
                         class="btn btn-outline-info btn-sm view-employee-btn"
                         title="View Profile"
                         onclick="showLoadingToast('Loading employee profile...')">
                        <i class="bi bi-eye"></i>
                      </a>
                      <button class="btn btn-outline-danger btn-sm delete-employee-btn"
                              title="Delete Employee"
                              data-employee-id="{{ $employee->employee_id }}"
                              data-employee-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted">No employees found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Employee Modal -->
  <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="admin_password" id="admin-password">
        <div class="modal-content">
          <div class="card-header modal-header">
            <h5 class="modal-title">Add New Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="employee_id" class="form-label">Employee ID*</label>
                <input type="text" name="employee_id" class="form-control" value="{{ $nextEmployeeId }}" required>
                <small class="form-text text-muted">Suggested ID: {{ $nextEmployeeId }}</small>
              </div>
              <div class="col-md-6">
                <label for="status" class="form-label">Status*</label>
                <select name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="first_name" class="form-label">First Name*</label>
                <input type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name*</label>
                <input type="text" name="last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="email" class="form-label">Email*</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="password" class="form-label">Password*</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password" minlength="12" id="password">
                <small class="form-text text-muted">
                  Password requirements: at least 12 characters, 1 uppercase, 1 number, 1 symbol<br>
                  <span id="password-requirements">
                    <span id="length-check" class="text-danger">✗ 12+ characters</span> |
                    <span id="upper-check" class="text-danger">✗ Uppercase</span> |
                    <span id="number-check" class="text-danger">✗ Number</span> |
                    <span id="symbol-check" class="text-danger">✗ Symbol</span>
                  </span>
                </small>
              </div>
              <div class="col-md-6">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" name="phone_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="position" class="form-label">Position</label>
                <input type="text" name="position" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="department_id" class="form-label">Department</label>
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
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="hire_date" class="form-label">Hire Date</label>
                <input type="date" name="hire_date" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control" accept="image/*">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveEmployeeBtn">
              <i class="bi bi-save me-1"></i> Save Employee
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Employee Modal -->
  <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="editEmployeeForm" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="card-header modal-header">
            <h5 class="modal-title">Edit Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="edit-first-name" class="form-label">First Name*</label>
                <input id="edit-first-name" type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="edit-last-name" class="form-label">Last Name*</label>
                <input id="edit-last-name" type="text" name="last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="edit-email" class="form-label">Email*</label>
                <input id="edit-email" type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="edit-phone" class="form-label">Phone Number</label>
                <input id="edit-phone" type="text" name="phone_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="edit-position" class="form-label">Position</label>
                <input id="edit-position" type="text" name="position" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="edit-department" class="form-label">Department</label>
                <select id="edit-department" name="department_id" class="form-select">
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
                <label for="edit-address" class="form-label">Address</label>
                <input id="edit-address" type="text" name="address" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="edit-status" class="form-label">Status*</label>
                <select id="edit-status" name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-arrow-repeat me-1"></i> Update Employee
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Password Verification Modal -->
  <div class="modal fade" id="verifyPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Verify Your Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Please enter your password to confirm adding this employee.</p>
          <input type="password" id="verify-password" class="form-control" placeholder="Enter your password">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirm-verify">Verify</button>
        </div>
      </div>
    </div>
  </div>

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
    // Add Employee button handler - show password verification first
    document.getElementById('addEmployeeBtn').addEventListener('click', function() {
      const verifyModal = new bootstrap.Modal(document.getElementById('verifyPasswordModal'));
      verifyModal.show();
    });

    // Search functionality
    document.getElementById('employee-search').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = document.querySelectorAll('.employee-row');

      rows.forEach(row => {
        const name = row.querySelector('.employee-name').textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const position = row.cells[3].textContent.toLowerCase();

        if (name.includes(searchTerm) || email.includes(searchTerm) || position.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
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

    // Handle add employee form submission
    document.querySelector('#addEmployeeModal form').addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent default form submission
      const form = this; // Store reference to form

      // Validate required fields
      const requiredFields = [
        { name: 'employee_id', label: 'Employee ID' },
        { name: 'status', label: 'Status' },
        { name: 'first_name', label: 'First Name' },
        { name: 'last_name', label: 'Last Name' },
        { name: 'email', label: 'Email' },
        { name: 'password', label: 'Password' }
      ];

      let missingFields = [];
      let firstEmptyField = null;

      requiredFields.forEach(field => {
        const element = form.querySelector(`[name="${field.name}"]`);
        if (!element || !element.value.trim()) {
          missingFields.push(field.label);
          if (!firstEmptyField) {
            firstEmptyField = element;
          }
        } else if (field.name === 'password') {
          const password = element.value;
          const errors = [];

          if (password.length < 12) {
            errors.push('at least 12 characters');
          }
          if (!/[A-Z]/.test(password)) {
            errors.push('1 uppercase letter');
          }
          if (!/\d/.test(password)) {
            errors.push('1 number');
          }
          if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            errors.push('1 symbol');
          }

          if (errors.length > 0) {
            missingFields.push(`${field.label} (missing: ${errors.join(', ')})`);
            if (!firstEmptyField) {
              firstEmptyField = element;
            }
          }
        }
      });

      if (missingFields.length > 0) {
        // Show error notification for missing fields
        Swal.fire({
          title: 'Required Fields Missing',
          html: `Please fill in the following required fields:<br><br><strong>${missingFields.join('<br>')}</strong>`,
          icon: 'error',
          confirmButtonColor: '#d33',
          confirmButtonText: 'OK'
        });

        // Focus on the first empty field
        if (firstEmptyField) {
          firstEmptyField.focus();
        }
        return;
      }

      // Show password verification modal again before saving
      const verifyModal = new bootstrap.Modal(document.getElementById('verifyPasswordModal'));
      verifyModal.show();
    });

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

    // Password strength checker
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;

      // Check length requirement
      const lengthCheck = document.getElementById('length-check');
      if (password.length >= 12) {
        lengthCheck.className = 'text-success';
        lengthCheck.innerHTML = '✓ 12+ characters';
      } else {
        lengthCheck.className = 'text-danger';
        lengthCheck.innerHTML = '✗ 12+ characters';
      }

      // Check uppercase requirement
      const upperCheck = document.getElementById('upper-check');
      if (/[A-Z]/.test(password)) {
        upperCheck.className = 'text-success';
        upperCheck.innerHTML = '✓ Uppercase';
      } else {
        upperCheck.className = 'text-danger';
        upperCheck.innerHTML = '✗ Uppercase';
      }

      // Check number requirement
      const numberCheck = document.getElementById('number-check');
      if (/\d/.test(password)) {
        numberCheck.className = 'text-success';
        numberCheck.innerHTML = '✓ Number';
      } else {
        numberCheck.className = 'text-danger';
        numberCheck.innerHTML = '✗ Number';
      }

      // Check symbol requirement
      const symbolCheck = document.getElementById('symbol-check');
      if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        symbolCheck.className = 'text-success';
        symbolCheck.innerHTML = '✓ Symbol';
      } else {
        symbolCheck.className = 'text-danger';
        symbolCheck.innerHTML = '✗ Symbol';
      }
    });

    // Password verification modal handler
    document.getElementById('confirm-verify').addEventListener('click', async function() {
      const password = document.getElementById('verify-password').value;
      if (!password.trim()) {
        Swal.fire({
          title: 'Password Required',
          text: 'Please enter your password.',
          icon: 'error'
        });
        return;
      }

      // Show loading state
      const verifyBtn = this;
      const originalText = verifyBtn.innerHTML;
      verifyBtn.disabled = true;
      verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...';

      try {
        // Verify password with backend
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
          // Password is correct
          document.getElementById('admin-password').value = password;
          bootstrap.Modal.getInstance(document.getElementById('verifyPasswordModal')).hide();
          document.getElementById('verify-password').value = '';

          // Check if add employee modal is already open (means we're in save mode)
          const addEmployeeModal = document.getElementById('addEmployeeModal');
          if (addEmployeeModal.classList.contains('show')) {
            // We're in save mode - show confirmation
            Swal.fire({
              title: 'Are you sure?',
              text: 'Do you want to save this employee?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, save it!'
            }).then((result) => {
              if (result.isConfirmed) {
                const submitBtn = document.getElementById('saveEmployeeBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                document.querySelector('#addEmployeeModal form').submit();
              }
            });
          } else {
            // We're in initial mode - show add employee modal
            const addModal = new bootstrap.Modal(addEmployeeModal);
            addModal.show();
          }
        } else {
          // Password is incorrect
          Swal.fire({
            title: 'Incorrect Password',
            text: result.message || 'The password you entered is incorrect.',
            icon: 'error',
            confirmButtonColor: '#d33'
          });
        }
      } catch (error) {
        console.error('Password verification error:', error);
        Swal.fire({
          title: 'Error',
          text: 'An error occurred while verifying your password. Please try again.',
          icon: 'error',
          confirmButtonColor: '#d33'
        });
      } finally {
        // Reset button state
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalText;
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
