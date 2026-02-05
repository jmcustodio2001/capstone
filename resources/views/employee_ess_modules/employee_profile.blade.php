<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Profile - Jetlouge Travels</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
            <h2 class="fw-bold mb-1">Employee Profile</h2>
            <p class="text-muted mb-0">View detailed employee information</p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employee.list') }}" class="text-decoration-none">Employee List</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Employee Profile Card -->
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Information</h4>
        <div class="d-flex gap-2">
          <a href="{{ route('employee.list') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
          </a>
          <button class="btn btn-primary edit-employee-btn"
                  data-id="{{ $employee->employee_id }}"
                  data-first-name="{{ $employee->first_name }}"
                  data-last-name="{{ $employee->last_name }}"
                  data-email="{{ $employee->email }}"
                  data-phone="{{ $employee->phone_number }}"
                  data-position="{{ $employee->position }}"
                  data-department="{{ $employee->department_id }}"
                  data-status="{{ $employee->status }}"
                  data-bs-toggle="modal" 
                  data-bs-target="#editEmployeeModal">
            <i class="bi bi-pencil me-1"></i> Edit Employee
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 text-center">
            <img src="{{ $employee->profile_picture ? (strpos($employee->profile_picture, 'http') === 0 ? $employee->profile_picture : asset('storage/' . $employee->profile_picture)) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->first_name . ' ' . $employee->last_name) }}" 
                 class="rounded-circle mb-3" width="150" height="150" alt="Profile Picture">
            <h4 class="fw-bold">{{ $employee->first_name }} {{ $employee->last_name }}</h4>
            <p class="text-muted">{{ $employee->position }}</p>
            <span class="badge {{ $employee->status == 'Active' ? 'bg-success' : 'bg-secondary' }} fs-6">
              {{ $employee->status }}
            </span>
          </div>
          <div class="col-md-8">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Employee ID</label>
                <p class="form-control-plaintext">{{ $employee->employee_id }}</p>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Email</label>
                <p class="form-control-plaintext">
                  <i class="bi bi-envelope me-2"></i>{{ $employee->email }}
                </p>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Phone Number</label>
                <p class="form-control-plaintext">
                  <i class="bi bi-telephone me-2"></i>{{ $employee->phone_number ?: 'Not provided' }}
                </p>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Department</label>
                <p class="form-control-plaintext">{{ $employee->department_id ?: 'Not assigned' }}</p>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Hire Date</label>
                <p class="form-control-plaintext">
                  <i class="bi bi-calendar me-2"></i>
                  {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'Not specified' }}
                </p>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Created</label>
                <p class="form-control-plaintext">
                  <i class="bi bi-clock me-2"></i>
                  {{ $employee->created_at ? $employee->created_at->format('M d, Y') : 'Unknown' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Edit Employee Modal -->
  <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="editEmployeeForm" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header">
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
                <input id="edit-department" type="text" name="department_id" class="form-control">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Edit employee modal
    document.querySelector('.edit-employee-btn').addEventListener('click', function() {
      const form = document.getElementById('editEmployeeForm');
      
      form.action = `/employees/${this.dataset.id}`;
      document.getElementById('edit-first-name').value = this.dataset.firstName;
      document.getElementById('edit-last-name').value = this.dataset.lastName;
      document.getElementById('edit-email').value = this.dataset.email;
      document.getElementById('edit-phone').value = this.dataset.phone;
      document.getElementById('edit-position').value = this.dataset.position;
      document.getElementById('edit-department').value = this.dataset.department;
      document.getElementById('edit-status').value = this.dataset.status;
    });
  </script>
</body>
</html>
