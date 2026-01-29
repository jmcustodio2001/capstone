<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Competency Details - {{ isset($competencyTracker['competency']) ? $competencyTracker['competency']['competency_name'] : ($competencyTracker['competency_name'] ?? 'Unknown') }}</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    .competency-detail-card {
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      border: none;
    }
    .detail-header {
      background: #f8f9fa;
      color: #333;
      border-radius: 15px 15px 0 0;
      padding: 1.5rem 2rem;
      border-bottom: 1px solid #dee2e6;
    }
    .progress-circle {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
      margin: 0 auto;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    .level-display {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 1rem;
    }
    .star-large {
      font-size: 1.5rem;
      color: #ffc107;
    }
    .star-large.empty {
      color: #e0e0e0;
    }
    .feedback-card {
      background: #f8f9fa;
      color: #333;
      border-radius: 0;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #dee2e6;
    }
    .training-card {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      border-radius: 12px;
      padding: 1.5rem;
    }
    .timeline-item {
      border-left: 3px solid #667eea;
      padding-left: 1.5rem;
      margin-bottom: 1.5rem;
      position: relative;
    }
    .timeline-item::before {
      content: '';
      width: 12px;
      height: 12px;
      background: #667eea;
      border-radius: 50%;
      position: absolute;
      left: -7.5px;
      top: 0;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

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
            <h2 class="fw-bold mb-1">{{ isset($competencyTracker['competency']) ? $competencyTracker['competency']['competency_name'] : ($competencyTracker['competency_name'] ?? 'Competency Details') }}</h2>
            <p class="text-muted mb-0">
              Detailed view of your competency progress and development plan.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employee.competency_profile.index') }}" class="text-decoration-none">Competency Tracker</a></li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Competency Overview -->
    <div class="competency-detail-card card mb-4">
      <div class="card-header detail-header">
        <div class="row align-items-center">
          <div class="col-md-8">
            <!-- Employee Profile Section -->
            <div class="d-flex align-items-center mb-3">
              @php
                $employee = Auth::guard('employee')->user();
                $firstName = $employee->first_name ?? 'Unknown';
                $lastName = $employee->last_name ?? 'Employee';
                $fullName = trim($firstName . ' ' . $lastName);
                
                // Profile picture logic - robust approach
                $profilePicUrl = null;
                if ($employee && !empty($employee->profile_picture)) {
                    $pic = $employee->profile_picture;
                    if (strpos($pic, 'http') === 0) {
                        $profilePicUrl = $pic;
                    } elseif (strpos($pic, 'storage/') === 0) {
                        $profilePicUrl = asset($pic);
                    } else {
                        $profilePicUrl = asset('storage/' . ltrim($pic, '/'));
                    }
                }

                // Generate consistent color based on employee ID for fallback
                $employeeId = $employee->employee_id ?? 'EMP';
                $colors = ['FF9A56', 'FF6B9D', '4ECDC4', '45B7D1', 'FFA726', 'AB47BC', 'EF5350', '66BB6A', 'FFCA28', '26A69A'];
                $colorIndex = abs(crc32($employeeId)) % count($colors);
                $bgColor = $colors[$colorIndex];
                
                // Fallback to UI Avatars if needed
                if (!$profilePicUrl) {
                  $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . "&background={$bgColor}&color=ffffff&size=128&bold=true&rounded=true";
                }
              @endphp
              
              <div class="avatar-lg me-3">
                <img src="{{ $profilePicUrl }}" 
                     class="rounded-circle" 
                     style="width: 60px; height: 60px; object-fit: cover;"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($fullName) }}&background={{ $bgColor }}&color=ffffff&size=128&bold=true&rounded=true'">
              </div>
              <div>
                <h4 class="mb-1 fw-bold">{{ $firstName }} {{ $lastName }}</h4>
                <p class="mb-0 text-muted">{{ $employee->employee_id ?? 'N/A' }} • {{ $employee->position ?? 'Employee' }}</p>
              </div>
            </div>
            
            <h3 class="mb-2">{{ isset($competencyTracker['competency']) ? $competencyTracker['competency']['competency_name'] : ($competencyTracker['competency_name'] ?? 'Unknown Competency') }}</h3>
            <p class="mb-3 opacity-90">{{ isset($competencyTracker['competency']) ? $competencyTracker['competency']['description'] : 'No description available' }}</p>
            <div class="d-flex align-items-center gap-3">
              <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-tag me-1"></i>{{ isset($competencyTracker['competency']) ? $competencyTracker['competency']['category'] : 'General' }}
              </span>
              <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-calendar me-1"></i>{{ $competencyTracker['status'] ?? 'Active' }}
              </span>
            </div>
          </div>
          <div class="col-md-4 text-center">
            <div class="progress-circle progress-{{ strtolower(str_replace(' ', '-', $competencyTracker['progress_status'] ?? 'needs-improvement')) }}">
              {{ number_format($competencyTracker['progress_percentage'] ?? 0, 0) }}%
            </div>
            <p class="mt-2 mb-0 opacity-90">Overall Progress</p>
          </div>
        </div>
      </div>
      
      <div class="card-body p-4">
        <div class="row">
          <div class="col-md-6">
            <h5 class="mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>Current Level</h5>
            <div class="level-display">
              @for($i = 1; $i <= 5; $i++)
                <i class="bi bi-star{{ $i <= ($competencyTracker['current_level'] ?? 0) ? '-fill' : '' }} star-large{{ $i <= ($competencyTracker['current_level'] ?? 0) ? '' : ' empty' }}"></i>
              @endfor
              <span class="fs-4 fw-bold text-primary ms-2">{{ $competencyTracker['current_level'] ?? 0 }}/5</span>
            </div>
            <p class="text-muted">Your current proficiency level</p>
          </div>
          
          <div class="col-md-6">
            <h5 class="mb-3"><i class="bi bi-target me-2 text-success"></i>Target Level</h5>
            <div class="level-display">
              @for($i = 1; $i <= 5; $i++)
                <i class="bi bi-star{{ $i <= ($competencyTracker['target_level'] ?? 5) ? '-fill' : '' }} star-large{{ $i <= ($competencyTracker['target_level'] ?? 5) ? '' : ' empty' }}"></i>
              @endfor
              <span class="fs-4 fw-bold text-success ms-2">{{ $competencyTracker['target_level'] ?? 5 }}/5</span>
            </div>
            <p class="text-muted">Target proficiency level</p>
          </div>
        </div>
        
        <div class="row mt-4">
          <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
              <h4 class="text-primary mb-1">{{ $competencyTracker['gap_score'] ?? 0 }}</h4>
              <small class="text-muted">Gap Score</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
              <h4 class="text-warning mb-1">{{ $competencyTracker['gap_status'] ?? 'Moderate' }}</h4>
              <small class="text-muted">Gap Status</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
              <h4 class="text-info mb-1">{{ $competencyTracker['promotion_path_alignment'] ?? 'N/A' }}</h4>
              <small class="text-muted">Promotion Alignment</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Manager Feedback & Training Recommendations -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header feedback-card">
            <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i>Manager Feedback</h5>
          </div>
          <div class="card-body">
            <div data-training-content>
              @if($competencyTracker['manager_feedback'] ?? null)
                <p class="mb-0">{{ $competencyTracker['manager_feedback'] ?? '' }}</p>
              @else
                <p class="mb-0 text-muted">No feedback provided yet. Your manager will provide feedback during your next review.</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline & Deadlines -->
    <div class="competency-detail-card card">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Development Timeline</h5>
      </div>
      <div class="card-body">
        <div class="timeline-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1">Last Assessment</h6>
              <p class="text-muted mb-0">
                {{ ($competencyTracker['last_assessment_date'] ?? null) ? \Carbon\Carbon::parse($competencyTracker['last_assessment_date'])->format('M d, Y') : 'Not assessed yet' }}
              </p>
            </div>
            <span class="badge bg-primary">Completed</span>
          </div>
        </div>
        
        <div class="timeline-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1">Target Deadline</h6>
              <p class="text-muted mb-0">
                @if(($competencyTracker['deadline'] ?? null) && !is_null($competencyTracker['deadline']))
                  {{ \Carbon\Carbon::parse($competencyTracker['deadline'])->format('M d, Y') }}
                  @if(\Carbon\Carbon::parse($competencyTracker['deadline'])->isPast())
                    <span class="text-danger">(Overdue)</span>
                  @else
                    <span class="text-success">({{ \Carbon\Carbon::parse($competencyTracker['deadline'])->diffForHumans() }})</span>
                  @endif
                @else
                  No deadline set
                @endif
              </p>
            </div>
            <span class="badge bg-{{ ($competencyTracker['deadline'] ?? null) && !is_null($competencyTracker['deadline']) && \Carbon\Carbon::parse($competencyTracker['deadline'])->isPast() ? 'danger' : 'warning' }}">
              {{ ($competencyTracker['deadline'] ?? null) && !is_null($competencyTracker['deadline']) && \Carbon\Carbon::parse($competencyTracker['deadline'])->isPast() ? 'Overdue' : 'Pending' }}
            </span>
          </div>
        </div>
        
        <div class="timeline-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1">Next Review</h6>
              <p class="text-muted mb-0">
                {{ ($competencyTracker['next_review_date'] ?? null) ? \Carbon\Carbon::parse($competencyTracker['next_review_date'])->format('M d, Y') : 'To be scheduled' }}
              </p>
            </div>
            <span class="badge bg-info">Scheduled</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mt-4">
      <a href="{{ route('employee.competency_profile.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Back to Tracker
      </a>
    </div>
  </main>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
  
  <script>
    // JavaScript functions can be added here if needed
  </script>
</body>
</html>
