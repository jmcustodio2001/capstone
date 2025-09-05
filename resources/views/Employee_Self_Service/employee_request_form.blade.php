<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Jetlouge Travels Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
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
                  @if($request->status === 'approved')
                    <span class="badge bg-success bg-opacity-10 text-success badge-simulation">Approved</span>
                  @elseif($request->status === 'pending')
                    <span class="badge bg-warning bg-opacity-10 text-warning badge-simulation">Pending</span>
                  @else
                    <span class="badge bg-danger bg-opacity-10 text-danger badge-simulation">Rejected</span>
                  @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($request->requested_date)->format('d/m/Y') }}</td>
                <td class="text-center action-btns">
                  @if($request->status === 'pending')
                    <button class="btn btn-success btn-sm me-1" onclick="updateRequestStatus({{ $request->request_id }}, 'approved')">
                      <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button class="btn btn-danger btn-sm me-1" onclick="showRejectModal({{ $request->request_id }})">
                      <i class="bi bi-x-circle"></i> Reject
                    </button>
                  @endif
                  <button class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editRequestModal{{ $request->request_id }}">
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                  <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#viewRequestModal{{ $request->request_id }}">
                    <i class="bi bi-eye"></i> View
                  </button>
                  <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $request->request_id }})">
                    <i class="bi bi-trash"></i> Delete
                  </button>
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
                          @if($request->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                          @elseif($request->status === 'pending')
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
                      @if($request->status === 'pending')
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

  <!-- Rejection Reason Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2 text-danger"></i>Reject Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-3">Please provide a reason for rejecting this request:</p>
          <textarea id="rejectionReason" class="form-control" rows="4" placeholder="Enter rejection reason..." required></textarea>
          <div class="invalid-feedback" id="rejectionError" style="display: none;">
            Please provide a reason for rejection.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="submitRejection()">
            <i class="bi bi-x-circle me-1"></i>Reject Request
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-trash me-2 text-danger"></i>Delete Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
            <h5 class="mt-3 mb-3">Are you sure you want to delete this request?</h5>
            <p class="text-muted mb-0">This action cannot be undone. The request will be permanently deleted from the system.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="deleteRequest()">
            <i class="bi bi-trash me-1"></i>Delete Request
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Request Status Update Script -->
  <script>
    let currentRequestId = null;

    function updateRequestStatus(requestId, status) {
      if (confirm(`Are you sure you want to ${status} this request?`)) {
        fetch(`/admin/employee-request-forms/${requestId}/update-status`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error updating request status');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error updating request status');
        });
      }
    }

    function showRejectModal(requestId) {
      currentRequestId = requestId;
      document.getElementById('rejectionReason').value = '';
      document.getElementById('rejectionError').style.display = 'none';
      document.getElementById('rejectionReason').classList.remove('is-invalid');
      
      const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
      modal.show();
    }

    function submitRejection() {
      const reason = document.getElementById('rejectionReason').value.trim();
      const errorDiv = document.getElementById('rejectionError');
      const textarea = document.getElementById('rejectionReason');

      if (!reason) {
        textarea.classList.add('is-invalid');
        errorDiv.style.display = 'block';
        return;
      }

      textarea.classList.remove('is-invalid');
      errorDiv.style.display = 'none';

      fetch(`/admin/employee-request-forms/${currentRequestId}/update-status`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
          status: 'rejected',
          rejection_reason: reason
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
          modal.hide();
          location.reload();
        } else {
          alert('Error rejecting request');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error rejecting request');
      });
    }

    // Delete functionality
    let deleteRequestId = null;

    function confirmDelete(requestId) {
      deleteRequestId = requestId;
      const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
      modal.show();
    }

    function deleteRequest() {
      if (!deleteRequestId) return;

      fetch(`/admin/employee-request-forms/${deleteRequestId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
          modal.hide();
          location.reload();
        } else {
          alert('Error deleting request');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error deleting request');
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
