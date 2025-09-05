<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels Admin</title>
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
            <li class="breadcrumb-item"><a href="{{ route('potential_successors.index') }}" class="text-decoration-none">Succession Planning</a></li>
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
              <button type="button" class="btn btn-primary w-100" id="addProfileBtn">Add</button>
            </div>
          </div>
        </form>

        <table class="table table-bordered">
          <thead class="table-primary">
            <tr>
              <th class="fw-bold">Employee</th>
              <th class="fw-bold">Competency</th>
              <th class="fw-bold">Proficiency Level</th>
              <th class="fw-bold">Assessment Date</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($profiles as $profile)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar-sm me-2">
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

                      // Generate consistent color based on employee name for fallback
                      $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                      $employeeId = $profile->employee->employee_id ?? 'default';
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
              <td>{{ $profile->competency->competency_name }}</td>
              <td>
                @php
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
                    $isManuallySet = $profile->proficiency_level > 1 ||
                                     ($profile->proficiency_level >= 1 && $profile->assessment_date &&
                                      \Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 7);

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

                  // Final display logic - ALWAYS prioritize actual training progress over stored proficiency
                  if ($actualProgress > 0) {
                    $displayProgress = $actualProgress;
                  } else {
                    // Check if we have any training records for this employee-competency combination
                    $hasTrainingRecord = false;
                    $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $profile->employee_id)->get();

                    foreach ($trainingRecords as $record) {
                      $courseTitle = $record->training_title ?? '';

                      // Enhanced matching for destination knowledge competencies
                      $cleanCompetency = str_replace(['Destination Knowledge - ', ' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                      $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                      // Multiple matching strategies
                      $isMatch = false;

                      // Strategy 1: Direct match after cleaning
                      if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                        $isMatch = true;
                      }

                      // Strategy 2: For destination competencies, check if course contains the location name
                      if (!$isMatch && $isDestinationCompetency) {
                        $locationName = trim(str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName));
                        if (!empty($locationName) && (stripos($courseTitle, $locationName) !== false || stripos($cleanCourse, $locationName) !== false)) {
                          $isMatch = true;
                        }
                      }

                      // Strategy 3: Check if competency name contains course name or vice versa
                      if (!$isMatch) {
                        $competencyWords = explode(' ', strtoupper($cleanCompetency));
                        $courseWords = explode(' ', strtoupper($cleanCourse));

                        foreach ($competencyWords as $compWord) {
                          if (strlen($compWord) > 2) { // Skip short words
                            foreach ($courseWords as $courseWord) {
                              if (strlen($courseWord) > 2 && $compWord === $courseWord) {
                                $isMatch = true;
                                break 2;
                              }
                            }
                          }
                        }
                      }

                      if ($isMatch) {
                        $hasTrainingRecord = true;
                        // Get actual progress from training record
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($profile->employee_id, $record->course_id);
                        $trainingProgress = $record->progress ?? 0;
                        $actualTrainingProgress = $examProgress > 0 ? $examProgress : $trainingProgress;

                        $displayProgress = $actualTrainingProgress;
                        $progressSource = 'training';
                        break;
                      }
                    }

                    // If no training record found, use stored proficiency only if manually set
                    if (!$hasTrainingRecord) {
                      // Check if this is a manually set proficiency that should be preserved
                      $isManuallySetForDisplay = false;
                      if ($isDestinationCompetency) {
                        $isManuallySetForDisplay = $profile->proficiency_level > 1 ||
                                                 ($profile->proficiency_level >= 1 && $profile->assessment_date &&
                                                  \Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 30);
                      } else {
                        $isManuallySetForDisplay = $profile->proficiency_level > 1 ||
                                                 ($profile->proficiency_level >= 1 && $profile->assessment_date &&
                                                  \Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 7);
                      }
                      
                      if ($isManuallySetForDisplay) {
                        $displayProgress = $storedProficiency;
                        $progressSource = 'manual';
                      } else {
                        $displayProgress = 0; // Start from 0% instead of 20%
                        $progressSource = 'no_data';
                      }
                    }
                  }
                @endphp
                <div class="d-flex align-items-center">
                  <div class="progress me-2" style="width: 80px; height: 20px;">
                    <div class="progress-bar bg-warning" data-progress="{{ round($displayProgress) }}" style="width: 0%;"></div>
                  </div>
                  <span class="fw-semibold">{{ round($displayProgress) }}%</span>
                  @if($progressSource === 'manual')
                    <small class="text-warning ms-1" title="Manual proficiency level: {{ $storedProficiency }}%">(manual)</small>
                  @elseif($progressSource === 'destination')
                    <small class="text-success ms-1" title="From destination knowledge training: {{ $actualProgress }}%">(destination)</small>
                  @elseif($progressSource === 'training')
                    <small class="text-primary ms-1" title="From employee training dashboard: {{ $actualProgress }}%">(training)</small>
                  @elseif($progressSource === 'no_data')
                    <small class="text-muted ms-1" title="No training progress or proficiency data found">(0% - not started)</small>
                  @elseif($storedProficiency > 0)
                    <small class="text-info ms-1" title="Using stored proficiency level: {{ $storedProficiency }}%">(profile)</small>
                  @else
                    <small class="text-muted ms-1" title="No training data or proficiency level found">(no data)</small>
                  @endif
                </div>
              </td>
              <td>{{ date('d/m/Y', strtotime($profile->assessment_date)) }}</td>
              <td class="text-center">
<!-- Edit Button -->
<button class="btn btn-warning btn-sm edit-btn"
        data-id="{{ $profile->id }}"
        data-employee-id="{{ $profile->employee_id }}"
        data-competency-id="{{ $profile->competency_id }}"
        data-proficiency="{{ $profile->proficiency_level }}"
        data-assessment-date="{{ $profile->assessment_date }}">
  <i class="bi bi-pencil"></i> Edit
</button>

<!-- Delete Button -->
<form action="{{ route('employee_competency_profiles.destroy', $profile->id) }}"
      method="POST"
      class="d-inline delete-form"
      data-profile-id="{{ $profile->id }}">
  @csrf
  @method('DELETE')
  <button type="button" class="btn btn-danger btn-sm delete-btn">
    <i class="bi bi-trash"></i> Delete
  </button>
</form>

                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
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

      editButtons.forEach(button => {
        button.addEventListener('click', function() {
          const buttonElement = this;

          // Check if password is already verified
          checkPasswordVerification().then(isVerified => {
            if (isVerified) {
              // Password already verified, proceed with edit
              performEditAction(buttonElement);
            } else {
              // Show password confirmation
              showPasswordConfirmation(() => performEditAction(buttonElement));
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
      });

      // Function to perform the actual edit action
      function performEditAction(buttonElement) {
        document.getElementById('overlay').style.display = 'none';

        const id = buttonElement.getAttribute('data-id');
        const employeeId = buttonElement.getAttribute('data-employee-id');
        const competencyId = buttonElement.getAttribute('data-competency-id');
        const proficiency = buttonElement.getAttribute('data-proficiency');
        const assessmentDate = buttonElement.getAttribute('data-assessment-date');

        console.log('Setting form action to:', `/admin/employee-competency-profiles/${id}`);
        editForm.action = `/admin/employee-competency-profiles/${id}`;
        console.log('Form action after setting:', editForm.action);

        editForm.querySelector('select[name="employee_id"]').value = employeeId;
        editForm.querySelector('select[name="competency_id"]').value = competencyId;
        editForm.querySelector('input[name="proficiency_level"]').value = proficiency;
        editForm.querySelector('input[name="assessment_date"]').value = assessmentDate;

        editModal.show();
      }

      // Delete button handlers
      const deleteButtons = document.querySelectorAll('.delete-btn');
      deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
          const form = this.closest('.delete-form');
          const profileId = form.getAttribute('data-profile-id');

          // Check if password is already verified
          checkPasswordVerification().then(isVerified => {
            if (isVerified) {
              // Password already verified, proceed with delete
              Swal.fire({
                title: 'Are you sure?',
                text: 'Delete this profile?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                if (result.isConfirmed) {
                  form.submit();
                }
              });
            } else {
              // Show password confirmation
              showPasswordConfirmation(() => {
                Swal.fire({
                  title: 'Are you sure?',
                  text: 'Delete this profile?',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#d33',
                  cancelButtonColor: '#3085d6',
                  confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                  if (result.isConfirmed) {
                    form.submit();
                  }
                });
              });
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
      });

      // Add button handler
      const addProfileBtn = document.getElementById('addProfileBtn');
      const addProfileForm = document.getElementById('addProfileForm');

      if (addProfileBtn && addProfileForm) {
        console.log('Add button handler attached');
        addProfileBtn.addEventListener('click', function(e) {
          console.log('Add button clicked');
          e.preventDefault(); // Prevent default form submission

          // Validate required fields first
          const employeeSelect = addProfileForm.querySelector('select[name="employee_id"]');
          const competencySelect = addProfileForm.querySelector('select[name="competency_id"]');
          const proficiencyInput = addProfileForm.querySelector('input[name="proficiency_level"]');
          const dateInput = addProfileForm.querySelector('input[name="assessment_date"]');

          console.log('Form values:', {
            employee: employeeSelect.value,
            competency: competencySelect.value,
            proficiency: proficiencyInput.value,
            date: dateInput.value
          });

          if (!employeeSelect.value || !competencySelect.value || !proficiencyInput.value || !dateInput.value) {
            // Show validation error using SweetAlert
            Swal.fire({
              icon: 'warning',
              title: 'Validation Error',
              text: 'Please fill in all required fields before proceeding.',
              confirmButtonText: 'OK'
            });
            return;
          }

          // Show loading state on button
          const originalBtnText = this.innerHTML;
          this.disabled = true;
          this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

          // Check if password is already verified
          checkPasswordVerification().then(isVerified => {
            console.log('Password verification result:', isVerified);
            if (isVerified) {
              // Password already verified, proceed with add
              console.log('Password already verified, submitting form');
              addProfileForm.submit();
            } else {
              // Reset button state before showing modal
              this.disabled = false;
              this.innerHTML = originalBtnText;

              // Show password confirmation
              console.log('Password not verified, showing modal');
              showPasswordConfirmation(() => {
                console.log('Password confirmed, submitting form');
                // Show loading again after confirmation
                addProfileBtn.disabled = true;
                addProfileBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
                addProfileForm.submit();
              });
            }
          }).catch(error => {
            console.error('Error checking password verification:', error);
            // Reset button state
            this.disabled = false;
            this.innerHTML = originalBtnText;

            // Show error alert using SweetAlert
            Swal.fire({
              icon: 'error',
              title: 'Verification Error',
              text: error.message || 'Failed to check password verification. Please try again.',
              confirmButtonText: 'OK'
            });
          });
        });
      } else {
        console.error('Add button or form not found:', { addProfileBtn, addProfileForm });
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
    });
  </script>
</body>
</html>
