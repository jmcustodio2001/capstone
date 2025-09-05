<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
    window.Laravel = {
      csrfToken: '{{ csrf_token() }}'
    };
  </script>
  <title>Jetlouge Travels Admin</title>
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
            <h2 class="fw-bold mb-1">Destination Knowledge Training</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage destination training records here.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Destination Knowledge Training</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card-body">
      <!-- Notifications -->
      <!-- Toast Notification Container -->
      <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="notificationContainer" class="position-fixed end-0 p-3" style="top: 120px; z-index: 1080;"></div>
      </div>
      @if(session('success'))
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          var toastEl = document.getElementById('successToast');
          if (toastEl) {
            var toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
          }
        });
      </script>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="sessionErrorAlert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
          // Auto-dismiss session error after 3 seconds if it contains duplicate message
          document.addEventListener('DOMContentLoaded', function() {
            const sessionError = document.getElementById('sessionErrorAlert');
            if (sessionError && (sessionError.textContent.includes('already exists') || 
                                sessionError.textContent.includes('duplicate') || 
                                sessionError.textContent.includes('BESTLINK COLLEGE'))) {
              setTimeout(() => {
                sessionError.remove();
              }, 3000);
            }
          });
        </script>
      @endif

      <!-- Employee Response Notifications -->
      @if(isset($notifications) && $notifications->count() > 0)
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Employee Responses</h5>
          </div>
          <div class="card-body">
            @foreach($notifications->take(5) as $notification)
              @php
                $message = $notification->message;
                $isAccepted = stripos($message, 'ACCEPTED') !== false || stripos($message, 'accepted') !== false;
                $isDeclined = stripos($message, 'DECLINED') !== false || stripos($message, 'declined') !== false || stripos($message, 'REJECTED') !== false || stripos($message, 'rejected') !== false;
                
                // Determine styling based on status
                if ($isAccepted) {
                  $alertClass = 'alert-success border-success';
                  $iconClass = 'fas fa-check-circle text-success';
                  $borderColor = 'border-success';
                } elseif ($isDeclined) {
                  $alertClass = 'alert-danger border-danger';
                  $iconClass = 'fas fa-times-circle text-danger';
                  $borderColor = 'border-danger';
                } else {
                  $alertClass = 'alert-light border-info';
                  $iconClass = 'fas fa-info-circle text-info';
                  $borderColor = 'border-info';
                }
              @endphp
              <div class="alert {{ $alertClass }} border-start border-4 {{ $borderColor }} mb-2">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <i class="{{ $iconClass }} me-2"></i>
                    @if($isAccepted)
                      {!! preg_replace('/(ACCEPTED|accepted)/i', '<span class="badge bg-success text-white px-2 py-1 rounded-pill fw-bold">$1</span>', $message) !!}
                    @elseif($isDeclined)
                      {!! preg_replace('/(DECLINED|declined|REJECTED|rejected)/i', '<span class="badge bg-danger text-white px-2 py-1 rounded-pill fw-bold">$1</span>', $message) !!}
                    @else
                      {{ $message }}
                    @endif
                  </div>
                  <small class="text-muted">{{ \Carbon\Carbon::parse($notification->sent_at)->diffForHumans() }}</small>
                </div>
              </div>
            @endforeach
            @if($notifications->count() > 5)
              <div class="text-center">
                <small class="text-muted">Showing 5 of {{ $notifications->count() }} notifications</small>
              </div>
            @endif
          </div>
        </div>
      @endif

      <!-- Possible Training Destinations Table -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #fff3cd;">
          <div class="d-flex align-items-center gap-3">
            <h2 class="fw-bold mb-1">Possible Training Destinations</h2>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPossibleDestinationModal">
              <i class="bi bi-plus-lg me-1"></i> Add New Destination
            </button>
          </div>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Destination</th>
                <th>Details</th>
                <th>Objectives</th>
                <th>Duration</th>
                <th>Delivery Mode</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="possibleDestinationsTableBody">
              @forelse($possibleDestinations as $index => $destination)
                <tr>
                  <td>{{ $destination->id }}</td>
                  <td>{{ $destination->destination_name }}</td>
                  <td>{{ $destination->details }}</td>
                  <td>{{ $destination->objectives }}</td>
                  <td>{{ $destination->duration }}</td>
                  <td>
                    @switch($destination->delivery_mode)
                      @case('On-site Training')
                        <span class="badge" style="background-color: #198754; color: white;">🏢 On-site Training</span>
                        @break
                      @case('Online Training')
                        <span class="badge" style="background-color: #fd7e14; color: white;">💻 Online Training</span>
                        @break
                      @case('Blended Learning')
                        <span class="badge" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</span>
                        @break
                      @case('Workshop')
                        <span class="badge" style="background-color: #6f42c1; color: white;">🎯 Workshop</span>
                        @break
                      @case('Seminar')
                        <span class="badge" style="background-color: #20c997; color: white;">📚 Seminar</span>
                        @break
                      @case('Field Training')
                        <span class="badge" style="background-color: #dc3545; color: white;">🏃 Field Training</span>
                        @break
                      @default
                        <span class="badge bg-secondary">{{ $destination->delivery_mode }}</span>
                    @endswitch
                  </td>
                  <td>
                    <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editPossibleDestinationModal{{ $destination->id }}">
                      <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm delete-possible-btn" 
                            data-destination-id="{{ $destination->id }}" 
                            data-destination-name="{{ $destination->destination_name }}">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">No possible destinations found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Destination Knowledge Training Table -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #e3f0ff;">
          <div class="d-flex align-items-center gap-3">
            <h2 class="fw-bold mb-1">Destination Knowledge Training</h2>
          </div>
          <div class="d-flex gap-2">
            <input type="text" id="searchInput" class="form-control" placeholder="Search employee or destination..." style="max-width: 220px;">
            <select id="progressFilter" class="form-select" style="max-width: 150px;">
              <option value="">All Status</option>
              <option value="completed">Completed</option>
              <option value="in-progress">In Progress</option>
              <option value="not-started">Not Started</option>
            </select>
            <input type="date" id="dateFilter" class="form-control" style="max-width: 170px;">
            <button class="btn btn-primary" id="applyFilters"><i class="bi bi-funnel"></i> Filter</button>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm" id="syncExistingBtn">
              <i class="bi bi-arrow-repeat me-1"></i> Sync Missing Records
            </button>
            <button class="btn btn-outline-warning btn-sm" id="syncStatusBtn">
              <i class="bi bi-arrow-clockwise me-1"></i> Sync Training Status
            </button>
            <button class="btn btn-outline-primary btn-sm" id="exportExcel">
              <i class="bi bi-download me-1"></i> Export Excel
            </button>
            <button class="btn btn-outline-primary btn-sm" id="exportPdf">
              <i class="bi bi-download me-1"></i> Export PDF
            </button>
            <button class="btn btn-primary btn-sm" id="addNewDestinationBtn">
              <i class="bi bi-plus-lg me-1"></i> Add New
            </button>
          </div>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Destination</th>
                <th>Details</th>
                <th>Delivery Mode</th>
                <th>Date Created</th>
                <th>Expired Date</th>
                <th>Status</th>
                <th>Course Status</th>
                <th>Actions</th>
                <th>Request</th>
                <th>Upcoming Training</th>
              </tr>
            </thead>
            <tbody id="destinationTableBody">
              @forelse($destinations as $record)
                <tr data-destination-id="{{ $record->id }}">
                  <td>{{ $record->id }}</td>
                  <td>
                    @if($record->employee)
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                          @php
                            $firstName = $record->employee->first_name ?? 'Unknown';
                            $lastName = $record->employee->last_name ?? 'Employee';
                            $fullName = $firstName . ' ' . $lastName;
                            
                            // Check if profile picture exists - simplified approach
                            $profilePicUrl = null;
                            if ($record->employee->profile_picture) {
                                // Direct asset URL generation - Laravel handles the storage symlink
                                $profilePicUrl = asset('storage/' . $record->employee->profile_picture);
                            }
                            
                            // Generate consistent color based on employee name for fallback
                            $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                            $employeeId = $record->employee->employee_id ?? 'default';
                            $colorIndex = abs(crc32($employeeId)) % count($colors);
                            $bgColor = $colors[$colorIndex];
                            
                            // Fallback to UI Avatars if no profile picture found
                            if (!$profilePicUrl) {
                                $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . 
                                               "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
                            }
                          @endphp
                          
                          <img src="{{ $profilePicUrl }}" 
                               alt="{{ $firstName }} {{ $lastName }}" 
                               class="rounded-circle" 
                               style="width: 40px; height: 40px; object-fit: cover;">
                        </div>
                        <span class="fw-semibold">
                          {{ $firstName }} {{ $lastName }}
                        </span>
                      </div>
                    @else
                      Unknown
                    @endif
                  </td>
                  <td>{{ $record->destination_name }}</td>
                  <td>{{ $record->details }}</td>
                  <td>
                    @if($record->delivery_mode)
                      @switch($record->delivery_mode)
                        @case('On-site Training')
                          <span class="badge" style="background-color: #198754; color: white;">🏢 On-site Training</span>
                          @break
                        @case('Online Training')
                          <span class="badge" style="background-color: #fd7e14; color: white;">💻 Online Training</span>
                          @break
                        @case('Blended Learning')
                          <span class="badge" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</span>
                          @break
                        @case('Workshop')
                          <span class="badge" style="background-color: #6f42c1; color: white;">🎯 Workshop</span>
                          @break
                        @case('Seminar')
                          <span class="badge" style="background-color: #20c997; color: white;">📚 Seminar</span>
                          @break
                        @case('Field Training')
                          <span class="badge" style="background-color: #dc3545; color: white;">🏃 Field Training</span>
                          @break
                        @default
                          <span class="badge bg-secondary">{{ $record->delivery_mode }}</span>
                      @endswitch
                    @else
                      <span class="text-muted">Not Set</span>
                    @endif
                  </td>
                  <td>{{ $record->created_at->format('Y-m-d') }}</td>
                  <td>
                    @php
                      // Prefer linked UpcomingTraining deadline if available; fall back to record's expired_date
                      $deadlineDate = null;
                      try {
                        $linkedUpcoming = \App\Models\UpcomingTraining::where('employee_id', $record->employee_id)
                          ->where('destination_training_id', $record->id)
                          ->first();
                        if ($linkedUpcoming) {
                          $deadlineDate = $linkedUpcoming->deadline ?? $linkedUpcoming->deadline_date ?? null;
                        }
                      } catch (\Exception $e) {
                        $deadlineDate = null;
                      }
                      $baseDate = $deadlineDate ?: $record->expired_date;
                    @endphp

                    @if($baseDate)
                      @php
                        $expiredDate = \Carbon\Carbon::parse($baseDate);
                        $now = \Carbon\Carbon::now();
                        $daysUntilExpiry = $now->diffInDays($expiredDate, false);
                        
                        // Color coding based on days until expiry
                        if ($daysUntilExpiry < 0) {
                          $colorClass = 'text-danger fw-bold';
                          $bgClass = 'bg-danger text-white';
                          $status = 'EXPIRED';
                        } elseif ($daysUntilExpiry <= 7) {
                          $colorClass = 'text-warning fw-bold';
                          $bgClass = 'bg-warning text-dark';
                          $status = 'URGENT';
                        } elseif ($daysUntilExpiry <= 30) {
                          $colorClass = 'text-info fw-bold';
                          $bgClass = 'bg-info text-white';
                          $status = 'SOON';
                        } else {
                          $colorClass = 'text-success fw-bold';
                          $bgClass = 'bg-success text-white';
                          $status = 'ACTIVE';
                        }
                      @endphp
                      <div class="d-flex flex-column align-items-center">
                        <span class="{{ $colorClass }}">{{ $expiredDate->format('Y-m-d') }}</span>
                        <small class="badge {{ $bgClass }} mt-1">{{ $status }}</small>
                        @if($daysUntilExpiry > 0)
                          <small class="text-muted">{{ floor($daysUntilExpiry) }} days left</small>
                        @elseif($daysUntilExpiry < 0)
                          @php $overdueDays = floor(abs($daysUntilExpiry)); @endphp
                          @if($overdueDays > 0)
                            <small class="text-danger">{{ $overdueDays }} days overdue</small>
                          @endif
                        @endif
                        @if($deadlineDate)
                          <small class="text-muted">Based on Upcoming Training</small>
                        @endif
                      </div>
                    @else
                      <span class="badge bg-secondary">Not Set</span>
                    @endif
                  </td>
                  <td>
                    @php
                      // Determine accurate status based on progress, expiry, and current status
                      $currentStatus = $record->status ?? 'not-started';
                      $currentProgress = $syncedProgress ?? $record->progress ?? 0;
                      
                      // Check if expired
                      $isExpired = false;
                      if ($record->expired_date) {
                        $expiredDate = \Carbon\Carbon::parse($record->expired_date);
                        $isExpired = \Carbon\Carbon::now()->gt($expiredDate);
                      }
                      
                      // Determine final status
                      if ($isExpired && $currentProgress < 100) {
                        $finalStatus = 'expired';
                        $badgeClass = 'bg-danger';
                        $textClass = 'text-danger';
                        $displayText = 'Expired';
                      } elseif ($currentProgress >= 100) {
                        $finalStatus = 'completed';
                        $badgeClass = 'bg-success';
                        $textClass = 'text-success';
                        $displayText = 'Completed';
                      } elseif ($currentProgress > 0) {
                        $finalStatus = 'in-progress';
                        $badgeClass = 'bg-primary';
                        $textClass = 'text-primary';
                        $displayText = 'In Progress';
                      } else {
                        $finalStatus = 'not-started';
                        $badgeClass = 'bg-secondary';
                        $textClass = 'text-secondary';
                        $displayText = 'Not Started';
                      }
                    @endphp
                    <span class="badge {{ $badgeClass }} bg-opacity-10 {{ $textClass }} fs-6 status-badge">{{ $displayText }}</span>
                  </td>
                  <td>
                    @php
                      // Check if there's a course in course_management for this destination
                      $courseStatus = 'Not Requested';
                      try {
                        $course = \App\Models\CourseManagement::where('course_title', $record->destination_name)->first();
                        if ($course) {
                          $courseStatus = $course->status;
                        }
                      } catch (\Exception $e) {
                        $courseStatus = 'Not Requested';
                      }
                    @endphp
                    
                    @if($courseStatus === 'Active')
                      <span class="badge bg-success bg-opacity-10 text-success fs-6">Active</span>
                    @elseif($courseStatus === 'Pending Approval')
                      <span class="badge bg-warning bg-opacity-10 text-warning fs-6">Pending Approval</span>
                    @elseif($courseStatus === 'Rejected')
                      <span class="badge bg-danger bg-opacity-10 text-danger fs-6">Rejected</span>
                    @else
                      <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6">Not Requested</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editDestinationModal{{ $record->id }}">
                        <i class="bi bi-pencil"></i> Edit
                      </button>
                      <form action="{{ route('admin.destination-knowledge-training.destroy', $record->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                          <i class="bi bi-trash"></i> Delete
                        </button>
                      </form>
                    </div>
                  </td>
                  <td>
                    @php
                      // Check if course is already assigned in employee training dashboard OR competency gap analysis
                      $destinationNameClean = str_replace([' Training', 'Training'], '', $record->destination_name);
                      
                      // Check Employee Training Dashboard - ONLY for this specific employee
                      try {
                        $isAssignedInTraining = \App\Models\EmployeeTrainingDashboard::where('employee_training_dashboards.employee_id', $record->employee_id)
                          ->join('course_management', 'employee_training_dashboards.course_id', '=', 'course_management.course_id')
                          ->where('course_management.course_title', 'LIKE', '%' . $destinationNameClean . '%')
                          ->exists();
                      } catch (\Exception $e) {
                        // Handle missing table gracefully
                        $isAssignedInTraining = false;
                      }
                      
                      // Check if this training was actually assigned from Competency Gap Analysis
                      // Only mark as "From Competency Gap" if the training details explicitly mention it was from competency gap
                      $isAssignedFromGap = false;
                      if ($record->details && (strpos($record->details, 'Training assigned from competency gap analysis') !== false || 
                                               strpos($record->details, 'from competency gap') !== false)) {
                        $isAssignedFromGap = true;
                      }
                      
                      // Check if this training is already in upcoming trainings for this employee
                      $isInUpcomingTraining = false;
                      try {
                        $isInUpcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $record->employee_id)
                          ->where('destination_training_id', $record->id)
                          ->exists();
                      } catch (\Exception $e) {
                        $isInUpcomingTraining = false;
                      }
                      
                      // Only disable if THIS SPECIFIC employee already has the course assigned or in upcoming training
                      $isAlreadyAssigned = $isAssignedInTraining || $isAssignedFromGap || $isInUpcomingTraining;
                      
                      // Determine button text and tooltip based on delivery mode
                      if ($record->delivery_mode === 'Online Training') {
                        $buttonText = 'Request Activation';
                        $tooltipText = $isAssignedFromGap ? 'Assigned from Competency Gap' : ($isAssignedInTraining ? 'Already in Training Dashboard' : 'Request activation for this online training');
                      } else {
                        $buttonText = 'Request Training';
                        $tooltipText = $isInUpcomingTraining ? 'Already in Upcoming Training' : ($isAssignedFromGap ? 'Assigned from Competency Gap' : ($isAssignedInTraining ? 'Already in Training Dashboard' : 'Request this training'));
                      }
                    @endphp
                    
                    <button class="btn btn-outline-primary btn-sm request-activation-btn {{ $isAlreadyAssigned ? 'disabled' : '' }}" 
                      data-record-id="{{ $record->id }}" 
                      data-destination-name="{{ $record->destination_name }}"
                      data-delivery-mode="{{ $record->delivery_mode }}"
                      data-already-assigned="{{ $isAlreadyAssigned ? 'true' : 'false' }}"
                      {{ $isAlreadyAssigned ? 'disabled' : '' }}
                      title="{{ $tooltipText }}">
                      <i class="bi bi-clipboard-check"></i> 
                      @if($isInUpcomingTraining)
                        Already in Upcoming
                      @elseif($isAssignedFromGap)
                        From Competency Gap
                      @elseif($isAssignedInTraining)
                        Already Assigned
                      @else
                        {{ $buttonText }}
                      @endif
                    </button>
                  </td>
                  <td>
                    @if($record->delivery_mode !== 'Online Training')
                      @php
                        // Check if employee has already accepted this specific training
                        $upcomingTraining = null;
                        $hasAccepted = false;
                        
                        try {
                          $upcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $record->employee_id)
                            ->where('destination_training_id', $record->id)
                            ->first();
                          
                          // Check if employee has accepted using multiple possible fields (case-insensitive, substring match)
                          if ($upcomingTraining) {
                            $candidates = [
                              strtolower(trim((string)($upcomingTraining->status ?? ''))),
                              strtolower(trim((string)($upcomingTraining->employee_response ?? ''))),
                              strtolower(trim((string)($upcomingTraining->response_status ?? '')))
                            ];
                            foreach ($candidates as $candidate) {
                              if ($candidate !== '' && (strpos($candidate, 'accepted') !== false || strpos($candidate, 'completed') !== false)) {
                                $hasAccepted = true;
                                break;
                              }
                            }
                            if (!$hasAccepted && isset($upcomingTraining->is_accepted)) {
                              $hasAccepted = (bool)$upcomingTraining->is_accepted;
                            }
                          }
                        } catch (\Exception $e) {
                          // Handle gracefully if table doesn't exist
                          $hasAccepted = false;
                        }
                      @endphp
                      
                      @if($hasAccepted)
                        <span class="badge bg-success text-white">
                          <i class="bi bi-check-circle"></i> Completed (Accepted by Employee)
                        </span>
                      @else
                        @if($record->admin_approved_for_upcoming)
                          <span class="badge bg-success text-white">
                            <i class="bi bi-check-circle"></i> Approved for Upcoming
                          </span>
                        @else
                          @php
                            // Check if request has been activated/approved
                            $isRequestActivated = false;
                            
                            // Check if there's an approved course in course_management for this destination
                            try {
                              $approvedCourse = \App\Models\CourseManagement::where('course_title', $record->destination_name)
                                ->where('status', 'Active')
                                ->first();
                              
                              if ($approvedCourse) {
                                $isRequestActivated = true;
                              }
                            } catch (\Exception $e) {
                              // Handle gracefully if table doesn't exist
                              $isRequestActivated = false;
                            }
                            
                            // Also check if status indicates activation was requested and processed
                            if ($record->status === 'in-progress' || $record->status === 'completed') {
                              $isRequestActivated = true;
                            }
                          @endphp
                          
                          @if($isRequestActivated)
                            <button class="btn btn-info btn-sm assign-to-upcoming-btn" 
                                    data-destination-id="{{ $record->id }}"
                                    data-employee-name="{{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }}"
                                    data-destination-name="{{ $record->destination_name }}"
                                    data-already-assigned="{{ $record->admin_approved_for_upcoming ? 'true' : 'false' }}"
                                    title="Assign {{ $record->destination_name }} to {{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }}'s upcoming training list">
                              <i class="bi bi-calendar-check"></i> Assign to Upcoming Training
                            </button>
                          @else
                            <button class="btn btn-secondary btn-sm" 
                                    disabled
                                    title="Please activate the request first before assigning to upcoming training">
                              <i class="bi bi-calendar-x"></i> Request Not Activated
                            </button>
                          @endif
                        @endif
                      @endif
                    @else
                      <span class="text-muted small">Only for another delivery mode</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center text-muted">No records found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add Destination Modal -->
      <div class="modal fade" id="addDestinationModal" tabindex="-1" aria-labelledby="addDestinationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 600px;">
          <div class="modal-content">
            <div class="card-header modal-header">
              <h5 class="modal-title" id="addDestinationModalLabel">Add Destination Knowledge Record</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="addDestinationForm" action="{{ route('admin.destination-knowledge-training.store') }}" method="POST">
                @csrf
                <div id="formErrors" class="alert alert-danger d-none" role="alert"></div>
                
                @if($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
                <div class="mb-3">
                  <label class="form-label" for="employee_id">Employee*</label>
                  <select class="form-select" name="employee_id" id="employee_id" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                      <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="destination_name">Destination Name*</label>
                  <select class="form-select" name="destination_name" id="destination_name" required>
                    <option value="">Select Destination</option>
                    @foreach($destinationMasters as $destination)
                      <option value="{{ $destination->destination_name }}">{{ $destination->destination_name }}</option>
                    @endforeach
                    <option value="custom">Custom Destination (Type your own)</option>
                  </select>
                  <input type="text" class="form-control mt-2 d-none" name="custom_destination_name" id="custom_destination_name" placeholder="Enter custom destination name">
                  <div id="duplicateWarning" class="alert alert-warning mt-2 d-none">
                    <i class="bi bi-exclamation-triangle"></i> This employee already has a training record for this destination.
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="details">Details*</label>
                    <textarea class="form-control" name="details" id="details" rows="2" required></textarea>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="objectives">Objectives*</label>
                    <textarea class="form-control" name="objectives" id="objectives" rows="2" required></textarea>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="duration">Duration*</label>
                    <input type="text" class="form-control" name="duration" id="duration" placeholder="e.g., 5 days, 2 weeks" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="delivery_mode">Delivery Mode*</label>
                    <select class="form-select" name="delivery_mode" id="delivery_mode" required>
                      <option value="">Select Delivery Mode</option>
                      <option value="On-site Training" style="background-color: #198754; color: white;">🏢 On-site Training</option>
                      <option value="Online Training" style="background-color: #fd7e14; color: white;">💻 Online Training</option>
                      <option value="Blended Learning" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                      <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                      <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                      <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="expired_date">Expired Date</label>
                    <input type="date" class="form-control" name="expired_date" id="expired_date">
                  </div>
                </div>
                <div class="row" id="onlineTrainingFields" style="display: none;">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="progress_level">Progress Level</label>
                    <select class="form-select" name="progress_level" id="progress_level">
                      <option value="0">0 - Not Started (0%)</option>
                      <option value="1">1 - Beginner (20%)</option>
                      <option value="2">2 - Developing (40%)</option>
                      <option value="3">3 - Proficient (60%)</option>
                      <option value="4">4 - Advanced (80%)</option>
                      <option value="5">5 - Expert (100%)</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" name="status" id="status">
                      <option value="not-started" selected>Not Started</option>
                      <option value="in-progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                    <small class="text-info">
                      <i class="bi bi-info-circle"></i> Status will be automatically set: 0% = Not Started, 1-99% = In Progress, 100% = Completed
                    </small>
                  </div>
                </div>
                <!-- Removed Active field -->
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" id="saveDestinationBtn">Save Record</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Destination Modal -->
      @foreach($destinations as $record)
      <div class="modal fade" id="editDestinationModal{{ $record->id }}" tabindex="-1" aria-labelledby="editDestinationModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 500px;">
          <div class="modal-content">
            <div class="card-header modal-header py-2">
              <h6 class="modal-title" id="editDestinationModalLabel{{ $record->id }}">Edit Destination Knowledge Record</h6>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
              <form action="{{ route('admin.destination-knowledge-training.update', $record->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="employee_id">Employee*</label>
                    <select class="form-select form-select-sm" name="employee_id" id="employee_id" required>
                      @foreach($employees as $employee)
                        <option value="{{ $employee->employee_id }}" {{ $record->employee_id == $employee->employee_id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="destination_name">Destination Name*</label>
                    <input type="text" class="form-control form-control-sm" name="destination_name" id="destination_name" value="{{ $record->destination_name }}" required>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="details">Details*</label>
                    <textarea class="form-control form-control-sm" name="details" id="details" rows="2" required>{{ $record->details }}</textarea>
                  </div>
                </div>

                <div class="row">
                  <div class="col-6 mb-2">
                    <label class="form-label small" for="progress">Progress Level</label>
                    <select class="form-select form-select-sm" name="progress_level" id="progress_level" required {{ $record->delivery_mode !== 'Online Training' ? 'disabled' : '' }}>
                      @php
                        // Convert current progress percentage to level (0-5)
                        $currentLevel = 0;
                        if ($record->progress >= 80) $currentLevel = 5;
                        elseif ($record->progress >= 60) $currentLevel = 4;
                        elseif ($record->progress >= 40) $currentLevel = 3;
                        elseif ($record->progress >= 20) $currentLevel = 2;
                        elseif ($record->progress > 0) $currentLevel = 1;
                        else $currentLevel = 0;
                      @endphp
                      <option value="0" {{ $currentLevel == 0 ? 'selected' : '' }}>0 - Not Started (0%)</option>
                      <option value="1" {{ $currentLevel == 1 ? 'selected' : '' }}>1 - Beginner (20%)</option>
                      <option value="2" {{ $currentLevel == 2 ? 'selected' : '' }}>2 - Developing (40%)</option>
                      <option value="3" {{ $currentLevel == 3 ? 'selected' : '' }}>3 - Proficient (60%)</option>
                      <option value="4" {{ $currentLevel == 4 ? 'selected' : '' }}>4 - Advanced (80%)</option>
                      <option value="5" {{ $currentLevel == 5 ? 'selected' : '' }}>5 - Expert (100%)</option>
                    </select>
                  </div>
                  <div class="col-6 mb-2">
                    <label class="form-label small" for="status">Status <small class="text-muted">(Auto-calculated)</small></label>
                    <select class="form-select form-select-sm" name="status" id="status" required disabled>
                      <option value="not-started" {{ ($record->status ?? 'not-started') == 'not-started' ? 'selected' : '' }}>Not Started</option>
                      <option value="in-progress" {{ ($record->status ?? 'in-progress') == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                      <option value="completed" {{ ($record->status ?? 'completed') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    <small class="text-info d-block">
                      <i class="bi bi-info-circle"></i> Auto-set based on progress level
                    </small>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="expired_date">Expired Date</label>
                    <input type="date" class="form-control form-control-sm" name="expired_date" id="expired_date" value="{{ $record->expired_date ? $record->expired_date->format('Y-m-d') : '' }}">
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="remarks">Remarks</label>
                    <textarea class="form-control form-control-sm" name="remarks" id="remarks" rows="1" placeholder="Optional remarks...">{{ $record->remarks ?? '' }}</textarea>
                  </div>
                </div>

                <div class="modal-footer py-2 px-0">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary btn-sm">Update Record</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      @endforeach

      <!-- Request Course Activation Modal -->
      @foreach($destinations as $record)
      <div class="modal fade" id="requestCourseActivationModal{{ $record->id }}" tabindex="-1" aria-labelledby="requestCourseActivationModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="requestCourseActivationModalLabel{{ $record->id }}">Request Course Activation</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="{{ route('admin.course_management.assign_destination', ['employeeId' => $record->employee_id]) }}" method="POST">
                @csrf
                <div class="mb-3">
                  <label class="form-label" for="destinationName">Destination Name*</label>
                  <input type="text" class="form-control" name="destinationName" id="destinationName" value="{{ $record->destination_name }}" readonly required>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="request_message">Message*</label>
                  <textarea class="form-control" name="request_message" id="request_message" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Send Request</button>
                </div>
              </form>
              @if(session('course_management_redirect'))
              <script>
                window.location.href = "{{ session('course_management_redirect') }}";
              </script>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach

      <!-- Add Possible Destination Modal -->
      <div class="modal fade" id="addPossibleDestinationModal" tabindex="-1" aria-labelledby="addPossibleDestinationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
              <h5 class="modal-title" id="addPossibleDestinationModalLabel">Add Possible Training Destination</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="addPossibleDestinationForm" action="{{ route('admin.destination-knowledge-training.store-possible') }}" method="POST">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="possible_destination">Destination*</label>
                      <input type="text" class="form-control" name="destination" id="possible_destination" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="possible_duration">Duration*</label>
                      <input type="text" class="form-control" name="duration" id="possible_duration" placeholder="e.g., 5 days, 2 weeks" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="possible_details">Details*</label>
                  <textarea class="form-control" name="details" id="possible_details" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="possible_objectives">Objectives*</label>
                  <textarea class="form-control" name="objectives" id="possible_objectives" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="possible_delivery_mode">Delivery Mode*</label>
                  <select class="form-select" name="delivery_mode" id="possible_delivery_mode" required>
                    <option value="">Select Delivery Mode</option>
                    <option value="On-site Training" style="background-color: #198754; color: white;">🏢 On-site Training</option>
                    <option value="Online Training" style="background-color: #fd7e14; color: white;">💻 Online Training</option>
                    <option value="Blended Learning" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                    <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                    <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                    <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" id="savePossibleDestinationBtn">Save Destination</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Possible Destination Modals -->
      <div class="modal fade" id="editPossibleDestinationModal1" tabindex="-1" aria-labelledby="editPossibleDestinationModalLabel1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
              <h5 class="modal-title" id="editPossibleDestinationModalLabel1">Edit Possible Training Destination</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="editPossibleDestinationForm1">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_destination1">Destination*</label>
                      <input type="text" class="form-control" name="destination" id="edit_possible_destination1" value="Baesa Quezon City" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_duration1">Duration*</label>
                      <input type="text" class="form-control" name="duration" id="edit_possible_duration1" value="5 days" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_details1">Details*</label>
                  <textarea class="form-control" name="details" id="edit_possible_details1" rows="3" required>Comprehensive training on Baesa operations and customer service protocols</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_objectives1">Objectives*</label>
                  <textarea class="form-control" name="objectives" id="edit_possible_objectives1" rows="3" required>Master destination-specific procedures, customer handling, and operational excellence</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_delivery_mode1">Delivery Mode*</label>
                  <select class="form-select" name="delivery_mode" id="edit_possible_delivery_mode1" required>
                    <option value="On-site Training" selected style="background-color: #198754; color: white;">🏢 On-site Training</option>
                    <option value="Online Training" style="background-color: #fd7e14; color: white;">💻 Online Training</option>
                    <option value="Blended Learning" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                    <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                    <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                    <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Destination</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="editPossibleDestinationModal2" tabindex="-1" aria-labelledby="editPossibleDestinationModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
              <h5 class="modal-title" id="editPossibleDestinationModalLabel2">Edit Possible Training Destination</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="editPossibleDestinationForm2">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_destination2">Destination*</label>
                      <input type="text" class="form-control" name="destination" id="edit_possible_destination2" value="Cubao Terminal" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_duration2">Duration*</label>
                      <input type="text" class="form-control" name="duration" id="edit_possible_duration2" value="3 days" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_details2">Details*</label>
                  <textarea class="form-control" name="details" id="edit_possible_details2" rows="3" required>Terminal operations and passenger management training</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_objectives2">Objectives*</label>
                  <textarea class="form-control" name="objectives" id="edit_possible_objectives2" rows="3" required>Learn terminal procedures, safety protocols, and passenger assistance</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_delivery_mode2">Delivery Mode*</label>
                  <select class="form-select" name="delivery_mode" id="edit_possible_delivery_mode2" required>
                    <option value="On-site Training" style="background-color: #198754; color: white;">🏢 On-site Training</option>
                    <option value="Online Training" style="background-color: #fd7e14; color: white;">💻 Online Training</option>
                    <option value="Blended Learning" selected style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                    <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                    <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                    <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Destination</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addDestinationForm');
    const formErrors = document.getElementById('formErrors');
    const saveBtn = document.getElementById('saveDestinationBtn');
    const modalEl = document.getElementById('addDestinationModal');
    const bsModal = new bootstrap.Modal(modalEl);

    // Function to show success toast
    function showSuccessToast(message) {
      const toast = new bootstrap.Toast(document.getElementById('successToast'), {
        animation: true,
        autohide: true,
        delay: 3000
      });
      document.querySelector('#successToast .toast-body').textContent = message || 'Record saved successfully';
      toast.show();
    }

    // Function to reset form and clear errors
    function resetForm() {
      addForm.reset();
      formErrors.style.display = 'none';
      
      // Clear validation errors
      addForm.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        const feedback = el.parentElement.querySelector('.invalid-feedback');
        if (feedback) feedback.remove();
      });
      // Hide duplicate warning
      document.getElementById('duplicateWarning').classList.add('d-none');
      
      // Clear any error notifications from the page
      setTimeout(() => {
        const errorNotifications = document.querySelectorAll('.alert-danger');
        errorNotifications.forEach(notification => {
          if (notification.textContent.includes('already exists') || 
              notification.textContent.includes('duplicate') ||
              notification.textContent.includes('BESTLINK COLLEGE')) {
            notification.remove();
          }
        });
        
        // Also remove any toast notifications with error content
        const toastNotifications = document.querySelectorAll('.toast');
        toastNotifications.forEach(toast => {
          if (toast.textContent.includes('already exists') || 
              toast.textContent.includes('duplicate') ||
              toast.textContent.includes('BESTLINK COLLEGE')) {
            toast.remove();
          }
        });
      }, 100);
    }

    // Function to check for duplicates
    function checkForDuplicates() {
      const employeeId = document.getElementById('employee_id').value;
      const destinationName = document.getElementById('destination_name').value;
      const deliveryMode = document.getElementById('delivery_mode').value;
      const duplicateWarning = document.getElementById('duplicateWarning');
      const saveBtn = document.getElementById('saveDestinationBtn');

      // Always hide warning and enable button initially
      duplicateWarning.classList.add('d-none');
      saveBtn.disabled = false;

      // Only check for duplicates if employee, destination AND delivery mode are selected
      if (employeeId && employeeId !== '' && destinationName && destinationName !== '' && destinationName !== 'custom' && deliveryMode && deliveryMode !== '') {
        // Get employee name from select option
        const selectedOption = document.querySelector(`#employee_id option[value="${employeeId}"]`);
        const selectedEmployeeName = selectedOption ? selectedOption.textContent.trim() : '';
        
        // Only proceed if we have a valid employee name
        if (selectedEmployeeName && selectedEmployeeName !== 'Select Employee') {
          // Check existing records in the table
          const tableRows = document.querySelectorAll('#destinationTableBody tr[data-destination-id]');
          let isDuplicate = false;
          let existingRecordId = null;

          tableRows.forEach(row => {
            const rowEmployee = row.children[1]?.textContent.trim();
            const rowDestination = row.children[2]?.textContent.trim();
            const rowDeliveryMode = row.children[4]?.textContent.trim();
            const recordId = row.getAttribute('data-destination-id');
            
            // Check if EXACT same employee, destination AND delivery mode combination exists
            if (rowEmployee === selectedEmployeeName && 
                rowDestination.toLowerCase().trim() === destinationName.toLowerCase().trim() &&
                rowDeliveryMode.includes(deliveryMode)) {
              isDuplicate = true;
              existingRecordId = recordId;
            }
          });

          if (isDuplicate) {
            duplicateWarning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> This employee already has an active training record for this destination with ${deliveryMode} delivery mode. Please update the existing record (ID: ${existingRecordId}) instead.`;
            duplicateWarning.classList.remove('d-none');
            saveBtn.disabled = true;
          }
        }
      }
    }

    if (addForm) {
      addForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Check for duplicates one more time before submission
        checkForDuplicates();
        
        // If save button is disabled due to duplicates, prevent submission
        if (saveBtn.disabled && duplicateWarning && !duplicateWarning.classList.contains('d-none')) {
          return false;
        }

        try {
          // Show loading state
          saveBtn.disabled = true;
          saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
          formErrors.style.display = 'none';

          const formData = new FormData(addForm);

          const response = await fetch(addForm.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (response.ok && data.success) {
            // Success case - close modal and show success
            resetForm();
            bsModal.hide();
            
            // Clear all error notifications immediately
            document.querySelectorAll('.alert-danger').forEach(notification => {
              notification.remove();
            });
            
            // Clear notification container
            const notificationContainer = document.getElementById('notificationContainer');
            if (notificationContainer) {
              notificationContainer.innerHTML = '';
            }
            
            showNotification('success', 'Record Saved!', data.message || 'Record saved successfully');
            
            // Reload page to show updated data
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            // Error case - show error but don't close modal
            if (data.message) {
              formErrors.innerHTML = data.message;
              formErrors.style.display = 'block';
            }
            showNotification('error', 'Error', data.message || 'Failed to save record');
          }

        } catch (error) {
          console.error('Form submission error:', error);
          // Show error message
          formErrors.innerHTML = 'An error occurred while saving the record.';
          formErrors.style.display = 'block';
          showNotification('error', 'Error', 'An error occurred while saving the record.');
        } finally {
          saveBtn.disabled = false;
          saveBtn.innerHTML = 'Save Record';
        }
      });
    }



    // Modal backdrop removal
    function removeBackdrops() {
      document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    }

    // Add event listeners for duplicate checking
    document.getElementById('employee_id').addEventListener('change', checkForDuplicates);
    document.getElementById('destination_name').addEventListener('change', checkForDuplicates);
    document.getElementById('delivery_mode').addEventListener('change', checkForDuplicates);

    modalEl.addEventListener('hidden.bs.modal', function () {
      resetForm();
      removeBackdrops();
      // Force remove any lingering error notifications
      const errorNotifications = document.querySelectorAll('.alert-danger');
      errorNotifications.forEach(notification => {
        if (notification.textContent.includes('already exists')) {
          notification.remove();
        }
      });
    });

    modalEl.addEventListener('shown.bs.modal', function() {
      removeBackdrops();
      // Clear any previous error states
      formErrors.style.display = 'none';
      document.getElementById('duplicateWarning').classList.add('d-none');
    });
  });
  
  // Enhanced backdrop removal for all modals
  document.querySelectorAll('.modal').forEach(function(modalEl) {
    modalEl.addEventListener('shown.bs.modal', function() {
      document.querySelectorAll('.modal-backdrop').forEach(function(bd) { bd.remove(); });
    });
    modalEl.addEventListener('hidden.bs.modal', function() {
      document.querySelectorAll('.modal-backdrop').forEach(function(bd) { bd.remove(); });
      // Remove any error notifications that might be lingering
      setTimeout(() => {
        const errorNotifications = document.querySelectorAll('.alert-danger');
        errorNotifications.forEach(notification => {
          if (notification.textContent.includes('already exists') || notification.textContent.includes('duplicate')) {
            notification.remove();
          }
        });
      }, 100);
    });
  });

  // Filter functionality
  document.getElementById('applyFilters').addEventListener('click', function() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const progressFilter = document.getElementById('progressFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const rows = document.querySelectorAll('#destinationTableBody tr');
    rows.forEach(row => {
      let show = true;
      const name = row.children[1]?.textContent.toLowerCase() || '';
      const progress = row.children[4]?.textContent || '';
      const status = row.children[5]?.textContent.toLowerCase() || '';
      const date = row.children[3]?.textContent || '';
      if (search && !name.includes(search)) show = false;
      if (progressFilter === 'completed' && status !== 'completed') show = false;
      if (progressFilter === 'in-progress' && status !== 'in progress') show = false;
      if (progressFilter === 'not-started' && status !== 'not started') show = false;
      if (dateFilter && !date.includes(dateFilter)) show = false;
      row.style.display = show ? '' : 'none';
    });
  });

  // Export Excel (basic CSV)
  document.getElementById('exportExcel').addEventListener('click', function() {
    let csv = '';
    document.querySelectorAll('table thead th').forEach(th => {
      csv += '"' + th.textContent.trim() + '",';
    });
    csv = csv.slice(0, -1) + '\n';
    document.querySelectorAll('table tbody tr').forEach(row => {
      if (row.style.display !== 'none') {
        row.querySelectorAll('td').forEach(td => {
          csv += '"' + td.textContent.trim() + '",';
        });
        csv = csv.slice(0, -1) + '\n';
      }
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'destination_knowledge_training.csv';
    link.click();
  });

  // Export PDF (print)
  document.getElementById('exportPdf').addEventListener('click', function() {
    window.print();
  });

  // (Deprecated duplicate) Sync Missing Records handler removed. Use setupSyncButton() below.
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

  // Progress tracking functionality
  function checkTrainingProgress() {
    const trainingIds = JSON.parse(sessionStorage.getItem('activeTrainingIds') || '[]');
    const destinationId = sessionStorage.getItem('destinationId');

    if (trainingIds.length > 0 && destinationId) {
      trainingIds.forEach(trainingId => {
        fetch(`/employee_trainings_dashboard/${trainingId}`)
          .then(res => res.json())
          .then(data => {
            if (data.progress !== undefined) {
              // Update destination knowledge progress
              fetch(`/destination-knowledge-training/progress/${destinationId}/${trainingId}`, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                  'Content-Type': 'application/json'
                }
              })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  // Update UI if needed
                  const row = document.querySelector(`tr[data-destination-id="${destinationId}"]`);
                  if (row) {
                    const progressBar = row.querySelector('progress');
                    const progressText = row.querySelector('.progress-text');
                    const statusBadge = row.querySelector('.status-badge');
                    if (progressBar) progressBar.value = data.progress;
                    if (progressText) progressText.textContent = `${data.progress}%`;
                    if (statusBadge) {
                      statusBadge.textContent = data.status;
                      statusBadge.className = `badge ${data.status === 'Completed' ? 'bg-success' : 'bg-primary'} bg-opacity-10 text-${data.status === 'Completed' ? 'success' : 'primary'} fs-6`;
                    }
                  }
                }
              })
              .catch(console.error);
            }
          })
          .catch(console.error);
      });
    }
  }

  // Request Activation functionality is now handled in attachEventListeners()

  // Notification function
  function showNotification(type, title, message) {
    const container = document.getElementById('notificationContainer');
    const toastId = 'toast-' + Date.now();
    
    let bgClass = 'text-bg-success';
    let icon = 'bi-check-circle';
    
    if (type === 'error') {
      bgClass = 'text-bg-danger';
      icon = 'bi-x-circle';
    } else if (type === 'warning') {
      bgClass = 'text-bg-warning';
      icon = 'bi-exclamation-triangle';
    } else if (type === 'info') {
      bgClass = 'text-bg-info';
      icon = 'bi-info-circle';
    }
    
    const toastHTML = `
      <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <div class="d-flex align-items-center">
              <i class="bi ${icon} me-2"></i>
              <div>
                <strong>${title}</strong><br>
                <small>${message}</small>
              </div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
      toastElement.remove();
    });
  }

  // Function to attach event listeners to new elements
  function attachEventListeners() {


    // Re-attach request activation button listeners
    document.querySelectorAll('.request-activation-btn:not([data-listener-attached])').forEach(button => {
      button.setAttribute('data-listener-attached', 'true');
      button.addEventListener('click', async function() {
        const recordId = this.getAttribute('data-record-id');
        const destinationName = this.getAttribute('data-destination-name');
        const deliveryMode = this.getAttribute('data-delivery-mode');
        const alreadyAssigned = this.getAttribute('data-already-assigned');
        
        // Check if already assigned
        if (alreadyAssigned === 'true') {
          const message = deliveryMode === 'Online Training' 
            ? 'This course is already assigned to the employee in the training dashboard.'
            : 'This training is already assigned or in upcoming training for this employee.';
          showNotification('warning', 'Already Assigned', message);
          return;
        }
        
        // Customize confirmation message based on delivery mode
        const confirmMessage = deliveryMode === 'Online Training' 
          ? `Request activation for online course: ${destinationName}?`
          : `Request ${deliveryMode.toLowerCase()} for: ${destinationName}?`;
          
        if (!confirm(confirmMessage)) {
          return;
        }

        try {
          this.disabled = true;
          this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Requesting...';

          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          
          const response = await fetch(`{{ url('admin/destination-knowledge-training') }}/${recordId}/request-activation`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              delivery_mode: deliveryMode
            })
          });

          const data = await response.json();
          
          if (data.success) {
            const successMessage = deliveryMode === 'Online Training' 
              ? 'Course activation request submitted successfully!'
              : `${deliveryMode} request submitted successfully!`;
            showNotification('success', 'Request Submitted', data.message || successMessage);
            
            // Update button to show pending status
            this.innerHTML = '<i class="bi bi-clock"></i> Request Submitted';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success', 'disabled');
            this.disabled = true;
            
            // Redirect to course management after a short delay for online training
            if (deliveryMode === 'Online Training') {
              setTimeout(() => {
                if (data.redirect_url) {
                  window.location.href = data.redirect_url;
                }
              }, 3000);
            }
          } else {
            showNotification('error', 'Request Failed', data.message || 'Request failed.');
          }
        } catch (error) {
          console.error('Request activation error:', error);
          showNotification('error', 'Request Error', 'Request failed: ' + (error.message || 'Please try again'));
        } finally {
          this.disabled = false;
          const buttonText = deliveryMode === 'Online Training' ? 'Request Activation' : 'Request Training';
          this.innerHTML = `<i class="bi bi-clipboard-check"></i> ${buttonText}`;
        }
      });
    });
  }

  // Possible Training Destinations functionality
  function initializePossibleDestinations() {
    // Add Possible Destination Form Handler
    const addPossibleForm = document.getElementById('addPossibleDestinationForm');
    if (addPossibleForm) {
      addPossibleForm.addEventListener('submit', function(e) {
        // Don't prevent default - let it submit to backend
        const saveBtn = document.getElementById('savePossibleDestinationBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
      });
    }
    
    // Edit Possible Destination Form Handlers
    const editForms = ['editPossibleDestinationForm1', 'editPossibleDestinationForm2'];
    editForms.forEach(formId => {
      const form = document.getElementById(formId);
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const submitBtn = form.querySelector('button[type="submit"]');
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
          
          // Get form data
          const formData = new FormData(form);
          const destination = formData.get('destination');
          const details = formData.get('details');
          const objectives = formData.get('objectives');
          const duration = formData.get('duration');
          const deliveryMode = formData.get('delivery_mode');
          
          // Update the corresponding table row
          const rowId = formId.includes('1') ? 1 : 2;
          const tableBody = document.getElementById('possibleDestinationsTableBody');
          const rows = tableBody.querySelectorAll('tr');
          
          rows.forEach(row => {
            if (row.cells[0].textContent == rowId) {
              row.cells[1].textContent = destination;
              row.cells[2].textContent = details;
              row.cells[3].textContent = objectives;
              row.cells[4].textContent = duration;
              row.cells[5].textContent = deliveryMode;
            }
          });
          
          // Close modal
          const modalId = formId.replace('Form', 'Modal');
          const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
          modal.hide();
          
          showNotification('success', 'Destination Updated', 'Possible training destination updated successfully!');
          
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Update Destination';
        });
      }
    });
  }
  
  // Delete Possible Destination function
  function deletePossibleDestination(id, destinationName) {
    if (confirm(`Are you sure you want to delete "${destinationName}"?`)) {
      fetch(`/admin/destination-knowledge-training/destroy-possible/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Remove the row from the table
          const tableBody = document.getElementById('possibleDestinationsTableBody');
          const rows = tableBody.querySelectorAll('tr');
          
          rows.forEach(row => {
            if (row.cells[0].textContent == id) {
              row.remove();
            }
          });
          
          showNotification('success', 'Destination Deleted', data.message);
        } else {
          showNotification('error', 'Delete Failed', data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error', 'Failed to delete destination. Please try again.');
      });
    }
  }

  // Auto-populate destination details when destination is selected
  function setupDestinationAutoPopulation() {
    const destinationSelect = document.getElementById('destination_name');
    const customDestinationInput = document.getElementById('custom_destination_name');
    const detailsTextarea = document.getElementById('details');
    const objectivesTextarea = document.getElementById('objectives');
    const durationInput = document.getElementById('duration');
    const deliveryModeSelect = document.getElementById('delivery_mode');

    if (destinationSelect) {
      destinationSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        
        if (selectedValue === 'custom') {
          // Show custom input field
          customDestinationInput.classList.remove('d-none');
          customDestinationInput.required = true;
          
          // Clear auto-populated fields
          detailsTextarea.value = '';
          objectivesTextarea.value = '';
          durationInput.value = '';
          deliveryModeSelect.value = '';
        } else if (selectedValue && selectedValue !== '') {
          // Hide custom input field
          customDestinationInput.classList.add('d-none');
          customDestinationInput.required = false;
          customDestinationInput.value = '';
          
          // Auto-populate from possible destinations table data
          const possibleDestinationsTable = document.getElementById('possibleDestinationsTableBody');
          let foundDestination = false;
          
          if (possibleDestinationsTable) {
            const rows = possibleDestinationsTable.querySelectorAll('tr');
            rows.forEach(row => {
              const destinationCell = row.querySelector('td:nth-child(2)');
              if (destinationCell && destinationCell.textContent.trim() === selectedValue) {
                // Found matching destination, populate fields
                const details = row.querySelector('td:nth-child(3)')?.textContent.trim() || '';
                const objectives = row.querySelector('td:nth-child(4)')?.textContent.trim() || '';
                const duration = row.querySelector('td:nth-child(5)')?.textContent.trim() || '';
                const deliveryModeCell = row.querySelector('td:nth-child(6)');
                let deliveryMode = '';
                
                // Extract delivery mode from badge text
                if (deliveryModeCell) {
                  const badge = deliveryModeCell.querySelector('.badge');
                  if (badge) {
                    const badgeText = badge.textContent.trim();
                    // Remove emoji and extract text
                    deliveryMode = badgeText.replace(/^[^\w\s]+\s*/, '').trim();
                  }
                }
                
                // Populate form fields
                detailsTextarea.value = details;
                objectivesTextarea.value = objectives;
                durationInput.value = duration;
                deliveryModeSelect.value = deliveryMode;
                
                foundDestination = true;
                showNotification('success', 'Auto-populated', 'Destination details loaded from possible destinations!');
              }
            });
          }
          
          // If not found in possible destinations, try API fallback
          if (!foundDestination) {
            fetch(`/admin/destination-knowledge-training/destination-details/${encodeURIComponent(selectedValue)}`)
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Auto-populate fields
                  detailsTextarea.value = data.data.details;
                  objectivesTextarea.value = data.data.objectives;
                  durationInput.value = data.data.duration;
                  deliveryModeSelect.value = data.data.delivery_mode;
                  
                  showNotification('success', 'Auto-populated', 'Destination details loaded from database!');
                } else {
                  showNotification('warning', 'No Details Found', 'Please fill in the details manually.');
                }
              })
              .catch(error => {
                console.error('Error fetching destination details:', error);
                showNotification('info', 'Manual Entry Required', 'Please fill in the destination details manually.');
              });
          }
        } else {
          // Clear all fields when no destination selected
          customDestinationInput.classList.add('d-none');
          customDestinationInput.required = false;
          customDestinationInput.value = '';
          detailsTextarea.value = '';
          objectivesTextarea.value = '';
          durationInput.value = '';
          deliveryModeSelect.value = '';
        }
      });
    }
  }

  // Update form submission to handle custom destination and use normal form submission
  function setupFormSubmission() {
    const addDestinationForm = document.getElementById('addDestinationForm');
    if (addDestinationForm) {
      addDestinationForm.addEventListener('submit', function(e) {
        const destinationSelect = document.getElementById('destination_name');
        const customDestinationInput = document.getElementById('custom_destination_name');
        
        // If custom destination is selected, use the custom input value
        if (destinationSelect && destinationSelect.value === 'custom' && customDestinationInput && customDestinationInput.value.trim()) {
          // Create a hidden input to override the destination name
          const existingHidden = this.querySelector('input[name="destination_name"][type="hidden"]');
          if (existingHidden) {
            existingHidden.remove();
          }
          
          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'destination_name';
          hiddenInput.value = customDestinationInput.value.trim();
          this.appendChild(hiddenInput);
        }
        
        // Allow normal form submission - this handles CSRF tokens properly
        return true;
      });
    }
  }

  // Add New Destination Button Handler
  function setupAddNewButton() {
    const addNewBtn = document.getElementById('addNewDestinationBtn');
    const modalElement = document.getElementById('addDestinationModal');
    
    if (addNewBtn && modalElement) {
      // Set up modal event listeners
      modalElement.addEventListener('shown.bs.modal', function () {
        console.log('Modal shown - setting up delivery mode handler');
        setupDeliveryModeHandler();
        
        // Check if Online Training is already selected and trigger immediately
        const deliveryModeSelect = document.getElementById('delivery_mode');
        if (deliveryModeSelect && deliveryModeSelect.value === 'Online Training') {
          if (window.handleDeliveryModeChange) {
            window.handleDeliveryModeChange();
          }
        }
      });
      
      addNewBtn.addEventListener('click', function() {
        console.log('Add New button clicked - opening modal');
        try {
          const modal = new bootstrap.Modal(modalElement);
          modal.show();
        } catch (error) {
          console.error('Error opening modal:', error);
          // Fallback method
          modalElement.style.display = 'block';
          modalElement.classList.add('show');
          
          // Set up delivery mode handler for fallback too
          setTimeout(() => {
            setupDeliveryModeHandler();
          }, 100);
        }
      });
    }
  }

  // Setup delivery mode change handler for conditional fields
  function setupDeliveryModeHandler() {
    const deliveryModeSelect = document.getElementById('delivery_mode');
    const onlineTrainingFields = document.getElementById('onlineTrainingFields');
    const progressLevelField = document.getElementById('progress_level');
    
    console.log('Setting up delivery mode handler...');
    console.log('deliveryModeSelect:', deliveryModeSelect);
    console.log('onlineTrainingFields:', onlineTrainingFields);
    console.log('progressLevelField:', progressLevelField);
    
    if (deliveryModeSelect && onlineTrainingFields && progressLevelField) {
      // Remove existing event listeners to prevent duplicates
      if (window.handleDeliveryModeChange) {
        deliveryModeSelect.removeEventListener('change', window.handleDeliveryModeChange);
        deliveryModeSelect.removeEventListener('input', window.handleDeliveryModeChange);
      }
      
      // Define the handler function
      window.handleDeliveryModeChange = function() {
        console.log('Delivery mode changed to:', deliveryModeSelect.value);
        if (deliveryModeSelect.value === 'Online Training') {
          onlineTrainingFields.style.display = 'block';
          progressLevelField.required = true;
          console.log('Showing online training fields');
        } else {
          onlineTrainingFields.style.display = 'none';
          progressLevelField.required = false;
          progressLevelField.value = '0'; // Reset to default
          const expiredDateField = document.getElementById('expired_date');
          if (expiredDateField) {
            expiredDateField.value = ''; // Clear expired date
          }
          console.log('Hiding online training fields');
        }
      };

      // Add both change and input event listeners for better compatibility
      deliveryModeSelect.addEventListener('change', window.handleDeliveryModeChange);
      deliveryModeSelect.addEventListener('input', window.handleDeliveryModeChange);
      
      // Trigger immediately to check current state
      window.handleDeliveryModeChange();
      
      console.log('Delivery mode handler set up successfully');
    } else {
      console.error('Could not find required elements for delivery mode handler');
    }
  }

  // Setup Assign to Upcoming Training functionality
  function setupAssignToUpcomingTraining() {
    const assignButtons = document.querySelectorAll('.assign-to-upcoming-btn');
    
    assignButtons.forEach(button => {
      button.addEventListener('click', function() {
        const destinationId = this.getAttribute('data-destination-id');
        const employeeName = this.getAttribute('data-employee-name');
        const destinationName = this.getAttribute('data-destination-name');
        
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Assigning...';
        
        // Get CSRF token safely with multiple fallback methods
        let csrfTokenValue = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (csrfToken) {
          csrfTokenValue = csrfToken.getAttribute('content') || csrfToken.content;
        }
        
        // Fallback: try to get from Laravel's global variable
        if (!csrfTokenValue && typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) {
          csrfTokenValue = window.Laravel.csrfToken;
        }
        
        // Fallback: try to get from form token if exists
        if (!csrfTokenValue) {
          const tokenInput = document.querySelector('input[name="_token"]');
          if (tokenInput) {
            csrfTokenValue = tokenInput.value;
          }
        }
        
        if (!csrfTokenValue) {
          console.error('CSRF token not found');
          showNotification('error', 'Error', 'Security token not found. Please refresh the page and try again.');
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-calendar-check"></i> Assign to Upcoming Training';
          return;
        }

        // Make AJAX request
        fetch('{{ route("admin.destination-knowledge-training.assign-to-upcoming") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfTokenValue,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            destination_id: destinationId
          })
        })
        .then(response => {
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          
          return response.json();
        })
        .then(data => {
          console.log('Response data:', data);
          
          if (data.success) {
            // Show success notification
            showNotification('success', 'Success', data.message);
            
            // Replace button with success badge
            const parentTd = this.parentElement;
            parentTd.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved for Upcoming</span>';
            
            // Refresh page after 2 seconds to show updated status
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            // Show error notification with detailed message
            const errorMessage = data.message || 'Unknown error occurred';
            console.error('Server error:', errorMessage);
            showNotification('error', 'Error', errorMessage);
            
            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-calendar-check"></i> Assign to Upcoming Training';
          }
        })
        .catch(error => {
          console.error('Network or parsing error:', error);
          console.error('Error details:', error.message);
          
          // Check if it's a CSRF token error and try to refresh
          if (error.message.includes('419') || error.message.includes('CSRF') || error.message.includes('token')) {
            console.log('CSRF token error detected, attempting to refresh token...');
            
            // Try to refresh CSRF token
            fetch('/csrf-refresh', {
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
                // Update global variable
                if (window.Laravel) {
                  window.Laravel.csrfToken = data.csrf_token;
                }
                showNotification('info', 'Token Refreshed', 'Security token refreshed. Please try again.');
              }
            })
            .catch(() => {
              showNotification('error', 'Token Error', 'Please refresh the page and try again.');
            });
          }
          
          // More specific error message
          let errorMessage = 'Failed to assign training. ';
          if (error.message.includes('HTTP error')) {
            errorMessage += `Server returned ${error.message}. `;
          } else if (error.message.includes('JSON')) {
            errorMessage += 'Invalid response from server. ';
          } else if (error.message.includes('419') || error.message.includes('CSRF')) {
            errorMessage += 'Security token expired. Token has been refreshed, please try again. ';
          } else {
            errorMessage += 'Network error. ';
          }
          errorMessage += 'Please try again.';
          
          showNotification('error', 'Error', errorMessage);
          
          // Reset button with dynamic text
          this.disabled = false;
          const isAlreadyAssigned = this.getAttribute('data-already-assigned') === 'true';
          this.innerHTML = isAlreadyAssigned ? 
            '<i class="bi bi-calendar-check"></i> Re-assign to Upcoming' : 
            '<i class="bi bi-calendar-check"></i> Assign to Upcoming Training';
        });
      });
    });
  }

  // Setup filter functionality
  function setupFilters() {
    const searchInput = document.getElementById('searchInput');
    const progressFilter = document.getElementById('progressFilter');
    const dateFilter = document.getElementById('dateFilter');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const tableRows = document.querySelectorAll('table tbody tr:not(.empty-row)');

    function applyFilters() {
      const searchTerm = searchInput.value.toLowerCase();
      const statusFilter = progressFilter.value.toLowerCase();
      const dateFilterValue = dateFilter.value;

      tableRows.forEach(row => {
        const employeeName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const destination = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';
        const createdDate = row.querySelector('td:nth-child(7)')?.textContent || '';

        let showRow = true;

        // Search filter
        if (searchTerm && !employeeName.includes(searchTerm) && !destination.includes(searchTerm)) {
          showRow = false;
        }

        // Status filter
        if (statusFilter && !status.includes(statusFilter)) {
          showRow = false;
        }

        // Date filter
        if (dateFilterValue && !createdDate.includes(dateFilterValue)) {
          showRow = false;
        }

        row.style.display = showRow ? '' : 'none';
      });
    }

    // Event listeners
    applyFiltersBtn.addEventListener('click', applyFilters);
    searchInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') applyFilters();
    });
    progressFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);

    // Real-time search
    searchInput.addEventListener('input', applyFilters);
  }

  // Setup export functionality
  function setupExportButtons() {
    const exportExcelBtn = document.getElementById('exportExcel');
    const exportPdfBtn = document.getElementById('exportPdf');

    if (exportExcelBtn) {
      exportExcelBtn.addEventListener('click', function() {
        showNotification('info', 'Export', 'Excel export functionality coming soon...');
      });
    }

    if (exportPdfBtn) {
      exportPdfBtn.addEventListener('click', function() {
        showNotification('info', 'Export', 'PDF export functionality coming soon...');
      });
    }
  }

  // Setup sync existing records button
  function setupSyncButton() {
    const syncBtn = document.getElementById('syncExistingBtn');
    
    if (syncBtn) {
      syncBtn.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Syncing...';
        
        fetch('{{ route("admin.destination-knowledge-training.sync-existing") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(async (response) => {
          const contentType = response.headers.get('content-type') || '';
          let payload = {};
          try {
            payload = contentType.includes('application/json') ? await response.json() : { success: response.ok, message: await response.text() };
          } catch (_) {
            payload = { success: response.ok, message: 'Unexpected response from server.' };
          }
          if (payload.success) {
            showNotification('success', 'Success', payload.message || 'Sync completed successfully.');
            setTimeout(() => window.location.reload(), 1500);
          } else {
            const msg = (payload && payload.message) ? String(payload.message).slice(0, 500) : 'Sync failed. Please try again.';
            showNotification('error', 'Error', msg);
          }
        })
        .catch(() => {
          showNotification('error', 'Error', 'Network error during sync. Please try again.');
        })
        .finally(() => {
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sync Missing Records';
        });
      });
    }
  }

  // Setup sync training status button
  function setupSyncStatusButton() {
    const syncStatusBtn = document.getElementById('syncStatusBtn');
    
    if (syncStatusBtn) {
      syncStatusBtn.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Syncing Status...';
        
        fetch('/admin/destination-knowledge-training/sync-all-records', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(async (response) => {
          const contentType = response.headers.get('content-type') || '';
          let payload = {};
          try {
            payload = contentType.includes('application/json') ? await response.json() : { success: response.ok, message: await response.text() };
          } catch (_) {
            payload = { success: response.ok, message: 'Unexpected response from server.' };
          }
          if (payload.success) {
            showNotification('success', 'Status Sync Complete', payload.message || 'Status synchronized.');
            setTimeout(() => window.location.reload(), 1500);
          } else {
            const msg = (payload && payload.message) ? String(payload.message).slice(0, 500) : 'Status sync failed. Please try again.';
            showNotification('error', 'Sync Error', msg);
          }
        })
        .catch(() => {
          showNotification('error', 'Error', 'Network error during status sync.');
        })
        .finally(() => {
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Sync Training Status';
        });
      });
    }
  }

  // Setup delete possible destination buttons
  function setupDeletePossibleButtons() {
    document.querySelectorAll('.delete-possible-btn').forEach(button => {
      button.addEventListener('click', function() {
        const destinationId = this.getAttribute('data-destination-id');
        const destinationName = this.getAttribute('data-destination-name');
        deletePossibleDestination(destinationId, destinationName);
      });
    });
  }

  // Check progress every 30 seconds
  setInterval(checkTrainingProgress, 30000);
  document.addEventListener('DOMContentLoaded', function() {
    checkTrainingProgress();
    attachEventListeners();
    initializePossibleDestinations();
    setupDestinationAutoPopulation();
    setupFormSubmission();
    setupAddNewButton();
    setupDeliveryModeHandler();
    setupAssignToUpcomingTraining();
    setupFilters();
    setupExportButtons();
    setupSyncButton();
    setupSyncStatusButton();
    setupDeletePossibleButtons();
  });
</script>

</body>
</html>
