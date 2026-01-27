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
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css" rel="stylesheet">

  <!-- Custom CSS for Employee Profile Pictures -->
  <style>
    .employee-avatar {
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .employee-avatar:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .position-relative:hover .employee-avatar {
      filter: brightness(1.1);
    }

    .avatar-sm, .avatar-lg {
      transition: all 0.3s ease;
    }

    .avatar-sm:hover, .avatar-lg:hover {
      transform: scale(1.05);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Profile picture loading animation */
    .employee-avatar {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .employee-avatar[src] {
      animation: none;
      background: none;
    }

    /* Status indicator styling */
    .position-absolute .badge {
      box-shadow: 0 0 0 2px white;
    }

    /* Tooltip styling for profile pictures */
    .tooltip-inner {
      background-color: rgba(0, 0, 0, 0.9);
      color: white;
      border-radius: 6px;
      padding: 8px 12px;
      font-size: 12px;
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
            <h2 class="fw-bold mb-1">Employee Training Feedback Tracking</h2>
            <p class="text-muted mb-0">
              Monitor and analyze training feedback submitted by employees
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee Training Feedback Tracking</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Feedback Analytics Cards -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-chat-square-text fs-4 text-primary"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="totalFeedback">{{ $totalFeedback ?? 0 }}</h3>
            <p class="text-muted mb-0">Total Feedback</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-star-fill fs-4 text-primary"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="avgRating">{{ number_format($avgRating ?? 0, 1) }}</h3>
            <p class="text-muted mb-0">Average Rating</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-calendar-week fs-4 text-primary"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="thisWeekFeedback">{{ $thisWeekFeedback ?? 0 }}</h3>
            <p class="text-muted mb-0">This Week</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-hand-thumbs-up fs-4 text-primary"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="recommendationRate">{{ number_format($recommendationRate ?? 0, 1) }}%</h3>
            <p class="text-muted mb-0">Recommend Rate</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-bold">Employee</label>
            <select class="form-select" id="employeeFilter">
              <option value="">All Employees</option>
              @if(isset($employees))
                @foreach($employees as $employee)
                  <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Training</label>
            <select class="form-select" id="trainingFilter">
              <option value="">All Trainings</option>
              @if(isset($trainings))
                @foreach($trainings as $training)
                  <option value="{{ $training }}">{{ $training }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Rating</label>
            <select class="form-select" id="ratingFilter">
              <option value="">All Ratings</option>
              <option value="5">5 Stars</option>
              <option value="4">4 Stars</option>
              <option value="3">3 Stars</option>
              <option value="2">2 Stars</option>
              <option value="1">1 Star</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Date Range</label>
            <select class="form-select" id="dateFilter">
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="quarter">This Quarter</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">&nbsp;</label>
            <button class="btn btn-primary w-100" onclick="applyFilters()">
              <i class="bi bi-funnel me-1"></i>Filter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Training Feedback & Competency Requests Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>Training Feedback & Competency Requests</h5>
        <div class="d-flex gap-2">
          <button class="btn btn-success btn-sm" onclick="exportFeedback()">
            <i class="bi bi-download me-1"></i>Export
          </button>
          <button class="btn btn-info btn-sm" onclick="refreshData()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="feedbackTable">
            <thead class="table-light">
              <tr>
                <th>Feedback ID</th>
                <th>Employee</th>
                <th>Training Title</th>
                <th>Overall Rating</th>
                <th>Recommend</th>
                <th>Format</th>
                <th>Submitted Date</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allFeedback ?? [] as $feedback)
                <tr>
                  <td><span class="badge bg-primary">{{ optional($feedback)->feedback_id ?? 'N/A' }}</span></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="position-relative me-2">
                        @php
                          $employee = optional($feedback)->employee;
                          $profilePicture = $employee->profile_picture ?? null;
                          $employeeName = ($employee->first_name ?? 'Unknown') . ' ' . ($employee->last_name ?? 'User');
                          $initials = substr($employee->first_name ?? 'U', 0, 1) . substr($employee->last_name ?? 'U', 0, 1);

                          // Handle profile picture - check if it's base64 encoded data or filename
                          $hasProfilePicture = false;
                          $profilePicturePath = '';

                          if ($profilePicture) {
                              // Check if it's base64 encoded data (starts with data:image)
                              if (strpos($profilePicture, 'data:image') === 0) {
                                  $hasProfilePicture = true;
                                  $profilePicturePath = $profilePicture;
                              }
                              else {
                                  // Clean up filename (remove any path if it's there, but keep it for testing)
                                  $filenameOnly = basename($profilePicture);
                                  
                                  $possiblePaths = [
                                      'storage/' . $profilePicture,
                                      'storage/profile_pictures/' . $filenameOnly,
                                      'storage/employee_photos/' . $filenameOnly,
                                      'assets/employee_photos/' . $filenameOnly,
                                      'uploads/employee_photos/' . $filenameOnly
                                  ];

                                  foreach ($possiblePaths as $path) {
                                      if (file_exists(public_path($path))) {
                                          $hasProfilePicture = true;
                                          $profilePicturePath = asset($path);
                                          break;
                                      }
                                  }
                              }
                          }

                          // If still no profile picture found, try common employee ID based filenames
                          if (!$hasProfilePicture && $employee->employee_id) {
                              $commonFilenames = [
                                  $employee->employee_id . '.jpg',
                                  $employee->employee_id . '.png',
                                  $employee->employee_id . '.jpeg',
                                  strtolower($employee->employee_id) . '.jpg',
                                  strtolower($employee->employee_id) . '.png'
                              ];

                              foreach ($commonFilenames as $filename) {
                                  $possiblePaths = [
                                      'storage/profile_pictures/' . $filename,
                                      'storage/employee_photos/' . $filename,
                                      'assets/employee_photos/' . $filename,
                                      'uploads/employee_photos/' . $filename
                                  ];

                                  foreach ($possiblePaths as $path) {
                                      if (file_exists(public_path($path))) {
                                          $hasProfilePicture = true;
                                          $profilePicturePath = asset($path);
                                          break 2;
                                      }
                                  }
                              }
                          }
                        @endphp

                        @if($hasProfilePicture)
                          <img src="{{ $profilePicturePath }}"
                               alt="{{ $employeeName }}"
                               class="rounded-circle employee-avatar"
                               style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #007bff;"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Employee Profile: {{ $employeeName }}"
                               onerror="this.style.display='none'; this.nextElementSibling.style.setProperty('display', 'flex', 'important');">
                          <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                               style="width: 40px; height: 40px; display: none !important; border: 2px solid #007bff;"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Employee Profile: {{ $employeeName }}">
                            <span class="text-primary fw-bold">{{ $initials }}</span>
                          </div>
                        @else
                          <!-- Debug info for missing profile picture -->
                          <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                               style="width: 40px; height: 40px; border: 2px solid #007bff;"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Employee Profile: {{ $employeeName }} (No photo found - ID: {{ $employee->employee_id ?? 'N/A' }})">
                            <span class="text-primary fw-bold">{{ $initials }}</span>
                          </div>
                        @endif

                        <!-- Online status indicator -->
                        <div class="position-absolute bottom-0 end-0">
                          <span class="badge bg-success rounded-pill" style="width: 12px; height: 12px; padding: 0;"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Active Employee"></span>
                        </div>
                      </div>
                      <div>
                        <div class="fw-bold">
                          {{ $employeeName }}
                          <button class="btn btn-link btn-sm p-0 ms-1" onclick="viewEmployeeProfile('{{ $employee->employee_id ?? 'N/A' }}')" title="View Employee Profile">
                            <i class="bi bi-person-circle text-info"></i>
                          </button>
                        </div>
                        <small class="text-muted">
                          <i class="bi bi-badge-ad me-1"></i>{{ $employee->employee_id ?? 'N/A' }}
                          @if($employee->department)
                            <span class="mx-1">•</span>
                            <i class="bi bi-building me-1"></i>{{ $employee->department }}
                          @endif
                        </small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <strong>{{ optional($feedback)->training_title ?? 'N/A' }}</strong>
                    @if(optional($feedback)->training_completion_date)
                      <br><small class="text-muted">Completed: {{ optional(optional($feedback)->training_completion_date)->format('M d, Y') }}</small>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span class="text-warning me-2">{{ str_repeat('★', optional($feedback)->overall_rating ?? 0) }}{{ str_repeat('☆', 5 - (optional($feedback)->overall_rating ?? 0)) }}</span>
                      <span class="badge bg-{{ (optional($feedback)->overall_rating ?? 0) >= 4 ? 'success' : ((optional($feedback)->overall_rating ?? 0) >= 3 ? 'warning' : 'danger') }}">{{ optional($feedback)->overall_rating ?? 0 }}/5</span>
                    </div>
                  </td>
                  <td>
                    @if(optional($feedback)->recommend_training ?? false)
                      <span class="badge bg-success"><i class="bi bi-check-circle"></i> Yes</span>
                    @else
                      <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> No</span>
                    @endif
                  </td>
                  <td>
                    @if(optional($feedback)->training_format ?? false)
                      <span class="badge bg-info">{{ optional($feedback)->training_format }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    {{ optional(optional($feedback)->submitted_at)->format('M d, Y') ?? 'N/A' }}<br>
                    <small class="text-muted">{{ optional(optional($feedback)->submitted_at)->format('h:i A') ?? 'N/A' }}</small>
                  </td>
                  <td>
                    <span class="badge bg-{{ (optional($feedback)->admin_reviewed ?? false) ? 'success' : 'warning' }}">
                      {{ (optional($feedback)->admin_reviewed ?? false) ? 'Reviewed' : 'Pending' }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewFeedbackModal" onclick="viewFeedbackDetails({{ optional($feedback)->id ?? 0 }})" title="View Details">
                        <i class="bi bi-eye"></i>
                      </button>
                      @if(!(optional($feedback)->admin_reviewed ?? false))
                        <button class="btn btn-success btn-sm" onclick="markAsReviewedWithConfirmation({{ optional($feedback)->id ?? 0 }})" title="Mark as Reviewed">
                          <i class="bi bi-check-circle"></i>
                        </button>
                      @else
                        <button class="btn btn-secondary btn-sm" title="Already Reviewed" disabled>
                          <i class="bi bi-check-all"></i>
                        </button>
                      @endif
                      <button class="btn btn-warning btn-sm" onclick="respondToFeedbackWithConfirmation({{ optional($feedback)->id ?? 0 }})" title="Respond">
                        <i class="bi bi-reply"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
              @endforelse

              <!-- Competency Feedback Requests -->
              @forelse($competencyRequests ?? [] as $request)
                <tr class="table-info">
                  <td><span class="badge bg-info">COMP-{{ $request->id }}</span></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="position-relative me-2">
                        @php
                          $compEmployee = $request->employee;
                          $compProfilePicture = $compEmployee->profile_picture ?? $compEmployee->photo ?? null;
                          $compEmployeeName = ($compEmployee->first_name ?? 'Unknown') . ' ' . ($compEmployee->last_name ?? 'User');
                          $compInitials = substr($compEmployee->first_name ?? 'U', 0, 1) . substr($compEmployee->last_name ?? 'U', 0, 1);
                        @endphp

                        @php
                          $compHasProfilePicture = false;
                          $compProfilePicturePath = '';
                          
                          if ($compProfilePicture) {
                              if (strpos($compProfilePicture, 'data:image') === 0) {
                                  $compHasProfilePicture = true;
                                  $compProfilePicturePath = $compProfilePicture;
                              } else {
                                  $compFilenameOnly = basename($compProfilePicture);
                                  $compPossiblePaths = [
                                      'storage/' . $compProfilePicture,
                                      'storage/profile_pictures/' . $compFilenameOnly,
                                      'storage/employee_photos/' . $compFilenameOnly,
                                      'assets/employee_photos/' . $compFilenameOnly,
                                      'uploads/employee_photos/' . $compFilenameOnly,
                                      'storage/profile_pictures/' . ($compEmployee->employee_id ?? '') . '.jpg',
                                      'storage/profile_pictures/' . ($compEmployee->employee_id ?? '') . '.png'
                                  ];
                                  
                                  foreach ($compPossiblePaths as $path) {
                                      if (!empty($path) && file_exists(public_path($path))) {
                                          $compHasProfilePicture = true;
                                          $compProfilePicturePath = asset($path);
                                          break;
                                      }
                                  }
                              }
                          }
                        @endphp

                        @if($compHasProfilePicture)
                          <img src="{{ $compProfilePicturePath }}"
                               alt="{{ $compEmployeeName }}"
                               class="rounded-circle employee-avatar"
                               style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #17a2b8;"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Employee Profile: {{ $compEmployeeName }}"
                               onerror="this.style.display='none'; this.nextElementSibling.style.setProperty('display', 'flex', 'important');">
                          <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                               style="width: 40px; height: 40px; display: none !important; border: 2px solid #17a2b8;"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Employee Profile: {{ $compEmployeeName }}">
                            <span class="text-info fw-bold">{{ $compInitials }}</span>
                          </div>
                        @else
                          <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                               style="width: 40px; height: 40px; border: 2px solid #17a2b8;"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Employee Profile: {{ $compEmployeeName }}">
                            <span class="text-info fw-bold">{{ $compInitials }}</span>
                          </div>
                        @endif

                        <!-- Competency request indicator -->
                        <div class="position-absolute bottom-0 end-0">
                          <span class="badge bg-info rounded-pill" style="width: 12px; height: 12px; padding: 0;"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Competency Request"></span>
                        </div>
                      </div>
                      <div>
                        <div class="fw-bold">
                          {{ $compEmployeeName }}
                          <button class="btn btn-link btn-sm p-0 ms-1" onclick="viewEmployeeProfile('{{ $compEmployee->employee_id ?? 'N/A' }}')" title="View Employee Profile">
                            <i class="bi bi-person-circle text-info"></i>
                          </button>
                        </div>
                        <small class="text-muted">
                          <i class="bi bi-badge-ad me-1"></i>{{ $compEmployee->employee_id ?? 'N/A' }}
                          @if($compEmployee->department)
                            <span class="mx-1">•</span>
                            <i class="bi bi-building me-1"></i>{{ $compEmployee->department }}
                          @endif
                        </small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <strong>{{ $request->competency->competency_name ?? 'Unknown Competency' }}</strong>
                    <br><small class="text-muted"><i class="bi bi-star me-1"></i>Competency Feedback Request</small>
                  </td>
                  <td>
                    <span class="badge bg-info">Competency</span>
                  </td>
                  <td>
                    <span class="text-muted">-</span>
                  </td>
                  <td>
                    <span class="badge bg-secondary">{{ $request->competency->category ?? 'General' }}</span>
                  </td>
                  <td>
                    {{ $request->created_at->format('M d, Y') }}<br>
                    <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                  </td>
                  <td>
                    <span class="badge bg-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'responded' ? 'success' : 'secondary') }}">
                      {{ ucfirst($request->status) }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button class="btn btn-info btn-sm" onclick="viewCompetencyRequestDetails({{ $request->id }})" title="View Details">
                        <i class="bi bi-eye"></i>
                      </button>
                      @if($request->status == 'pending')
                        <button class="btn btn-warning btn-sm" onclick="respondToCompetencyRequestWithConfirmation({{ $request->id }})" title="Respond">
                          <i class="bi bi-reply"></i>
                        </button>
                      @endif
                      @if($request->status != 'reviewed' && $request->status != 'completed')
                        <button class="btn btn-success btn-sm" onclick="markCompetencyRequestAsReviewedWithConfirmation({{ $request->id }})" title="Mark as Reviewed">
                          <i class="bi bi-check-circle"></i>
                        </button>
                      @else
                        <button class="btn btn-secondary btn-sm" title="Already Reviewed" disabled>
                          <i class="bi bi-check-all"></i>
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                @if(($allFeedback ?? collect())->isEmpty())
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-chat-square-text fs-1 text-muted d-block mb-2"></i>
                    No training feedback or competency requests submitted yet.
                  </td>
                </tr>
                @endif
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- View Feedback Details Modal -->
  <div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Feedback Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="viewFeedbackContent">
          <!-- Content loaded via AJAX -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-success" id="modalMarkAsReviewedBtn" onclick="markCurrentAsReviewed()">Mark as Reviewed</button>
        </div>
      </div>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
  <script>
    let currentFeedbackId = null;



    // View Feedback Details
    function viewFeedbackDetails(feedbackId) {
      console.log('Setting currentFeedbackId to:', feedbackId);
      currentFeedbackId = feedbackId;

      // Show loading state
      document.getElementById('viewFeedbackContent').innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading feedback details...</div>';

      fetch(`/admin/training-feedback/${feedbackId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Feedback data loaded:', data);
          const content = `
            <div class="row">
              <div class="col-md-6">
                <h6 class="fw-bold text-primary">Employee Information</h6>
                <table class="table table-borderless">
                  <tr><td><strong>Employee:</strong></td><td>${data.employee?.first_name || 'Unknown'} ${data.employee?.last_name || 'User'}</td></tr>
                  <tr><td><strong>Employee ID:</strong></td><td>${data.employee?.employee_id || 'N/A'}</td></tr>
                  <tr><td><strong>Department:</strong></td><td>${data.employee?.department || 'N/A'}</td></tr>
                </table>

                <h6 class="fw-bold text-primary mt-4">Training Information</h6>
                <table class="table table-borderless">
                  <tr><td><strong>Training:</strong></td><td>${data.training_title}</td></tr>
                  <tr><td><strong>Format:</strong></td><td>${data.training_format || 'N/A'}</td></tr>
                  <tr><td><strong>Completed:</strong></td><td>${data.training_completion_date || 'N/A'}</td></tr>
                  <tr><td><strong>Submitted:</strong></td><td>${new Date(data.submitted_at).toLocaleDateString()}</td></tr>
                </table>

                <h6 class="fw-bold text-primary mt-4">Ratings</h6>
                <table class="table table-borderless">
                  <tr><td><strong>Overall:</strong></td><td>${'★'.repeat(data.overall_rating)}${'☆'.repeat(5-data.overall_rating)} (${data.overall_rating}/5)</td></tr>
                  <tr><td><strong>Content Quality:</strong></td><td>${data.content_quality ? data.content_quality + '/5' : 'N/A'}</td></tr>
                  <tr><td><strong>Instructor:</strong></td><td>${data.instructor_effectiveness ? data.instructor_effectiveness + '/5' : 'N/A'}</td></tr>
                  <tr><td><strong>Material Relevance:</strong></td><td>${data.material_relevance ? data.material_relevance + '/5' : 'N/A'}</td></tr>
                  <tr><td><strong>Duration:</strong></td><td>${data.training_duration ? data.training_duration + '/5' : 'N/A'}</td></tr>
                  <tr><td><strong>Recommend:</strong></td><td><span class="badge bg-${data.recommend_training ? 'success' : 'secondary'}">${data.recommend_training ? 'Yes' : 'No'}</span></td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6 class="fw-bold text-primary">Detailed Feedback</h6>
                <div class="mb-3">
                  <strong>What they learned:</strong>
                  <p class="text-muted border-start border-3 border-primary ps-3">${data.what_learned || 'No response provided'}</p>
                </div>
                <div class="mb-3">
                  <strong>Most valuable aspect:</strong>
                  <p class="text-muted border-start border-3 border-success ps-3">${data.most_valuable || 'No response provided'}</p>
                </div>
                <div class="mb-3">
                  <strong>Suggestions for improvement:</strong>
                  <p class="text-muted border-start border-3 border-warning ps-3">${data.improvements || 'No response provided'}</p>
                </div>
                <div class="mb-3">
                  <strong>Additional topics:</strong>
                  <p class="text-muted border-start border-3 border-info ps-3">${data.additional_topics || 'No response provided'}</p>
                </div>
                <div class="mb-3">
                  <strong>Additional comments:</strong>
                  <p class="text-muted border-start border-3 border-secondary ps-3">${data.comments || 'No response provided'}</p>
                </div>

                ${data.admin_response ? `
                  <h6 class="fw-bold text-success mt-4">Admin Response</h6>
                  <div class="alert alert-success">
                    <p class="mb-1">${data.admin_response}</p>
                    ${data.action_taken ? `<small><strong>Action:</strong> ${data.action_taken}</small>` : ''}
                  </div>
                ` : ''}
              </div>
            </div>
          `;
          document.getElementById('viewFeedbackContent').innerHTML = content;
          
          // Toggle Mark as Reviewed button in modal footer
          const markAsReviewedBtn = document.getElementById('modalMarkAsReviewedBtn');
          if (data.admin_reviewed) {
            markAsReviewedBtn.style.display = 'none';
          } else {
            markAsReviewedBtn.style.display = 'block';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('viewFeedbackContent').innerHTML = '<div class="alert alert-danger">Error loading feedback details.</div>';
        });
    }

    // Mark as Reviewed with Confirmation
    async function markAsReviewedWithConfirmation(feedbackId) {
      const confirmed = await Swal.fire({
        title: 'Mark as Reviewed',
        text: 'Are you sure you want to mark this feedback as reviewed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Yes, Mark as Reviewed',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-success me-2',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
      });

      if (confirmed.isConfirmed) {
        await markAsReviewed(feedbackId);
      }
    }

    // Mark as Reviewed
    async function markAsReviewed(feedbackId) {
      try {
        // Show loading
        Swal.fire({
          title: 'Processing...',
          text: 'Marking feedback as reviewed',
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch(`/admin/training-feedback/${feedbackId}/review`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        const data = await response.json();

        if (data.success) {
          await Swal.fire({
            title: 'Success!',
            text: 'Feedback has been marked as reviewed.',
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error!',
            text: 'Failed to mark feedback as reviewed.',
            icon: 'error',
            confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
          });
        }
      } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
          title: 'Network Error!',
          text: 'An unexpected error occurred. Please try again.',
          icon: 'error',
          confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
          customClass: {
            confirmButton: 'btn btn-danger'
          },
          buttonsStyling: false
        });
      }
    }

    function markCurrentAsReviewed() {
      console.log('markCurrentAsReviewed called, currentFeedbackId:', currentFeedbackId);
      if (currentFeedbackId && currentFeedbackId !== null && currentFeedbackId !== 0) {
        markAsReviewedWithConfirmation(currentFeedbackId);
      } else {
        Swal.fire({
          title: 'Error',
          text: 'No feedback selected. Please close this modal and try again.',
          icon: 'error',
          confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
          customClass: {
            confirmButton: 'btn btn-danger'
          },
          buttonsStyling: false
        });
      }
    }

    async function respondToFeedbackWithConfirmation(feedbackId) {
      await showRespondToFeedbackForm(feedbackId);
    }

    // Show Respond to Feedback Form
    async function showRespondToFeedbackForm(feedbackId) {
      const { value: formValues } = await Swal.fire({
        title: 'Respond to Training Feedback',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Response</label>
              <textarea id="adminResponse" class="form-control" rows="4" placeholder="Enter your response to this feedback..." required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Action Taken</label>
              <select id="actionTaken" class="form-select">
                <option value="">Select action...</option>
                <option value="Training Updated">Training Content Updated</option>
                <option value="Instructor Notified">Instructor Notified</option>
                <option value="Process Improved">Process Improved</option>
                <option value="No Action Required">No Action Required</option>
                <option value="Under Review">Under Review</option>
              </select>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="notifyEmployee" checked>
                <label class="form-check-label" for="notifyEmployee">
                  Notify employee of response
                </label>
              </div>
            </div>
          </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i>Send Response',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-warning me-2',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        width: '600px',
        preConfirm: () => {
          const adminResponse = document.getElementById('adminResponse').value;
          const actionTaken = document.getElementById('actionTaken').value;
          const notifyEmployee = document.getElementById('notifyEmployee').checked;

          if (!adminResponse.trim()) {
            Swal.showValidationMessage('Admin response is required');
            return false;
          }

          return {
            admin_response: adminResponse,
            action_taken: actionTaken,
            notify_employee: notifyEmployee
          };
        }
      });

      if (formValues) {
        await submitFeedbackResponse(feedbackId, formValues);
      }
    }

    // Submit Feedback Response
    async function submitFeedbackResponse(feedbackId, formData) {
      try {
        // Show loading
        Swal.fire({
          title: 'Sending Response...',
          text: 'Please wait while we send your response',
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch(`/admin/training-feedback/${feedbackId}/respond`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
          await Swal.fire({
            title: 'Response Sent!',
            text: 'Your response has been sent successfully.',
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error!',
            text: 'Failed to send response. Please try again.',
            icon: 'error',
            confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
          });
        }
      } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
          title: 'Network Error!',
          text: 'An unexpected error occurred. Please try again.',
          icon: 'error',
          confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
          customClass: {
            confirmButton: 'btn btn-danger'
          },
          buttonsStyling: false
        });
      }
    }

    // Apply Filters
    function applyFilters() {
      const employee = document.getElementById('employeeFilter').value;
      const training = document.getElementById('trainingFilter').value;
      const rating = document.getElementById('ratingFilter').value;
      const dateRange = document.getElementById('dateFilter').value;

      const params = new URLSearchParams();
      if (employee) params.append('employee', employee);
      if (training) params.append('training', training);
      if (rating) params.append('rating', rating);
      if (dateRange) params.append('date_range', dateRange);

      window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    // Enhanced Export Feedback
    async function exportFeedback() {
      await showExportOptions();
    }

    // Show Export Options
    async function showExportOptions() {
      const { value: exportOptions } = await Swal.fire({
        title: 'Export Training Feedback',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Export Format</label>
              <select id="exportFormat" class="form-select">
                <option value="excel">Excel (.xlsx)</option>
                <option value="csv">CSV (.csv)</option>
                <option value="pdf">PDF Report</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Include Data</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeEmployeeProfiles" checked>
                <label class="form-check-label" for="includeEmployeeProfiles">
                  Employee Profile Information
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeDetailedFeedback" checked>
                <label class="form-check-label" for="includeDetailedFeedback">
                  Detailed Feedback Comments
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeAdminResponses" checked>
                <label class="form-check-label" for="includeAdminResponses">
                  Admin Responses & Actions
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeCompetencyRequests">
                <label class="form-check-label" for="includeCompetencyRequests">
                  Competency Feedback Requests
                </label>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Date Range</label>
              <select id="exportDateRange" class="form-select">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
              </select>
            </div>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <small>Export will include employee profile tracking data and comprehensive feedback analytics.</small>
            </div>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-download me-1"></i>Export Data',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-success me-2',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        width: '600px',
        preConfirm: () => {
          return {
            format: document.getElementById('exportFormat').value,
            includeEmployeeProfiles: document.getElementById('includeEmployeeProfiles').checked,
            includeDetailedFeedback: document.getElementById('includeDetailedFeedback').checked,
            includeAdminResponses: document.getElementById('includeAdminResponses').checked,
            includeCompetencyRequests: document.getElementById('includeCompetencyRequests').checked,
            dateRange: document.getElementById('exportDateRange').value
          };
        }
      });

      if (exportOptions) {
        await performExport(exportOptions);
      }
    }

    // Perform Export
    async function performExport(options) {
      try {
        // Show loading
        Swal.fire({
          title: 'Generating Export...',
          html: `
            <div class="text-center">
              <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p>Preparing ${options.format.toUpperCase()} export with employee profile tracking...</p>
              <small class="text-muted">This may take a few moments for large datasets.</small>
            </div>
          `,
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false
        });

        // Build export URL with parameters
        const params = new URLSearchParams();
        params.append('format', options.format);
        params.append('include_profiles', options.includeEmployeeProfiles ? '1' : '0');
        params.append('include_detailed', options.includeDetailedFeedback ? '1' : '0');
        params.append('include_responses', options.includeAdminResponses ? '1' : '0');
        params.append('include_competency', options.includeCompetencyRequests ? '1' : '0');
        params.append('date_range', options.dateRange);

        // Add current filter parameters
        const currentEmployee = document.getElementById('employeeFilter').value;
        const currentTraining = document.getElementById('trainingFilter').value;
        const currentRating = document.getElementById('ratingFilter').value;
        const currentDateFilter = document.getElementById('dateFilter').value;

        if (currentEmployee) params.append('employee_filter', currentEmployee);
        if (currentTraining) params.append('training_filter', currentTraining);
        if (currentRating) params.append('rating_filter', currentRating);
        if (currentDateFilter) params.append('date_filter', currentDateFilter);

        // Trigger download
        const exportUrl = `/admin/training-feedback/export?${params.toString()}`;

        // Create hidden link and trigger download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = `training_feedback_export_${new Date().toISOString().split('T')[0]}.${options.format}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Show success message
        setTimeout(async () => {
          await Swal.fire({
            title: 'Export Complete!',
            html: `
              <div class="text-center">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                <p>Your ${options.format.toUpperCase()} export has been generated successfully.</p>
                <div class="alert alert-success text-start">
                  <strong>Export includes:</strong>
                  <ul class="mb-0 mt-2">
                    ${options.includeEmployeeProfiles ? '<li>Employee profile information</li>' : ''}
                    ${options.includeDetailedFeedback ? '<li>Detailed feedback comments</li>' : ''}
                    ${options.includeAdminResponses ? '<li>Admin responses & actions</li>' : ''}
                    ${options.includeCompetencyRequests ? '<li>Competency feedback requests</li>' : ''}
                  </ul>
                </div>
              </div>
            `,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
        }, 2000);

      } catch (error) {
        console.error('Export error:', error);
        await Swal.fire({
          title: 'Export Failed!',
          text: 'An error occurred while generating the export. Please try again.',
          icon: 'error',
          confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
          customClass: {
            confirmButton: 'btn btn-danger'
          },
          buttonsStyling: false
        });
      }
    }

    // Enhanced Refresh Data with Analytics Update
    async function refreshData() {
      try {
        // Show loading with progress
        Swal.fire({
          title: 'Refreshing Data...',
          html: `
            <div class="text-center">
              <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p>Updating employee profiles and feedback analytics...</p>
              <div class="progress mb-3" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
              </div>
              <small class="text-muted">Fetching latest data from server...</small>
            </div>
          `,
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            // Animate progress bar
            const progressBar = document.querySelector('.progress-bar');
            let width = 0;
            const interval = setInterval(() => {
              width += 10;
              progressBar.style.width = width + '%';
              if (width >= 90) {
                clearInterval(interval);
              }
            }, 100);
          }
        });

        // Fetch updated analytics data
        const response = await fetch('/admin/training-feedback/analytics', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (response.ok) {
          const data = await response.json();

          // Update analytics cards
          if (data.analytics) {
            document.getElementById('totalFeedback').textContent = data.analytics.totalFeedback || 0;
            document.getElementById('avgRating').textContent = parseFloat(data.analytics.avgRating || 0).toFixed(1);
            document.getElementById('thisWeekFeedback').textContent = data.analytics.thisWeekFeedback || 0;
            document.getElementById('recommendationRate').textContent = parseFloat(data.analytics.recommendationRate || 0).toFixed(1) + '%';
          }

          // Show success and reload
          await Swal.fire({
            title: 'Data Refreshed!',
            html: `
              <div class="text-center">
                <i class="bi bi-arrow-clockwise text-success fs-1 mb-3"></i>
                <p>Employee profiles and feedback data have been updated successfully.</p>
                <div class="row text-center mt-3">
                  <div class="col-6">
                    <div class="card border-primary">
                      <div class="card-body py-2">
                        <small class="text-muted">Total Feedback</small>
                        <div class="fw-bold text-primary">${data.analytics?.totalFeedback || 0}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card border-success">
                      <div class="card-body py-2">
                        <small class="text-muted">Avg Rating</small>
                        <div class="fw-bold text-success">${parseFloat(data.analytics?.avgRating || 0).toFixed(1)}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check me-1"></i>Continue',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false,
            timer: 3000,
            timerProgressBar: true
          });

          // Reload page to show updated data
          location.reload();
        } else {
          throw new Error('Failed to fetch analytics data');
        }

      } catch (error) {
        console.error('Refresh error:', error);
        await Swal.fire({
          title: 'Refresh Failed!',
          text: 'Unable to refresh data. Reloading page instead.',
          icon: 'warning',
          confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Reload Page',
          customClass: {
            confirmButton: 'btn btn-warning'
          },
          buttonsStyling: false
        });
        location.reload();
      }
    }

    // Competency Request Functions
    function viewCompetencyRequestDetails(requestId) {
      fetch(`/admin/competency-feedback/${requestId}`)
        .then(response => response.json())
        .then(data => {
          const content = `
            <div class="row">
              <div class="col-md-6">
                <h6 class="fw-bold text-primary">Employee Information</h6>
                <table class="table table-borderless">
                  <tr><td><strong>Employee:</strong></td><td>${data.employee?.first_name || 'Unknown'} ${data.employee?.last_name || 'User'}</td></tr>
                  <tr><td><strong>Employee ID:</strong></td><td>${data.employee?.employee_id || 'N/A'}</td></tr>
                  <tr><td><strong>Department:</strong></td><td>${data.employee?.department || 'N/A'}</td></tr>
                </table>

                <h6 class="fw-bold text-primary mt-4">Competency Information</h6>
                <table class="table table-borderless">
                  <tr><td><strong>Competency:</strong></td><td>${data.competency?.competency_name || 'Unknown'}</td></tr>
                  <tr><td><strong>Category:</strong></td><td>${data.competency?.category || 'General'}</td></tr>
                  <tr><td><strong>Description:</strong></td><td>${data.competency?.description || 'No description'}</td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6 class="fw-bold text-primary">Request Details</h6>
                <div class="mb-3">
                  <strong>Request Message:</strong>
                  <p class="text-muted border-start border-3 border-primary ps-3">${data.request_message || 'No message provided'}</p>
                </div>
                <div class="mb-3">
                  <strong>Status:</strong>
                  <span class="badge bg-${data.status == 'pending' ? 'warning' : (data.status == 'responded' ? 'success' : 'secondary')}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span>
                </div>
                <div class="mb-3">
                  <strong>Requested:</strong> ${new Date(data.created_at).toLocaleDateString()}
                </div>
                ${data.responded_at ? `<div class="mb-3"><strong>Responded:</strong> ${new Date(data.responded_at).toLocaleDateString()}</div>` : ''}

                ${data.manager_response ? `
                  <h6 class="fw-bold text-success mt-4">Manager Response</h6>
                  <div class="alert alert-success">
                    <p class="mb-1">${data.manager_response}</p>
                    ${data.manager ? `<small><strong>Responded by:</strong> ${data.manager.name}</small>` : ''}
                  </div>
                ` : ''}
              </div>
            </div>
          `;
          document.getElementById('viewFeedbackContent').innerHTML = content;

          // Toggle Mark as Reviewed button in modal footer
          const markAsReviewedBtn = document.getElementById('modalMarkAsReviewedBtn');
          if (data.status === 'reviewed' || data.status === 'completed') {
            markAsReviewedBtn.style.display = 'none';
          } else {
            markAsReviewedBtn.style.display = 'block';
          }

          // Show modal
          const modal = new bootstrap.Modal(document.getElementById('viewFeedbackModal'));
          modal.show();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading request details');
        });
    }

    async function respondToCompetencyRequestWithConfirmation(requestId) {
      await showRespondToCompetencyRequestForm(requestId);
    }

    // Show Respond to Competency Request Form
    async function showRespondToCompetencyRequestForm(requestId) {
      const { value: formValues } = await Swal.fire({
        title: 'Respond to Competency Request',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Manager Response</label>
              <textarea id="managerResponse" class="form-control" rows="4" placeholder="Provide feedback on the employee's competency progress..." required></textarea>
            </div>
          </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i>Send Response',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-warning me-2',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        width: '600px',
        preConfirm: () => {
          const managerResponse = document.getElementById('managerResponse').value;

          if (!managerResponse.trim()) {
            Swal.showValidationMessage('Manager response is required');
            return false;
          }

          return {
            manager_response: managerResponse
          };
        }
      });

      if (formValues) {
        await submitCompetencyResponse(requestId, formValues);
      }
    }

    // Submit Competency Response
    async function submitCompetencyResponse(requestId, formData) {
      try {
        // Show loading
        Swal.fire({
          title: 'Sending Response...',
          text: 'Please wait while we send your response',
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch(`/admin/competency-feedback/${requestId}/respond`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
          await Swal.fire({
            title: 'Response Sent!',
            text: 'Your response has been sent successfully.',
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error!',
            text: 'Failed to send response. Please try again.',
            icon: 'error',
            confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
          });
        }
      } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
          title: 'Network Error!',
          text: 'An unexpected error occurred. Please try again.',
          icon: 'error',
          confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
          customClass: {
            confirmButton: 'btn btn-danger'
          },
          buttonsStyling: false
        });
      }
    }

    // Mark Competency Request as Reviewed with Confirmation
    async function markCompetencyRequestAsReviewedWithConfirmation(requestId) {
      const confirmed = await Swal.fire({
        title: 'Mark as Reviewed',
        text: 'Are you sure you want to mark this competency request as reviewed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Yes, Mark as Reviewed',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-success me-2',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
      });

      if (confirmed.isConfirmed) {
        await markCompetencyRequestAsReviewed(requestId);
      }
    }

    // Mark Competency Request as Reviewed
    async function markCompetencyRequestAsReviewed(requestId) {
      try {
        // Show loading
        Swal.fire({
          title: 'Processing...',
          text: 'Marking request as reviewed',
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch(`/admin/competency-feedback/${requestId}/review`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        const data = await response.json();

        if (data.success) {
          await Swal.fire({
            title: 'Success!',
            text: 'Competency request has been marked as reviewed.',
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error!',
            html: `<div class="text-start">
              <p><strong>Failed to mark request as reviewed.</strong></p>
              <p><small>Error: ${data.message || 'Unknown error'}</small></p>
              <p><small>Request ID: ${requestId}</small></p>
            </div>`,
            icon: 'error',
            confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
          });
        }
      } catch (error) {
        console.error('Fetch error:', error);
        await Swal.fire({
          title: 'Network Error!',
          html: `<div class="text-start">
            <p><strong>An unexpected error occurred.</strong></p>
            <p><small>Error: ${error.message}</small></p>
            <p><small>Request ID: ${requestId}</small></p>
            <p><small>Please check the browser console for more details.</small></p>
          </div>`,
          icon: 'error',
          confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Try Again',
          customClass: {
            confirmButton: 'btn btn-danger'
          },
          buttonsStyling: false
        });
      }
    }

    // Employee Profile Tracking Function
    async function viewEmployeeProfile(employeeId) {
      console.log('viewEmployeeProfile called with ID:', employeeId);

      if (!employeeId || employeeId === 'N/A') {
        await Swal.fire({
          title: 'Profile Not Available',
          text: 'Employee profile information is not available for this record.',
          icon: 'info',
          confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
          customClass: {
            confirmButton: 'btn btn-info'
          },
          buttonsStyling: false
        });
        return;
      }

      try {
        // Show loading
        Swal.fire({
          title: 'Loading Employee Profile...',
          html: `
            <div class="text-center">
              <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p>Fetching profile data for Employee ID: <strong>${employeeId}</strong></p>
              <small class="text-muted">API URL: /admin/employee-profile/${employeeId}</small>
            </div>
          `,
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false
        });

        // Fetch employee profile data
        const apiUrl = `/admin/employee-profile/${employeeId}`;
        console.log('Fetching from URL:', apiUrl);

        const response = await fetch(apiUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (response.ok) {
          const employee = await response.json();
          console.log('Employee data received:', employee);

          await Swal.fire({
            title: 'Employee Profile',
            html: `
              <div class="text-start">
                <div class="row">
                  <div class="col-md-4 text-center">
                    ${(() => {
                      const profilePic = employee.profile_picture || employee.photo;
                      if (!profilePic) return '';
                      
                      // Handle possible paths in JS
                      const filename = profilePic.split('/').pop();
                      const paths = [
                        `/storage/${profilePic}`,
                        `/storage/profile_pictures/${filename}`,
                        `/storage/employee_photos/${filename}`,
                        `/assets/employee_photos/${filename}`
                      ];
                      
                      // We can't use file_exists in JS easily, but we can use onerror to try next
                      return `
                        <img src="/storage/${profilePic}"
                             alt="${employee.first_name || 'Unknown'} ${employee.last_name || 'User'}"
                             class="rounded-circle mx-auto mb-3"
                             style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #007bff;"
                             onerror="this.onerror=function(){this.src='/storage/profile_pictures/${filename}'; this.onerror=function(){this.src='/storage/employee_photos/${filename}'; this.onerror=function(){this.style.display='none'; this.nextElementSibling.style.setProperty('display', 'flex', 'important');}}};">
                        <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; display: none !important; border: 3px solid #007bff;">
                          <span class="text-primary fw-bold fs-2">${employee.first_name?.charAt(0) || 'U'}${employee.last_name?.charAt(0) || 'U'}</span>
                        </div>
                      `;
                    })()}
                    ${!(employee.profile_picture || employee.photo) ? `
                      <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; border: 3px solid #007bff;">
                        <span class="text-primary fw-bold fs-2">${employee.first_name?.charAt(0) || 'U'}${employee.last_name?.charAt(0) || 'U'}</span>
                      </div>
                    ` : ''}
                    <h6 class="fw-bold">${employee.first_name || 'Unknown'} ${employee.last_name || 'User'}</h6>
                    <small class="text-muted">${employee.employee_id || 'N/A'}</small>
                  </div>
                  <div class="col-md-8">
                    <h6 class="fw-bold text-primary mb-3">Profile Information</h6>
                    <table class="table table-borderless table-sm">
                      <tr><td><strong>Employee ID:</strong></td><td>${employee.employee_id || 'N/A'}</td></tr>
                      <tr><td><strong>Department:</strong></td><td>${employee.department || 'Not specified'}</td></tr>
                      <tr><td><strong>Position:</strong></td><td>${employee.position || 'Not specified'}</td></tr>
                      <tr><td><strong>Email:</strong></td><td>${employee.email || 'Not provided'}</td></tr>
                      <tr><td><strong>Phone:</strong></td><td>${employee.phone || 'Not provided'}</td></tr>
                      <tr><td><strong>Hire Date:</strong></td><td>${employee.hire_date ? new Date(employee.hire_date).toLocaleDateString() : 'Not available'}</td></tr>
                      <tr><td><strong>Status:</strong></td><td><span class="badge bg-${employee.status === 'Active' ? 'success' : 'secondary'}">${employee.status || 'Unknown'}</span></td></tr>
                    </table>

                    ${employee.training_stats ? `
                      <h6 class="fw-bold text-success mt-4 mb-3">Training Statistics</h6>
                      <div class="row text-center">
                        <div class="col-4">
                          <div class="card border-primary">
                            <div class="card-body py-2">
                              <div class="fw-bold text-primary">${employee.training_stats.total_feedback || 0}</div>
                              <small class="text-muted">Total Feedback</small>
                            </div>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="card border-success">
                            <div class="card-body py-2">
                              <div class="fw-bold text-success">${parseFloat(employee.training_stats.avg_rating || 0).toFixed(1)}</div>
                              <small class="text-muted">Avg Rating</small>
                            </div>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="card border-info">
                            <div class="card-body py-2">
                              <div class="fw-bold text-info">${employee.training_stats.completed_trainings || 0}</div>
                              <small class="text-muted">Completed</small>
                            </div>
                          </div>
                        </div>
                      </div>
                    ` : ''}
                  </div>
                </div>
              </div>
            `,
            icon: 'info',
            confirmButtonText: '<i class="bi bi-check me-1"></i>Close',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false,
            width: '700px'
          });

        } else {
          // Get error details
          const errorText = await response.text();
          console.error('API Error Response:', errorText);
          console.error('Response status:', response.status);
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

      } catch (error) {
        console.error('Error loading employee profile:', error);

        // Show detailed error information
        await Swal.fire({
          title: 'Profile Not Found',
          html: `
            <div class="text-start">
              <p><strong>Unable to load employee profile.</strong></p>
              <div class="alert alert-warning">
                <small><strong>Debug Information:</strong></small><br>
                <small><strong>Employee ID:</strong> ${employeeId}</small><br>
                <small><strong>API URL:</strong> /admin/employee-profile/${employeeId}</small><br>
                <small><strong>Error:</strong> ${error.message}</small>
              </div>
              <p><small>Possible causes:</small></p>
              <ul class="small">
                <li>Employee record doesn't exist in database</li>
                <li>API route not properly configured</li>
                <li>Authentication/permission issues</li>
                <li>Server error</li>
              </ul>
            </div>
          `,
          icon: 'warning',
          confirmButtonText: '<i class="bi bi-check me-1"></i>OK',
          customClass: {
            confirmButton: 'btn btn-warning'
          },
          buttonsStyling: false,
          width: '600px'
        });
      }
    }

    // Utility Functions
    function applyFilters() {
      const employee = document.getElementById('employeeFilter').value;
      const training = document.getElementById('trainingFilter').value;
      const rating = document.getElementById('ratingFilter').value;
      const dateRange = document.getElementById('dateFilter').value;

      const params = new URLSearchParams();
      if (employee) params.append('employee', employee);
      if (training) params.append('training', training);
      if (rating) params.append('rating', rating);
      if (dateRange) params.append('date_range', dateRange);

      window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    // Initialize tooltips when page loads
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Bootstrap tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>

</body>
</html>
