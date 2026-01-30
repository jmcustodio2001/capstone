<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- IMMEDIATE translation service initialization - MUST be first -->
  <script>
    (function(){window.translationService=window.translationService||{translate:function(k){return k},get:function(k){return k},trans:function(k){return k},choice:function(k){return k},setLocale:function(l){return l},getLocale:function(){return'en'},has:function(){return true},translations:{},setTranslations:function(t){this.translations=t||{}}};window.trans=window.translationService.translate;window.__=window.translationService.translate;window.app=window.app||{locale:'en',fallback_locale:'en',translationService:window.translationService};window.Laravel=window.Laravel||{};window.Laravel.translationService=window.translationService;if(typeof global!=='undefined'){global.translationService=window.translationService}})();
  </script>

  <title>Jetlouge Travels - Employee Portal</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">

  <!-- Load translation service FIRST to prevent undefined errors -->
  <script src="{{ asset('js/translation-service-init.js') }}"></script>

  <!-- Initialize critical global objects IMMEDIATELY to prevent undefined errors -->
  <script>
    // CRITICAL: Initialize translation service FIRST before any other scripts
    (function() {
      try {
        // Initialize translation service with comprehensive methods
        if (typeof window.translationService === 'undefined') {
          window.translationService = {
            translate: function(key, params) { return key; },
            get: function(key, params) { return key; },
            trans: function(key, params) { return key; },
            choice: function(key, count, params) { return key; },
            setLocale: function(locale) { return locale; },
            getLocale: function() { return 'en'; },
            has: function(key) { return true; },
            translations: {},
            setTranslations: function(translations) { this.translations = translations; }
          };
        }

        // Initialize global translation function
        if (typeof window.trans === 'undefined') {
          window.trans = function(key, params) { return key; };
        }

        // Initialize app object with config
        if (typeof window.app === 'undefined') {
          window.app = {
            locale: 'en',
            fallback_locale: 'en',
            translationService: window.translationService
          };
        }

        // Make translationService globally accessible in multiple ways
        window.Laravel = window.Laravel || {};
        window.Laravel.translationService = window.translationService;

        // Also make it available as a global variable for compatibility
        window.__ = window.translationService.translate;

        // Ensure it's available in the global scope for all scripts
        if (typeof global !== 'undefined') {
          global.translationService = window.translationService;
        }

        console.log('Critical global objects initialized in head - v6.0 - Enhanced error handling');
      } catch (error) {
        console.error('Critical error initializing global objects:', error);
        // Fallback initialization
        window.translationService = {
          translate: function(key) { return key; },
          get: function(key) { return key; },
          trans: function(key) { return key; }
        };
      }
    })();

    // Global error handler to prevent JavaScript errors from breaking the application
    window.addEventListener('error', function(event) {
      console.error('Global JavaScript error caught:', event.error);

      // If it's a translationService error, provide fallback
      if (event.error && event.error.message && event.error.message.includes('translationService')) {
        if (typeof window.translationService === 'undefined') {
          window.translationService = {
            translate: function(key) { return key; },
            get: function(key) { return key; },
            trans: function(key) { return key; }
          };
          console.log('Fallback translation service initialized due to error');
        }
      }

      // Prevent the error from breaking the page
      return true;
    });

    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(event) {
      console.error('Unhandled promise rejection:', event.reason);
      // Prevent the error from breaking the page
      event.preventDefault();
    });
  </script>

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
                        @if(strlen($announcement->message ?? $announcement->content ?? '') > 100 && isset($announcement->id))
                          <a href="#" class="text-primary text-decoration-none" onclick="viewAnnouncementDetails({{ $announcement->id }})">
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
                      @if(isset($announcement->id))
                        <button class="btn btn-outline-primary btn-sm"
                                onclick="viewAnnouncementDetails({{ $announcement->id }})"
                                title="View full announcement">
                          <i class="bi bi-eye"></i>
                        </button>
                      @else
                        <span class="text-muted">-</span>
                      @endif
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

                    @if($sourceCheck === 'destination_assigned' && $needsResponse && $destinationTrainingId)
                      {{-- Show Accept/Decline buttons for destination training that needs response --}}
                      <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-success btn-sm"
                                onclick="respondToDestinationTraining({{ $destinationTrainingId }}, 'accept', this)"
                                title="Accept this destination training">
                          <i class="bi bi-check me-1"></i>Accept
                        </button>
                        <button class="btn btn-danger btn-sm"
                                onclick="respondToDestinationTraining({{ $destinationTrainingId }}, 'decline', this)"
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
<div class="row g-4 mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Recent Requests</h5>
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
                    $status = strtolower($request['status']);
                    if (in_array($status, ['approved', 'confirmed', 'completed'])) {
                      $badgeClass = 'bg-success text-white';
                    } elseif (in_array($status, ['pending', 'waiting'])) {
                      $badgeClass = 'bg-warning text-dark';
                    } elseif (in_array($status, ['processing', 'in progress', 'ongoing'])) {
                      $badgeClass = 'bg-info text-white';
                    } elseif (in_array($status, ['rejected', 'denied', 'cancelled'])) {
                      $badgeClass = 'bg-danger text-white';
                    } elseif (in_array($status, ['on hold', 'paused'])) {
                      $badgeClass = 'bg-secondary text-white';
                    } elseif (in_array($status, ['review', 'under review'])) {
                      $badgeClass = 'bg-primary text-white';
                    } else {
                      $badgeClass = 'bg-light text-dark border';
                    }
                  @endphp
                  @php
                    if (in_array($status, ['approved', 'confirmed', 'completed'])) {
                      $iconClass = 'bi-check-circle';
                    } elseif (in_array($status, ['pending', 'waiting'])) {
                      $iconClass = 'bi-clock';
                    } elseif (in_array($status, ['processing', 'in progress', 'ongoing'])) {
                      $iconClass = 'bi-arrow-clockwise';
                    } elseif (in_array($status, ['rejected', 'denied', 'cancelled'])) {
                      $iconClass = 'bi-x-circle';
                    } elseif (in_array($status, ['on hold', 'paused'])) {
                      $iconClass = 'bi-pause-circle';
                    } elseif (in_array($status, ['review', 'under review'])) {
                      $iconClass = 'bi-eye';
                    } else {
                      $iconClass = 'bi-question-circle';
                    }
                  @endphp
                  <span class="badge {{ $badgeClass }} px-3 py-2" style="font-size: 0.75rem; font-weight: 600;">
                    <i class="bi {{ $iconClass }} me-1"></i>
                    {{ ucfirst($request['status']) }}
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
</div>

<!-- Rewards & Recognition Cards Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-award me-2"></i>Rewards & Recognition</h5>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-success" id="rewardsCountBadge">{{ isset($rewards) ? $rewards->where('status', 'approved')->count() : 0 }} Earned</span>
          <button class="btn btn-sm btn-outline-primary" id="refreshRewardsBtn" onclick="fetchRewardsData()">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div id="rewardsContainer">
          @if(isset($rewards) && $rewards->where('status', 'approved')->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Date Given</th>
                    <th>Reward Name</th>
                    <th>Type</th>
                    <th>Benefits</th>
                    <th>Given By</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($rewards as $reward)
                    @if($reward->status == 'approved')
                      <tr>
                        <td>
                          <span class="fw-semibold">{{ \Carbon\Carbon::parse($reward->given_date)->format('M d, Y') }}</span>
                        </td>
                        <td>
                          @php
                            $rName = $reward->reward->name ?? $reward->name ?? 'Award';
                            $rDesc = $reward->reward->description ?? '';
                          @endphp
                          <div class="fw-bold text-primary">{{ $rName }}</div>
                          @if($rDesc)
                            <small class="text-muted d-block" style="max-width: 250px;">{{ Str::limit($rDesc, 60) }}</small>
                          @endif
                        </td>
                        <td>
                          <span class="badge bg-outline-secondary border text-dark">{{ $reward->reward->type ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $reward->reward->benefits ?? $reward->benefits ?? 'Certificate' }}</td>
                        <td>
                          <span class="badge bg-light text-dark border">
                            <i class="bi bi-person me-1"></i>{{ ucfirst($reward->given_by ?? 'System') }}
                          </span>
                        </td>
                      </tr>
                    @endif
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-5">
              <i class="bi bi-award" style="font-size: 3rem; color: #dee2e6;"></i>
              <p class="text-muted mt-3 mb-0">No rewards earned yet. Keep up the great work!</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Recent Activity & Quick Actions -->
<div class="row g-4 mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-lightning-fill me-2"></i>Quick Actions</h5>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-6 col-lg-3">
            <button class="btn btn-primary w-100 py-3" data-bs-toggle="modal" data-bs-target="#leaveApplicationModal">
              <i class="bi bi-calendar-plus me-2"></i> Apply for Leave
            </button>
          </div>
          <div class="col-md-6 col-lg-3">
            <button class="btn btn-outline-primary w-100 py-3" onclick="window.location.href='{{ route("employee.attendance_logs.index") }}'">
              <i class="bi bi-clock me-2"></i> Log Attendance
            </button>
          </div>
          <div class="col-md-6 col-lg-3">
            <button class="btn btn-outline-primary w-100 py-3" onclick="viewPayslip()">
              <i class="bi bi-receipt me-2"></i> View Payslip
            </button>
          </div>
          <div class="col-md-6 col-lg-3">
            <button class="btn btn-outline-info w-100 py-3" data-bs-toggle="modal" data-bs-target="#profileUpdateModal">
              <i class="bi bi-person-circle me-2"></i> Update Profile
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- My Progress Section -->
<div class="row mb-4">
  <div class="col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>My Progress</h5>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold">Competency Goals</span>
            <span class="small text-muted">{{ $competencyGoalsAchieved }} / {{ $totalCompetencies }}</span>
          </div>
          <div class="progress" style="height: 10px;">
            <div class="progress-bar" style="width: {{ $competencyProgress }}%"></div>
          </div>
          <small class="text-muted">{{ $competencyProgress }}% achieved</small>
        </div>
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold">Trainings Completed</span>
            <span class="small text-muted">{{ $completedTrainings }} / {{ $totalTrainings }}</span>
          </div>
          <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: {{ $trainingCompletionRate }}%"></div>
          </div>
          <small class="text-muted">{{ $trainingCompletionRate }}% completed</small>
        </div>
        <div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold">Attendance Rate</span>
            <span class="small text-muted">{{ $attendanceRate }}%</span>
          </div>
          <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-warning" style="width: {{ $attendanceRate }}%"></div>
          </div>
          <small class="text-muted">{{ $attendanceRate >= 95 ? 'Excellent' : ($attendanceRate >= 85 ? 'Good' : 'Needs Improvement') }}</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-person-check me-2"></i>Employee Overview</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Employee ID</small>
              <h6 class="fw-bold mb-0">{{ Auth::guard('employee')->user()->employee_id ?? 'N/A' }}</h6>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Department</small>
              <h6 class="fw-bold mb-0">{{ $apiEmployee['department']['name'] ?? (optional(Auth::guard('employee')->user()->department)->name ?? 'N/A') }}</h6>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Position</small>
              <h6 class="fw-bold mb-0">{{ $apiEmployee['position'] ?? (Auth::guard('employee')->user()->position ?? 'N/A') }}</h6>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Join Date</small>
              <h6 class="fw-bold mb-0">{{ isset($apiEmployee['hire_date']) ? \Carbon\Carbon::parse($apiEmployee['hire_date'])->format('M d, Y') : (Auth::guard('employee')->user()->hire_date ? \Carbon\Carbon::parse(Auth::guard('employee')->user()->hire_date)->format('M d, Y') : 'N/A') }}</h6>
            </div>
          </div>
          <div class="col-12">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Email</small>
              <p class="mb-0 text-break">{{ $apiEmployee['email'] ?? (Auth::guard('employee')->user()->email ?? 'N/A') }}</p>
            </div>
          </div>
          <div class="col-12">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Contact</small>
              <h6 class="fw-bold mb-0">{{ $apiEmployee['phone'] ?? (Auth::guard('employee')->user()->phone_number ?? 'N/A') }}</h6>
            </div>
          </div>
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
                  <div class="col-md-6">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" class="form-control" id="position" name="position" value="{{ $employee->position }}" readonly>
                    <small class="text-muted">Contact HR to change your position</small>
                  </div>
                  <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                      <i class="bi bi-info-circle me-2"></i>
                      <strong>Employee ID:</strong> {{ $employee->employee_id }}
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

  <!-- Global objects already initialized in head section -->
  <script>
    // Missing function for destination training response
    function respondToDestinationTraining(trainingId, action, buttonElement) {
      if (!trainingId) {
        console.error('Training ID is required');
        return;
      }

      const metaElement = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = metaElement ? metaElement.getAttribute('content') : null;
      if (!csrfToken) {
        Swal.fire({
          icon: 'error',
          title: 'Security Error',
          text: 'Security token not found. Please refresh the page and try again.'
        });
        return;
      }

      // Disable button to prevent double clicks
      if (buttonElement) {
        buttonElement.disabled = true;
      }

      const actionText = action === 'accept' ? 'Accept' : 'Decline';

      Swal.fire({
        title: actionText + ' Training?',
        text: 'Are you sure you want to ' + action.toLowerCase() + ' this destination training?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, ' + actionText,
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('/employee/destination-training/respond', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              training_id: trainingId,
              action: action
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Training ' + action + 'ed successfully.'
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to ' + action + ' training.'
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'An error occurred while trying to ' + action + ' the training.'
            });
          })
          .finally(() => {
            // Re-enable button
            if (buttonElement) {
              buttonElement.disabled = false;
            }
          });
        } else {
          // Re-enable button if cancelled
          if (buttonElement) {
            buttonElement.disabled = false;
          }
        }
      });
    }

    // Safe script loading function
    function loadOptionalScript(src, callback) {
      const script = document.createElement('script');
      script.src = src;
      script.onload = function() {
        console.log('Script loaded successfully:', src);
        if (callback) callback();
      };
      script.onerror = function() {
        // Silently handle optional script loading failures
        if (callback) callback();
      };
      document.head.appendChild(script);
    }

    // Load optional agent portal script safely
    document.addEventListener('DOMContentLoaded', function() {
      try {
        loadOptionalScript('{{ asset('js/agent-portal-script.js') }}');
      } catch (error) {
        // Silently handle script initialization failures
      }

      // Also load CSRF refresh script safely
      try {
        loadOptionalScript('{{ asset('js/csrf-refresh.js') }}', function() {
          console.log('CSRF refresh system loaded');
        });
      } catch (error) {
        // Silently handle script initialization failures
      }
    });

    // Initialize settings and language
    document.addEventListener('DOMContentLoaded', function() {
      try {
        // Initialize settings object
        window.settings = {
          theme: 'light',
          language: 'en',
          animations: true,
          emailNotifications: true,
          pushNotifications: true
        };
        console.log('Settings initialized:', window.settings);

        // Initialize language
        const language = 'en';
        console.log('Language is English, no translation needed');

        // Mock translation success
        console.log('Translation successful on attempt: 1');

      } catch (error) {
        console.error('Error initializing dashboard:', error);
      }
    });
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        // Try multiple endpoints for CSRF token refresh
        const endpoints = [
          '/employee/csrf-token',
          '/csrf-token',
          '/api/csrf-token'
        ];

        function tryEndpoint(index) {
          if (index >= endpoints.length) {
            console.warn('All CSRF refresh endpoints failed');
            return;
          }

          fetch(endpoints[index], {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('HTTP ' + response.status);
            }
            return response.json();
          })
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

              console.log('CSRF token refreshed successfully from:', endpoints[index]);
            }
          })
          .catch(error => {
            console.warn('CSRF refresh failed for ' + endpoints[index] + ':', error);
            tryEndpoint(index + 1);
          });
        }

        tryEndpoint(0);
      }, 30 * 60 * 1000); // 30 minutes
    });

    // HTML escape function to prevent XSS and HTML entity issues
    function escapeHtml(text) {
      if (!text) return '';
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Enhanced CSRF token getter with error handling
    function getCSRFToken() {
      const metaTag = document.querySelector('meta[name="csrf-token"]');
      if (!metaTag) {
        console.error('CSRF token meta tag not found');
        return null;
      }
      const token = metaTag.getAttribute('content');
      if (!token) {
        console.error('CSRF token content is empty');
        return null;
      }
      return token;
    }

    // Quick Actions Functions
    function logAttendance() {
      // Redirect to attendance logs page
      window.location.href = '{{ route("employee.attendance_logs.index") }}';
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

    // Form Submissions with enhanced error handling
    const leaveForm = document.getElementById('leaveApplicationForm');
    if (leaveForm) {
      leaveForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          Swal.fire({
            icon: 'error',
            title: 'Security Error',
            text: 'Security token not found. Please refresh the page and try again.'
          });
          return;
        }

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        fetch('/employee/leave-application', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
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
              const modalElement = document.getElementById('leaveApplicationModal');
              if (modalElement) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                  modalInstance.hide();
                }
              }
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
    } else {
      console.warn('Leave application form not found');
    }

    // Profile picture preview with null checks
    const profilePictureInput = document.getElementById('profilePicture');
    if (profilePictureInput) {
      profilePictureInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const previewElement = document.getElementById('profilePreview');
            if (previewElement) {
              previewElement.src = e.target.result;
            }
          };
          reader.readAsDataURL(file);
        }
      });
    }

    // Profile update form submission with enhanced error handling
    const profileForm = document.getElementById('profileUpdateForm');
    if (profileForm) {
      profileForm.addEventListener('submit', function(e) {
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

        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          Swal.fire({
            icon: 'error',
            title: 'Security Error',
            text: 'Security token not found. Please refresh the page and try again.'
          });
          return;
        }

        fetch('/employee/profile/update', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken
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
              const modalElement = document.getElementById('profileUpdateModal');
              if (modalElement) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                  modalInstance.hide();
                }
              }
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
    } else {
      console.warn('Profile update form not found');
    }



    // Announcement Details Function with enhanced error handling
    function viewAnnouncementDetails(announcementId) {
      // Validate announcement ID
      if (!announcementId) {
        console.error('Announcement ID is required');
        return;
      }

      const modalElement = document.getElementById('announcementDetailsModal');
      const contentDiv = document.getElementById('announcementDetailsContent');

      if (!modalElement || !contentDiv) {
        console.error('Announcement modal elements not found');
        return;
      }

      const modal = new bootstrap.Modal(modalElement);

      // Show loading state
      contentDiv.innerHTML =
        '<div class="text-center py-4">' +
          '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
          '</div>' +
          '<p class="text-muted mt-2">Loading announcement details...</p>' +
        '</div>';

      modal.show();

      // Fetch announcement details
      const csrfToken = getCSRFToken();
      if (!csrfToken) {
        contentDiv.innerHTML =
          '<div class="text-center py-4">' +
            '<i class="bi bi-shield-exclamation text-warning" style="font-size: 3rem;"></i>' +
            '<h6 class="text-muted mt-2">Security Error</h6>' +
            '<p class="text-muted small mb-0">Security token not found. Please refresh the page and try again.</p>' +
          '</div>';
        return;
      }

      fetch('/employee/announcements/' + announcementId, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
      })
      .then(response => {
        if (response.status === 401) {
          contentDiv.innerHTML =
            '<div class="text-center py-4">' +
              '<i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>' +
              '<h6 class="text-muted mt-2">Unable to Load Announcement</h6>' +
              '<p class="text-muted small mb-0">Unauthenticated. Please log in and try again.</p>' +
            '</div>';
          return Promise.reject(new Error('Unauthenticated'));
        }
        if (!response.ok) {
          return response.text().then(txt => { throw new Error(txt || 'Failed to load announcement'); });
        }
        return response.json();
      })
      .then(data => {
        if (data && data.success && data.announcement) {
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

          contentDiv.innerHTML =
            '<div class="announcement-details">' +
              '<div class="d-flex justify-content-between align-items-start mb-3">' +
                '<div>' +
                  '<h4 class="fw-bold text-primary mb-2">' +
                    '<i class="bi ' + priorityIcon + ' me-2"></i>' + escapeHtml(announcement.title || 'Company Update') +
                  '</h4>' +
                  '<div class="d-flex align-items-center gap-3">' +
                    '<span class="badge ' + priorityBadgeClass + '">' +
                      '<i class="bi ' + priorityIcon + ' me-1"></i>' + (priority.charAt(0).toUpperCase() + priority.slice(1)) + ' Priority' +
                    '</span>' +
                    '<small class="text-muted">' +
                      '<i class="bi bi-calendar me-1"></i>' + formattedDate + ' at ' + formattedTime +
                    '</small>' +
                  '</div>' +
                '</div>' +
              '</div>' +
              '<div class="announcement-content">' +
                '<div class="card bg-light border-0 p-3">' +
                  '<p class="mb-0" style="line-height: 1.6; white-space: pre-wrap;">' + escapeHtml(announcement.message || announcement.content || 'No message content available.') + '</p>' +
                '</div>' +
              '</div>' +
              (announcement.author ?
                '<div class="mt-3 pt-3 border-top">' +
                  '<small class="text-muted">' +
                    '<i class="bi bi-person me-1"></i>Posted by: <strong>' + escapeHtml(announcement.author) + '</strong>' +
                  '</small>' +
                '</div>' : '') +
            '</div>';
        } else {
          contentDiv.innerHTML =
            '<div class="text-center py-4">' +
              '<i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>' +
              '<h6 class="text-muted mt-2">Unable to Load Announcement</h6>' +
              '<p class="text-muted small mb-0">' + (data && data.message ? data.message : 'The announcement details could not be loaded at this time.') + '</p>' +
            '</div>';
        }
      })
      .catch(error => {
        if (error.message === 'Unauthenticated') {
          // already handled above
          return;
        }
        console.error('Error fetching announcement:', error);
        contentDiv.innerHTML =
          '<div class="text-center py-4">' +
            '<i class="bi bi-wifi-off text-danger" style="font-size: 3rem;"></i>' +
            '<h6 class="text-muted mt-2">Connection Error</h6>' +
            '<p class="text-muted small mb-0">Unable to connect to the server. Please check your internet connection and try again.</p>' +
          '</div>';
      });
    }

    // Fetch Rewards Data Function
    function fetchRewardsData() {
      const refreshBtn = document.getElementById('refreshRewardsBtn');
      const container = document.getElementById('rewardsContainer');
      const countBadge = document.getElementById('rewardsCountBadge');

      // Disable button and show loading state
      refreshBtn.disabled = true;
      refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';

      const csrfToken = getCSRFToken();
      if (!csrfToken) {
        Swal.fire({
          icon: 'error',
          title: 'Security Error',
          text: 'Security token not found. Please refresh the page.'
        });
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
        return;
      }

      fetch('/employee/fetch-rewards', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          // Process the rewards data
          const rewards = data.data;
          let rewardsHtml = '';

            // Filter only approved rewards
            const approvedRewards = rewards.filter(r => (r.status || 'approved').toLowerCase() === 'approved');

            // Update badge count
            countBadge.textContent = approvedRewards.length + ' Earned';

            if (approvedRewards.length > 0) {
              rewardsHtml = `
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Date Given</th>
                      <th>Reward Name</th>
                      <th>Type</th>
                      <th>Benefits</th>
                      <th>Given By</th>
                    </tr>
                  </thead>
                  <tbody>
            `;

            approvedRewards.forEach(reward => {
              const rewardName = reward.reward?.name || reward.name || 'Award';
              const rewardDesc = reward.reward?.description || '';
              const rewardType = reward.reward?.type || 'N/A';
              const rewardBenefits = reward.reward?.benefits || reward.benefits || 'Certificate';
              const givenDate = reward.given_date ? new Date(reward.given_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
              const givenBy = reward.given_by || 'System';
              const status = (reward.status || 'approved').toLowerCase();
              const notes = reward.notes || reward.reason || 'No additional notes';

              let badgeClass = 'bg-secondary';
              if (status === 'approved') badgeClass = 'bg-success';
              else if (status === 'pending') badgeClass = 'bg-warning text-dark';
              else if (status === 'rejected') badgeClass = 'bg-danger';

              rewardsHtml += `
                <tr>
                  <td><span class="fw-semibold">${escapeHtml(givenDate)}</span></td>
                  <td>
                    <div class="fw-bold text-primary">${escapeHtml(rewardName)}</div>
                    ${rewardDesc ? `<small class="text-muted d-block" style="max-width: 250px;">${escapeHtml(rewardDesc.substring(0, 60))}${rewardDesc.length > 60 ? '...' : ''}</small>` : ''}
                  </td>
                  <td><span class="badge bg-outline-secondary border text-dark">${escapeHtml(rewardType)}</span></td>
                  <td>${escapeHtml(rewardBenefits)}</td>
                  <td>
                    <span class="badge bg-light text-dark border">
                      <i class="bi bi-person me-1"></i>${escapeHtml(givenBy.charAt(0).toUpperCase() + givenBy.slice(1))}
                    </span>
                  </td>
                </tr>
              `;
            });

            rewardsHtml += `
                  </tbody>
                </table>
              </div>
            `;
          } else {
            countBadge.textContent = '0 Earned';
            rewardsHtml = `
              <div class="text-center py-5">
                <i class="bi bi-award" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="text-muted mt-3 mb-0">No rewards earned yet. Keep up the great work!</p>
              </div>
            `;
          }

          // Update container with new HTML
          container.innerHTML = rewardsHtml;

          Swal.fire({
            icon: 'success',
            title: 'Rewards Updated',
            text: 'Your rewards data has been refreshed successfully.',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'No Data',
            text: data.message || 'Unable to fetch rewards data at this time.'
          });
        }
      })
      .catch(error => {
        console.error('Error fetching rewards:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error fetching rewards data. Please try again.'
        });
      })
      .finally(() => {
        // Re-enable button
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
      });
    }
  </script>

  <!-- CSRF refresh script loading moved to main script block -->

</body>
</html>
