<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
// Robust translationService initialization to prevent undefined errors
if (typeof window.translationService === 'undefined') {
  window.translationService = {
    translate: function(key, params) { return key; },
    get: function(key, params) { return key; },
    trans: function(key, params) { return key; },
    choice: function(key, count, params) { return key; }
  };
}
if (typeof window.trans === 'undefined') {
  window.trans = function(key, params) { return key; };
}
    window.Laravel = {
      csrfToken: '{{ csrf_token() }}'
    };
  </script>
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">

  <!-- Custom Destination Card Styles -->
  <style>
    .destination-card {
      transition: all 0.3s ease;
      border: 1px solid #e0e0e0;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      font-size: 1rem;
      margin: 18px 0;
      padding: 0;
      background: #fff;
    }

    .destination-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      border-color: #007bff;
    }

    .info-section {
      transition: all 0.2s ease;
    }

    .info-section:hover {
      transform: translateY(-2px);
    }

    .info-section .bg-light {
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }

    .info-section:hover .bg-light {
      border-color: #dee2e6;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .destination-card .card-body {
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .destination-card .row.g-3 {
      flex: 1;
    }

    .destination-card .row.mt-3 {
      margin-top: auto !important;
    }

    /* Full-width maximized layout for all screen sizes */
    .col-12 {
      flex: 0 0 100%;
      max-width: 100%;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .destination-card .card-body {
        padding: 1rem;
      }

      .destination-card .card-header {
        padding: 0.75rem 1rem;
      }

      .destination-card .row.g-3 > .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }

    @media (min-width: 769px) {
      .destination-card .row.g-3 > .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
      }
    }

      .destination-card .card-header .d-flex {
        flex-direction: column;
        text-align: center;
      }

      .destination-card .card-header .text-end {
        text-align: center !important;
        margin-top: 1rem;
      }

      .destination-card .row .col-md-6 {
        margin-bottom: 1rem;
      }

      .destination-card .d-flex.justify-content-end {
        justify-content: center !important;
      }
    }

    .badge {
      font-size: 0.75rem;
      padding: 0.5rem 0.75rem;
    }

    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-outline-warning:hover,
    .btn-outline-danger:hover,
    .btn-outline-info:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced card content layout - Full Width Style */
    .destination-card .card-body {
      padding: 18px 24px 18px 24px;
      display: block;
      background: #fff;
    }

    .destination-card .info-section .bg-light {
      padding: 8px 14px;
      border-radius: 10px;
      border: 1px solid #e9ecef;
      word-wrap: break-word;
      overflow-wrap: break-word;
      background-color: #f8f9fa !important;
    }

    .destination-card .card-header {
      padding: 18px 24px 12px 24px;
      min-height: auto;
      border-radius: 16px 16px 0 0;
      background: #f3f8ff;
      border-bottom: none;
    }

    /* Horizontal layout for better space utilization */
    .destination-card .row.g-3 {
      margin: 0;
    }

    .destination-card .row.g-3 > .col-12,
    .destination-card .row.g-3 > .col-md-6 {
      margin-bottom: 1rem;
      padding: 0 0.75rem;
    }

    /* Improved button spacing */
    .destination-card .btn {
      margin: 0.25rem;
      white-space: nowrap;
    }

    /* Enhanced responsive design for maximized cards */
    @media (min-width: 1200px) {
      .destination-card .row.g-3 > .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
      }
    }

    /* Ensure text content is fully visible */
    .destination-card p {
      margin-bottom: 0.75rem;
      line-height: 1.6;
      word-wrap: break-word;
    }

    .destination-card .bg-light p {
      margin-bottom: 0.5rem;
    }

    .destination-card .bg-light p:last-child {
      margin-bottom: 0;
    }

    /* Maximize content visibility */
    .destination-card .info-section h6 {
      margin-bottom: 1rem;
      font-size: 1rem;
    }

    /* Better badge and status display */
    .destination-card .badge {
      font-size: 0.78rem;
      padding: 0.35rem 0.7rem;
      line-height: 1.1;
      border-radius: 8px;
    }

    /* Improve text readability */
    .destination-card .text-muted {
      font-size: 0.9rem;
    }

    .destination-card strong {
      font-weight: 600;
    }

    /* Full-width maximized card styling like the example */
    .destination-card {
      background: #ffffff;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .destination-card .card-header {
      border-bottom: 1px solid rgba(255,255,255,0.2);
      background: linear-gradient(135deg, #6c7b95, #8fa4c7) !important;
    }

    /* Better section organization */
    .destination-card .info-section {
      margin-bottom: 1rem;
    }

    .destination-card .info-section:last-child {
      margin-bottom: 0;
    }

    /* Enhanced action button area */
    .destination-card .row.mt-3 {
      border-top: 1px solid #e9ecef;
      padding-top: 1rem;
      margin-top: 1rem !important;
    }

    /* Clean, organized layout */
    .destination-card .row.g-3 > .col-12 {
      border-bottom: 1px solid #f1f3f4;
      padding-bottom: 1rem;
      margin-bottom: 1rem;
    }

    .destination-card .row.g-3 > .col-12:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }

    /* Progress bars and status indicators */
    .destination-card .progress {
      height: 8px;
      border-radius: 4px;
    }

    .destination-card .badge {
      font-size: 0.75rem;
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
    }

    /* Ensure cards are always visible */
    #destinationTableBody {
      visibility: visible !important;
      opacity: 1 !important;
    }

    #destinationTableBody .col-12[data-destination-id] {
      visibility: visible !important;
      opacity: 1 !important;
    }

    /* Prevent any JavaScript from hiding the container */
    .row.g-4 {
      display: flex !important;
      flex-wrap: wrap !important;
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
            <h2 class="fw-bold mb-1">Destination Knowledge Training</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage destination training records here.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Destination Knowledge Training</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card-body">



      <!-- Possible Training Destinations Table -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #fff3cd;">
          <div class="d-flex align-items-center gap-3">
            <h2 class="fw-bold mb-1">Accredited Training Centers</h2>
          </div>
        </div>
        <div class="card-body">
          @if($possibleDestinations->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover" id="possibleDestinationsTable">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Destination Name</th>
                    <th>Details</th>
                    <th>Objectives</th>
                    <th>Duration</th>
                    <th>Delivery Mode</th>
                  </tr>
                </thead>
                <tbody id="possibleDestinationsTableBody">
                  @foreach($possibleDestinations as $index => $destination)
  <tr class="possible-destination-row" data-index="{{ $index }}">
    <td>{{ $loop->iteration }}</td>
    <td><strong>{{ $destination->destination_name }}</strong></td>
    <td>
      <div style="max-width: 200px;">
        {{ Str::limit($destination->details, 100) }}
      </div>
    </td>
    <td>
      <div style="max-width: 200px;">
        {{ Str::limit($destination->objectives, 100) }}
      </div>
    </td>
    <td>{{ $destination->duration }}</td>
    <td>
      @switch($destination->delivery_mode)
        @case('On-Site Training')
          <span style="color: #000;">🏢 On-Site Training</span>
          @break
        @case('Blended Learning')
          <span style="color: #000;">🔄 Blended Learning</span>
          @break
        @case('Workshop')
          <span style="color: #000;">🎯 Workshop</span>
          @break
        @case('Seminar')
          <span style="color: #000;">📚 Seminar</span>
          @break
        @case('Field Training')
          <span style="color: #000;">🏃 Field Training</span>
          @break
        @case('Table Training')
          <span style="color: #000;">📋 Table Training</span>
          @break
        @default
          <span style="color: #000;">{{ $destination->delivery_mode }}</span>
      @endswitch
    </td>
  </tr>
@endforeach
                </tbody>
              </table>
            </div>

            <!-- Pagination for Possible Destinations -->
            <div class="d-flex justify-content-between align-items-center mt-3">
              <div class="text-muted">
                <small>Showing <span id="possibleCurrentStart">1</span> to <span id="possibleCurrentEnd">5</span> of <span id="possibleTotalRecords">{{ $possibleDestinations->count() }}</span> destinations</small>
              </div>
              <nav aria-label="Possible destinations pagination">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" id="possiblePrevBtn">
                    <button class="page-link" onclick="changePossiblePage(-1)">
                      <i class="bi bi-chevron-left"></i> Previous
                    </button>
                  </li>
                  <li class="page-item active">
                    <span class="page-link" id="possibleCurrentPage">1</span>
                  </li>
                  <li class="page-item" id="possibleNextBtn">
                    <button class="page-link" onclick="changePossiblePage(1)">
                      Next <i class="bi bi-chevron-right"></i>
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          @else
            <div class="text-center py-5">
              <div class="mb-4">
                <i class="bi bi-geo-alt display-1 text-muted"></i>
              </div>
              <h4 class="text-muted mb-3">No Possible Destinations Found</h4>
              <p class="text-muted mb-4">Get started by adding your first training destination</p>
              <button class="btn btn-primary btn-lg" onclick="confirmAction('add-destination', 'Add New Destination', 'Are you sure you want to add a new destination?')">
                <i class="bi bi-plus-lg me-2"></i> Add Your First Destination
              </button>
            </div>
          @endif
        </div>
      </div>

      <!-- Destination Knowledge Training Table -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #e3f0ff;">
          <div class="d-flex align-items-center gap-3">
            <h2 class="fw-bold mb-1">Destination Knowledge Training</h2>
          </div>
          <div class="d-flex gap-2">
            <input type="text" id="searchInput" class="form-control" placeholder="Search employee or destination..." style="max-width: 220px;">
            <select id="progressFilter" class="form-select" style="max-width: 150px;">
              <option value="">All Status</option>
              <option value="completed">Completed</option>
              <option value="in-progress">In Progress</option>
              <option value="not-started">Not Started</option>
            </select>
            <input type="date" id="dateFilter" class="form-control" style="max-width: 170px;">
            <button class="btn btn-primary" id="applyFilters"><i class="bi bi-funnel"></i> Filter</button>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="confirmAction('export-excel', 'Export Excel', 'Export training data to Excel?')">
              <i class="bi bi-download me-1"></i> Export Excel
            </button>
            <button class="btn btn-outline-primary btn-sm" onclick="confirmAction('export-pdf', 'Export PDF', 'Export training data to PDF?')">
              <i class="bi bi-download me-1"></i> Export PDF
            </button>
            <button class="btn btn-primary btn-sm" onclick="confirmAction('add-new', 'Add New Training', 'Add new training record?')">
              <i class="bi bi-plus-lg me-1"></i> Add New
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-4" id="destinationTableBody">
            @forelse($destinations as $record)
              @php
                $firstName = $record->employee->first_name ?? 'Unknown';
                $lastName = $record->employee->last_name ?? 'Employee';
                $fullName = $firstName . ' ' . $lastName;

                // Check if profile picture exists - simplified approach
                $profilePicUrl = null;
                if ($record->employee && $record->employee->profile_picture) {
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

                // Generate card header color based on status
                $headerColors = [
                    'completed' => 'linear-gradient(135deg, #6c7b95, #8fa4c7)',
                    'in-progress' => 'linear-gradient(135deg, #5a6c8a, #7289b0)',
                    'not-started' => 'linear-gradient(135deg, #4a5a78, #6c7b95)',
                    'expired' => 'linear-gradient(135deg, #3d4a63, #5a6c8a)',
                ];
                $statusKey = strtolower($record->status ?? 'not-started');
                $headerColor = $headerColors[$statusKey] ?? 'linear-gradient(135deg, #6c7b95, #8fa4c7)';
              @endphp

              <div class="col-12 col-md-6 col-lg-4" data-destination-id="{{ $record->id }}">
                <div class="destination-card h-100">
                  <!-- Card Header with Gradient -->
                  <div class="card-header" style="background: {{ $headerColor }}; color: white; padding: 1rem; position: relative;">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center">
                        <div class="me-3">
                          @if($record->employee)
                            <img src="{{ $profilePicUrl }}"
                                 alt="{{ $firstName }} {{ $lastName }}"
                                 class="rounded-circle border border-white"
                                 style="width: 50px; height: 50px; object-fit: cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($firstName . ' ' . $lastName) }}&size=200&background={{ $bgColor }}&color=ffffff&bold=true&rounded=true'">
                          @else
                            <div class="rounded-circle border border-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #6c757d;">
                              <i class="bi bi-person text-white"></i>
                            </div>
                          @endif
                        </div>
                        <div>
                          <h5 class="mb-1 fw-bold">
                            @if($record->employee)
                              {{ $firstName }} {{ $lastName }}
                            @else
                              Unknown Employee
                            @endif
                          </h5>
                          <p class="mb-0 opacity-75">
                            <i class="bi bi-hash me-1"></i>ID: {{ $loop->iteration }}
                          </p>
                        </div>
                      </div>
                      <div class="text-end">
                        <div class="badge bg-white bg-opacity-25 px-3 py-2 mb-1">
                          {{ $record->destination_name }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Card Body -->
                  <div class="card-body p-4">
                    <div class="row g-3">
                      <!-- Training Details -->
                      <div class="col-12">
                        <div class="info-section">
                          <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-info-circle me-2"></i>Training Details
                          </h6>
                          <div class="bg-light p-3 rounded">
                            <p class="mb-2"><strong>Details:</strong> {{ $record->details }}</p>
                            <p class="mb-0">
                              <strong>Delivery Mode:</strong>
                              @if($record->delivery_mode)
                                @switch($record->delivery_mode)
                                  @case('On-site Training')
                                    <span style="color: #000;">🏢 On-site Training</span>
                                    @break
                                  @case('Blended Learning')
                                    <span style="color: #000;">🔄 Blended Learning</span>
                                    @break
                                  @case('Workshop')
                                    <span style="color: #000;">🎯 Workshop</span>
                                    @break
                                  @case('Seminar')
                                    <span style="color: #000;">📚 Seminar</span>
                                    @break
                                  @case('Field Training')
                                    <span style="color: #000;">🏃 Field Training</span>
                                    @break
                                  @case('Table Training')
                                    <span style="color: #000;">📋 Table Training</span>
                                    @break
                                  @default
                                    <span style="color: #000;">{{ $record->delivery_mode }}</span>
                                @endswitch
                              @else
                                <span class="text-muted">Not Set</span>
                              @endif
                            </p>
                          </div>
                        </div>
                      </div>

                      <!-- Dates & Status -->
                      <div class="col-md-6">
                        <div class="info-section">
                          <h6 class="fw-bold text-success mb-2">
                            <i class="bi bi-calendar me-2"></i>Dates & Status
                          </h6>
                          <div class="bg-light p-3 rounded">
                            <p class="mb-2"><strong>Created:</strong> {{ $record->created_at->format('Y-m-d') }}</p>
                            <p class="mb-2"><strong>Expires:</strong>
                              @php
                                // Prefer linked UpcomingTraining deadline if available; fall back to record's expired_date
                                $deadlineDate = null;
                                try {
                                  $linkedUpcoming = \App\Models\UpcomingTraining::where('employee_id', $record->employee_id)
                                    ->where('destination_training_id', $record->id)
                                    ->first();
                                  if ($linkedUpcoming) {
                                    $deadlineDate = $linkedUpcoming->deadline ?? $linkedUpcoming->deadline_date ?? null;
                                  }
                                } catch (\Exception $e) {
                                  $deadlineDate = null;
                                }
                                $baseDate = $deadlineDate ?: $record->expired_date;
                              @endphp

                              @if($baseDate)
                                @php
                                  $expiredDate = \Carbon\Carbon::parse($baseDate);
                                  $now = \Carbon\Carbon::now();
                                  $daysUntilExpiry = $now->diffInDays($expiredDate, false);

                                  // Color coding based on days until expiry
                                  if ($daysUntilExpiry < 0) {
                                    $colorClass = 'text-danger fw-bold';
                                    $bgClass = 'bg-danger text-white';
                                    $status = 'EXPIRED';
                                  } elseif ($daysUntilExpiry <= 7) {
                                    $colorClass = 'text-warning fw-bold';
                                    $bgClass = 'bg-warning text-dark';
                                    $status = 'URGENT';
                                  } elseif ($daysUntilExpiry <= 30) {
                                    $colorClass = 'text-info fw-bold';
                                    $bgClass = 'bg-info text-white';
                                    $status = 'SOON';
                                  } else {
                                    $colorClass = 'text-success fw-bold';
                                    $bgClass = 'bg-success text-white';
                                    $status = 'ACTIVE';
                                  }
                                @endphp
                                <span class="{{ $colorClass }}">{{ $expiredDate->format('Y-m-d') }}</span>
                                <small class="badge {{ $bgClass }} mt-1 d-block">{{ $status }}</small>
                                @if($daysUntilExpiry > 0)
                                  <small class="text-muted d-block">{{ floor($daysUntilExpiry) }} days left</small>
                                @elseif($daysUntilExpiry < 0)
                                  @php $overdueDays = floor(abs($daysUntilExpiry)); @endphp
                                  @if($overdueDays > 0)
                                    <small class="text-danger d-block">{{ $overdueDays }} days overdue</small>
                                  @endif
                                @endif
                                @if($deadlineDate)
                                  <small class="text-muted d-block">Based on Upcoming Training</small>
                                @endif
                              @else
                                <span class="badge bg-secondary">Not Set</span>
                              @endif
                            </p>
                            <p class="mb-0">
                              <strong>Status:</strong>
                              @php
                                // Determine accurate status based on progress, expiry, and current status
                                $currentStatus = $record->status ?? 'not-started';
                                $currentProgress = $syncedProgress ?? $record->progress ?? 0;

                                // Check if expired
                                $isExpired = false;
                                if ($record->expired_date) {
                                  $expiredDate = \Carbon\Carbon::parse($record->expired_date);
                                  $isExpired = \Carbon\Carbon::now()->gt($expiredDate);
                                }

                                // Determine final status
                                if ($isExpired && $currentProgress < 100) {
                                  $finalStatus = 'expired';
                                  $badgeClass = 'bg-danger';
                                  $textClass = 'text-danger';
                                  $displayText = 'Expired';
                                } elseif ($currentProgress >= 100) {
                                  $finalStatus = 'completed';
                                  $badgeClass = 'bg-success';
                                  $textClass = 'text-success';
                                  $displayText = 'Completed';
                                } elseif ($currentStatus === 'in-progress' || $currentProgress > 0) {
                                  $finalStatus = 'in-progress';
                                  $badgeClass = 'bg-primary';
                                  $textClass = 'text-primary';
                                  $displayText = 'On Going';
                                } else {
                                  $finalStatus = 'not-started';
                                  $badgeClass = 'bg-secondary';
                                  $textClass = 'text-secondary';
                                  $displayText = 'Not Started';
                                }
                              @endphp
                              <span class="badge {{ $badgeClass }} bg-opacity-10 {{ $textClass }}">{{ $displayText }}</span>
                            </p>
                          </div>
                        </div>
                      </div>

                      <!-- Course Status -->
                      <div class="col-md-6">
                        <div class="info-section">
                          <h6 class="fw-bold text-info mb-2">
                            <i class="bi bi-bookmark me-2"></i>Course Status
                          </h6>
                          <div class="bg-light p-3 rounded text-center">
                            @php
                              // Check if there's a course in course_management for this destination
                              $courseStatus = 'Not Requested';
                              try {
                                $course = \App\Models\CourseManagement::where('course_title', $record->destination_name)->first();
                                if ($course) {
                                  $courseStatus = $course->status;
                                }
                              } catch (\Exception $e) {
                                $courseStatus = 'Not Requested';
                              }
                            @endphp

                            @if($courseStatus === 'Active')
                              <span class="badge bg-success bg-opacity-10 text-success fs-6">Active</span>
                            @elseif($courseStatus === 'Pending Approval')
                              <span class="badge bg-warning bg-opacity-10 text-warning fs-6">Pending Approval</span>
                            @elseif($courseStatus === 'Rejected')
                              <span class="badge bg-danger bg-opacity-10 text-danger fs-6">Rejected</span>
                            @else
                              <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6">Not Requested</span>
                            @endif
                          </div>
                        </div>
                      </div>

                      <!-- Upcoming Training -->
                      <div class="col-md-6">
                        <div class="info-section">
                          <h6 class="fw-bold text-secondary mb-2">
                            <i class="bi bi-calendar-check me-2"></i>Upcoming Training
                          </h6>
                          <div class="bg-light p-3 rounded text-center">
                            @php
                              // Check if employee has already accepted this specific training
                              $upcomingTraining = null;
                              $hasAccepted = false;

                              try {
                                $upcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $record->employee_id)
                                  ->where('destination_training_id', $record->id)
                                  ->first();

                                // Check if employee has accepted using multiple possible fields (case-insensitive, substring match)
                                if ($upcomingTraining) {
                                  $candidates = [
                                    strtolower(trim((string)($upcomingTraining->status ?? ''))),
                                    strtolower(trim((string)($upcomingTraining->employee_response ?? ''))),
                                    strtolower(trim((string)($upcomingTraining->response_status ?? '')))
                                  ];
                                  foreach ($candidates as $candidate) {
                                    if ($candidate !== '' && (strpos($candidate, 'accepted') !== false || strpos($candidate, 'completed') !== false)) {
                                      $hasAccepted = true;
                                      break;
                                    }
                                  }
                                  if (!$hasAccepted && isset($upcomingTraining->is_accepted)) {
                                    $hasAccepted = (bool)$upcomingTraining->is_accepted;
                                  }
                                }
                              } catch (\Exception $e) {
                                // Handle gracefully if table doesn't exist
                                $hasAccepted = false;
                              }
                            @endphp

                            @if($hasAccepted)
                              <span class="badge bg-success text-white">
                                <i class="bi bi-check-circle"></i> Completed (Accepted by Employee)
                              </span>
                            @else
                              @if($record->admin_approved_for_upcoming)
                                <span class="badge bg-success text-white">
                                  <i class="bi bi-check-circle"></i> Approved for Upcoming
                                </span>
                              @else
                                @php
                                  // Check if request has been activated/approved
                                  $isRequestActivated = false;

                                  // Check if there's an approved course in course_management for this destination
                                  try {
                                    $approvedCourse = \App\Models\CourseManagement::where('course_title', $record->destination_name)
                                      ->where('status', 'Active')
                                      ->first();

                                    if ($approvedCourse) {
                                      $isRequestActivated = true;
                                    }
                                  } catch (\Exception $e) {
                                    // Handle gracefully if table doesn't exist
                                    $isRequestActivated = false;
                                  }

                                  // Also check if status indicates activation was requested and processed
                                  if ($record->status === 'in-progress' || $record->status === 'completed') {
                                    $isRequestActivated = true;
                                  }
                                @endphp

                                @if($isRequestActivated)
                                  <button class="btn btn-info btn-sm assign-to-upcoming-btn"
                                          data-destination-id="{{ $record->id }}"
                                          data-employee-name="{{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }}"
                                          data-destination-name="{{ $record->destination_name }}"
                                          data-already-assigned="{{ $record->admin_approved_for_upcoming ? 'true' : 'false' }}"
                                          onclick="confirmAction('assign-upcoming', 'Assign to Upcoming Training', 'Assign {{ $record->destination_name }} to {{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }} upcoming training?', {{ $record->id }})"
                                          title="Assign {{ $record->destination_name }} to {{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }}'s upcoming training list">
                                    <i class="bi bi-calendar-check me-1"></i> Assign to Upcoming
                                  </button>
                                @else
                                  <button class="btn btn-secondary btn-sm"
                                          disabled
                                          title="Please activate the request first before assigning to upcoming training">
                                    <i class="bi bi-calendar-x me-1"></i> Request Not Activated
                                  </button>
                                @endif
                              @endif
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-3">
                      <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                          <button class="btn btn-outline-warning" onclick="confirmAction('edit-record', 'Edit Training Record', 'Edit training record for {{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }}?', {{ $record->id }})" title="Edit Record">
                            <i class="bi bi-pencil me-1"></i> Edit
                          </button>
                          <button class="btn btn-outline-danger" onclick="confirmDeleteRecord({{ $record->id }}, '{{ $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Employee' }}', '{{ $record->destination_name }}')" title="Delete Record">
                            <i class="bi bi-trash me-1"></i> Delete
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            @empty
              <div class="col-12">
                <div class="text-center py-5">
                  <div class="mb-4">
                    <i class="bi bi-person-workspace display-1 text-muted"></i>
                  </div>
                  <h4 class="text-muted mb-3">No Training Records Found</h4>
                  <p class="text-muted mb-4">Get started by adding your first destination knowledge training record</p>
                  <button class="btn btn-primary btn-lg" onclick="confirmAction('add-new', 'Add New Training', 'Add new training record?')">
                    <i class="bi bi-plus-lg me-2"></i> Add Your First Training Record
                  </button>
                </div>
              </div>
            @endforelse
          </div>

          <!-- Pagination for Destination Knowledge Training -->
          @if($destinations->count() > 0)
            <div class="d-flex justify-content-between align-items-center mt-4">
              <div class="text-muted">
                <small>Showing <span id="trainingCurrentStart">1</span> to <span id="trainingCurrentEnd">3</span> of <span id="trainingTotalRecords">{{ $destinations->count() }}</span> training records</small>
              </div>
              <nav aria-label="Training records pagination">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" id="trainingPrevBtn">
                    <button class="page-link" onclick="changeTrainingPage(-1)">
                      <i class="bi bi-chevron-left"></i> Previous
                    </button>
                  </li>
                  <li class="page-item active">
                    <span class="page-link" id="trainingCurrentPage">1</span>
                  </li>
                  <li class="page-item" id="trainingNextBtn">
                    <button class="page-link" onclick="changeTrainingPage(1)">
                      Next <i class="bi bi-chevron-right"></i>
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          @endif
        </div>
      </div>

      <!-- Add Destination Modal -->
      <div class="modal fade" id="addDestinationModal" tabindex="-1" aria-labelledby="addDestinationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 600px;">
          <div class="modal-content">
            <div class="card-header modal-header">
              <h5 class="modal-title" id="addDestinationModalLabel">Add Destination Knowledge Record</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="addDestinationForm" action="{{ route('admin.destination-knowledge-training.store') }}" method="POST">
                @csrf
                <div id="formErrors" class="alert alert-danger d-none" role="alert"></div>

                @if($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
                <div class="mb-3">
                  <label class="form-label" for="position">Position*</label>
                  <select class="form-select" name="position" id="position" required onchange="loadEmployeesByPosition()">
                    <option value="">Select Position</option>
                    <option value="EMPLOYEE">Employee</option>
                    <option value="MANAGER">Manager</option>
                    <option value="STAFF">Staff</option>
                    <option value="SUPERVISOR">Supervisor</option>
                    <option value="COORDINATOR">Coordinator</option>
                    <option value="AGENT">Travel Agent</option>
                    <option value="CONSULTANT">Travel Consultant</option>
                    <option value="GUIDE">Tour Guide</option>
                    <option value="DRIVER">Driver</option>
                  </select>
                </div>
                <div class="mb-3" id="employeeListContainer" style="display: none;">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Employees in Selected Position</label>
                    <div>
                      <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="selectAllEmployees()">
                        <i class="bi bi-check-all me-1"></i>Select All
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllEmployees()">
                        <i class="bi bi-x-square me-1"></i>Deselect All
                      </button>
                    </div>
                  </div>
                  <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <div id="employeeCheckboxList">
                      <!-- Employee checkboxes will be loaded here -->
                    </div>
                  </div>
                  <small class="text-muted">Select employees to assign training to</small>
                </div>
                <input type="hidden" name="employee_ids" id="employee_ids" value="">
                <div class="mb-3">
                  <label class="form-label" for="destination_name">Destination Name*</label>
                  <select class="form-select" name="destination_name" id="destination_name" required>
                    <option value="">Select Destination</option>
                    @foreach($destinationMasters as $destination)
                      <option value="{{ $destination->destination_name }}">{{ $destination->destination_name }}</option>
                    @endforeach
                    <option value="custom">Custom Destination (Type your own)</option>
                  </select>
                  <input type="text" class="form-control mt-2 d-none" name="custom_destination_name" id="custom_destination_name" placeholder="Enter custom destination name">
                  <div id="duplicateWarning" class="alert alert-warning mt-2 d-none">
                    <i class="bi bi-exclamation-triangle"></i> This employee already has a training record for this destination.
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="details">Details*</label>
                    <textarea class="form-control" name="details" id="details" rows="2" required></textarea>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="objectives">Objectives*</label>
                    <textarea class="form-control" name="objectives" id="objectives" rows="2" required></textarea>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="duration">Duration*</label>
                    <input type="text" class="form-control" name="duration" id="duration" placeholder="e.g., 5 days, 2 weeks" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="delivery_mode">Delivery Mode*</label>
                    <select class="form-select" name="delivery_mode" id="delivery_mode" required>
                      <option value="">Select Delivery Mode</option>
                      <option value="On-site Training" style="background-color: #198754; color: white;">🏢 On-site Training</option>
                      <option value="Blended Learning" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                      <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                      <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                      <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="expired_date">Expired Date</label>
                    <input type="date" class="form-control" name="expired_date" id="expired_date">
                  </div>
                </div>
                <div class="row" id="onlineTrainingFields" style="display: none;">
                  <div class="col-md-6 mb-3">
                    <label class="form-label" for="progress_level">Progress Level</label>
                    <select class="form-select" name="progress_level" id="progress_level">
                      <option value="0">0 - Not Started (0%)</option>
                      <option value="1">1 - Beginner (20%)</option>
                      <option value="2">2 - Developing (40%)</option>
                      <option value="3">3 - Proficient (60%)</option>
                      <option value="4">4 - Advanced (80%)</option>
                      <option value="5">5 - Expert (100%)</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" name="status" id="status">
                      <option value="not-started" selected>Not Started</option>
                    </select>
                  </div>
                </div>
                <!-- Removed Active field -->
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" id="saveDestinationBtn">Save Record</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Destination Modal -->
      @foreach($destinations as $record)
      <div class="modal fade" id="editDestinationModal{{ $record->id }}" tabindex="-1" aria-labelledby="editDestinationModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 500px;">
          <div class="modal-content">
            <div class="card-header modal-header py-2">
              <h6 class="modal-title" id="editDestinationModalLabel{{ $record->id }}">Edit Destination Knowledge Record</h6>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
              <form action="{{ route('admin.destination-knowledge-training.update', $record->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="employee_id_{{ $record->id }}">Employee*</label>
                    <select class="form-select form-select-sm" name="employee_id" id="employee_id_{{ $record->id }}" required>
                      @foreach($employees as $employee)
                        <option value="{{ $employee->employee_id }}" {{ $record->employee_id == $employee->employee_id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="destination_name_{{ $record->id }}">Destination Name*</label>
                    <input type="text" class="form-control form-control-sm" name="destination_name" id="destination_name_{{ $record->id }}" value="{{ $record->destination_name }}" required>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="details_{{ $record->id }}">Details*</label>
                    <textarea class="form-control form-control-sm" name="details" id="details_{{ $record->id }}" rows="2" required>{{ $record->details }}</textarea>
                  </div>
                </div>

                <div class="row">
                  <div class="col-6 mb-2">
                    <label class="form-label small" for="progress_level_{{ $record->id }}">Progress Level</label>
                    <select class="form-select form-select-sm" name="progress_level" id="progress_level_{{ $record->id }}" required disabled>
                      @php
                        // Convert current progress percentage to level (0-5)
                        $currentLevel = 0;
                        if ($record->progress >= 80) $currentLevel = 5;
                        elseif ($record->progress >= 60) $currentLevel = 4;
                        elseif ($record->progress >= 40) $currentLevel = 3;
                        elseif ($record->progress >= 20) $currentLevel = 2;
                        elseif ($record->progress > 0) $currentLevel = 1;
                        else $currentLevel = 0;
                      @endphp
                      <option value="0" {{ $currentLevel == 0 ? 'selected' : '' }}>0 - Not Started (0%)</option>
                      <option value="1" {{ $currentLevel == 1 ? 'selected' : '' }}>1 - Beginner (20%)</option>
                      <option value="2" {{ $currentLevel == 2 ? 'selected' : '' }}>2 - Developing (40%)</option>
                      <option value="3" {{ $currentLevel == 3 ? 'selected' : '' }}>3 - Proficient (60%)</option>
                      <option value="4" {{ $currentLevel == 4 ? 'selected' : '' }}>4 - Advanced (80%)</option>
                      <option value="5" {{ $currentLevel == 5 ? 'selected' : '' }}>5 - Expert (100%)</option>
                    </select>
                  </div>
                  <div class="col-6 mb-2">
                    <label class="form-label small" for="status_{{ $record->id }}">Status <small class="text-muted">(Auto-calculated)</small></label>
                    <select class="form-select form-select-sm" name="status" id="status_{{ $record->id }}" required disabled>
                      <option value="not-started" {{ ($record->status ?? 'not-started') == 'not-started' ? 'selected' : '' }}>Not Started</option>
                      <option value="in-progress" {{ ($record->status ?? 'in-progress') == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                      <option value="completed" {{ ($record->status ?? 'completed') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    <small class="text-info d-block">
                      <i class="bi bi-info-circle"></i> Auto-set based on progress level
                    </small>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="expired_date_{{ $record->id }}">Expired Date</label>
                    <input type="date" class="form-control form-control-sm" name="expired_date" id="expired_date_{{ $record->id }}" value="{{ $record->expired_date ? $record->expired_date->format('Y-m-d') : '' }}">
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <label class="form-label small" for="remarks_{{ $record->id }}">Remarks</label>
                    <textarea class="form-control form-control-sm" name="remarks" id="remarks_{{ $record->id }}" rows="1" placeholder="Optional remarks...">{{ $record->remarks ?? '' }}</textarea>
                  </div>
                </div>

                <div class="modal-footer py-2 px-0">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary btn-sm">Update Record</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      @endforeach

      <!-- Request Course Activation Modal -->
      @foreach($destinations as $record)
      <div class="modal fade" id="requestCourseActivationModal{{ $record->id }}" tabindex="-1" aria-labelledby="requestCourseActivationModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="requestCourseActivationModalLabel{{ $record->id }}">Request Course Activation</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="{{ route('admin.course_management.assign_destination', ['employeeId' => $record->employee_id]) }}" method="POST">
                @csrf
                <div class="mb-3">
                  <label class="form-label" for="destinationName_{{ $record->id }}">Destination Name*</label>
                  <input type="text" class="form-control" name="destinationName" id="destinationName_{{ $record->id }}" value="{{ $record->destination_name }}" readonly required>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="request_message_{{ $record->id }}">Message*</label>
                  <textarea class="form-control" name="request_message" id="request_message_{{ $record->id }}" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Send Request</button>
                </div>
              </form>
              @if(session('course_management_redirect'))
              <script>
                window.location.href = "{{ session('course_management_redirect') }}";
              </script>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach

      <!-- Add Possible Destination Modal -->
      <div class="modal fade" id="addPossibleDestinationModal" tabindex="-1" aria-labelledby="addPossibleDestinationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
              <h5 class="modal-title" id="addPossibleDestinationModalLabel">Add Possible Training Destination</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="addPossibleDestinationForm" action="{{ route('admin.destination-knowledge-training.store-possible') }}" method="POST">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="possible_destination">Destination*</label>
                      <input type="text" class="form-control" name="destination" id="possible_destination" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="possible_duration">Duration*</label>
                      <input type="text" class="form-control" name="duration" id="possible_duration" placeholder="e.g., 5 days, 2 weeks" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="possible_details">Details*</label>
                  <textarea class="form-control" name="details" id="possible_details" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="possible_objectives">Objectives*</label>
                  <textarea class="form-control" name="objectives" id="possible_objectives" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="possible_delivery_mode">Delivery Mode*</label>
                  <select class="form-select" name="delivery_mode" id="possible_delivery_mode" required>
                    <option value="">Select Delivery Mode</option>
                    <option value="On-site Training" style="background-color: #198754; color: white;">🏢 On-site Training</option>
                    <option value="Blended Learning" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                    <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                    <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                    <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" id="savePossibleDestinationBtn">Save Destination</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Possible Destination Modals -->
      <div class="modal fade" id="editPossibleDestinationModal1" tabindex="-1" aria-labelledby="editPossibleDestinationModalLabel1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
              <h5 class="modal-title" id="editPossibleDestinationModalLabel1">Edit Possible Training Destination</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="editPossibleDestinationForm1">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_destination1">Destination*</label>
                      <input type="text" class="form-control" name="destination" id="edit_possible_destination1" value="Baesa Quezon City" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_duration1">Duration*</label>
                      <input type="text" class="form-control" name="duration" id="edit_possible_duration1" value="5 days" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_details1">Details*</label>
                  <textarea class="form-control" name="details" id="edit_possible_details1" rows="3" required>Comprehensive training on Baesa operations and customer service protocols</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_objectives1">Objectives*</label>
                  <textarea class="form-control" name="objectives" id="edit_possible_objectives1" rows="3" required>Master destination-specific procedures, customer handling, and operational excellence</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_delivery_mode1">Delivery Mode*</label>
                  <select class="form-select" name="delivery_mode" id="edit_possible_delivery_mode1" required>
                    <option value="On-site Training" selected style="background-color: #198754; color: white;">🏢 On-site Training</option>
                    <option value="Blended Learning" style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                    <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                    <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                    <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Destination</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="editPossibleDestinationModal2" tabindex="-1" aria-labelledby="editPossibleDestinationModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
              <h5 class="modal-title" id="editPossibleDestinationModalLabel2">Edit Possible Training Destination</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="editPossibleDestinationForm2">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_destination2">Destination*</label>
                      <input type="text" class="form-control" name="destination" id="edit_possible_destination2" value="Cubao Terminal" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label" for="edit_possible_duration2">Duration*</label>
                      <input type="text" class="form-control" name="duration" id="edit_possible_duration2" value="3 days" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_details2">Details*</label>
                  <textarea class="form-control" name="details" id="edit_possible_details2" rows="3" required>Terminal operations and passenger management training</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_objectives2">Objectives*</label>
                  <textarea class="form-control" name="objectives" id="edit_possible_objectives2" rows="3" required>Learn terminal procedures, safety protocols, and passenger assistance</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="edit_possible_delivery_mode2">Delivery Mode*</label>
                  <select class="form-select" name="delivery_mode" id="edit_possible_delivery_mode2" required>
                    <option value="On-site Training" style="background-color: #198754; color: white;">🏢 On-site Training</option>
                    <option value="Blended Learning" selected style="background-color: #0d6efd; color: white;">🔄 Blended Learning</option>
                    <option value="Workshop" style="background-color: #6f42c1; color: white;">🎯 Workshop</option>
                    <option value="Seminar" style="background-color: #20c997; color: white;">📚 Seminar</option>
                    <option value="Field Training" style="background-color: #dc3545; color: white;">🏃 Field Training</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Destination</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Ensure cards are visible after page load
    ensureCardsVisible();

    const addForm = document.getElementById('addDestinationForm');
    const formErrors = document.getElementById('formErrors');
    const saveBtn = document.getElementById('saveDestinationBtn');
    const modalEl = document.getElementById('addDestinationModal');
    const bsModal = new bootstrap.Modal(modalEl);

    // Function to ensure cards are properly displayed
    function ensureCardsVisible() {
      const cardContainer = document.getElementById('destinationTableBody');
      const cards = document.querySelectorAll('#destinationTableBody .col-12[data-destination-id]');

      if (cardContainer && cards.length > 0) {
        // Make sure container is visible
        cardContainer.style.display = '';

        // Make sure all cards are visible
        cards.forEach(card => {
          card.style.display = '';
          card.style.visibility = 'visible';
          card.style.opacity = '1';
        });

        console.log(`Ensured ${cards.length} cards are visible`);
      } else if (cardContainer) {
        console.log('Card container found but no cards detected');
      }
    }

    // Function to show success toast
    function showSuccessToast(message) {
      const toast = new bootstrap.Toast(document.getElementById('successToast'), {
        animation: true,
        autohide: true,
        delay: 3000
      });
      document.querySelector('#successToast .toast-body').textContent = message || 'Record saved successfully';
      toast.show();
    }

    // Function to reset form and clear errors
    function resetForm() {
      addForm.reset();
      formErrors.style.display = 'none';

      // Clear validation errors
      addForm.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        const feedback = el.parentElement.querySelector('.invalid-feedback');
        if (feedback) feedback.remove();
      });
      // Hide duplicate warning
      document.getElementById('duplicateWarning').classList.add('d-none');

      // Clear any error notifications from the page
      setTimeout(() => {
        const errorNotifications = document.querySelectorAll('.alert-danger');
        errorNotifications.forEach(notification => {
          if (notification.textContent.includes('already exists') ||
              notification.textContent.includes('duplicate') ||
              notification.textContent.includes('BESTLINK COLLEGE')) {
            notification.remove();
          }
        });

        // Also remove any toast notifications with error content
        const toastNotifications = document.querySelectorAll('.toast');
        toastNotifications.forEach(toast => {
          if (toast.textContent.includes('already exists') ||
              toast.textContent.includes('duplicate') ||
              toast.textContent.includes('BESTLINK COLLEGE')) {
            toast.remove();
          }
        });
      }, 100);
    }

    // Function to check for duplicates
    function checkForDuplicates() {
      const employeeIdEl = document.getElementById('employee_id');
      const destinationNameEl = document.getElementById('destination_name');
      const deliveryModeEl = document.getElementById('delivery_mode');
      const duplicateWarning = document.getElementById('duplicateWarning');
      const saveBtn = document.getElementById('saveDestinationBtn');

      // Null checks for all required elements
      if (!employeeIdEl || !destinationNameEl || !deliveryModeEl || !duplicateWarning || !saveBtn) {
        return;
      }

      const employeeId = employeeIdEl.value;
      const destinationName = destinationNameEl.value;
      const deliveryMode = deliveryModeEl.value;

      // Always hide warning and enable button initially
      duplicateWarning.classList.add('d-none');
      saveBtn.disabled = false;

      // Only check for duplicates if employee, destination AND delivery mode are selected
      if (employeeId && employeeId !== '' && destinationName && destinationName !== '' && destinationName !== 'custom' && deliveryMode && deliveryMode !== '') {
        // Get employee name from select option
        const selectedOption = document.querySelector(`#employee_id option[value="${employeeId}"]`);
        const selectedEmployeeName = selectedOption ? selectedOption.textContent.trim() : '';

        // Only proceed if we have a valid employee name
        if (selectedEmployeeName && selectedEmployeeName !== 'Select Employee') {
          // Check existing records in the table
          const tableRows = document.querySelectorAll('#destinationTableBody tr[data-destination-id]');
          let isDuplicate = false;
          let existingRecordId = null;

          tableRows.forEach(row => {
            const rowEmployee = row.children[1]?.textContent.trim();
            const rowDestination = row.children[2]?.textContent.trim();
            const rowDeliveryMode = row.children[4]?.textContent.trim();
            const recordId = row.getAttribute('data-destination-id');

            // Check if EXACT same employee, destination AND delivery mode combination exists
            if (rowEmployee === selectedEmployeeName &&
                rowDestination.toLowerCase().trim() === destinationName.toLowerCase().trim() &&
                rowDeliveryMode.includes(deliveryMode)) {
              isDuplicate = true;
              existingRecordId = recordId;
            }
          });

          if (isDuplicate) {
            duplicateWarning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> This employee already has an active training record for this destination with ${deliveryMode} delivery mode. Please update the existing record (ID: ${existingRecordId}) instead.`;
            duplicateWarning.classList.remove('d-none');
            saveBtn.disabled = true;
          }
        }
      }
    }

    if (addForm) {
      addForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Check for duplicates one more time before submission
        checkForDuplicates();

        // If save button is disabled due to duplicates, prevent submission
        if (saveBtn.disabled && duplicateWarning && !duplicateWarning.classList.contains('d-none')) {
          return false;
        }

        try {
          // Show loading state
          saveBtn.disabled = true;
          saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
          formErrors.style.display = 'none';

          const formData = new FormData(addForm);

          const response = await fetch(addForm.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (response.ok && data.success) {
            // Success case - close modal and show success
            resetForm();
            bsModal.hide();

            // Clear all error notifications immediately
            document.querySelectorAll('.alert-danger').forEach(notification => {
              notification.remove();
            });

            // Clear notification container
            const notificationContainer = document.getElementById('notificationContainer');
            if (notificationContainer) {
              notificationContainer.innerHTML = '';
            }

            
            // Automatically assign training from Accredited Training Center
            if (data.record_ids && data.record_ids.length > 0) {
              console.log('Auto-assigning trainings for records:', data.record_ids);

              // Auto-assign each record
              const autoAssignPromises = data.record_ids.map(recordId => {
                return fetch(`/admin/destination-knowledge-training/${recordId}/request-activation`, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                    auto_assign: true
                  })
                }).catch(err => {
                  console.error('Auto-assign error for record', recordId, ':', err);
                });
              });

              // Wait for all auto-assignments to complete
              Promise.all(autoAssignPromises).then(() => {
                console.log('All trainings auto-assigned successfully');

                // Ensure cards are visible before reload
                ensureCardsVisible();

                // Reload page to show updated data immediately
                setTimeout(() => {
                  window.location.reload(true);
                }, 1500);
              });
            } else {
              // Ensure cards are visible before reload
              ensureCardsVisible();

              // Reload page to show updated data immediately
              setTimeout(() => {
                window.location.reload(true);
              }, 1000);
            }
          } else {
            // Error case - show error but don't close modal
            if (data.message) {
              formErrors.innerHTML = data.message;
              formErrors.style.display = 'block';
            }
            alert(data.message || 'Failed to save record');
          }

        } catch (error) {
          console.error('Form submission error:', error);
          // Show error message
          formErrors.innerHTML = 'An error occurred while saving the record.';
          formErrors.style.display = 'block';
          alert('An error occurred while saving the record.');
        } finally {
          saveBtn.disabled = false;
          saveBtn.innerHTML = 'Save Record';
        }
      });
    }



    // Modal backdrop removal
    function removeBackdrops() {
      document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    }

    // Add event listeners for duplicate checking
    const employeeIdEl = document.getElementById('employee_id');
    const destinationNameEl = document.getElementById('destination_name');
    const deliveryModeEl = document.getElementById('delivery_mode');

    if (employeeIdEl) employeeIdEl.addEventListener('change', checkForDuplicates);
    if (destinationNameEl) destinationNameEl.addEventListener('change', checkForDuplicates);
    if (deliveryModeEl) deliveryModeEl.addEventListener('change', checkForDuplicates);

    modalEl.addEventListener('hidden.bs.modal', function () {
      resetForm();
      removeBackdrops();
      // Force remove any lingering error notifications
      const errorNotifications = document.querySelectorAll('.alert-danger');
      errorNotifications.forEach(notification => {
        if (notification.textContent.includes('already exists')) {
          notification.remove();
        }
      });
    });

    modalEl.addEventListener('shown.bs.modal', function() {
      removeBackdrops();
      // Clear any previous error states
      formErrors.style.display = 'none';
      document.getElementById('duplicateWarning').classList.add('d-none');
    });
  });

  // Enhanced backdrop removal for all modals
  document.querySelectorAll('.modal').forEach(function(modalEl) {
    modalEl.addEventListener('shown.bs.modal', function() {
      document.querySelectorAll('.modal-backdrop').forEach(function(bd) { bd.remove(); });
    });
    modalEl.addEventListener('hidden.bs.modal', function() {
      document.querySelectorAll('.modal-backdrop').forEach(function(bd) { bd.remove(); });
      // Remove any error notifications that might be lingering
      setTimeout(() => {
        const errorNotifications = document.querySelectorAll('.alert-danger');
        errorNotifications.forEach(notification => {
          if (notification.textContent.includes('already exists') || notification.textContent.includes('duplicate')) {
            notification.remove();
          }
        });
      }, 100);
    });
  });

  // Filter functionality - Updated for card layout
  const applyFiltersBtn = document.getElementById('applyFilters');
  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const progressFilter = document.getElementById('progressFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const cards = document.querySelectorAll('#destinationTableBody .col-12[data-destination-id]');

    cards.forEach(cardCol => {
      let show = true;
      const card = cardCol.querySelector('.destination-card');

      if (card) {
        // Extract text content from card for filtering
        const cardText = card.textContent.toLowerCase();
        const employeeName = card.querySelector('.card-header h5')?.textContent.toLowerCase() || '';
        const statusBadge = card.querySelector('.badge')?.textContent.toLowerCase() || '';

        // Apply filters
        if (search && !cardText.includes(search) && !employeeName.includes(search)) {
          show = false;
        }

        if (progressFilter === 'completed' && !statusBadge.includes('completed')) {
          show = false;
        }

        if (progressFilter === 'in-progress' && !statusBadge.includes('progress')) {
          show = false;
        }

        if (progressFilter === 'not-started' && !statusBadge.includes('not started')) {
          show = false;
        }

        if (dateFilter && !cardText.includes(dateFilter)) {
          show = false;
        }
      }

      cardCol.style.display = show ? '' : 'none';
    });
    });
  }

  // Export Excel (basic CSV)
  const exportExcelBtn = document.getElementById('exportExcel');
  if (exportExcelBtn) {
    exportExcelBtn.addEventListener('click', function() {
    let csv = '';
    document.querySelectorAll('table thead th').forEach(th => {
      csv += '"' + th.textContent.trim() + '",';
    });
    csv = csv.slice(0, -1) + '\n';
    document.querySelectorAll('table tbody tr').forEach(row => {
      if (row.style.display !== 'none') {
        row.querySelectorAll('td').forEach(td => {
          csv += '"' + td.textContent.trim() + '",';
        });
        csv = csv.slice(0, -1) + '\n';
      }
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'destination_knowledge_training.csv';
    link.click();
    });
  }

  // Export PDF (print)
  const exportPdfBtn = document.getElementById('exportPdf');
  if (exportPdfBtn) {
    exportPdfBtn.addEventListener('click', function() {
      window.print();
    });
  }

  // (Deprecated duplicate) Sync Missing Records handler removed. Use setupSyncButton() below.

  // Initialize admin topbar dropdowns to fix Tools and Jetlouge Admin clickability
  setTimeout(function() {
    try {
      // Initialize Bootstrap dropdowns for admin topbar
      const dropdownElementList = document.querySelectorAll('.dropdown-toggle');

      dropdownElementList.forEach(function (dropdownToggleEl) {
        try {
          // Check if already initialized
          if (!bootstrap.Dropdown.getInstance(dropdownToggleEl)) {
            new bootstrap.Dropdown(dropdownToggleEl, {
              boundary: 'viewport',
              display: 'dynamic',
              autoClose: true
            });
            console.log('Initialized dropdown:', dropdownToggleEl.id);
          }
        } catch (e) {
          console.warn('Failed to initialize dropdown:', dropdownToggleEl.id, e);
        }
      });

      console.log('Admin topbar dropdowns initialized successfully');
    } catch (error) {
      console.error('Error initializing admin topbar dropdowns:', error);
    }
  }, 500); // Small delay to ensure Bootstrap is fully loaded

  // Function to load employees by selected position
  function loadEmployeesByPosition() {
    const positionSelect = document.getElementById('position');
    const employeeListContainer = document.getElementById('employeeListContainer');
    const employeeCheckboxList = document.getElementById('employeeCheckboxList');
    const employeeIdsInput = document.getElementById('employee_ids');

    const selectedPosition = positionSelect.value;

    if (!selectedPosition) {
      employeeListContainer.style.display = 'none';
      employeeCheckboxList.innerHTML = '';
      employeeIdsInput.value = '';
      return;
    }

    // Show loading state
    employeeCheckboxList.innerHTML = '<div class="text-center"><i class="bi bi-spinner-border"></i> Loading employees...</div>';
    employeeListContainer.style.display = 'block';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Fetch employees by position
    fetch('/admin/destination-knowledge-training/employees-by-position', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        position: selectedPosition
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.employees) {
        if (data.employees.length === 0) {
          employeeCheckboxList.innerHTML = '<div class="text-muted text-center">No employees found for this position</div>';
          return;
        }

        // Build checkbox list
        let checkboxHtml = '';
        data.employees.forEach(employee => {
          checkboxHtml += `
            <div class="form-check mb-2">
              <input class="form-check-input employee-checkbox" type="checkbox"
                     value="${employee.employee_id}"
                     id="emp_${employee.employee_id}"
                     onchange="updateSelectedEmployees()">
              <label class="form-check-label" for="emp_${employee.employee_id}">
                <strong>${employee.first_name} ${employee.last_name}</strong>
                <small class="text-muted d-block">ID: ${employee.employee_id}</small>
              </label>
            </div>
          `;
        });

        employeeCheckboxList.innerHTML = checkboxHtml;
      } else {
        employeeCheckboxList.innerHTML = '<div class="text-danger">Error loading employees: ' + (data.message || 'Unknown error') + '</div>';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      employeeCheckboxList.innerHTML = '<div class="text-danger">Error loading employees. Please try again.</div>';
    });
  }

  // Function to update selected employees hidden input
  function updateSelectedEmployees() {
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('employee_ids').value = selectedIds.join(',');
  }

  // Function to select all employees
  function selectAllEmployees() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.checked = true;
    });
    updateSelectedEmployees();
  }

  // Function to deselect all employees
  function deselectAllEmployees() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.checked = false;
    });
    updateSelectedEmployees();
  }

  </script>
  <script>
  // Remove all .modal-backdrop elements on page load and after any modal event
  function removeAllModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
      backdrop.remove();
    });
  }
  window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
  document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
  document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);

  // Additional check after window fully loads
  window.addEventListener('load', function() {
    setTimeout(() => {
      const cardContainer = document.getElementById('destinationTableBody');
      const cards = document.querySelectorAll('#destinationTableBody .col-12[data-destination-id]');

      if (cardContainer && cards.length > 0) {
        cardContainer.style.display = '';
        cards.forEach(card => {
          card.style.display = '';
          card.style.visibility = 'visible';
          card.style.opacity = '1';
        });
        console.log('Window load: Cards visibility ensured');
      }
    }, 100);
  });

  // Progress tracking functionality
  function checkTrainingProgress() {
    const trainingIds = JSON.parse(sessionStorage.getItem('activeTrainingIds') || '[]');
    const destinationId = sessionStorage.getItem('destinationId');

    if (trainingIds.length > 0 && destinationId) {
      trainingIds.forEach(trainingId => {
        fetch(`/employee_trainings_dashboard/${trainingId}`)
          .then(res => res.json())
          .then(data => {
            if (data.progress !== undefined) {
              // Update destination knowledge progress
              fetch(`/destination-knowledge-training/progress/${destinationId}/${trainingId}`, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                  'Content-Type': 'application/json'
                }
              })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  // Update UI if needed
                  const row = document.querySelector(`tr[data-destination-id="${destinationId}"]`);
                  if (row) {
                    const progressBar = row.querySelector('progress');
                    const progressText = row.querySelector('.progress-text');
                    const statusBadge = row.querySelector('.status-badge');
                    if (progressBar) progressBar.value = data.progress;
                    if (progressText) progressText.textContent = `${data.progress}%`;
                    if (statusBadge) {
                      statusBadge.textContent = data.status;
                      statusBadge.className = `badge ${data.status === 'Completed' ? 'bg-success' : 'bg-primary'} bg-opacity-10 text-${data.status === 'Completed' ? 'success' : 'primary'} fs-6`;
                    }
                  }
                }
              })
              .catch(console.error);
            }
          })
          .catch(console.error);
      });
    }
  }

  // Request Activation functionality is now handled in attachEventListeners()

  // Function to attach event listeners to new elements
  function attachEventListeners() {


    // Re-attach request activation button listeners
    document.querySelectorAll('.request-activation-btn:not([data-listener-attached])').forEach(button => {
      button.setAttribute('data-listener-attached', 'true');
      button.addEventListener('click', function() {
        const recordId = this.getAttribute('data-record-id');
        const destinationName = this.getAttribute('data-destination-name');
        const deliveryMode = this.getAttribute('data-delivery-mode');
        const alreadyAssigned = this.getAttribute('data-already-assigned');

        // Check if already assigned
        if (alreadyAssigned === 'true') {
          const message = 'This training is already assigned or in upcoming training for this employee.';
          alert(message);
          return;
        }

        // Show password verification before proceeding
        showPasswordVerification(async () => {
          try {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Requesting...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch(`{{ url('admin/destination-knowledge-training') }}/${recordId}/request-activation`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                delivery_mode: deliveryMode
              })
            });

            const data = await response.json();

            if (data.success) {
              const successMessage = `${deliveryMode} request submitted successfully!`;
              
              // Update button to show pending status
              this.innerHTML = '<i class="bi bi-clock"></i> Request Submitted';
              this.classList.remove('btn-outline-primary');
              this.classList.add('btn-success', 'disabled');
              this.disabled = true;

              // Redirect to course management after a short delay for online training
              if (deliveryMode === 'Online Training') {
                setTimeout(() => {
                  if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                  }
                }, 3000);
              }
            } else {
              alert(data.message || 'Request failed.');
            }
          } catch (error) {
            console.error('Request activation error:', error);
            alert('Request failed: ' + (error.message || 'Please try again'));
          } finally {
            this.disabled = false;
            const buttonText = 'Request Training';
            this.innerHTML = `<i class="bi bi-clipboard-check"></i> ${buttonText}`;
          }
        });
      });
    });
  }

  // Possible Training Destinations functionality
  function initializePossibleDestinations() {
    // Add Possible Destination Form Handler
    const addPossibleForm = document.getElementById('addPossibleDestinationForm');
    if (addPossibleForm) {
      addPossibleForm.addEventListener('submit', function(e) {
        // Don't prevent default - let it submit to backend
        const saveBtn = document.getElementById('savePossibleDestinationBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
      });
    }

    // Edit Possible Destination Form Handlers
    const editForms = ['editPossibleDestinationForm1', 'editPossibleDestinationForm2'];
    editForms.forEach(formId => {
      const form = document.getElementById(formId);
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();

          const submitBtn = form.querySelector('button[type="submit"]');
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

          // Get form data
          const formData = new FormData(form);
          const destination = formData.get('destination');
          const details = formData.get('details');
          const objectives = formData.get('objectives');
          const duration = formData.get('duration');
          const deliveryMode = formData.get('delivery_mode');

          // Update the corresponding table row
          const rowId = formId.includes('1') ? 1 : 2;
          const tableBody = document.getElementById('possibleDestinationsTableBody');
          const rows = tableBody.querySelectorAll('tr');

          rows.forEach(row => {
            if (row.cells[0].textContent == rowId) {
              row.cells[1].textContent = destination;
              row.cells[2].textContent = details;
              row.cells[3].textContent = objectives;
              row.cells[4].textContent = duration;
              row.cells[5].textContent = deliveryMode;
            }
          });

          // Close modal
          const modalId = formId.replace('Form', 'Modal');
          const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
          modal.hide();

          
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Update Destination';
        });
      }
    });
  }

  // Delete Possible Destination function
  function deletePossibleDestination(id, destinationName) {
    if (confirm(`Are you sure you want to delete "${destinationName}"?`)) {
      fetch(`/admin/destination-knowledge-training/destroy-possible/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Remove the row from the table
          const tableBody = document.getElementById('possibleDestinationsTableBody');
          const rows = tableBody.querySelectorAll('tr');

          rows.forEach(row => {
            if (row.cells[0].textContent == id) {
              row.remove();
            }
          });

                  } else {
                  }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete destination. Please try again.');
      });
    }
  }

  // Auto-populate destination details when destination is selected
  function setupDestinationAutoPopulation() {
    const destinationSelect = document.getElementById('destination_name');
    const customDestinationInput = document.getElementById('custom_destination_name');
    const detailsTextarea = document.getElementById('details');
    const objectivesTextarea = document.getElementById('objectives');
    const durationInput = document.getElementById('duration');
    const deliveryModeSelect = document.getElementById('delivery_mode');

    if (destinationSelect) {
      destinationSelect.addEventListener('change', function() {
        const selectedValue = this.value;

        if (selectedValue === 'custom') {
          // Show custom input field
          customDestinationInput.classList.remove('d-none');
          customDestinationInput.required = true;

          // Clear auto-populated fields
          detailsTextarea.value = '';
          objectivesTextarea.value = '';
          durationInput.value = '';
          deliveryModeSelect.value = '';
        } else if (selectedValue && selectedValue !== '') {
          // Hide custom input field
          customDestinationInput.classList.add('d-none');
          customDestinationInput.required = false;
          customDestinationInput.value = '';

          // Auto-populate from possible destinations table data
          const possibleDestinationsTable = document.getElementById('possibleDestinationsTableBody');
          let foundDestination = false;

          if (possibleDestinationsTable) {
            const rows = possibleDestinationsTable.querySelectorAll('tr');
            rows.forEach(row => {
              const destinationCell = row.querySelector('td:nth-child(2)');
              if (destinationCell && destinationCell.textContent.trim() === selectedValue) {
                // Found matching destination, populate fields
                const details = row.querySelector('td:nth-child(3)')?.textContent.trim() || '';
                const objectives = row.querySelector('td:nth-child(4)')?.textContent.trim() || '';
                const duration = row.querySelector('td:nth-child(5)')?.textContent.trim() || '';
                const deliveryModeCell = row.querySelector('td:nth-child(6)');
                let deliveryMode = '';

                // Extract delivery mode from badge text
                if (deliveryModeCell) {
                  const badge = deliveryModeCell.querySelector('.badge');
                  if (badge) {
                    const badgeText = badge.textContent.trim();
                    // Remove emoji and extract text
                    deliveryMode = badgeText.replace(/^[^\w\s]+\s*/, '').trim();
                  }
                }

                // Populate form fields
                detailsTextarea.value = details;
                objectivesTextarea.value = objectives;
                durationInput.value = duration;
                deliveryModeSelect.value = deliveryMode;

                foundDestination = true;
              }
            });
          }

          // If not found in possible destinations, try API fallback
          if (!foundDestination) {
            fetch(`/admin/destination-knowledge-training/destination-details/${encodeURIComponent(selectedValue)}`)
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Auto-populate fields
                  detailsTextarea.value = data.data.details;
                  objectivesTextarea.value = data.data.objectives;
                  durationInput.value = data.data.duration;
                  deliveryModeSelect.value = data.data.delivery_mode;

                  alert('Destination details loaded from database!');
                } else {
                  alert('Please fill in the details manually.');
                }
              })
              .catch(error => {
                console.error('Error fetching destination details:', error);
                alert('Please fill in the destination details manually.');
              });
          }
        } else {
          // Clear all fields when no destination selected
          customDestinationInput.classList.add('d-none');
          customDestinationInput.required = false;
          customDestinationInput.value = '';
          detailsTextarea.value = '';
          objectivesTextarea.value = '';
          durationInput.value = '';
          deliveryModeSelect.value = '';
        }
      });
    }
  }

  // Update form submission to handle custom destination and use normal form submission
  function setupFormSubmission() {
    const addDestinationForm = document.getElementById('addDestinationForm');
    if (addDestinationForm) {
      addDestinationForm.addEventListener('submit', function(e) {
        const destinationSelect = document.getElementById('destination_name');
        const customDestinationInput = document.getElementById('custom_destination_name');

        // If custom destination is selected, use the custom input value
        if (destinationSelect && destinationSelect.value === 'custom' && customDestinationInput && customDestinationInput.value.trim()) {
          // Create a hidden input to override the destination name
          const existingHidden = this.querySelector('input[name="destination_name"][type="hidden"]');
          if (existingHidden) {
            existingHidden.remove();
          }

          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'destination_name';
          hiddenInput.value = customDestinationInput.value.trim();
          this.appendChild(hiddenInput);
        }

        // Allow normal form submission - this handles CSRF tokens properly
        return true;
      });
    }
  }

  // Add New Destination Button Handler
  function setupAddNewButton() {
    const addNewBtn = document.getElementById('addNewDestinationBtn');
    const modalElement = document.getElementById('addDestinationModal');

    if (addNewBtn && modalElement) {
      // Set up modal event listeners
      modalElement.addEventListener('shown.bs.modal', function () {
        console.log('Modal shown - setting up delivery mode handler');
        setupDeliveryModeHandler();

      });

      addNewBtn.addEventListener('click', function() {
        console.log('Add New button clicked - opening modal');
        try {
          const modal = new bootstrap.Modal(modalElement);
          modal.show();
        } catch (error) {
          console.error('Error opening modal:', error);
          // Fallback method
          modalElement.style.display = 'block';
          modalElement.classList.add('show');

          // Set up delivery mode handler for fallback too
          setTimeout(() => {
            setupDeliveryModeHandler();
          }, 100);
        }
      });
    }
  }

  // Setup delivery mode change handler for conditional fields
  function setupDeliveryModeHandler() {
    const deliveryModeSelect = document.getElementById('delivery_mode');
    const onlineTrainingFields = document.getElementById('onlineTrainingFields');
    const progressLevelField = document.getElementById('progress_level');

    console.log('Setting up delivery mode handler...');
    console.log('deliveryModeSelect:', deliveryModeSelect);
    console.log('onlineTrainingFields:', onlineTrainingFields);
    console.log('progressLevelField:', progressLevelField);

    if (deliveryModeSelect && onlineTrainingFields && progressLevelField) {
      // Remove existing event listeners to prevent duplicates
      if (window.handleDeliveryModeChange) {
        deliveryModeSelect.removeEventListener('change', window.handleDeliveryModeChange);
        deliveryModeSelect.removeEventListener('input', window.handleDeliveryModeChange);
      }

      // Define the handler function
      window.handleDeliveryModeChange = function() {
        console.log('Delivery mode changed to:', deliveryModeSelect.value);
        if (deliveryModeSelect.value === 'Online Training') {
          onlineTrainingFields.style.display = 'block';
          progressLevelField.required = true;
          console.log('Showing online training fields');
        } else {
          onlineTrainingFields.style.display = 'none';
          progressLevelField.required = false;
          progressLevelField.value = '0'; // Reset to default
          const expiredDateField = document.getElementById('expired_date');
          if (expiredDateField) {
            expiredDateField.value = ''; // Clear expired date
          }
          console.log('Hiding online training fields');
        }
      };

      // Add both change and input event listeners for better compatibility
      deliveryModeSelect.addEventListener('change', window.handleDeliveryModeChange);
      deliveryModeSelect.addEventListener('input', window.handleDeliveryModeChange);

      // Trigger immediately to check current state
      window.handleDeliveryModeChange();

      console.log('Delivery mode handler set up successfully');
    } else {
      console.error('Could not find required elements for delivery mode handler');
    }
  }

  // Setup Assign to Upcoming Training functionality
  function setupAssignToUpcomingTraining() {
    const assignButtons = document.querySelectorAll('.assign-to-upcoming-btn');

    assignButtons.forEach(button => {
      button.addEventListener('click', function() {
        const destinationId = this.getAttribute('data-destination-id');
        const employeeName = this.getAttribute('data-employee-name');
        const destinationName = this.getAttribute('data-destination-name');

        // Show password verification before proceeding
        showPasswordVerification(() => {
          // Disable button and show loading
          this.disabled = true;
          this.innerHTML = '<i class="bi bi-hourglass-split"></i> Assigning...';

          // Get CSRF token safely with multiple fallback methods
          let csrfTokenValue = null;
          const csrfToken = document.querySelector('meta[name="csrf-token"]');

          if (csrfToken) {
            csrfTokenValue = csrfToken.getAttribute('content') || csrfToken.content;
          }

          // Fallback: try to get from Laravel's global variable
          if (!csrfTokenValue && typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) {
            csrfTokenValue = window.Laravel.csrfToken;
          }

          // Fallback: try to get from form token if exists
          if (!csrfTokenValue) {
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
              csrfTokenValue = tokenInput.value;
            }
          }

          if (!csrfTokenValue) {
            console.error('CSRF token not found');
            alert('Security token not found. Please refresh the page and try again.');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-calendar-check"></i> Assign to Upcoming Training';
            return;
          }

          // Make AJAX request
          fetch('{{ route("admin.destination-knowledge-training.assign-to-upcoming") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfTokenValue,
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              destination_id: destinationId
            })
          })
          .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
          })
          .then(data => {
            console.log('Response data:', data);

            if (data.success) {
              // Show success notification
              
              // Replace button with success badge
              const parentTd = this.parentElement;
              parentTd.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved for Upcoming</span>';

              // Refresh page after 2 seconds to show updated status
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            } else {
              // Show error notification with detailed message
              const errorMessage = data.message || 'Unknown error occurred';
              console.error('Server error:', errorMessage);
              alert(errorMessage);

              // Reset button
              this.disabled = false;
              this.innerHTML = '<i class="bi bi-calendar-check"></i> Assign to Upcoming Training';
            }
          })
          .catch(error => {
            console.error('Network or parsing error:', error);
            console.error('Error details:', error.message);

            // Check if it's a CSRF token error and try to refresh
            if (error.message.includes('419') || error.message.includes('CSRF') || error.message.includes('token')) {
              console.log('CSRF token error detected, attempting to refresh token...');

              // Try to refresh CSRF token
              fetch('/csrf-refresh', {
                method: 'GET',
                headers: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
              .then(response => response.json())
              .then(data => {
                if (data.csrf_token) {
                  // Update meta tag
                  const metaTag = document.querySelector('meta[name="csrf-token"]');
                  if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                  }
                  // Update global variable
                  if (window.Laravel) {
                    window.Laravel.csrfToken = data.csrf_token;
                  }
                  alert('Security token refreshed. Please try again.');
                }
              })
              .catch(() => {
                alert('Please refresh the page and try again.');
              });
            }

            // More specific error message
            let errorMessage = 'Failed to assign training. ';
            if (error.message.includes('HTTP error')) {
              errorMessage += `Server returned ${error.message}. `;
            } else if (error.message.includes('JSON')) {
              errorMessage += 'Invalid response from server. ';
            } else if (error.message.includes('419') || error.message.includes('CSRF')) {
              errorMessage += 'Security token expired. Token has been refreshed, please try again. ';
            } else {
              errorMessage += 'Network error. ';
            }
            errorMessage += 'Please try again.';

            alert(errorMessage);

            // Reset button with dynamic text
            this.disabled = false;
            const isAlreadyAssigned = this.getAttribute('data-already-assigned') === 'true';
            this.innerHTML = isAlreadyAssigned ?
              '<i class="bi bi-calendar-check"></i> Re-assign to Upcoming' :
              '<i class="bi bi-calendar-check"></i> Assign to Upcoming Training';
          });
        });
      });
    });
  }

  // Setup filter functionality
  function setupFilters() {
    const searchInput = document.getElementById('searchInput');
    const progressFilter = document.getElementById('progressFilter');
    const dateFilter = document.getElementById('dateFilter');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const tableRows = document.querySelectorAll('table tbody tr:not(.empty-row)');

    // Check if all required elements exist
    if (!searchInput || !progressFilter || !dateFilter || !applyFiltersBtn) {
      console.log('Filter elements not found, skipping filter setup');
      return;
    }

    function applyFilters() {
      const searchTerm = searchInput.value.toLowerCase();
      const statusFilter = progressFilter.value.toLowerCase();
      const dateFilterValue = dateFilter.value;

      tableRows.forEach(row => {
        const employeeName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const destination = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';
        const createdDate = row.querySelector('td:nth-child(7)')?.textContent || '';

        let showRow = true;

        // Search filter
        if (searchTerm && !employeeName.includes(searchTerm) && !destination.includes(searchTerm)) {
          showRow = false;
        }

        // Status filter
        if (statusFilter && !status.includes(statusFilter)) {
          showRow = false;
        }

        // Date filter
        if (dateFilterValue && !createdDate.includes(dateFilterValue)) {
          showRow = false;
        }

        row.style.display = showRow ? '' : 'none';
      });
    }

    // Event listeners
    applyFiltersBtn.addEventListener('click', applyFilters);
    searchInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') applyFilters();
    });
    progressFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);

    // Real-time search
    searchInput.addEventListener('input', applyFilters);
  }

  // Setup export functionality
  function setupExportButtons() {
    const exportExcelBtn = document.getElementById('exportExcel');
    const exportPdfBtn = document.getElementById('exportPdf');

    if (exportExcelBtn) {
      exportExcelBtn.addEventListener('click', function() {
        alert('Excel export functionality coming soon...');
      });
    }

    if (exportPdfBtn) {
      exportPdfBtn.addEventListener('click', function() {
        alert('PDF export functionality coming soon...');
      });
    }
  }





  // Setup delete possible destination buttons
  function setupDeletePossibleButtons() {
    document.querySelectorAll('.delete-possible-btn').forEach(button => {
      button.addEventListener('click', function() {
        const destinationId = this.getAttribute('data-destination-id');
        const destinationName = this.getAttribute('data-destination-name');
        deletePossibleDestination(destinationId, destinationName);
      });
    });
  }

  // Check progress every 30 seconds
  setInterval(checkTrainingProgress, 30000);
  document.addEventListener('DOMContentLoaded', function() {
    checkTrainingProgress();
    attachEventListeners();
    initializePossibleDestinations();
    setupDestinationAutoPopulation();
    setupFormSubmission();
    setupAddNewButton();
    setupDeliveryModeHandler();
    setupAssignToUpcomingTraining();
    setupFilters();
    setupExportButtons();

    setupDeletePossibleButtons();
  });
</script>

</body>
</html>

      <!-- Password Verification Modal -->
      <div class="modal fade" id="passwordVerificationModal" tabindex="-1" aria-labelledby="passwordVerificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="passwordVerificationModalLabel">Verify Password</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Please enter your password to confirm this action:</p>
              <input type="password" class="form-control" id="verificationPassword" placeholder="Enter your password">
              <div id="passwordError" class="text-danger mt-2" style="display: none;">Incorrect password. Please try again.</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" id="confirmPasswordAction">Confirm Action</button>
            </div>
          </div>
        </div>
      </div>

      <script>
        let pendingAction = null;

        function confirmAction(actionType, title, message, id = null) {
          // Skip confirmation dialog and go directly to password verification
          showPasswordVerification(() => {
            executeAction(actionType, id);
          });
        }

        function confirmDelete(destinationId, destinationName) {
          confirmAction('delete-destination', 'Delete Destination?', `Are you sure you want to delete "${destinationName}"? This action cannot be undone.`, destinationId);
        }

        function confirmDeleteRecord(recordId, employeeName, destinationName) {
          confirmAction('delete-record', 'Delete Training Record?', `Are you sure you want to delete ${employeeName}'s training record for "${destinationName}"? This action cannot be undone.`, recordId);
        }

        function executeAction(actionType, id) {
          switch(actionType) {
            case 'delete-destination':
              window.location.href = `/admin/destination-knowledge-training/delete-possible/${id}`;
              break;
            case 'delete-record':
              const deleteForm = document.createElement('form');
              deleteForm.method = 'POST';
              deleteForm.action = `/admin/destination-knowledge-training/${id}`;
              deleteForm.innerHTML = '@csrf @method("DELETE")';
              document.body.appendChild(deleteForm);
              deleteForm.submit();
              break;
            case 'add-destination':
              document.getElementById('addPossibleDestinationModal') && new bootstrap.Modal(document.getElementById('addPossibleDestinationModal')).show();
              break;
            case 'edit-destination':
              document.getElementById(`editPossibleDestinationModal${id}`) && new bootstrap.Modal(document.getElementById(`editPossibleDestinationModal${id}`)).show();
              break;
            case 'edit-record':
              document.getElementById(`editDestinationModal${id}`) && new bootstrap.Modal(document.getElementById(`editDestinationModal${id}`)).show();
              break;
            case 'add-new':
              document.getElementById('addDestinationModal') && new bootstrap.Modal(document.getElementById('addDestinationModal')).show();
              break;
            case 'sync-competency':
              window.location.href = '/admin/destination-knowledge-training/sync-competency';
              break;
            case 'export-excel':
              window.location.href = '/admin/destination-knowledge-training/export-excel';
              break;
            case 'export-pdf':
              window.location.href = '/admin/destination-knowledge-training/export-pdf';
              break;
            case 'request-training':
              // Handle request training logic
              requestTrainingActivation(id);
              break;
            case 'assign-upcoming':
              // Handle assign to upcoming training logic
              assignToUpcomingTraining(id);
              break;
          }
        }

        function showPasswordVerification(callback) {
          pendingAction = callback;
          const modal = new bootstrap.Modal(document.getElementById('passwordVerificationModal'));
          document.getElementById('verificationPassword').value = '';
          document.getElementById('passwordError').style.display = 'none';
          modal.show();
        }

        document.getElementById('confirmPasswordAction').addEventListener('click', function() {
          const password = document.getElementById('verificationPassword').value;

          if (!password) {
            document.getElementById('passwordError').textContent = 'Please enter your password.';
            document.getElementById('passwordError').style.display = 'block';
            return;
          }

          fetch('/admin/verify-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ password: password })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              bootstrap.Modal.getInstance(document.getElementById('passwordVerificationModal')).hide();
              if (pendingAction) {
                pendingAction();
                pendingAction = null;
              }
            } else {
              document.getElementById('passwordError').textContent = 'Incorrect password. Please try again.';
              document.getElementById('passwordError').style.display = 'block';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            document.getElementById('passwordError').textContent = 'An error occurred. Please try again.';
            document.getElementById('passwordError').style.display = 'block';
          });
        });

        // Function to handle training request activation
        function requestTrainingActivation(destinationId) {
          // Submit the request to the controller
          fetch(`/admin/destination-knowledge-training/${destinationId}/request-activation`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert(data.message || 'Training activation request has been submitted successfully.');
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
              } else {
                // Refresh the page to show updated status
                window.location.reload();
              }
            } else {
              alert(data.message || 'Failed to submit training activation request.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the request. Please try again.');
          });
        }

        // Function to handle assign to upcoming training
        function assignToUpcomingTraining(destinationId) {
          // Submit the assignment request to the controller
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          fetch('/admin/destination-knowledge-training/assign-to-upcoming', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              destination_id: destinationId
            })
          })
          .then(async response => {
            if (!response.ok) {
              let msg = 'An error occurred while assigning the training. Please try again.';
              try {
                const data = await response.json();
                msg = data.message || msg;
              } catch (e) {}
              alert(msg);
              throw new Error(msg);
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              alert(data.message || 'Training has been assigned to upcoming training list successfully.');
              window.location.reload();
            } else {
              alert(data.message || 'Failed to assign training to upcoming training list.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while assigning the training. Please try again.');
          });
        }

        @if(session('success'))
                  @endif

        @if(session('error'))
          alert('{{ session('error') }}');
        @endif

        // Pagination functionality
        let possibleCurrentPage = 1;
        let possibleItemsPerPage = 5;
        let trainingCurrentPage = 1;
        let trainingItemsPerPage = 3;

        // Initialize pagination on page load
        document.addEventListener('DOMContentLoaded', function() {
          initializePossibleDestinationsPagination();
          initializeTrainingPagination();
        });

        // Possible Destinations Pagination
        function initializePossibleDestinationsPagination() {
          const rows = document.querySelectorAll('.possible-destination-row');
          const totalRecords = rows.length;

          if (totalRecords <= possibleItemsPerPage) {
            // Hide pagination if not needed
            const possiblePrevBtn = document.querySelector('#possiblePrevBtn');
            if (possiblePrevBtn) {
              const pagination = possiblePrevBtn.closest('.d-flex');
              if (pagination) pagination.style.display = 'none';
            }
            return;
          }

          showPossibleDestinationsPage(1);
        }

        function showPossibleDestinationsPage(page) {
          const rows = document.querySelectorAll('.possible-destination-row');
          const totalRecords = rows.length;
          const totalPages = Math.ceil(totalRecords / possibleItemsPerPage);

          // Validate page number
          if (page < 1) page = 1;
          if (page > totalPages) page = totalPages;

          possibleCurrentPage = page;

          // Calculate start and end indices
          const startIndex = (page - 1) * possibleItemsPerPage;
          const endIndex = startIndex + possibleItemsPerPage;

          // Show/hide rows
          rows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });

          // Update pagination info
          const possibleCurrentStart = document.getElementById('possibleCurrentStart');
          const possibleCurrentEnd = document.getElementById('possibleCurrentEnd');
          const possibleTotalRecords = document.getElementById('possibleTotalRecords');
          const possibleCurrentPageEl = document.getElementById('possibleCurrentPage');

          if (possibleCurrentStart) possibleCurrentStart.textContent = startIndex + 1;
          if (possibleCurrentEnd) possibleCurrentEnd.textContent = Math.min(endIndex, totalRecords);
          if (possibleTotalRecords) possibleTotalRecords.textContent = totalRecords;
          if (possibleCurrentPageEl) possibleCurrentPageEl.textContent = page;

          // Update button states
          const prevBtn = document.getElementById('possiblePrevBtn');
          const nextBtn = document.getElementById('possibleNextBtn');

          if (prevBtn) {
            if (page <= 1) {
              prevBtn.classList.add('disabled');
            } else {
              prevBtn.classList.remove('disabled');
            }
          }

          if (nextBtn) {
            if (page >= totalPages) {
              nextBtn.classList.add('disabled');
            } else {
              nextBtn.classList.remove('disabled');
            }
          }
        }

        function changePossiblePage(direction) {
          const newPage = possibleCurrentPage + direction;
          showPossibleDestinationsPage(newPage);
        }

        // Training Records Pagination
        function initializeTrainingPagination() {
          const cards = document.querySelectorAll('#destinationTableBody .col-12[data-destination-id]');
          const totalRecords = cards.length;

          if (totalRecords <= trainingItemsPerPage) {
            // Hide pagination if not needed
            const trainingPrevBtn = document.querySelector('#trainingPrevBtn');
            if (trainingPrevBtn) {
              const pagination = trainingPrevBtn.closest('.d-flex');
              if (pagination) pagination.style.display = 'none';
            }
            return;
          }

          showTrainingPage(1);
        }

        function showTrainingPage(page) {
          const cards = document.querySelectorAll('#destinationTableBody .col-12[data-destination-id]');
          const totalRecords = cards.length;
          const totalPages = Math.ceil(totalRecords / trainingItemsPerPage);

          // Validate page number
          if (page < 1) page = 1;
          if (page > totalPages) page = totalPages;

          trainingCurrentPage = page;

          // Calculate start and end indices
          const startIndex = (page - 1) * trainingItemsPerPage;
          const endIndex = startIndex + trainingItemsPerPage;

          // Show/hide cards
          cards.forEach((card, index) => {
            if (index >= startIndex && index < endIndex) {
              card.style.display = '';
            } else {
              card.style.display = 'none';
            }
          });

          // Update pagination info
          const trainingCurrentStart = document.getElementById('trainingCurrentStart');
          const trainingCurrentEnd = document.getElementById('trainingCurrentEnd');
          const trainingTotalRecords = document.getElementById('trainingTotalRecords');
          const trainingCurrentPageEl = document.getElementById('trainingCurrentPage');

          if (trainingCurrentStart) trainingCurrentStart.textContent = startIndex + 1;
          if (trainingCurrentEnd) trainingCurrentEnd.textContent = Math.min(endIndex, totalRecords);
          if (trainingTotalRecords) trainingTotalRecords.textContent = totalRecords;
          if (trainingCurrentPageEl) trainingCurrentPageEl.textContent = page;

          // Update button states
          const prevBtn = document.getElementById('trainingPrevBtn');
          const nextBtn = document.getElementById('trainingNextBtn');

          if (prevBtn) {
            if (page <= 1) {
              prevBtn.classList.add('disabled');
            } else {
              prevBtn.classList.remove('disabled');
            }
          }

          if (nextBtn) {
            if (page >= totalPages) {
              nextBtn.classList.add('disabled');
            } else {
              nextBtn.classList.remove('disabled');
            }
          }
        }

        function changeTrainingPage(direction) {
          const newPage = trainingCurrentPage + direction;
          showTrainingPage(newPage);
        }
      </script>
    </main>
  </body>
</html>
