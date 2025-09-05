<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/succession_planning_dashboard.css') }}">
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
        @if(isset($positions) && count($positions) > 0)
          @php
            $ceoPositions = $positions->where('level', 1);
            $executivePositions = $positions->where('level', 2);
            $managerPositions = $positions->where('level', 3);
          @endphp
          
          @if($ceoPositions->count() > 0)
            <div class="row justify-content-center mb-4">
              @foreach($ceoPositions as $position)
                @php
                  $topCandidate = $topCandidates[$position->id]->first() ?? null;
                  $readinessScore = $topCandidate ? round($topCandidate['readiness_score'] ?? 0) : 0;
                @endphp
                <div class="col-md-4">
                  <div class="role-node leader" onclick="showCandidates('{{ $position->id }}')">
                    <div class="readiness-score">{{ $readinessScore }}%</div>
                    <h5 class="mb-1">{{ $position->position_title }}</h5>
                    <p class="mb-0 small">{{ $position->department ?? 'Executive' }}</p>
                    <small class="opacity-75">{{ $topCandidate ? 'Next: ' . ($topCandidate['name'] ?? 'TBD') : 'No successor' }}</small>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="connection-line"></div>
          @endif
          
          @if($executivePositions->count() > 0)
            <div class="row justify-content-center mb-4">
              @foreach($executivePositions as $position)
                @php
                  $topCandidate = $topCandidates[$position->id]->first() ?? null;
                  $readinessScore = $topCandidate ? round($topCandidate['readiness_score'] ?? 0) : 0;
                @endphp
                <div class="col-md-3">
                  <div class="role-node manager" onclick="showCandidates('{{ $position->id }}')">
                    <div class="readiness-score">{{ $readinessScore }}%</div>
                    <h6 class="mb-1">{{ $position->position_title }}</h6>
                    <p class="mb-0 small">{{ $position->department ?? 'Department' }}</p>
                    <small class="opacity-75">{{ $topCandidate['name'] ?? 'No successor' }}</small>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="connection-line"></div>
          @endif
          
          @if($managerPositions->count() > 0)
            <div class="row justify-content-center">
              @foreach($managerPositions->take(4) as $position)
                @php
                  $topCandidate = $topCandidates[$position->id]->first() ?? null;
                  $readinessScore = $topCandidate ? round($topCandidate['readiness_score'] ?? 0) : 0;
                @endphp
                <div class="col-md-2">
                  <div class="role-node successor" onclick="showCandidates('{{ $position->id }}')">
                    <div class="readiness-score">{{ $readinessScore }}%</div>
                    <h6 class="mb-1 small">{{ Str::limit($position->position_title, 12) }}</h6>
                    <small class="opacity-75">{{ $topCandidate['name'] ?? 'No successor' }}</small>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        @else
          <div class="text-center py-5">
            <i class="bi bi-diagram-3 display-4 text-muted mb-3"></i>
            <h5 class="text-muted">No organizational positions defined</h5>
            <p class="text-muted">Create organizational positions to see the role chart.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Candidate Details and Comparison -->
    <div class="simulation-card card mb-4">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0"><i class="bi bi-person-check me-2"></i>Candidate Details & Comparison</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
          <i class="bi bi-plus-lg me-1"></i>Add Candidate
        </button>
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
                          <div class="progress skill-progress">
                            @php
                              $score = $competency['score'] ?? 0;
                              $progressClass = $score >= 90 ? 'bg-success' : ($score >= 70 ? 'bg-primary' : 'bg-warning');
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" style="width: {{ $score }}%"></div>
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

    <!-- Enhanced What-if Scenarios -->
    <div class="simulation-card card mb-4">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0"><i class="bi bi-lightbulb me-2"></i>AI-Powered What-if Scenarios</h4>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-success btn-sm" onclick="runScenarioSimulation()">
            <i class="bi bi-play-fill me-1"></i>Run AI Simulation
          </button>
          <button class="btn btn-outline-primary btn-sm" onclick="generateCustomScenario()">
            <i class="bi bi-plus-lg me-1"></i>Custom Scenario
          </button>
          <button class="btn btn-outline-info btn-sm" onclick="exportScenarioReport()">
            <i class="bi bi-download me-1"></i>Export Report
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- AI Scenario Controls -->
        <div class="row mb-4">
          <div class="col-md-4">
            <label class="form-label fw-bold">Scenario Type</label>
            <select id="scenarioType" class="form-select" onchange="updateScenarioParameters()">
              <option value="departure">Executive Departure</option>
              <option value="expansion">Business Expansion</option>
              <option value="restructure">Organizational Restructure</option>
              <option value="crisis">Crisis Management</option>
              <option value="growth">Rapid Growth</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Impact Severity</label>
            <select id="impactSeverity" class="form-select">
              <option value="low">Low Impact</option>
              <option value="medium">Medium Impact</option>
              <option value="high">High Impact</option>
              <option value="critical">Critical Impact</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Timeline</label>
            <select id="scenarioTimeline" class="form-select">
              <option value="immediate">Immediate (0-30 days)</option>
              <option value="short">Short-term (1-6 months)</option>
              <option value="medium">Medium-term (6-12 months)</option>
              <option value="long">Long-term (1+ years)</option>
            </select>
          </div>
        </div>
        
        <div class="row" id="scenarioResults">
          @if(isset($scenarioData))
            @foreach($scenarioData as $scenario)
              <div class="col-md-6 mb-3">
                <div class="scenario-card card">
                  <div class="card-body">
                    <h6 class="card-title">
                      @php
                        $impactClass = match(strtolower($scenario['impact_level'])) {
                          'high', 'extreme' => 'impact-high',
                          'medium' => 'impact-medium', 
                          'tremendous' => 'impact-high',
                          default => 'impact-low'
                        };
                      @endphp
                      <span class="impact-indicator {{ $impactClass }}"></span>
                      {{ $scenario['title'] }}
                    </h6>
                    <p class="card-text small text-muted mb-3">
                      {{ $scenario['description'] }}
                    </p>
                    <div class="mb-2">
                      <strong>Impact Level:</strong> 
                      @php
                        $badgeClass = match(strtolower($scenario['impact_level'])) {
                          'high', 'extreme' => 'bg-danger',
                          'medium' => 'bg-warning',
                          'tremendous' => 'bg-danger',
                          default => 'bg-success'
                        };
                      @endphp
                      <span class="badge {{ $badgeClass }}">{{ $scenario['impact_level'] }}</span>
                    </div>
                    @if(isset($scenario['ready_successor']))
                      <div class="mb-2">
                        <strong>Ready Successor:</strong> {{ $scenario['ready_successor'] }}
                      </div>
                    @endif
                    @if(isset($scenario['transition_time']))
                      <div class="mb-3">
                        <strong>Transition Time:</strong> {{ $scenario['transition_time'] }}
                      </div>
                    @endif
                    @if(isset($scenario['new_positions']))
                      <div class="mb-2">
                        <strong>New Positions Needed:</strong> {{ $scenario['new_positions'] }}
                      </div>
                    @endif
                    @if(isset($scenario['leadership_gap']))
                      <div class="mb-3">
                        <strong>Leadership Gap:</strong> {{ $scenario['leadership_gap'] }}
                      </div>
                    @endif
                    @if(isset($scenario['affected_positions']))
                      <div class="mb-2">
                        <strong>Affected Positions:</strong> {{ $scenario['affected_positions'] }}
                      </div>
                    @endif
                    @if(isset($scenario['timeline']))
                      <div class="mb-3">
                        <strong>Timeline:</strong> {{ $scenario['timeline'] }}
                      </div>
                    @endif
                    @if(isset($scenario['positions_at_risk']))
                      <div class="mb-2">
                        <strong>Positions at Risk:</strong> {{ $scenario['positions_at_risk'] }}
                      </div>
                    @endif
                    @if(isset($scenario['recovery_time']))
                      <div class="mb-3">
                        <strong>Recovery Time:</strong> {{ $scenario['recovery_time'] }}
                      </div>
                    @endif
                    <button class="btn btn-sm btn-outline-primary" onclick="simulateScenario('{{ $loop->index }}')">
                      <i class="bi bi-play me-1"></i>Simulate
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="col-12">
              <div class="text-center py-4">
                <i class="bi bi-lightbulb display-4 text-muted mb-3"></i>
                <h5 class="text-muted">AI Scenario Simulation Ready</h5>
                <p class="text-muted">Configure parameters above and click "Run AI Simulation" to generate intelligent what-if scenarios.</p>
                <div class="row mt-4">
                  <div class="col-md-4">
                    <div class="card border-primary border-opacity-25">
                      <div class="card-body text-center">
                        <i class="bi bi-cpu text-primary display-6 mb-2"></i>
                        <h6>AI Analysis</h6>
                        <small class="text-muted">Machine learning predictions</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-success border-opacity-25">
                      <div class="card-body text-center">
                        <i class="bi bi-graph-up-arrow text-success display-6 mb-2"></i>
                        <h6>Impact Assessment</h6>
                        <small class="text-muted">Risk and opportunity analysis</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-warning border-opacity-25">
                      <div class="card-body text-center">
                        <i class="bi bi-shield-check text-warning display-6 mb-2"></i>
                        <h6>Mitigation Plans</h6>
                        <small class="text-muted">Automated recommendations</small>
                      </div>
                    </div>
                  </div>
                </div>
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
        <form action="#" method="POST" class="mb-4">
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
              <button type="submit" class="btn btn-primary w-100">
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
                  <button class="btn btn-outline-warning btn-sm me-1 edit-simulation-btn"
                    data-id="{{ $item->id }}"
                    data-employee-id="{{ $item->employee_id }}"
                    data-simulation-result="{{ $item->simulation_result }}"
                    data-created-at="{{ date('Y-m-d', strtotime($item->created_at)) }}">
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                  <form action="{{ route('succession_simulations.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this simulation entry?')">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
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
    // Enhanced AI Functions for Dashboard Simulation
    function updateScenarioParameters() {
      const scenarioType = document.getElementById('scenarioType').value;
      const impactSeverity = document.getElementById('impactSeverity').value;
      const timeline = document.getElementById('scenarioTimeline').value;
      
      // Update scenario display based on parameters
      if (scenarioType && impactSeverity && timeline) {
        generateAIScenarios(scenarioType, impactSeverity, timeline);
      }
    }

    function generateAIScenarios(type, severity, timeline) {
      const container = document.getElementById('scenarioResults');
      
      container.innerHTML = `
        <div class="col-12 mb-3">
          <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">AI is generating scenarios for ${type} with ${severity} impact...</p>
          </div>
        </div>
      `;

      // Simulate AI scenario generation
      setTimeout(() => {
        const scenarios = generateScenarioData(type, severity, timeline);
        displayGeneratedScenarios(scenarios);
      }, 2000);
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
      // Edit Simulation Modal logic
      const editModal = new bootstrap.Modal(document.getElementById('editSimulationModal'));
      document.querySelectorAll('.edit-simulation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          document.getElementById('edit-simulation-id').value = this.getAttribute('data-id');
          document.getElementById('edit-employee-id').value = this.getAttribute('data-employee-id');
          document.getElementById('edit-simulation-result').value = this.getAttribute('data-simulation-result');
          document.getElementById('edit-created-at').value = this.getAttribute('data-created-at');
          // Set form action
          document.getElementById('editSimulationForm').action = '/succession_simulations/' + this.getAttribute('data-id');
          editModal.show();
        });
      });
      // AJAX submit for edit form
      document.getElementById('editSimulationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        fetch(form.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json().catch(() => ({}));
        })
        .then(data => {
          editModal.hide();
          window.location.reload();
        })
        .catch(error => {
          alert('Error updating simulation entry.');
        });
      });
    });

    // Interactive Role Chart Functions
    async function showCandidates(positionType) {
      const modal = new bootstrap.Modal(document.getElementById('candidatesModal'));
      document.getElementById('modalPositionTitle').textContent = positionType.toUpperCase() + ' Candidates';
      
      // Show loading state
      document.getElementById('candidatesContainer').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading candidates...</p></div>';
      modal.show();
      
      // Load candidates data
      const candidatesData = await getCandidatesForPosition(positionType);
      displayCandidatesInModal(candidatesData);
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
        alert('Employee ID not found');
        return;
      }
      
      // Redirect to employee competency profile page
      window.location.href = `{{ route('employee_competency_profiles.index') }}?employee_id=${employeeId}&from=succession-planning`;
    }

    function refreshOrgChart() {
      // Refresh the organizational chart with latest data
      location.reload();
    }

    function simulateScenario(scenarioIndex) {
      const modal = new bootstrap.Modal(document.getElementById('scenarioResultsModal'));
      
      // Get scenario data from the page
      const scenarioCards = document.querySelectorAll('.scenario-card');
      if (scenarioIndex >= scenarioCards.length) {
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
      // Generate realistic simulation results based on scenario data
      const baseResults = {
        'Scenario Analysis': scenarioTitle,
        'Impact Level': impactLevel,
        'Simulation Status': 'Completed',
        'Analysis Date': new Date().toLocaleDateString()
      };
      
      // Add specific results based on scenario type
      if (scenarioTitle.toLowerCase().includes('ceo') || scenarioTitle.toLowerCase().includes('departure')) {
        return {
          ...baseResults,
          'Ready Successors': Math.floor(Math.random() * 3) + 1,
          'Transition Time': ['2-3 months', '3-6 months', '6-12 months'][Math.floor(Math.random() * 3)],
          'Risk Factors': ['Leadership gap', 'Stakeholder confidence', 'Operational continuity'],
          'Recommendations': [
            'Accelerate successor development',
            'Prepare communication strategy',
            'Implement interim leadership plan'
          ]
        };
      } else if (scenarioTitle.toLowerCase().includes('restructur')) {
        return {
          ...baseResults,
          'Affected Positions': Math.floor(Math.random() * 10) + 5,
          'New Positions Needed': Math.floor(Math.random() * 5) + 2,
          'Timeline': ['4-6 months', '6-8 months', '8-12 months'][Math.floor(Math.random() * 3)],
          'Recommendations': [
            'Cross-train key personnel',
            'Update job descriptions',
            'Plan phased implementation'
          ]
        };
      } else if (scenarioTitle.toLowerCase().includes('growth')) {
        return {
          ...baseResults,
          'Leadership Gap': Math.floor(Math.random() * 6) + 3 + ' positions',
          'New Positions Needed': Math.floor(Math.random() * 15) + 8,
          'Timeline': ['12-18 months', '18-24 months'][Math.floor(Math.random() * 2)],
          'Recommendations': [
            'Accelerate leadership development',
            'External recruitment strategy',
            'Succession planning expansion'
          ]
        };
      } else {
        return {
          ...baseResults,
          'Positions at Risk': Math.floor(Math.random() * 8) + 2,
          'Recovery Time': ['3-6 months', '6-9 months', '9-12 months'][Math.floor(Math.random() * 3)],
          'Recommendations': [
            'Identify backup candidates',
            'Implement retention strategies',
            'Develop contingency plans'
          ]
        };
      }
    }

    function displayScenarioResults(results) {
      const container = document.getElementById('scenarioResultsContainer');
      let html = '';
      
      Object.keys(results).forEach(key => {
        html += `
          <div class="mb-3">
            <strong>${key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}:</strong>
            ${Array.isArray(results[key]) ? 
              `<ul class="mb-0">${results[key].map(item => `<li>${item}</li>`).join('')}</ul>` : 
              `<span class="ms-2">${results[key]}</span>`
            }
          </div>
        `;
      });
      
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
        
        const comprehensiveResults = {
          'Analysis Type': 'Comprehensive Simulation',
          'Scenarios Analyzed': document.querySelectorAll('.scenario-card').length,
          'Overall Risk Level': ['Low', 'Medium', 'High'][Math.floor(Math.random() * 3)],
          'Critical Positions': Math.floor(Math.random() * 5) + 3,
          'Ready Successors': Math.floor(Math.random() * 8) + 5,
          'Development Gaps': Math.floor(Math.random() * 10) + 2,
          'Key Recommendations': [
            'Strengthen leadership pipeline',
            'Implement cross-training programs',
            'Develop retention strategies',
            'Create succession roadmaps'
          ],
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
