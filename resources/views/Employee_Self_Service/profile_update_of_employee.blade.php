<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <!-- SweetAlert2 CDN -->
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
    
    /* Fix modal positioning to avoid topbar overlap */
    .modal {
      padding-top: 80px !important;
    }
    
    .modal-dialog {
      margin-top: 20px;
      margin-bottom: 20px;
    }
    
    /* Ensure modal content doesn't get too close to top */
    @media (min-height: 600px) {
      .modal {
        padding-top: 100px !important;
      }
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
            <h2 class="fw-bold mb-1">Employee Profile Updates</h2>
            <p class="text-muted mb-0">
              Welcome back, Admin! Here are the latest profile updates of employees.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee Profile Update</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="simulation-card card">
      <div class="card-header card-header-custom">
        <h4 class="fw-bold mb-0">Employee Profile Updates</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="fw-bold">Update ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Field Name</th>
                <th class="fw-bold">Old Value</th>
                <th class="fw-bold">New Value</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Updated At</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($updates as $update)
              <tr>
                <td>#{{ $update->id }}</td>
                <td>
                  @if($update->employee)
                    @php
                      $firstName = $update->employee->first_name ?? 'Unknown';
                      $lastName = $update->employee->last_name ?? 'Employee';
                      
                      // Profile picture logic - same as other HR modules
                      $profilePicUrl = '';
                      if ($update->employee->profile_picture) {
                        $profilePicUrl = asset('storage/' . $update->employee->profile_picture);
                      } else {
                        // Fallback to UI Avatars with consistent color scheme
                        $employeeId = $update->employee->employee_id ?? 'EMP';
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
                      <div>
                        <span class="fw-semibold">{{ $firstName }} {{ $lastName }}</span>
                        <br><small class="text-muted">{{ $update->employee->employee_id ?? 'N/A' }}</small>
                      </div>
                    </div>
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>{{ $update->formatted_field_name }}</td>
                <td>
                  @if($update->field_name === 'profile_picture' && $update->old_value && $update->old_value !== 'N/A')
                    <img src="{{ asset('storage/' . $update->old_value) }}" alt="Current" style="max-width: 40px; max-height: 40px; border-radius: 4px; object-fit: cover;">
                  @else
                    {{ $update->old_value ?: 'N/A' }}
                  @endif
                </td>
                <td>
                  @if($update->field_name === 'profile_picture' && $update->new_value)
                    <img src="{{ asset('storage/' . $update->new_value) }}" alt="New" style="max-width: 40px; max-height: 40px; border-radius: 4px; object-fit: cover;">
                  @else
                    {{ $update->new_value }}
                  @endif
                </td>
                <td>
                  <span class="badge {{ $update->status == 'approved' ? 'bg-success' : ($update->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                    {{ ucfirst($update->status) }}
                  </span>
                </td>
                <td>{{ $update->requested_at ? $update->requested_at->format('M j, Y') : ($update->updated_at ? \Carbon\Carbon::parse($update->updated_at)->format('M j, Y') : 'N/A') }}</td>
                <td class="text-center action-btns">
                  <div class="btn-group" role="group">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewUpdateDetails({{ $update->id }})" title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    @if($update->status === 'pending')
                      <button class="btn btn-outline-success btn-sm" onclick="approveUpdateWithConfirmation({{ $update->id }})" title="Approve">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button class="btn btn-outline-danger btn-sm" onclick="rejectUpdateWithConfirmation({{ $update->id }})" title="Reject">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    @else
                      <button class="btn btn-outline-secondary btn-sm" disabled title="Already {{ $update->status }}">
                        <i class="bi bi-{{ $update->status === 'approved' ? 'check-circle' : 'x-circle' }}"></i>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No profile update requests found.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- View and Action Modals -->
    @foreach($updates as $update)
    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal{{ $update->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $update->id }}" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="card-header modal-header">
            <h5 class="modal-title" id="viewModalLabel{{ $update->id }}">Profile Update Request #{{ $update->id }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header bg-light">
                    <h6 class="mb-0">Employee Information</h6>
                  </div>
                  <div class="card-body">
                    @if($update->employee)
                      <div class="d-flex align-items-center mb-3">
                        @php
                          $firstName = $update->employee->first_name ?? 'Unknown';
                          $lastName = $update->employee->last_name ?? 'Employee';
                          $profilePicUrl = $update->employee->profile_picture ? asset('storage/' . $update->employee->profile_picture) : "https://ui-avatars.com/api/?name=" . urlencode(substr($firstName, 0, 1) . substr($lastName, 0, 1)) . "&background=6c757d&color=ffffff&size=128&bold=true";
                        @endphp
                        <img src="{{ $profilePicUrl }}" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                          <h6 class="mb-1">{{ $firstName }} {{ $lastName }}</h6>
                          <small class="text-muted">{{ $update->employee->employee_id ?? 'N/A' }}</small>
                        </div>
                      </div>
                      <p><strong>Email:</strong> {{ $update->employee->email ?? 'N/A' }}</p>
                      <p><strong>Position:</strong> {{ $update->employee->position ?? 'N/A' }}</p>
                    @else
                      <p class="text-muted">Employee information not available</p>
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header bg-light">
                    <h6 class="mb-0">Request Details</h6>
                  </div>
                  <div class="card-body">
                    <p><strong>Field:</strong> {{ $update->formatted_field_name }}</p>
                    <p><strong>Status:</strong> 
                      <span class="badge {{ $update->status == 'approved' ? 'bg-success' : ($update->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                        {{ ucfirst($update->status) }}
                      </span>
                    </p>
                    <p><strong>Requested:</strong> {{ $update->requested_at ? $update->requested_at->format('M j, Y g:i A') : 'N/A' }}</p>
                    @if($update->status !== 'pending')
                      <p><strong>Reviewed:</strong> {{ $update->approved_at ? $update->approved_at->format('M j, Y g:i A') : 'N/A' }}</p>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0">Value Changes</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6>Current Value:</h6>
                    <div class="p-3 bg-light rounded">
                      @if($update->field_name === 'profile_picture' && $update->old_value && $update->old_value !== 'N/A')
                        <img src="{{ asset('storage/' . $update->old_value) }}" alt="Current" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">
                      @else
                        {{ $update->old_value ?: 'N/A' }}
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6>Requested Value:</h6>
                    <div class="p-3 bg-primary bg-opacity-10 rounded border-start border-primary border-3">
                      @if($update->field_name === 'profile_picture' && $update->new_value)
                        <img src="{{ asset('storage/' . $update->new_value) }}" alt="New" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">
                      @else
                        {{ $update->new_value }}
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            @if($update->reason)
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0">Reason for Change</h6>
              </div>
              <div class="card-body">
                <p class="mb-0">{{ $update->reason }}</p>
              </div>
            </div>
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            @if($update->status === 'pending')
              <button type="button" class="btn btn-success" onclick="approveUpdateWithConfirmation({{ $update->id }})">
                <i class="bi bi-check-lg me-1"></i>Approve
              </button>
              <button type="button" class="btn btn-danger" onclick="rejectUpdateWithConfirmation({{ $update->id }})">
                <i class="bi bi-x-lg me-1"></i>Reject
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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

  // CSRF token for AJAX requests
  function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  // Admin password verification function
  async function verifyAdminPassword(password) {
    try {
      const response = await fetch('/admin/verify-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken(),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ password: password })
      });
      
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      
      const data = await response.json();
      return data.success || data.valid;
    } catch (error) {
      console.error('Password verification error:', error);
      return false;
    }
  }

  // View update details with SweetAlert
  function viewUpdateDetails(updateId) {
    // Find the update data from the page
    const updateRow = document.querySelector(`tr:has(button[onclick*="${updateId}"])`);
    if (!updateRow) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Update details not found'
      });
      return;
    }

    const cells = updateRow.querySelectorAll('td');
    const employeeName = cells[1]?.querySelector('.fw-semibold')?.textContent || 'N/A';
    const employeeId = cells[1]?.querySelector('.text-muted')?.textContent || 'N/A';
    const fieldName = cells[2]?.textContent || 'N/A';
    const oldValue = cells[3]?.textContent?.trim() || 'N/A';
    const newValue = cells[4]?.textContent?.trim() || 'N/A';
    const status = cells[5]?.querySelector('.badge')?.textContent || 'N/A';
    const updatedAt = cells[6]?.textContent || 'N/A';

    Swal.fire({
      title: `Profile Update Request #${updateId}`,
      html: `
        <div class="text-start">
          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-primary"><i class="bi bi-person-circle me-2"></i>Employee Information</h6>
              <p class="mb-1"><strong>Name:</strong> ${employeeName}</p>
              <p class="mb-1"><strong>ID:</strong> ${employeeId}</p>
            </div>
            <div class="col-md-6">
              <h6 class="text-info"><i class="bi bi-info-circle me-2"></i>Request Details</h6>
              <p class="mb-1"><strong>Field:</strong> ${fieldName}</p>
              <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary">${status}</span></p>
              <p class="mb-1"><strong>Date:</strong> ${updatedAt}</p>
            </div>
          </div>
          <div class="card">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Value Changes</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="text-secondary">Current Value:</h6>
                  <div class="p-2 bg-light rounded border">${oldValue}</div>
                </div>
                <div class="col-md-6">
                  <h6 class="text-primary">Requested Value:</h6>
                  <div class="p-2 bg-primary bg-opacity-10 rounded border border-primary">${newValue}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `,
      width: '800px',
      showCloseButton: true,
      confirmButtonText: 'Close',
      confirmButtonColor: '#6c757d'
    });
  }

  // Approve update with password confirmation
  function approveUpdateWithConfirmation(updateId) {
    Swal.fire({
      title: 'Approve Profile Update',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Security Notice:</strong> Admin password verification is required to approve profile update requests.
          </div>
          <p class="mb-3">You are about to approve profile update request #${updateId}. This action will apply the requested changes to the employee's profile.</p>
        </div>
        <div class="form-group">
          <label for="admin-password-approve" class="form-label fw-bold">Admin Password:</label>
          <input type="password" id="admin-password-approve" class="form-control" placeholder="Enter your admin password" minlength="3" required>
          <div class="form-text">Enter your admin password to confirm this action.</div>
        </div>
      `,
      width: '600px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Approve Update',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      preConfirm: async () => {
        const password = document.getElementById('admin-password-approve').value;
        
        if (!password || password.length < 3) {
          Swal.showValidationMessage('Please enter a valid admin password (minimum 3 characters)');
          return false;
        }
        
        // Show loading
        Swal.fire({
          title: 'Verifying Password...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Verify password
        const isValidPassword = await verifyAdminPassword(password);
        
        if (!isValidPassword) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The admin password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
          return false;
        }
        
        return { password };
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        submitApproveUpdate(updateId, result.value.password);
      }
    });
  }

  // Submit approve update
  function submitApproveUpdate(updateId, password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Approving profile update request...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch(`/admin/profile-updates/${updateId}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCSRFToken(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        status: 'approved',
        password_verification: password
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Update Approved!',
          text: 'The profile update request has been approved successfully.',
          confirmButtonColor: '#198754',
          timer: 3000,
          timerProgressBar: true
        }).then(() => {
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Approval Failed',
          text: data.message || 'Failed to approve the profile update request. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Network Error',
        text: 'An error occurred while processing the request. Please check your connection and try again.',
        confirmButtonColor: '#dc3545'
      });
    });
  }

  // Reject update with password confirmation
  function rejectUpdateWithConfirmation(updateId) {
    Swal.fire({
      title: 'Reject Profile Update',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> You are about to reject profile update request #${updateId}.
          </div>
          <p class="mb-3">Please provide a reason for rejecting this request and confirm with your admin password.</p>
        </div>
        <div class="form-group mb-3">
          <label for="rejection-reason" class="form-label fw-bold">Rejection Reason:</label>
          <textarea id="rejection-reason" class="form-control" rows="3" placeholder="Please explain why this request is being rejected..." required></textarea>
        </div>
        <div class="form-group">
          <label for="admin-password-reject" class="form-label fw-bold">Admin Password:</label>
          <input type="password" id="admin-password-reject" class="form-control" placeholder="Enter your admin password" minlength="3" required>
          <div class="form-text">Enter your admin password to confirm this action.</div>
        </div>
      `,
      width: '600px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-x-lg me-1"></i>Reject Update',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      preConfirm: async () => {
        const reason = document.getElementById('rejection-reason').value.trim();
        const password = document.getElementById('admin-password-reject').value;
        
        if (!reason) {
          Swal.showValidationMessage('Please provide a reason for rejecting this request');
          return false;
        }
        
        if (!password || password.length < 3) {
          Swal.showValidationMessage('Please enter a valid admin password (minimum 3 characters)');
          return false;
        }
        
        // Show loading
        Swal.fire({
          title: 'Verifying Password...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Verify password
        const isValidPassword = await verifyAdminPassword(password);
        
        if (!isValidPassword) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The admin password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
          return false;
        }
        
        return { reason, password };
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        submitRejectUpdate(updateId, result.value.reason, result.value.password);
      }
    });
  }

  // Submit reject update
  function submitRejectUpdate(updateId, reason, password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Rejecting profile update request...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch(`/admin/profile-updates/${updateId}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCSRFToken(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        status: 'rejected',
        rejection_reason: reason,
        password_verification: password
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Update Rejected',
          text: 'The profile update request has been rejected successfully.',
          confirmButtonColor: '#198754',
          timer: 3000,
          timerProgressBar: true
        }).then(() => {
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Rejection Failed',
          text: data.message || 'Failed to reject the profile update request. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Network Error',
        text: 'An error occurred while processing the request. Please check your connection and try again.',
        confirmButtonColor: '#dc3545'
      });
    });
  }

  // Fix old values with password confirmation
  function fixOldValuesWithConfirmation() {
    Swal.fire({
      title: 'Fix Old Values',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>System Maintenance:</strong> This operation will update all existing profile update records to show proper current values.
          </div>
          <p class="mb-3">This maintenance operation will:</p>
          <ul class="text-start">
            <li>Update all profile update records with missing current values</li>
            <li>Ensure proper before/after comparison display</li>
            <li>Improve data consistency across the system</li>
          </ul>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Note:</strong> This operation may take a few moments to complete.
          </div>
        </div>
        <div class="form-group">
          <label for="admin-password-fix" class="form-label fw-bold">Admin Password:</label>
          <input type="password" id="admin-password-fix" class="form-control" placeholder="Enter your admin password" minlength="3" required>
          <div class="form-text">Enter your admin password to confirm this maintenance operation.</div>
        </div>
      `,
      width: '600px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-wrench me-1"></i>Fix Old Values',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#ffc107',
      cancelButtonColor: '#6c757d',
      preConfirm: async () => {
        const password = document.getElementById('admin-password-fix').value;
        
        if (!password || password.length < 3) {
          Swal.showValidationMessage('Please enter a valid admin password (minimum 3 characters)');
          return false;
        }
        
        // Show loading
        Swal.fire({
          title: 'Verifying Password...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Verify password
        const isValidPassword = await verifyAdminPassword(password);
        
        if (!isValidPassword) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The admin password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
          return false;
        }
        
        return { password };
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        submitFixOldValues(result.value.password);
      }
    });
  }

  // Submit fix old values
  function submitFixOldValues(password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Fixing old values in profile update records...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch('/admin/profile-updates/fix-old-values', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCSRFToken(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        password_verification: password
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Values Fixed!',
          text: data.message || 'Old values have been fixed successfully.',
          confirmButtonColor: '#198754',
          timer: 3000,
          timerProgressBar: true
        }).then(() => {
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Fix Failed',
          text: data.message || 'Failed to fix old values. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Network Error',
        text: 'An error occurred while processing the request. Please check your connection and try again.',
        confirmButtonColor: '#dc3545'
      });
    });
  }
</script>
</body>
</html>
