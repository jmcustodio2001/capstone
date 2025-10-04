<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .simulation-card {
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      border: none;
    }
    .card-header-custom {
      background-color: #f8f9fa;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
    }
    .avatar-circle {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #e3f2fd;
      color: #1976d2;
      border-radius: 50%;
    }
    .badge-simulation {
      padding: 0.35em 0.65em;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    .action-btns .btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
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
            <h2 class="fw-bold mb-1">Employee Request Form</h2>
            <p class="text-muted mb-0">
              Welcome back, Admin! Here are the employee requests.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee Requests</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="simulation-card card">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Requests</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="fw-bold">Request ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Request Type</th>
                <th class="fw-bold">Reason</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Requested Date</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($requests as $request)
              <tr>
                <td>{{ $request->request_id }}</td>
                <td>
                  @if($request->employee)
                    @php
                      $firstName = $request->employee->first_name ?? 'Unknown';
                      $lastName = $request->employee->last_name ?? 'Employee';
                      
                      // Profile picture logic - same as other HR modules
                      $profilePicUrl = '';
                      if ($request->employee->profile_picture) {
                        $profilePicUrl = asset('storage/' . $request->employee->profile_picture);
                      } else {
                        // Fallback to UI Avatars with consistent color scheme
                        $employeeId = $request->employee->employee_id ?? 'EMP';
                        $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                        $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                        $colorIndex = crc32($employeeId) % count($colors);
                        $bgColor = $colors[$colorIndex];
                        $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background={$bgColor}&color=ffffff&size=128&bold=true";
                      }
                    @endphp
                    
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2">
                        <img src="{{ $profilePicUrl }}" 
                             class="rounded-circle" 
                             style="width: 40px; height: 40px; object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(substr($firstName, 0, 1) . substr($lastName, 0, 1)) }}&background=6c757d&color=ffffff&size=128&bold=true'">
                      </div>
                      <span class="fw-semibold">{{ $firstName }} {{ $lastName }}</span>
                    </div>
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>{{ $request->request_type }}</td>
                <td>{{ $request->reason }}</td>
                <td>
                  @if(strtolower($request->status) === 'approved')
                    <span class="badge bg-success bg-opacity-10 text-success badge-simulation">Approved</span>
                  @elseif(strtolower($request->status) === 'pending')
                    <span class="badge bg-warning bg-opacity-10 text-warning badge-simulation">Pending</span>
                  @else
                    <span class="badge bg-danger bg-opacity-10 text-danger badge-simulation">Rejected</span>
                  @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($request->requested_date)->format('d/m/Y') }}</td>
                <td class="text-center action-btns">
                  <div class="btn-group" role="group">
                    @if(strtolower($request->status) === 'pending')
                      <button class="btn btn-success btn-sm" onclick="approveRequestWithConfirmation({{ $request->request_id }})" title="Approve Request">
                        <i class="bi bi-check-circle"></i>
                      </button>
                      <button class="btn btn-danger btn-sm" onclick="rejectRequestWithConfirmation({{ $request->request_id }})" title="Reject Request">
                        <i class="bi bi-x-circle"></i>
                      </button>
                    @endif
                    <button class="btn btn-primary btn-sm" onclick="editRequestWithConfirmation({{ $request->request_id }}, '{{ addslashes($request->employee_id) }}', '{{ addslashes($request->request_type) }}', '{{ addslashes($request->reason) }}', '{{ $request->status }}', '{{ $request->requested_date }}', '{{ addslashes($request->rejection_reason ?? '') }}')" title="Edit Request">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-info btn-sm" onclick="viewRequestDetails({{ $request->request_id }}, '{{ addslashes($request->employee ? $request->employee->first_name . ' ' . $request->employee->last_name : 'N/A') }}', '{{ addslashes($request->employee_id) }}', '{{ addslashes($request->request_type) }}', '{{ addslashes($request->reason) }}', '{{ $request->status }}', '{{ \Carbon\Carbon::parse($request->requested_date)->format('d/m/Y') }}', '{{ addslashes($request->rejection_reason ?? '') }}')" title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteRequestWithConfirmation({{ $request->request_id }})" title="Delete Request">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- View Request Modal -->
              <div class="modal fade" id="viewRequestModal{{ $request->request_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Request Details</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Request ID:</strong> {{ $request->request_id }}</li>
                        <li class="list-group-item"><strong>Employee:</strong> {{ $request->employee ? $request->employee->first_name . ' ' . $request->employee->last_name : 'N/A' }}</li>
                        <li class="list-group-item"><strong>Employee ID:</strong> {{ $request->employee_id }}</li>
                        <li class="list-group-item"><strong>Request Type:</strong> {{ $request->request_type }}</li>
                        <li class="list-group-item"><strong>Reason:</strong> {{ $request->reason }}</li>
                        <li class="list-group-item"><strong>Status:</strong> 
                          @if(strtolower($request->status) === 'approved')
                            <span class="badge bg-success">Approved</span>
                          @elseif(strtolower($request->status) === 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                          @else
                            <span class="badge bg-danger">Rejected</span>
                          @endif
                        </li>
                        <li class="list-group-item"><strong>Requested Date:</strong> {{ \Carbon\Carbon::parse($request->requested_date)->format('d/m/Y') }}</li>
                        @if($request->rejection_reason)
                          <li class="list-group-item"><strong>Rejection Reason:</strong> <span class="text-danger">{{ $request->rejection_reason }}</span></li>
                        @endif
                      </ul>
                    </div>
                    <div class="modal-footer">
                      @if(strtolower($request->status) === 'pending')
                        <button type="button" class="btn btn-success" onclick="updateRequestStatus({{ $request->request_id }}, 'approved')">
                          <i class="bi bi-check-circle me-1"></i>Approve
                        </button>
                        <button type="button" class="btn btn-danger" onclick="showRejectModal({{ $request->request_id }})">
                          <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                      @endif
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Edit Request Modal -->
              <div class="modal fade" id="editRequestModal{{ $request->request_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Edit Request</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('employee_request_forms.update', $request->request_id) }}" method="POST">
                      @csrf
                      @method('PUT')
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="mb-3">
                              <label for="employee_id{{ $request->request_id }}" class="form-label">Employee ID</label>
                              <input type="text" name="employee_id" id="employee_id{{ $request->request_id }}" 
                                class="form-control" value="{{ $request->employee_id }}" required>
                            </div>
                            
                            <div class="mb-3">
                              <label for="request_type{{ $request->request_id }}" class="form-label">Request Type</label>
                              <select name="request_type" id="request_type{{ $request->request_id }}" class="form-select" required>
                                <option value="Leave / Time-Off Request Form" {{ $request->request_type == 'Leave / Time-Off Request Form' ? 'selected' : '' }}>Leave / Time-Off Request Form</option>
                                <option value="Overtime Request Form" {{ $request->request_type == 'Overtime Request Form' ? 'selected' : '' }}>Overtime Request Form</option>
                                <option value="Work From Home / Remote Work Request Form" {{ $request->request_type == 'Work From Home / Remote Work Request Form' ? 'selected' : '' }}>Work From Home / Remote Work Request Form</option>
                                <option value="Shift Change / Schedule Adjustment Request" {{ $request->request_type == 'Shift Change / Schedule Adjustment Request' ? 'selected' : '' }}>Shift Change / Schedule Adjustment Request</option>
                                <option value="Expense Reimbursement Request" {{ $request->request_type == 'Expense Reimbursement Request' ? 'selected' : '' }}>Expense Reimbursement Request</option>
                                <option value="Training & Development Request" {{ $request->request_type == 'Training & Development Request' ? 'selected' : '' }}>Training & Development Request</option>
                                <option value="Travel Request Form" {{ $request->request_type == 'Travel Request Form' ? 'selected' : '' }}>Travel Request Form</option>
                                <option value="Equipment / Asset Request" {{ $request->request_type == 'Equipment / Asset Request' ? 'selected' : '' }}>Equipment / Asset Request</option>
                                <option value="Payroll / Salary Adjustment Request" {{ $request->request_type == 'Payroll / Salary Adjustment Request' ? 'selected' : '' }}>Payroll / Salary Adjustment Request</option>
                                <option value="Personal Information Update Request" {{ $request->request_type == 'Personal Information Update Request' ? 'selected' : '' }}>Personal Information Update Request</option>
                              </select>
                            </div>
                            
                            <div class="mb-3">
                              <label for="status{{ $request->request_id }}" class="form-label">Status</label>
                              <select name="status" id="status{{ $request->request_id }}" class="form-select" required>
                                <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                              </select>
                            </div>
                          </div>
                          
                          <div class="col-md-6">
                            <div class="mb-3">
                              <label for="requested_date{{ $request->request_id }}" class="form-label">Requested Date</label>
                              <input type="date" name="requested_date" id="requested_date{{ $request->request_id }}" 
                                class="form-control" value="{{ $request->requested_date }}" required>
                            </div>
                            
                            <div class="mb-3">
                              <label for="reason{{ $request->request_id }}" class="form-label">Reason</label>
                              <textarea name="reason" id="reason{{ $request->request_id }}" rows="4" 
                                class="form-control" required>{{ $request->reason }}</textarea>
                            </div>
                            
                            <div class="mb-3">
                              <label for="rejection_reason{{ $request->request_id }}" class="form-label">Rejection Reason (if applicable)</label>
                              <textarea name="rejection_reason" id="rejection_reason{{ $request->request_id }}" rows="3" 
                                class="form-control" placeholder="Enter rejection reason if status is rejected...">{{ $request->rejection_reason ?? '' }}</textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                          <i class="bi bi-check-circle me-1"></i>Update Request
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No employee requests found.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SweetAlert Enhanced Request Management Script -->
  <script>
    // Approve Request with Password Confirmation
    function approveRequestWithConfirmation(requestId) {
      Swal.fire({
        title: '<i class="bi bi-check-circle text-success"></i> Approve Request',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to approve this request:</p>
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Password:</label>
              <input type="password" id="approvePassword" class="form-control" placeholder="Enter your password" minlength="3">
              <div class="form-text text-muted">
                <i class="bi bi-shield-lock"></i> Password verification required for security
              </div>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Approve Request',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        focusConfirm: false,
        preConfirm: () => {
          const password = document.getElementById('approvePassword').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return password;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitApproval(requestId, result.value);
        }
      });
    }

    function submitApproval(requestId, password) {
      Swal.fire({
        title: 'Processing...',
        text: 'Approving request, please wait...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch(`/admin/employee-request-forms/${requestId}/update-status`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
          status: 'approved',
          password: password
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Request Approved!',
            text: 'The employee request has been successfully approved.',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Approval Failed',
            text: data.message || 'Invalid password. Please enter your correct admin password.',
            confirmButtonColor: '#dc3545'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Network Error',
          text: 'Failed to approve request. Please check your connection and try again.',
          confirmButtonColor: '#dc3545'
        });
      });
    }

    // Reject Request with Password Confirmation
    function rejectRequestWithConfirmation(requestId) {
      Swal.fire({
        title: '<i class="bi bi-x-circle text-danger"></i> Reject Request',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Rejection Reason:</label>
              <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Password:</label>
              <input type="password" id="rejectPassword" class="form-control" placeholder="Enter your password" minlength="3">
              <div class="form-text text-muted">
                <i class="bi bi-shield-lock"></i> Password verification required for security
              </div>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-x-circle me-1"></i>Reject Request',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        focusConfirm: false,
        preConfirm: () => {
          const reason = document.getElementById('rejectionReason').value.trim();
          const password = document.getElementById('rejectPassword').value;
          
          if (!reason) {
            Swal.showValidationMessage('Please provide a rejection reason');
            return false;
          }
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return { reason, password };
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitRejection(requestId, result.value.reason, result.value.password);
        }
      });
    }

    function submitRejection(requestId, reason, password) {
      Swal.fire({
        title: 'Processing...',
        text: 'Rejecting request, please wait...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch(`/admin/employee-request-forms/${requestId}/update-status`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
          status: 'rejected',
          rejection_reason: reason,
          password: password
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Request Rejected',
            text: 'The employee request has been rejected with the provided reason.',
            confirmButtonColor: '#dc3545',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Rejection Failed',
            text: data.message || 'Invalid password. Please enter your correct admin password.',
            confirmButtonColor: '#dc3545'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Network Error',
          text: 'Failed to reject request. Please check your connection and try again.',
          confirmButtonColor: '#dc3545'
        });
      });
    }

    // View Request Details
    function viewRequestDetails(requestId, employeeName, employeeId, requestType, reason, status, requestedDate, rejectionReason) {
      const statusBadge = status.toLowerCase() === 'approved' ? 
        '<span class="badge bg-success">Approved</span>' :
        status.toLowerCase() === 'pending' ?
        '<span class="badge bg-warning text-dark">Pending</span>' :
        '<span class="badge bg-danger">Rejected</span>';

      const rejectionSection = rejectionReason ? 
        `<tr><td class="fw-bold">Rejection Reason:</td><td class="text-danger">${rejectionReason}</td></tr>` : '';

      Swal.fire({
        title: '<i class="bi bi-eye text-info"></i> Request Details',
        html: `
          <div class="text-start">
            <table class="table table-borderless">
              <tr><td class="fw-bold">Request ID:</td><td>${requestId}</td></tr>
              <tr><td class="fw-bold">Employee:</td><td>${employeeName}</td></tr>
              <tr><td class="fw-bold">Employee ID:</td><td>${employeeId}</td></tr>
              <tr><td class="fw-bold">Request Type:</td><td>${requestType}</td></tr>
              <tr><td class="fw-bold">Reason:</td><td>${reason}</td></tr>
              <tr><td class="fw-bold">Status:</td><td>${statusBadge}</td></tr>
              <tr><td class="fw-bold">Requested Date:</td><td>${requestedDate}</td></tr>
              ${rejectionSection}
            </table>
          </div>
        `,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Close',
        confirmButtonColor: '#6c757d',
        width: '600px'
      });
    }

    // Edit Request with Password Confirmation
    function editRequestWithConfirmation(requestId, employeeId, requestType, reason, status, requestedDate, rejectionReason) {
      Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Password Verification',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to edit this request:</p>
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Password:</label>
              <input type="password" id="editPassword" class="form-control" placeholder="Enter your password" minlength="3">
              <div class="form-text text-muted">
                <i class="bi bi-shield-lock"></i> Password verification required for security
              </div>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-arrow-right me-1"></i>Continue to Edit',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        focusConfirm: false,
        preConfirm: () => {
          const password = document.getElementById('editPassword').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return password;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          showEditRequestForm(requestId, employeeId, requestType, reason, status, requestedDate, rejectionReason, result.value);
        }
      });
    }

    function showEditRequestForm(requestId, employeeId, requestType, reason, status, requestedDate, rejectionReason, password) {
      const requestTypeOptions = [
        'Leave / Time-Off Request Form',
        'Overtime Request Form', 
        'Work From Home / Remote Work Request Form',
        'Shift Change / Schedule Adjustment Request',
        'Expense Reimbursement Request',
        'Training & Development Request',
        'Travel Request Form',
        'Equipment / Asset Request',
        'Payroll / Salary Adjustment Request',
        'Personal Information Update Request'
      ];

      const requestTypeOptionsHtml = requestTypeOptions.map(option => 
        `<option value="${option}" ${option === requestType ? 'selected' : ''}>${option}</option>`
      ).join('');

      Swal.fire({
        title: '<i class="bi bi-pencil text-primary"></i> Edit Request',
        html: `
          <div class="text-start">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Employee ID:</label>
                  <input type="text" id="editEmployeeId" class="form-control" value="${employeeId}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Request Type:</label>
                  <select id="editRequestType" class="form-select" required>
                    ${requestTypeOptionsHtml}
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Status:</label>
                  <select id="editStatus" class="form-select" required>
                    <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="approved" ${status === 'approved' ? 'selected' : ''}>Approved</option>
                    <option value="rejected" ${status === 'rejected' ? 'selected' : ''}>Rejected</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Requested Date:</label>
                  <input type="date" id="editRequestedDate" class="form-control" value="${requestedDate}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Reason:</label>
                  <textarea id="editReason" class="form-control" rows="3" required>${reason}</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Rejection Reason:</label>
                  <textarea id="editRejectionReason" class="form-control" rows="2" placeholder="Enter rejection reason if status is rejected...">${rejectionReason}</textarea>
                </div>
              </div>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Update Request',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        width: '800px',
        focusConfirm: false,
        preConfirm: () => {
          const formData = {
            employee_id: document.getElementById('editEmployeeId').value.trim(),
            request_type: document.getElementById('editRequestType').value,
            reason: document.getElementById('editReason').value.trim(),
            status: document.getElementById('editStatus').value,
            requested_date: document.getElementById('editRequestedDate').value,
            rejection_reason: document.getElementById('editRejectionReason').value.trim()
          };
          
          if (!formData.employee_id || !formData.request_type || !formData.reason || !formData.requested_date) {
            Swal.showValidationMessage('Please fill in all required fields');
            return false;
          }
          
          return formData;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitEditForm(requestId, result.value, password);
        }
      });
    }

    function submitEditForm(requestId, formData, password) {
      Swal.fire({
        title: 'Processing...',
        text: 'Updating request, please wait...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Add password to form data
      formData.password = password;

      fetch(`/admin/employee-request-forms/${requestId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Request Updated!',
            text: 'The employee request has been successfully updated.',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: data.message || 'Invalid password. Please enter your correct admin password.',
            confirmButtonColor: '#dc3545'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Network Error',
          text: 'Failed to update request. Please check your connection and try again.',
          confirmButtonColor: '#dc3545'
        });
      });
    }

    // Delete Request with Password Confirmation
    function deleteRequestWithConfirmation(requestId) {
      Swal.fire({
        title: '<i class="bi bi-exclamation-triangle text-warning"></i> Delete Request',
        html: `
          <div class="text-start">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Warning:</strong> This action cannot be undone. The request will be permanently deleted from the system.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Password:</label>
              <input type="password" id="deletePassword" class="form-control" placeholder="Enter your password to confirm deletion" minlength="3">
              <div class="form-text text-muted">
                <i class="bi bi-shield-lock"></i> Password verification required for security
              </div>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete Request',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        focusConfirm: false,
        preConfirm: () => {
          const password = document.getElementById('deletePassword').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 3) {
            Swal.showValidationMessage('Password must be at least 3 characters');
            return false;
          }
          return password;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitDeleteRequest(requestId, result.value);
        }
      });
    }

    function submitDeleteRequest(requestId, password) {
      Swal.fire({
        title: 'Processing...',
        text: 'Deleting request, please wait...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch(`/admin/employee-request-forms/${requestId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ password: password })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Request Deleted!',
            text: 'The employee request has been successfully deleted.',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Deletion Failed',
            text: data.message || 'Invalid password. Please enter your correct admin password.',
            confirmButtonColor: '#dc3545'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Network Error',
          text: 'Failed to delete request. Please check your connection and try again.',
          confirmButtonColor: '#dc3545'
        });
      });
    }
  </script>

<script>
  // Remove all .modal-backdrop elements on page load and after any modal event
  function removeAllModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
      backdrop.remove();
    });
  }
  window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
  document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
  document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);
</script>
</body>
</html>
