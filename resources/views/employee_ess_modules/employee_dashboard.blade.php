<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels - Employee Portal</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">

  <style>
    /* Announcement Section Styling */
    .announcement-message {
      line-height: 1.5;
      word-wrap: break-word;
    }

    .announcement-details .card {
      border-left: 4px solid #0d6efd;
    }

    .table th {
      font-weight: 600;
      color: #495057;
      border-bottom: 2px solid #dee2e6;
    }

    .badge {
      font-size: 0.75rem;
      font-weight: 500;
    }

    .btn-outline-primary:hover {
      transform: translateY(-1px);
      transition: all 0.2s ease;
    }

    /* Priority-based row highlighting */
    .table tbody tr:hover {
      background-color: rgba(13, 110, 253, 0.05);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .announcement-message {
        font-size: 0.9rem;
      }

      .table th, .table td {
        padding: 0.5rem;
        font-size: 0.85rem;
      }

      .badge {
        font-size: 0.7rem;
      }
    }
  </style>

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
                if ($hour >= 12 && $hour < 6) {
                  $greeting = 'Good morning';
                } elseif ($hour >= 6 && $hour < 12) {
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

<!-- Company Announcements Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-megaphone me-2"></i>Company Announcements</h5>
        <span class="badge bg-primary">{{ isset($announcements) ? $announcements->count() : 0 }} Active</span>
      </div>
      <div class="card-body">
        @if(isset($announcements) && $announcements->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th width="15%">Date</th>
                  <th width="20%">Title</th>
                  <th width="40%">Message</th>
                  <th width="15%">Priority</th>
                  <th width="10%" class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($announcements as $announcement)
                  <tr>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="fw-bold text-primary">{{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y') }}</span>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($announcement->created_at)->format('h:i A') }}</small>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        @if($announcement->priority === 'urgent')
                          <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        @elseif($announcement->priority === 'important')
                          <i class="bi bi-info-circle-fill text-warning me-2"></i>
                        @else
                          <i class="bi bi-chat-dots-fill text-info me-2"></i>
                        @endif
                        <span class="fw-semibold">{{ $announcement->title ?? 'Company Update' }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="announcement-message">
                        {{ Str::limit($announcement->message ?? $announcement->content ?? 'No message content', 100) }}
                        @if(strlen($announcement->message ?? $announcement->content ?? '') > 100)
                          <a href="#" class="text-primary text-decoration-none" onclick="viewAnnouncementDetails('{{ $announcement->id }}')">
                            <small>Read more...</small>
                          </a>
                        @endif
                      </div>
                    </td>
                    <td>
                      @php
                        $priority = $announcement->priority ?? 'normal';
                        $badgeClass = match($priority) {
                          'urgent' => 'bg-danger',
                          'important' => 'bg-warning text-dark',
                          'high' => 'bg-info',
                          default => 'bg-secondary'
                        };
                      @endphp
                      <span class="badge {{ $badgeClass }}">
                        <i class="bi {{ match($priority) {
                          'urgent' => 'bi-exclamation-triangle',
                          'important' => 'bi-exclamation-circle',
                          'high' => 'bi-info-circle',
                          default => 'bi-chat-dots'
                        } }} me-1"></i>
                        {{ ucfirst($priority) }}
                      </span>
                    </td>
                    <td class="text-center">
                      <button class="btn btn-outline-primary btn-sm"
                              onclick="viewAnnouncementDetails('{{ $announcement->id }}')"
                              title="View full announcement">
                        <i class="bi bi-eye"></i>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination if needed -->
          @if(method_exists($announcements, 'links'))
            <div class="d-flex justify-content-center mt-3">
              {{ $announcements->links() }}
            </div>
          @endif
        @else
          <div class="text-center py-4">
            <i class="bi bi-megaphone text-muted" style="font-size: 3rem;"></i>
            <h6 class="text-muted mt-2">No Announcements</h6>
            <p class="text-muted small mb-0">There are currently no company announcements to display.</p>
          </div>
        @endif
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
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Training ID</th>
                <th>Training Title</th>
                <th>Start Date</th>
                <th>Expired Date</th>
                <th>Status</th>
                <th>Source</th>
                <th>Assigned By</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($upcomingTrainingsList as $u)
                <tr>
                  <td>
                    @php
                      $upcomingId = is_array($u) ? ($u['upcoming_id'] ?? '') : ($u->upcoming_id ?? '');
                    @endphp
                    {{ $upcomingId }}
                  </td>
                  <td>
                    @php
                      $trainingTitle = is_array($u) ? ($u['training_title'] ?? '') : ($u->training_title ?? '');
                    @endphp
                    {{ $trainingTitle }}
                    @php
                      $showProgress = false;
                      $source = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                      $deliveryMode = is_array($u) ? ($u['delivery_mode'] ?? null) : ($u->delivery_mode ?? null);
                      $progress = is_array($u) ? ($u['progress'] ?? 0) : ($u->progress ?? 0);

                      // For destination knowledge training, only show progress if delivery mode is "Online Training"
                      if($source == 'destination_assigned') {
                        if($deliveryMode === 'Online Training') {
                          $showProgress = true;
                        }
                      } elseif($source == 'competency_gap' || $source == 'competency_assigned') {
                        // For competency gap assignments, show progress if available
                        if($progress > 0) {
                          $showProgress = true;
                        }
                      } else {
                        // For other training types, show progress if it exists and has online-related delivery mode
                        if($progress > 0 && $deliveryMode && (strtolower($deliveryMode) == 'online' || strtolower($deliveryMode) == 'e-learning' || strtolower($deliveryMode) == 'virtual')) {
                          $showProgress = true;
                        }
                      }
                    @endphp

                    @php
                      $progressValue = is_array($u) ? ($u['progress'] ?? 0) : ($u->progress ?? 0);
                    @endphp
                    @if($showProgress && $progressValue > 0)
                      <div class="progress mt-1" style="height: 4px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progressValue }}%"></div>
                      </div>
                      <small class="text-muted">{{ $progressValue }}% complete</small>
                    @endif
                  </td>
                  <td>
                    @php
                      $startDate = is_array($u) ? ($u['start_date'] ?? null) : ($u->start_date ?? null);
                    @endphp
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : '' }}
                  </td>
                  <td>
                    @php
                      $finalExpiredDate = is_array($u) ? ($u['expired_date'] ?? null) : ($u->expired_date ?? null);
                      $sourceValue = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);

                      // For competency gap trainings, try to get expiration date from competency gap table
                      if (($sourceValue === 'competency_assigned' || $sourceValue === 'competency_gap' || $sourceValue === 'admin_assigned') && !$finalExpiredDate) {
                        $employeeId = is_array($u) ? ($u['employee_id'] ?? null) : ($u->employee_id ?? null);
                        $trainingTitle = is_array($u) ? ($u['training_title'] ?? '') : ($u->training_title ?? '');

                        if ($employeeId && $trainingTitle) {
                          // Find matching competency gap by training title
                          $competencyName = str_replace([' Training', ' Course', ' Program'], '', $trainingTitle);
                          $competencyGap = \App\Models\CompetencyGap::whereHas('competency', function($query) use ($competencyName) {
                            $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                          })->where('employee_id', $employeeId)->first();

                          if ($competencyGap && $competencyGap->expired_date) {
                            $finalExpiredDate = $competencyGap->expired_date;
                          }
                        }
                      }
                    @endphp

                    @if($finalExpiredDate && $finalExpiredDate != '' && $finalExpiredDate != '0000-00-00 00:00:00')
                      @php
                        $expiredDate = \Carbon\Carbon::parse($finalExpiredDate)->setTimezone('Asia/Shanghai');
                        $now = \Carbon\Carbon::now()->setTimezone('Asia/Shanghai');
                        $daysUntilExpiry = $now->diffInDays($expiredDate, false);

                        if ($daysUntilExpiry < 0) {
                          $colorClass = 'text-danger fw-bold';
                          $bgClass = 'bg-danger';
                          $status = 'EXPIRED';
                        } elseif ($daysUntilExpiry <= 7) {
                          $colorClass = 'text-warning fw-bold';
                          $bgClass = 'bg-warning';
                          $status = 'URGENT';
                        } elseif ($daysUntilExpiry <= 30) {
                          $colorClass = 'text-info fw-bold';
                          $bgClass = 'bg-info';
                          $status = 'SOON';
                        } else {
                          $colorClass = 'text-success fw-bold';
                          $bgClass = 'bg-success';
                          $status = 'ACTIVE';
                        }
                      @endphp
                      <div class="d-flex flex-column align-items-start">
                        <div><strong class="{{ $colorClass }}">{{ $expiredDate->format('M d, Y') }}</strong></div>
                        <div class="text-muted small">{{ $expiredDate->format('h:i A') }}</div>
                        <div class="w-100 mt-1">
                          @if($daysUntilExpiry >= 0)
                            <span class="badge {{ $bgClass }} text-white">
                              <i class="fas fa-clock me-1"></i>{{ floor($daysUntilExpiry) }} day{{ floor($daysUntilExpiry) == 1 ? '' : 's' }} left
                            </span>
                          @else
                            <span class="badge bg-danger text-white">
                              <i class="fas fa-exclamation-triangle me-1"></i>Expired {{ floor(abs($daysUntilExpiry)) }} day{{ floor(abs($daysUntilExpiry)) == 1 ? '' : 's' }} ago
                            </span>
                          @endif
                        </div>
                      </div>
                    @else
                      <span class="badge bg-secondary">No Expiry</span>
                    @endif
                  </td>
                  <td>
                    @php
                      // Access data safely from array or object
                      $currentProgress = 0;
                      $currentStatus = 'Not Started';
                      $courseIdForProgress = is_array($u) ? ($u['course_id'] ?? null) : ($u->course_id ?? null);
                      $employeeIdForProgress = is_array($u) ? ($u['employee_id'] ?? null) : ($u->employee_id ?? null);

                      if (is_array($u)) {
                        $currentProgress = $u['progress'] ?? 0;
                        $currentStatus = $u['status'] ?? 'Not Started';
                      } elseif (is_object($u)) {
                        $currentProgress = $u->progress ?? 0;
                        $currentStatus = $u->status ?? 'Not Started';
                      }

                      // Check if expired using the calculated expired date from above
                      $isTrainingExpired = false;
                      if ($finalExpiredDate) {
                        $expiredDate = \Carbon\Carbon::parse($finalExpiredDate);
                        $isTrainingExpired = \Carbon\Carbon::now()->gt($expiredDate);
                      }

                      // Determine final status with expiry consideration
                      if ($isTrainingExpired && $currentProgress < 100) {
                        $finalStatus = 'Expired';
                        $badgeClass = 'bg-danger';
                        $textClass = 'text-white';
                      } elseif ($currentProgress >= 100) {
                        $finalStatus = 'Completed';
                        $badgeClass = 'bg-success';
                        $textClass = 'text-white';
                      } elseif ($currentProgress > 0) {
                        $finalStatus = 'In Progress';
                        $badgeClass = 'bg-warning';
                        $textClass = 'text-dark';
                      } elseif ($currentStatus == 'Assigned') {
                        $finalStatus = 'Assigned';
                        $badgeClass = 'bg-info';
                        $textClass = 'text-dark';
                      } elseif ($currentStatus == 'Approved') {
                        $finalStatus = 'Approved';
                        $badgeClass = 'bg-success';
                        $textClass = 'text-white';
                      } elseif ($currentStatus == 'Scheduled') {
                        $finalStatus = 'Scheduled';
                        $badgeClass = 'bg-primary';
                        $textClass = 'text-white';
                      } elseif ($currentStatus == 'Ongoing') {
                        $finalStatus = 'Ongoing';
                        $badgeClass = 'bg-success';
                        $textClass = 'text-white';
                      } elseif ($currentStatus == 'Active') {
                        $finalStatus = 'Active';
                        $badgeClass = 'bg-success';
                        $textClass = 'text-white';
                      } else {
                        $finalStatus = 'Not Started';
                        $badgeClass = 'bg-secondary';
                        $textClass = 'text-white';
                      }
                    @endphp
                    <span class="badge {{ $badgeClass }} {{ $textClass }}">{{ $finalStatus }}</span>
                  </td>
                  <td>
                    @php
                      $sourceValue = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                    @endphp
                    @if($sourceValue)
                      @if($sourceValue == 'admin_assigned')
                        <span class="badge bg-danger">Admin Assigned</span>
                      @elseif($sourceValue == 'competency_assigned' || $sourceValue == 'competency_gap' || $sourceValue == 'competency_auto_assigned')
                        <span class="badge bg-warning text-dark">Competency Gap</span>
                      @elseif($sourceValue == 'destination_assigned')
                        <span class="badge bg-info">Destination Training</span>
                      @elseif($sourceValue == 'auto_assigned')
                        <span class="badge bg-success">Auto Assigned</span>
                      @elseif($sourceValue == 'employee_requested')
                        <span class="badge bg-primary">Employee Requested</span>
                      @else
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $sourceValue ?? 'Unknown')) }}</span>
                      @endif
                    @else
                      <span class="badge bg-secondary">Unknown</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $assignedByName = is_array($u) ? ($u['assigned_by_name'] ?? $u['assigned_by'] ?? null) : ($u->assigned_by_name ?? $u->assigned_by ?? null);
                      $assignedDate = is_array($u) ? ($u['assigned_date'] ?? null) : ($u->assigned_date ?? null);

                      // Clean up role names and system assignments
                      if ($assignedByName) {
                        if (str_contains($assignedByName, '(competency_auto_assigned') || $assignedByName === 'ADMIN USER' || $assignedByName === 'USER') {
                          $assignedByName = 'System Auto-Assign';
                        }
                      }
                    @endphp
                    @if($assignedByName && $assignedByName !== 'System Auto-Assign')
                      <div class="d-flex flex-column">
                        <span class="fw-bold text-primary">{{ $assignedByName }}</span>
                        @if($assignedDate)
                          <small class="text-muted">{{ \Carbon\Carbon::parse($assignedDate)->format('M d, Y') }}</small>
                        @endif
                      </div>
                    @elseif($assignedByName === 'System Auto-Assign')
                      <div class="d-flex flex-column">
                        <span class="fw-bold text-secondary">{{ $assignedByName }}</span>
                        @if($assignedDate)
                          <small class="text-muted">{{ \Carbon\Carbon::parse($assignedDate)->format('M d, Y') }}</small>
                        @endif
                      </div>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @php
                      $sourceCheck = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                      $destinationTrainingId = is_array($u) ? ($u['destination_training_id'] ?? null) : ($u->destination_training_id ?? null);
                      $needsResponse = is_array($u) ? ($u['needs_response'] ?? false) : ($u->needs_response ?? false);
                    @endphp

                    @if($sourceCheck === 'destination_assigned' && $needsResponse)
                      {{-- Show Accept/Decline buttons for destination training that needs response --}}
                      <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-success btn-sm"
                                onclick="respondToDestinationTraining('{{ $destinationTrainingId }}', 'accept', this)"
                                title="Accept this destination training">
                          <i class="bi bi-check me-1"></i>Accept
                        </button>
                        <button class="btn btn-danger btn-sm"
                                onclick="respondToDestinationTraining('{{ $destinationTrainingId }}', 'decline', this)"
                                title="Decline this destination training">
                          <i class="bi bi-x me-1"></i>Decline
                        </button>
                      </div>
                    @else
                      {{-- Show View Training button for other trainings --}}
                      <a href="{{ route('employee.my_trainings.index') }}" class="btn btn-info btn-sm">
                        <i class="bi bi-eye me-1"></i>View Training
                      </a>
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
                    $badgeClass = match(strtolower($request['status'])) {
                      'approved', 'confirmed', 'completed' => 'bg-success text-white',
                      'pending', 'waiting' => 'bg-warning text-dark',
                      'processing', 'in progress', 'ongoing' => 'bg-info text-white',
                      'rejected', 'denied', 'cancelled' => 'bg-danger text-white',
                      'on hold', 'paused' => 'bg-secondary text-white',
                      'review', 'under review' => 'bg-primary text-white',
                      default => 'bg-light text-dark border'
                    };
                  @endphp
                  <span class="badge {{ $badgeClass }} px-3 py-2" style="font-size: 0.75rem; font-weight: 600;">
                    <i class="bi {{ match(strtolower($request['status'])) {
                      'approved', 'confirmed', 'completed' => 'bi-check-circle',
                      'pending', 'waiting' => 'bi-clock',
                      'processing', 'in progress', 'ongoing' => 'bi-arrow-clockwise',
                      'rejected', 'denied', 'cancelled' => 'bi-x-circle',
                      'on hold', 'paused' => 'bi-pause-circle',
                      'review', 'under review' => 'bi-eye',
                      default => 'bi-question-circle'
                    } }} me-1"></i>
                    {{ $request['status'] }}
                  </span>
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
                    $profilePicUrl = '';
                    if ($employee->profile_picture) {
                      $profilePicUrl = asset('storage/' . $employee->profile_picture);
                    } else {
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
                  <div class="col-12">
                    <label for="verifyPassword" class="form-label">Verify Password*</label>
                    <input type="password" class="form-control" id="verifyPassword" name="verify_password" placeholder="Enter your current password" required>
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



  <!-- Announcement Details Modal -->
  <div class="modal fade" id="announcementDetailsModal" tabindex="-1" aria-labelledby="announcementDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="announcementDetailsModalLabel">
            <i class="bi bi-megaphone me-2"></i>Announcement Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="announcementDetailsContent">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="text-muted mt-2">Loading announcement details...</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      Swal.fire({
        title: `Log attendance at ${timeString}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, log it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
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
              Swal.fire({
                icon: 'success',
                title: 'Attendance logged!',
                text: 'Attendance logged successfully.'
              }).then(() => location.reload());
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error logging attendance.'
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error logging attendance. Please try again.'
            });
          });
        }
      });
    }

    function viewPayslip() {
      Swal.fire({
        title: 'View Latest Payslip?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'View',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '{{ route("employee.payslips.index") }}';
        }
      });
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
          Swal.fire({
            icon: 'success',
            title: 'Leave Application Submitted!',
            text: 'Your leave application was submitted successfully.'
          }).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('leaveApplicationModal')).hide();
            this.reset();
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Error submitting application.'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error submitting application. Please try again.'
        });
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
      // Add password verification
      const password = formData.get('verify_password');
      if (!password) {
        Swal.fire({
          icon: 'error',
          title: 'Password Required',
          text: 'Please enter your current password to update your profile.'
        });
        return;
      }
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
          Swal.fire({
            icon: 'success',
            title: 'Profile Updated',
            text: 'Your profile has been updated successfully!'
          }).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('profileUpdateModal')).hide();
            location.reload();
          });
        } else if (data.error === 'invalid_password') {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.'
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Error updating profile.'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error updating profile. Please try again.'
        });
      });
    });



    // Announcement Details Function
    function viewAnnouncementDetails(announcementId) {
      const modal = new bootstrap.Modal(document.getElementById('announcementDetailsModal'));
      const contentDiv = document.getElementById('announcementDetailsContent');

      // Show loading state
      contentDiv.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted mt-2">Loading announcement details...</p>
        </div>
      `;

      modal.show();

      // Fetch announcement details
      fetch('/employee/announcements/' + announcementId, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.announcement) {
          const announcement = data.announcement;
          const priority = announcement.priority || 'normal';

          // Priority badge styling
          const priorityBadgeClass = {
            'urgent': 'bg-danger',
            'important': 'bg-warning text-dark',
            'high': 'bg-info',
            'normal': 'bg-secondary'
          }[priority] || 'bg-secondary';

          const priorityIcon = {
            'urgent': 'bi-exclamation-triangle',
            'important': 'bi-exclamation-circle',
            'high': 'bi-info-circle',
            'normal': 'bi-chat-dots'
          }[priority] || 'bi-chat-dots';

          // Format date
          const createdDate = new Date(announcement.created_at);
          const formattedDate = createdDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });
          const formattedTime = createdDate.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
          });

          contentDiv.innerHTML = `
            <div class="announcement-details">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h4 class="fw-bold text-primary mb-2">
                    <i class="bi ${priorityIcon} me-2"></i>${announcement.title || 'Company Update'}
                  </h4>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge ${priorityBadgeClass}">
                      <i class="bi ${priorityIcon} me-1"></i>${priority.charAt(0).toUpperCase() + priority.slice(1)} Priority
                    </span>
                    <small class="text-muted">
                      <i class="bi bi-calendar me-1"></i>${formattedDate} at ${formattedTime}
                    </small>
                  </div>
                </div>
              </div>

              <div class="announcement-content">
                <div class="card bg-light border-0 p-3">
                  <p class="mb-0" style="line-height: 1.6; white-space: pre-wrap;">${announcement.message || announcement.content || 'No message content available.'}</p>
                </div>
              </div>

              ${announcement.author ? `
                <div class="mt-3 pt-3 border-top">
                  <small class="text-muted">
                    <i class="bi bi-person me-1"></i>Posted by: <strong>${announcement.author}</strong>
                  </small>
                </div>
              ` : ''}
            </div>
          `;
        } else {
          contentDiv.innerHTML = `
            <div class="text-center py-4">
              <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
              <h6 class="text-muted mt-2">Unable to Load Announcement</h6>
              <p class="text-muted small mb-0">${data.message || 'The announcement details could not be loaded at this time.'}</p>
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Error fetching announcement:', error);
        contentDiv.innerHTML = `
          <div class="text-center py-4">
            <i class="bi bi-wifi-off text-danger" style="font-size: 3rem;"></i>
            <h6 class="text-muted mt-2">Connection Error</h6>
            <p class="text-muted small mb-0">Unable to connect to the server. Please check your internet connection and try again.</p>
          </div>
        `;
      });
    }
  </script>

  <!-- CSRF Token Refresh System -->
  <script src="{{ asset('js/csrf-refresh.js') }}"></script>

</body>
</html>
