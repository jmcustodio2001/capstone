<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            <h2 class="fw-bold mb-1">Competency Library</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Here's your Competency Library.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Competency Library</li>
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
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($competencies as $index => $comp)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $comp->competency_name }}</td>
                  <td>{{ $comp->description }}</td>
                  <td>
                    <!-- Debug: {{ $comp->category }} -->
                    @php
                      $categoryColors = [
                        'Technical' => 'bg-primary',
                        'Leadership' => 'bg-success',
                        'Communication' => 'bg-info',
                        'Behavioral' => 'bg-warning',
                        'Management' => 'bg-danger',
                        'Analytical' => 'bg-purple',
                        'Creative' => 'bg-pink',
                        'Strategic' => 'bg-dark',
                        'Destination Knowledge' => 'bg-orange'
                      ];
                      $colorClass = $categoryColors[$comp->category] ?? 'bg-secondary';

                      // Special styling for Destination Knowledge
                      if ($comp->category === 'Destination Knowledge') {
                        $badgeClass = 'badge text-white fw-bold';
                        $badgeStyle = 'background-color: #fd7e14 !important;'; // Orange background
                        $iconClass = 'bi bi-geo-alt-fill me-1';
                      } else {
                        $badgeClass = "badge {$colorClass} bg-opacity-10 text-" . str_replace('bg-', '', $colorClass);
                        $badgeStyle = '';
                        $iconClass = '';
                      }
                    @endphp
                    <span class="{{ $badgeClass }}" @if($comp->category === 'Destination Knowledge') style="{{ $badgeStyle }}" @endif>
                      @if($comp->category === 'Destination Knowledge')
                        <i class="{{ $iconClass }}"></i>
                      @endif
                      {{ $comp->category ?? 'No Category' }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="progress me-2" style="width: 80px; height: 20px;">
                        <div class="progress-bar bg-warning" style="width: {{ (($comp->rate ?? 0)/5)*100 }}%"></div>
                      </div>
                      <span class="fw-semibold">{{ round((($comp->rate ?? 0)/5)*100) }}%</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-outline-primary btn-sm me-1 edit-competency-btn"
                            data-id="{{ $comp->id }}"
                            data-name="{{ $comp->competency_name }}"
                            data-description="{{ $comp->description }}"
                            data-category="{{ $comp->category }}"
                            data-rate="{{ $comp->rate }}">
                      <i class="bi bi-pencil"></i> Edit
                    </button>
                    <form action="{{ route('admin.competency_library.destroy', $comp->id) }}" method="POST" style="display:inline-block;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirmDeleteCompetency()">
                        <i class="bi bi-trash"></i> Delete
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">No competencies found.</td>
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
          <button class="btn btn-warning btn-sm" id="syncTrainingBtn" title="Sync Training Progress with Competency Levels">
            <i class="bi bi-arrow-repeat"></i> Sync Training Progress
          </button>
          <input type="text" id="gap-search" class="form-control form-control-sm" placeholder="Search employee..." style="width: 180px;">
          <button class="btn btn-primary btn-sm" id="addGapBtn">
            <i class="bi bi-plus-lg"></i> Add Gap Record
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Competency</th>
                <th class="fw-bold">Rate</th>
                <th class="fw-bold">Required Level</th>
                <th class="fw-bold">Current Level</th>
                <th class="fw-bold">Gap</th>
                <th class="fw-bold">Expiration</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tbody id="gap-table-body">
                @forelse($gaps as $index => $gap)
                  <tr class="gap-row">
                    <td>{{ $index + 1 }}</td>
                    <td class="gap-employee">
                      @if($gap->employee)
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
                          <span class="fw-semibold">
                            {{ $firstName }} {{ $lastName }}
                          </span>
                        </div>
                      @else
                        N/A
                      @endif
                    </td>
                    <td>
                      @if($gap->competency)
                        {{ $gap->competency->competency_name }}
                      @else
                        N/A
                      @endif
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 80px; height: 20px;">
                          <div class="progress-bar bg-warning" style="width: {{ (($gap->competency->rate ?? 0)/5)*100 }}%"></div>
                        </div>
                        <span class="fw-semibold">{{ round((($gap->competency->rate ?? 0)/5)*100) }}%</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 80px; height: 20px;">
                          @php
                            $requiredLevel = min(5, max(0, $gap->required_level ?? 0));
                            $requiredPercentage = ($requiredLevel / 5) * 100;
                          @endphp
                          <div class="progress-bar bg-info" style="width: {{ $requiredPercentage }}%"></div>
                        </div>
                        <span class="fw-semibold">{{ round($requiredPercentage) }}%</span>
                      </div>
                    </td>
                    <td>
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
                      <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 80px; height: 20px;">
                          <div class="progress-bar bg-success" style="width: {{ $currentPercentage }}%"></div>
                        </div>
                        <span class="fw-semibold">{{ round($currentPercentage) }}%</span>
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
                      </div>
                    </td>
                    <td>
                      @php
                        // Calculate gap using the calculated current level from above
                        $requiredLevel = min(5, max(0, $gap->required_level ?? 0));
                        // Use the calculated $currentLevel from the progress calculation above
                        $gapValue = max(0, $requiredLevel - $currentLevel);
                        $badgeClass = $gapValue > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success';
                        $gapText = $gapValue > 0 ? $gapValue . ' level(s) below' : 'No gap';
                      @endphp
                      <span class="badge {{ $badgeClass }}">{{ $gapText }}</span>
                    </td>
                    <td>
                      @if($gap->expired_date && !empty(trim($gap->expired_date)) && trim($gap->expired_date) !== '0000-00-00 00:00:00' && trim($gap->expired_date) !== '0000-00-00')
                        @php
                          try {
                            $expiredDateRaw = trim($gap->expired_date);
                            $expiredDateObj = \Carbon\Carbon::parse($expiredDateRaw);
                            $now = \Carbon\Carbon::now();
                            $dateFormatted = $expiredDateObj->format('M d, Y');
                            $timeFormatted = $expiredDateObj->format('h:i A');
                            $daysLeft = $now->diffInDays($expiredDateObj, false);
                            $isExpired = $now->gt($expiredDateObj);
                            $showExpiredDate = true;
                          } catch (Exception $e) {
                            $showExpiredDate = false;
                          }
                        @endphp
                        @if($showExpiredDate)
                          <div class="d-flex flex-column align-items-start">
                            <div><strong>{{ $dateFormatted }}</strong></div>
                            <div class="text-muted small">{{ $timeFormatted }}</div>
                            <div class="w-100 mt-1">
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
                    </td>
                    <td class="text-center">
                      <!-- Always show edit button, no date parsing needed -->
                        <button class="btn btn-outline-primary btn-sm me-1 edit-gap-btn"
                                data-id="{{ $gap->id }}"
                                data-employee-id="{{ $gap->employee_id }}"
                                data-competency-id="{{ $gap->competency_id }}"
                                data-rate="{{ $gap->competency->rate ?? '' }}"
                                data-required-level="{{ $gap->required_level }}"
                                data-current-level="{{ $gap->current_level }}"
                                data-gap="{{ $gap->gap }}"
                                data-expired-date="">
                          <i class="bi bi-pencil"></i> Edit
                        </button>

                      @if($gap->expired_date)
                        <button class="btn btn-outline-warning btn-sm me-1 extend-expiration-btn"
                                data-id="{{ $gap->id }}"
                                data-employee="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                                data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                                data-expired="false">
                          <i class="bi bi-clock-history"></i>
                          Extend
                        </button>
                      @endif
                      <button class="btn btn-outline-success btn-sm me-1 assign-training-btn"
                              data-id="{{ $gap->id }}"
                              data-employee-id="{{ $gap->employee_id }}"
                              data-employee-name="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                              data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}"
                              data-expired-date="{{ $gap->expired_date }}">
                        <i class="bi bi-calendar-plus"></i> Assign to Training
                      </button>
                      <button class="btn btn-outline-danger btn-sm delete-gap-btn"
                              data-id="{{ $gap->id }}"
                              data-employee="{{ $gap->employee ? $gap->employee->first_name . ' ' . $gap->employee->last_name : 'N/A' }}"
                              data-competency="{{ $gap->competency ? $gap->competency->competency_name : 'N/A' }}">
                        <i class="bi bi-trash"></i> Delete
                      </button>
</script>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted">No gap records found.</td>
                  </tr>
                @endforelse
              </tbody>
            </tbody>
          </table>
        </div>
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

  <!-- Edit Gap Modal (Single Modal) -->
  <div class="modal fade" id="editGapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="editGapForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Gap Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="edit-gap-employee-id" class="form-label">Employee*</label>
              <select id="edit-gap-employee-id" name="employee_id" class="form-select" required>
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
            <div class="mb-3">
              <label for="edit-gap-competency-id" class="form-label">Competency*</label>
              <select id="edit-gap-competency-id" name="competency_id" class="form-select" required>
                <option value="">Select Competency</option>
                @foreach($competencies as $comp)
                  <option value="{{ $comp->id }}" data-rate="{{ $comp->rate }}">
                    {{ $comp->competency_name }} (Rate: {{ $comp->rate }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="edit-gap-rate" class="form-label">Rate</label>
                <input id="edit-gap-rate" type="number" class="form-control readonly-field" readonly>
              </div>
              <div class="col-md-4 mb-3">
                <label for="edit-gap-required-level" class="form-label">Required Level*</label>
                <input id="edit-gap-required-level" type="number" name="required_level"
                       class="form-control required-level" min="1" max="5" required>
              </div>
              <div class="col-md-4 mb-3">
                <label for="edit-gap-current-level" class="form-label">Current Level*</label>
                <input id="edit-gap-current-level" type="number" name="current_level"
                       class="form-control current-level" min="1" max="5" required>
              </div>
              <div class="col-md-4 offset-md-8 mb-3">
                <label for="edit-gap-value" class="form-label">Gap</label>
                <input id="edit-gap-value" type="number" name="gap"
                       class="form-control gap-field readonly-field" readonly>
              </div>
            </div>
            <div class="row">
              <div class="col-12 mb-3">
                <label for="edit-expired-date" class="form-label">Expiration Date (Optional)</label>
                <input id="edit-expired-date" type="datetime-local" name="expired_date" class="form-control">
                <small class="form-text text-muted">Leave empty for no expiration</small>
              </div>
            </div>

            <!-- Security Section for Edit -->
            <div class="row g-3 mt-3">
              <div class="col-12">
                <div class="alert alert-warning border-start border-warning border-4">
                  <i class="bi bi-shield-lock-fill me-2 text-warning"></i>
                  <strong>Sweet Security Verification:</strong> Please verify your password to edit this gap record.
                  <div class="mt-2">
                    <small class="text-muted">
                      <i class="bi bi-info-circle me-1"></i>
                      This additional security step ensures only authorized administrators can edit competency gap records.
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <label for="edit-admin-password" class="form-label fw-semibold">
                  <i class="bi bi-key me-1"></i>Verify Your Password*
                </label>
                <input id="edit-admin-password" type="password" name="admin_password" class="form-control" required
                       placeholder="Enter your admin password">
                <small class="form-text text-muted">Required for security verification</small>
              </div>
              <div class="col-md-6">
                <label for="edit-confirm-admin-password" class="form-label fw-semibold">
                  <i class="bi bi-key-fill me-1"></i>Confirm Password*
                </label>
                <input id="edit-confirm-admin-password" type="password" name="confirm_admin_password" class="form-control" required
                       placeholder="Confirm your password">
                <small class="form-text text-muted">Re-enter password for confirmation</small>
                <div id="edit-password-match-indicator" class="mt-1" style="display: none;">
                  <small id="edit-password-match-text"></small>
                </div>
              </div>
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

  <!-- Password Verification Modal -->
  <div class="modal fade" id="passwordVerificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">
            <i class="bi bi-shield-lock-fill me-2"></i>Verify Your Password
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning border-start border-warning border-4">
            <i class="bi bi-shield-lock-fill me-2 text-warning"></i>
            <strong>Sweet Security Verification:</strong> Please verify your password to proceed with this action.
            <div class="mt-2">
              <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                This additional security step ensures only authorized administrators can perform sensitive operations.
              </small>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label for="verify-admin-password" class="form-label fw-semibold">
                <i class="bi bi-key me-1"></i>Enter Your Admin Password*
              </label>
              <input id="verify-admin-password" type="password" class="form-control" required
                     placeholder="Enter your admin password">
              <small class="form-text text-muted">Required for security verification</small>
            </div>
            <div class="col-12">
              <label for="verify-confirm-admin-password" class="form-label fw-semibold">
                <i class="bi bi-key-fill me-1"></i>Confirm Password*
              </label>
              <input id="verify-confirm-admin-password" type="password" class="form-control" required
                     placeholder="Confirm your password">
              <small class="form-text text-muted">Re-enter password for confirmation</small>
              <div id="verify-password-match-indicator" class="mt-1" style="display: none;">
                <small id="verify-password-match-text"></small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="verify-password-btn">
            <i class="bi bi-shield-check me-1"></i>Verify & Proceed
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
  <script src="{{ asset('assets/js/admin_dashboard-script.js') }}"></script>
  <script src="{{ asset('js/csrf-refresh.js') }}"></script>
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

// Auto-refresh CSRF token every 10 minutes to prevent 419 errors
setInterval(function() {
  fetch('/csrf-token')
    .then(response => response.json())
    .then(data => {
      const newToken = data.token || data.csrf_token;
      if (newToken) {
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
        console.log('CSRF token auto-refreshed');
      }
    })
    .catch(error => console.error('CSRF token refresh failed:', error));
}, 10 * 60 * 1000); // 10 minutes

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
  const editGapModal = new bootstrap.Modal(document.getElementById('editGapModal'));
  const addGapModal = new bootstrap.Modal(document.getElementById('addGapModal'));
  const addCompetencyModal = new bootstrap.Modal(document.getElementById('addCompetencyModal'));
  const passwordVerificationModal = new bootstrap.Modal(document.getElementById('passwordVerificationModal'));

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
            refreshGapTable();
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

      // ========== GAP EDIT FUNCTIONALITY ==========
      document.querySelectorAll('.edit-gap-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const employeeId = this.getAttribute('data-employee-id');
          const competencyId = this.getAttribute('data-competency-id');
          const rate = this.getAttribute('data-rate');
          const requiredLevel = this.getAttribute('data-required-level');
          const currentLevel = this.getAttribute('data-current-level');
          const gap = this.getAttribute('data-gap');
          const expiredDate = this.getAttribute('data-expired-date');

          // Store the action data
          const actionData = {
            id, employeeId, competencyId, rate, requiredLevel, currentLevel, gap, expiredDate
          };

          // Set pending action to show edit modal with data
          pendingAction = function(data) {
            // Set form action URL
            document.getElementById('editGapForm').action = `/admin/competency-gap-analysis/${data.id}`;

            // Populate form fields
            document.getElementById('edit-gap-employee-id').value = data.employeeId;
            document.getElementById('edit-gap-competency-id').value = data.competencyId;
            document.getElementById('edit-gap-rate').value = data.rate;
            document.getElementById('edit-gap-required-level').value = data.requiredLevel;
            document.getElementById('edit-gap-current-level').value = data.currentLevel;
            document.getElementById('edit-gap-value').value = data.gap;
            document.getElementById('edit-expired-date').value = data.expiredDate || '';

            // Show modal
            editGapModal.show();
          };

          pendingActionData = actionData;
          passwordVerificationModal.show();
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



      // Sync Training Progress Button Handler
      const syncTrainingBtn = document.getElementById('syncTrainingBtn');
      if (syncTrainingBtn) {
        syncTrainingBtn.addEventListener('click', function() {
        const button = this;
        const originalHtml = button.innerHTML;

        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Syncing...';

        fetch('{{ route("admin.course_management.sync_training_competency") }}', {
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
            showSuccessToast(`✅ ${data.message} Updated ${data.updated_records} training records.`, 5000);

            // Show detailed sync results if available
            if (data.sync_details && data.sync_details.length > 0) {
              setTimeout(() => {
                let detailsMessage = '📊 Sync Details:\n';
                data.sync_details.slice(0, 5).forEach(detail => {
                  if (detail.sync_type === 'training_to_competency') {
                    detailsMessage += `• ${detail.competency}: Gap closed (${detail.old_current_level} → ${detail.new_current_level})\n`;
                  } else {
                    detailsMessage += `• ${detail.course_title}: ${detail.old_progress}% → ${detail.new_progress}%\n`;
                  }
                });
                if (data.sync_details.length > 5) {
                  detailsMessage += `... and ${data.sync_details.length - 5} more records updated.`;
                }
                showInfoToast(detailsMessage, 6000);
              }, 2000);
            }

            // Refresh the page to show updated competency gap levels
            setTimeout(() => {
              showInfoToast('🔄 Refreshing page to show updated competency levels...', 2000);
              setTimeout(() => {
                window.location.reload();
              }, 2500);
            }, 3000);
          } else {
            showErrorToast('❌ ' + (data.message || 'Failed to sync training records'), 4000);
          }

          // Reset button
          button.disabled = false;
          button.innerHTML = originalHtml;
        })
        .catch(error => {
          console.error('Sync error:', error);
          showErrorToast('❌ Network error occurred during sync', 4000);

          // Reset button
          button.disabled = false;
          button.innerHTML = originalHtml;
        });
        });
      }

      // Handle Extend Expiration buttons
      let currentGapId = null;
      let pendingAction = null; // Store the action to perform after password verification
      let pendingActionData = null; // Store data needed for the action
      const extendExpirationModal = new bootstrap.Modal(document.getElementById('extendExpirationModal'));
      const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

      // Password verification button handler
      const verifyPasswordBtn = document.getElementById('verify-password-btn');
      if (verifyPasswordBtn) {
        verifyPasswordBtn.addEventListener('click', function() {
        if (!validateVerificationPasswords()) {
          return;
        }

        // Close verification modal
        passwordVerificationModal.hide();

        // Execute the pending action
        if (pendingAction) {
          pendingAction(pendingActionData);
          pendingAction = null;
          pendingActionData = null;
        }

        // Clear verification form
        document.getElementById('verify-admin-password').value = '';
        document.getElementById('verify-confirm-admin-password').value = '';
        document.getElementById('verify-password-match-indicator').style.display = 'none';
        });
      }

      // Add Gap button handler - shows password verification first
      const addGapBtn = document.getElementById('addGapBtn');
      if (addGapBtn) {
        addGapBtn.addEventListener('click', function() {
        pendingAction = function() {
          addGapModal.show();
        };
        passwordVerificationModal.show();
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

          // Store the action data
          const actionData = {
            gapId, employeeName, competencyName
          };

          // Set pending action to show delete modal with data
          pendingAction = function(data) {
            currentGapId = data.gapId;
            document.getElementById('delete-employee-name').textContent = data.employeeName;
            document.getElementById('delete-competency-name').textContent = data.competencyName;

            // Clear password fields
            document.getElementById('delete-admin-password').value = '';
            document.getElementById('delete-confirm-admin-password').value = '';
            document.getElementById('delete-password-match-indicator').style.display = 'none';

            deleteConfirmationModal.show();
          };

          pendingActionData = actionData;
          passwordVerificationModal.show();
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
        // Validate passwords first
        if (!validateDeletePasswords()) {
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

      // AJAX for Edit Gap Form
      const editGapForm = document.getElementById('editGapForm');
      if (editGapForm) {
        editGapForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Validate passwords first
          if (!validateEditPasswords()) {
            return;
          }

          // Show loading state
          const submitBtn = editGapForm.querySelector('button[type="submit"]');
          const originalBtnText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

          const formData = new FormData(editGapForm);

          fetch(editGapForm.action, {
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
              editGapModal.hide();
              showSuccessToast('✅ Gap record updated successfully!', 3000);
              // Reload page to show updated record
              window.location.reload();
            } else if (data && data.errors) {
              // Show validation errors
              const errorMessages = Object.values(data.errors).flat();
              showErrorToast('❌ Validation errors:\n' + errorMessages.join('\n'), 5000);
            } else if (data && data.message) {
              showErrorToast('❌ ' + data.message, 4000);
            } else {
              showErrorToast('❌ Error updating gap record. Please check all required fields.', 4000);
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

        // Refresh CSRF token before making request
        fetch('/csrf-token')
          .then(response => response.json())
          .then(tokenData => {
            const newToken = tokenData.token || tokenData.csrf_token;
            if (newToken) {
              // Update meta tag
              document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
            }

            // Make the actual request with fresh token
            return fetch('{{ route("competency_gap_analysis.fix_expired_dates") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': newToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            });
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

      // Assign to Training button handler
      document.addEventListener('click', function(e) {
        if (e.target.closest('.assign-training-btn')) {
          const btn = e.target.closest('.assign-training-btn');
          const gapId = btn.getAttribute('data-id');
          const employeeId = btn.getAttribute('data-employee-id');
          const employeeName = btn.getAttribute('data-employee-name');
          const competency = btn.getAttribute('data-competency');
          const expiredDate = btn.getAttribute('data-expired-date');
          
          // Show confirmation dialog
          if (confirm(`Assign training for "${competency}" to ${employeeName}?`)) {
            // Show loading state
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Assigning...';
            
            // Make API call to assign training
            fetch('{{ route("competency_gap_analysis.assign_to_training") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                gap_id: gapId,
                employee_id: employeeId,
                competency: competency,
                expired_date: expiredDate
              })
            })
            .then(response => response.json())
            .then(data => {
              // Reset button state
              btn.disabled = false;
              btn.innerHTML = originalHtml;
              
              if (data.success) {
                showToast(`✅ ${data.message}`, 'success');
                // Update button to show assigned state
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Assigned';
                btn.disabled = true;
              } else {
                showToast(`❌ ${data.message || 'Failed to assign training'}`, 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              // Reset button state
              btn.disabled = false;
              btn.innerHTML = originalHtml;
              showToast('❌ Network error occurred while assigning training', 'error');
            });
          }
        }
      });
    });
  </script>
</body>
</html>
