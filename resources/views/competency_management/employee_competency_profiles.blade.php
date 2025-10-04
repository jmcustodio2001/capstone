<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <style>
    .spin {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Employee Competency Card Styles */
    .employee-competency-card {
      transition: all 0.3s ease;
      border-radius: 15px !important;
      overflow: hidden;
    }

    .employee-competency-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }

    .employee-competency-card .card-header {
      border-radius: 15px 15px 0 0 !important;
      position: relative;
      overflow: hidden;
    }

    .employee-competency-card .card-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
      pointer-events: none;
    }

    .employee-competency-card .card-footer {
      border-radius: 0 0 15px 15px !important;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    .employee-competency-card .progress {
      border-radius: 10px;
      background-color: rgba(0,0,0,0.1);
    }

    .employee-competency-card .progress-bar {
      border-radius: 10px;
      background: linear-gradient(90deg, currentColor 0%, rgba(255,255,255,0.2) 50%, currentColor 100%);
      background-size: 200% 100%;
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    .employee-competency-card .btn {
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .employee-competency-card .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .employee-competency-card .badge {
      border-radius: 8px;
      font-weight: 500;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .employee-competency-card .card-header img {
        width: 50px !important;
        height: 50px !important;
      }
      
      .employee-competency-card .card-header h5 {
        font-size: 1rem;
      }
      
      .employee-competency-card .btn {
        font-size: 0.8rem;
        padding: 0.375rem 0.5rem;
      }
    }

    /* Empty state styling */
    .empty-state {
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
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
            <h2 class="fw-bold mb-1">Employee Competency Profile</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Here's your Employee Competency Profile.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee Competency Profile</li>
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

    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Competency Profile</h4>
        <div class="btn-group">
          <button class="btn btn-success" id="syncTrainingBtn">
            <i class="bi bi-arrow-repeat me-1"></i> Sync Training Progress
          </button>
          <button class="btn btn-info" id="syncExistingBtn">
            <i class="bi bi-plus-circle me-1"></i> Create Missing Entries
          </button>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('employee_competency_profiles.store') }}" method="POST" class="mb-4" id="addProfileForm">
          @csrf
          <div class="row">
            <div class="col-md-3">
              <select name="employee_id" class="form-control" required>
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                  <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select name="competency_id" class="form-control" required>
                <option value="">Select Competency</option>
                @foreach($competencylibrary as $competency)
                  <option value="{{ $competency->id }}">{{ $competency->competency_name }}</option>
                @endforeach
                @if(isset($destinationTrainings) && $destinationTrainings->count() > 0)
                  <optgroup label="Possible Training Destinations">
                    @foreach($destinationTrainings as $destination)
                      <option value="destination_{{ $loop->index }}">{{ $destination }}</option>
                    @endforeach
                  </optgroup>
                @endif
              </select>
            </div>
            <div class="col-md-2">
              <input type="number" name="proficiency_level" placeholder="Proficiency Level" class="form-control" min="1" max="5" required>
              <small class="text-muted mt-1" id="training-progress-info" style="display: none;"></small>
            </div>
            <div class="col-md-2">
              <input type="date" name="assessment_date" class="form-control" required>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-primary w-100" id="addProfileBtn">
                <i class="bi bi-plus-circle me-1"></i>Add Profile
              </button>
            </div>
          </div>
        </form>

        <!-- Employee Competency Cards -->
        <div class="row g-4">
          @foreach($profiles as $profile)
            @php
              $firstName = $profile->employee->first_name ?? 'Unknown';
              $lastName = $profile->employee->last_name ?? 'Employee';
              $fullName = $firstName . ' ' . $lastName;

              // Check if profile picture exists - simplified approach
              $profilePicUrl = null;
              if ($profile->employee->profile_picture) {
                  // Direct asset URL generation - Laravel handles the storage symlink
                  $profilePicUrl = asset('storage/' . $profile->employee->profile_picture);
              }

              // Generate consistent color based on employee name for fallback - vibrant colors like in the design
              $colors = ['FF9A56', 'FF6B9D', '4ECDC4', '45B7D1', 'FFA726', 'AB47BC', 'EF5350', '66BB6A', 'FFCA28', '26A69A'];
              $employeeId = $profile->employee->employee_id ?? 'default';
              $colorIndex = abs(crc32($employeeId)) % count($colors);
              $bgColor = $colors[$colorIndex];

              // Fallback to UI Avatars if no profile picture found
              if (!$profilePicUrl) {
                  $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) .
                                 "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
              }

              // Progress calculation logic (same as before)
              $competencyName = $profile->competency->competency_name;
              $storedProficiency = ($profile->proficiency_level / 5) * 100;
              $actualProgress = 0;
              $progressSource = 'profile';

              // Check if this is a destination knowledge competency
              $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

              if ($isDestinationCompetency) {
                // For destination competencies, prefer destination training completion (100%) regardless of delivery mode
                $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                $locationName = trim($locationName);

                if (!empty($locationName)) {
                  // Find matching destination knowledge training record
                  $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $profile->employee_id)
                    ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                    ->first();
                  if ($destinationRecord) {
                    // If destination record is marked completed OR progress is 100 -> show 100%
                    $status = strtolower($destinationRecord->status ?? '');
                    if ($status === 'completed' || ($destinationRecord->progress ?? 0) >= 100) {
                      $actualProgress = 100;
                      $progressSource = 'destination';
                    } else {
                      // Otherwise, try to compute from exam/training dashboard regardless of delivery mode
                      $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);
                      $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                      $courseId = $matchingCourse ? $matchingCourse->course_id : null;
                      $combinedProgress = 0;
                      if ($courseId) {
                        $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                      }
                      if ($combinedProgress == 0 && $courseId) {
                        $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                          ->where('course_id', $courseId)
                          ->value('progress');
                        $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                      } else if ($combinedProgress == 0) {
                        $combinedProgress = $destinationRecord->progress ?? 0;
                      }
                      $actualProgress = min(100, round($combinedProgress));
                      $progressSource = 'destination';
                    }
                  }
                }
              } else {
                // For non-destination competencies, check if manually set - be more conservative
                $isManuallySet = $profile->proficiency_level > 0 ||
                                 ($profile->proficiency_level >= 1 && $profile->assessment_date &&
                                  \Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 30);

                if (!$isManuallySet) {
                  // Use employee training dashboard for non-destination competencies
                  $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $profile->employee_id)->get();

                  foreach ($trainingRecords as $record) {
                    $courseTitle = $record->training_title ?? '';

                    // General competency matching
                    $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                    $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                    if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                      // Get progress from this training record
                      $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($profile->employee_id, $record->course_id);
                      $trainingProgress = $record->progress ?? 0;

                      // Priority: Exam progress > Training record progress
                      $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                      $progressSource = 'training';
                      break;
                    }
                  }
                }
              }

              // SIMPLIFIED DISPLAY LOGIC - Always show manual proficiency level as percentage
              // This matches the new additive scoring system where each competency contributes based on its level
              if ($profile->proficiency_level > 0) {
                // Convert proficiency level (1-5) to percentage for display
                $displayProgress = ($profile->proficiency_level / 5) * 100;
                $progressSource = 'manual';
              } else {
                // If no proficiency level set, show 0%
                $displayProgress = 0;
                $progressSource = 'no_data';
              }

              // Check if competency is approved and active (proficiency level 5 = 100% = approved/active)
              $isApprovedAndActive = $profile->proficiency_level >= 5;
            @endphp

            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="card h-100 shadow-sm border-0 employee-competency-card" style="transition: all 0.3s ease;">
                <!-- Card Header with Employee Info -->
                <div class="card-header text-white border-0 py-3" style="background-color: #{{ $bgColor }};">
                  <div class="d-flex align-items-center">
                    <img src="{{ $profilePicUrl }}"
                         alt="{{ $firstName }} {{ $lastName }}"
                         class="rounded-circle me-3"
                         style="width: 45px; height: 45px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                    <div class="flex-grow-1">
                      <h6 class="mb-0 fw-bold">{{ $firstName }} {{ $lastName }}</h6>
                      <small class="text-dark fw-bold">
                        <i class="bi bi-person-badge me-1"></i>
                        Employee ID: {{ $profile->employee->employee_id }}
                      </small>
                    </div>
                  </div>
                </div>

                <!-- Card Body with Competency Details -->
                <div class="card-body">
                  <!-- Competency Name -->
                  <div class="mb-3">
                    <h6 class="text-muted mb-1">
                      <i class="bi bi-award me-1"></i>
                      Competency
                    </h6>
                    <p class="fw-semibold mb-0 text-dark">{{ $profile->competency->competency_name }}</p>
                  </div>

                  <!-- Proficiency Level with Progress Bar -->
                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="text-muted mb-0">
                        <i class="bi bi-graph-up me-1"></i>
                        Proficiency Level
                      </h6>
                      <div class="d-flex align-items-center">
                        <span class="fw-bold text-primary me-2">{{ round($displayProgress) }}%</span>
                        @if($progressSource === 'manual')
                          <span class="badge bg-success">Level {{ $profile->proficiency_level }}/5</span>
                        @else
                          <span class="badge bg-secondary">Not Assessed</span>
                        @endif
                      </div>
                    </div>
                    <div class="progress" style="height: 12px;">
                      <div class="progress-bar 
                        @if($displayProgress >= 80) bg-success 
                        @elseif($displayProgress >= 60) bg-info 
                        @elseif($displayProgress >= 40) bg-warning 
                        @else bg-danger 
                        @endif" 
                        data-progress="{{ round($displayProgress) }}" 
                        style="width: 0%; transition: width 1s ease-in-out;"
                        role="progressbar" 
                        aria-valuenow="{{ round($displayProgress) }}" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                      </div>
                    </div>
                    @if($progressSource === 'manual')
                      <small class="text-success mt-1 d-block">
                        <i class="bi bi-check-circle me-1"></i>
                        Proficiency Level {{ $profile->proficiency_level }}/5 = {{ round($displayProgress) }}%
                      </small>
                    @else
                      <small class="text-muted mt-1 d-block">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        No proficiency level set
                      </small>
                    @endif
                  </div>

                  <!-- Assessment Date -->
                  <div class="mb-3">
                    <h6 class="text-muted mb-1">
                      <i class="bi bi-calendar-check me-1"></i>
                      Assessment Date
                    </h6>
                    <p class="mb-0">
                      <span class="badge bg-light text-dark">
                        {{ date('d/m/Y', strtotime($profile->assessment_date)) }}
                      </span>
                    </p>
                  </div>

                  <!-- Status Badge -->
                  <div class="mb-3">
                    @if($isApprovedAndActive)
                      <span class="badge bg-success fs-6">
                        <i class="bi bi-check-circle me-1"></i>
                        Approved & Active
                      </span>
                    @else
                      <span class="badge bg-warning text-dark fs-6">
                        <i class="bi bi-clock me-1"></i>
                        Pending Approval
                      </span>
                    @endif
                  </div>
                </div>

                <!-- Card Footer with Action Buttons -->
                <div class="card-footer bg-light border-0">
                  <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <!-- View Button -->
                    <button class="btn btn-info btn-sm view-btn"
                            data-employee-name="{{ $profile->employee->first_name }} {{ $profile->employee->last_name }}"
                            data-competency-name="{{ $profile->competency->competency_name }}"
                            data-proficiency="{{ $profile->proficiency_level }}"
                            data-assessment-date="{{ $profile->assessment_date }}"
                            title="View Details">
                      <i class="bi bi-eye me-1"></i>View
                    </button>

                    <!-- Edit Button -->
                    <button class="btn btn-warning btn-sm edit-btn"
                            data-id="{{ $profile->id }}"
                            data-employee-id="{{ $profile->employee_id }}"
                            data-competency-id="{{ $profile->competency_id }}"
                            data-proficiency="{{ $profile->proficiency_level }}"
                            data-assessment-date="{{ $profile->assessment_date }}"
                            title="Edit Profile">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>

                    <!-- Notify Course Mgmt Button -->
                    <button class="btn btn-outline-success btn-sm notify-course-btn {{ $isApprovedAndActive ? 'disabled' : '' }}"
                            data-id="{{ $profile->id }}"
                            data-competency-id="{{ $profile->competency_id }}"
                            data-competency-name="{{ $profile->competency->competency_name }}"
                            data-employee-name="{{ $profile->employee->first_name }} {{ $profile->employee->last_name }}"
                            data-proficiency="{{ $profile->proficiency_level }}"
                            title="{{ $isApprovedAndActive ? 'Competency already approved and active' : 'Notify Course Management' }}"
                            {{ $isApprovedAndActive ? 'disabled' : '' }}>
                      <i class="bi bi-bell me-1"></i>Notify
                    </button>

                    <!-- Delete Button -->
                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                            data-id="{{ $profile->id }}"
                            data-employee-name="{{ $profile->employee->first_name }} {{ $profile->employee->last_name }}"
                            data-competency-name="{{ $profile->competency->competency_name }}"
                            title="Delete Profile">
                      <i class="bi bi-trash me-1"></i>Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        @if($profiles->isEmpty())
          <div class="text-center py-5 empty-state">
            <div class="mb-4">
              <i class="bi bi-person-x display-1 text-muted"></i>
            </div>
            <h4 class="text-muted">No Employee Competency Profiles Found</h4>
            <p class="text-muted">Start by adding competency profiles for your employees using the form above.</p>
          </div>
        @endif
      </div>
    </div>
  </main>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Competency Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editForm" method="POST" action="">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Employee</label>
              <select name="employee_id" class="form-control" required>
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                  <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Competency</label>
              <select name="competency_id" class="form-control" required>
                <option value="">Select Competency</option>
                @foreach($competencylibrary as $competency)
                  <option value="{{ $competency->id }}">{{ $competency->competency_name }}</option>
                @endforeach
                @if(isset($destinationTrainings) && $destinationTrainings->count() > 0)
                  <optgroup label="Possible Training Destinations">
                    @foreach($destinationTrainings as $destination)
                      <option value="destination_{{ $loop->index }}">{{ $destination }}</option>
                    @endforeach
                  </optgroup>
                @endif
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Proficiency Level (1-5)</label>
              <input type="number" name="proficiency_level" class="form-control" min="1" max="5" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Assessment Date</label>
              <input type="date" name="assessment_date" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" onclick="console.log('Form action before submit:', document.getElementById('editForm').action)">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Password Confirmation Modal -->
  <div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="passwordConfirmForm">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="confirmPassword" class="form-label">Enter your password to confirm this action</label>
              <input type="password" class="form-control" id="confirmPassword" name="password" required>
              <div class="invalid-feedback" id="passwordError"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script>

    document.addEventListener('DOMContentLoaded', function() {
      const editButtons = document.querySelectorAll('.edit-btn');
      const viewButtons = document.querySelectorAll('.view-btn');
      const deleteButtons = document.querySelectorAll('.delete-btn');
      const notifyButtons = document.querySelectorAll('.notify-course-btn');
      const editModal = new bootstrap.Modal(document.getElementById('editModal'));
      const editForm = document.getElementById('editForm');
      let passwordConfirmModal = null;
      let passwordConfirmForm = null;

      // Initialize password confirmation modal
      try {
        const modalElement = document.getElementById('passwordConfirmModal');
        const formElement = document.getElementById('passwordConfirmForm');

        if (modalElement && formElement) {
          passwordConfirmModal = new bootstrap.Modal(modalElement);
          passwordConfirmForm = formElement;
          console.log('Password confirmation modal initialized successfully');
        } else {
          console.error('Password confirmation modal or form elements not found');
        }
      } catch (error) {
        console.error('Error initializing password confirmation modal:', error);
      }

      const syncTrainingBtn = document.getElementById('syncTrainingBtn');

      let pendingAction = null; // Store the action to perform after password verification

      // Function to check if password is already verified
      function checkPasswordVerification() {
        console.log('Checking password verification...');
        return fetch('{{ route("admin.check_password_verification") }}', {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(response => {
          console.log('Password check response:', response);
          if (!response.ok) {
            // Try to get error details from response
            return response.text().then(text => {
              let errorMessage = `Password verification check failed (${response.status})`;
              try {
                const errorData = JSON.parse(text);
                if (errorData.message) {
                  errorMessage = errorData.message;
                }
              } catch (e) {
                // If response is not JSON, use status text
                if (response.statusText) {
                  errorMessage = `${response.status} ${response.statusText}`;
                }
              }
              throw new Error(errorMessage);
            });
          }
          return response.json();
        })
        .then(data => {
          console.log('Password check data:', data);
          return data.verified || false;
        })
        .catch(error => {
          console.error('Password check error:', error);
          // Provide more specific error messages for different types of errors
          if (error.name === 'TypeError' && error.message.includes('fetch')) {
            throw new Error('Network connection error. Please check your internet connection and try again.');
          } else if (error.message.includes('Network response was not ok')) {
            throw new Error('Server error occurred. Please try again later.');
          } else {
            throw error;
          }
        });
      }

      // Function to verify password
      function verifyPassword(password) {
        return fetch('{{ route("admin.verify_password") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ password: password })
        })
        .then(response => {
          if (!response.ok) {
            // Try to get error details from response
            return response.text().then(text => {
              let errorMessage = `Password verification failed (${response.status})`;
              try {
                const errorData = JSON.parse(text);
                if (errorData.message) {
                  errorMessage = errorData.message;
                } else if (errorData.error) {
                  errorMessage = errorData.error;
                }
              } catch (e) {
                // If response is not JSON, use status text
                if (response.statusText) {
                  errorMessage = `${response.status} ${response.statusText}`;
                }
              }
              throw new Error(errorMessage);
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            return true;
          } else {
            throw new Error(data.message || data.error || 'Invalid password');
          }
        })
        .catch(error => {
          console.error('Password verification error:', error);
          // Provide more specific error messages for different types of errors
          if (error.name === 'TypeError' && error.message.includes('fetch')) {
            throw new Error('Network connection error. Please check your internet connection and try again.');
          } else if (error.message.includes('Network response was not ok')) {
            throw new Error('Server error occurred. Please try again later.');
          } else {
            throw error;
          }
        });
      }

      // Function to show password confirmation modal
      function showPasswordConfirmation(callback) {
        if (!passwordConfirmModal || !passwordConfirmForm) {
          console.error('Password confirmation modal not properly initialized');
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Security verification system is not available. Please refresh the page and try again.',
            confirmButtonText: 'OK'
          });
          return;
        }

        pendingAction = callback;
        passwordConfirmForm.reset();
        document.getElementById('passwordError').textContent = '';
        document.getElementById('confirmPassword').classList.remove('is-invalid');

        passwordConfirmModal.show();
      }

      // Password confirmation form handler
      if (passwordConfirmForm) {
        passwordConfirmForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const password = document.getElementById('confirmPassword').value;
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;

          if (!password) {
            document.getElementById('confirmPassword').classList.add('is-invalid');
            document.getElementById('passwordError').textContent = 'Password is required';
            return;
          }

          // Clear previous errors
          document.getElementById('confirmPassword').classList.remove('is-invalid');
          document.getElementById('passwordError').textContent = '';

          // Show loading state
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';

          // Verify password
          verifyPassword(password).then(() => {
            // Success
            if (passwordConfirmModal) {
              passwordConfirmModal.hide();
            }
            if (pendingAction) {
              pendingAction();
              pendingAction = null;
            }
          }).catch(error => {
            // Error
            document.getElementById('confirmPassword').classList.add('is-invalid');
            document.getElementById('passwordError').textContent = error.message || 'Invalid password';
          }).finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          });
        });
      }

      // Apply progress widths set via data attribute to avoid inline Blade in CSS
      document.querySelectorAll('.progress-bar[data-progress]').forEach(function(bar) {
        const value = parseInt(bar.getAttribute('data-progress')) || 0;
        bar.style.width = value + '%';
      });

      // Enhanced Edit functionality with comprehensive SweetAlert
      editButtons.forEach(button => {
        button.addEventListener('click', function() {
          const profileId = this.getAttribute('data-id');
          const employeeId = this.getAttribute('data-employee-id');
          const competencyId = this.getAttribute('data-competency-id');
          const proficiency = this.getAttribute('data-proficiency');
          const assessmentDate = this.getAttribute('data-assessment-date');
          
          editProfileWithConfirmation(profileId, employeeId, competencyId, proficiency, assessmentDate);
        });
      });

      function editProfileWithConfirmation(profileId, employeeId, competencyId, proficiency, assessmentDate) {
        // Get employee and competency names for display
        const employeeSelect = addProfileForm.querySelector('select[name="employee_id"]');
        const competencySelect = addProfileForm.querySelector('select[name="competency_id"]');
        
        let employeeName = 'Unknown Employee';
        let competencyName = 'Unknown Competency';
        
        // Find employee name
        for (let option of employeeSelect.options) {
          if (option.value === employeeId) {
            employeeName = option.text;
            break;
          }
        }
        
        // Find competency name
        for (let option of competencySelect.options) {
          if (option.value === competencyId) {
            competencyName = option.text;
            break;
          }
        }

        Swal.fire({
          title: '<i class="bi bi-shield-lock text-warning"></i> Password Confirmation Required',
          html: `
            <div class="text-start mb-3">
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle text-info"></i> 
                For security purposes, please enter your password to edit this competency profile.
              </p>
              <div class="bg-light p-3 rounded mb-3">
                <strong>Profile to Edit:</strong><br>
                <strong>Employee:</strong> ${employeeName}<br>
                <strong>Competency:</strong> ${competencyName}<br>
                <strong>Current Proficiency:</strong> ${proficiency}/5<br>
                <strong>Assessment Date:</strong> ${new Date(assessmentDate).toLocaleDateString()}
              </div>
            </div>
            <input type="password" id="swal-edit-password" class="swal2-input" placeholder="Enter your password" required>
          `,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-pencil"></i> Edit Profile',
          cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
          confirmButtonColor: '#ffc107',
          cancelButtonColor: '#6c757d',
          preConfirm: () => {
            const password = document.getElementById('swal-edit-password').value;
            if (!password) {
              Swal.showValidationMessage('Password is required');
              return false;
            }
            return password;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            verifyPasswordAndEdit(profileId, employeeId, competencyId, proficiency, assessmentDate, result.value);
          }
        });
      }

      function verifyPasswordAndEdit(profileId, employeeId, competencyId, proficiency, assessmentDate, password) {
        Swal.fire({
          title: 'Verifying...',
          text: 'Checking password...',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Verify password first
        verifyPassword(password).then(() => {
          // Password verified, show edit form
          Swal.close();
          showEditForm(profileId, employeeId, competencyId, proficiency, assessmentDate);
        }).catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Authentication Failed',
            text: error.message || 'Invalid password. Please try again.',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545'
          }).then(() => {
            // Retry the edit process
            editProfileWithConfirmation(profileId, employeeId, competencyId, proficiency, assessmentDate);
          });
        });
      }

      function showEditForm(profileId, employeeId, competencyId, proficiency, assessmentDate) {
        editForm.action = `/admin/employee-competency-profiles/${profileId}`;
        editForm.querySelector('select[name="employee_id"]').value = employeeId;
        editForm.querySelector('select[name="competency_id"]').value = competencyId;
        editForm.querySelector('input[name="proficiency_level"]').value = proficiency;
        editForm.querySelector('input[name="assessment_date"]').value = assessmentDate;
        editModal.show();
      }

      // Enhanced Delete functionality with comprehensive SweetAlert
      deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
          const profileId = this.getAttribute('data-id');
          const employeeName = this.getAttribute('data-employee-name');
          const competencyName = this.getAttribute('data-competency-name');
          
          deleteProfileWithConfirmation(profileId, employeeName, competencyName);
        });
      });

      function deleteProfileWithConfirmation(profileId, employeeName, competencyName) {
        Swal.fire({
          title: '<i class="bi bi-shield-lock text-warning"></i> Password Confirmation Required',
          html: `
            <div class="text-start mb-3">
              <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Warning:</strong> This action cannot be undone!
              </div>
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle text-info"></i> 
                For security purposes, please enter your password to confirm deleting this competency profile.
              </p>
              <div class="bg-light p-3 rounded mb-3">
                <strong>Profile to Delete:</strong><br>
                <strong>Employee:</strong> ${employeeName}<br>
                <strong>Competency:</strong> ${competencyName}
              </div>
            </div>
            <input type="password" id="swal-delete-password" class="swal2-input" placeholder="Enter your password" required>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-trash"></i> Delete Profile',
          cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          preConfirm: () => {
            const password = document.getElementById('swal-delete-password').value;
            if (!password) {
              Swal.showValidationMessage('Password is required');
              return false;
            }
            return password;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitDeleteProfile(profileId, result.value);
          }
        });
      }

      function submitDeleteProfile(profileId, password) {
        Swal.fire({
          title: 'Processing...',
          text: 'Deleting competency profile...',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Verify password first
        verifyPassword(password).then(() => {
          // Password verified, proceed with deletion
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/employee-competency-profiles/${profileId}`;
          
          const csrfToken = document.createElement('input');
          csrfToken.type = 'hidden';
          csrfToken.name = '_token';
          csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          
          const methodField = document.createElement('input');
          methodField.type = 'hidden';
          methodField.name = '_method';
          methodField.value = 'DELETE';
          
          form.appendChild(csrfToken);
          form.appendChild(methodField);
          document.body.appendChild(form);
          form.submit();
        }).catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Authentication Failed',
            text: error.message || 'Invalid password. Please try again.',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545'
          }).then(() => {
            // Retry the delete process
            deleteProfileWithConfirmation(profileId, employeeName, competencyName);
          });
        });
      }

      // View Details functionality
      viewButtons.forEach(button => {
        button.addEventListener('click', function() {
          const employeeName = this.getAttribute('data-employee-name');
          const competencyName = this.getAttribute('data-competency-name');
          const proficiency = this.getAttribute('data-proficiency');
          const assessmentDate = this.getAttribute('data-assessment-date');
          
          const proficiencyPercent = Math.round((proficiency / 5) * 100);
          const formattedDate = new Date(assessmentDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });

          Swal.fire({
            title: '<i class="bi bi-person-badge text-primary"></i> Competency Profile Details',
            html: `
              <div class="text-start">
                <div class="row mb-3">
                  <div class="col-4"><strong>Employee:</strong></div>
                  <div class="col-8">${employeeName}</div>
                </div>
                <div class="row mb-3">
                  <div class="col-4"><strong>Competency:</strong></div>
                  <div class="col-8">${competencyName}</div>
                </div>
                <div class="row mb-3">
                  <div class="col-4"><strong>Proficiency Level:</strong></div>
                  <div class="col-8">
                    <span class="badge bg-primary">${proficiency}/5</span>
                    <span class="text-muted">(${proficiencyPercent}%)</span>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-4"><strong>Assessment Date:</strong></div>
                  <div class="col-8">${formattedDate}</div>
                </div>
              </div>
            `,
            icon: 'info',
            confirmButtonText: '<i class="bi bi-check-lg"></i> Close',
            confirmButtonColor: '#0d6efd',
            width: '500px'
          });
        });
      });

      // Add Profile with Password Confirmation
      const addProfileBtn = document.getElementById('addProfileBtn');
      const addProfileForm = document.getElementById('addProfileForm');

      if (addProfileBtn && addProfileForm) {
        addProfileBtn.addEventListener('click', function(e) {
          e.preventDefault();
          addProfileWithConfirmation();
        });
      }

      function addProfileWithConfirmation() {
        // Validate required fields first
        const employeeSelect = addProfileForm.querySelector('select[name="employee_id"]');
        const competencySelect = addProfileForm.querySelector('select[name="competency_id"]');
        const proficiencyInput = addProfileForm.querySelector('input[name="proficiency_level"]');
        const dateInput = addProfileForm.querySelector('input[name="assessment_date"]');

        if (!employeeSelect.value || !competencySelect.value || !proficiencyInput.value || !dateInput.value) {
          Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please fill in all required fields before proceeding.',
            confirmButtonText: 'OK'
          });
          return;
        }

        // Get selected option texts for display
        const employeeName = employeeSelect.options[employeeSelect.selectedIndex].text;
        const competencyName = competencySelect.options[competencySelect.selectedIndex].text;

        Swal.fire({
          title: '<i class="bi bi-shield-lock text-warning"></i> Password Confirmation Required',
          html: `
            <div class="text-start mb-3">
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle text-info"></i> 
                For security purposes, please enter your password to confirm adding this competency profile.
              </p>
              <div class="bg-light p-3 rounded mb-3">
                <strong>Profile Details:</strong><br>
                <strong>Employee:</strong> ${employeeName}<br>
                <strong>Competency:</strong> ${competencyName}<br>
                <strong>Proficiency Level:</strong> ${proficiencyInput.value}/5<br>
                <strong>Assessment Date:</strong> ${new Date(dateInput.value).toLocaleDateString()}
              </div>
            </div>
            <input type="password" id="swal-password" class="swal2-input" placeholder="Enter your password" required>
          `,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-plus-circle"></i> Add Profile',
          cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
          confirmButtonColor: '#198754',
          cancelButtonColor: '#6c757d',
          preConfirm: () => {
            const password = document.getElementById('swal-password').value;
            if (!password) {
              Swal.showValidationMessage('Password is required');
              return false;
            }
            return password;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitAddProfileForm(result.value);
          }
        });
      }

      function submitAddProfileForm(password) {
        Swal.fire({
          title: 'Processing...',
          text: 'Adding competency profile...',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Verify password first
        verifyPassword(password).then(() => {
          // Password verified, submit the form
          addProfileForm.submit();
        }).catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Authentication Failed',
            text: error.message || 'Invalid password. Please try again.',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545'
          }).then(() => {
            // Retry the add process
            addProfileWithConfirmation();
          });
        });
      }

      // Sync Training Progress functionality
      if (syncTrainingBtn) {
        syncTrainingBtn.addEventListener('click', function() {
          const buttonElement = this;

          // Check if password is already verified
          checkPasswordVerification().then(isVerified => {
            if (isVerified) {
              // Password already verified, proceed with sync
              performSyncTrainingAction(buttonElement);
            } else {
              // Show password confirmation
              showPasswordConfirmation(() => performSyncTrainingAction(buttonElement));
            }
          }).catch(error => {
            console.error('Error checking password verification:', error);
            Swal.fire({
              icon: 'error',
              title: 'Verification Error',
              text: error.message || 'Failed to check password verification status. Please try again.',
              confirmButtonText: 'OK'
            });
          });
        });
      }

      // Function to perform sync training action
      function performSyncTrainingAction(buttonElement) {
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Syncing...';
        buttonElement.disabled = true;

        fetch('{{ route("employee_competency_profiles.sync_training") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(async (response) => {
          const isJson = (response.headers.get('content-type') || '').includes('application/json');
          const data = isJson ? await response.json() : { success: response.ok, message: response.ok ? 'Sync completed.' : 'Request failed.' };
          return data;
        })
        .then(data => {
          if (data.success) {
            // Show success message using SweetAlert
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: data.message,
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              // Refresh page after alert closes
              window.location.reload();
            });
          } else {
            // Show error message using SweetAlert
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message,
              confirmButtonText: 'OK'
            });
          }
        })
        .catch(function(error) {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error syncing training progress. Please try again.',
            confirmButtonText: 'OK'
          });
        })
        .finally(function() {
          buttonElement.innerHTML = originalText;
          buttonElement.disabled = false;
        });
      }

      // Sync Existing Training Records functionality
      const syncExistingBtn = document.getElementById('syncExistingBtn');
      if (syncExistingBtn) {
        syncExistingBtn.addEventListener('click', function() {
          const buttonElement = this;

          // Check if password is already verified
          checkPasswordVerification().then(isVerified => {
            if (isVerified) {
              // Password already verified, proceed with sync
              performSyncExistingAction(buttonElement);
            } else {
              // Show password confirmation
              showPasswordConfirmation(() => performSyncExistingAction(buttonElement));
            }
          }).catch(error => {
            console.error('Error checking password verification:', error);
            Swal.fire({
              icon: 'error',
              title: 'Verification Error',
              text: error.message || 'Failed to check password verification status. Please try again.',
              confirmButtonText: 'OK'
            });
          });
        });
      }

      // Function to perform sync existing action
      function performSyncExistingAction(buttonElement) {
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="bi bi-plus-circle spin"></i> Creating...';
        buttonElement.disabled = true;

        fetch('{{ route("admin.employee_trainings_dashboard.sync_records") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(async (response) => {
          const isJson = (response.headers.get('content-type') || '').includes('application/json');
          const data = isJson ? await response.json() : { success: response.ok, message: response.ok ? 'Sync completed.' : 'Request failed.' };
          return data;
        })
        .then(data => {
          if (data.success) {
            // Show success message using SweetAlert
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: data.message,
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              // Refresh page after alert closes
              window.location.reload();
            });
          } else {
            // Show error message using SweetAlert
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message,
              confirmButtonText: 'OK'
            });
          }
        })
        .catch(function(error) {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error creating missing entries. Please try again.',
            confirmButtonText: 'OK'
          });
        })
        .finally(function() {
          buttonElement.innerHTML = originalText;
          buttonElement.disabled = false;
        });
      }

      // ========== COURSE MANAGEMENT NOTIFICATION FUNCTIONALITY ==========
      notifyButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Skip if button is disabled
          if (this.disabled || this.classList.contains('disabled')) {
            return;
          }

          const profileId = this.getAttribute('data-id');
          const competencyId = this.getAttribute('data-competency-id');
          const competencyName = this.getAttribute('data-competency-name');
          const employeeName = this.getAttribute('data-employee-name');
          const proficiency = this.getAttribute('data-proficiency');
          
          notifyCourseManagementWithConfirmation(profileId, competencyId, competencyName, employeeName, proficiency);
        });
      });

      function notifyCourseManagementWithConfirmation(profileId, competencyId, competencyName, employeeName, proficiency) {
        Swal.fire({
          title: '<i class="bi bi-shield-lock text-warning"></i> Password Confirmation Required',
          html: `
            <div class="text-start mb-3">
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle text-info"></i> 
                For security purposes, please enter your password to send notification to course management.
              </p>
              <div class="bg-light p-3 rounded mb-3">
                <strong>Notification Details:</strong><br>
                <strong>Employee:</strong> ${employeeName}<br>
                <strong>Competency:</strong> ${competencyName}<br>
                <strong>Current Proficiency:</strong> ${proficiency}/5 (${Math.round((proficiency/5)*100)}%)<br>
                <strong>Action:</strong> Notify course management about competency status
              </div>
              <div class="alert alert-info" role="alert">
                <i class="bi bi-bell"></i>
                This will send a notification to course management about the current status of this competency profile.
              </div>
            </div>
            <input type="password" id="swal-notify-password" class="swal2-input" placeholder="Enter your password" required>
          `,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-bell"></i> Send Notification',
          cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
          confirmButtonColor: '#198754',
          cancelButtonColor: '#6c757d',
          preConfirm: () => {
            const password = document.getElementById('swal-notify-password').value;
            if (!password) {
              Swal.showValidationMessage('Password is required');
              return false;
            }
            return password;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitNotificationRequest(profileId, competencyId, competencyName, employeeName, proficiency, result.value);
          }
        });
      }

      function submitNotificationRequest(profileId, competencyId, competencyName, employeeName, proficiency, password) {
        Swal.fire({
          title: 'Processing...',
          text: 'Sending notification to course management...',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Verify password first
        verifyPassword(password).then(() => {
          // Password verified, send notification
          return fetch(`/admin/employee-competency-profiles/${profileId}/notify-course-management`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              competency_id: competencyId,
              competency_name: competencyName,
              employee_name: employeeName,
              proficiency_level: proficiency
            })
          });
        }).then(response => {
          if (!response.ok) {
            return response.text().then(text => {
              let errorMessage = `Notification failed (${response.status})`;
              try {
                const errorData = JSON.parse(text);
                if (errorData.message) {
                  errorMessage = errorData.message;
                }
              } catch (e) {
                if (response.statusText) {
                  errorMessage = `${response.status} ${response.statusText}`;
                }
              }
              throw new Error(errorMessage);
            });
          }
          return response.json();
        }).then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Notification Sent Successfully',
              html: `
                <div class="text-start">
                  <p><strong>Competency:</strong> ${competencyName}</p>
                  <p><strong>Employee:</strong> ${employeeName}</p>
                  <p><strong>Message:</strong> ${data.message}</p>
                  ${data.active_courses_count ? `<p><strong>Active Courses Affected:</strong> ${data.active_courses_count}</p>` : ''}
                </div>
              `,
              confirmButtonText: 'OK',
              confirmButtonColor: '#198754',
              timer: 5000,
              timerProgressBar: true
            }).then(() => {
              // Disable the button after successful notification
              const notifyBtn = document.querySelector(`[data-id="${profileId}"].notify-course-btn`);
              if (notifyBtn) {
                notifyBtn.disabled = true;
                notifyBtn.classList.add('disabled');
                notifyBtn.title = 'Notification already sent';
                notifyBtn.innerHTML = '<i class="bi bi-check-circle"></i>';
              }
            });
          } else {
            throw new Error(data.message || 'Failed to send notification');
          }
        }).catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Notification Failed',
            text: error.message || 'Failed to send notification. Please try again.',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545'
          }).then(() => {
            // Retry the notification process
            notifyCourseManagementWithConfirmation(profileId, competencyId, competencyName, employeeName, proficiency);
          });
        });
      }
    });
  </script>
</body>
</html>
