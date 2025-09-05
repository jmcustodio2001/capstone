<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <li class="breadcrumb-item active" aria-current="page">Profile Updates</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="simulation-card card">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Profile Updates</h4>
        <button class="btn btn-warning btn-sm" onclick="fixOldValues()" title="Fix missing current values in existing records">
          <i class="bi bi-wrench"></i> Fix Old Values
        </button>
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
                  <button class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $update->id }}" title="View Details">
                    <i class="bi bi-eye"></i>
                  </button>
                  @if($update->status === 'pending')
                    <button class="btn btn-outline-success btn-sm me-1" onclick="approveUpdate({{ $update->id }})" title="Approve">
                      <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="rejectUpdate({{ $update->id }})" title="Reject">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  @else
                    <button class="btn btn-outline-secondary btn-sm" disabled title="Already {{ $update->status }}">
                      <i class="bi bi-{{ $update->status === 'approved' ? 'check-circle' : 'x-circle' }}"></i>
                    </button>
                  @endif
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
              <button type="button" class="btn btn-success" onclick="approveUpdate({{ $update->id }})">
                <i class="bi bi-check-lg me-1"></i>Approve
              </button>
              <button type="button" class="btn btn-danger" onclick="rejectUpdate({{ $update->id }})">
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
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // Approve update function
  function approveUpdate(updateId) {
    if (confirm('Are you sure you want to approve this profile update request?')) {
      fetch(`/admin/profile-updates/${updateId}/approve`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
          status: 'approved'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Close any open modals
          const modals = document.querySelectorAll('.modal.show');
          modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) modalInstance.hide();
          });
          
          // Show success message and reload page
          alert('Profile update request approved successfully!');
          window.location.reload();
        } else {
          alert('Error: ' + (data.message || 'Failed to approve update'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the update');
      });
    }
  }

  // Reject update function
  function rejectUpdate(updateId) {
    const reason = prompt('Please provide a reason for rejecting this request:');
    if (reason !== null && reason.trim() !== '') {
      fetch(`/admin/profile-updates/${updateId}/reject`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
          status: 'rejected',
          rejection_reason: reason.trim()
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Close any open modals
          const modals = document.querySelectorAll('.modal.show');
          modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) modalInstance.hide();
          });
          
          // Show success message and reload page
          alert('Profile update request rejected successfully!');
          window.location.reload();
        } else {
          alert('Error: ' + (data.message || 'Failed to reject update'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the update');
      });
    }
  }

  // Fix old values function
  function fixOldValues() {
    if (confirm('This will update all existing profile update records to show proper current values. Continue?')) {
      fetch('/admin/profile-updates/fix-old-values', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          window.location.reload();
        } else {
          alert('Error: ' + (data.message || 'Failed to fix old values'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fixing old values');
      });
    }
  }
</script>
</body>
</html>
