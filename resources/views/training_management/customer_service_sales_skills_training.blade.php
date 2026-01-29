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

    /* Custom table header styling */
    .custom-table-header {
      background: linear-gradient(135deg, #a8d0f0 0%, #d6e8f5 100%) !important;
      color: #2c5282 !important;
    }

    .custom-table-header th {
      background: linear-gradient(135deg, #a8d0f0 0%, #d6e8f5 100%) !important;
      color: #2c5282 !important;
      font-weight: 600 !important;
      font-size: 0.875rem !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
      padding: 1rem 0.75rem !important;
      border: none !important;
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
            <h2 class="fw-bold mb-1">Customer Service & Sales Skills Training</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage training records for customer service & sales skills.
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
          <div class="pagination-controls">
            <button class="btn btn-outline-secondary btn-sm" onclick="previousPage('gaps')" id="gaps-prev-btn">
              <i class="bi bi-chevron-left"></i> Previous
            </button>
            <span class="mx-2 text-muted" id="gaps-page-info">Page 1</span>
            <button class="btn btn-outline-secondary btn-sm" onclick="nextPage('gaps')" id="gaps-next-btn">
              Next <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="gaps-container">
        @forelse($gaps as $gap)
          @php
            // Add null check for employee relationship
            $employee = $gap->employee ?? null;
            $firstName = $employee->first_name ?? 'Unknown';
            $lastName = $employee->last_name ?? 'Employee';
            $fullName = $firstName . ' ' . $lastName;

            // Check if profile picture exists - robust approach
            $profilePicUrl = null;
            if ($employee && !empty($employee->profile_picture)) {
                $profilePic = $employee->profile_picture;
                if (strpos($profilePic, 'http') === 0) {
                    $profilePicUrl = $profilePic;
                } elseif (strpos($profilePic, 'storage/') === 0) {
                    $profilePicUrl = asset($profilePic);
                } else {
                    $profilePicUrl = asset('storage/' . ltrim($profilePic, '/'));
                }
            }

            // Generate consistent color based on employee name for fallback
            $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
            $employeeId = $employee->employee_id ?? 'default';
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

            // Dynamic row color based on gap severity
            $rowClass = '';
            if ($displayGap >= 4) {
                $rowClass = 'table-danger';
            } elseif ($displayGap >= 3) {
                $rowClass = 'table-warning';
            } elseif ($displayGap >= 2) {
                $rowClass = 'table-info';
            }
          @endphp

          @if($loop->first)
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="custom-table-header">
                <tr class="custom-table-header">
                  <th scope="col" style="width: 15%;">Employee</th>
                  <th scope="col" style="width: 20%;">Competency Required</th>
                  <th scope="col" style="width: 20%;">Recommended Training</th>
                  <th scope="col" style="width: 10%;">Required Level</th>
                  <th scope="col" style="width: 10%;">Current Level</th>
                  <th scope="col" style="width: 10%;">Gap</th>
                  <th scope="col" style="width: 15%;">Progress</th>
                </tr>
              </thead>
              <tbody>
          @endif

                <tr class="{{ $rowClass }}">
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="{{ $profilePicUrl }}"
                           alt="{{ $firstName }} {{ $lastName }}"
                           class="rounded-circle me-2"
                           style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($firstName . ' ' . $lastName) }}&size=200&background={{ $bgColor }}&color=ffffff&bold=true&rounded=true'">
                      <div>
                        <div class="fw-semibold">{{ $firstName }} {{ $lastName }}</div>
                        <small class="text-muted">ID: {{ $employee->employee_id ?? $gap->employee_id ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="bi bi-award text-primary me-2"></i>
                      <div>
                        <div class="fw-semibold">{{ $gap->competency->competency_name ?? 'Unknown Competency' }}</div>
                        <small class="text-muted">Core skill needed</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="bi bi-book text-success me-2"></i>
                      <div>
                        @if($gap->recommended_training && isset($gap->recommended_training->course_title))
                          <div class="fw-semibold">{{ $gap->recommended_training->course_title }}</div>
                          <small class="text-muted">Suggested course</small>
                        @else
                          <span class="text-muted">No training assigned</span>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-info fs-6 px-3 py-2">{{ $displayRequiredLevel }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-warning fs-6 px-3 py-2">{{ $displayCurrentLevel }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-danger fs-6 px-3 py-2">{{ $displayGap }}</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="progress flex-grow-1 me-2" style="height: 8px;">
                        @php
                          // Calculate progress as current level percentage (20%, 40%, 60%, 80%, 100%)
                          $currentLevelPercentage = ($displayCurrentLevel / 5) * 100;
                          // Calculate progress bar width relative to required level
                          $progressBarWidth = min(($displayCurrentLevel / $displayRequiredLevel) * 100, 100);
                        @endphp
                        <div class="progress-bar progress-bar-striped"
                             role="progressbar"
                             style="width: {{ $progressBarWidth }}%; background: linear-gradient(45deg, #007bff, #0056b3);"
                             aria-valuenow="{{ $currentLevelPercentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                      </div>
                      <small class="text-muted">{{ round($currentLevelPercentage) }}%</small>
                    </div>
                  </td>
                </tr>

          @if($loop->last)
              </tbody>
            </table>
          </div>
          @endif

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


    <!-- Skills Overview Table -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Overall Rating - Customer Service & Sales Skills</h4>
        <div class="d-flex gap-2">
          <input type="text" id="skillsSearch" class="form-control form-control-sm" placeholder="Search skills..." style="max-width: 200px;">
          <select id="skillsFilter" class="form-select form-select-sm" style="max-width: 150px;">
            <option value="">All Levels</option>
            <option value="expert">Expert (4.5+)</option>
            <option value="advanced">Advanced (3.5+)</option>
            <option value="intermediate">Intermediate (2.5+)</option>
            <option value="needs-improvement">Needs Improvement</option>
          </select>
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
      </div>
      <div class="card-body">
        @if($skills->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width: 40%;">Skill Name</th>
                  <th style="width: 20%;">Rating</th>
                  <th style="width: 20%;">Progress</th>
                  <th style="width: 20%;">Level</th>
                </tr>
              </thead>
              <tbody id="skillsTableBody">
                @php $seenSkills = []; @endphp
                @foreach($skills as $skill)
                  @php
                    $descLower = Str::lower($skill->description ?? '');
                    $isAutoCreated = Str::contains($descLower, 'auto-created')
                                  || Str::contains($descLower, 'auto created')
                                  || Str::contains($descLower, 'api skills')
                                  || Str::contains($descLower, 'employee api skills')
                                  || Str::contains($descLower, 'auto-created from')
                                  || Str::contains($descLower, '(training:)');
                    $normalizedSkill = trim(strtolower($skill->competency_name ?? ''));
                  @endphp

                  @if($isAutoCreated)
                    @continue
                  @endif

                  @if($normalizedSkill !== '' && in_array($normalizedSkill, $seenSkills))
                    @continue
                  @endif
                  @php if($normalizedSkill !== '') $seenSkills[] = $normalizedSkill; @endphp

                  @php
                    // Get the skill level from rate or default to average for the category
                    $skillLevel = $skill->rate;
                    if (!$skillLevel) {
                        // Calculate category average if rate is not set
                        $categoryAvg = App\Models\CompetencyLibrary::where('category', $skill->category)
                            ->whereNotNull('rate')
                            ->avg('rate') ?? 3; // Default to 3 if no category average
                        $skillLevel = $categoryAvg;
                    }

                    // Calculate training progress based on skill level and any additional factors
                    $baseProgress = $skillLevel * 20; // Convert 0-5 scale to percentage

                    // Add bonuses for specific categories
                    $categoryBonus = 0;
                    if (in_array($skill->category, ['Sales', 'Customer Service'])) {
                        $categoryBonus = 10; // Add 10% bonus for core skills
                    } elseif (in_array($skill->category, ['Communication', 'Leadership'])) {
                        $categoryBonus = 5; // Add 5% bonus for important soft skills
                    }

                    $trainingProgress = min(100, $baseProgress + $categoryBonus); // Cap at 100%

                    // Calculate overall skill level and percentage
                    $skillLevel = max($skillLevel, ($trainingProgress / 100 * 5));
                    $skillPercentage = min(($skillLevel / 5) * 100, 100);

                    // Generate star rating
                    $fullStars = floor($skillLevel);
                    $hasHalfStar = ($skillLevel - $fullStars) >= 0.5;
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

                    // Determine level class and text
                    if ($skillLevel >= 4.5) {
                        $levelClass = 'bg-success';
                        $levelText = 'Expert';
                        $progressClass = 'bg-success';
                    } elseif ($skillLevel >= 3.5) {
                        $levelClass = 'bg-info';
                        $levelText = 'Advanced';
                        $progressClass = 'bg-info';
                    } elseif ($skillLevel >= 2.5) {
                        $levelClass = 'bg-warning';
                        $levelText = 'Intermediate';
                        $progressClass = 'bg-warning';
                    } else {
                        $levelClass = 'bg-danger';
                        $levelText = 'Needs Improvement';
                        $progressClass = 'bg-danger';
                    }
                  @endphp
                  @php
                    // Get the skill level from rate or default to average for the category
                    $skillLevel = $skill->rate;
                    if (!$skillLevel) {
                        // Calculate category average if rate is not set
                        $categoryAvg = App\Models\CompetencyLibrary::where('category', $skill->category)
                            ->whereNotNull('rate')
                            ->avg('rate') ?? 3; // Default to 3 if no category average
                        $skillLevel = $categoryAvg;
                    }

                    // Calculate training progress based on skill level and any additional factors
                    $baseProgress = $skillLevel * 20; // Convert 0-5 scale to percentage

                    // Add bonuses for specific categories
                    $categoryBonus = 0;
                    if (in_array($skill->category, ['Sales', 'Customer Service'])) {
                        $categoryBonus = 10; // Add 10% bonus for core skills
                    } elseif (in_array($skill->category, ['Communication', 'Leadership'])) {
                        $categoryBonus = 5; // Add 5% bonus for important soft skills
                    }

                    $trainingProgress = min(100, $baseProgress + $categoryBonus); // Cap at 100%

                    // Calculate overall skill level and percentage
                    $skillLevel = max($skillLevel, ($trainingProgress / 100 * 5));
                    $skillPercentage = min(($skillLevel / 5) * 100, 100);

                    // Generate star rating
                    $fullStars = floor($skillLevel);
                    $hasHalfStar = ($skillLevel - $fullStars) >= 0.5;
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

                    // Determine level class and text
                    if ($skillLevel >= 4.5) {
                        $levelClass = 'bg-success';
                        $levelText = 'Expert';
                        $progressClass = 'bg-success';
                    } elseif ($skillLevel >= 3.5) {
                        $levelClass = 'bg-info';
                        $levelText = 'Advanced';
                        $progressClass = 'bg-info';
                    } elseif ($skillLevel >= 2.5) {
                        $levelClass = 'bg-warning';
                        $levelText = 'Intermediate';
                        $progressClass = 'bg-warning';
                    } else {
                        $levelClass = 'bg-danger';
                        $levelText = 'Needs Improvement';
                        $progressClass = 'bg-danger';
                    }
                  @endphp
                  <tr class="skill-row" data-skill-level="{{ strtolower(str_replace(' ', '-', $levelText)) }}">
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="bi bi-award text-primary me-2"></i>
                        <div>
                          <strong>{{ $skill->competency_name }}</strong>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary">{{ $skill->category }}</span>
                          </div>
                          <small class="text-muted d-block">{{ $skill->description }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="star-rating me-2" style="color: #ffc107; font-size: 0.9rem;">
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
                        <span class="fw-bold text-primary">{{ number_format($skillLevel, 0) }}/5</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                          <div class="progress-container" style="height: 8px; background-color: #e9ecef; border-radius: 4px;">
                            <div class="{{ $progressClass }}"
                                 style="width: {{ $skillPercentage }}%; height: 100%; border-radius: 4px; transition: width 0.6s ease;">
                              <progress value="{{ $skillPercentage }}" max="100" class="visually-hidden">
                                {{ $skillPercentage }}%
                              </progress>
                            </div>
                          </div>
                          <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">Progress</small>
                            <small class="text-{{ str_replace('bg-', '', $progressClass) }} fw-bold">{{ round($skillPercentage) }}%</small>
                          </div>
                        </div>
                        <div class="text-center" style="min-width: 70px;">
                          <span class="badge bg-{{ str_replace('bg-', '', $progressClass) }} bg-opacity-10 text-{{ str_replace('bg-', '', $progressClass) }}">
                            {{ number_format($skillLevel, 0) }}/5
                          </span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge {{ $levelClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $levelClass) }}">{{ $levelText }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-book text-primary" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-primary">No Skills Defined</h4>
            <p class="text-muted">No skills are currently defined in the competency library.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Training Records -->
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Training Dashboard</h4>
        <div class="d-flex gap-2">
          <div class="pagination-controls">
            <button class="btn btn-outline-secondary btn-sm" onclick="previousPage('records')" id="records-prev-btn">
              <i class="bi bi-chevron-left"></i> Previous
            </button>
            <span class="mx-2 text-muted" id="records-page-info">Page 1</span>
            <button class="btn btn-outline-secondary btn-sm" onclick="nextPage('records')" id="records-next-btn">
              Next <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-4" id="records-container">
        @php
          // Group records by employee
          $groupedRecords = $records->groupBy('employee_id');
        @endphp

        @forelse($groupedRecords as $employeeId => $employeeRecords)
          @php
            // Get employee info from first record
            $firstRecord = $employeeRecords->first();
            $employee = $firstRecord->employee ?? null;
            $firstName = $employee->first_name ?? 'Unknown';
            $lastName = $employee->last_name ?? 'Employee';
            $fullName = $firstName . ' ' . $lastName;

            // Check if profile picture exists - robust approach
            $profilePicUrl = null;
            if ($employee && !empty($employee->profile_picture)) {
                $profilePic = $employee->profile_picture;
                if (strpos($profilePic, 'http') === 0) {
                    $profilePicUrl = $profilePic;
                } elseif (strpos($profilePic, 'storage/') === 0) {
                    $profilePicUrl = asset($profilePic);
                } else {
                    $profilePicUrl = asset('storage/' . ltrim($profilePic, '/'));
                }
            }

            // Generate consistent color based on employee name for fallback
            $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
            $colorIndex = abs(crc32($employeeId)) % count($colors);
            $bgColor = $colors[$colorIndex];

            // Fallback to UI Avatars if no profile picture found
            if (!$profilePicUrl) {
                $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) .
                               "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
            }

            // Calculate readiness score
            if ($employeeId) {
              $controller = new \App\Http\Controllers\SuccessionReadinessRatingController();
              $readiness = round($controller->calculateEmployeeReadinessScore($employeeId));
            } else {
              $readiness = 0;
            }

            // Calculate overall progress for this employee
            $totalProgress = 0;
            $completedTrainings = 0;
            $inProgressTrainings = 0;
            $expiredTrainings = 0;

            foreach ($employeeRecords as $record) {
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
                    $courseByTitle = \App\Models\CourseManagement::where('course_title', $courseTitle)->first();
                    if ($courseByTitle) {
                        $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseByTitle->course_id);
                    }
                }

                // Strategy 3: Check EmployeeCompetencyProfile table when no exam/training progress found
                if ($combinedProgress == 0) {
                    $trainingTitle = $record->training->course_title ?? ($record->course->course_title ?? '');
                    if ($trainingTitle) {
                        $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($trainingTitle) {
                            $q->where('competency_name', $trainingTitle)
                              ->orWhere('competency_name', 'LIKE', '%' . $trainingTitle . '%');
                        })->where('employee_id', $record->employee_id)->first();

                        if ($competencyProfile && $competencyProfile->proficiency_level) {
                            $combinedProgress = round(($competencyProfile->proficiency_level / 5) * 100);
                        }
                    }
                }

                // Get the required level and cap progress
                $requiredLevel = null;
                $trainingTitle = $record->training->course_title ?? ($record->course->course_title ?? '');
                if ($trainingTitle) {
                    $competencyGap = \App\Models\CompetencyGap::where('employee_id', $record->employee_id)
                        ->whereHas('competency', function($q) use ($trainingTitle) {
                            $q->where('competency_name', $trainingTitle)
                              ->orWhere('competency_name', 'LIKE', '%' . $trainingTitle . '%');
                        })->first();

                    if ($competencyGap) {
                        $requiredLevel = $competencyGap->required_level;
                    }
                }

                $rawProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);

                if ($requiredLevel && $requiredLevel > 0) {
                    $maxAllowedProgress = min(($requiredLevel / 5) * 100, 100);
                    $displayProgress = min($rawProgress, $maxAllowedProgress);
                } else {
                    $displayProgress = $rawProgress;
                }

                $displayProgress = max(0, min(100, (int)$displayProgress));

                // Store the calculated progress in the record for JavaScript access
                $record->calculated_progress = $displayProgress;

                // Check if expired (SAME LOGIC AS MAIN DASHBOARD)
                $finalExpiredDate = $record->expired_date ?? ($record->course->expired_date ?? null);
                $isExpired = false;
                if ($finalExpiredDate) {
                    $expiredDate = \Carbon\Carbon::parse($finalExpiredDate);
                    $isExpired = \Carbon\Carbon::now()->gt($expiredDate);
                }

                $totalProgress += $displayProgress;
                if ($isExpired && $displayProgress < 100) {
                    $expiredTrainings++;
                } elseif ($displayProgress >= 100) {
                    $completedTrainings++;
                } elseif ($displayProgress > 0) {
                    $inProgressTrainings++;
                }
            }

            $averageProgress = $employeeRecords->count() > 0 ? round($totalProgress / $employeeRecords->count()) : 0;
            $notStartedTrainings = $employeeRecords->count() - $completedTrainings - $inProgressTrainings - $expiredTrainings;
          @endphp

          <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100 shadow-sm border-0 training-record-card" style="transition: all 0.3s ease;">
              <!-- Employee Header -->
              <div class="card-header bg-light text-dark" style="border-radius: 12px 12px 0 0 !important;">
                <div class="d-flex align-items-center">
                  <img src="{{ $profilePicUrl }}"
                       alt="{{ $firstName }} {{ $lastName }}"
                       class="rounded-circle me-3"
                       style="width: 50px; height: 50px; object-fit: cover; border: 3px solid rgba(0,0,0,0.1);" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($firstName . ' ' . $lastName) }}&size=200&background={{ $bgColor }}&color=ffffff&bold=true&rounded=true'">
                  <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold text-dark">{{ $firstName }} {{ $lastName }}</h5>
                    <small class="text-muted">ID: {{ $employee->employee_id ?? $employeeId ?? 'N/A' }} • {{ $employeeRecords->count() }} Training(s)</small>
                  </div>
                  <div class="text-end">
                    <div class="fs-6 fw-bold text-primary">{{ $readiness }}{{ is_numeric($readiness) ? '%' : '' }}</div>
                    <small class="text-muted">Readiness</small>
                  </div>
                </div>
              </div>

              <!-- Card Body with Training Summary -->
              <div class="card-body p-4">
                <!-- Training Summary -->
                <div class="mb-3">
                  <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-collection text-primary fs-5 me-2"></i>
                    <h6 class="mb-0 fw-bold text-primary">Training Summary</h6>
                  </div>
                  <div class="row text-center">
                    <div class="col-4">
                      <div class="text-success fw-bold fs-5">{{ $completedTrainings }}</div>
                      <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-4">
                      <div class="text-primary fw-bold fs-5">{{ $inProgressTrainings }}</div>
                      <small class="text-muted">In Progress</small>
                    </div>
                    <div class="col-4">
                      <div class="text-secondary fw-bold fs-5">{{ $employeeRecords->count() - $completedTrainings - $inProgressTrainings }}</div>
                      <small class="text-muted">Not Started</small>
                    </div>
                  </div>
                </div>

                <!-- Overall Progress -->
                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Average Progress</span>
                    <span class="fw-bold text-primary">{{ $averageProgress }}%</span>
                  </div>
                  <div class="progress mb-2" style="height: 12px; border-radius: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         style="width: {{ $averageProgress }}%; background: linear-gradient(45deg, {{ $averageProgress >= 100 ? '#28a745, #20c997' : ($averageProgress >= 75 ? '#17a2b8, #20c997' : ($averageProgress >= 50 ? '#ffc107, #fd7e14' : '#dc3545, #e74c3c')) }});"
                         aria-valuenow="{{ $averageProgress }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                  </div>

                  <div class="mt-2">
                    @if($averageProgress >= 100)
                      <span class="badge bg-success bg-opacity-10 text-success fs-6 px-3 py-2">All Completed</span>
                    @elseif($averageProgress >= 75)
                      <span class="badge bg-info bg-opacity-10 text-info fs-6 px-3 py-2">Nearly Complete</span>
                    @elseif($averageProgress > 0)
                      <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2">In Progress</span>
                    @else
                      <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6 px-3 py-2">Not Started</span>
                    @endif
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                  <button class="btn btn-outline-primary btn-sm" title="View All Trainings" onclick="viewEmployeeTrainings('{{ $employeeId }}')">
                    <i class="bi bi-eye me-1"></i> View All Trainings ({{ $employeeRecords->count() }})
                  </button>
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
              <label for="employee_id" class="form-label">Employee*</label>
              <select class="form-control" id="employee_id" name="employee_id" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->employee_id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_id }})</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="training_id" class="form-label">Training*</label>
              <select class="form-control" id="training_id" name="training_id" required>
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
              <label for="date_completed" class="form-label">Date Completed*</label>
              <input type="date" class="form-control" id="date_completed" name="date_completed" required>
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

              // Get the required level for this employee-training combination from competency gaps
              $requiredLevel = null;
              $trainingTitle = $record->training->course_title ?? ($record->course->course_title ?? '');
              if ($trainingTitle) {
                  $competencyGap = \App\Models\CompetencyGap::where('employee_id', $record->employee_id)
                      ->whereHas('competency', function($q) use ($trainingTitle) {
                          $q->where('competency_name', $trainingTitle)
                            ->orWhere('competency_name', 'LIKE', '%' . $trainingTitle . '%');
                      })->first();

                  if ($competencyGap) {
                      $requiredLevel = $competencyGap->required_level;
                  }
              }

              $rawProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);

              // Cap progress at required level if available
              if ($requiredLevel && $requiredLevel > 0) {
                  // Convert required level (1-5 scale) to percentage (20%, 40%, 60%, 80%, 100%)
                  $maxAllowedProgress = min(($requiredLevel / 5) * 100, 100);
                  $displayProgress = min($rawProgress, $maxAllowedProgress);
              } else {
                  $displayProgress = $rawProgress;
              }
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
              <input type="date" class="form-control" name="date_completed" value="{{ $record->date_completed ? \Carbon\Carbon::parse($record->date_completed)->format('Y-m-d') : '' }}" required>
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

      // Strategy 4: Check EmployeeCompetencyProfile table when no exam/training progress found
      if ($combinedProgress == 0) {
          $trainingTitle = $record->training->course_title ?? ($record->course->course_title ?? '');
          if ($trainingTitle) {
              $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($trainingTitle) {
                  $q->where('competency_name', $trainingTitle)
                    ->orWhere('competency_name', 'LIKE', '%' . $trainingTitle . '%');
              })->where('employee_id', $record->employee_id)->first();

              if ($competencyProfile && $competencyProfile->proficiency_level) {
                  // Convert competency proficiency levels (1-5 scale) to percentages (0-100%)
                  $combinedProgress = round(($competencyProfile->proficiency_level / 5) * 100);
              }
          }
      }

      // Get the required level for this employee-training combination from competency gaps
      $requiredLevel = null;
      $trainingTitle = $record->training->course_title ?? ($record->course->course_title ?? '');
      if ($trainingTitle) {
          $competencyGap = \App\Models\CompetencyGap::where('employee_id', $record->employee_id)
              ->whereHas('competency', function($q) use ($trainingTitle) {
                  $q->where('competency_name', $trainingTitle)
                    ->orWhere('competency_name', 'LIKE', '%' . $trainingTitle . '%');
              })->first();

          if ($competencyGap) {
              $requiredLevel = $competencyGap->required_level;
          }
      }

      $rawProgress = $combinedProgress > 0 ? $combinedProgress : ($record->progress ?? 0);

      // Cap progress at required level if available
      if ($requiredLevel && $requiredLevel > 0) {
          // Convert required level (1-5 scale) to percentage (20%, 40%, 60%, 80%, 100%)
          $maxAllowedProgress = min(($requiredLevel / 5) * 100, 100);
          $displayProgress = min($rawProgress, $maxAllowedProgress);
      } else {
          $displayProgress = $rawProgress;
      }

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

  <!-- Initialize global objects to prevent undefined errors -->
  <script>
    // Prevent translation service errors
    if (typeof window.translationService === 'undefined') {
      window.translationService = {
        translate: function(key) { return key; },
        get: function(key) { return key; }
      };
    }

    // Add any other global objects that might be missing
    if (typeof window.app === 'undefined') {
      window.app = {};
    }

    // Prevent sidebar toggle errors
    if (typeof window.toggleSidebar === 'undefined') {
      window.toggleSidebar = function() {
        console.log('Sidebar toggle called (fallback)');
      };
    }

    // Safe Bootstrap initialization
    document.addEventListener('DOMContentLoaded', function() {
      try {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log('Tooltips initialized:', tooltipList.length);
      } catch (error) {
        console.warn('Tooltip initialization failed:', error);
      }

      try {
        // Initialize dropdowns
        const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdownElements.forEach(function(element) {
          new bootstrap.Dropdown(element);
        });
        console.log('Dropdowns initialized:', dropdownElements.length);
      } catch (error) {
        console.warn('Dropdown initialization failed:', error);
      }
    });

    console.log('Global objects and Bootstrap components initialized safely');
  </script>

  <script>
    // Pagination variables
    const itemsPerPage = 15; // Show 15 items per page for better viewing
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
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Bootstrap tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Initialize pagination after a short delay to ensure DOM is ready
      setTimeout(initializePagination, 100);
    });

    // Initialize pagination for all sections
    function initializePagination() {
      // Check if elements exist before updating pagination
      if (document.getElementById('gaps-container')) {
        updatePagination('gaps');
      }
      if (document.getElementById('skillsTableBody')) {
        updatePagination('skills');
      }
      if (document.getElementById('records-container')) {
        updatePagination('records');
      }
    }

    // Pagination Functions
    function previousPage(section) {
      let container;
      if (section === 'skills') {
        container = document.getElementById('skillsTableBody');
      } else {
        container = document.getElementById(`${section}-container`);
      }

      if (container && currentPages[section] > 1) {
        currentPages[section]--;
        updatePagination(section);
      }
    }

    function nextPage(section) {
      let container;
      if (section === 'skills') {
        container = document.getElementById('skillsTableBody');
      } else {
        container = document.getElementById(`${section}-container`);
      }

      if (container) {
        const totalPages = Math.ceil(allData[section].length / itemsPerPage);
        if (currentPages[section] < totalPages) {
          currentPages[section]++;
          updatePagination(section);
        }
      }
    }

    function updatePagination(section) {
      let container;
      if (section === 'skills') {
        container = document.getElementById('skillsTableBody');
      } else {
        container = document.getElementById(`${section}-container`);
      }
      if (!container) return;

      const data = allData[section];
      const totalPages = Math.ceil(data.length / itemsPerPage);
      const currentPage = currentPages[section];

      // Get elements
      const pageInfo = document.getElementById(`${section}-page-info`);
      const prevBtn = document.getElementById(`${section}-prev-btn`);
      const nextBtn = document.getElementById(`${section}-next-btn`);

      // Update elements if they exist
      if (pageInfo) {
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
      }
      if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
      }
      if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
      }

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
      const tableBody = document.getElementById('skillsTableBody');
      if (!tableBody) return;

      // Hide all skill rows first
      const allSkillRows = tableBody.querySelectorAll('.skill-row');
      allSkillRows.forEach((row, index) => {
        if (index < startIndex || index >= endIndex) {
          row.style.display = 'none';
        } else {
          row.style.display = 'table-row';
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

    // Get CSRF Token with error handling
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

    // Verify Admin Password
    async function verifyAdminPassword(password) {
      try {
        const csrfToken = getCSRFToken();
        if (!csrfToken) {
          throw new Error('CSRF token not available');
        }

        const response = await fetch('/admin/verify-password', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
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
              <input type="date" class="form-control" name="date_completed" value="${record.date_completed ? new Date(record.date_completed).toISOString().split('T')[0] : ''}" required>
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

    // Skills table search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
      const skillsSearch = document.getElementById('skillsSearch');
      const skillsFilter = document.getElementById('skillsFilter');
      const skillsTableBody = document.getElementById('skillsTableBody');

      function filterSkills() {
        const searchTerm = skillsSearch.value.toLowerCase();
        const filterValue = skillsFilter.value;
        const rows = skillsTableBody.querySelectorAll('.skill-row');

        rows.forEach(row => {
          const skillName = row.querySelector('strong').textContent.toLowerCase();
          const skillLevel = row.getAttribute('data-skill-level');

          let showRow = true;

          // Apply search filter
          if (searchTerm && !skillName.includes(searchTerm)) {
            showRow = false;
          }

          // Apply level filter
          if (filterValue && skillLevel !== filterValue) {
            showRow = false;
          }

          row.style.display = showRow ? '' : 'none';
        });

        // Show/hide empty message
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        const emptyMessage = document.getElementById('skillsEmptyMessage');

        if (visibleRows.length === 0 && !emptyMessage) {
          const tbody = document.getElementById('skillsTableBody');
          const emptyRow = document.createElement('tr');
          emptyRow.id = 'skillsEmptyMessage';
          emptyRow.innerHTML = `
            <td colspan="4" class="text-center py-4">
              <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
              <p class="text-muted mt-2 mb-0">No skills match your search criteria</p>
            </td>
          `;
          tbody.appendChild(emptyRow);
        } else if (visibleRows.length > 0 && emptyMessage) {
          emptyMessage.remove();
        }
      }

      if (skillsSearch) {
        skillsSearch.addEventListener('input', filterSkills);
      }

      if (skillsFilter) {
        skillsFilter.addEventListener('change', filterSkills);
      }
    });

    // View Employee Trainings Function
    function viewEmployeeTrainings(employeeId) {
      // Get employee records from the PHP data
      const employeeRecords = @json($records->groupBy('employee_id'));
      const records = employeeRecords[employeeId] || [];

      if (records.length === 0) {
        Swal.fire({
          icon: 'info',
          title: 'No Trainings Found',
          text: 'No training records found for this employee.',
          confirmButtonColor: '#0d6efd'
        });
        return;
      }

      // Get employee info
      const firstRecord = records[0];
      const employee = firstRecord.employee;
      const employeeName = employee ? `${employee.first_name} ${employee.last_name}` : 'Unknown Employee';

      // Build training list HTML
      let trainingsHtml = '';
      records.forEach((record, index) => {
        const trainingTitle = record.training?.course_title || record.training?.title || 'Unknown Training';
        const dateCompleted = record.date_completed && record.date_completed !== '1970-01-01'
          ? new Date(record.date_completed).toLocaleDateString()
          : 'Not completed';

        // Use the calculated progress from the record (includes exam scores and competency profiles)
        let progress = 0;

        // Try to get the calculated progress from different sources
        if (record.calculated_progress !== undefined) {
          progress = record.calculated_progress;
          console.log(`Using calculated_progress for ${trainingTitle}: ${progress}%`);
        } else if (record.display_progress !== undefined) {
          progress = record.display_progress;
          console.log(`Using display_progress for ${trainingTitle}: ${progress}%`);
        } else {
          progress = record.progress || 0;
          console.log(`Using fallback progress for ${trainingTitle}: ${progress}%`);
        }

        progress = Math.max(0, Math.min(100, parseInt(progress) || 0));
        const progressColor = progress >= 100 ? 'success' : progress > 0 ? 'primary' : 'secondary';
        const statusText = progress >= 100 ? 'Completed' : progress > 0 ? 'In Progress' : 'Not Started';

        trainingsHtml += `
          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <h6 class="card-title mb-1">${trainingTitle}</h6>
                  <small class="text-muted">Date Completed: ${dateCompleted}</small>
                </div>
                <div class="text-end">
                  <div class="progress mb-1" style="width: 100px; height: 8px;">
                    <div class="progress-bar bg-${progressColor}" style="width: ${progress}%"></div>
                  </div>
                  <small class="text-${progressColor}">${progress}%</small>
                </div>
              </div>
              <div class="mt-2">
                <span class="badge bg-${progressColor} bg-opacity-10 text-${progressColor}">${statusText}</span>
              </div>
            </div>
          </div>
        `;
      });

      Swal.fire({
        title: `<i class="bi bi-person-circle me-2"></i>${employeeName}`,
        html: `
          <div class="text-start">
            <h6 class="mb-3 text-primary">Training Records (${records.length})</h6>
            <div style="max-height: 400px; overflow-y: auto;">
              ${trainingsHtml}
            </div>
          </div>
        `,
        width: 700,
        showConfirmButton: true,
        confirmButtonText: '<i class="bi bi-check"></i> Close',
        confirmButtonColor: '#0d6efd',
        customClass: {
          htmlContainer: 'text-start'
        }
      });
    }
  </script>
</body>
</html>
