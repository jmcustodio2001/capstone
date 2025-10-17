<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Employee Training Dashboard - Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <link rel="stylesheet" href="{{ asset('resources/css/responsive.css') }}">
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- jQuery for AJAX functionality -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .dashboard-card {
      transition: transform 0.3s ease;
    }
    
    .dashboard-card:hover {
      transform: translateY(-2px);
    }
    
    /* Training Card Styles */
    .training-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
      overflow: hidden;
      position: relative;
    }
    
    .training-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    
    .card-header-custom {
      background: rgba(13, 110, 253, 0.05);
      border-bottom: 1px solid rgba(13, 110, 253, 0.1);
      padding: 1rem 1.25rem;
    }
    
    .employee-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    .progress-container {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 0.75rem;
      margin: 0.5rem 0;
    }
    
    .progress-bar-custom {
      height: 8px;
      border-radius: 4px;
      background: #e9ecef;
      overflow: hidden;
      position: relative;
    }

    .progress-fill {
      height: 100%;
      border-radius: 4px;
      transition: width 0.8s ease;
      background: linear-gradient(90deg, #007bff, #28a745);
    }

    /* Enhanced Progress Bar Styling */
    .progress {
      background-color: #e9ecef !important;
      border-radius: 5px;
      overflow: hidden;
    }

    .progress .progress-bar {
      background: linear-gradient(90deg, #007bff 0%, #0056b3 50%, #28a745 100%) !important;
      transition: width 1s ease-in-out;
      border-radius: 5px;
    }

    /* Progress bar color variations based on percentage */
    .progress .progress-bar[aria-valuenow="0"] {
      background: #6c757d !important;
    }
    
    .status-badge-custom {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .course-badge {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 15px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .action-buttons {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
      margin-top: 1rem;
    }
    
    .btn-action {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      transition: all 0.3s ease;
      font-size: 1.1rem;
    }
    
    .btn-action:hover {
      transform: scale(1.1);
    }
    
    .info-row {
      display: flex;
      align-items: center;
      margin: 0.5rem 0;
      padding: 0.25rem 0;
    }
    
    .info-icon {
      width: 20px;
      margin-right: 0.5rem;
      color: #6c757d;
    }
    
    .readiness-score {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      font-weight: bold;
      text-align: center;
      min-width: 60px;
    }
    
    .expired-indicator {
      position: absolute;
      top: 15px;
      right: 15px;
      background: #dc3545;
      color: white;
      padding: 0.25rem 0.5rem;
      border-radius: 10px;
      font-size: 0.75rem;
      font-weight: bold;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }
    
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    
    @media (max-width: 767px) {
      .page-header h2 {
        font-size: 1.5rem;
      }
      
      .dashboard-logo img {
        width: 40px;
        height: 40px;
      }
      
      .card-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .training-card {
        margin: 0 0.5rem;
      }
      
      .action-buttons {
        flex-wrap: wrap;
      }
    }
    
    @media (max-width: 480px) {
      .card-grid {
        grid-template-columns: 1fr;
        margin: 0 -0.5rem;
      }
      
      .employee-avatar {
        width: 40px;
        height: 40px;
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
  <div class="d-flex justify-content-between align-items-center page-header flex-wrap">
    <div class="d-flex align-items-center mb-2 mb-md-0">
      <div class="dashboard-logo me-3 mobile-hidden">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="width: 50px; height: 50px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Employee Training Dashboard</h2>
        <p class="text-muted mb-0 d-none d-md-block">
          Welcome back,
          @if(Auth::check())
            {{ Auth::user()->name }}
          @else
            Admin
          @endif
          ! Track employee training progress here.
        </p>
      </div>
    </div>
    <nav aria-label="breadcrumb" class="mobile-hidden">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Employee Training Dashboard</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Table Section -->
<div class="card shadow-sm border-0 dashboard-card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
      <h4 class="fw-bold mb-2 mb-md-0">Employee Training Records</h4>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-sm btn-outline-primary d-flex align-items-center" onclick="exportTrainingDataWithConfirmation()">
          <i class="bi bi-download me-1"></i> <span class="d-none d-sm-inline">Export</span>
        </button>
      </div>
    </div>
  </div>
      <div class="card-body">
    <!-- Filter Section -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-6 col-lg-3">
        <label class="form-label small text-muted d-block d-md-none">Employee</label>
        <select class="form-select form-select-sm" id="filterEmployee">
          <option value="">All Employees</option>
          @foreach($employees as $employee)
            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <label class="form-label small text-muted d-block d-md-none">Course</label>
        <select class="form-select form-select-sm" id="filterCourse">
          <option value="">All Courses</option>
          @foreach($courses as $course)
              <option value="{{ $course->course_id }}">{{ $course->course_title }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <label class="form-label small text-muted d-block d-md-none">Status</label>
        <select class="form-select form-select-sm" id="filterStatus">
          <option value="">All Statuses</option>
          <option value="completed">Completed</option>
          <option value="in-progress">In Progress</option>
          <option value="not-started">Not Started</option>
        </select>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <button class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center" id="applyFilters">
          <i class="bi bi-funnel me-1"></i> Apply Filters
        </button>
      </div>
    </div>

    <!-- Training Cards Grid -->
    <div class="card-grid">
      @forelse($trainingRecords as $record)
        @php
          $firstName = $record->employee->first_name ?? 'Unknown';
          $lastName = $record->employee->last_name ?? 'Employee';
          $fullName = $firstName . ' ' . $lastName;
          $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));

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

        <div class="card training-card">
          <!-- Card Header with Employee Info -->
          <div class="card-header-custom">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <img src="{{ $profilePicUrl }}"
                     alt="{{ $firstName }} {{ $lastName }}"
                     class="employee-avatar me-3">
                <div>
                  <h6 class="mb-1 fw-bold">{{ $firstName }} {{ $lastName }}</h6>
                  <small class="text-muted">Employee ID: {{ $record->employee->employee_id ?? 'N/A' }}</small>
                </div>
              </div>
              @php
                // Check if we should hide direct input data (when coming from auto-assign)
                $hideInput = request()->has('hide_input');

                if ($hideInput) {
                  // Use simplified readiness calculation when coming from auto-assign
                  $readiness = 'N/A';
                } else {
                  // Use the same backend calculation as succession planning system
                  $employee = $record->employee;
                  $employeeId = $employee->employee_id ?? null;
                  
                  if ($employeeId) {
                    // Call the same controller method that succession planning uses
                    $controller = new \App\Http\Controllers\SuccessionReadinessRatingController();
                    $readiness = round($controller->calculateEmployeeReadinessScore($employeeId));
                  } else {
                    $readiness = 0;
                  }
                }
              @endphp
              <div class="readiness-score">
                {{ $readiness }}{{ is_numeric($readiness) ? '%' : '' }}
              </div>
            </div>
          </div>

          <!-- Card Body with Course Information -->
          <div class="card-body">
            @php
              // Priority system for course title: training_title > course->course_title > fallback
              $displayTitle = '';
              $displayDescription = '';
              
              if (!empty($record->training_title)) {
                // Use training_title from the record (highest priority)
                $displayTitle = $record->training_title;
                $displayDescription = $record->course->description ?? 'Training course: ' . $record->training_title;
              } elseif ($record->course && !empty($record->course->course_title)) {
                // Use course relationship title
                $displayTitle = $record->course->course_title;
                $displayDescription = $record->course->description ?? 'Course: ' . $record->course->course_title;
              } else {
                // Fallback - check if this is a training request
                $isTrainingRequest = str_starts_with($record->id, 'request_');
                if ($isTrainingRequest && !empty($record->course_title)) {
                  $displayTitle = $record->course_title;
                  $displayDescription = 'Training requested by employee';
                } else {
                  $displayTitle = 'Unknown Training';
                  $displayDescription = 'No course information available';
                }
              }
            @endphp

            <!-- Course Title and Badges -->
            <div class="mb-3">
              <h5 class="card-title mb-2">{{ $displayTitle }}</h5>
              
              @php
                $isDestinationCourse = false;
                $courseTitle = strtolower($displayTitle);
                $courseDescription = strtolower($displayDescription);

                $destinationKeywords = [
                  'destination', 'location', 'place', 'city', 'terminal', 'station',
                  'baesa', 'quezon', 'cubao', 'baguio', 'boracay', 'cebu', 'davao',
                  'manila', 'geography', 'route', 'travel', 'area knowledge'
                ];

                foreach($destinationKeywords as $keyword) {
                  if(strpos($courseTitle, $keyword) !== false || strpos($courseDescription, $keyword) !== false) {
                    $isDestinationCourse = true;
                    break;
                  }
                }
              @endphp

              <div class="d-flex flex-wrap gap-2">
                @if($isDestinationCourse)
                  <span class="course-badge bg-success" title="Exam and Quiz Available">
                    <i class="bi bi-mortarboard me-1"></i> Exam Enabled
                  </span>
                @else
                  <span class="course-badge bg-info" title="Announcement Only - No Exam/Quiz">
                    <i class="bi bi-megaphone me-1"></i> Announcement Only
                  </span>
                @endif
              </div>
              
              <p class="text-muted small mt-2 mb-0">{{ Str::limit($displayDescription, 80) }}</p>
            </div>

            @php
              // Check if this is an approved request record (from pseudo record)
              $isApprovedRequest = isset($record->source) && $record->source == 'Training Request (Approved)';
              
              // Initialize combinedProgress for all cases
              $combinedProgress = 0;
              
              if ($isApprovedRequest) {
                  // For approved requests, use the progress calculated in the controller
                  $displayProgress = $record->progress ?? 0;
                  $progressSource = 'approved_request';
              } else {
                  // Use exam progress instead of raw progress to match employee view
                  $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $record->course_id);
                  $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);
                  $progressSource = $combinedProgress > 0 ? 'exam' : 'training';
              }

              // Enhanced progress calculation with competency profile fallback
              if ($displayProgress == 0) {
                  $trainingTitle = $record->training_title ?? ($record->course->course_title ?? '');
                  
                  // Check competency profiles for proficiency levels
                  $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($trainingTitle) {
                      $q->where('competency_name', $trainingTitle)
                        ->orWhere('competency_name', 'LIKE', '%' . $trainingTitle . '%');
                  })->where('employee_id', $record->employee_id)->first();
                  
                  if ($competencyProfile && $competencyProfile->proficiency_level) {
                      $displayProgress = round(($competencyProfile->proficiency_level / 5) * 100);
                      $progressSource = 'competency_profile';
                  }
              }

              // Ensure progress is never negative and is properly formatted
              $displayProgress = max(0, min(100, (int)$displayProgress));
            @endphp

            <!-- Progress Section -->
            <div class="progress-container">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Training Progress</span>
                <span class="fw-bold text-primary">{{ $displayProgress }}%</span>
              </div>
              
              <!-- Bootstrap Progress Bar -->
              @php
                // Dynamic color based on progress percentage
                if ($displayProgress == 0) {
                  $progressColor = '#6c757d'; // Gray for 0%
                } elseif ($displayProgress < 40) {
                  $progressColor = 'linear-gradient(90deg, #dc3545, #fd7e14)'; // Red to Orange for low progress
                } elseif ($displayProgress < 70) {
                  $progressColor = 'linear-gradient(90deg, #fd7e14, #ffc107)'; // Orange to Yellow for medium progress
                } elseif ($displayProgress < 90) {
                  $progressColor = 'linear-gradient(90deg, #ffc107, #20c997)'; // Yellow to Teal for good progress
                } else {
                  $progressColor = 'linear-gradient(90deg, #20c997, #28a745)'; // Teal to Green for excellent progress
                }
              @endphp
              <div class="progress mb-2" style="height: 12px; background-color: #e9ecef; border-radius: 6px;">
                <div class="progress-bar" 
                     role="progressbar" 
                     style="width: {{ $displayProgress }}%; background: {{ $progressColor }} !important; border-radius: 6px;" 
                     aria-valuenow="{{ $displayProgress }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
              </div>
              
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex flex-wrap gap-1">
                  @if($progressSource === 'manual')
                    <small class="badge bg-warning" title="Manual proficiency level">Manual</small>
                  @elseif($progressSource === 'destination' || $progressSource === 'destination_training')
                    <small class="badge bg-success" title="From destination knowledge training">Destination</small>
                  @elseif($progressSource === 'training')
                    <small class="badge bg-primary" title="From employee training dashboard">Training</small>
                  @elseif($progressSource === 'competency_profile')
                    <small class="badge bg-info" title="From competency profile">Competency</small>
                  @elseif($progressSource === 'exam')
                    <small class="badge bg-success" title="From exam results">Exam</small>
                  @elseif($progressSource === 'approved_request')
                    <small class="badge bg-warning" title="From approved training request">Request</small>
                  @else
                    <small class="badge bg-secondary" title="No data found">No Data</small>
                  @endif
                </div>
                
                @if($combinedProgress > 0)
                  @php
                    $breakdown = \App\Models\ExamAttempt::getScoreBreakdown($record->employee_id, $record->course_id);
                  @endphp
                  @if($breakdown['exam_score'] > 0)
                    <small class="text-success" title="Exam Score: {{ $breakdown['exam_score'] }}%">
                      <i class="bi bi-mortarboard me-1"></i>{{ $breakdown['exam_score'] }}%
                    </small>
                  @endif
                @endif
              </div>
            </div>

            @php
              // Get expired date from multiple sources - check both systems
              $finalExpiredDate = null;

              // First check: Employee Training Dashboard record itself
              if (isset($record->expired_date) && $record->expired_date) {
                $finalExpiredDate = $record->expired_date;
              }

              // Second check: Course Management table
              if (!$finalExpiredDate && $record->course && isset($record->course->expired_date) && $record->course->expired_date) {
                $finalExpiredDate = $record->course->expired_date;
              }

              // Determine accurate status based on progress and expiry date
              $currentProgress = $displayProgress;

              // Check if expired using the calculated expired date
              $isExpired = false;
              if ($finalExpiredDate) {
                $expiredDate = \Carbon\Carbon::parse($finalExpiredDate);
                $isExpired = \Carbon\Carbon::now()->gt($expiredDate);
              }

              // Determine final status
              if ($isExpired && $currentProgress < 100) {
                $statusBadgeClass = 'bg-danger';
                $statusTextClass = 'text-dark';
                $statusText = 'Expired';
              } elseif ($currentProgress >= 100) {
                $statusBadgeClass = 'bg-success';
                $statusTextClass = 'text-dark';
                $statusText = 'Completed';
              } elseif ($currentProgress > 0) {
                $statusBadgeClass = 'bg-primary';
                $statusTextClass = 'text-dark';
                $statusText = 'In Progress';
              } else {
                $statusBadgeClass = 'bg-secondary';
                $statusTextClass = 'text-dark';
                $statusText = 'Not Started';
              }
            @endphp

            <!-- Status and Dates Information -->
            <div class="row g-2 mb-3">
              <div class="col-12 col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <i class="bi bi-flag-fill me-2 text-muted"></i>
                  <div class="flex-grow-1">
                    <small class="text-muted d-block mb-1">Status</small>
                    <span class="badge {{ $statusBadgeClass }} {{ $statusTextClass }} px-2 py-1">{{ $statusText }}</span>
                  </div>
                </div>
              </div>
              
              <div class="col-12 col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <i class="bi bi-calendar-x-fill me-2 text-muted"></i>
                  <div class="flex-grow-1">
                    <small class="text-muted d-block mb-1">Expires</small>
                    @if($finalExpiredDate)
                      @php
                        $expiredDate = \Carbon\Carbon::parse($finalExpiredDate);
                        $now = \Carbon\Carbon::now();
                        $daysUntilExpiry = $now->diffInDays($expiredDate, false);

                        if ($daysUntilExpiry < 0) {
                          $dateClass = 'text-danger fw-bold';
                          $statusBadge = 'bg-danger text-white';
                          $statusLabel = 'EXPIRED';
                        } elseif ($daysUntilExpiry <= 7) {
                          $dateClass = 'text-warning fw-bold';
                          $statusBadge = 'bg-warning text-dark';
                          $statusLabel = 'URGENT';
                        } elseif ($daysUntilExpiry <= 30) {
                          $dateClass = 'text-info fw-bold';
                          $statusBadge = 'bg-info text-white';
                          $statusLabel = 'SOON';
                        } else {
                          $dateClass = 'text-success fw-bold';
                          $statusBadge = 'bg-success text-white';
                          $statusLabel = 'ACTIVE';
                        }
                      @endphp
                      <div class="d-flex align-items-center flex-wrap">
                        <span class="{{ $dateClass }} me-1">{{ $expiredDate->format('M d, Y') }}</span>
                        <small class="badge {{ $statusBadge }}">{{ $statusLabel }}</small>
                      </div>
                    @else
                      <span class="text-muted">Not Set</span>
                    @endif
                  </div>
                </div>
              </div>
              
              <div class="col-12 col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <i class="bi bi-clock-history me-2 text-muted"></i>
                  <div class="flex-grow-1">
                    <small class="text-muted d-block mb-1">Last Accessed</small>
                    <span class="fw-semibold">
                      @if(isset($record->last_accessed) && $record->last_accessed)
                        {{ \Carbon\Carbon::parse($record->last_accessed)->format('M d, Y') }}
                      @else
                        <span class="text-muted">Never</span>
                      @endif
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            @php
              $isTrainingRequest = str_starts_with($record->id, 'request_');
            @endphp
            
            @if(!$isTrainingRequest)
              <div class="action-buttons">
                <button class="btn btn-outline-primary btn-action" 
                        onclick="viewTrainingDetails('{{ $record->id }}', '{{ ($record->employee->first_name ?? 'Unknown') }} {{ ($record->employee->last_name ?? 'Employee') }}', '{{ $displayTitle }}', '{{ $displayProgress }}', '{{ $statusText }}', '{{ $finalExpiredDate ? \Carbon\Carbon::parse($finalExpiredDate)->format('Y-m-d') : 'Not Set' }}', '{{ $record->last_accessed ? \Carbon\Carbon::parse($record->last_accessed)->format('Y-m-d') : 'Never' }}')"
                        data-bs-toggle="tooltip" title="View Details">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-outline-success btn-action certificate-btn" 
                        onclick="alert('Button clicked!'); window.open('/admin/training-record-certificate-tracking?employee_id={{ $record->employee->employee_id ?? '' }}&employee_name={{ urlencode(($record->employee->first_name ?? 'Unknown') . ' ' . ($record->employee->last_name ?? 'Employee')) }}', '_blank');"
                        data-bs-toggle="tooltip" title="View Certificates"
                        style="display: none;">
                  <i class="bi bi-award"></i>
                </button>
              </div>
            @else
              <div class="text-center">
                <span class="badge bg-info text-dark fs-6">
                  <i class="bi bi-file-earmark-text me-1"></i> Training Request
                </span>
                <small class="text-muted d-block mt-1">Manage in Training Requests</small>
              </div>
            @endif

            <!-- Expired Indicator -->
            @if($isExpired && $currentProgress < 100)
              <div class="expired-indicator">
                <i class="bi bi-exclamation-triangle me-1"></i>EXPIRED
              </div>
            @endif
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="card text-center py-5">
            <div class="card-body">
              <i class="bi bi-inbox display-1 text-muted mb-3"></i>
              <h5 class="text-muted">No Training Records Found</h5>
              <p class="text-muted">There are no training records to display at the moment.</p>
            </div>
          </div>
        </div>
      @endforelse
    </div>
        <!-- Pagination -->
        @if(method_exists($trainingRecords, 'hasPages') && $trainingRecords->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">
            Showing <span class="fw-semibold">{{ $trainingRecords->firstItem() }}</span> to <span class="fw-semibold">{{ $trainingRecords->lastItem() }}</span> of <span class="fw-semibold">{{ $trainingRecords->total() }}</span> entries
          </div>
          <nav>
            {{ $trainingRecords->links() }}
          </nav>
        </div>
        @endif
      </div>
    </div>
  </main>


  <!-- SweetAlert Integration - No modal needed -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // Initialize tooltips and basic functionality
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Animate Bootstrap progress bars
    document.querySelectorAll('.progress-bar').forEach((bar, index) => {
      const width = bar.style.width;
      bar.style.width = '0%';
      setTimeout(() => {
        bar.style.transition = 'width 1s ease-in-out';
        bar.style.width = width;
      }, 200 + (index * 100)); // Stagger animation
    });

    // Animate old progress bars (fallback)
    document.querySelectorAll('progress').forEach(bar => {
      const value = bar.value;
      bar.value = 0;
      setTimeout(() => {
        bar.value = value;
      }, 100);
    });

    // Filter functionality
    document.getElementById('applyFilters').addEventListener('click', function() {
      const employeeFilter = document.getElementById('filterEmployee').value;
      const courseFilter = document.getElementById('filterCourse').value;
      const statusFilter = document.getElementById('filterStatus').value;

      Swal.fire({
        title: 'Filters Applied',
        html: `<strong>Employee:</strong> ${employeeFilter || 'All'}<br>
               <strong>Course:</strong> ${courseFilter || 'All'}<br>
               <strong>Status:</strong> ${statusFilter || 'All'}`,
        icon: 'info',
        confirmButtonText: 'OK'
      });
    });
  });

  // Password verification function
  async function verifyAdminPassword(password) {
    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      const response = await fetch('/admin/verify-password', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ password: password })
      });
      
      const data = await response.json();
      console.log('Password verification response:', data); // Debug log
      
      // AdminController returns 'success' field, not 'valid'
      if (response.ok && data.success === true) {
        return true;
      } else {
        console.log('Password verification failed:', data.message || 'Invalid password');
        return false;
      }
    } catch (error) {
      console.error('Password verification error:', error);
      return false;
    }
  }

  // View Training Details
  function viewTrainingDetails(id, employeeName, courseName, progress, status, expiredDate, lastAccessed) {
    Swal.fire({
      title: '<i class="bi bi-eye text-primary"></i> Training Details',
      html: `
        <div class="text-start">
          <div class="row mb-3">
            <div class="col-sm-4"><strong>Employee:</strong></div>
            <div class="col-sm-8">${employeeName}</div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4"><strong>Course:</strong></div>
            <div class="col-sm-8">${courseName}</div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4"><strong>Progress:</strong></div>
            <div class="col-sm-8">
              <div class="progress" style="height: 20px;">
                <div class="progress-bar" role="progressbar" style="width: ${progress}%">${progress}%</div>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4"><strong>Status:</strong></div>
            <div class="col-sm-8"><span class="badge bg-primary">${status}</span></div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4"><strong>Expired Date:</strong></div>
            <div class="col-sm-8">${expiredDate}</div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4"><strong>Last Accessed:</strong></div>
            <div class="col-sm-8">${lastAccessed}</div>
          </div>
        </div>
      `,
      width: '600px',
      showConfirmButton: true,
      confirmButtonText: '<i class="bi bi-check"></i> Close',
      confirmButtonColor: '#0d6efd'
    });
  }

  // Edit Training with Password Confirmation
  function editTrainingWithConfirmation(id, employeeId, courseId, progress, lastAccessed) {
    Swal.fire({
      title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Security Notice:</strong> You are about to edit a training record. 
            Please enter your admin password to verify your identity.
          </div>
          <label for="admin-password" class="form-label">Admin Password:</label>
          <input type="password" id="admin-password" class="form-control" placeholder="Enter your admin password" minlength="3">
          <small class="text-muted">Password must be at least 3 characters long.</small>
        </div>
      `,
      width: '500px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-check"></i> Verify & Continue',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#ffc107',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const password = document.getElementById('admin-password').value;
        if (!password) {
          Swal.showValidationMessage('Please enter your admin password');
          return false;
        }
        if (password.length < 3) {
          Swal.showValidationMessage('Password must be at least 3 characters long');
          return false;
        }
        return password;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        verifyAdminPassword(result.value).then(isValid => {
          if (isValid) {
            showEditTrainingForm(id, employeeId, courseId, progress, lastAccessed, result.value);
          } else {
            Swal.fire({
              title: 'Invalid Password',
              text: 'The password you entered is incorrect. Please enter your correct admin password.',
              icon: 'error',
              confirmButtonText: 'Try Again'
            });
          }
        });
      }
    });
  }

  // Show Edit Training Form
  function showEditTrainingForm(id, employeeId, courseId, progress, lastAccessed, password) {
    const employees = @json($employees);
    const courses = @json($courses);

    let employeeOptions = '';
    employees.forEach(emp => {
      const selected = emp.employee_id === employeeId ? 'selected' : '';
      employeeOptions += `<option value="${emp.employee_id}" ${selected}>${emp.first_name} ${emp.last_name}</option>`;
    });

    let courseOptions = '';
    courses.forEach(course => {
      const selected = course.course_id == courseId ? 'selected' : '';
      courseOptions += `<option value="${course.course_id}" ${selected}>${course.course_title}</option>`;
    });

    let formattedLastAccessed = '';
    if (lastAccessed && lastAccessed !== 'null' && lastAccessed !== 'Never') {
      const date = new Date(lastAccessed);
      if (!isNaN(date.getTime())) {
        formattedLastAccessed = date.toISOString().slice(0, 16);
      }
    }

    Swal.fire({
      title: '<i class="bi bi-pencil text-warning"></i> Edit Training Record',
      html: `
        <form id="editTrainingForm" class="text-start">
          <input type="hidden" id="edit-password" value="${password}">
          <div class="mb-3">
            <label for="edit-employee" class="form-label">Employee:</label>
            <select id="edit-employee" class="form-select" required>
              ${employeeOptions}
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-course" class="form-label">Course:</label>
            <select id="edit-course" class="form-select" required>
              ${courseOptions}
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-progress" class="form-label">Progress (%):</label>
            <input type="number" id="edit-progress" class="form-control" min="0" max="100" value="${progress || 0}" required>
          </div>
          <div class="mb-3">
            <label for="edit-last-accessed" class="form-label">Last Accessed:</label>
            <input type="datetime-local" id="edit-last-accessed" class="form-control" value="${formattedLastAccessed}">
          </div>
          <div class="mb-3">
            <label for="edit-training-date" class="form-label">Training Date:</label>
            <input type="date" id="edit-training-date" class="form-control" required>
          </div>
        </form>
      `,
      width: '600px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-check"></i> Save Changes',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const employee = document.getElementById('edit-employee').value;
        const course = document.getElementById('edit-course').value;
        const progress = document.getElementById('edit-progress').value;
        const trainingDate = document.getElementById('edit-training-date').value;

        if (!employee || !course || !progress || !trainingDate) {
          Swal.showValidationMessage('Please fill in all required fields');
          return false;
        }

        if (progress < 0 || progress > 100) {
          Swal.showValidationMessage('Progress must be between 0 and 100');
          return false;
        }

        return {
          employee_id: employee,
          course_id: course,
          progress: progress,
          last_accessed: document.getElementById('edit-last-accessed').value,
          training_date: trainingDate,
          password: document.getElementById('edit-password').value
        };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        submitEditTrainingForm(id, result.value);
      }
    });
  }

  // Submit Edit Training Form
  function submitEditTrainingForm(id, formData) {
    Swal.fire({
      title: 'Processing...',
      text: 'Updating training record...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(`{{ url('/admin/employee-trainings-dashboard') }}/${id}`, {
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          title: 'Success!',
          text: data.message || 'Training record updated successfully!',
          icon: 'success',
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false
        }).then(() => {
          window.location.reload();
        });
      } else {
        throw new Error(data.message || 'Failed to update training record');
      }
    })
    .catch(error => {
      console.error('Edit error:', error);
      Swal.fire({
        title: 'Error!',
        text: 'Failed to update training record: ' + error.message,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    });
  }

  // Delete Training with Password Confirmation
  function deleteTrainingWithConfirmation(id, employeeName, courseName) {
    if (id.startsWith('request_')) {
      Swal.fire({
        title: 'Cannot Delete',
        text: 'Cannot delete training requests from this dashboard. Please manage training requests from the Training Requests section.',
        icon: 'warning',
        confirmButtonText: 'OK'
      });
      return;
    }

    Swal.fire({
      title: '<i class="bi bi-exclamation-triangle text-danger"></i> Delete Training Record',
      html: `
        <div class="text-start">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Warning:</strong> You are about to permanently delete this training record. This action cannot be undone.
          </div>
          <div class="mb-3">
            <strong>Employee:</strong> ${employeeName}<br>
            <strong>Course:</strong> ${courseName}
          </div>
          <div class="alert alert-warning">
            <i class="bi bi-shield-lock"></i>
            <strong>Security Notice:</strong> Please enter your admin password to verify your identity.
          </div>
          <label for="delete-password" class="form-label">Admin Password:</label>
          <input type="password" id="delete-password" class="form-control" placeholder="Enter your admin password" minlength="3">
          <small class="text-muted">Password must be at least 3 characters long.</small>
        </div>
      `,
      width: '500px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-trash"></i> Delete Record',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const password = document.getElementById('delete-password').value;
        if (!password) {
          Swal.showValidationMessage('Please enter your admin password');
          return false;
        }
        if (password.length < 3) {
          Swal.showValidationMessage('Password must be at least 3 characters long');
          return false;
        }
        return password;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        verifyAdminPassword(result.value).then(isValid => {
          if (isValid) {
            submitDeleteTraining(id, result.value);
          } else {
            Swal.fire({
              title: 'Invalid Password',
              text: 'The password you entered is incorrect. Please enter your correct admin password.',
              icon: 'error',
              confirmButtonText: 'Try Again'
            });
          }
        });
      }
    });
  }

  // Submit Delete Training
  function submitDeleteTraining(id, password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Deleting training record...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(`{{ url('/admin/employee-trainings-dashboard') }}/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          title: 'Deleted!',
          text: data.message || 'Training record deleted successfully!',
          icon: 'success',
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false
        }).then(() => {
          window.location.reload();
        });
      } else {
        throw new Error(data.message || 'Failed to delete training record');
      }
    })
    .catch(error => {
      console.error('Delete error:', error);
      Swal.fire({
        title: 'Error!',
        text: 'Failed to delete training record: ' + error.message,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    });
  }

  // Create Missing Entries with Password Confirmation
  function createMissingEntriesWithConfirmation() {
    Swal.fire({
      title: '<i class="bi bi-shield-lock text-success"></i> Security Verification Required',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Action:</strong> Create missing training entries for all employees.
          </div>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Security Notice:</strong> Please enter your admin password to verify your identity.
          </div>
          <label for="create-password" class="form-label">Admin Password:</label>
          <input type="password" id="create-password" class="form-control" placeholder="Enter your admin password" minlength="3">
          <small class="text-muted">Password must be at least 3 characters long.</small>
        </div>
      `,
      width: '500px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Create Entries',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const password = document.getElementById('create-password').value;
        if (!password) {
          Swal.showValidationMessage('Please enter your admin password');
          return false;
        }
        if (password.length < 3) {
          Swal.showValidationMessage('Password must be at least 3 characters long');
          return false;
        }
        return password;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        verifyAdminPassword(result.value).then(isValid => {
          if (isValid) {
            submitCreateMissingEntries(result.value);
          } else {
            Swal.fire({
              title: 'Invalid Password',
              text: 'The password you entered is incorrect. Please enter your correct admin password.',
              icon: 'error',
              confirmButtonText: 'Try Again'
            });
          }
        });
      }
    });
  }

  // Submit Create Missing Entries
  function submitCreateMissingEntries(password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Creating missing training entries...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Use GET method with password as query parameter since route only supports GET
    console.log('Making GET request to create missing entries with password verification');
    console.log('Request URL:', `/admin/create-missing-training-entries?password=${encodeURIComponent(password)}&_t=${Date.now()}`);
    console.log('Request method: GET');
    
    fetch(`/admin/create-missing-training-entries?password=${encodeURIComponent(password)}&_t=${Date.now()}`, {
      method: 'GET',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Cache-Control': 'no-cache'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          title: 'Success!',
          text: data.message || 'Missing entries created successfully!',
          icon: 'success',
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false
        }).then(() => {
          window.location.reload();
        });
      } else {
        throw new Error(data.message || 'Failed to create missing entries');
      }
    })
    .catch(error => {
      console.error('Create missing entries error:', error);
      console.error('Error details:', error.stack);
      Swal.fire({
        title: 'Error!',
        text: 'Failed to create missing entries: ' + error.message,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    });
  }

  // Fix Expired Dates with Password Confirmation
  function fixExpiredDatesWithConfirmation() {
    Swal.fire({
      title: '<i class="bi bi-shield-lock text-info"></i> Security Verification Required',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-info">
            <i class="bi bi-calendar-check"></i>
            <strong>Action:</strong> Fix expired dates for all training records.
          </div>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Security Notice:</strong> Please enter your admin password to verify your identity.
          </div>
          <label for="fix-password" class="form-label">Admin Password:</label>
          <input type="password" id="fix-password" class="form-control" placeholder="Enter your admin password" minlength="3">
          <small class="text-muted">Password must be at least 3 characters long.</small>
        </div>
      `,
      width: '500px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-calendar-check"></i> Fix Dates',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#0dcaf0',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const password = document.getElementById('fix-password').value;
        if (!password) {
          Swal.showValidationMessage('Please enter your admin password');
          return false;
        }
        if (password.length < 3) {
          Swal.showValidationMessage('Password must be at least 3 characters long');
          return false;
        }
        return password;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        verifyAdminPassword(result.value).then(isValid => {
          if (isValid) {
            submitFixExpiredDates(result.value);
          } else {
            Swal.fire({
              title: 'Invalid Password',
              text: 'The password you entered is incorrect. Please enter your correct admin password.',
              icon: 'error',
              confirmButtonText: 'Try Again'
            });
          }
        });
      }
    });
  }

  // Submit Fix Expired Dates
  function submitFixExpiredDates(password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Fixing expired dates...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('{{ route("admin.employee_trainings_dashboard.fix_expired_dates") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          title: 'Success!',
          text: data.message || 'Expired dates fixed successfully!',
          icon: 'success',
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false
        }).then(() => {
          window.location.reload();
        });
      } else {
        throw new Error(data.message || 'Failed to fix expired dates');
      }
    })
    .catch(error => {
      console.error('Fix expired dates error:', error);
      Swal.fire({
        title: 'Error!',
        text: 'Failed to fix expired dates: ' + error.message,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    });
  }

  // Export Training Data with Password Confirmation
  function exportTrainingDataWithConfirmation() {
    Swal.fire({
      title: '<i class="bi bi-shield-lock text-primary"></i> Security Verification Required',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-info">
            <i class="bi bi-download"></i>
            <strong>Action:</strong> Export training data to Excel/CSV format.
          </div>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Security Notice:</strong> Please enter your admin password to verify your identity and proceed with data export.
          </div>
          <div class="mb-3">
            <label for="export-format" class="form-label">Export Format:</label>
            <select id="export-format" class="form-select">
              <option value="excel">Excel (.xlsx)</option>
              <option value="csv">CSV (.csv)</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="export-password" class="form-label">Admin Password:</label>
            <input type="password" id="export-password" class="form-control" placeholder="Enter your admin password" minlength="3">
            <small class="text-muted">Password must be at least 3 characters long.</small>
          </div>
        </div>
      `,
      width: '500px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-download"></i> Export Data',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#0d6efd',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const password = document.getElementById('export-password').value;
        const format = document.getElementById('export-format').value;
        
        if (!password) {
          Swal.showValidationMessage('Please enter your admin password');
          return false;
        }
        if (password.length < 3) {
          Swal.showValidationMessage('Password must be at least 3 characters long');
          return false;
        }
        return { password: password, format: format };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        verifyAdminPassword(result.value.password).then(isValid => {
          if (isValid) {
            submitExportTrainingData(result.value.password, result.value.format);
          } else {
            Swal.fire({
              title: 'Invalid Password',
              text: 'The password you entered is incorrect. Please enter your correct admin password.',
              icon: 'error',
              confirmButtonText: 'Try Again'
            });
          }
        });
      }
    });
  }

  // Submit Export Training Data
  function submitExportTrainingData(password, format) {
    Swal.fire({
      title: 'Processing...',
      text: 'Exporting training data...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Create a form to submit the export request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/employee-trainings-dashboard/export';
    form.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // Add password
    const passwordInput = document.createElement('input');
    passwordInput.type = 'hidden';
    passwordInput.name = 'password';
    passwordInput.value = password;
    form.appendChild(passwordInput);

    // Add format
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = format;
    form.appendChild(formatInput);

    // Add current filters if any
    const employeeFilter = document.getElementById('filterEmployee')?.value;
    const courseFilter = document.getElementById('filterCourse')?.value;
    const statusFilter = document.getElementById('filterStatus')?.value;

    if (employeeFilter) {
      const empInput = document.createElement('input');
      empInput.type = 'hidden';
      empInput.name = 'employee_filter';
      empInput.value = employeeFilter;
      form.appendChild(empInput);
    }

    if (courseFilter) {
      const courseInput = document.createElement('input');
      courseInput.type = 'hidden';
      courseInput.name = 'course_filter';
      courseInput.value = courseFilter;
      form.appendChild(courseInput);
    }

    if (statusFilter) {
      const statusInput = document.createElement('input');
      statusInput.type = 'hidden';
      statusInput.name = 'status_filter';
      statusInput.value = statusFilter;
      form.appendChild(statusInput);
    }

    document.body.appendChild(form);

    // Submit form to trigger download
    form.submit();

    // Clean up
    document.body.removeChild(form);

    // Show success message after a short delay
    setTimeout(() => {
      Swal.fire({
        title: 'Export Started!',
        text: `Your ${format.toUpperCase()} file download should begin shortly.`,
        icon: 'success',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      });
    }, 1000);
  }

  // Remove Unknown Courses with Password Confirmation
  function removeUnknownCoursesWithConfirmation() {
    Swal.fire({
      title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
      html: `
        <div class="text-start mb-3">
          <div class="alert alert-warning">
            <i class="bi bi-trash"></i>
            <strong>Action:</strong> Remove training records with "Unknown Course" or invalid course associations.
          </div>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Warning:</strong> This will permanently delete training records that have:
            <ul class="mt-2 mb-0">
              <li>Training titles containing "Unknown Course"</li>
              <li>Empty or null training titles</li>
              <li>Course IDs that don't exist in the course management table</li>
            </ul>
          </div>
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Security Notice:</strong> Please enter your admin password to verify your identity and proceed with cleanup.
          </div>
          <div class="mb-3">
            <label for="cleanup-password" class="form-label">Admin Password:</label>
            <input type="password" id="cleanup-password" class="form-control" placeholder="Enter your admin password" minlength="3">
            <small class="text-muted">Password must be at least 3 characters long.</small>
          </div>
        </div>
      `,
      width: '600px',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-trash"></i> Remove Unknown Courses',
      cancelButtonText: '<i class="bi bi-x"></i> Cancel',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const password = document.getElementById('cleanup-password').value;
        
        if (!password) {
          Swal.showValidationMessage('Please enter your admin password');
          return false;
        }
        if (password.length < 3) {
          Swal.showValidationMessage('Password must be at least 3 characters long');
          return false;
        }
        return password;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        verifyAdminPassword(result.value).then(isValid => {
          if (isValid) {
            submitRemoveUnknownCourses(result.value);
          } else {
            Swal.fire({
              title: 'Invalid Password',
              text: 'The password you entered is incorrect. Please enter your correct admin password.',
              icon: 'error',
              confirmButtonText: 'Try Again'
            });
          }
        });
      }
    });
  }

  // Submit Remove Unknown Courses
  function submitRemoveUnknownCourses(password) {
    Swal.fire({
      title: 'Processing...',
      text: 'Removing unknown course records...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Use GET method with password as query parameter
    fetch(`/admin/employee-trainings-dashboard/remove-unknown-courses?password=${encodeURIComponent(password)}&_t=${Date.now()}`, {
      method: 'GET',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Cache-Control': 'no-cache'
      }
    })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response OK:', response.ok);
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.success) {
        Swal.fire({
          title: 'Success!',
          text: data.message || 'Unknown course records removed successfully!',
          icon: 'success',
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false
        }).then(() => {
          window.location.reload();
        });
      } else {
        throw new Error(data.message || 'Failed to remove unknown course records');
      }
    })
    .catch(error => {
      console.error('Remove unknown courses error:', error);
      console.error('Error details:', error.stack);
      Swal.fire({
        title: 'Error!',
        text: 'Failed to remove unknown course records: ' + error.message,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    });
  }
  </script>

  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('resources/js/responsive.js') }}"></script>
</body>
</html>
