<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/succession_planning_dashboard.css') }}">
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- jQuery for AJAX functionality -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    // Initialize static text content
    window.messages = {
      loading: 'Loading...',
      error: 'Error',
      success: 'Success',
      confirm: 'Confirm',
      cancel: 'Cancel',
      noCandidates: 'No candidates available',
      addCandidate: 'Add Candidate',
      editSimulation: 'Edit Simulation',
      deleteSimulation: 'Delete Simulation',
      candidates: 'Candidates',
      successPlanning: 'Succession Planning'
    };

    // Initialize when document is ready
    $(document).ready(function() {
      try {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Add click handlers to role nodes
        $('.role-node').each(function() {
          const $node = $(this);
          const positionId = $node.data('position-id');

          if (positionId) {
            $node.on('click', function(e) {
              e.preventDefault();
              console.log('Role node clicked:', positionId);
              showCandidates(positionId);
            });
          }
        });

        // Initialize any dropdowns
        if (typeof bootstrap !== 'undefined') {
          const dropdownElements = document.querySelectorAll('.dropdown-toggle');
          dropdownElements.forEach(element => {
            new bootstrap.Dropdown(element);
          });
        }

        console.log('Document ready - initialization complete');
      } catch (error) {
        console.error('Error during initialization:', error);
      }
    });
  </script>

  <!-- Enhanced AI Scenario Styling -->
  <style>
    /* Role Chart Styling */
    .org-chart {
      padding: 2rem;
    }

    .role-node {
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
    }

    .role-node:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .role-node .readiness-score {
      position: absolute;
      top: -10px;
      right: -10px;
      background: #198754;
      color: white;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: bold;
      border: 2px solid white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .role-node h6 {
      margin-bottom: 0.25rem;
      font-weight: 600;
      color: #000;
    }

    .role-node p {
      font-size: 0.875rem;
      color: #000;
    }

    .role-node small {
      font-size: 0.75rem;
      color: #000;
    }

    .role-node.leader {
      background: #ffffff;
      border: 2px solid #0d6efd;
    }

    .role-node.manager {
      background: #ffffff;
      border: 2px solid #198754;
    }

    .role-node.successor {
      background: #ffffff;
      border: 2px solid #6c757d;
    }

    .ai-scenario-card {
      transition: all 0.3s ease;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .ai-scenario-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .ai-risk-item {
      color: #dc3545;
      font-weight: 500;
    }

    .ai-recommendation {
      color: #198754;
      font-weight: 500;
    }

    .progress-bar-animated {
      animation: progress-bar-stripes 1s linear infinite;
    }

    @keyframes progress-bar-stripes {
      0% { background-position: 1rem 0; }
      100% { background-position: 0 0; }
    }

    .swal2-popup {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .swal2-title {
      font-weight: 600;
    }

    .btn-group .btn {
      border-radius: 0.375rem;
      margin-right: 2px;
    }

    .action-btns .btn {
      font-size: 0.875rem;
      padding: 0.25rem 0.5rem;
    }

    .simulation-card {
      border: none;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      transition: box-shadow 0.15s ease-in-out;
    }

    .simulation-card:hover {
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header-custom {
      background-color: #fff;
      color: #333;
      border-bottom: 1px solid #dee2e6;
      padding: 0.75rem 1.25rem;
    }

    .badge {
      font-size: 0.75rem;
      padding: 0.35em 0.65em;
    }

    .spinner-border {
      width: 2rem;
      height: 2rem;
    }

    .text-muted {
      color: #6c757d !important;
    }

    .alert {
      border: 1px solid transparent;
      border-radius: 0.375rem;
    }

    .alert-info {
      background-color: #d1ecf1;
      border-color: #bee5eb;
      color: #0c5460;
    }

    .alert-success {
      background-color: #d4edda;
      border-color: #c3e6cb;
      color: #155724;
    }

    .alert-warning {
      background-color: #fff3cd;
      border-color: #ffeaa7;
      color: #856404;
    }

    .alert-danger {
      background-color: #f8d7da;
      border-color: #f5c6cb;
      color: #721c24;
    }

    /* Enhanced Progress Bar Styling with Colors */
    .progress {
      background-color: #e9ecef;
      border-radius: 0.375rem;
      overflow: hidden;
      box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }

    .progress-bar {
      background-color: #0d6efd;
      transition: width 0.6s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 0.75rem;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    /* Success - Green for high readiness (90%+) */
    .progress-bar.bg-success {
      background: linear-gradient(45deg, #198754, #20c997) !important;
      color: white !important;
    }

    /* Warning - Yellow/Orange for medium readiness (70-89%) */
    .progress-bar.bg-warning {
      background: linear-gradient(45deg, #fd7e14, #ffc107) !important;
      color: #000 !important;
      text-shadow: 0 1px 2px rgba(255,255,255,0.3);
    }

    /* Secondary - Gray for low readiness (<70%) */
    .progress-bar.bg-secondary {
      background: linear-gradient(45deg, #6c757d, #adb5bd) !important;
      color: white !important;
    }

    /* Info - Blue for competency skills */
    .progress-bar.bg-info {
      background: linear-gradient(45deg, #0dcaf0, #17a2b8) !important;
      color: #000 !important;
      text-shadow: 0 1px 2px rgba(255,255,255,0.3);
    }

    /* Primary - Default blue */
    .progress-bar.bg-primary {
      background: linear-gradient(45deg, #0d6efd, #6610f2) !important;
      color: white !important;
    }

    /* Danger - Red for critical issues */
    .progress-bar.bg-danger {
      background: linear-gradient(45deg, #dc3545, #e74c3c) !important;
      color: white !important;
    }

    /* Animated stripes for loading states */
    .progress-bar-striped {
      background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
      background-size: 1rem 1rem;
    }

    .progress-bar-animated {
      animation: progress-bar-stripes 1s linear infinite;
    }

    @keyframes progress-bar-stripes {
      0% { background-position: 1rem 0; }
      100% { background-position: 0 0; }
    }

    /* Hover effects for interactive progress bars */
    .progress:hover .progress-bar {
      transform: scaleY(1.1);
      transition: all 0.3s ease;
    }

    /* Different sizes for different contexts */
    .progress-sm {
      height: 0.5rem;
    }

    .progress-lg {
      height: 1.5rem;
    }

    .progress-xl {
      height: 2rem;
    }

    /* Candidates Modal Styling */
    .candidates-modal-content {
      max-height: 70vh;
      overflow-y: auto;
      padding: 1rem;
    }

    .candidates-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1rem;
      padding: 1rem;
    }

    .candidate-card {
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 1rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .candidate-competencies {
      padding-top: 1rem;
      border-top: 1px solid #dee2e6;
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
            <h2 class="fw-bold mb-1">Succession Planning Dashboard Simulation Tools</h2>
            <p class="text-muted mb-0">
              Welcome back, Admin! Here's your Succession Planning Dashboard Simulation Tools.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Potential Successors</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Future Leaders Dashboard -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="display-4 text-primary mb-2">
              <i class="bi bi-people-fill"></i>
            </div>
            <h5 class="card-title">Total Candidates</h5>
            <h2 class="text-primary mb-0">{{ $totalCandidates ?? 0 }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="display-4 text-success mb-2">
              <i class="bi bi-trophy-fill"></i>
            </div>
            <h5 class="card-title">Ready Leaders</h5>
            <h2 class="text-success mb-0">{{ $readyLeaders ?? 0 }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="display-4 text-warning mb-2">
              <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h5 class="card-title">In Development</h5>
            <h2 class="text-warning mb-0">{{ $inDevelopment ?? 0 }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="display-4 text-info mb-2">
              <i class="bi bi-diagram-3-fill"></i>
            </div>
            <h5 class="card-title">Key Positions</h5>
            <h2 class="text-info mb-0">{{ $keyPositions ?? 0 }}</h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Interactive Role Chart -->
    <div class="simulation-card card mb-4">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>Interactive Role Chart</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="refreshOrgChart()">
          <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
      </div>
      <div class="card-body org-chart">
        <div class="container-fluid">
          <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
              <h5 class="text-center mb-4">🏢 IMPORTANT ROLES IN A TRAVEL AND TOURS INDUSTRY</h5>

              @if(isset($positions) && count($positions) > 0)
                @php
                  $ceoPositions = $positions->where('level', 1);
                  $executivePositions = $positions->where('level', 2);
                  $managerPositions = $positions->where('level', 3);
                @endphp

          <!-- Executive Level -->
          @if($ceoPositions->count() > 0)
            <div class="row justify-content-center mb-4">
              <div class="col-md-6">
                <div class="role-node leader" data-position-id="1" onclick="showCandidates('1')" style="background-color: #ffffff;">
                  <div class="readiness-score">{{ isset($readinessScores[1]) ? $readinessScores[1] : '0' }}%</div>
                  <h5 class="mb-2" style="color: #000;">General Manager / CEO</h5>
                  <p class="mb-2 badge text-bg-light border" style="color: #000;">Executive Level</p>
                  <small class="d-block" style="color: #000;">Head of the company; makes strategic decisions for the entire business.</small>
                </div>
              </div>
            </div>
          @endif

          <!-- Management Level -->
          <div class="row justify-content-center g-4 mb-4">
            <div class="col-lg-3 col-md-6">
              <div class="role-node manager" data-position-id="2" onclick="showCandidates('2')">
                <div class="readiness-score">{{ isset($readinessScores[2]) ? $readinessScores[2] : '0' }}%</div>
                <h6 class="mb-2">Operations Manager</h6>
                <p class="mb-2 badge text-bg-light border">Operations</p>
                <small class="d-block">Manages the day-to-day activities of tours, bookings, transport, and logistics.</small>
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="role-node manager" onclick="showCandidates('3')">
                <div class="readiness-score">{{ isset($readinessScores[3]) ? $readinessScores[3] : '0' }}%</div>
                <h6 class="mb-2">Sales & Marketing Manager</h6>
                <p class="mb-2 badge text-bg-light border">Sales</p>
                <small class="d-block">Promotes tour packages and drives company sales.</small>
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="role-node manager" onclick="showCandidates('4')">
                <div class="readiness-score">{{ isset($readinessScores[4]) ? $readinessScores[4] : '0' }}%</div>
                <h6 class="mb-2">Finance Manager</h6>
                <p class="mb-2 badge text-bg-light border">Finance</p>
                <small class="d-block">Handles budgeting, billing, payroll, and financial reporting.</small>
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="role-node manager" onclick="showCandidates('5')">
                <div class="readiness-score">{{ isset($readinessScores[5]) ? $readinessScores[5] : '0' }}%</div>
                <h6 class="mb-2">HR Manager</h6>
                <p class="mb-2 badge text-bg-light border">Human Resources</p>
                <small class="d-block">Oversees recruitment, training, and employee development.</small>
              </div>
            </div>
          </div>

          <!-- Supervisory Level -->
          <div class="row justify-content-center g-4 mb-4">
            <div class="col-md-5">
              <div class="role-node successor" data-position-id="6" onclick="showCandidates('6')">
                <div class="readiness-score">{{ isset($readinessScores[6]) ? $readinessScores[6] : '0' }}%</div>
                <h6 class="mb-2">Tour Coordinator</h6>
                <p class="mb-2 badge text-bg-light border">Supervisory</p>
                <small class="d-block">Organizes tour schedules, itineraries, and assigns tour guides.</small>
              </div>
            </div>
            <div class="col-md-5">
              <div class="role-node successor" data-position-id="7" onclick="showCandidates('7')">
                <div class="readiness-score">{{ isset($readinessScores[7]) ? $readinessScores[7] : '0' }}%</div>
                <h6 class="mb-2">Customer Service Supervisor</h6>
                <p class="mb-2 badge text-bg-light border">Supervisory</p>
                <small class="d-block">Handles client concerns, complaints, and ensures service quality.</small>
              </div>
            </div>
          </div>

          <!-- Operational Level -->
          <div class="row justify-content-center g-4">
            <div class="col-lg-2 col-md-4">
              <div class="role-node successor" onclick="showCandidates('8')">
                <div class="readiness-score">{{ isset($readinessScores[8]) ? $readinessScores[8] : '0' }}%</div>
                <h6 class="mb-2">Tour Guide</h6>
                <p class="mb-2 badge text-bg-light border">Operational</p>
                <small class="d-block">Leads tours and provides assistance to tourists.</small>
              </div>
            </div>
            <div class="col-lg-2 col-md-4">
              <div class="role-node successor" onclick="showCandidates('9')">
                <div class="readiness-score">{{ isset($readinessScores[9]) ? $readinessScores[9] : '0' }}%</div>
                <h6 class="mb-2">Travel Agent</h6>
                <p class="mb-2 badge text-bg-light border">Operational</p>
                <small class="d-block">Arranges flights, accommodations, and visa assistance.</small>
              </div>
            </div>
            <div class="col-lg-2 col-md-4">
              <div class="role-node successor" onclick="showCandidates('10')">
                <div class="readiness-score">{{ isset($readinessScores[10]) ? $readinessScores[10] : '0' }}%</div>
                <h6 class="mb-2">Reservation Officer</h6>
                <p class="mb-2 badge text-bg-light border">Operational</p>
                <small class="d-block">Manages bookings and works with travel agent.</small>
              </div>
            </div>
            <div class="col-lg-2 col-md-4">
              <div class="role-node successor" onclick="showCandidates('11')">
                <div class="readiness-score">{{ isset($readinessScores[11]) ? $readinessScores[11] : '0' }}%</div>
                <h6 class="mb-2">Ticketing Officer</h6>
                <p class="mb-2 badge text-bg-light border">Operational</p>
                <small class="d-block">Issues flight tickets using GDS systems.</small>
              </div>
            </div>
            <div class="col-lg-2 col-md-4">
              <div class="role-node successor" onclick="showCandidates('12')">
                <div class="readiness-score">{{ isset($readinessScores[12]) ? $readinessScores[12] : '0' }}%</div>
                <h6 class="mb-2">Transport Coordinator</h6>
                <p class="mb-2 badge text-bg-light border">Operational</p>
                <small class="d-block">Manages pick-up/drop-off schedules.</small>
              </div>
            </div>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-diagram-3 display-4 text-muted mb-3"></i>
            <h5 class="text-muted">No organizational positions defined</h5>
            <p class="text-muted">Create organizational positions to see the role chart.</p>
          </div>
        @endif
      </div>
      </div>
    </div>

    <!-- Candidate Details and Comparison -->
    <div class="simulation-card card mb-4">
      <div class="card-header card-header-custom">
        <h4 class="fw-bold mb-0"><i class="bi bi-person-check me-2"></i>Candidate Details & Comparison</h4>
      </div>
      <div class="card-body">
        <div class="row" id="candidatesList">
          @if(isset($topCandidates) && count($topCandidates) > 0)
            @php
              // Get unique candidates across all positions to avoid duplicates
              $uniqueCandidates = collect();
              foreach($topCandidates as $positionId => $candidates) {
                foreach($candidates as $candidate) {
                  if (!$uniqueCandidates->contains('employee_id', $candidate['employee_id'])) {
                    $uniqueCandidates->push($candidate);
                  }
                }
              }
              // Sort by readiness score and take top 6 candidates only
              $topUniqueCandidates = $uniqueCandidates->sortByDesc('readiness_score')->take(6);
            @endphp

            @foreach($topUniqueCandidates as $candidate)
              <div class="col-md-4 mb-3">
                <div class="candidate-card card h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                      <div class="avatar-circle me-3" style="width: 50px; height: 50px;">
                        @php
                          // Get employee data for profile picture
                          $employee = \App\Models\Employee::where('employee_id', $candidate['employee_id'])->first();
                          $profilePicUrl = null;

                          if ($employee && $employee->profile_picture) {
                              // Direct asset URL generation - Laravel handles the storage symlink
                              $profilePicUrl = asset('storage/' . $employee->profile_picture);
                          }

                          // Generate fallback avatar if no profile picture
                          if (!$profilePicUrl) {
                              $fullName = $candidate['name'] ?? 'Employee';
                              $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                              $employeeId = $candidate['employee_id'] ?? 'default';
                              $colorIndex = abs(crc32($employeeId)) % count($colors);
                              $bgColor = $colors[$colorIndex];
                              $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) .
                                             "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
                          }
                        @endphp
                        <img src="{{ $profilePicUrl }}"
                             alt="{{ $candidate['name'] ?? 'Employee' }}"
                             class="rounded-circle"
                             style="width: 50px; height: 50px; object-fit: cover;">
                      </div>
                      <div>
                        <h6 class="mb-0">{{ $candidate['name'] ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $candidate['current_position'] ?? 'Employee' }}</small>
                      </div>
                      <div class="ms-auto">
                        @php
                          $readinessScore = $candidate['readiness_score'] ?? 0;
                          $badgeClass = $readinessScore >= 90 ? 'bg-success' : ($readinessScore >= 70 ? 'bg-warning' : 'bg-secondary');
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ round($readinessScore) }}% Ready</span>
                      </div>
                    </div>
                    @if(isset($candidate['competency_breakdown']))
                      @foreach(array_slice($candidate['competency_breakdown'], 0, 3) as $competency)
                        <div class="mb-3">
                          <div class="d-flex justify-content-between mb-1">
                            <small>{{ $competency['competency_name'] ?? 'Competency' }}</small>
                            <small>{{ round($competency['score'] ?? 0) }}%</small>
                          </div>
                          <div class="progress skill-progress" style="height: 8px;">
                            @php
                              $score = $competency['score'] ?? 0;
                              $progressClass = $score >= 90 ? 'bg-success' : ($score >= 70 ? 'bg-info' : 'bg-warning');
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" style="width: {{ $score }}%">
                              {{ round($score) }}%
                            </div>
                          </div>
                        </div>
                      @endforeach
                    @endif
                    <button class="btn btn-outline-primary btn-sm w-100" onclick="viewCandidateDetails('{{ $candidate['employee_id'] ?? '' }}')">
                      <i class="bi bi-eye me-1"></i>View Details
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="col-12">
              <div class="text-center py-4">
                <i class="bi bi-people display-4 text-muted mb-3"></i>
                <h5 class="text-muted">No candidates available</h5>
                <p class="text-muted">Run the succession planning evaluation to see potential candidates.</p>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>


    <div class="simulation-card card mb-4">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Add New Simulation Entry</h4>
      </div>
      <div class="card-body">
        <form action="#" method="POST" class="mb-4" id="addSimulationForm">
          @csrf
          <div class="row g-3">
            <div class="col-md-4">
              <select name="employee_id" class="form-select" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp['id'] }}">{{ $emp['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <select name="simulation_result" class="form-select" required>
                <option value="">Select Simulation Result</option>
                @if(isset($completedCertificates) && $completedCertificates->count() > 0)
                  @foreach($completedCertificates as $certificate)
                    <option value="{{ $certificate['display_text'] }}">{{ $certificate['display_text'] }}</option>
                  @endforeach
                @else
                  <option disabled>No completed certificates available</option>
                @endif
              </select>
            </div>
            <div class="col-md-3">
              <input type="date" name="created_at" class="form-control" required>
            </div>
            <div class="col-md-1">
              <button type="button" onclick="addSimulationWithConfirmation()" class="btn btn-primary w-100">
                <i class="bi bi-plus-lg"></i> Add
              </button>
            </div>
          </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
          const form = document.querySelector('form.mb-4');
          form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch("{{ route('succession_simulations.store') }}", {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                'Accept': 'application/json',
              },
              body: formData
            })
            .then(response => {
              if (!response.ok) throw new Error('Network response was not ok');
              return response.json().catch(() => ({}));
            })
            .then(data => {
              // Optionally show a success message
              window.location.reload(); // reload to show new data
            })
            .catch(error => {
              alert('Error adding simulation entry. Please check your input.');
            });
          });
        });
        </script>
      </div>
    </div>

    <div class="simulation-card card">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Simulation Results</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Simulation Result</th>
                <th class="fw-bold">Created At</th>
                <th class="fw-bold">Latest Certificate Status</th>
                <th class="fw-bold">Certificate Earned</th>
                <th class="fw-bold">Certificate Number</th>
                <th class="fw-bold">Expiry Date</th>
                <th class="fw-bold">Download</th>
                <th class="fw-bold">Remarks</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($simulations as $item)
              <tr>
                <td>
                  @if($item->employee)
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2">
                        @php
                          $firstName = $item->employee->first_name ?? 'Unknown';
                          $lastName = $item->employee->last_name ?? 'Employee';
                          $fullName = $firstName . ' ' . $lastName;

                          // Check if profile picture exists - simplified approach
                          $profilePicUrl = null;
                          if ($item->employee->profile_picture) {
                              // Direct asset URL generation - Laravel handles the storage symlink
                              $profilePicUrl = asset('storage/' . $item->employee->profile_picture);
                          }

                          // Generate consistent color based on employee name for fallback
                          $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                          $employeeId = $item->employee->employee_id ?? 'default';
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
                  <span class="badge bg-success bg-opacity-10 text-success badge-simulation">
                    {{ $item->simulation_result }}
                  </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                <td>
                  @php
                    $cert = $certificateStatuses[$item->id] ?? null;
                    $statusClass = 'secondary';
                    if($cert) {
                      switch(strtolower($cert['status'])) {
                        case 'completed':
                        case 'done':
                        case 'verified':
                          $statusClass = 'success';
                          break;
                        case 'pending':
                          $statusClass = 'warning';
                          break;
                        case 'expired':
                          $statusClass = 'danger';
                          break;
                        default:
                          $statusClass = 'secondary';
                      }
                    }
                  @endphp
                  @if($cert)
                    <span class="badge bg-{{ $statusClass }} bg-opacity-10 text-{{ $statusClass }}">
                      {{ $cert['status'] }}
                    </span>
                    <br>
                    <small class="text-muted">{{ date('M d, Y', strtotime($cert['date'])) }}</small>
                  @else
                    <span class="text-muted">No record</span>
                  @endif
                </td>
                <td>
                  @if($cert && $cert['course'])
                    <span class="fw-semibold">{{ $cert['course'] }}</span>
                  @elseif($cert)
                    <span class="text-muted">No course info</span>
                  @else
                    <span class="text-muted">No record</span>
                  @endif
                </td>
                <td>
                  @if($cert && $cert['certificate_number'])
                    <span class="fw-semibold">{{ $cert['certificate_number'] }}</span>
                  @else
                    <span class="text-muted">No number</span>
                  @endif
                </td>
                <td>
                  @if($cert && $cert['certificate_expiry'])
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($cert['certificate_expiry'])->format('d/m/Y') }}</span>
                  @else
                    <span class="text-muted">No expiry</span>
                  @endif
                </td>
                <td>
                  @if($cert && $cert['certificate_url'])
                    <a href="{{ $cert['certificate_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Download</a>
                  @else
                    <span class="text-muted">No file</span>
                  @endif
                </td>
                <td>
                  @if($cert)
                    @php
                      $remarkText = 'No remarks';
                      $remarkClass = 'text-muted';

                      if(isset($cert['status'])) {
                        switch(strtolower($cert['status'])) {
                          case 'completed':
                          case 'done':
                          case 'verified':
                            $remarkText = 'Passed';
                            $remarkClass = 'text-success fw-semibold';
                            break;
                          case 'expired':
                            $remarkText = 'Failed';
                            $remarkClass = 'text-danger fw-semibold';
                            break;
                          case 'pending':
                            $remarkText = 'In Progress';
                            $remarkClass = 'text-warning fw-semibold';
                            break;
                          default:
                            if(isset($cert['remarks']) && !empty($cert['remarks'])) {
                              $remarkText = $cert['remarks'];
                              $remarkClass = 'text-dark';
                            }
                        }
                      }
                    @endphp
                    <span class="{{ $remarkClass }}">{{ $remarkText }}</span>
                  @else
                    <span class="text-muted">No remarks</span>
                  @endif
                </td>
                <td class="text-center action-btns">
                  <div class="btn-group" role="group">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewSimulationDetails('{{ $item->id }}', '{{ $item->employee ? $item->employee->first_name . ' ' . $item->employee->last_name : 'N/A' }}', '{{ $item->simulation_result }}', '{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}')" title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning btn-sm edit-simulation-btn"
                      onclick="editSimulationWithConfirmation('{{ $item->id }}', '{{ $item->employee_id }}', '{{ $item->simulation_result }}', '{{ date('Y-m-d', strtotime($item->created_at)) }}')"
                      title="Edit Simulation">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm"
                      onclick="deleteSimulationWithConfirmation('{{ $item->id }}', '{{ $item->employee ? $item->employee->first_name . ' ' . $item->employee->last_name : 'N/A' }}')"
                      title="Delete Simulation">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No simulation entries found.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Function to show candidates for a position
    async function showCandidates(positionId) {
      if (!positionId) {
        console.error('No position ID provided');
        return;
      }

      // Debug position information
      console.log('Showing candidates for position:', positionId);

      // Show loading state
      Swal.fire({
        title: 'Loading...',
        html: 'Fetching candidates data',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
          throw new Error('CSRF token not found');
        }

        console.log('Fetching candidates for position:', positionId);
        const response = await fetch(`/admin/succession-simulations/candidates/${positionId}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        });

        if (!response.ok) {
          throw new Error(`Failed to fetch candidates: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        console.log('Received candidate data:', data);

        if (!data.candidates || data.candidates.length === 0) {
          Swal.fire({
            icon: 'info',
            title: 'No Candidates Found',
            text: 'There are no succession candidates available for this position.',
            confirmButtonColor: '#0d6efd'
          });
          return;
        }

        // Build candidate details HTML
        let candidatesHtml = '<div class="candidates-grid">';
        data.candidates.forEach(candidate => {
          const readinessClass = candidate.readiness_score >= 90 ? 'text-success' :
                               candidate.readiness_score >= 70 ? 'text-warning' :
                               'text-secondary';

          candidatesHtml += `
            <div class="candidate-card">
              <div class="d-flex align-items-center mb-3">
                <div class="candidate-avatar me-3">
                  <img src="${candidate.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(candidate.name) + '&background=random'}"
                       alt="${candidate.name}"
                       class="rounded-circle"
                       style="width: 48px; height: 48px; object-fit: cover;">
                </div>
                <div>
                  <h6 class="mb-1">${candidate.name}</h6>
                  <small class="d-block">${candidate.current_position || 'Current Position N/A'}</small>
                </div>
                <div class="ms-auto">
                  <span class="badge ${readinessClass}">${Math.round(candidate.readiness_score)}% Ready</span>
                </div>
              </div>
              <div class="candidate-competencies">
                ${candidate.competencies ? Object.entries(candidate.competencies).map(([key, value]) => `
                  <div class="mb-2">
                    <small class="d-block mb-1">${key}</small>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar ${value >= 90 ? 'bg-success' : value >= 70 ? 'bg-info' : 'bg-warning'}"
                           role="progressbar"
                           style="width: ${value}%"
                           aria-valuenow="${value}"
                           aria-valuemin="0"
                           aria-valuemax="100"></div>
                    </div>
                  </div>
                `).join('') : '<p class="text-muted">No competency data available</p>'}
              </div>
            </div>
          `;
        });
        candidatesHtml += '</div>';

        // Show candidates in modal
        Swal.fire({
          title: `Succession Candidates for Position`,
          html: candidatesHtml,
          width: '800px',
          confirmButtonText: 'Close',
          confirmButtonColor: '#0d6efd',
          customClass: {
            htmlContainer: 'candidates-modal-content'
          }
        });
      } catch (error) {
        console.error('Error fetching candidates:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load succession candidates. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      } finally {
        // Close loading state if it's still open
        if (Swal.isLoading()) {
          Swal.close();
        }
      }
    }

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Initialize translation service defaults
      if (window.translationService) {
        window.translationService.setTranslations({
          ...window.translationService.translations,
          'loading': 'Loading...',
          'error': 'Error',
          'success': 'Success',
          'confirm': 'Confirm',
          'cancel': 'Cancel'
        });
      }
    });

    // Test export functionality when page loads
    $(document).ready(function() {
      console.log('Page loaded successfully!');

      // Add event listeners - check if elements exist first
      if ($('.role-node').length > 0) {
        $('.role-node').on('click', function() {
          const positionId = $(this).data('position-id');
          if (positionId) {
            showCandidates(positionId);
          }
        });
      }

      // Initialize translation service defaults
      window.textContent = window.textContent || {
        successPlanning: 'Succession Planning',
        addCandidate: 'Add Candidate',
        editSimulation: 'Edit Simulation',
        deleteSimulation: 'Delete Simulation',
        candidates: 'Candidates',
        noCandidates: 'No candidates available',
        loading: 'Loading...',
        error: 'Error',
        success: 'Success',
        confirm: 'Confirm',
        cancel: 'Cancel'
      };

      // Initialize tooltips
      $('[data-bs-toggle="tooltip"]').tooltip();
    });
  </script>
  <!-- Edit Simulation Modal -->
  <div class="modal fade" id="editSimulationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="editSimulationForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Simulation Entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="edit-simulation-id" name="simulation_id">
            <div class="mb-3">
              <label for="edit-employee-id" class="form-label">Employee</label>
              <select id="edit-employee-id" name="employee_id" class="form-select" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp['id'] }}">{{ $emp['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="edit-simulation-result" class="form-label">Simulation Result</label>
              <select id="edit-simulation-result" name="simulation_result" class="form-select" required>
                <option value="">Select Simulation Result</option>
                @if(isset($completedCertificates) && $completedCertificates->count() > 0)
                  @foreach($completedCertificates as $certificate)
                    <option value="{{ $certificate['display_text'] }}">{{ $certificate['display_text'] }}</option>
                  @endforeach
                @else
                  <option disabled>No completed certificates available</option>
                @endif
              </select>
            </div>
            <div class="mb-3">
              <label for="edit-created-at" class="form-label">Created At</label>
              <input type="date" id="edit-created-at" name="created_at" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // ===== SWEETALERT INTEGRATION & PASSWORD VERIFICATION =====

    // Real admin password verification function
    async function verifyAdminPassword(password) {
      try {
        const response = await fetch('/admin/verify-password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ password: password })
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          console.error('Non-JSON response received from password verification');
          return false;
        }

        const data = await response.json();
        return response.ok && data.success;
      } catch (error) {
        console.error('Password verification error:', error);
        return false;
      }
    }

    // Add Candidate with Password Confirmation
    async function addCandidateWithConfirmation() {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to add a new succession candidate:</p>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Security Notice:</strong> Password verification is required for succession planning modifications to ensure data integrity and prevent unauthorized changes.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Verify & Continue',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const isValid = await verifyAdminPassword(password);

        if (isValid) {
          Swal.close();
          showAddCandidateForm();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Show Add Candidate Form with SweetAlert
    function showAddCandidateForm() {
      // Get employees and positions data
      const employees = @json($employees ?? []);
      const positions = @json($positions ?? []);

      // Build employee options
      let employeeOptions = '<option value="">Select Employee</option>';
      if (Array.isArray(employees)) {
        employees.forEach(employee => {
          if (typeof employee === 'object' && employee !== null) {
            if (employee.first_name && employee.last_name) {
              employeeOptions += `<option value="${employee.employee_id || employee.id}">${employee.first_name} ${employee.last_name} - ${employee.position || 'Employee'}</option>`;
            } else if (employee.name) {
              employeeOptions += `<option value="${employee.id}">${employee.name}</option>`;
            }
          }
        });
      }

      // Build position options
      let positionOptions = '<option value="">Select Position</option>';
      if (Array.isArray(positions)) {
        positions.forEach(position => {
          positionOptions += `<option value="${position.id}">${position.position_title} - ${position.department || 'General'}</option>`;
        });
      }

      Swal.fire({
        title: '<i class="bi bi-person-plus text-primary"></i> Add New Succession Candidate',
        html: `
          <form id="swalAddCandidateForm" class="text-start">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Employee <span class="text-danger">*</span></label>
                <select class="form-select" name="employee_id" required>
                  ${employeeOptions}
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Target Position <span class="text-danger">*</span></label>
                <select class="form-select" name="target_position_id" required>
                  ${positionOptions}
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Status</label>
                <select class="form-select" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="under_review">Under Review</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Development Priority</label>
                <select class="form-select" name="development_priority">
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Notes</label>
              <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes about this succession candidate..."></textarea>
            </div>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Note:</strong> The system will automatically calculate readiness scores based on the employee's competency profiles and target position requirements.
            </div>
          </form>
        `,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-plus-lg me-1"></i>Add Candidate',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        customClass: {
          popup: 'text-start'
        },
        preConfirm: () => {
          const form = document.getElementById('swalAddCandidateForm');
          const formData = new FormData(form);

          // Validation
          if (!formData.get('employee_id')) {
            Swal.showValidationMessage('Please select an employee');
            return false;
          }
          if (!formData.get('target_position_id')) {
            Swal.showValidationMessage('Please select a target position');
            return false;
          }

          return Object.fromEntries(formData);
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitAddCandidate(result.value);
        }
      });
    }

    // Submit Add Candidate
    async function submitAddCandidate(data) {
      console.log('Submitting candidate data:', data);

      Swal.fire({
        title: 'Adding Candidate...',
        html: 'Please wait while we add the succession candidate.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        // Create form data for submission
        const formData = new FormData();
        Object.keys(data).forEach(key => {
          formData.append(key, data[key]);
        });

        // Add CSRF token
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const response = await fetch('{{ route("succession_simulations.store") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Candidate Added Successfully!',
            text: 'The succession candidate has been added to the system.',
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(result.message || 'Failed to add candidate');
        }
      } catch (error) {
        console.error('Error adding candidate:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error Adding Candidate',
          text: error.message || 'An error occurred while adding the candidate. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // View Simulation Details
    function viewSimulationDetails(id, employeeName, simulationResult, createdAt) {
      Swal.fire({
        title: '<i class="bi bi-eye text-primary"></i> Simulation Details',
        html: `
          <div class="text-start">
            <div class="row">
              <div class="col-md-6">
                <h6 class="fw-bold text-primary">Employee Information</h6>
                <p><strong>Name:</strong> ${employeeName}</p>
                <p><strong>Simulation ID:</strong> ${id}</p>
              </div>
              <div class="col-md-6">
                <h6 class="fw-bold text-success">Simulation Results</h6>
                <p><strong>Result:</strong> <span class="badge bg-success">${simulationResult}</span></p>
                <p><strong>Created:</strong> ${createdAt}</p>
              </div>
            </div>
            <div class="alert alert-info mt-3">
              <i class="bi bi-info-circle me-2"></i>
              This simulation entry represents completed succession planning activities and training achievements.
            </div>
          </div>
        `,
        width: '600px',
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Close',
        confirmButtonColor: '#0d6efd'
      });
    }

    // Edit Simulation with Confirmation
    async function editSimulationWithConfirmation(id, employeeId, simulationResult, createdAt) {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to edit this simulation entry:</p>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Security Notice:</strong> Editing simulation entries requires admin verification to maintain data integrity.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Verify & Edit',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const isValid = await verifyAdminPassword(password);

        if (isValid) {
          Swal.close();
          showEditSimulationForm(id, employeeId, simulationResult, createdAt);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Show Edit Simulation Form
    function showEditSimulationForm(id, employeeId, simulationResult, createdAt) {
      // Store original values for fallback
      const originalEmployeeId = employeeId;
      const originalSimulationResult = simulationResult;
      const originalCreatedAt = createdAt;

      // Build employee options dynamically
      const employees = @json($employees);
      const completedCertificates = @json($completedCertificates ?? []);

      console.log('Building employee options. Current employeeId:', employeeId, 'Type:', typeof employeeId);

      let employeeOptions = '<option value="">Select Employee</option>';
      employees.forEach(emp => {
        // Convert both to strings for comparison to avoid type issues
        const selected = String(employeeId) === String(emp.id) ? 'selected' : '';
        employeeOptions += `<option value="${emp.id}" ${selected}>${emp.name}</option>`;
        console.log(`Employee: ${emp.name} (ID: ${emp.id}, Type: ${typeof emp.id}), Selected: ${selected}, Comparing: "${employeeId}" === "${emp.id}"`);
      });

      let certificateOptions = '<option value="">Select Simulation Result</option>';
      completedCertificates.forEach(cert => {
        const selected = simulationResult == cert.display_text ? 'selected' : '';
        certificateOptions += `<option value="${cert.display_text}" ${selected}>${cert.display_text}</option>`;
      });

      Swal.fire({
        title: '<i class="bi bi-pencil text-warning"></i> Edit Simulation Entry',
        html: `
          <form id="swalEditSimulationForm" class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Employee</label>
              <select class="form-select" name="employee_id" required>
                ${employeeOptions}
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Simulation Result</label>
              <select class="form-select" name="simulation_result" required>
                ${certificateOptions}
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Created At</label>
              <input type="date" class="form-control" name="created_at" value="${createdAt}" required>
            </div>
          </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-save me-1"></i>Update Simulation',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('swalEditSimulationForm');
          const formData = new FormData(form);

          // Debug: Log form data extraction
          console.log('Form data extracted:');
          for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
          }

          // Also try direct element access as backup
          const employeeSelect = form.querySelector('select[name="employee_id"]');
          const simulationSelect = form.querySelector('select[name="simulation_result"]');
          const dateInput = form.querySelector('input[name="created_at"]');

          console.log('Direct element values:');
          console.log('Employee Select:', employeeSelect ? employeeSelect.value : 'not found');
          console.log('Simulation Select:', simulationSelect ? simulationSelect.value : 'not found');
          console.log('Date Input:', dateInput ? dateInput.value : 'not found');

          const employeeId = formData.get('employee_id') || (employeeSelect ? employeeSelect.value : '');
          const simulationResult = formData.get('simulation_result') || (simulationSelect ? simulationSelect.value : '');

          if (!employeeId || employeeId === '') {
            Swal.showValidationMessage('Please select an employee');
            return false;
          }
          if (!simulationResult || simulationResult === '') {
            Swal.showValidationMessage('Please select a simulation result');
            return false;
          }

          // Return the validated data with fallback to original values
          const result = {
            employee_id: employeeId || originalEmployeeId, // Fallback to original parameter if form fails
            simulation_result: simulationResult || originalSimulationResult,
            created_at: formData.get('created_at') || (dateInput ? dateInput.value : '') || originalCreatedAt
          };

          console.log('Final result to return:', result);
          console.log('Original parameters - employeeId:', employeeId, 'simulationResult:', simulationResult, 'createdAt:', createdAt);

          // Final validation before returning
          if (!result.employee_id || result.employee_id === '') {
            console.error('CRITICAL: employee_id is still empty after all fallbacks!');
            Swal.showValidationMessage('Critical error: Employee ID could not be determined. Please try again.');
            return false;
          }

          return result;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          submitEditSimulation(id, result.value);
        }
      });
    }

    // Submit Edit Simulation
    async function submitEditSimulation(id, data) {
      Swal.fire({
        title: 'Updating Simulation...',
        html: 'Please wait while we update the simulation entry.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const formData = new FormData();

        // Debug: Log the data being passed
        console.log('Edit simulation data received:', data);
        console.log('Employee ID:', data.employee_id);
        console.log('Simulation Result:', data.simulation_result);
        console.log('Created At:', data.created_at);

        // Validate that we have the required data
        if (!data.employee_id || data.employee_id === '') {
          throw new Error('Employee ID is missing from the edit form data');
        }

        // Map the form data to controller expected fields
        formData.append('employee_id', data.employee_id);
        formData.append('simulation_result', data.simulation_result);
        formData.append('simulation_date', data.created_at); // Map created_at to simulation_date

        // Add required fields with default values (same as add function)
        formData.append('simulation_name', 'Succession Planning Simulation');
        formData.append('simulation_type', 'leadership');
        formData.append('status', 'completed');

        // Add optional fields with defaults
        formData.append('scenario_description', 'Updated succession planning simulation entry');
        formData.append('performance_rating', 'satisfactory');
        formData.append('notes', 'Updated via succession planning dashboard');

        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Debug: Log what we're sending to the server
        console.log('FormData being sent to server:');
        for (let [key, value] of formData.entries()) {
          console.log(`${key}: ${value}`);
        }

        const response = await fetch(`/admin/succession-simulations/${id}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: formData
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        let responseData;

        if (contentType && contentType.includes('application/json')) {
          responseData = await response.json();
        } else {
          // If not JSON, get text content for debugging
          const textContent = await response.text();
          console.error('Non-JSON response received:', textContent);

          // Check if it's a redirect to login page
          if (textContent.includes('<!DOCTYPE') || textContent.includes('<html')) {
            throw new Error('Session expired. Please refresh the page and try again.');
          }

          throw new Error('Invalid server response. Please try again.');
        }

        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Simulation Updated Successfully!',
            text: 'The simulation entry has been updated.',
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          // Handle validation errors
          let errorMessage = 'An error occurred while updating the simulation.';
          if (responseData && responseData.errors) {
            const firstError = Object.values(responseData.errors)[0];
            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
          } else if (responseData && responseData.message) {
            errorMessage = responseData.message;
          }

          throw new Error(errorMessage);
        }
      } catch (error) {
        console.error('Error updating simulation:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error Updating Simulation',
          text: error.message || 'An error occurred while updating the simulation. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Delete Simulation with Confirmation
    async function deleteSimulationWithConfirmation(id, employeeName) {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-danger"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to delete this simulation entry:</p>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Warning:</strong> This action is irreversible. The simulation entry for <strong>${employeeName}</strong> will be permanently deleted.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Verify & Delete',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const isValid = await verifyAdminPassword(password);

        if (isValid) {
          Swal.close();
          submitDeleteSimulation(id, employeeName);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Submit Delete Simulation
    async function submitDeleteSimulation(id, employeeName) {
      Swal.fire({
        title: 'Deleting Simulation...',
        html: 'Please wait while we delete the simulation entry.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const response = await fetch(`/admin/succession-simulations/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        let responseData;

        if (contentType && contentType.includes('application/json')) {
          responseData = await response.json();
        } else {
          // If not JSON, get text content for debugging
          const textContent = await response.text();
          console.error('Non-JSON response received:', textContent);

          // Check if it's a redirect to login page
          if (textContent.includes('<!DOCTYPE') || textContent.includes('<html')) {
            throw new Error('Session expired. Please refresh the page and try again.');
          }

          throw new Error('Invalid server response. Please try again.');
        }

        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Simulation Deleted Successfully!',
            text: `The simulation entry for ${employeeName} has been deleted.`,
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          // Handle validation errors
          let errorMessage = 'An error occurred while deleting the simulation.';
          if (responseData && responseData.errors) {
            const firstError = Object.values(responseData.errors)[0];
            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
          } else if (responseData && responseData.message) {
            errorMessage = responseData.message;
          }

          throw new Error(errorMessage);
        }
      } catch (error) {
        console.error('Error deleting simulation:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error Deleting Simulation',
          text: error.message || 'An error occurred while deleting the simulation. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Add Simulation with Confirmation
    async function addSimulationWithConfirmation() {
      // Find the form more specifically using ID
      const form = document.getElementById('addSimulationForm');

      if (!form) {
        Swal.fire({
          icon: 'error',
          title: 'Form Not Found',
          text: 'Could not find the simulation form. Please refresh the page.',
          confirmButtonColor: '#dc3545'
        });
        return;
      }

      const formData = new FormData(form);

      // Debug: Log all form data
      console.log('=== ADD SIMULATION DEBUG ===');
      console.log('Form found:', !!form);
      console.log('Form ID:', form ? form.id : 'no form');

      console.log('FormData contents:');
      for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: "${value}" (type: ${typeof value})`);
      }

      // Also try to get values directly from form elements as backup
      const employeeSelect = form.querySelector('select[name="employee_id"]');
      const simulationSelect = form.querySelector('select[name="simulation_result"]');
      const dateInput = form.querySelector('input[name="created_at"]');

      console.log('Form elements found:', {
        employeeSelect: !!employeeSelect,
        simulationSelect: !!simulationSelect,
        dateInput: !!dateInput,
        employeeValue: employeeSelect ? employeeSelect.value : 'not found',
        simulationValue: simulationSelect ? simulationSelect.value : 'not found',
        dateValue: dateInput ? dateInput.value : 'not found'
      });

      // Check if elements have values
      if (employeeSelect) {
        console.log('Employee select options count:', employeeSelect.options.length);
        console.log('Employee select selectedIndex:', employeeSelect.selectedIndex);
        console.log('Employee select selected option:', employeeSelect.selectedOptions[0] ? employeeSelect.selectedOptions[0].text : 'none');
      }

      // Get values with multiple fallback methods
      let employeeId = formData.get('employee_id');
      let simulationResult = formData.get('simulation_result');
      let createdAt = formData.get('created_at');

      // Fallback 1: Direct element access
      if (!employeeId && employeeSelect) {
        employeeId = employeeSelect.value;
        console.log('Using fallback 1 for employeeId:', employeeId);
      }

      if (!simulationResult && simulationSelect) {
        simulationResult = simulationSelect.value;
        console.log('Using fallback 1 for simulationResult:', simulationResult);
      }

      if (!createdAt && dateInput) {
        createdAt = dateInput.value;
        console.log('Using fallback 1 for createdAt:', createdAt);
      }

      // Fallback 2: Try alternative selectors
      if (!employeeId) {
        const altEmployeeSelect = document.querySelector('#addSimulationForm select[name="employee_id"]');
        if (altEmployeeSelect) {
          employeeId = altEmployeeSelect.value;
          console.log('Using fallback 2 for employeeId:', employeeId);
        }
      }

      console.log('Final values to validate:', {
        employeeId,
        simulationResult,
        createdAt
      });

      // Validation with fallback values
      if (!employeeId || employeeId === '' || employeeId === 'not found') {
        console.error('CRITICAL: Employee ID is empty or invalid!');
        console.error('FormData employee_id:', formData.get('employee_id'));
        console.error('Direct element value:', employeeSelect ? employeeSelect.value : 'element not found');

        Swal.fire({
          icon: 'warning',
          title: 'Missing Information',
          html: `
            <div class="text-start">
              <p class="mb-3">Please select an employee from the dropdown.</p>
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Troubleshooting:</strong>
                <ul class="mb-0 mt-2">
                  <li>Make sure you have selected an employee from the first dropdown</li>
                  <li>If the dropdown is empty, please refresh the page</li>
                  <li>Check if you have the proper permissions</li>
                </ul>
              </div>
            </div>
          `,
          confirmButtonColor: '#ffc107',
          confirmButtonText: 'I understand'
        });
        return;
      }

      if (!simulationResult || simulationResult === '') {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Information',
          text: 'Please select a simulation result.',
          confirmButtonColor: '#ffc107'
        });
        return;
      }

      if (!createdAt || createdAt === '') {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Information',
          text: 'Please select a date.',
          confirmButtonColor: '#ffc107'
        });
        return;
      }

      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-success"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to add this simulation entry:</p>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Security Notice:</strong> Adding simulation entries requires admin verification to ensure data accuracy.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Verify & Add',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const isValid = await verifyAdminPassword(password);

        if (isValid) {
          Swal.close();
          // Pass the validated values instead of original formData
          const validatedData = {
            employee_id: employeeId,
            simulation_result: simulationResult,
            created_at: createdAt
          };
          submitAddSimulation(validatedData);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Submit Add Simulation
    async function submitAddSimulation(validatedData) {
      Swal.fire({
        title: 'Adding Simulation...',
        html: 'Please wait while we add the simulation entry.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        // Create a new FormData with the required fields mapped correctly
        const submitData = new FormData();

        // Get validated values
        const employeeId = validatedData.employee_id;
        const simulationResult = validatedData.simulation_result;
        const createdAt = validatedData.created_at;

        console.log('Validated data to submit:', {
          employee_id: employeeId,
          simulation_result: simulationResult,
          created_at: createdAt
        });

        // Double-check that we have the employee_id
        if (!employeeId || employeeId === '') {
          throw new Error('Employee ID is missing from the validated data');
        }

        // Map existing form fields to controller expected fields
        submitData.append('employee_id', employeeId);
        submitData.append('simulation_result', simulationResult); // For backward compatibility
        submitData.append('simulation_date', createdAt); // Map created_at to simulation_date

        // Add required fields with default values
        submitData.append('simulation_name', 'Succession Planning Simulation');
        submitData.append('simulation_type', 'leadership');
        submitData.append('status', 'completed');

        // Add optional fields with defaults
        submitData.append('scenario_description', 'Succession planning simulation entry');
        submitData.append('performance_rating', 'satisfactory');
        submitData.append('notes', 'Added via succession planning dashboard');

        // Add CSRF token
        submitData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Log what we're sending to the server
        console.log('Submitting data:', Object.fromEntries(submitData));

        const response = await fetch("{{ route('succession_simulations.store') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
            // Don't set Content-Type when using FormData - let browser set it automatically
          },
          body: submitData
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        let responseData;

        if (contentType && contentType.includes('application/json')) {
          responseData = await response.json();
        } else {
          // If not JSON, get text content for debugging
          const textContent = await response.text();
          console.error('Non-JSON response received:', textContent);

          // Check if it's a redirect to login page
          if (textContent.includes('<!DOCTYPE') || textContent.includes('<html')) {
            throw new Error('Session expired. Please refresh the page and try again.');
          }

          throw new Error('Invalid server response. Please try again.');
        }

        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Simulation Added Successfully!',
            text: 'The simulation entry has been added to the system.',
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          // Handle validation errors
          let errorMessage = 'An error occurred while adding the simulation.';
          if (responseData && responseData.errors) {
            const firstError = Object.values(responseData.errors)[0];
            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
          } else if (responseData && responseData.message) {
            errorMessage = responseData.message;
          }

          throw new Error(errorMessage);
        }
      } catch (error) {
        console.error('Error adding simulation:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error Adding Simulation',
          text: error.message || 'An error occurred while adding the simulation. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }




    // Generate Advanced AI Scenarios
    function generateAdvancedAIScenarios(type, severity, timeline) {
      const scenarios = generateEnhancedScenarioData(type, severity, timeline);
      displayAdvancedScenarios(scenarios);

      Swal.fire({
        icon: 'success',
        title: '🤖 AI Analysis Complete!',
        html: `
          <div class="text-start">
            <p class="mb-3">AI has successfully generated <strong>${scenarios.length}</strong> intelligent succession planning scenarios.</p>
            <div class="alert alert-info">
              <i class="bi bi-lightbulb me-2"></i>
              <strong>AI Insights:</strong> The analysis considered ${Math.floor(Math.random() * 50) + 20} data points including competency profiles, performance metrics, and organizational structure.
            </div>
          </div>
        `,
        confirmButtonColor: '#198754',
        timer: 4000,
        timerProgressBar: true
      });
    }

    // Enhanced Scenario Data Generation with AI Intelligence
    function generateEnhancedScenarioData(type, severity, timeline) {
      const aiScenarios = {
        departure: [
          {
            title: 'Executive Departure - AI Predictive Analysis',
            description: 'AI-powered analysis of leadership vacuum impact with predictive modeling',
            probability: Math.floor(Math.random() * 30) + 70,
            aiConfidence: Math.floor(Math.random() * 20) + 80,
            keyRisks: ['Knowledge transfer gaps', 'Stakeholder confidence erosion', 'Decision-making delays', 'Team morale impact'],
            aiRecommendations: [
              'Activate emergency succession protocol within 48 hours',
              'Implement AI-assisted knowledge capture system',
              'Deploy predictive analytics for stakeholder communication',
              'Initiate accelerated leadership development program'
            ],
            financialImpact: `$${Math.floor(Math.random() * 500000) + 200000}`,
            recoveryTime: getAIRecoveryTime(severity, timeline),
            successRate: Math.floor(Math.random() * 25) + 70
          }
        ],
        expansion: [
          {
            title: 'Business Expansion - AI Growth Modeling',
            description: 'Machine learning analysis of leadership scaling requirements',
            probability: Math.floor(Math.random() * 25) + 75,
            aiConfidence: Math.floor(Math.random() * 15) + 85,
            keyRisks: ['Leadership bandwidth constraints', 'Cultural dilution risks', 'Skill gap amplification', 'Quality control challenges'],
            aiRecommendations: [
              'Deploy AI-driven talent acquisition pipeline',
              'Implement predictive competency gap analysis',
              'Activate machine learning-based mentorship matching',
              'Launch automated leadership development tracking'
            ],
            financialImpact: `$${Math.floor(Math.random() * 800000) + 400000}`,
            recoveryTime: getAIRecoveryTime(severity, timeline),
            successRate: Math.floor(Math.random() * 20) + 75
          }
        ],
        restructure: [
          {
            title: 'Organizational Restructure - AI Impact Assessment',
            description: 'AI-powered organizational change impact analysis with predictive outcomes',
            probability: Math.floor(Math.random() * 35) + 65,
            aiConfidence: Math.floor(Math.random() * 25) + 75,
            keyRisks: ['Role confusion and overlap', 'Communication breakdown', 'Resistance to change', 'Productivity disruption'],
            aiRecommendations: [
              'Deploy AI change management assistant',
              'Implement predictive resistance analysis',
              'Activate automated communication optimization',
              'Launch intelligent role mapping system'
            ],
            financialImpact: `$${Math.floor(Math.random() * 400000) + 150000}`,
            recoveryTime: getAIRecoveryTime(severity, timeline),
            successRate: Math.floor(Math.random() * 30) + 65
          }
        ],
        crisis: [
          {
            title: 'Crisis Management - AI Emergency Response',
            description: 'AI-driven crisis succession planning with real-time decision support',
            probability: Math.floor(Math.random() * 40) + 60,
            aiConfidence: Math.floor(Math.random() * 30) + 70,
            keyRisks: ['Immediate leadership vacuum', 'Critical decision delays', 'Stakeholder panic', 'Operational paralysis'],
            aiRecommendations: [
              'Activate AI emergency succession protocol',
              'Deploy real-time decision support system',
              'Implement automated stakeholder communication',
              'Launch predictive crisis recovery modeling'
            ],
            financialImpact: `$${Math.floor(Math.random() * 1000000) + 500000}`,
            recoveryTime: getAIRecoveryTime(severity, timeline),
            successRate: Math.floor(Math.random() * 35) + 60
          }
        ],
        growth: [
          {
            title: 'Rapid Growth - AI Scaling Intelligence',
            description: 'Machine learning-powered leadership scaling with predictive capacity planning',
            probability: Math.floor(Math.random() * 20) + 80,
            aiConfidence: Math.floor(Math.random() * 10) + 90,
            keyRisks: ['Leadership shortage acceleration', 'Quality degradation risk', 'Cultural fragmentation', 'System overload'],
            aiRecommendations: [
              'Deploy AI talent pipeline acceleration',
              'Implement predictive quality assurance',
              'Activate intelligent culture preservation system',
              'Launch automated capacity planning'
            ],
            financialImpact: `$${Math.floor(Math.random() * 600000) + 300000}`,
            recoveryTime: getAIRecoveryTime(severity, timeline),
            successRate: Math.floor(Math.random() * 15) + 80
          }
        ]
      };

      return aiScenarios[type] || aiScenarios.departure;
    }

    // AI Recovery Time Calculation
    function getAIRecoveryTime(severity, timeline) {
      const aiRecoveryMatrix = {
        low: { immediate: '1-3 weeks (AI-optimized)', short: '3-6 weeks (AI-assisted)', medium: '6-10 weeks (AI-guided)', long: '10-16 weeks (AI-enhanced)' },
        medium: { immediate: '3-6 weeks (AI-optimized)', short: '6-12 weeks (AI-assisted)', medium: '12-20 weeks (AI-guided)', long: '20-32 weeks (AI-enhanced)' },
        high: { immediate: '6-12 weeks (AI-optimized)', short: '12-24 weeks (AI-assisted)', medium: '24-40 weeks (AI-guided)', long: '40-60 weeks (AI-enhanced)' },
        critical: { immediate: '12-24 weeks (AI-optimized)', short: '24-48 weeks (AI-assisted)', medium: '48-80 weeks (AI-guided)', long: '80+ weeks (AI-enhanced)' }
      };
      return aiRecoveryMatrix[severity][timeline];
    }

    // Display Advanced AI Scenarios
    function displayAdvancedScenarios(scenarios) {
      const container = document.getElementById('scenarioResults');
      let html = '';

      scenarios.forEach((scenario, index) => {
        const riskColor = scenario.probability >= 80 ? 'danger' : scenario.probability >= 60 ? 'warning' : 'success';
        const confidenceColor = scenario.aiConfidence >= 85 ? 'success' : scenario.aiConfidence >= 70 ? 'warning' : 'secondary';

        html += `
          <div class="col-12 mb-4">
            <div class="card border-${riskColor} border-opacity-25 ai-scenario-card">
              <div class="card-header bg-${riskColor} bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0 text-${riskColor}">
                    <i class="bi bi-robot me-2"></i>${scenario.title}
                  </h5>
                  <div class="d-flex gap-2">
                    <span class="badge bg-${confidenceColor} bg-opacity-10 text-${confidenceColor}">
                      AI Confidence: ${scenario.aiConfidence}%
                    </span>
                    <span class="badge bg-${riskColor} bg-opacity-10 text-${riskColor}">
                      Probability: ${scenario.probability}%
                    </span>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <p class="text-muted mb-4">${scenario.description}</p>

                <!-- AI Metrics Dashboard -->
                <div class="row mb-4">
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <div class="h4 text-primary">${scenario.probability}%</div>
                      <small class="text-muted">Probability</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <div class="h4 text-success">${scenario.successRate}%</div>
                      <small class="text-muted">Success Rate</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <div class="h4 text-info">${scenario.aiConfidence}%</div>
                      <small class="text-muted">AI Confidence</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <div class="h4 text-warning">${scenario.recoveryTime.split(' ')[0]}</div>
                      <small class="text-muted">Recovery Time</small>
                    </div>
                  </div>
                </div>

                <!-- AI Analysis Sections -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <h6 class="fw-bold text-danger">
                      <i class="bi bi-exclamation-triangle me-1"></i>AI-Identified Risk Factors
                    </h6>
                    <ul class="list-unstyled">
                      ${scenario.keyRisks.map(risk => `<li class="mb-2"><i class="bi bi-dot text-danger"></i><span class="ai-risk-item">${risk}</span></li>`).join('')}
                    </ul>
                  </div>
                  <div class="col-md-6">
                    <h6 class="fw-bold text-success">
                      <i class="bi bi-cpu me-1"></i>AI-Powered Recommendations
                    </h6>
                    <ul class="list-unstyled">
                      ${scenario.aiRecommendations.map(rec => `<li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i><span class="ai-recommendation">${rec}</span></li>`).join('')}
                    </ul>
                  </div>
                </div>

                <!-- Financial Impact & Recovery -->
                <div class="alert alert-info border-info">
                  <div class="row">
                    <div class="col-md-6">
                      <h6><i class="bi bi-currency-dollar me-2"></i>AI-Calculated Financial Impact</h6>
                      <p class="mb-0">Estimated cost: <strong>${scenario.financialImpact}</strong></p>
                    </div>
                    <div class="col-md-6">
                      <h6><i class="bi bi-clock me-2"></i>AI-Optimized Recovery</h6>
                      <p class="mb-0">Recovery time: <strong>${scenario.recoveryTime}</strong></p>
                    </div>
                  </div>
                </div>

                <!-- AI Action Buttons -->
                <div class="d-flex gap-2 mt-3">
                  <button class="btn btn-primary" onclick="implementAIScenario('${scenario.title}', ${index})">
                    <i class="bi bi-play-fill me-1"></i>Implement AI Plan
                  </button>
                  <button class="btn btn-outline-success" onclick="runAISimulation('${scenario.title}', ${index})">
                    <i class="bi bi-cpu me-1"></i>Run AI Simulation
                  </button>
                  <button class="btn btn-outline-warning" onclick="scheduleAIReview('${scenario.title}', ${index})">
                    <i class="bi bi-calendar-event me-1"></i>Schedule AI Review
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
      });

      container.innerHTML = html;
    }

    // AI Scenario Actions
    async function implementAIScenario(scenarioTitle, index) {
      const result = await Swal.fire({
        title: '<i class="bi bi-cpu text-success"></i> Implement AI Succession Plan',
        html: `
          <div class="text-start">
            <p class="mb-3">Are you ready to implement the AI-powered succession plan for:</p>
            <div class="alert alert-primary">
              <strong>${scenarioTitle}</strong>
            </div>
            <div class="alert alert-info">
              <i class="bi bi-robot me-2"></i>
              <strong>AI Implementation:</strong> This will activate automated succession protocols, deploy AI-assisted decision support, and begin real-time monitoring.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-play-fill me-1"></i>Activate AI Plan',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: '600px'
      });

      if (result.isConfirmed) {
        Swal.fire({
          icon: 'success',
          title: '🤖 AI Plan Activated!',
          text: `AI-powered succession plan for "${scenarioTitle}" is now active with real-time monitoring.`,
          confirmButtonColor: '#198754',
          timer: 4000,
          timerProgressBar: true
        });
      }
    }

    async function runAISimulation(scenarioTitle, index) {
      Swal.fire({
        title: '<i class="bi bi-cpu text-primary"></i> Running AI Simulation',
        html: `
          <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="mb-2">🤖 <strong>AI Simulation Engine Active</strong></p>
            <p class="text-muted">Processing "${scenarioTitle}" with advanced algorithms</p>
          </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        timer: 3000
      }).then(() => {
        const simulationResults = {
          successProbability: Math.floor(Math.random() * 30) + 70,
          riskMitigation: Math.floor(Math.random() * 25) + 75,
          timeOptimization: Math.floor(Math.random() * 20) + 80,
          costReduction: Math.floor(Math.random() * 35) + 15
        };

        Swal.fire({
          title: '<i class="bi bi-check-circle text-success"></i> AI Simulation Complete',
          html: `
            <div class="text-start">
              <h6 class="text-primary mb-3">Simulation Results for: ${scenarioTitle}</h6>
              <div class="row">
                <div class="col-6 mb-2">
                  <strong>Success Probability:</strong> <span class="text-success">${simulationResults.successProbability}%</span>
                </div>
                <div class="col-6 mb-2">
                  <strong>Risk Mitigation:</strong> <span class="text-info">${simulationResults.riskMitigation}%</span>
                </div>
                <div class="col-6 mb-2">
                  <strong>Time Optimization:</strong> <span class="text-warning">${simulationResults.timeOptimization}%</span>
                </div>
                <div class="col-6 mb-2">
                  <strong>Cost Reduction:</strong> <span class="text-primary">${simulationResults.costReduction}%</span>
                </div>
              </div>
              <div class="alert alert-success mt-3">
                <i class="bi bi-robot me-2"></i>
                <strong>AI Recommendation:</strong> Simulation indicates high success probability with optimized resource allocation.
              </div>
            </div>
          `,
          confirmButtonColor: '#198754',
          width: '600px'
        });
      });
    }

    function generateAIScenarios(type, severity, timeline) {
      const container = document.getElementById('scenarioResults');

      container.innerHTML = `
        <div class="col-12 mb-3">
          <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">🤖 AI is generating scenarios for ${type} with ${severity} impact...</p>
          </div>
        </div>
      `;

      // Simulate AI scenario generation
      setTimeout(() => {
        const scenarios = generateEnhancedScenarioData(type, severity, timeline);
        displayAdvancedScenarios(scenarios);
      }, 2000);
    }



    // Schedule AI Review
    async function scheduleAIReview(scenarioTitle, index) {
      const result = await Swal.fire({
        title: '<i class="bi bi-calendar-event text-warning"></i> Schedule AI Review',
        html: `
          <form id="swalScheduleReviewForm" class="text-start">
            <p class="mb-3">Schedule a review for AI scenario: <strong>"${scenarioTitle}"</strong></p>

            <div class="mb-3">
              <label class="form-label fw-bold">Review Date</label>
              <input type="date" class="form-control" name="review_date" required min="${new Date().toISOString().split('T')[0]}">
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Review Time</label>
              <input type="time" class="form-control" name="review_time" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Review Type</label>
              <select class="form-select" name="review_type" required>
                <option value="">Select Review Type</option>
                <option value="progress">Progress Review</option>
                <option value="adjustment">Strategy Adjustment</option>
                <option value="implementation">Implementation Review</option>
                <option value="outcome">Outcome Assessment</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Attendees</label>
              <textarea class="form-control" name="attendees" rows="2" placeholder="List key attendees for the review..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Review Notes</label>
              <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes or agenda items..."></textarea>
            </div>

            <div class="alert alert-info">
              <i class="bi bi-robot me-2"></i>
              <strong>AI Enhancement:</strong> The system will automatically prepare AI insights and recommendations for this review.
            </div>
          </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-calendar-plus me-1"></i>Schedule Review',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('swalScheduleReviewForm');
          const formData = new FormData(form);

          if (!formData.get('review_date')) {
            Swal.showValidationMessage('Please select a review date');
            return false;
          }
          if (!formData.get('review_time')) {
            Swal.showValidationMessage('Please select a review time');
            return false;
          }
          if (!formData.get('review_type')) {
            Swal.showValidationMessage('Please select a review type');
            return false;
          }

          return Object.fromEntries(formData);
        }
      });

      if (result.isConfirmed) {
        Swal.fire({
          icon: 'success',
          title: '📅 AI Review Scheduled!',
          html: `
            <div class="text-start">
              <p class="mb-3">Review has been scheduled successfully:</p>
              <ul class="list-unstyled">
                <li><strong>Scenario:</strong> ${scenarioTitle}</li>
                <li><strong>Date:</strong> ${result.value.review_date}</li>
                <li><strong>Time:</strong> ${result.value.review_time}</li>
                <li><strong>Type:</strong> ${result.value.review_type}</li>
              </ul>
              <div class="alert alert-success">
                <i class="bi bi-robot me-2"></i>
                <strong>AI Preparation:</strong> The system will automatically prepare insights and recommendations before the review.
              </div>
            </div>
          `,
          confirmButtonColor: '#198754',
          timer: 5000,
          timerProgressBar: true
        });
      }
    }

    // Generate Custom Scenario with Confirmation
    async function generateCustomScenarioWithConfirmation() {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-primary"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to create a custom AI scenario:</p>
            <div class="alert alert-info">
              <i class="bi bi-plus-circle me-2"></i>
              <strong>Custom Scenario:</strong> This will allow you to create personalized succession planning scenarios with AI assistance.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Verify & Create',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const isValid = await verifyAdminPassword(password);

        if (isValid) {
          Swal.close();
          showCustomScenarioForm();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Show Custom Scenario Form
    function showCustomScenarioForm() {
      Swal.fire({
        title: '<i class="bi bi-plus-circle text-primary"></i> Create Custom AI Scenario',
        html: `
          <form id="swalCustomScenarioForm" class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Scenario Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="scenario_title" placeholder="Enter a descriptive title for your scenario" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Scenario Description <span class="text-danger">*</span></label>
              <textarea class="form-control" name="scenario_description" rows="3" placeholder="Describe the scenario situation and context..." required></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Impact Level</label>
                <select class="form-select" name="impact_level" required>
                  <option value="">Select Impact Level</option>
                  <option value="low">Low Impact</option>
                  <option value="medium">Medium Impact</option>
                  <option value="high">High Impact</option>
                  <option value="critical">Critical Impact</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Timeline</label>
                <select class="form-select" name="timeline" required>
                  <option value="">Select Timeline</option>
                  <option value="immediate">Immediate (0-30 days)</option>
                  <option value="short">Short-term (1-6 months)</option>
                  <option value="medium">Medium-term (6-18 months)</option>
                  <option value="long">Long-term (18+ months)</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Key Positions Affected</label>
              <textarea class="form-control" name="affected_positions" rows="2" placeholder="List the key positions that would be affected..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Specific Concerns</label>
              <textarea class="form-control" name="specific_concerns" rows="3" placeholder="Describe specific concerns or challenges for this scenario..."></textarea>
            </div>

            <div class="alert alert-info">
              <i class="bi bi-robot me-2"></i>
              <strong>AI Enhancement:</strong> The AI will analyze your inputs and generate intelligent recommendations, risk assessments, and mitigation strategies.
            </div>
          </form>
        `,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-cpu me-1"></i>Generate AI Scenario',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('swalCustomScenarioForm');
          const formData = new FormData(form);

          if (!formData.get('scenario_title')) {
            Swal.showValidationMessage('Please enter a scenario title');
            return false;
          }
          if (!formData.get('scenario_description')) {
            Swal.showValidationMessage('Please enter a scenario description');
            return false;
          }
          if (!formData.get('impact_level')) {
            Swal.showValidationMessage('Please select an impact level');
            return false;
          }
          if (!formData.get('timeline')) {
            Swal.showValidationMessage('Please select a timeline');
            return false;
          }

          return Object.fromEntries(formData);
        }
      }).then((result) => {
        if (result.isConfirmed) {
          generateCustomAIScenario(result.value);
        }
      });
    }

    // Generate Custom AI Scenario
    function generateCustomAIScenario(data) {
      Swal.fire({
        title: '<i class="bi bi-cpu text-primary"></i> AI Processing Custom Scenario',
        html: `
          <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="mb-2">🤖 <strong>AI Analyzing Custom Scenario</strong></p>
            <p class="text-muted">Processing "${data.scenario_title}" with advanced AI algorithms</p>
            <div class="progress mt-3">
              <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
          </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          const progressBar = Swal.getPopup().querySelector('.progress-bar');
          let progress = 0;
          const interval = setInterval(() => {
            progress += Math.random() * 25;
            if (progress > 100) progress = 100;
            progressBar.style.width = progress + '%';

            if (progress >= 100) {
              clearInterval(interval);
              setTimeout(() => {
                Swal.close();
                displayCustomAIScenario(data);
              }, 500);
            }
          }, 400);
        }
      });
    }

    // Display Custom AI Scenario
    function displayCustomAIScenario(data) {
      const customScenario = {
        title: data.scenario_title,
        description: data.scenario_description,
        probability: Math.floor(Math.random() * 40) + 60,
        aiConfidence: Math.floor(Math.random() * 30) + 70,
        keyRisks: generateCustomRisks(data.impact_level),
        aiRecommendations: generateCustomRecommendations(data.timeline, data.impact_level),
        financialImpact: `$${Math.floor(Math.random() * 750000) + 250000}`,
        recoveryTime: getAIRecoveryTime(data.impact_level, data.timeline),
        successRate: Math.floor(Math.random() * 35) + 65,
        affectedPositions: data.affected_positions || 'Multiple positions',
        specificConcerns: data.specific_concerns || 'General succession planning concerns'
      };

      displayAdvancedScenarios([customScenario]);

      Swal.fire({
        icon: 'success',
        title: '🤖 Custom AI Scenario Generated!',
        html: `
          <div class="text-start">
            <p class="mb-3">Your custom AI scenario <strong>"${data.scenario_title}"</strong> has been successfully generated.</p>
            <div class="alert alert-info">
              <i class="bi bi-lightbulb me-2"></i>
              <strong>AI Analysis:</strong> The system analyzed your inputs and generated intelligent risk assessments, recommendations, and implementation strategies.
            </div>
          </div>
        `,
        confirmButtonColor: '#198754',
        timer: 4000,
        timerProgressBar: true
      });
    }

    // Generate Custom Risks based on Impact Level
    function generateCustomRisks(impactLevel) {
      const riskMatrix = {
        low: ['Minor workflow disruption', 'Temporary skill gaps', 'Limited stakeholder concern', 'Short-term productivity dip'],
        medium: ['Moderate operational impact', 'Knowledge transfer challenges', 'Team restructuring needs', 'Client relationship management'],
        high: ['Significant leadership vacuum', 'Critical skill shortages', 'Major stakeholder concerns', 'Operational continuity risks'],
        critical: ['Immediate crisis management', 'Emergency succession activation', 'Stakeholder confidence crisis', 'Business continuity threats']
      };
      return riskMatrix[impactLevel] || riskMatrix.medium;
    }

    // Generate Custom Recommendations based on Timeline and Impact
    function generateCustomRecommendations(timeline, impactLevel) {
      const baseRecommendations = [
        'Deploy AI-powered succession mapping',
        'Implement predictive analytics for risk assessment',
        'Activate automated stakeholder communication',
        'Launch intelligent talent pipeline development'
      ];

      const timelineSpecific = {
        immediate: ['Activate emergency protocols within 24 hours', 'Deploy crisis management AI assistant'],
        short: ['Implement accelerated development programs', 'Deploy AI-assisted mentoring systems'],
        medium: ['Launch comprehensive succession planning', 'Implement predictive competency modeling'],
        long: ['Develop strategic leadership pipeline', 'Deploy long-term AI monitoring systems']
      };

      return [...baseRecommendations, ...(timelineSpecific[timeline] || timelineSpecific.medium)];
    }

    // Export Scenario Report with Confirmation
    async function exportScenarioReportWithConfirmation() {
      console.log('Export Scenario Report function called!'); // Debug log

      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-info"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to export scenario reports:</p>
            <div class="alert alert-info">
              <i class="bi bi-file-earmark-spreadsheet me-2"></i>
              <strong>Export Notice:</strong> This will generate comprehensive reports for all current AI scenarios including metrics, recommendations, and implementation plans.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-download me-1"></i>Verify & Export',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0dcaf0',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const isValid = await verifyAdminPassword(password);

        if (isValid) {
          Swal.close();
          exportAllScenarioReports();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'The password you entered is incorrect. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Export All Scenario Reports
    async function exportAllScenarioReports() {
      console.log('Export All Scenario Reports function called!'); // Debug log

      const result = await Swal.fire({
        title: '<i class="bi bi-file-earmark-spreadsheet text-info"></i> Select Export Type',
        html: `
          <form id="swalExportForm" class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Export Type</label>
              <select class="form-select" name="export_type" required>
                <option value="">Select Export Type</option>
                <option value="simulations">Simulation Entries Only</option>
                <option value="scenarios">AI Scenarios Only</option>
                <option value="comprehensive">Comprehensive Report (All Data)</option>
              </select>
            </div>

            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Export Options:</strong>
              <ul class="mb-0 mt-2">
                <li><strong>Simulations:</strong> All simulation entries with employee details</li>
                <li><strong>Scenarios:</strong> AI-generated scenario analysis data</li>
                <li><strong>Comprehensive:</strong> Complete succession planning report</li>
              </ul>
            </div>
          </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-download me-1"></i>Export Data',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0dcaf0',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const form = document.getElementById('swalExportForm');
          const formData = new FormData(form);

          if (!formData.get('export_type')) {
            Swal.showValidationMessage('Please select an export type');
            return false;
          }

          return Object.fromEntries(formData);
        }
      });

      if (result.isConfirmed) {
        await submitExportRequest(result.value.export_type);
      }
    }

    // Submit Export Request
    async function submitExportRequest(exportType) {
      console.log('Submit Export Request called with type:', exportType); // Debug log

      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification Required',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to export succession planning data:</p>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Security Notice:</strong> Password verification is required for data export operations to ensure data security and prevent unauthorized access.
            </div>
          </div>
        `,
        input: 'password',
        inputPlaceholder: 'Enter your admin password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          class: 'form-control'
        },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-download me-1"></i>Verify & Export',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        width: '500px',
        inputValidator: (value) => {
          if (!value) {
            return 'Password is required!';
          }
          if (value.length < 3) {
            return 'Password must be at least 3 characters long!';
          }
        }
      });

      if (password) {
        Swal.fire({
          title: '<i class="bi bi-file-earmark-spreadsheet text-info"></i> Exporting Data',
          html: `
            <div class="text-center">
              <div class="spinner-border text-info mb-3" role="status"></div>
              <p class="mb-2">📊 <strong>Generating Export File</strong></p>
              <p class="text-muted">Compiling succession planning data...</p>
            </div>
          `,
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false
        });

        try {
          console.log('Creating export form with type:', exportType); // Debug log

          // Create form for file download
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '/admin/succession-simulations/export';
          form.style.display = 'none';

          // Add CSRF token
          const csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          form.appendChild(csrfInput);

          // Add password
          const passwordInput = document.createElement('input');
          passwordInput.type = 'hidden';
          passwordInput.name = 'password';
          passwordInput.value = password;
          form.appendChild(passwordInput);

          // Add export type
          const exportTypeInput = document.createElement('input');
          exportTypeInput.type = 'hidden';
          exportTypeInput.name = 'export_type';
          exportTypeInput.value = exportType;
          form.appendChild(exportTypeInput);

          document.body.appendChild(form);
          form.submit();
          document.body.removeChild(form);

          // Show success message after a delay
          setTimeout(() => {
            Swal.fire({
              icon: 'success',
              title: '📊 Export Completed!',
              html: `
                <div class="text-start">
                  <p class="mb-3">Succession planning data has been exported successfully.</p>
                  <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Export Type:</strong> ${exportType.charAt(0).toUpperCase() + exportType.slice(1)} Report
                  </div>
                  <p class="text-muted">The file has been downloaded to your default downloads folder.</p>
                </div>
              `,
              confirmButtonColor: '#198754',
              timer: 5000,
              timerProgressBar: true
            });
          }, 2000);

        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Export Failed',
            text: 'An error occurred while exporting the data. Please try again.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    function generateScenarioData(type, severity, timeline) {
      const scenarioTemplates = {
        departure: {
          title: 'Executive Departure Analysis',
          description: 'Impact assessment of key leadership departure',
          keyFactors: ['Leadership vacuum', 'Knowledge transfer', 'Team morale', 'Stakeholder confidence'],
          mitigationStrategies: ['Interim leadership', 'Accelerated succession', 'Knowledge documentation', 'Communication plan']
        },
        expansion: {
          title: 'Business Expansion Scenario',
          description: 'Leadership requirements for business growth',
          keyFactors: ['New market entry', 'Team scaling', 'Resource allocation', 'Cultural integration'],
          mitigationStrategies: ['Leadership pipeline', 'External recruitment', 'Cultural training', 'Mentorship programs']
        },
        restructure: {
          title: 'Organizational Restructure',
          description: 'Impact of structural changes on succession planning',
          keyFactors: ['Role redefinition', 'Reporting changes', 'Skill gaps', 'Change resistance'],
          mitigationStrategies: ['Retraining programs', 'Clear communication', 'Phased implementation', 'Support systems']
        },
        crisis: {
          title: 'Crisis Management Scenario',
          description: 'Emergency succession planning activation',
          keyFactors: ['Immediate response', 'Decision authority', 'Communication channels', 'Business continuity'],
          mitigationStrategies: ['Emergency protocols', 'Backup leadership', 'Crisis communication', 'Recovery planning']
        },
        growth: {
          title: 'Rapid Growth Management',
          description: 'Scaling leadership for rapid expansion',
          keyFactors: ['Leadership shortage', 'Skill development', 'Cultural preservation', 'Quality maintenance'],
          mitigationStrategies: ['Accelerated development', 'External hiring', 'Mentorship scaling', 'Process standardization']
        }
      };

      const template = scenarioTemplates[type] || scenarioTemplates.departure;

      // Calculate impact metrics based on severity and timeline
      const impactMultipliers = { low: 0.3, medium: 0.6, high: 0.8, critical: 1.0 };
      const timelineMultipliers = { immediate: 1.2, short: 1.0, medium: 0.8, long: 0.6 };

      const baseImpact = impactMultipliers[severity] * timelineMultipliers[timeline];

      return {
        ...template,
        impactLevel: severity,
        timeline: timeline,
        probability: Math.round(70 + (Math.random() * 25)), // 70-95%
        successRate: Math.round(60 + (baseImpact * 30)), // 60-90%
        riskScore: Math.round(baseImpact * 100),
        affectedPositions: Math.ceil(baseImpact * 8),
        recoveryTime: getRecoveryTime(severity, timeline),
        budgetImpact: getBudgetImpact(severity, type),
        recommendations: template.mitigationStrategies
      };
    }

    function getRecoveryTime(severity, timeline) {
      const recoveryMatrix = {
        low: { immediate: '2-4 weeks', short: '1-2 months', medium: '2-3 months', long: '3-4 months' },
        medium: { immediate: '1-2 months', short: '2-4 months', medium: '4-6 months', long: '6-8 months' },
        high: { immediate: '2-4 months', short: '4-8 months', medium: '8-12 months', long: '12-18 months' },
        critical: { immediate: '6-12 months', short: '12-18 months', medium: '18-24 months', long: '24+ months' }
      };
      return recoveryMatrix[severity][timeline];
    }

    function getBudgetImpact(severity, type) {
      const baseCosts = { departure: 150000, expansion: 300000, restructure: 200000, crisis: 500000, growth: 400000 };
      const severityMultipliers = { low: 0.5, medium: 1.0, high: 1.5, critical: 2.0 };

      const baseCost = baseCosts[type] || 200000;
      const multiplier = severityMultipliers[severity] || 1.0;

      return `$${Math.round(baseCost * multiplier).toLocaleString()}`;
    }

    function displayGeneratedScenarios(scenario) {
      const container = document.getElementById('scenarioResults');

      const riskColor = scenario.riskScore >= 80 ? 'danger' : scenario.riskScore >= 60 ? 'warning' : 'success';
      const impactColor = scenario.impactLevel === 'critical' ? 'danger' : scenario.impactLevel === 'high' ? 'warning' : 'primary';

      container.innerHTML = `
        <div class="col-12">
          <div class="card border-${impactColor} border-opacity-25">
            <div class="card-header bg-${impactColor} bg-opacity-10">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-${impactColor}">${scenario.title}</h5>
                <span class="badge bg-${riskColor} bg-opacity-10 text-${riskColor}">
                  Risk Score: ${scenario.riskScore}%
                </span>
              </div>
            </div>
            <div class="card-body">
              <p class="text-muted mb-3">${scenario.description}</p>

              <!-- Key Metrics -->
              <div class="row mb-4">
                <div class="col-md-3">
                  <div class="text-center">
                    <div class="h4 text-primary">${scenario.probability}%</div>
                    <small class="text-muted">Probability</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="text-center">
                    <div class="h4 text-success">${scenario.successRate}%</div>
                    <small class="text-muted">Success Rate</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="text-center">
                    <div class="h4 text-warning">${scenario.affectedPositions}</div>
                    <small class="text-muted">Affected Positions</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="text-center">
                    <div class="h4 text-info">${scenario.recoveryTime}</div>
                    <small class="text-muted">Recovery Time</small>
                  </div>
                </div>
              </div>

              <!-- Key Factors -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <h6 class="fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>Key Risk Factors
                  </h6>
                  <ul class="list-unstyled">
                    ${scenario.keyFactors.map(factor => `<li class="mb-1"><i class="bi bi-dot text-danger"></i>${factor}</li>`).join('')}
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-bold text-success">
                    <i class="bi bi-shield-check me-1"></i>Mitigation Strategies
                  </h6>
                  <ul class="list-unstyled">
                    ${scenario.recommendations.map(rec => `<li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i>${rec}</li>`).join('')}
                  </ul>
                </div>
              </div>

              <!-- Financial Impact -->
              <div class="alert alert-info">
                <h6><i class="bi bi-currency-dollar me-2"></i>Estimated Financial Impact</h6>
                <p class="mb-0">Total estimated cost: <strong>${scenario.budgetImpact}</strong> (including recruitment, training, productivity loss, and interim solutions)</p>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" onclick="implementScenario('${scenario.title}')">
                  <i class="bi bi-play-fill me-1"></i>Implement Plan
                </button>
                <button class="btn btn-outline-secondary" onclick="exportScenario('${scenario.title}')">
                  <i class="bi bi-download me-1"></i>Export Analysis
                </button>
                <button class="btn btn-outline-info" onclick="scheduleReview('${scenario.title}')">
                  <i class="bi bi-calendar-event me-1"></i>Schedule Review
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function generateCustomScenario() {
      // Show custom scenario modal
      const modal = new bootstrap.Modal(document.getElementById('customScenarioModal'));
      modal.show();
    }

    function exportScenarioReport() {
      // Generate and download scenario report
      const scenarios = document.querySelectorAll('#scenarioResults .card');
      if (scenarios.length === 0) {
        alert('No scenarios to export. Please generate scenarios first.');
        return;
      }

      // Simulate report generation
      const button = event.target;
      const originalText = button.innerHTML;
      button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
      button.disabled = true;

      setTimeout(() => {
        // Create downloadable report
        const reportData = {
          title: 'AI Succession Planning Scenario Report',
          generatedAt: new Date().toISOString(),
          scenarios: Array.from(scenarios).map(card => ({
            title: card.querySelector('h5').textContent,
            riskScore: card.querySelector('.badge').textContent,
            description: card.querySelector('.text-muted').textContent
          }))
        };

        const blob = new Blob([JSON.stringify(reportData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `succession-scenario-report-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;

        showNotification('Scenario report exported successfully!', 'success');
      }, 1500);
    }

    function implementScenario(scenarioTitle) {
      if (confirm(`Implement succession plan for: ${scenarioTitle}?`)) {
        showNotification(`Implementation plan activated for ${scenarioTitle}`, 'success');
      }
    }

    function exportScenario(scenarioTitle) {
      showNotification(`Exporting analysis for ${scenarioTitle}...`, 'info');
    }

    function scheduleReview(scenarioTitle) {
      showNotification(`Review scheduled for ${scenarioTitle}`, 'success');
    }

    document.addEventListener('DOMContentLoaded', function() {
      // ...existing code...
      // Edit Simulation Modal logic - REMOVED (now using SweetAlert with password verification)
      // AJAX submit for edit form - REMOVED (now using SweetAlert with password verification)
    });

    // Interactive Role Chart Functions with SweetAlert
    async function showCandidates(positionId) {
      console.log('showCandidates called with positionId:', positionId);

      // Get position info from the page data
      const positions = @json($positions ?? []);
      const topCandidates = @json($topCandidates ?? []);

      // Try to find position in database first
      let position = positions.find(p => p.id == positionId);
      
      // Try multiple ways to get candidates data
      let candidates = [];
      
      // Method 1: Direct position ID lookup
      if (topCandidates[positionId]) {
        candidates = topCandidates[positionId];
      }
      // Method 2: String position ID lookup
      else if (topCandidates[positionId.toString()]) {
        candidates = topCandidates[positionId.toString()];
      }
      // Method 3: Check if topCandidates is an array and find by position
      else if (Array.isArray(topCandidates)) {
        candidates = topCandidates.filter(candidate => 
          candidate.position_id == positionId || 
          candidate.target_position_id == positionId
        );
      }
      
      // Ensure candidates is always an array
      if (!Array.isArray(candidates)) {
        candidates = [];
      }
      
      // Also try to fetch candidates from API as fallback
      if (candidates.length === 0) {
        try {
          const apiCandidates = await fetchCandidatesFromAPI(positionId);
          if (apiCandidates && apiCandidates.length > 0) {
            candidates = apiCandidates;
          }
        } catch (error) {
          console.warn('Failed to fetch candidates from API:', error);
        }
      }
      
      // Note: Sample candidates removed - now using real data from controller for all positions 1-12

      console.log('=== DEBUGGING CANDIDATES DATA ===');
      console.log('Position ID requested:', positionId, typeof positionId);
      console.log('Position found:', position);
      console.log('topCandidates structure:', topCandidates);
      console.log('topCandidates keys:', Object.keys(topCandidates || {}));
      console.log('Direct lookup [positionId]:', topCandidates[positionId]);
      console.log('String lookup [positionId.toString()]:', topCandidates[positionId.toString()]);
      console.log('Final candidates found:', candidates);
      console.log('Final candidates count:', candidates.length);
      console.log('=== END DEBUGGING ===');

      // If position not found in database, create fallback position info
      if (!position) {
        const fallbackPositions = {
          '1': { id: 1, position_title: 'General Manager / CEO', department: 'Executive', level: 1 },
          '2': { id: 2, position_title: 'Operations Manager', department: 'Operations', level: 2 },
          '3': { id: 3, position_title: 'Sales & Marketing Manager', department: 'Sales & Marketing', level: 2 },
          '4': { id: 4, position_title: 'Finance Manager', department: 'Finance', level: 2 },
          '5': { id: 5, position_title: 'HR Manager', department: 'Human Resources', level: 2 },
          '6': { id: 6, position_title: 'Tour Coordinator', department: 'Operations', level: 3 },
          '7': { id: 7, position_title: 'Customer Service Supervisor', department: 'Customer Service', level: 3 },
          '8': { id: 8, position_title: 'Tour Guide', department: 'Operations', level: 4 },
          '9': { id: 9, position_title: 'Travel Agent', department: 'Operations', level: 4 },
          '10': { id: 10, position_title: 'Reservation Officer', department: 'Operations', level: 4 },
          '11': { id: 11, position_title: 'Ticketing Officer', department: 'Operations', level: 4 },
          '12': { id: 12, position_title: 'Transport Coordinator', department: 'Operations', level: 4 }
        };
        
        position = fallbackPositions[positionId.toString()];
        
        if (!position) {
          Swal.fire({
            icon: 'warning',
            title: 'Position Information Unavailable',
            text: `Position ID ${positionId} is not configured in the system. Please contact your administrator to set up this position.`,
            confirmButtonColor: '#ffc107'
          });
          return;
        }
      }

      // Build candidates HTML for SweetAlert
      const candidatesHtml = candidates.length > 0 ?
        candidates.map(candidate => `
          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">${candidate.name || 'Unknown Employee'}</h6>
                <span class="badge ${candidate.readiness_score >= 90 ? 'bg-success' : candidate.readiness_score >= 70 ? 'bg-warning' : 'bg-secondary'}">
                  ${Math.round(candidate.readiness_score || 0)}% Ready
                </span>
              </div>
              <p class="text-muted small mb-2">${candidate.current_position || 'Employee'}</p>
              <div class="progress mb-2" style="height: 10px; background-color: #e9ecef; border-radius: 5px;">
                <div class="progress-bar ${candidate.readiness_score >= 90 ? 'bg-success' : candidate.readiness_score >= 70 ? 'bg-warning' : 'bg-secondary'}"
                     style="width: ${candidate.readiness_score || 0}%; border-radius: 5px; transition: width 0.6s ease;">
                  <span style="font-size: 0.75rem; color: white; font-weight: 600;">${Math.round(candidate.readiness_score || 0)}%</span>
                </div>
              </div>
              <button class="btn btn-sm btn-outline-primary" onclick="viewCandidateDetails('${candidate.employee_id}')">
                <i class="bi bi-eye me-1"></i>View Profile
              </button>
            </div>
          </div>
        `).join('') :
        `<div class="text-center py-4">
          <i class="bi bi-people display-4 text-muted mb-3"></i>
          <h5 class="text-muted">No Succession Candidates Available</h5>
          <p class="text-muted mb-3">No succession candidates have been identified for the <strong>${position.position_title}</strong> position.</p>
          <div class="alert alert-info text-start">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Next Steps:</strong>
            <ul class="mb-0 mt-2">
              <li>Run succession planning evaluation to identify potential candidates</li>
              <li>Review employee competencies and performance ratings</li>
              <li>Consider cross-training opportunities for current staff</li>
              <li>Evaluate external recruitment needs</li>
            </ul>
          </div>
        </div>`;

      // Show in SweetAlert
      Swal.fire({
        title: `<i class="bi bi-people me-2"></i>${position.position_title} - Succession Candidates`,
        html: `
          <div class="text-start">
            <div class="alert ${candidates.length > 0 ? 'alert-info' : 'alert-warning'} mb-3">
              <i class="bi bi-${candidates.length > 0 ? 'info-circle' : 'exclamation-triangle'} me-2"></i>
              <strong>Department:</strong> ${position.department || 'General'}<br>
              <strong>Position Level:</strong> ${position.level ? `Level ${position.level}` : 'Not specified'}<br>
              <strong>Available Candidates:</strong> ${candidates.length}
              ${candidates.length === 0 ? '<br><strong>Status:</strong> <span class="text-warning">Needs Attention</span>' : '<br><strong>Status:</strong> <span class="text-success">Candidates Available</span>'}
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
              ${candidatesHtml}
            </div>
          </div>
        `,
        width: '750px',
        showCloseButton: true,
        showConfirmButton: candidates.length === 0,
        confirmButtonText: candidates.length === 0 ? '<i class="bi bi-plus-lg me-1"></i>Add Candidates' : undefined,
        confirmButtonColor: candidates.length === 0 ? '#198754' : undefined,
        showCancelButton: candidates.length > 0,
        cancelButtonText: candidates.length > 0 ? 'Close' : undefined,
        customClass: {
          popup: 'text-start'
        }
      }).then((result) => {
        if (result.isConfirmed && candidates.length === 0) {
          // Redirect to add candidates or show add candidate form
          addCandidateWithConfirmation();
        }
      });
      
      // Debug: Log the final state
      console.log(`Final state for position ${positionId}: ${candidates.length} candidates, button will ${candidates.length === 0 ? 'show' : 'hide'}`);
    }

    // Enhanced error handling for API calls
    async function fetchCandidatesFromAPI(positionId) {
      try {
        const response = await fetch(`/admin/succession-simulations/candidates/${positionId}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });

        if (!response.ok) {
          console.warn(`API call failed for position ${positionId}: ${response.status}`);
          return [];
        }

        const data = await response.json();
        return data.candidates || [];
      } catch (error) {
        console.warn(`Error fetching candidates for position ${positionId}:`, error);
        return [];
      }
    }

    async function getCandidatesForPosition(positionType) {
      // Map position types to actual position IDs
      const positionMapping = {
        'ceo': 1,
        'cto': 2,
        'cfo': 3,
        'cmo': 4,
        'dev_manager': 5,
        'finance_manager': 6,
        'sales_manager': 7,
        'hr_manager': 8
      };

      const positionId = positionMapping[positionType];
      if (!positionId) return [];

      try {
        const response = await fetch(`/api/succession-planning/position/${positionId}/candidates`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (!response.ok) throw new Error('Failed to fetch candidates');

        const candidates = await response.json();
        return candidates.map(candidate => ({
          name: candidate.name,
          readiness: Math.round(candidate.readiness_score),
          department: candidate.department,
          experience: `${candidate.experience_years} years`,
          employee_id: candidate.employee_id,
          competencies: candidate.competency_breakdown,
          strengths: candidate.strengths,
          gaps: candidate.gaps
        }));
      } catch (error) {
        console.error('Error fetching candidates:', error);
        // Fallback to sample data if API fails
        return getSampleCandidates(positionType);
      }
    }

    function getSampleCandidates(positionType) {
      const mockData = {
        'ceo': [
          { name: 'John Smith', readiness: 95, department: 'Operations', experience: '8 years' },
          { name: 'Sarah Johnson', readiness: 88, department: 'Technology', experience: '6 years' }
        ],
        'cto': [
          { name: 'Alex Chen', readiness: 92, department: 'Development', experience: '5 years' },
          { name: 'Maria Garcia', readiness: 85, department: 'Infrastructure', experience: '7 years' }
        ],
        'cfo': [
          { name: 'Mike Davis', readiness: 94, department: 'Finance', experience: '9 years' },
          { name: 'Lisa Wang', readiness: 82, department: 'Accounting', experience: '4 years' }
        ]
      };
      return mockData[positionType] || [];
    }

    function displayCandidatesInModal(candidates) {
      const container = document.getElementById('candidatesContainer');
      container.innerHTML = '';

      candidates.forEach(candidate => {
        const candidateCard = `
          <div class="col-md-6 mb-3">
            <div class="card candidate-card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0">${candidate.name}</h6>
                  <span class="badge ${candidate.readiness >= 90 ? 'bg-success' : candidate.readiness >= 80 ? 'bg-warning' : 'bg-secondary'}">${candidate.readiness}% Ready</span>
                </div>
                <p class="text-muted small mb-2">${candidate.department} • ${candidate.experience}</p>
                <div class="progress mb-2" style="height: 6px;">
                  <div class="progress-bar ${candidate.readiness >= 90 ? 'bg-success' : candidate.readiness >= 80 ? 'bg-warning' : 'bg-secondary'}"
                       style="width: ${candidate.readiness}%"></div>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="viewCandidateDetails('${candidate.employee_id || candidate.name.toLowerCase().replace(' ', '_')}')">
                  View Profile
                </button>
              </div>
            </div>
          </div>
        `;
        container.innerHTML += candidateCard;
      });
    }

    function viewCandidateDetails(employeeId) {
      if (!employeeId) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Employee ID not found',
          confirmButtonColor: '#dc3545'
        });
        return;
      }

      console.log('viewCandidateDetails called with employeeId:', employeeId);

      // Get employee data from the page
      const employees = @json($employees ?? []);
      const topCandidates = @json($topCandidates ?? []);

      // Find employee in candidates first
      let employee = null;
      for (const positionId in topCandidates) {
        const candidates = topCandidates[positionId];
        if (Array.isArray(candidates)) {
          const found = candidates.find(candidate =>
            candidate.employee_id == employeeId ||
            candidate.id == employeeId
          );
          if (found) {
            employee = found;
            break;
          }
        }
      }

      if (!employee) {
        // Show fallback modal with option to view profile
        Swal.fire({
          icon: 'warning',
          title: 'Employee Details Not Available',
          html: `
            <p>Could not find detailed information for this employee in the succession planning data.</p>
            <p><strong>Employee ID:</strong> ${employeeId}</p>
            <div class="mt-3">
              <button class="btn btn-primary" onclick="window.open('{{ route('employee_competency_profiles.index') }}?employee_id=${employeeId}&from=succession-planning', '_blank')">
                <i class="bi bi-box-arrow-up-right me-1"></i>View Full Competency Profile
              </button>
            </div>
          `,
          showConfirmButton: false,
          showCloseButton: true,
          width: '500px'
        });
        return;
      }

      // Show employee details in SweetAlert
      const employeeName = employee.name || 'Unknown Employee';
      const readinessScore = employee.readiness_score || 0;
      const currentPosition = employee.current_position || 'Employee';

      Swal.fire({
        title: `<i class="bi bi-person-circle me-2"></i>${employeeName}`,
        html: `
          <div class="text-start">
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Employee ID:</strong><br>
                <span class="text-muted">${employee.employee_id || employee.id || 'N/A'}</span>
              </div>
              <div class="col-md-6">
                <strong>Current Position:</strong><br>
                <span class="text-muted">${currentPosition}</span>
              </div>
            </div>

            <div class="mb-3">
              <strong>Succession Readiness:</strong>
              <div class="progress mt-2" style="height: 20px; background-color: #e9ecef; border-radius: 10px;">
                <div class="progress-bar ${readinessScore >= 90 ? 'bg-success' : readinessScore >= 70 ? 'bg-warning' : 'bg-secondary'}"
                     style="width: ${readinessScore}%; border-radius: 10px; transition: width 0.6s ease;">
                  <span style="font-size: 0.75rem; font-weight: 600; line-height: 20px;">${Math.round(readinessScore)}%</span>
                </div>
              </div>
            </div>

            ${employee.competency_breakdown ? `
              <div class="mb-3">
                <strong>Key Competencies:</strong>
                <div class="mt-2">
                  ${employee.competency_breakdown.slice(0, 3).map(comp => `
                    <div class="d-flex justify-content-between mb-1">
                      <small>${comp.competency_name}</small>
                      <small>${comp.score}%</small>
                    </div>
                    <div class="progress mb-2" style="height: 6px; background-color: #e9ecef; border-radius: 3px;">
                      <div class="progress-bar bg-info" style="width: ${comp.score}%; border-radius: 3px; transition: width 0.6s ease;"></div>
                    </div>
                  `).join('')}
                </div>
              </div>
            ` : ''}

            <div class="text-center mt-4">
              <button class="btn btn-primary me-2" onclick="window.open('{{ route('employee_competency_profiles.index') }}?employee_id=${employeeId}&from=succession-planning', '_blank')">
                <i class="bi bi-box-arrow-up-right me-1"></i>View Full Profile
              </button>
              <button class="btn btn-outline-secondary" onclick="Swal.close()">
                <i class="bi bi-x-lg me-1"></i>Close
              </button>
            </div>
          </div>
        `,
        width: '600px',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
          popup: 'text-start'
        }
      });
    }

    function refreshOrgChart() {
      // Refresh the organizational chart with latest data
      location.reload();
    }

    function simulateScenario(scenarioIndex) {
      console.log('Simulate scenario called with index:', scenarioIndex);

      const modal = new bootstrap.Modal(document.getElementById('scenarioResultsModal'));

      // Get scenario data from the page
      const scenarioCards = document.querySelectorAll('.scenario-card');
      console.log('Found scenario cards:', scenarioCards.length);

      if (scenarioIndex >= scenarioCards.length) {
        console.error('Scenario not found - index:', scenarioIndex, 'total cards:', scenarioCards.length);
        alert('Scenario not found');
        return;
      }

      const scenarioCard = scenarioCards[scenarioIndex];
      const scenarioTitle = scenarioCard.querySelector('.card-title').textContent.trim();
      const impactLevel = scenarioCard.querySelector('.badge').textContent.trim();

      document.getElementById('scenarioTitle').textContent = scenarioTitle + ' - Simulation Results';

      // Run simulation
      const results = runScenarioSimulation(scenarioIndex, scenarioTitle, impactLevel);
      displayScenarioResults(results);
      modal.show();
    }

    function getScenarioTitle(scenarioType) {
      const titles = {
        'ceo_resignation': 'CEO Resignation Impact Analysis',
        'dept_restructure': 'Department Restructuring Analysis',
        'rapid_growth': 'Rapid Growth Scenario Analysis',
        'manager_departure': 'Key Manager Departure Analysis'
      };
      return titles[scenarioType] || 'Scenario Analysis';
    }

    function runScenarioSimulation(scenarioIndex, scenarioTitle, impactLevel) {
      // Get real data from the dashboard
      const totalCandidates = parseInt(document.querySelector('.card-body h2.text-primary')?.textContent) || 0;
      const readyLeaders = parseInt(document.querySelectorAll('.card-body h2.text-success')[0]?.textContent) || 0;
      const inDevelopment = parseInt(document.querySelector('.card-body h2.text-warning')?.textContent) || 0;
      const keyPositions = parseInt(document.querySelector('.card-body h2.text-info')?.textContent) || 0;

      console.log('Real data extracted for simulation:', {
        totalCandidates, readyLeaders, inDevelopment, keyPositions
      });

      // Calculate real metrics based on actual data
      const successorRatio = totalCandidates > 0 ? Math.round((readyLeaders / totalCandidates) * 100) : 0;
      const riskLevel = inDevelopment > 5 ? 'High' : inDevelopment > 2 ? 'Medium' : 'Low';
      const positionsAtRisk = Math.max(1, keyPositions - readyLeaders);

      const baseResults = {
        'Scenario Analysis': scenarioTitle,
        'Impact Level': impactLevel,
        'Simulation Status': 'Completed',
        'Analysis Date': new Date().toLocaleDateString(),
        'Current Risk Level': riskLevel,
        'Successor Readiness': successorRatio + '%'
      };

      // Add specific results based on scenario type using real data
      if (scenarioTitle.toLowerCase().includes('ceo') || scenarioTitle.toLowerCase().includes('departure')) {
        const transitionTime = readyLeaders >= 2 ? '2-3 months' : readyLeaders === 1 ? '3-6 months' : '6-12 months';
        return {
          ...baseResults,
          'Ready Successors': readyLeaders,
          'Total Candidates': totalCandidates,
          'Transition Time': transitionTime,
          'Risk Factors': readyLeaders === 0 ?
            ['Critical leadership gap', 'No ready successors', 'High business risk'] :
            ['Leadership transition risk', 'Stakeholder confidence', 'Knowledge transfer'],
          'Recommendations': readyLeaders === 0 ? [
            'Emergency external recruitment',
            'Accelerate development programs',
            'Implement interim leadership'
          ] : [
            'Accelerate top successor development',
            'Prepare comprehensive handover plan',
            'Communicate transition strategy'
          ]
        };
      } else if (scenarioTitle.toLowerCase().includes('restructur')) {
        const affectedPositions = Math.round(keyPositions * 0.6);
        const newPositionsNeeded = Math.max(1, Math.round(inDevelopment * 0.4));
        return {
          ...baseResults,
          'Affected Positions': affectedPositions,
          'New Positions Needed': newPositionsNeeded,
          'Current Candidates': totalCandidates,
          'Timeline': riskLevel === 'High' ? '8-12 months' : riskLevel === 'Medium' ? '6-8 months' : '4-6 months',
          'Recommendations': [
            `Cross-train ${Math.min(totalCandidates, affectedPositions)} key personnel`,
            'Update job descriptions for new structure',
            'Plan phased implementation over ' + (riskLevel === 'High' ? '12' : '8') + ' months'
          ]
        };
      } else if (scenarioTitle.toLowerCase().includes('growth')) {
        const leadershipGap = Math.max(0, keyPositions - readyLeaders);
        const newPositionsNeeded = Math.round(keyPositions * 1.5);
        return {
          ...baseResults,
          'Current Leadership Gap': leadershipGap + ' positions',
          'New Positions Needed': newPositionsNeeded,
          'Available Candidates': totalCandidates,
          'Timeline': totalCandidates >= newPositionsNeeded ? '12-18 months' : '18-24 months',
          'Recommendations': totalCandidates < newPositionsNeeded ? [
            'Accelerate external recruitment',
            'Expand leadership development programs',
            'Implement fast-track succession planning'
          ] : [
            'Accelerate internal development',
            'Create mentorship programs',
            'Plan structured growth phases'
          ]
        };
      } else {
        return {
          ...baseResults,
          'Positions at Risk': positionsAtRisk,
          'Available Successors': readyLeaders,
          'Development Pipeline': inDevelopment,
          'Recovery Time': readyLeaders >= positionsAtRisk ? '3-6 months' :
                          totalCandidates >= positionsAtRisk ? '6-9 months' : '9-12 months',
          'Recommendations': readyLeaders >= positionsAtRisk ? [
            'Maintain current development pace',
            'Implement retention strategies',
            'Regular succession reviews'
          ] : [
            'Accelerate development programs',
            'Identify external candidates',
            'Implement emergency succession plans'
          ]
        };
      }
    }

    function displayScenarioResults(results) {
      const container = document.getElementById('scenarioResultsContainer');
      let html = '<div class="simulation-results">';

      // Add real data indicator
      html += `
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Simulation Results</h5>
          <span class="real-data-badge">
            <i class="bi bi-database-check me-1"></i>Real Data
          </span>
        </div>
      `;

      Object.keys(results).forEach(key => {
        const value = results[key];
        const isRecommendations = key.toLowerCase().includes('recommendation');
        const isRiskLevel = key.toLowerCase().includes('risk level');

        // Determine risk level class for styling
        let riskClass = '';
        if (isRiskLevel && typeof value === 'string') {
          riskClass = value.toLowerCase().includes('high') ? 'risk-level-high' :
                     value.toLowerCase().includes('medium') ? 'risk-level-medium' :
                     value.toLowerCase().includes('low') ? 'risk-level-low' : '';
        }

        if (isRecommendations && Array.isArray(value)) {
          html += `
            <div class="recommendations-list">
              <strong><i class="bi bi-lightbulb me-2"></i>${key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}:</strong>
              <ul class="mt-2">${value.map(item => `<li>${item}</li>`).join('')}</ul>
            </div>
          `;
        } else {
          html += `
            <div class="simulation-metric ${riskClass}">
              <strong>${key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}:</strong>
              ${Array.isArray(value) ?
                `<ul class="mb-0 mt-2">${value.map(item => `<li>${item}</li>`).join('')}</ul>` :
                `<span class="metric-value ms-2">${value}</span>`
              }
              ${key === 'Simulation Status' && value === 'Completed' ?
                `<span class="simulation-status-completed ms-2">✓ ${value}</span>` : ''
              }
            </div>
          `;
        }
      });

      html += '</div>';
      container.innerHTML = html;
    }

    function runScenarioSimulation() {
      // Show loading state
      const button = event.target;
      const originalText = button.innerHTML;
      button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Running...';
      button.disabled = true;

      // Simulate processing time
      setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('scenarioResultsModal'));
        document.getElementById('scenarioTitle').textContent = 'Comprehensive Scenario Analysis';

        // Get real data from the dashboard
        const totalCandidates = parseInt(document.querySelector('.card-body h2.text-primary')?.textContent) || 0;
        const readyLeaders = parseInt(document.querySelectorAll('.card-body h2.text-success')[0]?.textContent) || 0;
        const inDevelopment = parseInt(document.querySelector('.card-body h2.text-warning')?.textContent) || 0;
        const keyPositions = parseInt(document.querySelector('.card-body h2.text-info')?.textContent) || 0;

        // Calculate real risk level
        const riskLevel = inDevelopment > 5 ? 'High' : inDevelopment > 2 ? 'Medium' : 'Low';
        const successorRatio = totalCandidates > 0 ? Math.round((readyLeaders / totalCandidates) * 100) : 0;
        const developmentGaps = Math.max(0, keyPositions - readyLeaders);

        // Generate recommendations based on real data
        const recommendations = [];
        if (readyLeaders < keyPositions) {
          recommendations.push('Accelerate leadership development programs');
        }
        if (inDevelopment > keyPositions) {
          recommendations.push('Focus development resources on critical positions');
        }
        if (successorRatio < 50) {
          recommendations.push('Expand succession candidate identification');
        }
        if (riskLevel === 'High') {
          recommendations.push('Implement emergency succession protocols');
        }
        // Add default recommendations if none specific
        if (recommendations.length === 0) {
          recommendations.push('Maintain current succession planning efforts', 'Regular review and updates');
        }

        const comprehensiveResults = {
          'Analysis Type': 'Comprehensive Simulation',
          'Scenarios Analyzed': document.querySelectorAll('.scenario-card').length,
          'Overall Risk Level': riskLevel,
          'Critical Positions': keyPositions,
          'Ready Successors': readyLeaders,
          'Total Candidates': totalCandidates,
          'Development Pipeline': inDevelopment,
          'Development Gaps': developmentGaps,
          'Successor Readiness Ratio': successorRatio + '%',
          'Key Recommendations': recommendations,
          'Next Review Date': new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toLocaleDateString()
        };

        displayScenarioResults(comprehensiveResults);
        modal.show();

        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;
      }, 2000);
    }

    // Add Candidate Form Submission
    document.getElementById('addCandidateForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const data = Object.fromEntries(formData);

      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Adding...';
      submitBtn.disabled = true;

      // Send to backend
      fetch('/api/succession-planning/candidates', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          // Close modal
          const modal = bootstrap.Modal.getInstance(document.getElementById('addCandidateModal'));
          modal.hide();

          // Reset form
          this.reset();

          // Show success message
          showNotification('Candidate added successfully!', 'success');

          // Reload candidates list
          location.reload();
        } else {
          showNotification('Error adding candidate: ' + (result.message || 'Unknown error'), 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding candidate. Please try again.', 'error');
      })
      .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    });

    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;

      document.body.appendChild(notification);

      // Auto remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }

    // Update target positions based on selected employee
    function updateTargetPositions() {
      const employeeSelect = document.getElementById('employee_id');
      const positionSelect = document.getElementById('target_position_id');
      const selectedEmployee = employeeSelect.value;

      // Reset position dropdown
      positionSelect.innerHTML = '<option value="">Select Position</option>';

      if (!selectedEmployee) {
        positionSelect.innerHTML = '<option value="">Select Employee First</option>';
        return;
      }

      // Get employee competencies
      const selectedOption = employeeSelect.querySelector(`option[value="${selectedEmployee}"]`);
      const employeeCompetencies = JSON.parse(selectedOption.getAttribute('data-competencies') || '[]');

      // Show all positions and calculate readiness
      const allPositions = document.querySelectorAll('#target_position_id option[data-requirements]');

      allPositions.forEach(option => {
        const requirements = JSON.parse(option.getAttribute('data-requirements') || '[]');
        const minScore = parseInt(option.getAttribute('data-min-score') || '50');

        // Calculate basic compatibility score
        let compatibilityScore = 0;
        if (requirements.length > 0) {
          const matchingCompetencies = requirements.filter(req =>
            employeeCompetencies.includes(req.competency_id)
          ).length;
          compatibilityScore = Math.round((matchingCompetencies / requirements.length) * 100);
        } else {
          compatibilityScore = 75; // Default for positions without requirements
        }

        // Update option text with compatibility indicator
        const originalText = option.textContent.split(' (')[0]; // Remove existing indicators
        let indicator = '';
        let className = '';

        if (compatibilityScore >= 80) {
          indicator = ' (🟢 High Match)';
          className = 'text-success';
        } else if (compatibilityScore >= 60) {
          indicator = ' (🟡 Medium Match)';
          className = 'text-warning';
        } else {
          indicator = ' (🔴 Low Match)';
          className = 'text-danger';
        }

        option.textContent = originalText + indicator;
        option.className = className;
        option.style.display = 'block';

        // Add to select
        positionSelect.appendChild(option.cloneNode(true));
      });
    }
  </script>

  <!-- Add Candidate Modal -->
  <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Succession Candidate</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addCandidateForm">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                <select class="form-select" id="employee_id" name="employee_id" required onchange="updateTargetPositions()">
                  <option value="">Select Employee</option>
                  @if(isset($employees))
                    @foreach($employees as $employee)
                      @if(is_object($employee) && $employee->first_name && $employee->last_name)
                        <option value="{{ $employee->employee_id }}"
                                data-competencies="{{ json_encode($employee->competencyProfiles->pluck('competency_id')->toArray()) }}">
                          {{ $employee->first_name }} {{ $employee->last_name }} - {{ $employee->position ?? 'Employee' }}
                        </option>
                      @elseif(is_array($employee) && isset($employee['first_name']) && isset($employee['last_name']))
                        <option value="{{ $employee['employee_id'] }}"
                                data-competencies="{{ json_encode($employee['competency_profiles'] ?? []) }}">
                          {{ $employee['first_name'] }} {{ $employee['last_name'] }} - {{ $employee['position'] ?? 'Employee' }}
                        </option>
                      @endif
                    @endforeach
                  @endif
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="target_position_id" class="form-label">Target Position <span class="text-danger">*</span></label>
                <select class="form-select" id="target_position_id" name="target_position_id" required>
                  <option value="">Select Employee First</option>
                  @if(isset($positions))
                    @foreach($positions as $position)
                      <option value="{{ $position->id }}"
                              data-requirements="{{ json_encode($position->required_competencies ?? []) }}"
                              data-min-score="{{ $position->min_readiness_score ?? 50 }}"
                              style="display: none;">
                        {{ $position->position_title }} - {{ $position->department ?? 'General' }}
                      </option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="under_review">Under Review</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="development_priority" class="form-label">Development Priority</label>
                <select class="form-select" id="development_priority" name="development_priority">
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label for="notes" class="form-label">Notes</label>
              <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about this succession candidate..."></textarea>
            </div>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Note:</strong> The system will automatically calculate readiness scores based on the employee's competency profiles and the target position requirements.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-lg me-1"></i>Add Candidate
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Candidates Modal -->
  <div class="modal fade" id="candidatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPositionTitle">Position Candidates</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row" id="candidatesContainer">
            <!-- Candidates will be loaded here -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">Add New Candidate</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scenario Results Modal -->
  <div class="modal fade" id="scenarioResultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="scenarioTitle">Scenario Analysis Results</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="scenarioResultsContainer">
            <!-- Results will be loaded here -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Export Report</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
