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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
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

    /* Competency profile item styling */
    .competency-profile-item {
      position: relative;
    }

    .competency-profile-item.border-top {
      border-top: 1px solid #e9ecef !important;
      margin-top: 1rem !important;
      padding-top: 1rem !important;
    }

    /* View All Competencies Button */
    .view-all-competencies-btn {
      transition: all 0.3s ease;
    }

    .view-all-competencies-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Toggle icon rotation */
    .view-all-competencies-btn .toggle-icon {
      transition: transform 0.3s ease;
    }

    .view-all-competencies-btn[aria-expanded="true"] .toggle-icon {
      transform: rotate(180deg);
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

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Error:</strong>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Competency Profile</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('employee_competency_profiles.store') }}" method="POST" class="mb-4" id="addProfileForm">
          @csrf
          <div class="row">
            <div class="col-md-3">
              <select name="employee_id" class="form-control" id="employeeSelect" required>
                <option value="">Select Employee</option>
                @foreach(($allEmployees ?? $employees) as $employee)
                  @php
                    $empId = is_array($employee)
                      ? ($employee['external_employee_id'] ?? $employee['id'] ?? '')
                      : ($employee->employee_id ?? $employee->id ?? '');
                    $firstName = is_array($employee) ? ($employee['first_name'] ?? '') : ($employee->first_name ?? '');
                    $lastName = is_array($employee) ? ($employee['last_name'] ?? '') : ($employee->last_name ?? '');
                  @endphp
                  @if($empId)
                    <option value="{{ $empId }}">{{ $firstName }} {{ $lastName }}</option>
                  @endif
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

          <!-- Skill Gap Detection Section -->
          <div id="skillGapSection" class="mt-4" style="display: none;">
            <div class="card border-warning">
              <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  Detected Skill Gaps for <span id="selectedEmployeeName"></span>
                </h5>
              </div>
              <div class="card-body">
                <div id="skillGapsList" class="row g-3">
                  <!-- Skill gaps will be loaded here -->
                </div>
                <div class="text-center mt-3">
                  <button type="button" class="btn btn-success" id="addAllSkillGapsBtn" style="display: none;">
                    <i class="bi bi-plus-circle-fill me-1"></i>Add All Missing Skills
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>

        <!-- Employee Competency Cards -->
        <div class="row g-4">
          @php
            // Group profiles by employee
            $groupedProfiles = $profiles->groupBy('employee_id');
          @endphp

          @foreach($employees as $employee)
            @php
              $empId = is_array($employee)
                ? ($employee['external_employee_id'] ?? $employee['employee_id'] ?? $employee['id'] ?? '')
                : ($employee->employee_id ?? $employee->id ?? '');

              if (!$empId) continue;

              $employeeProfiles = $groupedProfiles->get($empId, collect());

              $firstName = is_array($employee) ? ($employee['first_name'] ?? 'Unknown') : ($employee->first_name ?? 'Unknown');
              $lastName = is_array($employee) ? ($employee['last_name'] ?? 'Employee') : ($employee->last_name ?? 'Employee');
              $fullName = $firstName . ' ' . $lastName;

              // Check if profile picture exists
              $profilePic = is_array($employee) ? ($employee['profile_picture'] ?? null) : ($employee->profile_picture ?? null);
              $profilePicUrl = null;
              if ($profilePic) {
                  $profilePicUrl = asset('storage/' . $profilePic);
              }

              // Generate consistent color based on employee name for fallback
              $colors = ['FF9A56', 'FF6B9D', '4ECDC4', '45B7D1', 'FFA726', 'AB47BC', 'EF5350', '66BB6A', 'FFCA28', '26A69A'];
              $colorIndex = abs(crc32($empId)) % count($colors);
              $bgColor = $colors[$colorIndex];

              // Fallback to UI Avatars if no profile picture found
              if (!$profilePicUrl) {
                  $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) .
                                 "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
              }
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
                        {{ $employeeProfiles->count() }} Competenc{{ $employeeProfiles->count() === 1 ? 'y' : 'ies' }}
                      </small>
                    </div>
                  </div>
                </div>

                <!-- Card Body with Competency Details -->
                <div class="card-body">
                  <!-- View All Competencies Button -->
                  <div class="d-grid mb-3">
                    <button class="btn btn-outline-primary btn-sm view-all-competencies-btn"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#competencies-{{ $empId }}"
                            aria-expanded="false"
                            aria-controls="competencies-{{ $empId }}">
                      <i class="bi bi-eye me-1"></i>
                      @if($employeeProfiles->count() > 0)
                        View All {{ $employeeProfiles->count() }} Competenc{{ $employeeProfiles->count() === 1 ? 'y' : 'ies' }}
                      @else
                        No Competencies
                      @endif
                      <i class="bi bi-chevron-down ms-1 toggle-icon"></i>
                    </button>
                  </div>

                  <!-- Collapsible Competencies Container -->
                  <div class="collapse" id="competencies-{{ $empId }}">
                    <div class="competencies-list" data-employee-id="{{ $empId }}" data-current-page="1">
                    <!-- All Competencies for this Employee -->
                    @forelse($employeeProfiles as $profileIndex => $profile)
                      <div class="competency-profile-item mb-4 {{ $profileIndex > 0 ? 'border-top pt-3' : '' }}" data-item-index="{{ $profileIndex }}">
                        <!-- Competency Name -->
                        <div class="mb-3">
                          <h6 class="text-muted mb-1">
                            <span class="badge bg-secondary me-2">{{ $profileIndex + 1 }}</span>
                            <i class="bi bi-award me-1"></i>
                            Competency
                          </h6>
                          <p class="fw-semibold mb-0 text-dark">{{ $profile->competency->competency_name }}</p>
                        </div>

                        @php
                          // Calculate display progress for this specific profile
                          $displayProgress = $profile->proficiency_level > 0 ? ($profile->proficiency_level / 5) * 100 : 0;
                          $progressSource = $profile->proficiency_level > 0 ? 'manual' : 'no_data';
                        @endphp

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
                          <span class="badge bg-success fs-6">
                            <i class="bi bi-check-circle me-1"></i>
                            Active
                          </span>
                        </div>


                        <!-- Individual Action Buttons for this competency -->
                        <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                          <!-- View Button -->
                          <button class="btn btn-info btn-sm view-btn"
                                  data-employee-name="{{ $firstName }} {{ $lastName }}"
                                  data-competency-name="{{ $profile->competency->competency_name }}"
                                  data-proficiency="{{ $profile->proficiency_level }}"
                                  data-assessment-date="{{ $profile->assessment_date }}"
                                  title="View Details">
                            <i class="bi bi-eye me-1"></i>View
                          </button>
                        </div>
                      </div> <!-- End competency-profile-item -->
                    @empty
                      <div class="text-center py-4">
                        <i class="bi bi-info-circle text-muted mb-2 fs-3"></i>
                        <p class="text-muted mb-0">No competency profiles found for this employee.</p>
                      </div>
                    @endforelse
                    </div>

                    <!-- Pagination Controls -->
                    <div class="competencies-pagination d-flex justify-content-center align-items-center gap-2 mt-3" id="pagination-{{ $empId }}" style="display: none;">
                        <button class="btn btn-sm btn-outline-secondary prev-page-btn" data-employee-id="{{ $empId }}" disabled>
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="page-info small text-muted">Page <span class="current-page">1</span> of <span class="total-pages">1</span></span>
                        <button class="btn btn-sm btn-outline-secondary next-page-btn" data-employee-id="{{ $empId }}">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                  </div> <!-- End collapsible competencies container -->

                  <!-- Employee-level Action Buttons -->
                  <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        @if(is_a($employees, 'Illuminate\Pagination\LengthAwarePaginator'))
          <div class="mt-4 d-flex justify-content-center">
            {{ $employees->links('pagination::bootstrap-5') }}
          </div>
        @endif

        @if(method_exists($employees, 'total') ? $employees->total() === 0 : empty($employees))
          <div class="text-center py-5 empty-state">
            <div class="mb-4">
              <i class="bi bi-person-x display-1 text-muted"></i>
            </div>
            <h4 class="text-muted">No Employees Found</h4>
            <p class="text-muted">There are no employees in the system to display competency profiles for.</p>
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
                  @php
                    if (is_array($employee)) {
                      $empId = $employee['external_employee_id'] ?? $employee['id'] ?? '';
                      $firstName = $employee['first_name'] ?? '';
                      $lastName = $employee['last_name'] ?? '';
                    } else {
                      $empId = $employee->employee_id ?? $employee->id ?? '';
                      $firstName = $employee->first_name ?? '';
                      $lastName = $employee->last_name ?? '';
                    }
                  @endphp
                  @if($empId)
                    <option value="{{ $empId }}">{{ $firstName }} {{ $lastName }}</option>
                  @endif
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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    // Initialize global objects to prevent undefined errors - MUST BE FIRST
    try {
      if (typeof window.translationService === 'undefined') {
        window.translationService = {
          translate: function(key, params) { return key; },
          get: function(key, params) { return key; },
          trans: function(key, params) { return key; },
          choice: function(key, count, params) { return key; }
        };
      }

      // Add global trans function
      if (typeof window.trans === 'undefined') {
        window.trans = function(key, params) { return key; };
      }

      // Add app object if missing
      if (typeof window.app === 'undefined') {
        window.app = {};
      }

      console.log('Global objects initialized successfully');
    } catch (error) {
      console.error('Error initializing global objects:', error);
    }

    // Enhanced CSRF token getter with multiple fallbacks
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

    document.addEventListener('DOMContentLoaded', function() {
      const editButtons = document.querySelectorAll('.edit-btn');
      const viewButtons = document.querySelectorAll('.view-btn');
      const deleteButtons = document.querySelectorAll('.delete-btn');
      const notifyButtons = document.querySelectorAll('.notify-course-btn');
      const viewAwardsButtons = document.querySelectorAll('.view-awards-btn');
      const editModal = new bootstrap.Modal(document.getElementById('editModal'));
      const editForm = document.getElementById('editForm');
      const employeeSelect = document.getElementById('employeeSelect');
      const skillGapSection = document.getElementById('skillGapSection');
      const skillGapsList = document.getElementById('skillGapsList');
      const addAllSkillGapsBtn = document.getElementById('addAllSkillGapsBtn');
      let passwordConfirmModal = null;
      let passwordConfirmForm = null;
      let currentSkillGaps = [];

      // Initialize Select2 for all employee selects
      $('select[name="employee_id"]').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select employee...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1,
        matcher: function(params, data) {
          // If there are no search terms, return all of the data
          if ($.trim(params.term) === '') {
            return data;
          }

          // Do not display the item if there is no 'text' property
          if (typeof data.text === 'undefined') {
            return null;
          }

          // `params.term` should be the term that the user is searching for
          // `data.text` is the text that is displayed for the data object
          if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            var modifiedData = $.extend({}, data, true);
            return modifiedData;
          }

          // Return `null` if the term does not match
          return null;
        },
        language: {
          noResults: function() {
            return 'No employee found';
          }
        },
        dropdownCss: {
          'min-width': '100%'
        }
      });

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


      let pendingAction = null; // Store the action to perform after password verification

      // Employee selection change handler for skill gap detection and skills display
      if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
          const selectedEmployeeId = this.value;
          const selectedEmployeeName = this.options[this.selectedIndex].text;

          if (selectedEmployeeId) {
            const selectedEmployeeNameElement = document.getElementById('selectedEmployeeName');
            if (selectedEmployeeNameElement) {
              selectedEmployeeNameElement.textContent = selectedEmployeeName;
            }

            // Show employee's existing skills first
            showEmployeeSkills(selectedEmployeeId, selectedEmployeeName);

            // Then detect skill gaps
            detectSkillGaps(selectedEmployeeId, selectedEmployeeName);
          } else {
            skillGapSection.style.display = 'none';
          }
        });
      }

      // Function to check if password is already verified
      function checkPasswordVerification() {
        console.log('Checking password verification...');
        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          return Promise.reject(new Error('CSRF token not available'));
        }

        return fetch('{{ route("admin.check_password_verification") }}', {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
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
        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          return Promise.reject(new Error('CSRF token not available'));
        }

        return fetch('{{ route("admin.verify_password") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
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

        const passwordError = document.getElementById('passwordError');
        const confirmPassword = document.getElementById('confirmPassword');

        if (passwordError) {
          passwordError.textContent = '';
        }
        if (confirmPassword) {
          confirmPassword.classList.remove('is-invalid');
        }

        passwordConfirmModal.show();
      }

      // Password confirmation form handler
      if (passwordConfirmForm) {
        passwordConfirmForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const confirmPasswordElement = document.getElementById('confirmPassword');
          const passwordErrorElement = document.getElementById('passwordError');

          if (!confirmPasswordElement || !passwordErrorElement) {
            console.error('Password confirmation elements not found');
            return;
          }

          const password = confirmPasswordElement.value;
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;

          if (!password) {
            confirmPasswordElement.classList.add('is-invalid');
            passwordErrorElement.textContent = 'Password is required';
            return;
          }

          // Clear previous errors
          confirmPasswordElement.classList.remove('is-invalid');
          passwordErrorElement.textContent = '';

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
            if (confirmPasswordElement) {
              confirmPasswordElement.classList.add('is-invalid');
            }
            if (passwordErrorElement) {
              passwordErrorElement.textContent = error.message || 'Invalid password';
            }
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
        // Check password verification silently in background
        checkPasswordVerification().then(isVerified => {
          if (isVerified) {
            // Password already verified, proceed directly to edit form
            showEditForm(profileId, employeeId, competencyId, proficiency, assessmentDate);
          } else {
            // Password verification required, but handle without showing sensitive details
            Swal.fire({
              title: 'Security Verification Required',
              text: 'Please enter your password to continue.',
              input: 'password',
              inputPlaceholder: 'Enter your password',
              showCancelButton: true,
              confirmButtonText: 'Continue',
              cancelButtonText: 'Cancel',
              inputValidator: (value) => {
                if (!value) {
                  return 'Password is required';
                }
              }
            }).then((result) => {
              if (result.isConfirmed) {
                verifyPasswordAndEdit(profileId, employeeId, competencyId, proficiency, assessmentDate, result.value);
              }
            });
          }
        }).catch(error => {
          console.error('Password verification check failed:', error);
          // Fallback to password prompt without showing sensitive details
          Swal.fire({
            title: 'Security Verification Required',
            text: 'Please enter your password to continue.',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
              if (!value) {
                return 'Password is required';
              }
            }
          }).then((result) => {
            if (result.isConfirmed) {
              verifyPasswordAndEdit(profileId, employeeId, competencyId, proficiency, assessmentDate, result.value);
            }
          });
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
        // First show deletion warning without sensitive details
        Swal.fire({
          title: '<i class="bi bi-exclamation-triangle text-warning"></i> Delete Confirmation',
          html: `
            <div class="text-start mb-3">
              <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Warning:</strong> This action cannot be undone!
              </div>
              <p class="text-muted mb-3">
                Are you sure you want to delete this competency profile?
              </p>
            </div>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-trash"></i> Yes, Delete',
          cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d'
        }).then((result) => {
          if (result.isConfirmed) {
            // Check password verification silently in background
            checkPasswordVerification().then(isVerified => {
              if (isVerified) {
                // Password already verified, proceed with deletion
                submitDeleteProfile(profileId, null);
              } else {
                // Password verification required
                Swal.fire({
                  title: 'Security Verification Required',
                  text: 'Please enter your password to confirm deletion.',
                  input: 'password',
                  inputPlaceholder: 'Enter your password',
                  showCancelButton: true,
                  confirmButtonText: 'Delete',
                  cancelButtonText: 'Cancel',
                  confirmButtonColor: '#dc3545',
                  inputValidator: (value) => {
                    if (!value) {
                      return 'Password is required';
                    }
                  }
                }).then((passwordResult) => {
                  if (passwordResult.isConfirmed) {
                    submitDeleteProfile(profileId, passwordResult.value);
                  }
                });
              }
            }).catch(error => {
              console.error('Password verification check failed:', error);
              // Fallback to password prompt
              Swal.fire({
                title: 'Security Verification Required',
                text: 'Please enter your password to confirm deletion.',
                input: 'password',
                inputPlaceholder: 'Enter your password',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                inputValidator: (value) => {
                  if (!value) {
                    return 'Password is required';
                  }
                }
              }).then((passwordResult) => {
                if (passwordResult.isConfirmed) {
                  submitDeleteProfile(profileId, passwordResult.value);
                }
              });
            });
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

        // Verify password first (if password provided, otherwise assume already verified)
        const passwordPromise = password ? verifyPassword(password) : Promise.resolve();
        passwordPromise.then(() => {
          // Password verified, proceed with deletion
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/employee-competency-profiles/${profileId}`;

          const csrfToken = getCSRFToken();
          if (!csrfToken) {
            throw new Error('CSRF token not available');
          }

          const csrfTokenInput = document.createElement('input');
          csrfTokenInput.type = 'hidden';
          csrfTokenInput.name = '_token';
          csrfTokenInput.value = csrfToken;

          const methodField = document.createElement('input');
          methodField.type = 'hidden';
          methodField.name = '_method';
          methodField.value = 'DELETE';

          form.appendChild(csrfTokenInput);
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
            // Retry the delete process - note: employeeName and competencyName not available here
            // This is acceptable since we're removing detailed info anyway
            console.log('Delete failed, user can try again manually');
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

      // View Awards functionality
      viewAwardsButtons.forEach(button => {
        button.addEventListener('click', function() {
          const employeeId = this.getAttribute('data-employee-id');
          const employeeName = this.getAttribute('data-employee-name');

          viewEmployeeAwards(employeeId, employeeName);
        });
      });

      // Function to view employee awards
      function viewEmployeeAwards(employeeId, employeeName) {
        // Show loading dialog
        Swal.fire({
          title: `<i class="bi bi-trophy text-warning"></i> Awards for ${employeeName}`,
          html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading awards...</p></div>',
          showConfirmButton: false,
          allowOutsideClick: true,
          width: '800px',
          timer: 15000,
          timerProgressBar: true
        });

        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          Swal.fire({
            icon: 'error',
            title: 'Security Error',
            text: 'Security token not found. Please refresh the page and try again.',
            timer: 3000
          });
          return;
        }

        fetch(`/admin/employee-awards/employee/${employeeId}`, {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayEmployeeAwards(data.awards, employeeName, data.total_awards);
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to load employee awards.',
              timer: 3000
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load employee awards. Please try again.',
            timer: 3000
          });
        });
      }

      // Function to display employee awards
      function displayEmployeeAwards(awards, employeeName, totalAwards) {
        if (awards.length === 0) {
          Swal.fire({
            title: `<i class="bi bi-trophy text-warning"></i> Awards for ${employeeName}`,
            html: `
              <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                This employee has no awards yet.
              </div>
              <div class="text-center py-4">
                <i class="bi bi-trophy display-4 text-muted mb-3"></i>
                <h5 class="text-muted">No Awards Found</h5>
                <p class="text-muted">Awards will appear here once they are requested and approved.</p>
              </div>
            `,
            confirmButtonText: 'Close',
            width: '600px'
          });
          return;
        }

        let html = `
          <div class="mb-3">
            <span class="badge bg-success fs-6">${totalAwards} Total Awards</span>
          </div>
          <div class="row g-3">
        `;

        awards.forEach(award => {
          const statusBadgeClass = getStatusBadgeClass(award.status);
          const statusText = award.status.charAt(0).toUpperCase() + award.status.slice(1);

          html += `
            <div class="col-md-6">
              <div class="card h-100 border-success">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title text-success mb-0">
                      <i class="bi bi-trophy me-1"></i>
                      ${award.award_name}
                    </h6>
                    <span class="badge ${statusBadgeClass}">${statusText}</span>
                  </div>
                  <p class="card-text">
                    <strong>Type:</strong> ${award.award_type}<br>
                    <strong>Date:</strong> ${new Date(award.award_date).toLocaleDateString()}<br>
                    <strong>Awarded By:</strong> ${award.awarded_by}
                  </p>
                  ${award.description ? `<p class="card-text"><small class="text-muted">${award.description}</small></p>` : ''}
                  ${award.notes ? `<div class="mt-2"><small class="badge bg-light text-dark">Note: ${award.notes}</small></div>` : ''}
                </div>
              </div>
            </div>
          `;
        });

        html += '</div>';

        Swal.fire({
          title: `<i class="bi bi-trophy text-warning"></i> Awards for ${employeeName}`,
          html: html,
          confirmButtonText: 'Close',
          width: '900px',
          customClass: {
            htmlContainer: 'text-start'
          },
          showClass: {
            popup: 'animate__animated animate__fadeInUp'
          }
        });
      }

      // Add Profile with Password Confirmation
      const addProfileBtn = document.getElementById('addProfileBtn');
      const addProfileForm = document.getElementById('addProfileForm');

      console.log('Add Profile Button:', addProfileBtn);
      console.log('Add Profile Form:', addProfileForm);

      if (addProfileBtn && addProfileForm) {
        console.log('Adding click event listener to Add Profile button');
        addProfileBtn.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Add Profile button clicked!');
          addProfileWithConfirmation();
        });
      } else {
        console.error('Add Profile button or form not found!');
        if (!addProfileBtn) console.error('addProfileBtn element not found');
        if (!addProfileForm) console.error('addProfileForm element not found');
      }

      function addProfileWithConfirmation() {
        console.log('addProfileWithConfirmation function called');

        // Validate required fields first
        const employeeSelect = addProfileForm.querySelector('select[name="employee_id"]');
        const competencySelect = addProfileForm.querySelector('select[name="competency_id"]');
        const proficiencyInput = addProfileForm.querySelector('input[name="proficiency_level"]');
        const dateInput = addProfileForm.querySelector('input[name="assessment_date"]');

        console.log('Form elements found:', {
          employeeSelect: employeeSelect,
          competencySelect: competencySelect,
          proficiencyInput: proficiencyInput,
          dateInput: dateInput
        });

        if (!employeeSelect.value || !competencySelect.value || !proficiencyInput.value || !dateInput.value) {
          Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please fill in all required fields before proceeding.',
            confirmButtonText: 'OK'
          });
          return;
        }

        // Always show password verification for security
        Swal.fire({
          title: 'Security Verification Required',
          text: 'Please enter your password to add this competency profile.',
          input: 'password',
          inputPlaceholder: 'Enter your password',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-plus-circle"></i> Add Profile',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#198754',
          inputValidator: (value) => {
            if (!value) {
              return 'Password is required';
            }
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
        // Check password verification silently in background
        checkPasswordVerification().then(isVerified => {
          if (isVerified) {
            // Password already verified, proceed directly with notification
            Swal.fire({
              title: 'Send Notification',
              text: 'Send notification to course management?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: '<i class="bi bi-bell"></i> Send Notification',
              cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
              confirmButtonColor: '#198754',
              cancelButtonColor: '#6c757d'
            }).then((result) => {
              if (result.isConfirmed) {
                submitNotificationRequest(profileId, competencyId, competencyName, employeeName, proficiency, null);
              }
            });
          } else {
            // Password verification required, but handle without showing sensitive details
            Swal.fire({
              title: 'Security Verification Required',
              text: 'Please enter your password to send notification.',
              input: 'password',
              inputPlaceholder: 'Enter your password',
              showCancelButton: true,
              confirmButtonText: 'Send Notification',
              cancelButtonText: 'Cancel',
              confirmButtonColor: '#198754',
              inputValidator: (value) => {
                if (!value) {
                  return 'Password is required';
                }
              }
            }).then((result) => {
              if (result.isConfirmed) {
                submitNotificationRequest(profileId, competencyId, competencyName, employeeName, proficiency, result.value);
              }
            });
          }
        }).catch(error => {
          console.error('Password verification check failed:', error);
          // Fallback to password prompt without showing sensitive details
          Swal.fire({
            title: 'Security Verification Required',
            text: 'Please enter your password to send notification.',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            showCancelButton: true,
            confirmButtonText: 'Send Notification',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#198754',
            inputValidator: (value) => {
              if (!value) {
                return 'Password is required';
              }
            }
          }).then((result) => {
            if (result.isConfirmed) {
              submitNotificationRequest(profileId, competencyId, competencyName, employeeName, proficiency, result.value);
            }
          });
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

        // Verify password first (if password provided, otherwise assume already verified)
        const passwordPromise = password ? verifyPassword(password) : Promise.resolve();
        passwordPromise.then(() => {
          // Password verified, send notification
          const csrfToken = getCSRFToken();
          if (!csrfToken) {
            throw new Error('Security token not available');
          }

          return fetch(`/admin/employee-competency-profiles/${profileId}/notify-course-management`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
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

      // Function to show employee's existing skills
      function showEmployeeSkills(employeeId, employeeName) {
        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          Swal.fire({
            icon: 'error',
            title: 'Security Error',
            text: 'Security token not found. Please refresh the page and try again.',
            confirmButtonText: 'OK'
          });
          return;
        }

        Swal.fire({
          title: `<i class="bi bi-person-badge text-primary"></i> Current Skills for ${employeeName}`,
          html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading existing skills...</p></div>',
          showConfirmButton: false,
          allowOutsideClick: true,
          width: '900px',
          timer: 15000, // Auto close after 15 seconds
          timerProgressBar: true
        });

        fetch(`/admin/employee-competency-profiles/get-employee-skills/${employeeId}`, {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
        .then(response => {
          if (!response.ok) {
            if (response.status === 404) {
              throw new Error('Employee skills endpoint not found.');
            } else if (response.status === 500) {
              throw new Error('Server error occurred while loading skills.');
            } else {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            displayEmployeeSkills(data.skills, employeeName, data.total_skills);
          } else {
            Swal.fire({
              icon: 'info',
              title: 'No Skills Found',
              text: data.message || 'This employee has no competency profiles yet.',
              confirmButtonText: 'OK',
              timer: 3000
            });
          }
        })
        .catch(error => {
          console.error('Employee skills loading error:', error);
          Swal.fire({
            icon: 'warning',
            title: 'Skills Loading Unavailable',
            html: `
              <div class="text-start">
                <p>${error.message || 'Failed to load employee skills.'}</p>
                <p class="text-muted small">
                  <i class="bi bi-lightbulb me-1"></i>
                  You can still view and manage competency profiles using the main interface.
                </p>
              </div>
            `,
            confirmButtonText: 'OK',
            timer: 5000
          });
        });
      }

      // Function to display employee skills in modal
      function displayEmployeeSkills(skills, employeeName, totalSkills) {
        if (skills.length === 0) {
          const employeeId = document.getElementById('employeeSelect').value;

          Swal.fire({
            title: `<i class="bi bi-person-badge text-primary"></i> Current Skills for ${employeeName}`,
            html: `
              <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                This employee has no competency profiles yet.
              </div>
              <div class="card border-primary">
                <div class="card-body text-center">
                  <i class="bi bi-magic display-4 text-primary mb-3"></i>
                  <h5 class="card-title text-primary">Auto-Initialize Basic Skills</h5>
                  <p class="card-text text-muted">
                    We can automatically set up basic competency profiles for this employee with common workplace skills like Communication, Customer Service, Problem-Solving, and more.
                  </p>
                  <p class="small text-muted">
                    <i class="bi bi-lightbulb me-1"></i>
                    All skills will start at Level 1 (Beginner) and can be updated later.
                  </p>
                </div>
              </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-magic me-1"></i> Initialize Basic Skills',
            cancelButtonText: 'Manual Add Only',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            width: '700px'
          }).then((result) => {
            if (result.isConfirmed) {
              initializeBasicSkills(employeeId, employeeName);
            }
          });
          return;
        }

        let html = `
          <div class="mb-3">
            <span class="badge bg-primary fs-6">${totalSkills} Total Skills</span>
          </div>
          <div class="row g-3">
        `;

        skills.forEach(skill => {
          const proficiencyPercent = Math.round((skill.proficiency_level / 5) * 100);
          const statusBadge = skill.proficiency_level >= 5 ?
            '<span class="badge bg-success">Expert</span>' :
            skill.proficiency_level >= 4 ?
            '<span class="badge bg-info">Advanced</span>' :
            skill.proficiency_level >= 3 ?
            '<span class="badge bg-warning">Proficient</span>' :
            skill.proficiency_level >= 2 ?
            '<span class="badge bg-secondary">Developing</span>' :
            '<span class="badge bg-danger">Beginner</span>';

          const progressBarColor = skill.proficiency_level >= 4 ? 'bg-success' :
                                   skill.proficiency_level >= 3 ? 'bg-info' :
                                   skill.proficiency_level >= 2 ? 'bg-warning' : 'bg-danger';

          html += `
            <div class="col-md-6">
              <div class="card h-100 border-primary">
                <div class="card-body">
                  <h6 class="card-title text-primary">
                    <i class="bi bi-award me-1"></i>
                    ${skill.competency_name}
                  </h6>
                  <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <small class="text-muted">Proficiency Level</small>
                      <span class="fw-bold">${skill.proficiency_level}/5 (${proficiencyPercent}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar ${progressBarColor}" style="width: ${proficiencyPercent}%"></div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                      <i class="bi bi-calendar-check me-1"></i>
                      ${new Date(skill.assessment_date).toLocaleDateString()}
                    </small>
                    ${statusBadge}
                  </div>
                  ${skill.category ? `<div class="mt-2"><small class="badge bg-light text-dark">${skill.category}</small></div>` : ''}
                </div>
              </div>
            </div>
          `;
        });

        html += '</div>';

        Swal.fire({
          title: `<i class="bi bi-person-badge text-primary"></i> Current Skills for ${employeeName}`,
          html: html,
          confirmButtonText: 'Close',
          width: '1000px',
          customClass: {
            htmlContainer: 'text-start'
          },
          showClass: {
            popup: 'animate__animated animate__fadeInUp'
          }
        });
      }

      // Function to detect skill gaps for selected employee
      function detectSkillGaps(employeeId, employeeName) {
        const skillGapSection = document.getElementById('skillGapSection');
        const skillGapContent = document.getElementById('skillGapsList');

        // Check if elements exist
        if (!skillGapSection || !skillGapContent) {
          console.error('Skill gap elements not found');
          return;
        }

        // Show loading state
        skillGapContent.innerHTML = `
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Detecting skill gaps for ${employeeName}...</p>
          </div>
        `;
        skillGapSection.style.display = 'block';

        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          skillGapContent.innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Security token not found. Please refresh the page and try again.
            </div>
          `;
          return;
        }

        fetch(`/admin/employee-competency-profiles/detect-skill-gaps/${employeeId}`, {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
        .then(response => {
          if (!response.ok) {
            // Handle different HTTP status codes
            if (response.status === 404) {
              throw new Error('Skill gap detection endpoint not found. This feature may not be available.');
            } else if (response.status === 500) {
              throw new Error('Server error occurred while detecting skill gaps. Please try again later.');
            } else {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            displaySkillGaps(data.skill_gaps, data.total_gaps, employeeId, employeeName);
          } else {
            skillGapContent.innerHTML = `
              <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                ${data.message || 'No skill gaps detected for this employee.'}
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Skill gap detection error:', error);

          // Show user-friendly error message with fallback option
          skillGapContent.innerHTML = `
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Skill Gap Detection Unavailable</strong><br>
              ${error.message || 'Failed to detect skill gaps automatically.'}<br>
              <small class="text-muted mt-2 d-block">
                <i class="bi bi-lightbulb me-1"></i>
                You can still manually add competency profiles using the form above.
              </small>
            </div>
            <div class="text-center mt-3">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="skillGapSection.style.display='none'">
                <i class="bi bi-x-circle me-1"></i>Hide This Section
              </button>
            </div>
          `;
        });
      }

      // Function to display skill gaps
      function displaySkillGaps(skillGaps, totalGaps, employeeId, employeeName) {
        const skillGapContent = document.getElementById('skillGapsList');

        // Check if element exists
        if (!skillGapContent) {
          console.error('skillGapsList element not found');
          return;
        }

        if (skillGaps.length === 0) {
          skillGapContent.innerHTML = `
            <div class="alert alert-success">
              <i class="bi bi-check-circle me-2"></i>
              <strong>Great!</strong> ${employeeName} has no skill gaps. All competencies are covered.
            </div>
          `;
          return;
        }

        let html = `
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <span class="badge bg-warning fs-6">${totalGaps} Missing Skills</span>
            </div>
            <button type="button" class="btn btn-success btn-sm" onclick="addAllMissingSkills(${employeeId}, '${employeeName}')">
              <i class="bi bi-plus-circle me-1"></i>Add All Missing Skills
            </button>
          </div>
          <div class="row g-3">
        `;

        skillGaps.forEach(gap => {
          const priorityClass = gap.priority === 'High' ? 'bg-danger' :
                               gap.priority === 'Medium' ? 'bg-warning' : 'bg-info';

          html += `
            <div class="col-md-6">
              <div class="card border-warning">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title text-warning mb-0">
                      <i class="bi bi-exclamation-triangle me-1"></i>
                      ${gap.competency_name}
                    </h6>
                    <span class="badge ${priorityClass}">${gap.priority}</span>
                  </div>
                  <p class="card-text small text-muted">${gap.description || 'No description available'}</p>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Category: ${gap.category}</small>
                    <button type="button" class="btn btn-outline-success btn-sm"
                            onclick="addSingleMissingSkill(${gap.competency_id}, '${gap.competency_name}', ${employeeId}, '${employeeName}')">
                      <i class="bi bi-plus me-1"></i>Add
                    </button>
                  </div>
                </div>
              </div>
            </div>
          `;
        });

        html += '</div>';
        skillGapContent.innerHTML = html;
      }

      // Function to add single missing skill
      function addSingleMissingSkill(competencyId, competencyName, employeeId, employeeName) {
        // Implementation for adding single skill
        console.log(`Adding skill: ${competencyName} for employee: ${employeeName}`);
      }

      // Function to add all missing skills
      function addAllMissingSkills(employeeId, employeeName) {
        // Implementation for adding all missing skills
        console.log(`Adding all missing skills for employee: ${employeeName}`);
      }

      // Function to initialize basic skills for employee
      function initializeBasicSkills(employeeId, employeeName) {
        // Check password verification silently in background
        checkPasswordVerification().then(isVerified => {
          if (isVerified) {
            // Password already verified, proceed directly with initialization
            Swal.fire({
              title: 'Initialize Basic Skills',
              text: 'Initialize basic competency profiles for this employee?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: '<i class="bi bi-magic"></i> Initialize Skills',
              cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
              confirmButtonColor: '#198754',
              cancelButtonColor: '#6c757d'
            }).then((result) => {
              if (result.isConfirmed) {
                submitInitializeBasicSkills(employeeId, employeeName, null);
              }
            });
          } else {
            // Password verification required, but handle without showing sensitive details
            Swal.fire({
              title: 'Security Verification Required',
              text: 'Please enter your password to initialize basic skills.',
              input: 'password',
              inputPlaceholder: 'Enter your password',
              showCancelButton: true,
              confirmButtonText: 'Initialize Skills',
              cancelButtonText: 'Cancel',
              confirmButtonColor: '#198754',
              inputValidator: (value) => {
                if (!value) {
                  return 'Password is required';
                }
              }
            }).then((result) => {
              if (result.isConfirmed) {
                submitInitializeBasicSkills(employeeId, employeeName, result.value);
              }
            });
          }
        }).catch(error => {
          console.error('Password verification check failed:', error);
          // Fallback to password prompt without showing sensitive details
          Swal.fire({
            title: 'Security Verification Required',
            text: 'Please enter your password to initialize basic skills.',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            showCancelButton: true,
            confirmButtonText: 'Initialize Skills',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#198754',
            inputValidator: (value) => {
              if (!value) {
                return 'Password is required';
              }
            }
          }).then((result) => {
            if (result.isConfirmed) {
              submitInitializeBasicSkills(employeeId, employeeName, result.value);
            }
          });
        });
      }

      // Function to submit basic skills initialization
      function submitInitializeBasicSkills(employeeId, employeeName, password) {
        Swal.fire({
          title: 'Processing...',
          text: 'Initializing basic skills...',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Verify password first (if password provided, otherwise assume already verified)
        const passwordPromise = password ? verifyPassword(password) : Promise.resolve();
        passwordPromise.then(() => {
          // Password verified, proceed with initialization
          const csrfToken = getCSRFToken();
          if (!csrfToken) {
            throw new Error('Security token not available');
          }

          return fetch(`/admin/employee-competency-profiles/initialize-basic-skills/${employeeId}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          });
        }).then(response => {
          if (!response.ok) {
            return response.text().then(text => {
              let errorMessage = `Initialization failed (${response.status})`;
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
              title: 'Basic Skills Initialized Successfully!',
              html: `
                <div class="text-start">
                  <p><strong>Employee:</strong> ${employeeName}</p>
                  <p><strong>Skills Created:</strong> ${data.total_created}</p>
                  <div class="mt-3">
                    <h6>Initialized Skills:</h6>
                    <ul class="list-unstyled">
                      ${data.created_profiles.map(profile =>
                        `<li><i class="bi bi-check-circle text-success me-2"></i>${profile.competency_name} (Level ${profile.proficiency_level})</li>`
                      ).join('')}
                    </ul>
                  </div>
                </div>
              `,
              confirmButtonText: 'Great!',
              confirmButtonColor: '#198754',
              timer: 8000,
              timerProgressBar: true
            }).then(() => {
              // Refresh the employee skills display
              showEmployeeSkills(employeeId, employeeName);

              // Also refresh skill gaps
              detectSkillGaps(employeeId, employeeName);
            });
          } else {
            throw new Error(data.message || 'Failed to initialize basic skills');
          }
        }).catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Initialization Failed',
            text: error.message || 'Failed to initialize basic skills. Please try again.',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545'
          }).then(() => {
            // Retry the initialization process
            initializeBasicSkills(employeeId, employeeName);
          });
        });
      }

      // Handle View All Competencies button toggle
      document.addEventListener('click', function(e) {
        if (e.target.closest('.view-all-competencies-btn')) {
          const btn = e.target.closest('.view-all-competencies-btn');
          const targetId = btn.getAttribute('data-bs-target');
          const collapseElement = document.querySelector(targetId);

          // Update button text and icon when collapsed/expanded
          collapseElement.addEventListener('shown.bs.collapse', function() {
            const competencyCount = btn.textContent.match(/\d+/)[0];
            const competencyText = competencyCount > 1 ? 'ies' : 'y';
            btn.innerHTML = `<i class="bi bi-eye-slash me-1"></i>Hide ${competencyCount} Competenc${competencyText} <i class="bi bi-chevron-up ms-1 toggle-icon"></i>`;
            btn.setAttribute('aria-expanded', 'true');
          });

          collapseElement.addEventListener('hidden.bs.collapse', function() {
            const competencyCount = btn.textContent.match(/\d+/)[0];
            const competencyText = competencyCount > 1 ? 'ies' : 'y';
            btn.innerHTML = `<i class="bi bi-eye me-1"></i>View All ${competencyCount} Competenc${competencyText} <i class="bi bi-chevron-down ms-1 toggle-icon"></i>`;
            btn.setAttribute('aria-expanded', 'false');
          });
        }
      });

    });

    // ========== COMPETENCIES CARD PAGINATION ==========
    const ITEMS_PER_PAGE = 5;

    function initCardPagination() {
      document.querySelectorAll('.competencies-list').forEach(list => {
        const empId = list.dataset.employeeId;
        const items = list.querySelectorAll('.competency-profile-item');
        const totalItems = items.length;
        const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
        const paginationContainer = document.getElementById(`pagination-${empId}`);

        if (totalItems > ITEMS_PER_PAGE) {
            paginationContainer.style.display = 'flex';
            paginationContainer.querySelector('.total-pages').textContent = totalPages;
            showPage(empId, 1);
        } else {
            paginationContainer.style.display = 'none';
        }
      });
    }

    function showPage(empId, page) {
      const list = document.querySelector(`.competencies-list[data-employee-id="${empId}"]`);
      if (!list) return;

      const items = list.querySelectorAll('.competency-profile-item');
      items.forEach((item, index) => {
        const start = (page - 1) * ITEMS_PER_PAGE;
        const end = start + ITEMS_PER_PAGE;
        if (index >= start && index < end) {
            item.style.display = 'block';
            // Fix border top issue: first item of the page shouldn't have top border/padding visually if it's not the very first item
            // But we can leave styling as is for now, or remove border-top-class if it's the first visible item
        } else {
            item.style.display = 'none';
        }
      });

      list.dataset.currentPage = page;
      updatePaginationControls(empId, page);
    }

    function updatePaginationControls(empId, page) {
      const paginationContainer = document.getElementById(`pagination-${empId}`);
      if (!paginationContainer) return;

      const items = document.querySelectorAll(`.competencies-list[data-employee-id="${empId}"] .competency-profile-item`);
      const totalPages = Math.ceil(items.length / ITEMS_PER_PAGE);

      paginationContainer.querySelector('.current-page').textContent = page;
      const prevBtn = paginationContainer.querySelector('.prev-page-btn');
      const nextBtn = paginationContainer.querySelector('.next-page-btn');

      prevBtn.disabled = page <= 1;
      nextBtn.disabled = page >= totalPages;
    }

    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.prev-page-btn, .next-page-btn');
      if (!btn) return;

      e.preventDefault();
      const empId = btn.dataset.employeeId;
      const list = document.querySelector(`.competencies-list[data-employee-id="${empId}"]`);
      let currentPage = parseInt(list.dataset.currentPage) || 1;

      if (btn.classList.contains('prev-page-btn')) {
        currentPage--;
      } else {
        currentPage++;
      }

      showPage(empId, currentPage);
    });

    // Initialize pagination on load
    initCardPagination();
  </script>
</body>
</html>
