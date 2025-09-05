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
        <button class="btn btn-success btn-sm" onclick="syncTrainingProgress()">
          <i class="bi bi-arrow-repeat me-1"></i> Sync Training Progress
        </button>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead class="table-primary">
            <tr>
              <th class="fw-bold">Employee</th>
              <th class="fw-bold">Competency</th>
              <th class="fw-bold">Required Level</th>
              <th class="fw-bold">Current Level</th>
              <th class="fw-bold">Gap</th>
              <th class="fw-bold">Recommended Training</th>
            </tr>
          </thead>
          <tbody>
            @forelse($gaps as $gap)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar-sm me-2">
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
                    @endphp
                    
                    <img src="{{ $profilePicUrl }}" 
                         alt="{{ $firstName }} {{ $lastName }}" 
                         class="rounded-circle" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                  </div>
                  <div>
                    <span class="fw-semibold">{{ $firstName }} {{ $lastName }}</span>
                    <br><small class="text-muted">({{ $gap->employee->employee_id }})</small>
                  </div>
                </div>
              </td>
              <td>{{ $gap->competency->competency_name }}</td>
              @php
                // Convert levels to 1-5 scale for display
                $displayRequiredLevel = $gap->required_level > 5 ? 5 : ($gap->required_level < 1 ? round($gap->required_level * 20) : $gap->required_level);
                $displayCurrentLevel = $gap->current_level > 5 ? 5 : ($gap->current_level < 1 ? round($gap->current_level * 20) : $gap->current_level);
                $displayGap = max(0, $displayRequiredLevel - $displayCurrentLevel);
              @endphp
              <td>{{ $displayRequiredLevel }}</td>
              <td>{{ $displayCurrentLevel }}</td>
              <td class="text-danger fw-bold">{{ $displayGap }}</td>
              <td>
                @if($gap->recommended_training && isset($gap->recommended_training->course_title))
                  {{ $gap->recommended_training->course_title }}
                @else
                  <span class="text-muted">No training assigned</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted">All employees meet required skills.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Skills Reference -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Customer Service & Sales Skills Reference</h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead class="table-primary">
            <tr>
              <th class="fw-bold">Skill</th>
              <th class="fw-bold">Rating</th>
            </tr>
          </thead>
          <tbody>
            @forelse($skills as $skill)
            <tr>
              <td>{{ $skill->competency_name }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="progress me-2" style="width: 100px; height: 20px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(($skill->rate * 20), 100) }}%;" aria-valuenow="{{ min(($skill->rate * 20), 100) }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <span class="fw-semibold">{{ min(($skill->rate * 20), 100) }}%</span>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="2" class="text-center text-muted">No skills defined in competency library.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Training Records -->
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Training Dashboard</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
          <i class="bi bi-plus-lg me-1"></i> Add Record
        </button>
      </div>
      <div class="card-body">

        <table class="table table-bordered">
          <thead class="table-primary">
            <tr>
              <th class="fw-bold">ID</th>
              <th class="fw-bold">Employee</th>
              <th class="fw-bold">Readiness Score</th>
              <th class="fw-bold">Training</th>
              <th class="fw-bold">Date Completed</th>
              <th class="fw-bold">Progress</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($records as $record)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
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
                  <div>
                    <div class="fw-semibold">{{ $firstName }} {{ $lastName }}</div>
                    <small class="text-muted">{{ $record->employee->employee_id }}</small>
                  </div>
                </div>
              </td>
              <td>
                @php
                  // Calculate actual readiness score using EXACT same algorithm as Employee Training Dashboard
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
                  
                  // Get training data
                  $trainingRecords = $employeeId ? \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)->get() : collect();
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
                  
                  // Calculate training records component (30% weight) - Ultra-conservative approach
                  $trainingRecordsScore = 0;
                  if ($totalCourses > 0) {
                      // Cap training progress at 15% to prevent inflation (was 25%)
                      $trainingProgressScore = min($avgTrainingProgress * 0.15, 15);
                      
                      // Cap completion rate at 12% (was 20%)
                      $completionRateScore = min($completionRate * 0.12, 12);
                      
                      // Ultra-conservative assignment scoring - requires 50+ courses (was 30+)
                      $assignmentScore = min(($totalCourses / 50) * 8, 8); // Max 8% (was 12%)
                      
                      // Ultra-conservative certificate scoring - requires 15+ certificates (was 10+)
                      $certificates = $employeeId ? \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)->count() : 0;
                      $certificateScore = $certificates > 0 ? min(($certificates / 15) * 5, 5) : 0; // Max 5% (was 10%)
                      
                      // Ultra-conservative weighted average
                      $trainingRecordsScore = ($trainingProgressScore * 0.6) + ($completionRateScore * 0.25) + ($assignmentScore * 0.1) + ($certificateScore * 0.05);
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
                @endphp
                <span class="fw-semibold">{{ $readiness }}{{ is_numeric($readiness) ? '%' : '' }}</span>
              </td>
              <td>
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
              </td>
              <td>
                @if(!empty($record->date_completed) && $record->date_completed != '1970-01-01')
                  {{ \Carbon\Carbon::parse($record->date_completed)->format('d/m/Y') }}
                @else
                  <span class="text-muted">Not completed</span>
                @endif
              </td>
              <td>
                @php
                  // Use exam progress instead of raw progress to match employee view
                  // Get the correct course_id - training_id maps to course_id in EmployeeTrainingDashboard
                  $courseId = $record->training_id ?? $record->course_id;
                  $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseId);
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
                
                <div class="mt-1">
                  @if($displayProgress >= 100)
                    <span class="badge bg-success bg-opacity-10 text-success fs-6">Completed</span>
                  @elseif($displayProgress > 0)
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6">In Progress</span>
                  @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6">Not Started</span>
                  @endif
                </div>
              </td>
              <td class="text-center">
                <button class="btn btn-info btn-sm me-1" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->id }}">
                  <i class="bi bi-eye"></i> View
                </button>
                <button class="btn btn-warning btn-sm me-1" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $record->id }}">
                  <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteRecord({{ $record->id }})">
                  <i class="bi bi-trash"></i> Delete
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted">No training records found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>

        <div class="mt-3">
          <span class="fw-bold text-success">Note:</span>
          When a training record is marked completed, a certificate will be awarded and tracked automatically.
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
              // Use exam progress instead of raw progress to match employee view
              // Get the correct course_id - training_id maps to course_id in EmployeeTrainingDashboard
              $courseId = $record->training_id ?? $record->course_id;
              $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseId);
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function refreshGapTable() {
      fetch('/api/competency-gaps')
        .then(response => response.text())
        .then(html => {
          document.getElementById('gap-table-container').innerHTML = html;
        });
    }

    function deleteRecord(id) {
      if (confirm('Are you sure you want to delete this training record?')) {
        fetch(`/customer_service_sales_skills_training/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
          },
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error deleting record');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error deleting record');
        });
      }
    }

    function syncTrainingProgress() {
      if (confirm('Sync training progress with competency levels? This will update competency gaps based on completed training.')) {
        fetch('/admin/course_management/sync_training_competency', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
          },
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Training progress synced successfully!');
            location.reload();
          } else {
            alert('Error syncing training progress: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error syncing training progress. Please check console for details.');
        });
      }
    }
  </script>
</body>
</html>
