<!DOCTYPE html>
<html lang="en">
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
  <!-- jQuery for AJAX -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Card Hover Effects */
    .training-gap-card:hover,
    .skill-card:hover,
    .training-record-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    /* Star Rating Hover Effects */
    .star-rating i {
      transition: all 0.2s ease;
    }

    .star-rating:hover i {
      transform: scale(1.1);
    }

    /* Progress Ring Animation */
    .progress-ring circle {
      transition: stroke-dashoffset 0.5s ease-in-out;
    }

    /* Button Group Hover Effects */
    .btn-group .btn {
      transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
      transform: translateY(-1px);
    }

    /* Card Header Gradient Effects */
    .card-header.bg-primary {
      background: linear-gradient(135deg, #007bff, #0056b3) !important;
    }

    .card-header.bg-success {
      background: linear-gradient(135deg, #28a745, #20c997) !important;
    }

    .card-header.bg-info {
      background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    }

    .card-header.bg-warning {
      background: linear-gradient(135deg, #ffc107, #fd7e14) !important;
    }

    .card-header.bg-danger {
      background: linear-gradient(135deg, #dc3545, #e74c3c) !important;
    }

    /* Responsive Grid Adjustments */
    @media (max-width: 768px) {
      .training-gap-card,
      .skill-card,
      .training-record-card {
        margin-bottom: 1rem;
      }

      .card-header h5 {
        font-size: 1rem;
      }

      .progress-ring {
        width: 100px;
        height: 100px;
      }

      .progress-ring circle {
        r: 40;
        cx: 50;
        cy: 50;
      }
    }

    /* Animation for Progress Bars */
    @keyframes progressAnimation {
      0% { width: 0%; }
      100% { width: var(--progress-width); }
    }

    .progress-bar {
      animation: progressAnimation 1.5s ease-in-out;
    }

    /* Badge Animations */
    .badge {
      transition: all 0.2s ease;
    }

    .badge:hover {
      transform: scale(1.05);
    }

    /* Pagination Controls Styling */
    .pagination-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .pagination-controls .btn {
      transition: all 0.2s ease;
      border-radius: 6px;
      font-size: 0.875rem;
      padding: 0.375rem 0.75rem;
    }

    .pagination-controls .btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .pagination-controls .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .pagination-controls span {
      font-size: 0.875rem;
      font-weight: 500;
      min-width: 80px;
      text-align: center;
    }

    /* Responsive pagination */
    @media (max-width: 768px) {
      .pagination-controls {
        flex-direction: column;
        gap: 4px;
      }
      
      .pagination-controls span {
        min-width: auto;
      }
      
      .d-flex.gap-2 {
        flex-direction: column;
        gap: 8px !important;
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
            <h2 class="fw-bold mb-1">Customer Service Sales Skills Training</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage training records for customer service sales skills.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Customer Service Sales Skills Training</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Employees Needing Training -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employees Needing Customer Service & Sales Skills Training</h4>
        <div class="d-flex gap-2">
          <div class="pagination-controls me-3">
            <button class="btn btn-outline-secondary btn-sm" onclick="previousPage('gaps')" id="gaps-prev-btn">
              <i class="bi bi-chevron-left"></i> Previous
            </button>
            <span class="mx-2 text-muted" id="gaps-page-info">Page 1</span>
            <button class="btn btn-outline-secondary btn-sm" onclick="nextPage('gaps')" id="gaps-next-btn">
              Next <i class="bi bi-chevron-right"></i>
            </button>
          </div>
          <button class="btn btn-success btn-sm" onclick="syncTrainingProgressWithConfirmation()">
            <i class="bi bi-arrow-repeat me-1"></i> Sync Training Progress
          </button>
        </div>
      </div>
      <div class="card-body">
        <div id="gaps-container">
        @forelse($gaps as $gap)
          @php
            $firstName = $gap->employee->first_name ?? 'Unknown';
            $lastName = $gap->employee->last_name ?? 'Employee';
            $fullName = $firstName . ' ' . $lastName;
            
            // Check if profile picture exists - simplified approach
            $profilePicUrl = null;
            if ($gap->employee->profile_picture) {
                // Direct asset URL generation - Laravel handles the storage symlink
                $profilePicUrl = asset('storage/' . $gap->employee->profile_picture);
            }
            
            // Generate consistent color based on employee name for fallback
            $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
            $employeeId = $gap->employee->employee_id ?? 'default';
            $colorIndex = abs(crc32($employeeId)) % count($colors);
            $bgColor = $colors[$colorIndex];
            
            // Fallback to UI Avatars if no profile picture found
            if (!$profilePicUrl) {
                $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . 
                               "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
            }
            
            // Convert levels to 1-5 scale for display
            $displayRequiredLevel = $gap->required_level > 5 ? 5 : ($gap->required_level < 1 ? round($gap->required_level * 20) : $gap->required_level);
            $displayCurrentLevel = $gap->current_level > 5 ? 5 : ($gap->current_level < 1 ? round($gap->current_level * 20) : $gap->current_level);
            $displayGap = max(0, $displayRequiredLevel - $displayCurrentLevel);
            
            // Dynamic header color based on gap severity
            $headerClass = 'bg-primary';
            if ($displayGap >= 4) {
                $headerClass = 'bg-danger';
            } elseif ($displayGap >= 3) {
                $headerClass = 'bg-warning';
            } elseif ($displayGap >= 2) {
                $headerClass = 'bg-info';
            }
          @endphp
          
          <div class="col-12 mb-4">
            <div class="card h-100 shadow-sm border-0 training-gap-card" style="transition: all 0.3s ease;">
              <!-- Dynamic Header with Employee Info -->
              <div class="card-header {{ $headerClass }} text-white rounded-top" style="border-radius: 12px 12px 0 0 !important;">
                <div class="d-flex align-items-center">
                  <img src="{{ $profilePicUrl }}" 
                       alt="{{ $firstName }} {{ $lastName }}" 
                       class="rounded-circle me-3" 
                       style="width: 50px; height: 50px; object-fit: cover; border: 3px solid rgba(255,255,255,0.3);">
                  <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">{{ $firstName }} {{ $lastName }}</h5>
                    <small class="opacity-75">ID: {{ $gap->employee->employee_id }} • Gap Level: {{ $displayGap }}</small>
                  </div>
                  <div class="text-end">
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">Training Needed</span>
                  </div>
                </div>
              </div>
              
              <!-- Card Body with Competency Details -->
              <div class="card-body p-4">
                <div class="row g-4">
                  <!-- Competency Information -->
                  <div class="col-md-6">
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded">
                      <i class="bi bi-award text-primary fs-3 me-3 mt-1"></i>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold text-primary fs-5">Competency Required</h6>
                        <p class="mb-0 fs-6 fw-semibold">{{ $gap->competency->competency_name }}</p>
                        <small class="text-muted">Core skill needed for role</small>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Recommended Training -->
                  <div class="col-md-6">
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded">
                      <i class="bi bi-book text-success fs-3 me-3 mt-1"></i>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold text-success fs-5">Recommended Training</h6>
                        <p class="mb-0 fs-6 fw-semibold">
                          @if($gap->recommended_training && isset($gap->recommended_training->course_title))
                            {{ $gap->recommended_training->course_title }}
                          @else
                            <span class="text-muted">No training assigned</span>
                          @endif
                        </p>
                        <small class="text-muted">Suggested course to close gap</small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Progress Levels -->
                <div class="row g-4 mt-2">
                  <div class="col-md-4">
                    <div class="text-center p-4 bg-info bg-opacity-10 border border-info border-opacity-25 rounded">
                      <i class="bi bi-target text-info fs-3 mb-2"></i>
                      <div class="fs-1 fw-bold text-info">{{ $displayRequiredLevel }}</div>
                      <div class="fw-semibold text-info">Required Level</div>
                      <small class="text-muted">Target proficiency</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="text-center p-4 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded">
                      <i class="bi bi-person-check text-warning fs-3 mb-2"></i>
                      <div class="fs-1 fw-bold text-warning">{{ $displayCurrentLevel }}</div>
                      <div class="fw-semibold text-warning">Current Level</div>
                      <small class="text-muted">Present skill level</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="text-center p-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded">
                      <i class="bi bi-exclamation-triangle text-danger fs-3 mb-2"></i>
                      <div class="fs-1 fw-bold text-danger">{{ $displayGap }}</div>
                      <div class="fw-semibold text-danger">Gap to Close</div>
                      <small class="text-muted">Training needed</small>
                    </div>
                  </div>
                </div>
                
                <!-- Visual Progress Bar -->
                <div class="mt-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Skill Progress</span>
                    <span class="text-muted">{{ round(($displayCurrentLevel / $displayRequiredLevel) * 100) }}%</span>
                  </div>
                  <div class="progress" style="height: 12px; border-radius: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: {{ min(($displayCurrentLevel / $displayRequiredLevel) * 100, 100) }}%; background: linear-gradient(45deg, #007bff, #0056b3);" 
                         aria-valuenow="{{ ($displayCurrentLevel / $displayRequiredLevel) * 100 }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-5">
            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-success">All Employees Meet Required Skills!</h4>
            <p class="text-muted">No training gaps found for customer service and sales skills.</p>
          </div>
        @endforelse
        </div>
      </div>
    </div>

    <!-- Skills Reference -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Customer Service & Sales Skills Reference</h4>
        <div class="pagination-controls">
          <button class="btn btn-outline-secondary btn-sm" onclick="previousPage('skills')" id="skills-prev-btn">
            <i class="bi bi-chevron-left"></i> Previous
          </button>
          <span class="mx-2 text-muted" id="skills-page-info">Page 1</span>
          <button class="btn btn-outline-secondary btn-sm" onclick="nextPage('skills')" id="skills-next-btn">
            Next <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-4" id="skills-container">
        @forelse($skills as $skill)
          @php
            $skillPercentage = min(($skill->rate * 20), 100);
            $skillLevel = $skill->rate;
            
            // Dynamic color based on skill level
            $headerClass = 'bg-primary';
            $progressClass = 'bg-primary';
            if ($skillLevel >= 4.5) {
                $headerClass = 'bg-success';
                $progressClass = 'bg-success';
            } elseif ($skillLevel >= 3.5) {
                $headerClass = 'bg-info';
                $progressClass = 'bg-info';
            } elseif ($skillLevel >= 2.5) {
                $headerClass = 'bg-warning';
                $progressClass = 'bg-warning';
            } elseif ($skillLevel < 2.5) {
                $headerClass = 'bg-danger';
                $progressClass = 'bg-danger';
            }
            
            // Generate star rating
            $fullStars = floor($skillLevel);
            $hasHalfStar = ($skillLevel - $fullStars) >= 0.5;
            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
          @endphp
          
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0 skill-card" style="transition: all 0.3s ease; cursor: pointer;">
              <!-- Skill Header -->
              <div class="card-header {{ $headerClass }} text-white" style="border-radius: 12px 12px 0 0 !important;">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="mb-1 fw-bold">{{ $skill->competency_name }}</h5>
                    <small class="opacity-75">Proficiency Level</small>
                  </div>
                  <div class="text-end">
                    <i class="bi bi-award fs-3"></i>
                  </div>
                </div>
              </div>
              
              <!-- Card Body -->
              <div class="card-body p-4 text-center">
                <!-- Star Rating Display -->
                <div class="mb-3">
                  <div class="star-rating fs-4" style="color: #ffc107;">
                    @for($i = 0; $i < $fullStars; $i++)
                      <i class="bi bi-star-fill"></i>
                    @endfor
                    @if($hasHalfStar)
                      <i class="bi bi-star-half"></i>
                    @endif
                    @for($i = 0; $i < $emptyStars; $i++)
                      <i class="bi bi-star"></i>
                    @endfor
                  </div>
                  <div class="mt-2">
                    <span class="fs-5 fw-bold text-primary">{{ number_format($skillLevel, 1) }}/5.0</span>
                  </div>
                </div>
                
                <!-- Progress Circle -->
                <div class="position-relative d-inline-block mb-3">
                  <svg width="120" height="120" class="progress-ring">
                    <circle cx="60" cy="60" r="50" 
                            fill="transparent" 
                            stroke="#e9ecef" 
                            stroke-width="8"/>
                    <circle cx="60" cy="60" r="50" 
                            fill="transparent" 
                            stroke="{{ $skillLevel >= 4.5 ? '#28a745' : ($skillLevel >= 3.5 ? '#17a2b8' : ($skillLevel >= 2.5 ? '#ffc107' : '#dc3545')) }}" 
                            stroke-width="8"
                            stroke-linecap="round"
                            stroke-dasharray="{{ 2 * pi() * 50 }}"
                            stroke-dashoffset="{{ 2 * pi() * 50 * (1 - $skillPercentage / 100) }}"
                            style="transition: stroke-dashoffset 0.5s ease-in-out; transform: rotate(-90deg); transform-origin: 50% 50%;"/>
                  </svg>
                  <div class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="fs-4 fw-bold text-primary">{{ round($skillPercentage) }}%</div>
                    <small class="text-muted">Proficiency</small>
                  </div>
                </div>
                
                <!-- Skill Level Badge -->
                <div class="mt-3">
                  @if($skillLevel >= 4.5)
                    <span class="badge bg-success bg-opacity-10 text-success fs-6 px-3 py-2">Expert Level</span>
                  @elseif($skillLevel >= 3.5)
                    <span class="badge bg-info bg-opacity-10 text-info fs-6 px-3 py-2">Advanced</span>
                  @elseif($skillLevel >= 2.5)
                    <span class="badge bg-warning bg-opacity-10 text-warning fs-6 px-3 py-2">Intermediate</span>
                  @else
                    <span class="badge bg-danger bg-opacity-10 text-danger fs-6 px-3 py-2">Needs Improvement</span>
                  @endif
                </div>
                
                <!-- Linear Progress Bar -->
                <div class="mt-4">
                  <div class="progress" style="height: 8px; border-radius: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $progressClass }}" 
                         role="progressbar" 
                         style="width: {{ $skillPercentage }}%;" 
                         aria-valuenow="{{ $skillPercentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                  </div>
                  <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">Beginner</small>
                    <small class="text-muted">Expert</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-5">
            <i class="bi bi-book text-primary" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-primary">No Skills Defined</h4>
            <p class="text-muted">No skills are currently defined in the competency library.</p>
          </div>
        @endforelse
        </div>
      </div>
    </div>

    <!-- Training Records -->
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Training Dashboard</h4>
        <div class="d-flex gap-2">
          <div class="pagination-controls me-3">
            <button class="btn btn-outline-secondary btn-sm" onclick="previousPage('records')" id="records-prev-btn">
              <i class="bi bi-chevron-left"></i> Previous
            </button>
            <span class="mx-2 text-muted" id="records-page-info">Page 1</span>
            <button class="btn btn-outline-secondary btn-sm" onclick="nextPage('records')" id="records-next-btn">
              Next <i class="bi bi-chevron-right"></i>
            </button>
          </div>
          <button class="btn btn-primary btn-sm" onclick="addRecordWithConfirmation()">
            <i class="bi bi-plus-lg me-1"></i> Add Record
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-4" id="records-container">
        @forelse($records as $record)
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
            
            // Use the EXACT same calculation as main Employee Training Dashboard
            $employee = $record->employee;
            $employeeId = $employee->employee_id ?? null;
            
            if ($employeeId) {
              // Call the same controller method that succession planning uses (EXACT same as main dashboard)
              $controller = new \App\Http\Controllers\SuccessionReadinessRatingController();
              $readiness = round($controller->calculateEmployeeReadinessScore($employeeId));
            } else {
              $readiness = 0;
            }
            
            // Enhanced progress calculation with multiple fallback strategies
            $courseId = $record->course_id ?? $record->training_id;
            $combinedProgress = 0;
            
            // Strategy 1: Try with the course_id we have
            if ($courseId) {
                $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseId);
            }
            
            // Strategy 2: If no progress found, try to find by course title
            if ($combinedProgress == 0 && isset($record->training) && isset($record->training->course_title)) {
                $courseTitle = $record->training->course_title;
                // Find course by title
                $courseByTitle = \App\Models\CourseManagement::where('course_title', $courseTitle)->first();
                if ($courseByTitle) {
                    $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseByTitle->course_id);
                }
            }
            
            // Strategy 3: If still no progress, try Communication Skills specifically
            if ($combinedProgress == 0 && (stripos($record->training->course_title ?? '', 'Communication') !== false)) {
                $commSkillsCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%Communication Skills%')->first();
                if ($commSkillsCourse) {
                    $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $commSkillsCourse->course_id);
                }
            }
            
            $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);
            
            // Dynamic header color based on progress
            $headerClass = 'bg-primary';
            if ($displayProgress >= 100) {
                $headerClass = 'bg-success';
            } elseif ($displayProgress >= 75) {
                $headerClass = 'bg-info';
            } elseif ($displayProgress >= 50) {
                $headerClass = 'bg-warning';
            } elseif ($displayProgress < 25) {
                $headerClass = 'bg-danger';
            }
          @endphp
          
          <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100 shadow-sm border-0 training-record-card" style="transition: all 0.3s ease;">
              <!-- Dynamic Header with Employee Info -->
              <div class="card-header {{ $headerClass }} text-white" style="border-radius: 12px 12px 0 0 !important;">
                <div class="d-flex align-items-center">
                  <img src="{{ $profilePicUrl }}" 
                       alt="{{ $firstName }} {{ $lastName }}" 
                       class="rounded-circle me-3" 
                       style="width: 50px; height: 50px; object-fit: cover; border: 3px solid rgba(255,255,255,0.3);">
                  <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">{{ $firstName }} {{ $lastName }}</h5>
                    <small class="opacity-75">ID: {{ $record->employee->employee_id }} • Record #{{ $loop->iteration }}</small>
                  </div>
                  <div class="text-end">
                    <div class="fs-6 fw-bold">{{ $readiness }}{{ is_numeric($readiness) ? '%' : '' }}</div>
                    <small class="opacity-75">Readiness</small>
                  </div>
                </div>
              </div>
              
              <!-- Card Body with Training Details -->
              <div class="card-body p-4">
                <!-- Training Information -->
                <div class="mb-3">
                  <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-book text-primary fs-5 me-2"></i>
                    <h6 class="mb-0 fw-bold text-primary">Training Course</h6>
                  </div>
                  <p class="mb-0 ms-4">
                    @if(isset($record->training))
                      @if(isset($record->training->course))
                        {{ $record->training->course->course_title }}
                      @elseif(isset($record->training->title))
                        {{ $record->training->title }}
                      @else
                        <span class="text-muted">No training assigned</span>
                      @endif
                    @else
                      <span class="text-muted">No training assigned</span>
                    @endif
                  </p>
                </div>
                
                <!-- Completion Date -->
                <div class="mb-3">
                  <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-calendar-check text-success fs-5 me-2"></i>
                    <h6 class="mb-0 fw-bold text-success">Date Completed</h6>
                  </div>
                  <p class="mb-0 ms-4">
                    @if(!empty($record->date_completed) && $record->date_completed != '1970-01-01')
                      {{ \Carbon\Carbon::parse($record->date_completed)->format('d/m/Y') }}
                    @else
                      <span class="text-muted">Not completed</span>
                    @endif
                  </p>
                </div>
                
                <!-- Progress Section -->
                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Training Progress</span>
                    <span class="fw-bold text-primary">{{ $displayProgress }}%</span>
                  </div>
                  <div class="progress mb-2" style="height: 12px; border-radius: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: {{ $displayProgress }}%; background: linear-gradient(45deg, {{ $displayProgress >= 100 ? '#28a745, #20c997' : ($displayProgress >= 75 ? '#17a2b8, #20c997' : ($displayProgress >= 50 ? '#ffc107, #fd7e14' : '#dc3545, #e74c3c')) }});" 
                         aria-valuenow="{{ $displayProgress }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                  </div>
                  
                  @if($combinedProgress > 0)
                    @php
                      $breakdown = \App\Models\ExamAttempt::getScoreBreakdown($record->employee_id, $courseId);
                    @endphp
                    <small class="text-muted" 
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Exam Score: {{ $breakdown['exam_score'] }}% = {{ $breakdown['combined_progress'] }}% total progress">
                      @if($breakdown['exam_score'] > 0)
                        <i class="bi bi-mortarboard me-1"></i>Exam: {{ $breakdown['exam_score'] }}%
                      @endif
                      <i class="bi bi-info-circle ms-1" style="cursor: help;"></i>
                    </small>
                  @endif
                  
                  <div class="mt-2">
                    @if($displayProgress >= 100)
                      <span class="badge bg-success bg-opacity-10 text-success fs-6 px-3 py-2">Completed</span>
                    @elseif($displayProgress > 0)
                      <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2">In Progress</span>
                    @else
                      <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6 px-3 py-2">Not Started</span>
                    @endif
                  </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                  <div class="btn-group" role="group">
                    <button class="btn btn-outline-info btn-sm" title="View Details" onclick="viewRecordDetails({{ $record->id }})">
                      <i class="bi bi-eye me-1"></i> View
                    </button>
                    <button class="btn btn-outline-warning btn-sm" title="Edit Record" onclick="editRecordWithConfirmation({{ $record->id }})">
                      <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <button class="btn btn-outline-danger btn-sm" title="Delete Record" onclick="deleteRecordWithConfirmation({{ $record->id }})">
                      <i class="bi bi-trash me-1"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="text-center py-5">
              <i class="bi bi-clipboard-data text-primary" style="font-size: 4rem;"></i>
              <h4 class="mt-3 text-primary">No Training Records Found</h4>
              <p class="text-muted">No training records are currently available. Click "Add Record" to create a new training record.</p>
            </div>
          </div>
        @endforelse
        </div>

        <div class="mt-4 p-3 bg-light rounded">
          <div class="d-flex align-items-center">
            <i class="bi bi-info-circle text-success fs-5 me-2"></i>
            <div>
              <span class="fw-bold text-success">Note:</span>
              When a training record is marked completed, a certificate will be awarded and tracked automatically.
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Modal -->
  <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Training Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('customer_service_sales_skills_training.store') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Employee*</label>
              <select class="form-control" name="employee_id" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->employee_id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_id }})</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Training*</label>
              <select class="form-control" name="training_id" required>
                <option value="">Select Training</option>
                @foreach($trainings as $training)
                  <option value="{{ $training->id }}">
                    @if(isset($training->course))
                      {{ $training->course->course_title }}
                    @elseif(isset($training->title))
                      {{ $training->title }}
                    @else
                      Training
                    @endif
                  </option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Date Completed*</label>
              <input type="date" class="form-control" name="date_completed" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View and Edit Modals -->
  @foreach($records as $record)
  <!-- View Modal -->
  <div class="modal fade" id="viewModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">View Training Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <strong>Employee:</strong> {{ $record->employee->first_name }} {{ $record->employee->last_name }} ({{ $record->employee->employee_id }})
          </div>
          <div class="mb-3">
            <strong>Training:</strong>
            @if(isset($record->training))
              @if(isset($record->training->course))
                {{ $record->training->course->course_title }}
              @elseif(isset($record->training->title))
                {{ $record->training->title }}
              @else
                <span class="text-muted">No training assigned</span>
              @endif
            @else
              <span class="text-muted">No training assigned</span>
            @endif
          </div>
          <div class="mb-3">
            <strong>Date Completed:</strong>
            @if(!empty($record->date_completed) && $record->date_completed != '1970-01-01')
              {{ \Carbon\Carbon::parse($record->date_completed)->format('d/m/Y') }}
            @else
              <span class="text-muted">Not completed</span>
            @endif
          </div>
          <div class="mb-3">
            <strong>Progress:</strong>
            @php
              // Enhanced progress calculation with multiple fallback strategies
              $courseId = $record->course_id ?? $record->training_id;
              $combinedProgress = 0;
              
              // Strategy 1: Try with the course_id we have
              if ($courseId) {
                  $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseId);
              }
              
              // Strategy 2: If no progress found, try to find by course title
              if ($combinedProgress == 0 && isset($record->training) && isset($record->training->course_title)) {
                  $courseTitle = $record->training->course_title;
                  // Find course by title
                  $courseByTitle = \App\Models\CourseManagement::where('course_title', $courseTitle)->first();
                  if ($courseByTitle) {
                      $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseByTitle->course_id);
                  }
              }
              
              // Strategy 3: If still no progress, try Communication Skills specifically
              if ($combinedProgress == 0 && (stripos($record->training->course_title ?? '', 'Communication') !== false)) {
                  $commSkillsCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%Communication Skills%')->first();
                  if ($commSkillsCourse) {
                      $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $commSkillsCourse->course_id);
                  }
              }
              
              $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);
            @endphp
            <div class="d-flex align-items-center">
              <progress class="flex-grow-1 me-2" value="{{ $displayProgress }}" max="100" style="height: 8px; width: 100%;"></progress>
              <span class="fw-semibold">{{ $displayProgress }}%</span>
            </div>
            
            @if($combinedProgress > 0)
              @php
                $breakdown = \App\Models\ExamAttempt::getScoreBreakdown($record->employee_id, $courseId);
              @endphp
              <small class="text-muted mt-1 d-block">
                @if($breakdown['exam_score'] > 0)
                  <i class="bi bi-mortarboard me-1"></i>Exam Score: {{ $breakdown['exam_score'] }}%
                @endif
              </small>
            @endif
            
            <div class="mt-2">
              @if($displayProgress >= 100)
                <span class="badge bg-success bg-opacity-10 text-success fs-6">Completed</span>
              @elseif($displayProgress > 0)
                <span class="badge bg-primary bg-opacity-10 text-primary fs-6">In Progress</span>
              @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6">Not Started</span>
              @endif
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Training Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('customer_service_sales_skills_training.update', $record->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Employee*</label>
              <select class="form-control" name="employee_id" required>
                @foreach($employees as $emp)
                  <option value="{{ $emp->employee_id }}" {{ $record->employee_id == $emp->employee_id ? 'selected' : '' }}>
                    {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_id }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Training*</label>
              <select class="form-control" name="training_id" required>
                @foreach($trainings as $training)
                  <option value="{{ $training->id }}" {{ $record->training_id == $training->id ? 'selected' : '' }}>
                    @if(isset($training->course))
                      {{ $training->course->course_title }}
                    @elseif(isset($training->title))
                      {{ $training->title }}
                    @else
                      Training
                    @endif
                  </option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Date Completed*</label>
              <input type="date" class="form-control" name="date_completed" value="{{ $record->date_completed }}" required>
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
  @endforeach

  @php
    // Pre-calculate progress data to avoid complex PHP in JavaScript
    $recordProgressData = [];
    foreach($records as $record) {
      // Enhanced progress calculation with multiple fallback strategies
      $courseId = $record->course_id ?? $record->training_id;
      $combinedProgress = 0;
      
      // Strategy 1: Try with the course_id we have
      if ($courseId) {
          $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseId);
      }
      
      // Strategy 2: If no progress found, try to find by course title
      if ($combinedProgress == 0 && isset($record->training) && isset($record->training->course_title)) {
          $courseTitle = $record->training->course_title;
          // Find course by title
          $courseByTitle = \App\Models\CourseManagement::where('course_title', $courseTitle)->first();
          if ($courseByTitle) {
              $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseByTitle->course_id);
          }
      }
      
      // Strategy 3: If still no progress, try Communication Skills specifically
      if ($combinedProgress == 0 && (stripos($record->training->course_title ?? '', 'Communication') !== false)) {
          $commSkillsCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%Communication Skills%')->first();
          if ($commSkillsCourse) {
              $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $commSkillsCourse->course_id);
          }
      }
      
      $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);
      
      // Get exam score breakdown if available
      $examScore = 0;
      if ($combinedProgress > 0) {
          $breakdown = \App\Models\ExamAttempt::getScoreBreakdown($record->employee_id, $courseId);
          $examScore = $breakdown['exam_score'] ?? 0;
      }
      
      // Get training title with multiple fallback strategies
      $trainingTitle = 'No training assigned';
      if (isset($record->training) && isset($record->training->course_title)) {
          $trainingTitle = $record->training->course_title;
      } elseif (isset($record->training) && isset($record->training->title)) {
          $trainingTitle = $record->training->title;
      } elseif (isset($record->course) && isset($record->course->course_title)) {
          $trainingTitle = $record->course->course_title;
      }

      $recordProgressData[$record->id] = [
          'display_progress' => $displayProgress,
          'exam_score' => $examScore,
          'has_exam_data' => $combinedProgress > 0,
          'course_id' => $courseId,
          'training_title' => $trainingTitle
      ];
    }
  @endphp

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Pagination variables
    const itemsPerPage = 5; // Show 5 items per page
    let currentPages = {
      gaps: 1,
      skills: 1,
      records: 1
    };

    // Data arrays
    const allData = {
      gaps: @json($gaps->values()),
      skills: @json($skills->values()),
      records: @json($records->values())
    };

    // Pre-calculated progress data for each record
    const recordProgressData = @json($recordProgressData);

    // Initialize tooltips and pagination
    $(document).ready(function() {
      $('[data-bs-toggle="tooltip"]').tooltip();
      initializePagination();
    });

    // Initialize pagination for all sections
    function initializePagination() {
      updatePagination('gaps');
      updatePagination('skills');
      updatePagination('records');
    }

    // Pagination Functions
    function previousPage(section) {
      if (currentPages[section] > 1) {
        currentPages[section]--;
        updatePagination(section);
      }
    }

    function nextPage(section) {
      const totalPages = Math.ceil(allData[section].length / itemsPerPage);
      if (currentPages[section] < totalPages) {
        currentPages[section]++;
        updatePagination(section);
      }
    }

    function updatePagination(section) {
      const data = allData[section];
      const totalPages = Math.ceil(data.length / itemsPerPage);
      const currentPage = currentPages[section];
      
      // Update page info
      document.getElementById(`${section}-page-info`).textContent = `Page ${currentPage} of ${totalPages}`;
      
      // Update button states
      document.getElementById(`${section}-prev-btn`).disabled = currentPage === 1;
      document.getElementById(`${section}-next-btn`).disabled = currentPage === totalPages || totalPages === 0;
      
      // Show/hide items based on current page
      const startIndex = (currentPage - 1) * itemsPerPage;
      const endIndex = startIndex + itemsPerPage;
      
      if (section === 'gaps') {
        updateGapsDisplay(data, startIndex, endIndex);
      } else if (section === 'skills') {
        updateSkillsDisplay(data, startIndex, endIndex);
      } else if (section === 'records') {
        updateRecordsDisplay(data, startIndex, endIndex);
      }
    }

    function updateGapsDisplay(data, startIndex, endIndex) {
      const container = document.getElementById('gaps-container');
      const visibleData = data.slice(startIndex, endIndex);
      
      // Hide all gap items first
      const allGapItems = container.querySelectorAll('.col-12');
      allGapItems.forEach((item, index) => {
        if (index < startIndex || index >= endIndex) {
          item.style.display = 'none';
        } else {
          item.style.display = 'block';
        }
      });
    }

    function updateSkillsDisplay(data, startIndex, endIndex) {
      const container = document.getElementById('skills-container');
      
      // Hide all skill items first
      const allSkillItems = container.querySelectorAll('.col-lg-4');
      allSkillItems.forEach((item, index) => {
        if (index < startIndex || index >= endIndex) {
          item.style.display = 'none';
        } else {
          item.style.display = 'block';
        }
      });
    }

    function updateRecordsDisplay(data, startIndex, endIndex) {
      const container = document.getElementById('records-container');
      
      // Hide all record items first
      const allRecordItems = container.querySelectorAll('.col-lg-6');
      allRecordItems.forEach((item, index) => {
        if (index < startIndex || index >= endIndex) {
          item.style.display = 'none';
        } else {
          item.style.display = 'block';
        }
      });
    }

    // Get CSRF Token
    function getCSRFToken() {
      return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Verify Admin Password
    async function verifyAdminPassword(password) {
      try {
        const response = await fetch('/admin/verify-password', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken(),
            'Content-Type': 'application/json'
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

    // View Record Details
    async function viewRecordDetails(recordId) {
      const recordData = @json($records->keyBy('id'));
      const employeeData = @json($employees->keyBy('employee_id'));
      const trainingData = @json($trainings->keyBy('id'));
      
      const record = recordData[recordId];
      if (!record) {
        Swal.fire('Error', 'Record not found', 'error');
        return;
      }

      const employee = employeeData[record.employee_id];
      const training = trainingData[record.training_id];

      console.log('Training Data:', training);
      console.log('Record:', record);

      // Get pre-calculated progress data
      const progressInfo = recordProgressData[recordId];
      
      // Use pre-calculated training title or fallback to manual extraction
      let trainingTitle = progressInfo && progressInfo.training_title ? progressInfo.training_title : 'No training assigned';
      
      // If still no title, try manual extraction as fallback
      if (trainingTitle === 'No training assigned') {
        if (training && training.course && training.course.course_title) {
          trainingTitle = training.course.course_title;
        } else if (training && training.title) {
          trainingTitle = training.title;
        } else if (training && training.course_title) {
          trainingTitle = training.course_title;
        } else if (record.training && record.training.course_title) {
          trainingTitle = record.training.course_title;
        } else if (record.training && record.training.title) {
          trainingTitle = record.training.title;
        }
      }

      console.log('Final Training Title:', trainingTitle);

      // Get progress data from the already retrieved progressInfo
      let displayProgress = progressInfo ? progressInfo.display_progress : (record.progress || 0);
      let examScore = progressInfo ? progressInfo.exam_score : 0;
      let hasExamData = progressInfo ? progressInfo.has_exam_data : false;

      console.log('Record ID:', recordId);
      console.log('Progress Info:', progressInfo);
      console.log('Display Progress:', displayProgress);
      console.log('Exam Score:', examScore);
      console.log('Has Exam Data:', hasExamData);

      // Determine progress bar color
      let progressBarColor = '#6c757d'; // secondary
      if (displayProgress >= 100) {
        progressBarColor = '#198754'; // success
      } else if (displayProgress >= 75) {
        progressBarColor = '#0dcaf0'; // info
      } else if (displayProgress >= 50) {
        progressBarColor = '#ffc107'; // warning
      } else if (displayProgress >= 25) {
        progressBarColor = '#fd7e14'; // orange
      } else if (displayProgress > 0) {
        progressBarColor = '#0d6efd'; // primary
      }

      Swal.fire({
        title: '<i class="bi bi-eye text-info"></i> Training Record Details',
        html: `
          <div class="text-start">
            <div class="mb-3 p-3 bg-light rounded">
              <h6 class="text-primary mb-2"><i class="bi bi-person"></i> Employee Information</h6>
              <strong>Name:</strong> ${employee ? employee.first_name + ' ' + employee.last_name : 'Unknown'}<br>
              <strong>ID:</strong> ${record.employee_id}
            </div>
            <div class="mb-3 p-3 bg-light rounded">
              <h6 class="text-success mb-2"><i class="bi bi-book"></i> Training Information</h6>
              <strong>Course:</strong> ${trainingTitle}<br>
              <strong>Date Completed:</strong> ${record.date_completed && record.date_completed !== '1970-01-01' ? new Date(record.date_completed).toLocaleDateString() : 'Not completed'}
            </div>
            <div class="mb-3 p-3 bg-light rounded">
              <h6 class="text-warning mb-2"><i class="bi bi-graph-up"></i> Progress Status</h6>
              <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: ${displayProgress}%; background-color: ${progressBarColor}; color: white; font-weight: 600;">
                  ${displayProgress}%
                </div>
              </div>
              ${hasExamData && examScore > 0 ? `
                <div class="mb-2">
                  <small class="text-muted">
                    <i class="bi bi-mortarboard me-1"></i>Exam Score: ${examScore}% 
                    <span class="badge bg-info bg-opacity-10 text-info ms-1">Exam Progress</span>
                  </small>
                </div>
              ` : displayProgress > 0 && !hasExamData ? `
                <div class="mb-2">
                  <small class="text-muted">
                    <i class="bi bi-database me-1"></i>Progress Source: 
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">System Data</span>
                  </small>
                </div>
              ` : ''}
              <span class="badge ${displayProgress >= 100 ? 'bg-success' : displayProgress > 0 ? 'bg-primary' : 'bg-secondary'} bg-opacity-10 ${displayProgress >= 100 ? 'text-success' : displayProgress > 0 ? 'text-primary' : 'text-secondary'} fs-6 px-3 py-2">
                ${displayProgress >= 100 ? 'Completed' : displayProgress > 0 ? 'In Progress' : 'Not Started'}
              </span>
            </div>
          </div>
        `,
        width: 600,
        showCloseButton: true,
        confirmButtonText: '<i class="bi bi-check"></i> Close',
        confirmButtonColor: '#6c757d'
      });
    }

    // Add Record with Confirmation
    async function addRecordWithConfirmation() {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
        html: `
          <div class="text-start mb-3">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i> 
              <strong>Admin Password Required</strong><br>
              Please enter your admin password to add a new training record.
            </div>
          </div>
          <input type="password" id="admin-password" class="swal2-input" placeholder="Enter admin password" minlength="6">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-unlock"></i> Verify & Continue',
        cancelButtonText: '<i class="bi bi-x"></i> Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 6) {
            Swal.showValidationMessage('Password must be at least 6 characters');
            return false;
          }
          return password;
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const isValid = await verifyAdminPassword(password);
        
        if (isValid) {
          showAddRecordForm(password);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The admin password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Show Add Record Form
    function showAddRecordForm(password) {
      const employees = @json($employees);
      const trainings = @json($trainings);

      let employeeOptions = '<option value="">Select Employee</option>';
      employees.forEach(emp => {
        employeeOptions += `<option value="${emp.employee_id}">${emp.first_name} ${emp.last_name} (${emp.employee_id})</option>`;
      });

      let trainingOptions = '<option value="">Select Training</option>';
      trainings.forEach(training => {
        let title = 'Training';
        if (training.course && training.course.course_title) {
          title = training.course.course_title;
        } else if (training.title) {
          title = training.title;
        }
        trainingOptions += `<option value="${training.id}">${title}</option>`;
      });

      Swal.fire({
        title: '<i class="bi bi-plus-circle text-primary"></i> Add Training Record',
        html: `
          <form id="addRecordForm" class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Employee *</label>
              <select class="form-select" name="employee_id" required>
                ${employeeOptions}
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Training *</label>
              <select class="form-select" name="training_id" required>
                ${trainingOptions}
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Date Completed *</label>
              <input type="date" class="form-control" name="date_completed" required>
            </div>
            <input type="hidden" name="password_verification" value="${password}">
          </form>
        `,
        width: 600,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-save"></i> Add Record',
        cancelButtonText: '<i class="bi bi-x"></i> Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('addRecordForm');
          const formData = new FormData(form);
          
          if (!formData.get('employee_id')) {
            Swal.showValidationMessage('Please select an employee');
            return false;
          }
          if (!formData.get('training_id')) {
            Swal.showValidationMessage('Please select a training');
            return false;
          }
          if (!formData.get('date_completed')) {
            Swal.showValidationMessage('Please select completion date');
            return false;
          }
          
          return formData;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitAddRecord(result.value);
        }
      });
    }

    // Submit Add Record
    async function submitAddRecord(formData) {
      try {
        Swal.fire({
          title: 'Processing...',
          text: 'Adding training record',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const response = await fetch('{{ route("customer_service_sales_skills_training.store") }}', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken()
          },
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Training record added successfully',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(data.message || 'Failed to add record');
        }
      } catch (error) {
        console.error('Add record error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to add training record. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Edit Record with Confirmation
    async function editRecordWithConfirmation(recordId) {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
        html: `
          <div class="text-start mb-3">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i> 
              <strong>Admin Password Required</strong><br>
              Please enter your admin password to edit this training record.
            </div>
          </div>
          <input type="password" id="admin-password" class="swal2-input" placeholder="Enter admin password" minlength="6">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-unlock"></i> Verify & Continue',
        cancelButtonText: '<i class="bi bi-x"></i> Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 6) {
            Swal.showValidationMessage('Password must be at least 6 characters');
            return false;
          }
          return password;
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const isValid = await verifyAdminPassword(password);
        
        if (isValid) {
          showEditRecordForm(recordId, password);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The admin password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Show Edit Record Form
    function showEditRecordForm(recordId, password) {
      const recordData = @json($records->keyBy('id'));
      const employees = @json($employees);
      const trainings = @json($trainings);
      
      const record = recordData[recordId];
      if (!record) {
        Swal.fire('Error', 'Record not found', 'error');
        return;
      }

      let employeeOptions = '';
      employees.forEach(emp => {
        const selected = record.employee_id == emp.employee_id ? 'selected' : '';
        employeeOptions += `<option value="${emp.employee_id}" ${selected}>${emp.first_name} ${emp.last_name} (${emp.employee_id})</option>`;
      });

      let trainingOptions = '';
      trainings.forEach(training => {
        let title = 'Training';
        if (training.course && training.course.course_title) {
          title = training.course.course_title;
        } else if (training.title) {
          title = training.title;
        }
        const selected = record.training_id == training.id ? 'selected' : '';
        trainingOptions += `<option value="${training.id}" ${selected}>${title}</option>`;
      });

      Swal.fire({
        title: '<i class="bi bi-pencil text-warning"></i> Edit Training Record',
        html: `
          <form id="editRecordForm" class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Employee *</label>
              <select class="form-select" name="employee_id" required>
                ${employeeOptions}
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Training *</label>
              <select class="form-select" name="training_id" required>
                ${trainingOptions}
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Date Completed *</label>
              <input type="date" class="form-control" name="date_completed" value="${record.date_completed}" required>
            </div>
            <input type="hidden" name="password_verification" value="${password}">
            <input type="hidden" name="_method" value="PUT">
          </form>
        `,
        width: 600,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-save"></i> Save Changes',
        cancelButtonText: '<i class="bi bi-x"></i> Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('editRecordForm');
          const formData = new FormData(form);
          
          if (!formData.get('employee_id')) {
            Swal.showValidationMessage('Please select an employee');
            return false;
          }
          if (!formData.get('training_id')) {
            Swal.showValidationMessage('Please select a training');
            return false;
          }
          if (!formData.get('date_completed')) {
            Swal.showValidationMessage('Please select completion date');
            return false;
          }
          
          return formData;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitEditRecord(recordId, result.value);
        }
      });
    }

    // Submit Edit Record
    async function submitEditRecord(recordId, formData) {
      try {
        Swal.fire({
          title: 'Processing...',
          text: 'Updating training record',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const response = await fetch(`/customer_service_sales_skills_training/${recordId}`, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken()
          },
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Training record updated successfully',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(data.message || 'Failed to update record');
        }
      } catch (error) {
        console.error('Edit record error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to update training record. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Delete Record with Confirmation
    async function deleteRecordWithConfirmation(recordId) {
      const result = await Swal.fire({
        title: '<i class="bi bi-exclamation-triangle text-danger"></i> Delete Training Record',
        html: `
          <div class="text-start">
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle"></i> 
              <strong>Warning!</strong><br>
              This action will permanently delete the training record and cannot be undone.
            </div>
            <p><strong>Record ID:</strong> ${recordId}</p>
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-arrow-right"></i> Continue',
        cancelButtonText: '<i class="bi bi-x"></i> Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
      });

      if (result.isConfirmed) {
        const { value: password } = await Swal.fire({
          title: '<i class="bi bi-shield-lock text-danger"></i> Final Security Check',
          html: `
            <div class="text-start mb-3">
              <div class="alert alert-danger">
                <i class="bi bi-shield-exclamation"></i> 
                <strong>Admin Password Required</strong><br>
                Please enter your admin password to confirm deletion of this training record.
              </div>
            </div>
            <input type="password" id="admin-password" class="swal2-input" placeholder="Enter admin password" minlength="6">
          `,
          focusConfirm: false,
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-trash"></i> Delete Record',
          cancelButtonText: '<i class="bi bi-x"></i> Cancel',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          preConfirm: () => {
            const password = document.getElementById('admin-password').value;
            if (!password) {
              Swal.showValidationMessage('Please enter your password');
              return false;
            }
            if (password.length < 6) {
              Swal.showValidationMessage('Password must be at least 6 characters');
              return false;
            }
            return password;
          }
        });

        if (password) {
          Swal.fire({
            title: 'Verifying Password...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });

          const isValid = await verifyAdminPassword(password);
          
          if (isValid) {
            submitDeleteRecord(recordId);
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Invalid Password',
              text: 'The admin password you entered is incorrect. Please try again.',
              confirmButtonColor: '#dc3545'
            });
          }
        }
      }
    }

    // Submit Delete Record
    async function submitDeleteRecord(recordId) {
      try {
        Swal.fire({
          title: 'Processing...',
          text: 'Deleting training record',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const response = await fetch(`/customer_service_sales_skills_training/${recordId}`, {
          method: 'DELETE',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken(),
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Training record has been deleted successfully',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(data.message || 'Failed to delete record');
        }
      } catch (error) {
        console.error('Delete record error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to delete training record. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Sync Training Progress with Confirmation
    async function syncTrainingProgressWithConfirmation() {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-arrow-repeat text-success"></i> Sync Training Progress',
        html: `
          <div class="text-start mb-3">
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> 
              <strong>Sync Training Progress</strong><br>
              This will update competency gaps based on completed training records and synchronize progress across the system.
            </div>
            <div class="alert alert-warning">
              <i class="bi bi-shield-lock"></i> 
              <strong>Admin Password Required</strong><br>
              Please enter your admin password to perform this system operation.
            </div>
          </div>
          <input type="password" id="admin-password" class="swal2-input" placeholder="Enter admin password" minlength="6">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Sync Progress',
        cancelButtonText: '<i class="bi bi-x"></i> Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          if (password.length < 6) {
            Swal.showValidationMessage('Password must be at least 6 characters');
            return false;
          }
          return password;
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const isValid = await verifyAdminPassword(password);
        
        if (isValid) {
          submitSyncProgress();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The admin password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Submit Sync Progress
    async function submitSyncProgress() {
      try {
        Swal.fire({
          title: 'Syncing Progress...',
          text: 'Updating competency gaps and training progress',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const response = await fetch('/admin/course-management/sync-training-competency', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken(),
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Sync Complete!',
            text: 'Training progress has been synchronized successfully',
            timer: 3000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(data.message || 'Sync failed');
        }
      } catch (error) {
        console.error('Sync progress error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Sync Failed',
          text: error.message || 'Failed to sync training progress. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }
  </script>
</body>
</html>
