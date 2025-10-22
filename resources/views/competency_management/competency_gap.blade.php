<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <style>
    /* Card hover effects */
    .gap-card {
      transition: all 0.3s ease !important;
      border: 1px solid #e9ecef !important;
    }
    
    .gap-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
      border-color: #007bff !important;
    }
    
    /* Progress bar animations */
    .progress-bar {
      transition: width 0.6s ease;
    }
    
    /* Button group styling */
    .btn-group .btn {
      border-radius: 0.375rem !important;
      margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
      margin-right: 0;
    }
    
    /* Empty state styling */
    .bi-inbox {
      opacity: 0.3;
    }
    
    /* Employee avatar styling */
    .gap-card .card-header img {
      border: 2px solid rgba(255,255,255,0.3) !important;
    }
    
    /* Competency gap item styling */
    .competency-gap-item {
      position: relative;
    }
    
    .competency-gap-item.border-top {
      border-top: 1px solid #e9ecef !important;
      margin-top: 1rem !important;
      padding-top: 1rem !important;
    }
    
    /* Competency numbering badge */
    .competency-gap-item .badge {
      font-size: 0.7rem;
    }
    
    /* View All Gaps Button */
    .view-all-gaps-btn {
      transition: all 0.3s ease;
    }
    
    .view-all-gaps-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Toggle icon rotation */
    .view-all-gaps-btn .toggle-icon {
      transition: transform 0.3s ease;
    }
    
    .view-all-gaps-btn[aria-expanded="true"] .toggle-icon {
      transform: rotate(180deg);
    }
    
    /* Collapse animation */
    .collapse {
      transition: all 0.3s ease;
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
            <h2 class="fw-bold mb-1">Competency GAP List</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Here's your Competency Gap List.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Competency Gap</li>
          </ol>
        </nav>
      </div>
    </div>


    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Competency List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Competency List</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">ID</th>
                <th class="fw-bold">Competency Name</th>
                <th class="fw-bold">Description</th>
                <th class="fw-bold">Category</th>
                <th class="fw-bold">Rate</th>
              </tr>
            </thead>
            <tbody>
              @forelse($competencies->where('category', '!=', 'Destination Knowledge') as $index => $comp)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $comp->competency_name }}</td>
                  <td>{{ $comp->description }}</td>
                  <td>
                    <!-- Debug: {{ $comp->category }} -->
                    @php
                      // Use single color for all categories (same as card header)
                      $colorClass = 'bg-primary'; // Single blue color for all badges
                      $badgeClass = 'badge bg-primary text-white';
                      $badgeStyle = '';
                      $iconClass = '';
                    @endphp
                    <span class="{{ $badgeClass }}" @if($comp->category === 'Destination Knowledge') style="{{ $badgeStyle }}" @endif>
                      @if($comp->category === 'Destination Knowledge')
                        <i class="{{ $iconClass }}"></i>
                      @endif
                      {{ $comp->category ?? 'No Category' }}
                    </span>
                  </td>
                  <td>
                    @php
                      $rate = $comp->rate ?? 5; // Default to 5 if rate is null
                      $percentage = round(($rate/5)*100);
                    @endphp
                    <div class="d-flex align-items-center">
                      <div class="progress me-2" style="width: 80px; height: 20px;">
                        <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                      </div>
                      <span class="fw-semibold">{{ $percentage }}%</span>
                      @if($comp->rate === null)
                        <small class="text-muted ms-1" title="Default rate applied">(default)</small>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">No competencies found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Gap List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Competency Gap List</h4>
        <div class="d-flex gap-2">
          <form method="POST" action="{{ route('competency_gap_analysis.export') }}">
            @csrf
            <button type="submit" class="btn btn-success btn-sm" title="Export">
              <i class="bi bi-download"></i> Export
            </button>
          </form>
          <button class="btn btn-info btn-sm" id="autoDetectGapsBtn" title="Auto-detect competency gaps for all employees">
            <i class="bi bi-magic"></i> Auto-Detect Gaps
          </button>
          <input type="text" id="gap-search" class="form-control form-control-sm" placeholder="Search employee..." style="width: 180px;">
        </div>
      </div>
      <div class="card-body">
        @php
          // Group gaps by employee
          $groupedGaps = $gaps->groupBy('employee_id');
        @endphp
        
        @forelse($groupedGaps as $employeeId => $employeeGaps)
          @if($loop->first)
            <div class="row g-4" id="gap-cards-container">
          @endif
              <div class="col-lg-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 gap-card" style="transition: all 0.3s ease;">
                  @php
                    $firstGap = $employeeGaps->first();
                    $firstName = $firstGap->employee->first_name ?? 'Unknown';
                    $lastName = $firstGap->employee->last_name ?? 'Employee';
                    $fullName = $firstName . ' ' . $lastName;

                    // Check if profile picture exists - simplified approach
                    $profilePicUrl = null;
                    if ($firstGap->employee->profile_picture) {
                        // Direct asset URL generation - Laravel handles the storage symlink
                        $profilePicUrl = asset('storage/' . $firstGap->employee->profile_picture);
                    }

                    // Generate consistent color based on employee name for fallback
                    $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                    $employeeIdForColor = $firstGap->employee->employee_id ?? 'default';
                    $colorIndex = abs(crc32($employeeIdForColor)) % count($colors);
                    $bgColor = $colors[$colorIndex];

                    // Fallback to UI Avatars if no profile picture found
                    if (!$profilePicUrl) {
                        $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) .
                                       "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
                    }
                  @endphp
                  
                  <!-- Card Header with Employee Info -->
                  <div class="card-header bg-primary text-white border-0 py-3">
                    <div class="d-flex align-items-center">
                      <img src="{{ $profilePicUrl }}"
                           alt="{{ $firstName }} {{ $lastName }}"
                           class="rounded-circle me-3"
                           style="width: 45px; height: 45px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ $firstName }} {{ $lastName }}</h6>
                        <small class="text-dark fw-bold">{{ $employeeGaps->count() }} Competency Gap{{ $employeeGaps->count() > 1 ? 's' : '' }}</small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card-body">
                    <!-- View All Gaps Button -->
                    <div class="d-grid mb-3">
                      <button class="btn btn-outline-primary btn-sm view-all-gaps-btn" 
                              type="button" 
                              data-bs-toggle="collapse" 
                              data-bs-target="#gaps-{{ $employeeId }}" 
                              aria-expanded="false" 
                              aria-controls="gaps-{{ $employeeId }}">
                        <i class="bi bi-eye me-1"></i>View All {{ $employeeGaps->count() }} Competency Gap{{ $employeeGaps->count() > 1 ? 's' : '' }}
                        <i class="bi bi-chevron-down ms-1 toggle-icon"></i>
                      </button>
                    </div>

                    <!-- Collapsible Gaps Container -->
                    <div class="collapse" id="gaps-{{ $employeeId }}">
                      <!-- All Competencies for this Employee -->
                      @foreach($employeeGaps as $gapIndex => $gap)
                      <div class="competency-gap-item mb-4 {{ $gapIndex > 0 ? 'border-top pt-3' : '' }}">
                        <!-- Competency Name -->
                        <h6 class="card-title fw-bold text-dark mb-3">
                          <span class="badge bg-secondary me-2">{{ $gapIndex + 1 }}</span>
                          @if($gap->competency)
                            {{ $gap->competency->competency_name }}
                          @else
                            N/A
                          @endif
                        </h6>

                        @php
                          // Calculate required level percentage for this gap
                          $requiredLevel = min(5, max(0, $gap->required_level ?? 0));
                          $requiredPercentage = ($requiredLevel / 5) * 100;
                        @endphp

                        <!-- Progress Bars Section -->
                        <div class="mb-3">
                      <!-- Rate Progress -->
                      <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <small class="fw-semibold text-secondary">Rate</small>
                          <small class="fw-bold text-warning">{{ round((($gap->competency->rate ?? 0)/5)*100) }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar bg-warning" style="width: {{ (($gap->competency->rate ?? 0)/5)*100 }}%"></div>
                        </div>
                      </div>

                      <!-- Required Level -->
                      <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <small class="fw-semibold text-secondary">Required Level</small>
                          <small class="fw-bold text-info">{{ round($requiredPercentage) }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar bg-info" style="width: {{ $requiredPercentage }}%"></div>
                        </div>
                      </div>

                      <!-- Current Level -->
                      @php
                        // Use same logic as competency profiles for current level calculation
                        $competencyProfile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $gap->employee_id)
                          ->where('competency_id', $gap->competency_id)
                          ->first();

                        $currentPercentage = 0;
                        $currentLevel = 0;
                        $progressSource = 'none';

                        if ($competencyProfile) {
                          $competencyName = $gap->competency->competency_name;
                          $storedProficiency = ($competencyProfile->proficiency_level / 5) * 100;
                          $actualProgress = 0;

                          // Check if this is truly manually set (not from destination knowledge sync)
                          // For destination knowledge competencies, always use training data unless explicitly manual
                          $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

                          if ($isDestinationCompetency) {
                            // For destination competencies, always use training data (never treat as manual)
                            // These are synced from destination knowledge training system
                            $isManuallySet = false;
                          } else {
                            // For non-destination competencies, use broader manual detection
                            $isManuallySet = $competencyProfile->proficiency_level > 1 ||
                                             ($competencyProfile->proficiency_level == 1 && $competencyProfile->assessment_date &&
                                              \Carbon\Carbon::parse($competencyProfile->assessment_date)->diffInDays(now()) < 30);
                          }

                          if (stripos($competencyName, 'Destination Knowledge') !== false) {
                            // Extract location name from competency
                            $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                            $locationName = trim($locationName);

                            if (!empty($locationName)) {
                              // Find matching destination knowledge training record
                              $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $gap->employee_id)
                                ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                                ->first();

                              if ($destinationRecord) {
                                // Use the same progress calculation as destination knowledge training view
                                $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);

                                // Find matching course ID for this destination
                                $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                                $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                                // Get exam progress (same as destination training view)
                                $combinedProgress = 0;
                                if ($courseId) {
                                  $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                                }

                                // Fall back to training dashboard progress if no exam data
                                if ($combinedProgress == 0) {
                                  $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                                    ->where('course_id', $courseId)
                                    ->value('progress');
                                  $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                                }

                                $actualProgress = min(100, round($combinedProgress));
                                $progressSource = 'destination';
                              }
                            }
                          } else {
                            // For non-destination competencies, use employee training dashboard
                            $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $gap->employee_id)->get();

                            foreach ($trainingRecords as $record) {
                              $courseTitle = $record->training_title ?? '';

                              // General competency matching
                              $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                              $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                              if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                                // Get progress from this training record
                                $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($gap->employee_id, $record->course_id);
                                $trainingProgress = $record->progress ?? 0;

                                // Priority: Exam progress > Training record progress
                                $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                                $progressSource = 'training';
                                break;
                              }
                            }
                          }

                          // Use manual proficiency level if manually set, otherwise use training data
                          if ($isManuallySet) {
                            $currentPercentage = $storedProficiency;
                            $progressSource = 'manual';
                          } else {
                            $currentPercentage = $actualProgress > 0 ? $actualProgress : $storedProficiency;
                          }

                          // Convert percentage to level (1-5)
                          if ($currentPercentage >= 90) $currentLevel = 5;
                          elseif ($currentPercentage >= 70) $currentLevel = 4;
                          elseif ($currentPercentage >= 50) $currentLevel = 3;
                          elseif ($currentPercentage >= 30) $currentLevel = 2;
                          elseif ($currentPercentage > 0) $currentLevel = 1;
                          else $currentLevel = 0;

                          if ($actualProgress == 0) {
                            $progressSource = 'profile';
                          }
                        }
                      @endphp
                      <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <small class="fw-semibold text-secondary">Current Level</small>
                          <div class="d-flex align-items-center">
                            <small class="fw-bold text-success me-1">{{ round($currentPercentage) }}%</small>
                            @if($progressSource === 'manual')
                              <small class="text-warning" title="Manual proficiency level">(manual)</small>
                            @elseif($progressSource === 'destination')
                              <small class="text-success" title="From destination knowledge training">(destination)</small>
                            @elseif($progressSource === 'training')
                              <small class="text-primary" title="From employee training dashboard">(training)</small>
                            @elseif($progressSource === 'profile')
                              <small class="text-info" title="Using stored proficiency level">(profile)</small>
                            @else
                              <small class="text-muted" title="No data found">(no data)</small>
                            @endif
                          </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar bg-success" style="width: {{ $currentPercentage }}%"></div>
                        </div>
                      </div>
                    </div>

                    <!-- Gap and Status Section -->
                    <div class="row mb-3">
                      <div class="col-6">
                        @php
                          // Calculate gap using the calculated current level from above
                          $requiredLevel = min(5, max(0, $gap->required_level ?? 0));
                          // Use the calculated $currentLevel from the progress calculation above
                          $gapValue = max(0, $requiredLevel - $currentLevel);
                          $badgeClass = $gapValue > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success';
                          $gapText = $gapValue > 0 ? $gapValue . ' level(s) below' : 'No gap';
                        @endphp
                        <small class="text-muted d-block">Gap</small>
                        <span class="badge {{ $badgeClass }}">{{ $gapText }}</span>
                      </div>
                      <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        @if($gap->assigned_to_training)
                          <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i> Assigned
                          </span>
                        @else
                          <span class="badge bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock"></i> Pending
                          </span>
                        @endif
                      </div>
                    </div>

                    <!-- Expiration Date -->
                    <div class="mb-3">
                      <small class="text-muted d-block">Expiration</small>
                      @if($gap->expired_date && !empty(trim($gap->expired_date)) && trim($gap->expired_date) !== '0000-00-00 00:00:00' && trim($gap->expired_date) !== '0000-00-00')
                        @php
                          try {
                            $expiredDateRaw = trim($gap->expired_date);
                            $expiredDateObj = \Carbon\Carbon::parse($expiredDateRaw);
                            $now = \Carbon\Carbon::now();
                            $dateFormatted = $expiredDateObj->format('M d, Y');
                            $daysLeft = $now->diffInDays($expiredDateObj, false);
                            $isExpired = $now->gt($expiredDateObj);
                            $showExpiredDate = true;
                          } catch (Exception $e) {
                            $showExpiredDate = false;
                          }
                        @endphp
                        @if($showExpiredDate)
                          <div class="text-center">
                            <div class="fw-semibold">{{ $dateFormatted }}</div>
                            <div class="mt-1">
                              @if(!$isExpired)
                                <span class="badge bg-info bg-opacity-10 text-info">
                                  <i class="bi bi-clock"></i> {{ floor(abs($daysLeft)) }} day{{ floor(abs($daysLeft)) == 1 ? '' : 's' }} left
                                </span>
                              @else
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                  <i class="bi bi-exclamation-triangle"></i> Expired {{ floor(abs($daysLeft)) }} day{{ floor(abs($daysLeft)) == 1 ? '' : 's' }} ago
                                </span>
                              @endif
                            </div>
                          </div>
                        @else
                          <span class="badge bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-calendar-x"></i> Invalid Date
                          </span>
                        @endif
                      @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                          <i class="bi bi-calendar-x"></i> Not Set
                        </span>
                      @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                      <div class="btn-group" role="group">
                        <button class="btn btn-outline-info btn-sm view-gap-btn"
                                data-id="{{ $gap->id }}"
                                data-employee-name="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                                data-competency-name="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                                data-competency-description="{{ $gap->competency ? $gap->competency->description : 'N/A' }}"
                                data-competency-category="{{ $gap->competency ? $gap->competency->category : 'N/A' }}"
                                data-competency-rate="{{ $gap->competency->rate ?? 'N/A' }}"
                                data-required-level="{{ $gap->required_level }}"
                                data-current-level="{{ $currentLevel }}"
                                data-current-percentage="{{ round($currentPercentage) }}"
                                data-gap-value="{{ $gapValue }}"
                                data-progress-source="{{ $progressSource }}"
                                data-assigned-to-training="{{ $gap->assigned_to_training ? 'Yes' : 'No' }}"
                                data-expired-date="{{ $gap->expired_date }}"
                                title="View Details">
                          <i class="bi bi-eye"></i>
                        </button>
                        @if(!$gap->assigned_to_training)
                          <button class="btn btn-outline-danger btn-sm delete-gap-btn"
                                  data-id="{{ $gap->id }}"
                                  data-employee="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                                  data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                                  title="Delete Gap">
                            <i class="bi bi-trash"></i>
                          </button>
                        @else
                          <button class="btn btn-outline-secondary btn-sm" disabled title="Cannot delete - already assigned to training">
                            <i class="bi bi-lock"></i>
                          </button>
                        @endif
                      </div>
                      
                      <!-- Additional Action Buttons -->
                      <div class="mt-2">
                        @if($gap->expired_date)
                          <button class="btn btn-outline-warning btn-sm w-100 mb-1 extend-expiration-btn"
                                  data-id="{{ $gap->id }}"
                                  data-employee="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                                  data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                                  data-expired="false">
                            <i class="bi bi-clock-history"></i> Extend Expiration
                          </button>
                        @endif
                        @if(!$gap->assigned_to_training)
                          <button class="btn btn-outline-success btn-sm w-100 assign-training-btn"
                                  data-id="{{ $gap->id }}"
                                  data-employee-id="{{ $gap->employee_id }}"
                                  data-employee-name="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                                  data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                                  data-expired-date="{{ $gap->expired_date }}">
                            <i class="bi bi-calendar-plus"></i> Assign to Training
                          </button>
                        @else
                          <button class="btn btn-outline-warning btn-sm w-100 unassign-training-btn"
                                  data-id="{{ $gap->id }}"
                                  data-employee="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                                  data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                                  title="Unassign from training to allow editing">
                            <i class="bi bi-arrow-counterclockwise"></i> Unassign Training
                          </button>
                        @endif
                      </div>
                    </div>
                      </div> <!-- End competency-gap-item -->
                    @endforeach
                    </div> <!-- End collapsible gaps container -->
                  </div>
                </div>
              </div>
          @if($loop->last)
            </div>
          @endif
        @empty
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-inbox display-1 text-muted"></i>
            </div>
            <h5 class="text-muted mb-2">No Gap Records Found</h5>
            <p class="text-muted mb-3">Get started by adding your first competency gap record.</p>
            <button class="btn btn-primary" id="addGapBtn">
              <i class="bi bi-plus-lg me-1"></i> Add Your First Gap Record
            </button>
          </div>
        @endforelse
      </div>
    </div>

  </main>

  <!-- Edit Competency Modal (Single Modal) -->
  <div class="modal fade" id="editCompetencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="editCompetencyForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Competency</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="edit-competency-name" class="form-label">Competency Name*</label>
              <input id="edit-competency-name" type="text" name="competency_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="edit-description" class="form-label">Description</label>
              <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label for="edit-category" class="form-label">Category</label>
              <input id="edit-category" type="text" name="category" class="form-control">
            </div>
            <div class="mb-3">
              <label for="edit-rate" class="form-label">Rate*</label>
              <input id="edit-rate" type="number" name="rate" class="form-control" min="1" max="5" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-arrow-repeat me-1"></i> Update
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Competency Modal -->
  <div class="modal fade" id="addCompetencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" action="{{ route('admin.competency_library.store') }}">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Competency</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="add-competency-name" class="form-label">Competency Name*</label>
              <input id="add-competency-name" type="text" name="competency_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="add-description" class="form-label">Description</label>
              <textarea id="add-description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label for="add-category" class="form-label">Category</label>
              <input id="add-category" type="text" name="category" class="form-control">
            </div>
            <div class="mb-3">
              <label for="add-rate" class="form-label">Rate*</label>
              <input id="add-rate" type="number" name="rate" class="form-control" min="1" max="5" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Save
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>


  <!-- Add Gap Modal -->
  <div class="modal fade" id="addGapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="addGapForm" method="POST" action="{{ route('competency_gap_analysis.store') }}">
        @csrf
        <div class="modal-content">
          <div class="card-header modal-header">
            <h5 class="modal-title">Add New Gap Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="add-employee-id" class="form-label">Employee*</label>
                <select id="add-employee-id" name="employee_id" class="form-select" required>
                  <option value="">Select Employee</option>
                  @foreach($employees as $emp)
                    @if(is_array($emp))
                      <option value="{{ $emp['id'] }}">{{ $emp['name'] }}</option>
                    @else
                      <option value="{{ $emp->employee_id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endif
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label for="add-competency-id" class="form-label">Competency*</label>
                <select id="add-competency-id" name="competency_id" class="form-select" required>
                  <option value="">Select Competency</option>
                  @foreach($competencies as $comp)
                    <option value="{{ $comp->id }}" data-rate="{{ $comp->rate }}">
                      {{ $comp->competency_name }} (Rate: {{ $comp->rate }})
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="gap-calc-fields">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label for="gap-competency-rate" class="form-label">Competency Rate</label>
                  <input id="gap-competency-rate" type="number" class="form-control readonly-field" readonly>
                </div>
                <div class="col-md-4">
                  <label for="add-required-level" class="form-label">Required Level*</label>
                  <input id="add-required-level" type="number" name="required_level"
                    class="form-control required-level" min="1" max="5" value="5" required>
                </div>
                <div class="col-md-4">
                  <label for="add-current-level" class="form-label">Current Level*</label>
                  <input id="add-current-level" type="number" name="current_level"
                    class="form-control current-level" min="1" max="5" required>
                </div>
                <div class="col-md-4 offset-md-8">
                  <label for="add-gap" class="form-label">Gap</label>
                  <input id="add-gap" type="number" name="gap"
                    class="form-control gap-field readonly-field" readonly>
                </div>
              </div>
              <div class="row g-3 mt-2">
                <div class="col-md-6">
                  <label for="add-gap-description" class="form-label">Gap Description (Optional)</label>
                  <textarea id="add-gap-description" name="gap_description" class="form-control" rows="2" placeholder="Optional description of the competency gap..."></textarea>
                </div>
                <div class="col-md-6">
                  <label for="add-expired-date" class="form-label">Expiration Date (Optional)</label>
                  <input id="add-expired-date" type="datetime-local" name="expired_date" class="form-control">
                  <small class="form-text text-muted">Leave empty for no expiration</small>
                </div>
              </div>
            </div>

            <!-- Security Section -->
            <div class="row g-3 mt-3">
              <div class="col-12">
                <div class="alert alert-warning border-start border-warning border-4">
                  <i class="bi bi-shield-lock-fill me-2 text-warning"></i>
                  <strong>Sweet Security Verification:</strong> Please verify your password to add this gap record.
                  <div class="mt-2">
                    <small class="text-muted">
                      <i class="bi bi-info-circle me-1"></i>
                      This additional security step ensures only authorized administrators can add competency gap records.
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <label for="admin-password" class="form-label fw-semibold">
                  <i class="bi bi-key me-1"></i>Verify Your Password*
                </label>
                <input id="admin-password" type="password" name="admin_password" class="form-control" required
                       placeholder="Enter your admin password">
                <small class="form-text text-muted">Required for security verification</small>
              </div>
              <div class="col-md-6">
                <label for="confirm-admin-password" class="form-label fw-semibold">
                  <i class="bi bi-key-fill me-1"></i>Confirm Password*
                </label>
                <input id="confirm-admin-password" type="password" name="confirm_admin_password" class="form-control" required
                       placeholder="Confirm your password">
                <small class="form-text text-muted">Re-enter password for confirmation</small>
                <div id="password-match-indicator" class="mt-1" style="display: none;">
                  <small id="password-match-text"></small>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Save
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>


  <!-- Extend Expiration Modal -->
  <div class="modal fade" id="extendExpirationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-clock-history"></i> Extend Expiration Date
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <strong>Employee:</strong> <span id="extend-employee-name"></span>
          </div>
          <div class="mb-3">
            <strong>Competency:</strong> <span id="extend-competency-name"></span>
          </div>
          <div class="mb-3">
            <label for="extension-days" class="form-label">Extension Period</label>
            <select class="form-select" id="extension-days" required>
              <option value="">Select extension period</option>
              <option value="7">1 Week (7 days)</option>
              <option value="14">2 Weeks (14 days)</option>
              <option value="21">3 Weeks (21 days)</option>
              <option value="30">1 Month (30 days)</option>
            </select>
          </div>
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Note:</strong> Extending the expiration will reactivate this competency gap assignment and allow the employee to access it again.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="confirm-extend-btn">
            <i class="bi bi-arrow-clockwise"></i> Extend Expiration
          </button>
        </div>
      </div>
    </div>
  </div>


  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-trash"></i> Delete Competency Gap Record
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <strong>Employee:</strong> <span id="delete-employee-name"></span>
          </div>
          <div class="mb-3">
            <strong>Competency:</strong> <span id="delete-competency-name"></span>
          </div>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Warning:</strong> This action cannot be undone. The competency gap record will be permanently deleted.
          </div>

          <!-- Security Section for Delete -->
          <div class="row g-3 mt-3">
            <div class="col-12">
              <div class="alert alert-warning border-start border-warning border-4">
                <i class="bi bi-shield-lock-fill me-2 text-warning"></i>
                <strong>Sweet Security Verification:</strong> Please verify your password to delete this gap record.
                <div class="mt-2">
                  <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    This additional security step ensures only authorized administrators can delete competency gap records.
                  </small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label for="delete-admin-password" class="form-label fw-semibold">
                <i class="bi bi-key me-1"></i>Verify Your Password*
              </label>
              <input id="delete-admin-password" type="password" class="form-control" required
                     placeholder="Enter your admin password">
              <small class="form-text text-muted">Required for security verification</small>
            </div>
            <div class="col-md-6">
              <label for="delete-confirm-admin-password" class="form-label fw-semibold">
                <i class="bi bi-key-fill me-1"></i>Confirm Password*
              </label>
              <input id="delete-confirm-admin-password" type="password" class="form-control" required
                     placeholder="Confirm your password">
              <small class="form-text text-muted">Re-enter password for confirmation</small>
              <div id="delete-password-match-indicator" class="mt-1" style="display: none;">
                <small id="delete-password-match-text"></small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirm-delete-btn">
            <i class="bi bi-trash"></i> Delete Record
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  
  <!-- Load optional JavaScript files with error handling -->
  <script>
    // Function to safely load optional scripts
    function loadOptionalScript(src, callback) {
      const script = document.createElement('script');
      script.src = src;
      script.onload = function() {
        console.log('Successfully loaded:', src);
        if (callback) callback();
      };
      script.onerror = function() {
        console.warn('Failed to load optional script:', src);
        if (callback) callback();
      };
      document.head.appendChild(script);
    }
    
    // Load optional scripts
    loadOptionalScript('{{ asset('assets/js/admin_dashboard-script.js') }}');
    loadOptionalScript('{{ asset('js/csrf-refresh.js') }}');
  </script>
  <script>
// Sweet Alert confirmation for competency delete
function confirmDeleteCompetency() {
  return Swal.fire({
    title: 'Delete this competency?',
    text: 'This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    return result.isConfirmed;
  });
}

// Note: CSRF token auto-refresh removed to prevent 404 errors
// The token should be refreshed by the server on page load or through other means

// Enhanced Toast Notification Functions using SweetAlert2
function showSuccessToast(message, duration = 3000) {
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: duration,
    timerProgressBar: true,
    icon: 'success',
    title: message,
    background: '#d1edff',
    color: '#0f5132'
  });
}

function showWarningToast(message, duration = 4000) {
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: duration,
    timerProgressBar: true,
    icon: 'warning',
    title: message,
    background: '#fff3cd',
    color: '#664d03'
  });
}

function showErrorToast(message, duration = 4000) {
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: duration,
    timerProgressBar: true,
    icon: 'error',
    title: message,
    background: '#f8d7da',
    color: '#721c24'
  });
}

function showInfoToast(message, duration = 3000) {
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: duration,
    timerProgressBar: true,
    icon: 'info',
    title: message,
    background: '#d1ecf1',
    color: '#0c5460'
  });
}

// Backward compatibility function
function showToast(message, duration = 3000) {
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: duration,
    timerProgressBar: true,
    icon: 'info',
    title: message,
    background: '#e2e3e5',
    color: '#383d41'
  });
}

document.addEventListener('DOMContentLoaded', function () {
  // Initialize Bootstrap modals
  const editCompetencyModal = new bootstrap.Modal(document.getElementById('editCompetencyModal'));
  const addGapModal = new bootstrap.Modal(document.getElementById('addGapModal'));
  const addCompetencyModal = new bootstrap.Modal(document.getElementById('addCompetencyModal'));

  // Auto-assign button handler - Fixed to work with competency gaps
  function setupAutoAssignButtons() {
    document.querySelectorAll('.auto-assign-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const employeeId = this.getAttribute('data-employee-id');
        const button = this;

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Assigning...';

        // Get employee's competency gaps to find courses to assign
        fetch(`{{ route('admin.course_management.auto_assign', ['employeeId' => 'EMPLOYEE_ID']) }}`.replace('EMPLOYEE_ID', employeeId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            auto_assign_from_gaps: true
          })
        })
        .then(response => {
          console.log('Response status:', response.status);
          if (!response.ok) {
            return response.text().then(text => {
              console.error('Response text:', text);
              throw new Error(`HTTP ${response.status}: ${text}`);
            });
          }
          return response.json();
        })
        .then(data => {
          console.log('Auto-assign response:', data);

          // Show response message
          showToast(data.message || 'Auto-assign complete');

          // Reset button
          button.disabled = false;
          button.innerHTML = '<i class="bi bi-lightning-charge"></i> Auto-Assign Courses';

          if (data.success && data.assigned_courses && data.assigned_courses.length > 0) {
            // Redirect to employee training dashboard after successful assignment
            setTimeout(() => {
              window.location.href = '{{ route("admin.employee_trainings_dashboard.index") }}';
            }, 1500);
          } else {
            // Refresh the current view if no courses were assigned
            if (typeof refreshGapTable === 'function') {
              refreshGapTable();
            } else {
              // Fallback: reload the page
              window.location.reload();
            }
          }
        })
        .catch(error => {
          console.error('Auto-assign error:', error);
          showToast('Auto-assign failed: ' + (error.message || 'Please try again'));
          button.disabled = false;
          button.innerHTML = '<i class="bi bi-lightning-charge"></i> Auto-Assign Courses';
        });
      });
    });
  }      // ========== COMPETENCY EDIT FUNCTIONALITY ==========
      document.querySelectorAll('.edit-competency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const name = this.getAttribute('data-name');
          const description = this.getAttribute('data-description');
          const category = this.getAttribute('data-category');
          const rate = this.getAttribute('data-rate');

          // Set form action URL
          document.getElementById('editCompetencyForm').action = `/admin/competency_library/${id}`;

          // Populate form fields
          document.getElementById('edit-competency-name').value = name;
          document.getElementById('edit-description').value = description;
          document.getElementById('edit-category').value = category;
          document.getElementById('edit-rate').value = rate;

          // Show modal
          editCompetencyModal.show();
        });
      });


      // ========== GAP CALCULATION ==========
      const calculateGap = (inputElement) => {
        const container = inputElement.closest('.modal-body') || inputElement.closest('.gap-calc-fields');
        if (!container) return;

        const requiredInput = container.querySelector('.required-level');
        const currentInput = container.querySelector('.current-level');
        const gapField = container.querySelector('.gap-field');
        const rateInput = container.querySelector('[id$="-rate"]');

        if (!requiredInput || !currentInput || !gapField) return;

        const required = parseInt(requiredInput.value) || 0;
        const current = parseInt(currentInput.value) || 0;
        const maxRate = parseInt(rateInput?.value) || 5;

        // Ensure current level doesn't exceed max rate
        if (currentInput.value > maxRate) {
          currentInput.value = maxRate;
        }

        gapField.value = required - current;
      };

      document.addEventListener('input', (e) => {
        if (e.target.classList.contains('required-level') || e.target.classList.contains('current-level')) {
          calculateGap(e.target);
        }
      });

      // ========== RATE AUTO-FILL AND CURRENT LEVEL AUTO-POPULATION ==========
      document.querySelectorAll('[id$="-competency-id"]').forEach(select => {
        select.addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];
          const rate = selectedOption?.getAttribute('data-rate') || '';
          const modalBody = this.closest('.modal-body');

          if (modalBody) {
            const rateInput = modalBody.querySelector('[id$="-rate"]');
            if (rateInput) rateInput.value = rate;

            // Update max values for level inputs
            const requiredInput = modalBody.querySelector('.required-level');
            const currentInput = modalBody.querySelector('.current-level');
            if (requiredInput) requiredInput.max = rate;
            if (currentInput) currentInput.max = rate;

            // Auto-populate current level if this is the add modal
            if (this.id === 'add-competency-id') {
              fetchCurrentLevel();
            }
          }
        });
      });

      // Add event listener for employee selection in add modal
      const addEmployeeSelect = document.getElementById('add-employee-id');
      if (addEmployeeSelect) {
        addEmployeeSelect.addEventListener('change', function() {
          // Auto-populate current level when employee changes
          fetchCurrentLevel();
        });
      }

      // Function to fetch and populate current level from employee competency profile
      function fetchCurrentLevel() {
        const employeeSelect = document.getElementById('add-employee-id');
        const competencySelect = document.getElementById('add-competency-id');
        const currentLevelInput = document.getElementById('add-current-level');

        if (!employeeSelect || !competencySelect || !currentLevelInput) {
          return;
        }

        const employeeId = employeeSelect.value;
        const competencyId = competencySelect.value;

        // Only fetch if both employee and competency are selected
        if (!employeeId || !competencyId) {
          currentLevelInput.value = '';
          return;
        }

        // Show loading state
        currentLevelInput.value = '';
        currentLevelInput.placeholder = 'Loading...';
        currentLevelInput.disabled = true;

        // Make API call to fetch current competency level
        fetch('/admin/competency-gap-analysis/get-competency-data', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            employee_id: employeeId,
            competency_id: competencyId
          })
        })
        .then(response => response.json())
        .then(data => {
          // Reset input state
          currentLevelInput.placeholder = 'Current Level*';
          currentLevelInput.disabled = false;

          if (data.success && data.has_profile) {
            // Found existing competency profile - auto-populate
            currentLevelInput.value = data.current_level;
            calculateGap(currentLevelInput);
            showSuccessToast(`✅ Auto-populated current level: ${data.current_level}`, 2000);
          } else {
            // No existing profile found - leave empty for manual entry
            currentLevelInput.value = '';
            currentLevelInput.focus();
            showInfoToast('ℹ️ No existing competency profile found. Please enter current level manually.', 3000);
          }
        })
        .catch(error => {
          console.error('Error fetching competency profile:', error);

          // Reset input state on error
          currentLevelInput.placeholder = 'Current Level*';
          currentLevelInput.disabled = false;
          currentLevelInput.value = '';
          currentLevelInput.focus();

          showWarningToast('⚠️ Could not fetch existing competency data. Please enter current level manually.', 4000);
        });
      }

      // ========== SEARCH FUNCTIONALITY ==========
      const gapSearch = document.getElementById('gap-search');
      if (gapSearch) {
        gapSearch.addEventListener('input', function() {
          const searchValue = this.value.trim().toLowerCase();
          document.querySelectorAll('.gap-row').forEach(row => {
            const employeeCell = row.querySelector('.gap-employee');
            const shouldShow = employeeCell && employeeCell.textContent.toLowerCase().includes(searchValue);
            row.style.display = shouldShow ? '' : 'none';
          });
        });
      }

      // Initialize gap calculation for any pre-filled values
      document.querySelectorAll('.required-level, .current-level').forEach(input => {
        calculateGap(input);
      });




      // Handle Extend Expiration buttons
      let currentGapId = null;
      const extendExpirationModal = new bootstrap.Modal(document.getElementById('extendExpirationModal'));
      const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

      // Auto-Detect Gaps button handler
      const autoDetectGapsBtn = document.getElementById('autoDetectGapsBtn');
      if (autoDetectGapsBtn) {
        autoDetectGapsBtn.addEventListener('click', function() {
          const button = this;
          const originalHtml = button.innerHTML;


          // Show loading state
          button.disabled = true;
          button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Detecting...';

          fetch('{{ route("competency_gap_analysis.auto_detect_gaps") }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showSuccessToast(`✅ ${data.message}\n📊 Created: ${data.created} | Updated: ${data.updated} | Skipped: ${data.skipped}`, 6000);

              // Reload page to show new gaps
              setTimeout(() => {
                showInfoToast('🔄 Refreshing page to show detected gaps...', 2000);
                setTimeout(() => {
                  window.location.reload();
                }, 2500);
              }, 3000);
            } else {
              showErrorToast('❌ ' + (data.message || 'Failed to auto-detect gaps'), 4000);
            }

            // Reset button
            button.disabled = false;
            button.innerHTML = originalHtml;
          })
          .catch(error => {
            console.error('Auto-detect error:', error);
            showErrorToast('❌ Network error occurred during auto-detection', 4000);

            // Reset button
            button.disabled = false;
            button.innerHTML = originalHtml;
          });
        });
      }

      document.addEventListener('click', function(e) {
        if (e.target.closest('.extend-expiration-btn')) {
          const button = e.target.closest('.extend-expiration-btn');
          currentGapId = button.dataset.id;

          document.getElementById('extend-employee-name').textContent = button.dataset.employee;
          document.getElementById('extend-competency-name').textContent = button.dataset.competency;

          extendExpirationModal.show();
        }

        if (e.target.closest('.delete-gap-btn')) {
          const button = e.target.closest('.delete-gap-btn');
          const gapId = button.dataset.id;
          const employeeName = button.dataset.employee;
          const competencyName = button.dataset.competency;

          // Directly show delete modal with data
          currentGapId = gapId;
          document.getElementById('delete-employee-name').textContent = employeeName;
          document.getElementById('delete-competency-name').textContent = competencyName;

          // Clear password fields
          document.getElementById('delete-admin-password').value = '';
          document.getElementById('delete-confirm-admin-password').value = '';
          document.getElementById('delete-password-match-indicator').style.display = 'none';

          deleteConfirmationModal.show();
        }
      });

      // Handle extend expiration confirmation
      const confirmExtendBtn = document.getElementById('confirm-extend-btn');
      if (confirmExtendBtn) {
        confirmExtendBtn.addEventListener('click', function() {
        const extensionDays = document.getElementById('extension-days').value;

        if (!extensionDays) {
          showErrorToast('Please select an extension period', 3000);
          return;
        }

        if (!currentGapId) {
          showErrorToast('No competency gap selected', 3000);
          return;
        }

        // Show loading state
        const button = this;
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Extending...';

        fetch(`/admin/competency-gap-analysis/${currentGapId}/extend-expiration`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            extension_days: parseInt(extensionDays)
          })
        })
        .then(response => response.json())
        .then(data => {
          button.disabled = false;
          button.innerHTML = originalHtml;

          if (data.success) {
            extendExpirationModal.hide();
            showSuccessToast(`✅ Expiration extended successfully! New expiry: ${data.new_expiry}`, 5000);

            // Reload page to show updated expiration status
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            showErrorToast(`❌ Error: ${data.message}`, 4000);
          }
        })
        .catch(error => {
          console.error('Extend expiration error:', error);
          button.disabled = false;
          button.innerHTML = originalHtml;
          showErrorToast('❌ Network error occurred while extending expiration', 4000);
        });
        });
      }

      // Handle delete confirmation
      const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
      if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
        if (!currentGapId) {
          showErrorToast('No competency gap selected', 3000);
          return;
        }

        // Show loading state
        const button = this;
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch(`/admin/competency-gap-analysis/${currentGapId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          button.disabled = false;
          button.innerHTML = originalHtml;

          if (data.success) {
            deleteConfirmationModal.hide();
            showSuccessToast('✅ Competency gap record deleted successfully!', 3000);

            // Reload page to show updated list
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            showErrorToast(`❌ Error: ${data.message}`, 4000);
          }
        })
        .catch(error => {
          console.error('Delete error:', error);
          button.disabled = false;
          button.innerHTML = originalHtml;
          showErrorToast('❌ Network error occurred while deleting record', 4000);
        });
        });
      }

      // Refresh the gap table after adding a new gap
      function refreshGapTable() {
        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(res => res.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.getElementById('gap-table-body');
            if (newTbody) {
              document.getElementById('gap-table-body').innerHTML = newTbody.innerHTML;
              setupAutoAssignButtons(); // Re-attach handlers after refresh
            }
          });
      }


      // Real-time password matching feedback
      function updatePasswordMatchIndicator() {
        const password = document.getElementById('admin-password').value;
        const confirmPassword = document.getElementById('confirm-admin-password').value;
        const indicator = document.getElementById('password-match-indicator');
        const text = document.getElementById('password-match-text');

        if (!confirmPassword) {
          indicator.style.display = 'none';
          return;
        }

        indicator.style.display = 'block';

        if (password === confirmPassword) {
          text.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">Passwords match</span>';
        } else {
          text.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger fw-semibold">Passwords do not match</span>';
        }
      }

      // Real-time password matching feedback for edit
      function updateEditPasswordMatchIndicator() {
        const password = document.getElementById('edit-admin-password').value;
        const confirmPassword = document.getElementById('edit-confirm-admin-password').value;
        const indicator = document.getElementById('edit-password-match-indicator');
        const text = document.getElementById('edit-password-match-text');

        if (!confirmPassword) {
          indicator.style.display = 'none';
          return;
        }

        indicator.style.display = 'block';

        if (password === confirmPassword) {
          text.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">Passwords match</span>';
        } else {
          text.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger fw-semibold">Passwords do not match</span>';
        }
      }

      // Real-time password matching feedback for delete
      function updateDeletePasswordMatchIndicator() {
        const password = document.getElementById('delete-admin-password').value;
        const confirmPassword = document.getElementById('delete-confirm-admin-password').value;
        const indicator = document.getElementById('delete-password-match-indicator');
        const text = document.getElementById('delete-password-match-text');

        if (!confirmPassword) {
          indicator.style.display = 'none';
          return;
        }

        indicator.style.display = 'block';

        if (password === confirmPassword) {
          text.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">Passwords match</span>';
        } else {
          text.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger fw-semibold">Passwords do not match</span>';
        }
      }

      // Add event listeners for real-time validation
      const adminPasswordInput = document.getElementById('admin-password');
      const confirmAdminPasswordInput = document.getElementById('confirm-admin-password');
      if (adminPasswordInput) adminPasswordInput.addEventListener('input', updatePasswordMatchIndicator);
      if (confirmAdminPasswordInput) confirmAdminPasswordInput.addEventListener('input', updatePasswordMatchIndicator);

      // Add event listeners for edit password validation
      const editAdminPasswordInput = document.getElementById('edit-admin-password');
      const editConfirmAdminPasswordInput = document.getElementById('edit-confirm-admin-password');
      if (editAdminPasswordInput) editAdminPasswordInput.addEventListener('input', updateEditPasswordMatchIndicator);
      if (editConfirmAdminPasswordInput) editConfirmAdminPasswordInput.addEventListener('input', updateEditPasswordMatchIndicator);

      // Add event listeners for delete password validation
      const deleteAdminPasswordInput = document.getElementById('delete-admin-password');
      const deleteConfirmAdminPasswordInput = document.getElementById('delete-confirm-admin-password');
      if (deleteAdminPasswordInput) deleteAdminPasswordInput.addEventListener('input', updateDeletePasswordMatchIndicator);
      if (deleteConfirmAdminPasswordInput) deleteConfirmAdminPasswordInput.addEventListener('input', updateDeletePasswordMatchIndicator);

      // Add event listeners for verification password validation
      const verifyAdminPasswordInput = document.getElementById('verify-admin-password');
      const verifyConfirmAdminPasswordInput = document.getElementById('verify-confirm-admin-password');
      if (verifyAdminPasswordInput) verifyAdminPasswordInput.addEventListener('input', updateVerifyPasswordMatchIndicator);
      if (verifyConfirmAdminPasswordInput) verifyConfirmAdminPasswordInput.addEventListener('input', updateVerifyPasswordMatchIndicator);

      // Password validation for Add Gap Form
      function validatePasswords() {
        const password = document.getElementById('admin-password').value;
        const confirmPassword = document.getElementById('confirm-admin-password').value;

        if (!password || !confirmPassword) {
          showErrorToast('❌ Please fill in both password fields.', 3000);
          return false;
        }

        if (password !== confirmPassword) {
          showErrorToast('❌ Passwords do not match. Please try again.', 3000);
          return false;
        }

        if (password.length < 6) {
          showErrorToast('❌ Password must be at least 6 characters long.', 3000);
          return false;
        }

        return true;
      }

      // Password validation for Edit Gap Form
      function validateEditPasswords() {
        const password = document.getElementById('edit-admin-password').value;
        const confirmPassword = document.getElementById('edit-confirm-admin-password').value;

        if (!password || !confirmPassword) {
          showErrorToast('❌ Please fill in both password fields.', 3000);
          return false;
        }

        if (password !== confirmPassword) {
          showErrorToast('❌ Passwords do not match. Please try again.', 3000);
          return false;
        }

        if (password.length < 6) {
          showErrorToast('❌ Password must be at least 6 characters long.', 3000);
          return false;
        }

        return true;
      }

      // Password validation for Delete Gap Form
      function validateDeletePasswords() {
        const password = document.getElementById('delete-admin-password').value;
        const confirmPassword = document.getElementById('delete-confirm-admin-password').value;

        if (!password || !confirmPassword) {
          showErrorToast('❌ Please fill in both password fields.', 3000);
          return false;
        }

        if (password !== confirmPassword) {
          showErrorToast('❌ Passwords do not match. Please try again.', 3000);
          return false;
        }

        if (password.length < 6) {
          showErrorToast('❌ Password must be at least 6 characters long.', 3000);
          return false;
        }

        return true;
      }

      // Real-time password matching feedback for verification modal
      function updateVerifyPasswordMatchIndicator() {
        const password = document.getElementById('verify-admin-password').value;
        const confirmPassword = document.getElementById('verify-confirm-admin-password').value;
        const indicator = document.getElementById('verify-password-match-indicator');
        const text = document.getElementById('verify-password-match-text');

        if (!confirmPassword) {
          indicator.style.display = 'none';
          return;
        }

        indicator.style.display = 'block';

        if (password === confirmPassword) {
          text.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">Passwords match</span>';
        } else {
          text.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger fw-semibold">Passwords do not match</span>';
        }
      }

      // Password verification function
      function validateVerificationPasswords() {
        const password = document.getElementById('verify-admin-password').value;
        const confirmPassword = document.getElementById('verify-confirm-admin-password').value;

        if (!password || !confirmPassword) {
          showErrorToast('❌ Please fill in both password fields.', 3000);
          return false;
        }

        if (password !== confirmPassword) {
          showErrorToast('❌ Passwords do not match. Please try again.', 3000);
          return false;
        }

        if (password.length < 6) {
          showErrorToast('❌ Password must be at least 6 characters long.', 3000);
          return false;
        }

        return true;
      }

      // AJAX for Add Gap
      const addGapForm = document.getElementById('addGapForm');
      if (addGapForm) {
        addGapForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Validate passwords first
          if (!validatePasswords()) {
            return;
          }

          // Show loading state
          const submitBtn = addGapForm.querySelector('button[type="submit"]');
          const originalBtnText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

          const formData = new FormData(addGapForm);

          fetch(addGapForm.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': formData.get('_token'),
              'Accept': 'application/json',
            },
            body: formData
          })
          .then(async response => {
            let data;
            try {
              data = await response.json();
            } catch (e) {
              console.error('JSON parse error:', e);
              data = null;
            }

            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (response.ok && data && data.success) {
              // Auto-close modal immediately
              addGapModal.hide();
              showSuccessToast('✅ Gap record saved successfully!', 3000);
              // Clear form
              addGapForm.reset();
              // Reload page to show new record without delay
              window.location.reload();
            } else if (data && data.errors) {
              // Show validation errors
              const errorMessages = Object.values(data.errors).flat();
              showErrorToast('❌ Validation errors:\n' + errorMessages.join('\n'), 5000);
            } else if (data && data.message) {
              showErrorToast('❌ ' + data.message, 4000);
            } else {
              showErrorToast('❌ Error saving gap record. Please check all required fields.', 4000);
            }
          })
          .catch(error => {
            console.error('Network error:', error);
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            showErrorToast('❌ Network error occurred while saving. Please try again.', 4000);
          });
        });
      }

      // AJAX for Add Competency Form
      const addCompetencyForm = document.querySelector('#addCompetencyModal form');
      if (addCompetencyForm) {
        addCompetencyForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Show loading state
          const submitBtn = addCompetencyForm.querySelector('button[type="submit"]');
          const originalBtnText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

          const formData = new FormData(addCompetencyForm);

          fetch(addCompetencyForm.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': formData.get('_token'),
              'Accept': 'application/json',
            },
            body: formData
          })
          .then(async response => {
            let data;
            try {
              data = await response.json();
            } catch (e) {
              console.error('JSON parse error:', e);
              data = null;
            }

            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (response.ok && data && data.success) {
              // Auto-close modal immediately
              addCompetencyModal.hide();
              showSuccessToast('✅ Competency saved successfully!', 3000);
              // Clear form
              addCompetencyForm.reset();
              // Reload page to show new record without delay
              window.location.reload();
            } else if (data && data.errors) {
              // Show validation errors
              const errorMessages = Object.values(data.errors).flat();
              showErrorToast('❌ Validation errors:\n' + errorMessages.join('\n'), 5000);
            } else if (data && data.message) {
              showErrorToast('❌ ' + data.message, 4000);
            } else {
              showErrorToast('❌ Error saving competency. Please check all required fields.', 4000);
            }
          })
          .catch(error => {
            console.error('Network error:', error);
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            showErrorToast('❌ Network error occurred while saving. Please try again.', 4000);
          });
        });
      }

      // AJAX for Edit Competency Form
      const editCompetencyForm = document.getElementById('editCompetencyForm');
      if (editCompetencyForm) {
        editCompetencyForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Show loading state
          const submitBtn = editCompetencyForm.querySelector('button[type="submit"]');
          const originalBtnText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

          const formData = new FormData(editCompetencyForm);

          fetch(editCompetencyForm.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': formData.get('_token'),
              'Accept': 'application/json',
            },
            body: formData
          })
          .then(async response => {
            let data;
            try {
              data = await response.json();
            } catch (e) {
              console.error('JSON parse error:', e);
              data = null;
            }

            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (response.ok && data && data.success) {
              // Auto-close modal immediately
              editCompetencyModal.hide();
              showSuccessToast('✅ Competency updated successfully!', 3000);
              // Reload page to show updated record
              window.location.reload();
            } else if (data && data.errors) {
              // Show validation errors
              const errorMessages = Object.values(data.errors).flat();
              showErrorToast('❌ Validation errors:\n' + errorMessages.join('\n'), 5000);
            } else if (data && data.message) {
              showErrorToast('❌ ' + data.message, 4000);
            } else {
              showErrorToast('❌ Error updating competency. Please check all required fields.', 4000);
            }
          })
          .catch(error => {
            console.error('Network error:', error);
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            showErrorToast('❌ Network error occurred while updating. Please try again.', 4000);
          });
        });
      }

      // Enhanced toast notification function with type support
      function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
          toastContainer = document.createElement('div');
          toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
          toastContainer.style.zIndex = '9999';
          document.body.appendChild(toastContainer);
        }

        // Determine toast styling based on type
        let bgClass, iconClass, textClass;
        switch(type) {
          case 'success':
            bgClass = 'bg-success';
            iconClass = 'fas fa-check-circle';
            textClass = 'text-white';
            break;
          case 'error':
            bgClass = 'bg-danger';
            iconClass = 'fas fa-exclamation-triangle';
            textClass = 'text-white';
            break;
          case 'warning':
            bgClass = 'bg-warning';
            iconClass = 'fas fa-exclamation-circle';
            textClass = 'text-dark';
            break;
          default:
            bgClass = 'bg-info';
            iconClass = 'fas fa-info-circle';
            textClass = 'text-white';
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center ${textClass} ${bgClass} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">
              <i class="${iconClass} me-2"></i>
              ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        `;

        toastContainer.appendChild(toast);

        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();

        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
          toast.remove();
        });

        // Also show SweetAlert popup for important messages
        if (type === 'success' || type === 'error') {
          Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Assignment Successful!' : 'Assignment Failed!',
            text: message.replace(/[✅❌]/g, '').trim(),
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
          });
        }
      }

      // Helper functions for handling specific messages
      function handleAutoAssignResponse(data) {
        if (data.message === 'No new courses to assign.') {
          if (!window.noCoursesAlerted) {
            showToast(data.message);
            window.noCoursesAlerted = true;
          }
        } else {
          showToast(data.message || 'Auto-assign complete.');
        }
      }

      function handleAssignmentResponse(data) {
        if (data.message === 'Training already assigned for this destination.') {
          if (!window.trainingAssignedAlerted) {
            showToast(data.message);
            window.trainingAssignedAlerted = true;
          }
        } else {
          showToast(data.message || 'Assignment complete.');
        }
      }

      // Fix Expired Dates button handler
      const fixExpiredDatesBtn = document.getElementById('fixExpiredDatesBtn');
      if (fixExpiredDatesBtn) {
        fixExpiredDatesBtn.addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;

        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Fixing...';

        // Get current CSRF token and make the request
        const currentToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!currentToken) {
          console.error('CSRF token not found');
          btn.disabled = false;
          btn.innerHTML = originalText;
          showToast('❌ Security token not found. Please refresh the page.');
          return;
        }

        // Make the request with current token
        fetch('{{ route("competency_gap_analysis.fix_expired_dates") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': currentToken
          }
        })
        .then(response => response.json())
        .then(data => {
          // Reset button state
          btn.disabled = false;
          btn.innerHTML = originalText;

          if (data.success) {
            showToast(`✅ Fixed expiration dates for ${data.updated_count} competency gap records`);
            // Reload page to show updated dates
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            showToast('❌ ' + (data.message || 'Error fixing expiration dates'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          // Reset button state
          btn.disabled = false;
          btn.innerHTML = originalText;
          showToast('❌ Network error occurred while fixing dates');
        });
        });
      }

      // Assign to Training button handler with SweetAlert and password verification
      document.addEventListener('click', function(e) {
        if (e.target.closest('.assign-training-btn')) {
          const btn = e.target.closest('.assign-training-btn');
          const gapId = btn.getAttribute('data-id');
          const employeeId = btn.getAttribute('data-employee-id');
          const employeeName = btn.getAttribute('data-employee-name');
          const competency = btn.getAttribute('data-competency');
          const expiredDate = btn.getAttribute('data-expired-date');
          
          assignTrainingWithConfirmation(gapId, employeeId, employeeName, competency, expiredDate);
        }
        
        // Unassign from Training button handler with SweetAlert and password verification
        if (e.target.closest('.unassign-training-btn')) {
          const btn = e.target.closest('.unassign-training-btn');
          const gapId = btn.getAttribute('data-id');
          const employeeName = btn.getAttribute('data-employee');
          const competency = btn.getAttribute('data-competency');
          
          unassignTrainingWithConfirmation(gapId, employeeName, competency);
        }
      });

      // ========== VIEW GAP DETAILS FUNCTIONALITY ==========
      document.querySelectorAll('.view-gap-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const gapData = {
            id: this.getAttribute('data-id'),
            employeeName: this.getAttribute('data-employee-name'),
            competencyName: this.getAttribute('data-competency-name'),
            competencyDescription: this.getAttribute('data-competency-description'),
            competencyCategory: this.getAttribute('data-competency-category'),
            competencyRate: this.getAttribute('data-competency-rate'),
            requiredLevel: this.getAttribute('data-required-level'),
            currentLevel: this.getAttribute('data-current-level'),
            currentPercentage: this.getAttribute('data-current-percentage'),
            gapValue: this.getAttribute('data-gap-value'),
            progressSource: this.getAttribute('data-progress-source'),
            assignedToTraining: this.getAttribute('data-assigned-to-training'),
            expiredDate: this.getAttribute('data-expired-date')
          };
          
          viewGapDetails(gapData);
        });
      });
      
      // View Gap Details with SweetAlert
      function viewGapDetails(gapData) {
        // Format expiration date
        let expirationInfo = 'Not Set';
        if (gapData.expiredDate && gapData.expiredDate !== 'null' && gapData.expiredDate.trim() !== '') {
          try {
            const expDate = new Date(gapData.expiredDate);
            const now = new Date();
            const isExpired = now > expDate;
            const daysDiff = Math.ceil((expDate - now) / (1000 * 60 * 60 * 24));
            
            expirationInfo = `
              <div class="d-flex align-items-center">
                <strong>${expDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</strong>
                <span class="badge ms-2 ${
                  isExpired ? 'bg-danger' : (daysDiff <= 7 ? 'bg-warning' : 'bg-success')
                }">
                  ${isExpired ? `Expired ${Math.abs(daysDiff)} days ago` : `${daysDiff} days left`}
                </span>
              </div>
            `;
          } catch (e) {
            expirationInfo = 'Invalid Date';
          }
        }
        
        // Format progress source
        const sourceLabels = {
          'manual': '<span class="badge bg-warning"><i class="bi bi-pencil"></i> Manual Entry</span>',
          'destination': '<span class="badge bg-info"><i class="bi bi-geo-alt"></i> Destination Training</span>',
          'training': '<span class="badge bg-primary"><i class="bi bi-book"></i> Training Dashboard</span>',
          'profile': '<span class="badge bg-secondary"><i class="bi bi-person"></i> Competency Profile</span>',
          'none': '<span class="badge bg-light text-dark"><i class="bi bi-question"></i> No Data</span>'
        };
        
        const progressSourceLabel = sourceLabels[gapData.progressSource] || sourceLabels['none'];
        
        // Determine gap status color
        const gapValue = parseInt(gapData.gapValue) || 0;
        const gapStatusColor = gapValue > 0 ? 'text-danger' : 'text-success';
        const gapStatusIcon = gapValue > 0 ? 'bi-exclamation-triangle' : 'bi-check-circle';
        const gapStatusText = gapValue > 0 ? `${gapValue} level(s) below required` : 'No gap - meets requirement';
        
        Swal.fire({
          title: '<i class="bi bi-eye text-info"></i> Competency Gap Details',
          html: `
            <div class="text-start">
              <!-- Employee Information -->
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <h6 class="mb-0"><i class="bi bi-person-circle me-2"></i>Employee Information</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-12">
                      <strong><i class="bi bi-person me-1"></i>Name:</strong> ${gapData.employeeName}
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Competency Information -->
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <h6 class="mb-0"><i class="bi bi-award me-2"></i>Competency Information</h6>
                </div>
                <div class="card-body">
                  <div class="row g-2">
                    <div class="col-12">
                      <strong><i class="bi bi-bookmark me-1"></i>Name:</strong> ${gapData.competencyName}
                    </div>
                    <div class="col-12">
                      <strong><i class="bi bi-card-text me-1"></i>Description:</strong> 
                      <div class="text-muted small mt-1">${gapData.competencyDescription || 'No description available'}</div>
                    </div>
                    <div class="col-6">
                      <strong><i class="bi bi-tag me-1"></i>Category:</strong> ${gapData.competencyCategory}
                    </div>
                    <div class="col-6">
                      <strong><i class="bi bi-star me-1"></i>Rate:</strong> ${gapData.competencyRate}/5
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Gap Analysis -->
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Gap Analysis</h6>
                </div>
                <div class="card-body">
                  <div class="row g-2">
                    <div class="col-4">
                      <strong><i class="bi bi-arrow-up-circle me-1"></i>Required Level:</strong>
                      <div class="mt-1">
                        <span class="badge bg-info">${gapData.requiredLevel}/5</span>
                        <div class="progress mt-1" style="height: 8px;">
                          <div class="progress-bar bg-info" style="width: ${(gapData.requiredLevel/5)*100}%"></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <strong><i class="bi bi-speedometer me-1"></i>Current Level:</strong>
                      <div class="mt-1">
                        <span class="badge bg-success">${gapData.currentLevel}/5 (${gapData.currentPercentage}%)</span>
                        <div class="progress mt-1" style="height: 8px;">
                          <div class="progress-bar bg-success" style="width: ${gapData.currentPercentage}%"></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <strong><i class="bi bi-gap me-1"></i>Gap Status:</strong>
                      <div class="mt-1">
                        <div class="${gapStatusColor}">
                          <i class="${gapStatusIcon} me-1"></i>${gapStatusText}
                        </div>
                      </div>
                    </div>
                    <div class="col-12 mt-2">
                      <strong><i class="bi bi-database me-1"></i>Progress Source:</strong> ${progressSourceLabel}
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Training Assignment Status -->
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Training Assignment</h6>
                </div>
                <div class="card-body">
                  <div class="row g-2">
                    <div class="col-6">
                      <strong><i class="bi bi-check-square me-1"></i>Assigned to Training:</strong>
                      <div class="mt-1">
                        ${gapData.assignedToTraining === 'Yes' ? 
                          '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Yes</span>' : 
                          '<span class="badge bg-warning"><i class="bi bi-clock"></i> No</span>'
                        }
                      </div>
                    </div>
                    <div class="col-6">
                      <strong><i class="bi bi-calendar-x me-1"></i>Expiration Date:</strong>
                      <div class="mt-1">${expirationInfo}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `,
          width: '700px',
          showConfirmButton: true,
          confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Close',
          confirmButtonColor: '#6c757d',
          customClass: {
            popup: 'text-start'
          }
        });
      }
      
      // ========== SWEETALERT FUNCTIONS FOR COMPETENCY GAP ACTIONS ==========
      
      // Assign Training with SweetAlert and Password Verification
      function assignTrainingWithConfirmation(gapId, employeeId, employeeName, competency, expiredDate) {
        Swal.fire({
          title: '<i class="bi bi-calendar-plus text-success"></i> Assign Training',
          html: `
            <div class="text-start">
              <div class="alert alert-info border-start border-info border-4">
                <i class="bi bi-info-circle me-2 text-info"></i>
                <strong>Training Assignment Details:</strong>
                <div class="mt-2">
                  <small class="text-muted">
                    <i class="bi bi-person me-1"></i><strong>Employee:</strong> ${employeeName}<br>
                    <i class="bi bi-award me-1"></i><strong>Competency:</strong> ${competency}<br>
                    <i class="bi bi-calendar me-1"></i><strong>Expiration:</strong> ${expiredDate || 'Not set'}
                  </small>
                </div>
              </div>
              
              <div class="alert alert-warning border-start border-warning border-4 mt-3">
                <i class="bi bi-shield-lock-fill me-2 text-warning"></i>
                <strong>Security Verification Required:</strong>
                <div class="mt-2">
                  <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Please verify your admin password to assign this training.
                  </small>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="assign-password" class="form-label fw-semibold">
                  <i class="bi bi-key me-1"></i>Admin Password*
                </label>
                <input id="assign-password" type="password" class="form-control" placeholder="Enter your admin password" required>
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-calendar-plus me-1"></i>Assign Training',
          cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
          confirmButtonColor: '#198754',
          cancelButtonColor: '#6c757d',
          width: '500px',
          preConfirm: () => {
            const password = document.getElementById('assign-password').value;
            if (!password) {
              Swal.showValidationMessage('Please enter your admin password');
              return false;
            }
            if (password.length < 6) {
              Swal.showValidationMessage('Password must be at least 6 characters long');
              return false;
            }
            return { password };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitTrainingAssignment(gapId, employeeId, competency, expiredDate, result.value.password);
          }
        });
      }
      
      // Submit Training Assignment
      function submitTrainingAssignment(gapId, employeeId, competency, expiredDate, password) {
        Swal.fire({
          title: 'Processing...',
          html: '<i class="bi bi-hourglass-split"></i> Assigning training to employee...',
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
            Swal.showLoading();
          }
        });
        
        fetch('{{ route("competency_gap_analysis.assign_to_training") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            gap_id: gapId,
            employee_id: employeeId,
            competency: competency,
            expired_date: expiredDate,
            admin_password: password
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Training Assigned Successfully!',
              html: `
                <div class="text-start">
                  <div class="alert alert-success border-start border-success border-4">
                    <i class="bi bi-check-circle me-2 text-success"></i>
                    <strong>Assignment Complete:</strong>
                    <div class="mt-2">
                      <small class="text-muted">
                        ${data.message || 'Training has been successfully assigned to the employee.'}
                      </small>
                    </div>
                  </div>
                </div>
              `,
              timer: 3000,
              timerProgressBar: true,
              showConfirmButton: false
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Assignment Failed',
              html: `
                <div class="alert alert-danger border-start border-danger border-4">
                  <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                  <strong>Error:</strong> ${data.message || 'Failed to assign training'}
                </div>
              `,
              confirmButtonText: 'Try Again',
              confirmButtonColor: '#dc3545'
            });
          }
        })
        .catch(error => {
          console.error('Assignment error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Network Error',
            html: `
              <div class="alert alert-danger border-start border-danger border-4">
                <i class="bi bi-wifi-off me-2 text-danger"></i>
                <strong>Connection Error:</strong> Unable to assign training. Please check your connection and try again.
              </div>
            `,
            confirmButtonText: 'Retry',
            confirmButtonColor: '#dc3545'
          });
        });
      }
      
      // Unassign Training with SweetAlert and Password Verification
      function unassignTrainingWithConfirmation(gapId, employeeName, competency) {
        Swal.fire({
          title: '<i class="bi bi-arrow-counterclockwise text-warning"></i> Unassign Training',
          html: `
            <div class="text-start">
              <div class="alert alert-warning border-start border-warning border-4">
                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                <strong>Unassignment Details:</strong>
                <div class="mt-2">
                  <small class="text-muted">
                    <i class="bi bi-person me-1"></i><strong>Employee:</strong> ${employeeName}<br>
                    <i class="bi bi-award me-1"></i><strong>Competency:</strong> ${competency}<br>
                    <i class="bi bi-info-circle me-1"></i><strong>Effect:</strong> This will allow the record to be edited again
                  </small>
                </div>
              </div>
              
              <div class="alert alert-danger border-start border-danger border-4 mt-3">
                <i class="bi bi-shield-lock-fill me-2 text-danger"></i>
                <strong>Security Verification Required:</strong>
                <div class="mt-2">
                  <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Please verify your admin password to unassign this training.
                  </small>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="unassign-password" class="form-label fw-semibold">
                  <i class="bi bi-key me-1"></i>Admin Password*
                </label>
                <input id="unassign-password" type="password" class="form-control" placeholder="Enter your admin password" required>
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i>Unassign Training',
          cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
          confirmButtonColor: '#ffc107',
          cancelButtonColor: '#6c757d',
          width: '500px',
          preConfirm: () => {
            const password = document.getElementById('unassign-password').value;
            if (!password) {
              Swal.showValidationMessage('Please enter your admin password');
              return false;
            }
            if (password.length < 6) {
              Swal.showValidationMessage('Password must be at least 6 characters long');
              return false;
            }
            return { password };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitTrainingUnassignment(gapId, result.value.password);
          }
        });
      }
      
      // Submit Training Unassignment
      function submitTrainingUnassignment(gapId, password) {
        Swal.fire({
          title: 'Processing...',
          html: '<i class="bi bi-hourglass-split"></i> Unassigning training from employee...',
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
            Swal.showLoading();
          }
        });
        
        fetch(`/admin/competency-gap-analysis/${gapId}/unassign-training`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            admin_password: password
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const removedCount = data.removed_count || 0;
            Swal.fire({
              icon: 'success',
              title: 'Training Unassigned Successfully!',
              html: `
                <div class="text-start">
                  <div class="alert alert-success border-start border-success border-4">
                    <i class="bi bi-check-circle me-2 text-success"></i>
                    <strong>Unassignment Complete:</strong>
                    <div class="mt-2">
                      <small class="text-muted">
                        ${data.message || 'Training has been successfully unassigned. The record can now be edited.'}
                      </small>
                      ${removedCount > 0 ? `<br><small class="text-info"><i class="bi bi-info-circle me-1"></i>Removed ${removedCount} related training records from all views.</small>` : ''}
                    </div>
                  </div>
                  <div class="alert alert-info border-start border-info border-4 mt-2">
                    <i class="bi bi-arrow-clockwise me-2 text-info"></i>
                    <small><strong>Note:</strong> Training counts will be updated automatically. The page will refresh to show current data.</small>
                  </div>
                </div>
              `,
              timer: 4000,
              timerProgressBar: true,
              showConfirmButton: false
            }).then(() => {
              // Try to refresh dashboard counts via AJAX first, then reload
              refreshDashboardCounts().finally(() => {
                // Force a complete page reload to refresh all counts
                setTimeout(() => {
                  window.location.reload(true);
                }, 1000);
              });
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Unassignment Failed',
              html: `
                <div class="alert alert-danger border-start border-danger border-4">
                  <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                  <strong>Error:</strong> ${data.message || 'Failed to unassign training'}
                </div>
              `,
              confirmButtonText: 'Try Again',
              confirmButtonColor: '#dc3545'
            });
          }
        })
        .catch(error => {
          console.error('Unassignment error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Network Error',
            html: `
              <div class="alert alert-danger border-start border-danger border-4">
                <i class="bi bi-wifi-off me-2 text-danger"></i>
                <strong>Connection Error:</strong> Unable to unassign training. Please check your connection and try again.
              </div>
            `,
            confirmButtonText: 'Retry',
            confirmButtonColor: '#dc3545'
          });
        });
      }
      
      // Re-attach event listeners after dynamic content updates
      function reattachEventListeners() {
        // Re-attach view gap button listeners
        document.querySelectorAll('.view-gap-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const gapData = {
              id: this.getAttribute('data-id'),
              employeeName: this.getAttribute('data-employee-name'),
              competencyName: this.getAttribute('data-competency-name'),
              competencyDescription: this.getAttribute('data-competency-description'),
              competencyCategory: this.getAttribute('data-competency-category'),
              competencyRate: this.getAttribute('data-competency-rate'),
              requiredLevel: this.getAttribute('data-required-level'),
              currentLevel: this.getAttribute('data-current-level'),
              currentPercentage: this.getAttribute('data-current-percentage'),
              gapValue: this.getAttribute('data-gap-value'),
              progressSource: this.getAttribute('data-progress-source'),
              assignedToTraining: this.getAttribute('data-assigned-to-training'),
              expiredDate: this.getAttribute('data-expired-date')
            };
            
            viewGapDetails(gapData);
          });
        });
        
        // Re-attach other button listeners as needed
        if (typeof setupAutoAssignButtons === 'function') {
          setupAutoAssignButtons();
        }
      }
      
      // Function to refresh gap table (placeholder)
      function refreshGapTable() {
        console.log('Refreshing gap table...');
        // Simple page reload as fallback
        window.location.reload();
      }
      
      // Function to refresh dashboard counts via AJAX
      function refreshDashboardCounts() {
        // Check if CSRF token exists
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
          console.warn('CSRF token not found, skipping dashboard refresh');
          return Promise.resolve();
        }
        
        // Try both endpoints to ensure counts are updated
        const endpoints = [
          '/employee/my-trainings/get-counts',
          '/employee/dashboard/get-counts'
        ];
        
        return Promise.all(endpoints.map(endpoint => 
          fetch(endpoint, {
            method: 'GET',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          })
          .then(response => {
            if (!response.ok) {
              console.warn(`Endpoint ${endpoint} returned ${response.status}`);
              return null;
            }
            return response.json();
          })
          .catch(error => {
            console.warn(`Error fetching from ${endpoint}:`, error);
            return null;
          })
        ))
        .then(results => {
          // Use the first successful result
          const data = results.find(result => result && result.success);
          
          if (data && data.counts) {
            // Try multiple selectors to find dashboard count elements
            const selectors = [
              '[data-count="upcoming"]',
              '.upcoming-count',
              '#upcoming-count',
              '.card-body h2:contains("' + data.counts.upcoming + '")',
              '.card:contains("Upcoming") .card-body h2',
              '.card:contains("Upcoming") h2'
            ];
            
            // Update upcoming count
            selectors.forEach(selector => {
              try {
                const element = document.querySelector(selector);
                if (element) {
                  element.textContent = data.counts.upcoming;
                  console.log(`Updated upcoming count via selector: ${selector}`);
                }
              } catch (e) {
                // Ignore selector errors
              }
            });
            
            // Also try to find and update by card content
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
              const cardText = card.textContent.toLowerCase();
              if (cardText.includes('upcoming')) {
                const countElement = card.querySelector('h2, .h2, .card-title, .display-4, .fw-bold');
                if (countElement && /^\d+$/.test(countElement.textContent.trim())) {
                  countElement.textContent = data.counts.upcoming;
                  console.log('Updated upcoming count in card:', countElement);
                }
              }
            });
            
            console.log('Dashboard counts refresh attempted:', data.counts);
          }
        })
        .catch(error => {
          console.warn('Could not refresh dashboard counts:', error);
        });
      }

      // Handle View All Gaps button toggle
      document.addEventListener('click', function(e) {
        if (e.target.closest('.view-all-gaps-btn')) {
          const btn = e.target.closest('.view-all-gaps-btn');
          const targetId = btn.getAttribute('data-bs-target');
          const collapseElement = document.querySelector(targetId);
          
          // Update button text and icon when collapsed/expanded
          collapseElement.addEventListener('shown.bs.collapse', function() {
            const gapCount = btn.textContent.match(/\d+/)[0];
            btn.innerHTML = `<i class="bi bi-eye-slash me-1"></i>Hide ${gapCount} Competency Gap${gapCount > 1 ? 's' : ''} <i class="bi bi-chevron-up ms-1 toggle-icon"></i>`;
            btn.setAttribute('aria-expanded', 'true');
          });
          
          collapseElement.addEventListener('hidden.bs.collapse', function() {
            const gapCount = btn.textContent.match(/\d+/)[0];
            btn.innerHTML = `<i class="bi bi-eye me-1"></i>View All ${gapCount} Competency Gap${gapCount > 1 ? 's' : ''} <i class="bi bi-chevron-down ms-1 toggle-icon"></i>`;
            btn.setAttribute('aria-expanded', 'false');
          });
        }
      });

      // Call reattach function after page loads
      reattachEventListeners();
    });
  </script>
</body>
</html>
