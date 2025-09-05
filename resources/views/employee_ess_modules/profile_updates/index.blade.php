<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels - Profile Updates</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --light-bg: #f8f9fa;
    }

    body {
      background-color: #f8f9fa !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .profile-update-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      transition: transform 0.3s ease;
    }

    .profile-update-card:hover {
      transform: translateY(-5px);
    }

    .card-header-custom {
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
      border-radius: 12px 12px 0 0 !important;
    }

    .avatar-circle {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border-radius: 50%;
      font-weight: 600;
    }

    .action-btns .btn {
      padding: 0.35rem 0.65rem;
      font-size: 0.875rem;
      border-radius: 6px;
    }

    .status-badge {
      padding: 0.5em 0.8em;
      font-weight: 500;
      letter-spacing: 0.5px;
      border-radius: 6px;
    }

    .profile-summary {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .profile-header {
      display: flex;
      align-items: center;
      margin-bottom: 2rem;
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2rem;
      font-weight: 700;
      margin-right: 1.5rem;
    }

    .profile-info h4 {
      margin-bottom: 0.25rem;
      font-weight: 700;
    }

    .profile-info p {
      color: #6c757d;
      margin-bottom: 0.5rem;
    }

    .profile-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1.5rem;
    }

    .stat-item {
      text-align: center;
      padding: 1rem;
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 0.25rem;
    }

    .stat-label {
      color: #6c757d;
      font-size: 0.875rem;
    }

    .filter-container {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .btn-new-request {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 50px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
      transition: all 0.3s ease;
    }

    .btn-new-request:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
    }

    .update-row {
      transition: all 0.3s ease;
    }

    .update-row:hover {
      background-color: rgba(67, 97, 238, 0.05) !important;
    }

    .status-pending {
      background-color: rgba(255, 193, 7, 0.1);
      color: #ffc107;
    }

    .status-approved {
      background-color: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }

    .status-rejected {
      background-color: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .modal-header {
      border-bottom: 1px solid #eaeaea;
      padding: 1.5rem;
    }

    .modal-footer {
      border-top: 1px solid #eaeaea;
      padding: 1rem 1.5rem;
    }

    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      border: 1px solid #ced4da;
    }

    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
      border-color: var(--primary-color);
    }

    .detail-card {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .detail-label {
      font-weight: 600;
      color: #495057;
    }

    .detail-value {
      color: #6c757d;
    }

    .change-highlight {
      background-color: rgba(76, 201, 240, 0.1);
      padding: 0.5rem;
      border-radius: 6px;
      border-left: 3px solid var(--primary-color);
    }

    @media (max-width: 768px) {
      .profile-header {
        flex-direction: column;
        text-align: center;
      }

      .profile-avatar {
        margin-right: 0;
        margin-bottom: 1rem;
      }

      .profile-stats {
        grid-template-columns: 1fr;
      }

      .action-btns {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  </style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

<main id="main-content" style="margin-left: 280px; margin-top: 4rem; padding: 2rem;">
  <!-- Page Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">Profile Management</h2>
          <p class="text-muted mb-0">
            View and manage your profile information and update requests.
          </p>
        </div>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Profile Updates</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Profile Summary -->
  <div class="card-header profile-summary">
    <div class="profile-header">
      <div class="profile-avatar">
        @if($employee && $employee->profile_picture)
          <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        @elseif($employee)
          {{ substr($employee->first_name ?? '', 0, 1) }}{{ substr($employee->last_name ?? '', 0, 1) }}
        @else
          U
        @endif
      </div>
      <div class="profile-info">
        <h4>
          @if($employee)
            {{ $employee->first_name ?? 'Unknown' }} {{ $employee->last_name ?? 'User' }}
          @else
            Unknown User
          @endif
        </h4>
        <p>
          @if($employee)
            {{ $employee->position ?? 'Position Not Set' }}
          @else
            Position Not Set
          @endif
        </p>
        <div>
          <span class="badge bg-light text-dark me-2">
            <i class="bi bi-person-badge me-1"></i> ID:
            @if($employee)
              {{ $employee->employee_id ?? 'Not Available' }}
            @else
              Not Available
            @endif
          </span>
          <span class="badge bg-light text-dark me-2">
            <i class="bi bi-envelope me-1"></i>
            @if($employee)
              {{ $employee->email ?? 'No Email' }}
            @else
              No Email
            @endif
          </span>
          <span class="badge bg-light text-dark">
            <i class="bi bi-telephone me-1"></i>
            @if($employee)
              {{ $employee->phone_number ?? 'No Phone' }}
            @else
              No Phone
            @endif
          </span>
        </div>
      </div>
    </div>

    <div class="profile-stats">
      <div class="stat-item">
        <div class="stat-value">{{ $updates->count() }}</div>
        <div class="stat-label">Total Requests</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ $updates->where('status', 'pending')->count() }}</div>
        <div class="stat-label">Pending</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ $updates->where('status', 'approved')->count() }}</div>
        <div class="stat-label">Approved</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ $updates->where('status', 'rejected')->count() }}</div>
        <div class="stat-label">Rejected</div>
      </div>
    </div>
  </div>

  <!-- Action Buttons and Filters -->
  <div class="filter-container">
    <div class="row">
      <div class="col-md-8">
        <div class="row">
          <div class="col-md-4 mb-2">
            <label for="status-filter" class="form-label">Status</label>
            <select class="form-select" id="status-filter">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="col-md-4 mb-2">
            <label for="field-filter" class="form-label">Field</label>
            <select class="form-select" id="field-filter">
              <option value="">All Fields</option>
              <option value="first_name">First Name</option>
              <option value="last_name">Last Name</option>
              <option value="email">Email</option>
              <option value="phone">Phone</option>
              <option value="address">Address</option>
              <option value="profile_picture">Profile Picture</option>
            </select>
          </div>
          <div class="col-md-4 mb-2">
            <label for="date-filter" class="form-label">Date Range</label>
            <select class="form-select" id="date-filter">
              <option value="">All Dates</option>
              <option value="this_week">This Week</option>
              <option value="this_month">This Month</option>
              <option value="last_month">Last Month</option>
            </select>
          </div>
        </div>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <div class="d-flex w-100">
          <input type="text" id="search-updates" class="form-control me-2" placeholder="Search requests...">
          <button id="reset-filters" class="btn btn-outline-secondary">Reset</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Updates Table -->
  <div class="profile-update-card card">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0">Profile Update History</h4>
      <button class="btn btn-new-request" data-bs-toggle="modal" data-bs-target="#newUpdateModal">
        <i class="bi bi-plus-circle me-2"></i>New Update Request
      </button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="updatesTable">
          <thead class="table-light">
            <tr>
              <th class="fw-bold">Request ID</th>
              <th class="fw-bold">Field</th>
              <th class="fw-bold">Current Value</th>
              <th class="fw-bold">Requested Value</th>
              <th class="fw-bold">Status</th>
              <th class="fw-bold">Requested Date</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($updates as $update)
            <tr class="update-row" data-update-id="{{ $update->id }}" data-status="{{ $update->status }}" data-field="{{ $update->field_name }}" data-old-value="{{ $update->old_value }}" data-new-value="{{ $update->new_value }}" data-reason="{{ $update->reason ?? 'No reason provided' }}" data-requested-at="{{ $update->requested_at }}" data-approved-at="{{ $update->approved_at }}" data-approved-by="{{ $update->approved_by }}" data-rejection-reason="{{ $update->rejection_reason }}">
              <td>
                <div class="fw-semibold">#{{ $update->id }}</div>
                <small class="text-muted">{{ $update->requested_at ? $update->requested_at->format('m/d/Y') : 'N/A' }}</small>
              </td>
              <td>
                <span class="fw-semibold">{{ $update->formatted_field_name }}</span>
              </td>
              <td>
                @if($update->field_name === 'profile_picture' && $update->old_value && $update->old_value !== 'N/A')
                  <img src="{{ asset('storage/' . $update->old_value) }}" alt="Current" style="max-width: 40px; max-height: 40px; border-radius: 4px; object-fit: cover;">
                @else
                  <span class="text-muted">{{ Str::limit($update->old_value, 30) }}</span>
                @endif
              </td>
              <td>
                @if($update->field_name === 'profile_picture' && $update->new_value)
                  <img src="{{ asset('storage/' . $update->new_value) }}" alt="New" style="max-width: 40px; max-height: 40px; border-radius: 4px; object-fit: cover;">
                @else
                  <span class="fw-medium">{{ Str::limit($update->new_value, 30) }}</span>
                @endif
              </td>
              <td>
                <span class="badge status-badge status-{{ $update->status }}">
                  {{ ucfirst($update->status) }}
                </span>
              </td>
              <td>{{ $update->requested_at ? $update->requested_at->format('M j, Y') : 'N/A' }}</td>
              <td class="text-center action-btns">
                <button class="btn btn-sm btn-info text-white me-1" onclick="viewUpdate({{ $update->id }})">
                  <i class="bi bi-eye"></i>
                </button>
                @if($update->status === 'pending')
                  <button class="btn btn-sm btn-warning me-1" onclick="editUpdate({{ $update->id }})">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" onclick="deleteUpdate({{ $update->id }})">
                    <i class="bi bi-trash"></i>
                  </button>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-info-circle me-2"></i>No profile update requests found.
                <br><small>Click "New Update Request" to submit your first request.</small>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if($updates->count() > 0)
        <div class="d-flex justify-content-center mt-4">
          <!-- Pagination would go here if using paginated data -->
        </div>
      @endif
    </div>
  </div>
</main>

<!-- View Update Details Modal -->
<div class="modal fade" id="viewUpdateModal" tabindex="-1" aria-labelledby="viewUpdateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewUpdateModalLabel">Profile Update Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="detail-card">
              <div class="detail-label">Request Information</div>
              <div class="mb-2">
                <strong>Request ID:</strong>
                <span id="view-request-id" class="ms-2"></span>
              </div>
              <div class="mb-2">
                <strong>Field Name:</strong>
                <span id="view-field-name" class="ms-2"></span>
              </div>
              <div class="mb-2">
                <strong>Status:</strong>
                <span id="view-status" class="ms-2"></span>
              </div>
              <div class="mb-2">
                <strong>Requested Date:</strong>
                <span id="view-requested-date" class="ms-2"></span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="detail-card">
              <div class="detail-label">Review Information</div>
              <div class="mb-2">
                <strong>Reviewed By:</strong>
                <span id="view-reviewed-by" class="ms-2">Not reviewed yet</span>
              </div>
              <div class="mb-2">
                <strong>Reviewed Date:</strong>
                <span id="view-reviewed-date" class="ms-2">N/A</span>
              </div>
              <div>
                <strong>Review Notes:</strong>
                <div id="view-review-notes" class="mt-1 p-2 bg-white rounded border">No notes provided</div>
              </div>
            </div>
          </div>
        </div>

        <div class="detail-card">
          <div class="detail-label">Value Changes</div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <strong>Current Value:</strong>
                <div class="p-2 bg-white rounded border mt-1">
                  <div id="view-current-value" class="text-muted"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <strong>Requested Value:</strong>
                <div class="p-2 bg-white rounded border mt-1 change-highlight">
                  <div id="view-new-value" class="fw-medium"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="detail-card">
          <div class="detail-label">Reason for Change</div>
          <div class="p-2 bg-white rounded border">
            <span id="view-reason"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="edit-from-view" style="display: none;" onclick="openEditFromView()">Edit Request</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Update Request Modal -->
<div class="modal fade" id="editUpdateModal" tabindex="-1" aria-labelledby="editUpdateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUpdateModalLabel">Edit Profile Update Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUpdateForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_field_name" class="form-label">Field to Update</label>
            <select class="form-select" id="edit_field_name" name="field_name" required onchange="updateEditCurrentValue()">
              <option value="">Select field to update</option>
              <option value="first_name">First Name</option>
              <option value="last_name">Last Name</option>
              <option value="email">Email Address</option>
              <option value="phone">Phone Number</option>
              <option value="address">Address</option>
              <option value="profile_picture">Profile Picture</option>
              <option value="emergency_contact_name">Emergency Contact Name</option>
              <option value="emergency_contact_phone">Emergency Contact Phone</option>
              <option value="emergency_contact_relationship">Emergency Contact Relationship</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Current Value</label>
            <div class="p-2 bg-light rounded">
              <span id="edit-current-value-display">Select a field to see current value</span>
            </div>
          </div>

          <div class="mb-3">
            <label for="edit_new_value" class="form-label">New Value</label>
            <input type="text" class="form-control" id="edit_new_value" name="new_value" required maxlength="500">
            <input type="file" class="form-control" id="edit_new_value_file" name="new_value_file" accept="image/*" style="display: none;">
            <div class="form-text" id="edit_new_value_help">Enter the new value you want for this field.</div>
          </div>

          <div class="mb-3">
            <label for="edit_reason" class="form-label">Reason for Change</label>
            <textarea class="form-control" id="edit_reason" name="reason" rows="3" maxlength="1000" placeholder="Please explain why you need this change..."></textarea>
            <div class="form-text">Optional: Explain why you need this change.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- New Update Request Modal -->
<div class="modal fade" id="newUpdateModal" tabindex="-1" aria-labelledby="newUpdateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newUpdateModalLabel">Request Profile Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('employee.profile_updates.store') }}" method="POST" id="newUpdateForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="field_name" class="form-label">Field to Update</label>
            <select class="form-select" id="field_name" name="field_name" required onchange="updateCurrentValue()">
              <option value="">Select field to update</option>
              <option value="first_name">First Name</option>
              <option value="last_name">Last Name</option>
              <option value="email">Email Address</option>
              <option value="phone">Phone Number</option>
              <option value="address">Address</option>
              <option value="profile_picture">Profile Picture</option>
              <option value="emergency_contact_name">Emergency Contact Name</option>
              <option value="emergency_contact_phone">Emergency Contact Phone</option>
              <option value="emergency_contact_relationship">Emergency Contact Relationship</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Current Value</label>
            <div class="p-2 bg-light rounded">
              <span id="current-value-display">Select a field to see current value</span>
            </div>
          </div>

          <div class="mb-3">
            <label for="new_value" class="form-label">New Value</label>
            <input type="text" class="form-control" id="new_value" name="new_value" required maxlength="500">
            <input type="file" class="form-control" id="new_value_file" name="new_value_file" accept="image/*" style="display: none;">
            <div class="form-text" id="new_value_help">Enter the new value you want for this field.</div>
          </div>

          <div class="mb-3">
            <label for="reason" class="form-label">Reason for Change</label>
            <textarea class="form-control" id="reason" name="reason" rows="3" maxlength="1000" placeholder="Please explain why you need this change..."></textarea>
            <div class="form-text">Optional: Explain why you need this change.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitNewUpdate">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Password Verification Modal -->
<div class="modal fade" id="passwordVerificationModal" tabindex="-1" aria-labelledby="passwordVerificationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordVerificationModalLabel">Verify Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="password-input" class="form-label">Please enter your password to confirm:</label>
          <input
            type="password"
            class="form-control"
            id="password-input"
            placeholder="Enter your password"
            autocapitalize="off"
            autocorrect="off"
            autocomplete="current-password"
            required
          >
          <div class="form-text">Password must be at least 6 characters long.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="verifyPasswordBtn">Verify & Submit</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Employee data (current values from server)
  const employeeData = {
    first_name: "@if($employee){{ addslashes($employee->first_name ?? '') }}@endif",
    last_name: "@if($employee){{ addslashes($employee->last_name ?? '') }}@endif",
    email: "@if($employee){{ addslashes($employee->email ?? '') }}@endif",
    phone: "@if($employee){{ addslashes($employee->phone_number ?? '') }}@endif",
    phone_number: "@if($employee){{ addslashes($employee->phone_number ?? '') }}@endif",
    address: "@if($employee){{ addslashes($employee->address ?? '') }}@endif",
    profile_picture: "@if($employee){{ addslashes($employee->profile_picture ?? '') }}@endif",
    emergency_contact_name: "@if($employee){{ addslashes($employee->emergency_contact_name ?? '') }}@endif",
    emergency_contact_phone: "@if($employee){{ addslashes($employee->emergency_contact_phone ?? '') }}@endif",
    emergency_contact_relationship: "@if($employee){{ addslashes($employee->emergency_contact_relationship ?? '') }}@endif"
  };

  // Update current value display when field selection changes
  function updateCurrentValue() {
    const fieldName = document.getElementById('field_name').value;
    const displayElement = document.getElementById('current-value-display');
    const textInput = document.getElementById('new_value');
    const fileInput = document.getElementById('new_value_file');
    const helpText = document.getElementById('new_value_help');

    if (fieldName === 'profile_picture') {
      // Show file input for profile picture
      textInput.style.display = 'none';
      fileInput.style.display = 'block';
      textInput.removeAttribute('required');
      fileInput.setAttribute('required', 'required');
      helpText.textContent = 'Upload a new profile picture (JPG, PNG, GIF - max 2MB)';

      if (employeeData[fieldName]) {
        displayElement.innerHTML = `<img src="/storage/${employeeData[fieldName]}" alt="Current Profile Picture" style="max-width: 100px; max-height: 100px; border-radius: 8px;">`;
      } else {
        displayElement.textContent = "No profile picture set";
      }
    } else {
      // Show text input for other fields
      textInput.style.display = 'block';
      fileInput.style.display = 'none';
      textInput.setAttribute('required', 'required');
      fileInput.removeAttribute('required');
      helpText.textContent = 'Enter the new value you want for this field.';

      if (fieldName && employeeData[fieldName] && employeeData[fieldName].trim() !== '') {
        displayElement.textContent = employeeData[fieldName];
      } else if (fieldName) {
        displayElement.textContent = "Not set";
      } else {
        displayElement.textContent = "Select a field to see current value";
      }
    }
  }

  // Update current value display for edit modal
  function updateEditCurrentValue() {
    const fieldName = document.getElementById('edit_field_name').value;
    const displayElement = document.getElementById('edit-current-value-display');
    const textInput = document.getElementById('edit_new_value');
    const fileInput = document.getElementById('edit_new_value_file');
    const helpText = document.getElementById('edit_new_value_help');

    if (fieldName === 'profile_picture') {
      // Show file input for profile picture
      textInput.style.display = 'none';
      fileInput.style.display = 'block';
      textInput.removeAttribute('required');
      fileInput.setAttribute('required', 'required');
      helpText.textContent = 'Upload a new profile picture (JPG, PNG, GIF - max 2MB)';

      if (employeeData[fieldName] && employeeData[fieldName].trim() !== '') {
        displayElement.innerHTML = `<img src="/storage/${employeeData[fieldName]}" alt="Current Profile Picture" style="max-width: 100px; max-height: 100px; border-radius: 8px;">`;
      } else {
        displayElement.textContent = "No profile picture set";
      }
    } else {
      // Show text input for other fields
      textInput.style.display = 'block';
      fileInput.style.display = 'none';
      textInput.setAttribute('required', 'required');
      fileInput.removeAttribute('required');
      helpText.textContent = 'Enter the new value you want for this field.';

      if (fieldName && employeeData[fieldName] && employeeData[fieldName].trim() !== '') {
        displayElement.textContent = employeeData[fieldName];
      } else if (fieldName) {
        displayElement.textContent = "Not set";
      } else {
        displayElement.textContent = "Select a field to see current value";
      }
    }
  }

  // Filter functionality
  document.getElementById('status-filter').addEventListener('change', filterTable);
  document.getElementById('field-filter').addEventListener('change', filterTable);
  document.getElementById('date-filter').addEventListener('change', filterTable);
  document.getElementById('search-updates').addEventListener('keyup', filterTable);

  document.getElementById('reset-filters').addEventListener('click', function() {
    document.getElementById('status-filter').value = '';
    document.getElementById('field-filter').value = '';
    document.getElementById('date-filter').value = '';
    document.getElementById('search-updates').value = '';
    filterTable();
  });

  function filterTable() {
    const statusFilter = document.getElementById('status-filter').value;
    const fieldFilter = document.getElementById('field-filter').value;
    const dateFilter = document.getElementById('date-filter').value;
    const searchFilter = document.getElementById('search-updates').value.toLowerCase();

    const rows = document.querySelectorAll('#updatesTable tbody tr');

    rows.forEach(row => {
      let showRow = true;
      const statusCell = row.cells[4].textContent.toLowerCase();
      const fieldCell = row.cells[1].textContent.toLowerCase();
      const dateCell = row.cells[5].textContent;
      const rowText = row.textContent.toLowerCase();

      // Apply status filter
      if (statusFilter && statusCell !== statusFilter) {
        showRow = false;
      }

      // Apply field filter
      if (fieldFilter) {
        const fieldMapping = {
          'first_name': 'first name',
          'last_name': 'last name',
          'email': 'email',
          'phone': 'phone',
          'address': 'address'
        };

        if (fieldCell !== fieldMapping[fieldFilter]) {
          showRow = false;
        }
      }

      // Apply date filter (simplified)
      if (dateFilter) {
        // In a real application, you would implement proper date filtering
        showRow = true; // For demo purposes
      }

      // Apply search filter
      if (searchFilter && !rowText.includes(searchFilter)) {
        showRow = false;
      }

      row.style.display = showRow ? '' : 'none';
    });
  }

  // View update details
  function viewUpdate(updateId) {
    // Fetch actual update details from the server
    fetch(`/employee/profile-updates/${updateId}/details`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          populateViewModal(data.update);
        } else {
          throw new Error(data.message || 'Failed to fetch update details');
        }
      })
      .catch(error => {
        console.error('Error fetching update details:', error);
        // Fallback to basic data from table row
        const row = document.querySelector(`tr[data-update-id="${updateId}"]`);
        if (row) {
          const update = {
            id: updateId,
            field_name: row.dataset.field,
            formatted_field_name: row.dataset.field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()),
            old_value: row.dataset.oldValue,
            new_value: row.dataset.newValue,
            status: row.dataset.status,
            requested_at: row.dataset.requestedAt,
            approved_at: row.dataset.approvedAt,
            approved_by: row.dataset.approvedBy,
            rejection_reason: row.dataset.rejectionReason,
            reason: row.dataset.reason
          };
          populateViewModal(update);
        }
      });
  }

  function populateViewModal(update) {

    // Populate the view modal
    document.getElementById('view-request-id').textContent = '#' + update.id;
    document.getElementById('view-field-name').textContent = update.formatted_field_name;

    // Handle profile picture display in view modal
    const currentValueElement = document.getElementById('view-current-value');
    const newValueElement = document.getElementById('view-new-value');

    if (update.field_name === 'profile_picture') {
      if (update.old_value && update.old_value !== 'N/A') {
        currentValueElement.innerHTML = `<img src="/storage/${update.old_value}" alt="Current Profile Picture" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">`;
      } else {
        currentValueElement.textContent = 'No profile picture set';
      }

      if (update.new_value) {
        newValueElement.innerHTML = `<img src="/storage/${update.new_value}" alt="New Profile Picture" style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;">`;
      } else {
        newValueElement.textContent = 'No new picture uploaded';
      }
    } else {
      currentValueElement.textContent = update.old_value;
      newValueElement.textContent = update.new_value;
    }

    document.getElementById('view-reason').textContent = update.reason;
    document.getElementById('view-requested-date').textContent = new Date(update.requested_at).toLocaleDateString('en-US', {
      year: 'numeric', month: 'short', day: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });

    // Set status with proper styling
    const statusElement = document.getElementById('view-status');
    statusElement.innerHTML = `<span class="badge status-${update.status}">${update.status.charAt(0).toUpperCase() + update.status.slice(1)}</span>`;

    // Show review information based on actual status
    if (update.status !== 'pending') {
      // Show actual review information for approved/rejected requests
      document.getElementById('view-reviewed-by').textContent = update.approved_by ? `Admin User #${update.approved_by}` : 'System Admin';
      document.getElementById('view-reviewed-date').textContent = update.formatted_approved_date ||
        (update.approved_at ? new Date(update.approved_at).toLocaleDateString('en-US', {
          year: 'numeric', month: 'short', day: 'numeric',
          hour: '2-digit', minute: '2-digit'
        }) : 'N/A');

      // Show appropriate review notes based on status
      let reviewNotes = 'No additional notes provided.';
      if (update.rejection_reason) {
        reviewNotes = update.rejection_reason;
      } else if (update.status === 'approved') {
        reviewNotes = 'Request approved successfully.';
      } else if (update.status === 'rejected') {
        reviewNotes = 'Request was rejected.';
      }
      document.getElementById('view-review-notes').textContent = reviewNotes;
    } else {
      // Reset to default values for pending requests
      document.getElementById('view-reviewed-by').textContent = 'Not reviewed yet';
      document.getElementById('view-reviewed-date').textContent = 'N/A';
      document.getElementById('view-review-notes').textContent = 'No notes provided';
    }

    // Show edit button only for pending requests
    const editButton = document.getElementById('edit-from-view');
    if (update.status === 'pending') {
      editButton.style.display = 'inline-block';
    } else {
      editButton.style.display = 'none';
    }

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('viewUpdateModal'));
    modal.show();
  }

  // Edit update function
  function editUpdate(updateId) {
    // Get the update data from the table row
    const row = document.querySelector(`tr[data-update-id="${updateId}"]`);
    if (row) {
      const fieldName = row.dataset.field;
      const newValue = row.dataset.newValue;
      const reason = row.dataset.reason;

      // Populate the edit modal
      document.getElementById('edit_field_name').value = fieldName;
      document.getElementById('edit_new_value').value = newValue;
      document.getElementById('edit_reason').value = reason;

      // Update the current value display
      updateEditCurrentValue();

      // Set the form action
      document.getElementById('editUpdateForm').action = `/employee/profile-updates/${updateId}`;

      // Show the edit modal
      const modal = new bootstrap.Modal(document.getElementById('editUpdateModal'));
      modal.show();
    }
  }

  // Delete update function
  function deleteUpdate(updateId) {
    if (confirm('Are you sure you want to cancel this profile update request?')) {
      // Create a form to send DELETE request
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/employee/profile-updates/${updateId}`;

      // Add CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);

      // Add method override for DELETE
      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'DELETE';
      form.appendChild(methodInput);

      document.body.appendChild(form);
      form.submit();
    }
  }

  // Open edit from view
  function openEditFromView() {
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewUpdateModal'));
    viewModal.hide();

    // Get the update ID from the modal
    const updateId = document.getElementById('view-request-id').textContent.replace('#', '');

    // Call the editUpdate function to open the edit modal
    editUpdate(updateId);
  }

  // Auto-refresh page every 60 seconds to show status updates
  setInterval(function() {
    const openModals = document.querySelectorAll('.modal.show');
    if (openModals.length === 0) {
      // Check if there are any pending requests that might have been updated
      const pendingCount = document.querySelectorAll('.status-pending').length;
      if (pendingCount > 0) {
        // Show a subtle notification about auto-refresh
        console.log('Auto-refreshing profile updates...');
        // In a real application, you would fetch updates via AJAX
      }
    }
  }, 60000);

  // Sweet security: Confirmation before submitting new update request
  document.getElementById('submitNewUpdate').addEventListener('click', function(e) {
    e.preventDefault();
    const form = document.getElementById('newUpdateForm');
    Swal.fire({
      title: 'Are you sure?',
      text: 'Do you want to submit this profile update request?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
      if (result.isConfirmed) {
        // Show password verification modal
        const passwordModal = new bootstrap.Modal(document.getElementById('passwordVerificationModal'));
        passwordModal.show();

        // Focus password input when modal opens
        document.getElementById('passwordVerificationModal').addEventListener('shown.bs.modal', function() {
          document.getElementById('password-input').focus();
        });
      }
    });
  });

  // Handle password verification modal submit
  document.getElementById('verifyPasswordBtn').addEventListener('click', async function() {
    const passwordInput = document.getElementById('password-input');
    const password = passwordInput.value.trim();
    const form = document.getElementById('newUpdateForm');
    const verifyBtn = this;

    if (!password) {
      Swal.fire({
        title: 'Password Required',
        text: 'Please enter your password to continue.',
        icon: 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#ffc107'
      }).then(() => {
        passwordInput.focus();
      });
      return;
    }

    if (password.length < 6) {
      Swal.fire({
        title: 'Password Too Short',
        text: 'Password must be at least 6 characters long.',
        icon: 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#ffc107'
      }).then(() => {
        passwordInput.focus();
      });
      return;
    }

    // Disable button and show loading state
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';

    try {
      // Verify password via AJAX
      const response = await fetch('/employee/verify-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          password: password
        })
      });

      const result = await response.json();

      if (result.success) {
        // Password is correct, proceed with form submission
        const hiddenPasswordInput = document.createElement('input');
        hiddenPasswordInput.type = 'hidden';
        hiddenPasswordInput.name = 'password_verification';
        hiddenPasswordInput.value = password;
        form.appendChild(hiddenPasswordInput);

        // Hide modal and submit form
        const passwordModal = bootstrap.Modal.getInstance(document.getElementById('passwordVerificationModal'));
        passwordModal.hide();

        // Submit the form
        form.submit();
      } else {
        // Password is incorrect - show SweetAlert error
        Swal.fire({
          title: 'Incorrect Password',
          text: 'The password you entered is incorrect. Please try again.',
          icon: 'error',
          confirmButtonText: 'Try Again',
          confirmButtonColor: '#dc3545'
        }).then(() => {
          passwordInput.focus();
          passwordInput.select();
        });
      }
    } catch (error) {
      console.error('Password verification error:', error);
      Swal.fire({
        title: 'Verification Error',
        text: 'An error occurred while verifying your password. Please try again.',
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
      }).then(() => {
        passwordInput.focus();
      });
    } finally {
      // Re-enable button
      verifyBtn.disabled = false;
      verifyBtn.innerHTML = 'Verify & Submit';
    }
  });
</script>

</body>
</html>
