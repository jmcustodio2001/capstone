<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels - Employee Portal</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">


</head>
<body style="background-color: #f8f9fa !important;">

  <!-- Employee Topbar -->
  @include('employee_ess_modules.partials.employee_topbar')

  <!-- Employee Sidebar -->
  @include('employee_ess_modules.partials.employee_sidebar')

  <!-- Overlay for mobile -->
  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <!-- Main Content -->
  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Employee Portal</h2>
            <p class="text-muted mb-0">
              @php
                $hour = (int)date('H');
                if ($hour >= 5 && $hour < 12) {
                  $greeting = 'Good morning';
                } elseif ($hour >= 12 && $hour < 6) {
                  $greeting = 'Good afternoon';
                } else {
                  $greeting = 'Good evening';
                }
              @endphp
              <h1>
    {{ $greeting }}, {{ trim(Auth::guard('employee')->user()->first_name . ' ' . Auth::guard('employee')->user()->last_name) ?: 'Employee' }}!
</h1>
            </p>
          </div>
        </div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Employee Dashboard</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
 <!-- Notifications Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="bi bi-bell me-2"></i>Training Notifications</h5>
      </div>
      <div class="card-body">
        @if($notifications->count() > 0)
          <div class="list-group list-group-flush">
            @foreach($notifications as $notification)
              <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom py-3">
                <div>
                  <p class="mb-0">{{ $notification->message }}</p>
                  <small class="text-muted">{{ \Carbon\Carbon::parse($notification->sent_at)->diffForHumans() }}</small>
                </div>
                <span class="badge bg-primary rounded-pill">New</span>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-muted mb-0">No new notifications</p>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
            <i class="bi bi-calendar-event"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0">{{ $pendingLeaveRequests }}</h3>
            <p class="text-muted mb-0 small">Pending Leave Requests</p>
            <small class="text-success">+{{ max(0, $pendingLeaveRequests - 9) }} from last week</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0">{{ $attendanceRate }}%</h3>
            <p class="text-muted mb-0 small">Attendance This Month</p>
            <small class="text-success">{{ $attendanceRate >= 95 ? 'Excellent' : ($attendanceRate >= 85 ? 'Good' : 'Needs Improvement') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
            <i class="bi bi-receipt"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0">₱{{ number_format($latestPayslip) }}</h3>
            <p class="text-muted mb-0 small">Latest Payslip</p>
            <small class="text-muted">{{ $payslipMonth }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
            <i class="bi bi-journal-text"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0">{{ $upcomingTrainings }}</h3>
            <p class="text-muted mb-0 small">Upcoming Trainings</p>
            <small class="text-primary">{{ $upcomingTrainings > 0 ? 'Starts next week' : 'All up to date' }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Upcoming Trainings Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Upcoming Trainings</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Training ID</th>
                <th>Training Title</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Source</th>
                <th>Assigned By</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($upcomingTrainingsList as $training)
                <tr>
                  <td>{{ $training['upcoming_id'] ?? $training['id'] ?? 'N/A' }}</td>
                  <td>{{ $training['training_title'] ?? $training['title'] ?? 'Unknown Training' }}</td>
                  <td>{{ $training['start_date'] ?? 'TBD' }}</td>
                  <td>{{ $training['end_date'] ?? 'TBD' }}</td>
                  <td>
                    <span class="badge {{ $training['status_class'] ?? 'bg-secondary' }}">{{ $training['status'] ?? 'Assigned' }}</span>
                  </td>
                  <td>{{ $training['source'] ?? 'System' }}</td>
                  <td>{{ $training['assigned_by'] ?? 'System' }}</td>
                  <td>
                    @if($training['source'] === 'Employee Training Dashboard')
                      <a href="{{ route('employee.my_trainings.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-play-circle"></i> Start Training
                      </a>
                    @else
                      <span class="text-muted small">{{ $training['delivery_mode'] ?? 'Scheduled' }}</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">
                    No upcoming trainings
                    <!-- Debug info -->
                    <br><small class="text-info">Debug: Employee ID {{ Auth::guard('employee')->user()->employee_id ?? 'Unknown' }}</small>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Recent Requests</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Type</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th>Training Title</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentRequests as $request)
              <tr>
                <td>{{ $request['type'] }}</td>
                <td>{{ $request['date'] }}</td>
                <td>
                  @php
                    $badgeClass = match($request['status']) {
                      'Approved', 'Confirmed' => 'bg-success',
                      'Pending' => 'bg-warning',
                      'Processing' => 'bg-info',
                      'Rejected' => 'bg-danger',
                      default => 'bg-secondary'
                    };
                  @endphp
                  <span class="badge {{ $badgeClass }}">{{ $request['status'] }}</span>
                </td>
                <td>{{ $request['remarks'] }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No recent requests found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Quick Actions</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaveApplicationModal">
            <i class="bi bi-calendar-plus me-2"></i> Apply for Leave
          </button>
          <button class="btn btn-outline-primary" onclick="logAttendance()">
            <i class="bi bi-clock me-2"></i> Log Attendance
          </button>
          <button class="btn btn-outline-primary" onclick="viewPayslip()">
            <i class="bi bi-receipt me-2"></i> View Payslip
          </button>
          <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#profileUpdateModal">
            <i class="bi bi-person-circle me-2"></i> Update Profile
          </button>
          <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#requestFormModal">
            <i class="bi bi-file-earmark-text me-2"></i> Submit Request Form
          </button>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">My Progress</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small">Competency Goals</span>
            <span class="small text-muted">{{ $competencyGoalsAchieved }} / {{ $totalCompetencies }}</span>
          </div>
          <div class="progress" style="height: 8px;">
            <div class="progress-bar" style="width: {{ $competencyProgress }}%"></div>
          </div>
          <small class="text-muted">{{ $competencyProgress }}% achieved</small>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small">Trainings Completed</span>
            <span class="small text-muted">{{ $completedTrainings }} / {{ $totalTrainings }}</span>
          </div>
          <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: {{ $trainingCompletionRate }}%"></div>
          </div>
          <small class="text-muted">{{ $trainingCompletionRate }}% completed</small>
        </div>
        <div>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small">Attendance Rate</span>
            <span class="small text-muted">{{ $attendanceRate }}%</span>
          </div>
          <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-warning" style="width: {{ $attendanceRate }}%"></div>
          </div>
          <small class="text-muted">{{ $attendanceRate >= 95 ? 'Excellent' : ($attendanceRate >= 85 ? 'Good' : 'Needs Improvement') }}</small>
        </div>
      </div>
    </div>
  </div>
</div>

  </main>

  <!-- Leave Application Modal -->
  <div class="modal fade" id="leaveApplicationModal" tabindex="-1" aria-labelledby="leaveApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="leaveApplicationModalLabel">Apply for Leave</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="leaveApplicationForm">
          <div class="modal-body">
            <div class="mb-3">
              <label for="leaveType" class="form-label">Leave Type</label>
              <select class="form-select" id="leaveType" name="leave_type" required>
                <option value="">Select leave type</option>
                <option value="Vacation Leave">Vacation Leave</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Emergency Leave">Emergency Leave</option>
                <option value="Maternity/Paternity Leave">Maternity/Paternity Leave</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="startDate" class="form-label">Start Date</label>
                  <input type="date" class="form-control" id="startDate" name="start_date" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="endDate" class="form-label">End Date</label>
                  <input type="date" class="form-control" id="endDate" name="end_date" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="leaveReason" class="form-label">Reason</label>
              <textarea class="form-control" id="leaveReason" name="reason" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Application</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Profile Update Modal -->
  <div class="modal fade" id="profileUpdateModal" tabindex="-1" aria-labelledby="profileUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="profileUpdateModalLabel">Update My Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="profileUpdateForm" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 text-center">
                <div class="mb-3">
                  @php
                    $firstName = $employee->first_name ?? 'Unknown';
                    $lastName = $employee->last_name ?? 'Employee';
                    
                    // Profile picture logic - consistent with other HR modules
                    $profilePicUrl = '';
                    if ($employee->profile_picture) {
                      $profilePicUrl = asset('storage/' . $employee->profile_picture);
                    } else {
                      // Fallback to UI Avatars with consistent color scheme
                      $employeeId = $employee->employee_id ?? 'EMP';
                      $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                      $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                      $colorIndex = crc32($employeeId) % count($colors);
                      $bgColor = $colors[$colorIndex];
                      $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background={$bgColor}&color=ffffff&size=128&bold=true";
                    }
                  @endphp
                  
                  <img id="profilePreview" 
                       src="{{ $profilePicUrl }}" 
                       class="rounded-circle mb-3" 
                       width="120" 
                       height="120" 
                       style="object-fit: cover; border: 3px solid #e9ecef;"
                       alt="Profile Picture">
                  <div>
                    <label for="profilePicture" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-camera me-1"></i> Change Photo
                    </label>
                    <input type="file" id="profilePicture" name="profile_picture" class="d-none" accept="image/*">
                  </div>
                  <small class="text-muted d-block mt-2">JPG, PNG, GIF up to 2MB</small>
                </div>
              </div>
              <div class="col-md-8">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="firstName" class="form-label">First Name*</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" value="{{ $employee->first_name }}" required>
                  </div>
                  <div class="col-md-6">
                    <label for="lastName" class="form-label">Last Name*</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" value="{{ $employee->last_name }}" required>
                  </div>
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $employee->email }}" required>
                  </div>
                  <div class="col-md-6">
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phoneNumber" name="phone_number" value="{{ $employee->phone_number }}">
                  </div>
                  <div class="col-12">
                    <div class="alert alert-info">
                      <i class="bi bi-info-circle me-2"></i>
                      <strong>Employee ID:</strong> {{ $employee->employee_id }} (cannot be changed)
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Update Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Request Form Modal -->
  <div class="modal fade" id="requestFormModal" tabindex="-1" aria-labelledby="requestFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="requestFormModalLabel">Submit Request Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="requestForm">
          <div class="modal-body">
            <div class="mb-3">
              <label for="requestType" class="form-label">Request Type</label>
              <select class="form-select" id="requestType" name="request_type" required>
                <option value="">Select request type</option>
                <option value="Equipment Request">Equipment Request</option>
                <option value="Attendance Adjustment">Attendance Adjustment</option>
                <option value="Certificate Request">Certificate Request</option>
                <option value="Training Request">Training Request</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="requestReason" class="form-label">Details/Reason</label>
              <textarea class="form-control" id="requestReason" name="reason" rows="4" required></textarea>
            </div>
            <div class="mb-3">
              <label for="requestDate" class="form-label">Requested Date (if applicable)</label>
              <input type="date" class="form-control" id="requestDate" name="requested_date">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="agent-portal-script.js"></script>
  
  <script>
    // CSRF Token Management to prevent 419 Page Expired errors
    document.addEventListener('DOMContentLoaded', function() {
      // Set up CSRF token for AJAX requests
      const token = document.querySelector('meta[name="csrf-token"]');
      if (token) {
        window.Laravel = {
          csrfToken: token.getAttribute('content')
        };
        
        // Set default AJAX headers
        if (typeof $ !== 'undefined') {
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': token.getAttribute('content')
            }
          });
        }
      }
      
      // Refresh CSRF token every 30 minutes to prevent expiration
      setInterval(function() {
        fetch('/csrf-token', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.csrf_token) {
            // Update meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
              metaTag.setAttribute('content', data.csrf_token);
            }
            
            // Update Laravel object
            if (window.Laravel) {
              window.Laravel.csrfToken = data.csrf_token;
            }
            
            // Update jQuery AJAX setup if available
            if (typeof $ !== 'undefined') {
              $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': data.csrf_token
                }
              });
            }
            
            console.log('CSRF token refreshed successfully');
          }
        })
        .catch(error => {
          console.warn('Failed to refresh CSRF token:', error);
        });
      }, 30 * 60 * 1000); // 30 minutes
    });

    // Quick Actions Functions
    function logAttendance() {
      const now = new Date();
      const timeString = now.toLocaleTimeString();
      
      if (confirm(`Log attendance at ${timeString}?`)) {
        // This would integrate with your attendance system
        fetch('/employee/attendance/log', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            timestamp: now.toISOString()
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Attendance logged successfully!');
            location.reload();
          } else {
            alert('Error logging attendance: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error logging attendance. Please try again.');
        });
      }
    }

    function viewPayslip() {
      // This would open payslip in new window/tab
      window.open('/employee/payslip/latest', '_blank');
    }

    // Form Submissions
    document.getElementById('leaveApplicationForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      
      fetch('/employee/leave-application', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Leave application submitted successfully!');
          bootstrap.Modal.getInstance(document.getElementById('leaveApplicationModal')).hide();
          this.reset();
          location.reload();
        } else {
          alert('Error submitting application: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error submitting application. Please try again.');
      });
    });

    // Profile picture preview
    document.getElementById('profilePicture').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Profile update form submission
    document.getElementById('profileUpdateForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      fetch('/employee/profile/update', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Profile updated successfully!');
          bootstrap.Modal.getInstance(document.getElementById('profileUpdateModal')).hide();
          location.reload();
        } else {
          alert('Error updating profile: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error updating profile. Please try again.');
      });
    });

    document.getElementById('requestForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      
      fetch('/employee/request-form', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Request submitted successfully!');
          bootstrap.Modal.getInstance(document.getElementById('requestFormModal')).hide();
          this.reset();
          location.reload();
        } else {
          alert('Error submitting request: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error submitting request. Please try again.');
      });
    });
  </script>

  <!-- CSRF Token Refresh System -->
  <script src="{{ asset('js/csrf-refresh.js') }}"></script>

</body>
</html>
