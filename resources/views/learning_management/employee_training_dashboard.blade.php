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
            <h2 class="fw-bold mb-1">Employee Training Dashboard</h2>
            <p class="text-muted mb-0">
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
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Training Dashboard</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Training Records</h4>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-success btn-sm d-flex align-items-center" id="createMissingEntriesBtn">
            <i class="bi bi-arrow-repeat me-1"></i> Create Missing Entries
          </button>
          <button class="btn btn-info btn-sm d-flex align-items-center" id="fixExpiredDatesBtn">
            <i class="bi bi-calendar-check me-1"></i> Fix Expired Dates
          </button>
          <button class="btn btn-sm btn-outline-primary d-flex align-items-center">
            <i class="bi bi-download me-1"></i> Export
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Filter Section -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterEmployee">
              <option value="">All Employees</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterCourse">
              <option value="">All Courses</option>
              @foreach($courses as $course)
                  <option value="{{ $course->course_id }}">{{ $course->course_title }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterStatus">
              <option value="">All Statuses</option>
              <option value="completed">Completed</option>
              <option value="in-progress">In Progress</option>
              <option value="not-started">Not Started</option>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center" id="applyFilters">
              <i class="bi bi-funnel me-1"></i> Apply Filters
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Readiness Score</th>
                <th class="fw-bold">Course</th>
                <th class="fw-bold">Progress</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Expired Date</th>
                <th class="fw-bold">Last Accessed</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($trainingRecords as $record)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
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

                      <img src="{{ $profilePicUrl }}"
                           alt="{{ $firstName }} {{ $lastName }}"
                           class="rounded-circle"
                           style="width: 40px; height: 40px; object-fit: cover;">
                    </div>
                    <span class="fw-semibold">
                      {{ $firstName }} {{ $lastName }}
                    </span>
                  </div>
                </td>
                <td>
                  @php
                    // Check if we should hide direct input data (when coming from auto-assign)
                    $hideInput = request()->has('hide_input');

                    if ($hideInput) {
                      // Use simplified readiness calculation when coming from auto-assign
                      $readiness = 'N/A';
                    } else {
                      // Calculate actual readiness score using unified algorithm
                      $employee = $record->employee;

                      // Get competency data - handle null employee
                      $employeeId = $employee->employee_id ?? null;
                      $competencyProfiles = $employeeId ? \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)->get() : collect();

                      // Calculate competency metrics
                      $avgProficiency = 0;
                      $leadershipCount = 0;
                      $totalCompetencies = $competencyProfiles->count();

                    if ($totalCompetencies > 0) {
                        $totalProficiency = 0;
                        foreach ($competencyProfiles as $profile) {
                            $totalProficiency += $profile->proficiency_level;

                            // Check if it's a leadership competency
                            $competencyName = strtolower($profile->competency->competency_name ?? '');
                            if (strpos($competencyName, 'leadership') !== false ||
                                strpos($competencyName, 'management') !== false ||
                                strpos($competencyName, 'supervisor') !== false) {
                                $leadershipCount++;
                            }
                        }
                        $avgProficiency = $totalProficiency / $totalCompetencies; // Keep as level (1-5)
                    }

                    // Get training data - FRESH query to ensure latest progress is included
                    $trainingRecords = $employeeId ? \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)->get()->fresh() : collect();
                    $totalCourses = $trainingRecords->count();
                    $completedCourses = $trainingRecords->where('progress', '>=', 100)->count();
                    $totalProgress = $trainingRecords->sum('progress');
                    $avgTrainingProgress = $totalCourses > 0 ? $totalProgress / $totalCourses : 0;
                    $completionRate = $totalCourses > 0 ? ($completedCourses / $totalCourses) * 100 : 0;


                    // Fixed readiness calculation: 70% Employee Competency Profile + 30% Training Records

                    // Calculate competency profile component (70% weight) - Additive approach that rewards growth
                    $competencyProfileScore = 0;
                    if ($totalCompetencies > 0) {
                        // NEW: Additive proficiency scoring - each competency contributes based on its individual level
                        // This ensures adding new competencies ALWAYS increases the score
                        $totalProficiencyPoints = 0;
                        foreach ($competencyProfiles as $profile) {
                            // Each competency contributes points based on its proficiency level (1-5)
                            // Level 5 = 7 points, Level 4 = 5.6 points, Level 3 = 4.2 points, Level 2 = 2.8 points, Level 1 = 1.4 points
                            $totalProficiencyPoints += ($profile->proficiency_level * 1.4);
                        }
                        // Cap proficiency score at 35% but scale based on total points
                        $proficiencyScore = min($totalProficiencyPoints, 35);

                        // Leadership score - based on actual leadership competencies, not ratio
                        $leadershipScore = min($leadershipCount * 3, 20); // 3% per leadership competency, max 20%

                        // Competency breadth - progressive scoring that rewards more competencies
                        if ($totalCompetencies >= 20) {
                            $competencyBreadthScore = 15; // Full 15% for 20+ competencies
                        } elseif ($totalCompetencies >= 10) {
                            $competencyBreadthScore = 10; // 10% for 10-19 competencies
                        } elseif ($totalCompetencies >= 5) {
                            $competencyBreadthScore = 7; // 7% for 5-9 competencies
                        } else {
                            $competencyBreadthScore = $totalCompetencies * 1.5; // 1.5% per competency for 1-4
                        }

                        // Weighted average that rewards competency development
                        $competencyProfileScore = ($proficiencyScore * 0.6) + ($leadershipScore * 0.25) + ($competencyBreadthScore * 0.15);
                    }

                    // Calculate training records component (30% weight) - Enhanced to properly reflect completion
                    $trainingRecordsScore = 0;
                    if ($totalCourses > 0) {
                        // Enhanced training progress scoring - more responsive to completion
                        $trainingProgressScore = min($avgTrainingProgress * 0.25, 25); // Increased from 0.15 to 0.25

                        // Enhanced completion rate scoring
                        $completionRateScore = min($completionRate * 0.20, 20); // Increased from 0.12 to 0.20

                        // Assignment scoring based on course count
                        $assignmentScore = min(($totalCourses / 30) * 12, 12); // Reduced threshold from 50 to 30

                        // Certificate scoring
                        $certificates = $employeeId ? \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)->count() : 0;
                        $certificateScore = $certificates > 0 ? min(($certificates / 10) * 8, 8) : 0; // Reduced threshold from 15 to 10

                        // Enhanced weighted average - prioritizes progress and completion
                        $trainingRecordsScore = ($trainingProgressScore * 0.5) + ($completionRateScore * 0.3) + ($assignmentScore * 0.15) + ($certificateScore * 0.05);

                    }

                    // Final weighted calculation: 70% competency + 30% training (capped at 100%)
                    if ($totalCompetencies > 0 && $totalCourses > 0) {
                        // Both competency and training data available
                        $readiness = ($competencyProfileScore * 0.70) + ($trainingRecordsScore * 0.30);
                    } elseif ($totalCompetencies > 0) {
                        // Only competency data available
                        $readiness = $competencyProfileScore;
                    } elseif ($totalCourses > 0) {
                        // Only training data available
                        $readiness = $trainingRecordsScore;
                    } else {
                        // No data available
                        $readiness = 0; // Baseline score for employees with no data
                    }

                    // Ensure maximum is 100%
                    $readiness = min(round($readiness), 100);
                  }
                  @endphp
                  <span class="fw-semibold">{{ $readiness }}{{ is_numeric($readiness) ? '%' : '' }}</span>
                </td>
                <td>
                  <div class="fw-semibold">
                    @if($record->course && isset($record->course->course_title))
                      {{ $record->course->course_title }}

                                            @php
                        $isDestinationCourse = false;
                        if($record->course) {
                          $courseTitle = strtolower($record->course->course_title);
                          $courseDescription = strtolower($record->course->description ?? '');

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
                        }
                      @endphp

                      @if($isDestinationCourse)
                        <span class="badge bg-success ms-2" title="Exam and Quiz Available">
                          <i class="bi bi-mortarboard"></i> Exam Enabled
                        </span>
                      @else
                        <span class="badge bg-info ms-2" title="Announcement Only - No Exam/Quiz">
                          <i class="bi bi-megaphone"></i> Announcement Only
                        </span>
                      @endif
                    @else
                      <span class="text-muted">No course</span>
                    @endif
                  </div>
                  <small class="text-muted">
                    @if($record->course && isset($record->course->description))
                      {{ Str::limit($record->course->description, 50) }}
                    @else
                      No description
                    @endif
                  </small>
                </td>
                <td>
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
                  @endphp
                  @php
                    // Check if we should hide direct input data (when coming from auto-assign)
                    $hideInput = request()->has('hide_input');

                    if (!$hideInput) {
                      // Only perform these calculations if not coming from auto-assign
                      // Get current level from training record itself
                      $courseTitle = $record->course->course_title ?? '';
                      $currentLevelProgress = 0;
                      $competencyProgressSource = 'none';

                      // Use our own training data model rather than directly querying competency gap
                      $competencyProfile = null;

                      // Get employee's training profile directly from training dashboard records
                      $trainingProfile = \App\Models\EmployeeTrainingDashboard::where('employee_id', $record->employee_id)
                          ->where('course_id', $record->course_id)
                          ->first();

                      if ($trainingProfile) {
                            // Simplified approach that doesn't rely on competency gap data
                            // Use the training record's own progress
                            $actualProgress = 0;

                            // First check for exam progress
                            $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $record->course_id);

                            // If we have exam progress, use that
                            if ($examProgress > 0) {
                                $actualProgress = $examProgress;
                                $competencyProgressSource = 'exam';
                            } else {
                                // Otherwise use the training progress
                                $actualProgress = $trainingProfile->progress ?? 0;
                                $competencyProgressSource = 'training';
                            }

                            // Check if this is destination knowledge training
                            $isDestinationCourse = false;
                            if($record->course) {
                                $courseTitle = strtolower($record->course->course_title);
                                if(strpos($courseTitle, 'destination') !== false) {
                                    $isDestinationCourse = true;

                                    // Check for destination knowledge specific progress
                                    $cleanDestinationName = str_replace(['training', 'course', 'program'], '', $courseTitle);
                                    $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $record->employee_id)
                                        ->where('destination_name', 'LIKE', '%' . trim($cleanDestinationName) . '%')
                                        ->first();

                                    if ($destinationRecord && $destinationRecord->progress > 0) {
                                        $actualProgress = $destinationRecord->progress;
                                        $competencyProgressSource = 'destination';
                                    }
                                }
                            }

                            $currentLevelProgress = min(100, round($actualProgress));
                        }

                        // Use the progress we calculated (only for non-approved requests)
                        if (!$isApprovedRequest && $combinedProgress == 0 && $currentLevelProgress > 0) {
                            $displayProgress = $currentLevelProgress;
                            $progressSource = $competencyProgressSource;
                        }
                    }
                  @endphp
                  <div class="d-flex align-items-center">
                    <progress class="flex-grow-1 me-2" value="{{ $displayProgress }}" max="100" style="height: 8px; width: 100%;"></progress>
                    <span class="fw-semibold">{{ $displayProgress }}%</span>
                  </div>
                  @if($progressSource === 'manual')
                    <small class="text-warning ms-1" title="Manual proficiency level">(manual)</small>
                  @elseif($progressSource === 'destination')
                    <small class="text-success ms-1" title="From destination knowledge training">(destination)</small>
                  @elseif($progressSource === 'training')
                    <small class="text-primary ms-1" title="From employee training dashboard">(training)</small>
                  @elseif($progressSource === 'profile')
                    <small class="text-info ms-1" title="Using stored proficiency level">(profile)</small>
                  @else
                    <small class="text-muted ms-1" title="No data found">(no data)</small>
                  @endif

                                    @if($combinedProgress > 0)
                    @php
                      $breakdown = \App\Models\ExamAttempt::getScoreBreakdown($record->employee_id, $record->course_id);
                    @endphp
                    <small class="text-muted mt-1 d-block"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="Exam Score: {{ $breakdown['exam_score'] }}% = {{ $breakdown['combined_progress'] }}% total progress">
                      @if($breakdown['exam_score'] > 0)
                        <i class="bi bi-mortarboard me-1"></i>Exam: {{ $breakdown['exam_score'] }}%
                      @endif
                      <i class="bi bi-info-circle ms-1" style="cursor: help;"></i>
                    </small>
                  @endif
                </td>
                <td>
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

                    // Third check: Destination Knowledge Training (for destination-specific courses)
                    if (!$finalExpiredDate) {
                      $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $record->employee_id)
                          ->where('destination_name', 'LIKE', '%' . str_replace(['Training', 'Course', 'Program'], '', $record->course->course_title ?? '') . '%')
                          ->first();

                      if ($destinationTraining && $destinationTraining->expired_date) {
                        $finalExpiredDate = $destinationTraining->expired_date;
                      }
                    }

                    // Fourth check: Competency Gap table (for competency-based training)
                    if (!$finalExpiredDate) {
                      $competencyName = str_replace(['Training', 'Course', 'Program'], '', $record->course->course_title ?? '');
                      $competencyGap = \App\Models\CompetencyGap::where('employee_id', $record->employee_id)
                          ->whereHas('competency', function($query) use ($competencyName) {
                              $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                          })
                          ->first();

                      if ($competencyGap && $competencyGap->expired_date) {
                        $finalExpiredDate = $competencyGap->expired_date;
                      }
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
                      $statusTextClass = 'text-danger';
                      $statusText = 'Expired';
                    } elseif ($currentProgress >= 100) {
                      $statusBadgeClass = 'bg-success';
                      $statusTextClass = 'text-success';
                      $statusText = 'Completed';
                    } elseif ($currentProgress > 0) {
                      $statusBadgeClass = 'bg-primary';
                      $statusTextClass = 'text-primary';
                      $statusText = 'In Progress';
                    } else {
                      $statusBadgeClass = 'bg-secondary';
                      $statusTextClass = 'text-secondary';
                      $statusText = 'Not Started';
                    }
                  @endphp
                  <span class="badge {{ $statusBadgeClass }} bg-opacity-10 {{ $statusTextClass }} fs-6">{{ $statusText }}</span>
                </td>
                <td>
                  @php
                    // Check if we should hide direct input data (when coming from auto-assign)
                    $hideInput = request()->has('hide_input');

                    // Get expired date without directly querying competency gap
                    $finalExpiredDate = null;

                    if (!$hideInput) {
                        // First check: Employee Training Dashboard record itself (primary source)
                        if (isset($record->expired_date) && $record->expired_date) {
                          $finalExpiredDate = $record->expired_date;
                        }

                        // Second check: Course Management table
                        if (!$finalExpiredDate && $record->course && isset($record->course->expired_date) && $record->course->expired_date) {
                          $finalExpiredDate = $record->course->expired_date;
                        }

                        // Third check: Destination Knowledge Training (for destination-specific courses)
                        if (!$finalExpiredDate && $record->course) {
                          $courseTitle = $record->course->course_title ?? '';
                          if (stripos($courseTitle, 'destination') !== false) {
                              $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $record->employee_id)
                                  ->where('destination_name', 'LIKE', '%' . str_replace(['Training', 'Course', 'Program'], '', $courseTitle) . '%')
                                  ->first();

                              if ($destinationTraining && $destinationTraining->expired_date) {
                                $finalExpiredDate = $destinationTraining->expired_date;
                              }
                          }
                        }
                   } else {
                     // When hide_input is true, use simplified approach
                     $finalExpiredDate = isset($record->expired_date) ? $record->expired_date : null;
                   }
                 @endphp

                  @if($finalExpiredDate)
                    @php
                      $expiredDate = \Carbon\Carbon::parse($finalExpiredDate);
                      $now = \Carbon\Carbon::now();
                      $daysUntilExpiry = $now->diffInDays($expiredDate, false);

                      // Color coding based on days until expiry (same as Destination Knowledge Training)
                      if ($daysUntilExpiry < 0) {
                        // Already expired - red
                        $colorClass = 'text-danger fw-bold';
                        $bgClass = 'bg-danger text-white';
                        $status = 'EXPIRED';
                      } elseif ($daysUntilExpiry <= 7) {
                        // Expires within 7 days - orange/warning
                        $colorClass = 'text-warning fw-bold';
                        $bgClass = 'bg-warning text-dark';
                        $status = 'URGENT';
                      } elseif ($daysUntilExpiry <= 30) {
                        // Expires within 30 days - yellow
                        $colorClass = 'text-info fw-bold';
                        $bgClass = 'bg-info text-white';
                        $status = 'SOON';
                      } else {
                        // More than 30 days - green
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
                    </div>
                  @else
                    <span class="badge bg-secondary">Not Set</span>
                  @endif
                </td>
                <td>
                  @if($record->last_accessed)
                    {{ \Carbon\Carbon::parse($record->last_accessed)->format('d/m/Y h:i A') }}
                  @else
                    <span class="text-muted">Never</span>
                  @endif
                </td>
                <td class="text-center">
                  <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editTrainingModal"
                    data-id="{{ $record->id }}"
                    data-employee-id="{{ $record->employee_id }}"
                    data-course-id="{{ $record->course_id }}"
                    data-progress="{{ $record->progress }}"
                    data-last-accessed="{{ $record->last_accessed }}">
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                  <button type="button" class="btn btn-danger btn-sm delete-btn"
                    data-id="{{ $record->id }}"
                    data-employee="{{ ($record->employee->first_name ?? 'Unknown') }} {{ ($record->employee->last_name ?? 'Employee') }}"
                    data-course="{{ $record->course ? $record->course->course_title : 'No course' }}">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted">No training records found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
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


  <!-- Edit Training Modal -->
  <div class="modal fade" id="editTrainingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="card-header modal-header">
          <h5 class="modal-title">Update Training Progress</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editTrainingForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <input type="hidden" name="id" id="editTrainingId">
            <div class="mb-3">
              <label class="form-label">Employee</label>
              <select name="employee_id" id="editEmployeeId" class="form-select">
                @foreach($employees as $employee)
                  <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Course</label>
              <select name="course_id" id="editCourseId" class="form-select">
                @foreach($courses as $course)
                    <option value="{{ $course->course_id }}">{{ $course->course_title }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Progress (%)</label>
              <input type="number" name="progress" id="editProgress" class="form-control" min="0" max="100">
            </div>
            <div class="mb-3">
              <label class="form-label">Last Accessed</label>
              <input type="datetime-local" name="last_accessed" id="editLastAccessed" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Training Date</label>
              <input type="date" name="training_date" id="editTrainingDate" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // Initialize tooltips for score breakdown
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    // Initialize edit modal
    const editTrainingModal = document.getElementById('editTrainingModal');
    if (editTrainingModal) {
      editTrainingModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const employeeId = button.getAttribute('data-employee-id');
        const courseId = button.getAttribute('data-course-id');
        const progress = button.getAttribute('data-progress');
        const lastAccessed = button.getAttribute('data-last-accessed');

        const modal = this;
        modal.querySelector('#editTrainingId').value = id;
        modal.querySelector('#editEmployeeId').value = employeeId;
        modal.querySelector('#editCourseId').value = courseId;
        modal.querySelector('#editProgress').value = progress;

        if (lastAccessed) {
          const date = new Date(lastAccessed);
          const formattedDate = date.toISOString().slice(0, 16);
          modal.querySelector('#editLastAccessed').value = formattedDate;
        } else {
          modal.querySelector('#editLastAccessed').value = '';
        }

        // Set form action and method for standard POST
        const form = modal.querySelector('#editTrainingForm');
        form.action = `{{ url('/admin/employee-trainings-dashboard') }}/${id}`;
        form.setAttribute('method', 'POST');
      });
    }

    // Filter functionality
    document.getElementById('applyFilters').addEventListener('click', function() {
      const employeeFilter = document.getElementById('filterEmployee').value;
      const courseFilter = document.getElementById('filterCourse').value;
      const statusFilter = document.getElementById('filterStatus').value;

      // This would typically be an AJAX call to filter server-side
      // For demo purposes, we'll just show an alert
      alert(`Filters applied:\nEmployee: ${employeeFilter || 'All'}\nCourse: ${courseFilter || 'All'}\nStatus: ${statusFilter || 'All'}`);

      // In a real implementation, you would:
      // 1. Send filter parameters to server via AJAX
      // 2. Update the table with the filtered results
    });

    // Delete functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
      button.addEventListener('click', async function() {
        const recordId = this.getAttribute('data-id');
        const employeeName = this.getAttribute('data-employee');
        const courseName = this.getAttribute('data-course');

        if (!confirm(`Are you sure you want to delete the training record for ${employeeName} - ${courseName}?`)) {
          return;
        }

        try {
          this.disabled = true;
          this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
          if (!csrfToken) {
            throw new Error('CSRF token not found');
          }

          const response = await fetch(`{{ url('/admin/employee-trainings-dashboard') }}/${recordId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            }
          });

          const result = await response.json();

          if (response.ok && result.success) {
            // Remove the row from the table
            const row = this.closest('tr');
            row.remove();

            // Show success message
            alert(result.message || 'Training record deleted successfully!');
          } else {
            const errorMessage = result.message || `Server error: ${response.status} ${response.statusText}`;
            throw new Error(errorMessage);
          }
        } catch (error) {
          console.error('Delete error:', error);
          alert('Failed to delete record: ' + error.message);
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-trash"></i> Delete';
        }
      });
    });

    // Animate progress bars
    document.querySelectorAll('progress').forEach(bar => {
      const value = bar.value;
      bar.value = 0;
      setTimeout(() => {
        bar.value = value;
      }, 100);
    });

    // Create Missing Entries functionality
    document.getElementById('createMissingEntriesBtn').addEventListener('click', async function() {
      const button = this;
      const originalText = button.innerHTML;

      try {
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
          throw new Error('CSRF token not found');
        }

        const response = await fetch('{{ route("admin.employee_trainings_dashboard.sync_existing") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        const result = await response.json();

        if (response.ok && result.success) {
          // Show success message
          alert(result.message || 'Missing entries created successfully!');

          // Refresh the page to show updated data
          window.location.reload();
        } else {
          const errorMessage = result.message || `Server error: ${response.status} ${response.statusText}`;
          throw new Error(errorMessage);
        }
      } catch (error) {
        console.error('Create missing entries error:', error);
        alert('Error creating missing entries. Please try again: ' + error.message);

        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
      }
    });

    // Fix Expired Dates functionality
    document.getElementById('fixExpiredDatesBtn').addEventListener('click', async function() {
      const button = this;
      const originalText = button.innerHTML;

      try {
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Fixing...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
          throw new Error('CSRF token not found');
        }

        const response = await fetch('{{ route("admin.employee_trainings_dashboard.fix_expired_dates") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        const result = await response.json();

        if (response.ok && result.success) {
          // Show success message
          alert(result.message || 'Expired dates fixed successfully!');

          // Refresh the page to show updated data
          window.location.reload();
        } else {
          const errorMessage = result.message || `Server error: ${response.status} ${response.statusText}`;
          throw new Error(errorMessage);
        }
      } catch (error) {
        console.error('Fix expired dates error:', error);
        alert('Error fixing expired dates. Please try again: ' + error.message);

        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
      }
    });

  });
  </script>

</body>
</html>
