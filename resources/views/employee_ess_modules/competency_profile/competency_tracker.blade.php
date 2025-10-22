<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Competency Progress Tracker</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <style>
    /* Custom styles for competency tracker */
    .competency-card {
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: none;
      transition: transform 0.2s ease;
    }
    .competency-card:hover {
      transform: translateY(-2px);
    }
    .card-header-custom {
      background: #f8f9fa;
      color: #495057;
      border-bottom: 1px solid #dee2e6;
      padding: 1.5rem;
      border-radius: 12px 12px 0 0;
    }
    .progress-ring {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: white;
      font-size: 14px;
    }
    .progress-excellent { background: linear-gradient(135deg, #4CAF50, #45a049); }
    .progress-good { background: linear-gradient(135deg, #2196F3, #1976D2); }
    .progress-fair { background: linear-gradient(135deg, #FF9800, #F57C00); }
    .progress-needs-improvement { background: linear-gradient(135deg, #f44336, #d32f2f); }

    .gap-badge {
      padding: 0.4em 0.8em;
      border-radius: 20px;
      font-size: 0.85em;
      font-weight: 500;
    }
    .gap-strong { background-color: transparent; color: #2e7d32; }
    .gap-moderate { background-color: transparent; color: #ef6c00; }
    .gap-needs-development { background-color: transparent; color: #c62828; }

    /* Category Badge Colors - Simple Card Header Style */
    .category-leadership,
    .category-technical,
    .category-behavioral,
    .category-destination-knowledge,
    .category-communication,
    .category-general,
    .category-interpersonal,
    .category-cognitive,
    .category-analytical,
    .category-creative,
    .category-problem-solving,
    .category-decision-making,
    .category-emotional-intelligence,
    .category-adaptability,
    .category-teamwork,
    .category-management,
    .category-strategic-thinking,
    .category-innovation,
    .category-organizational {
      background: #f8f9fa !important;
      color: black !important;
      border: 1px solid #dee2e6 !important;
    }

    /* Deadline Status Colors */
    .deadline-overdue {
      background-color: #ffebee;
      border-left: 4px solid #f44336;
      padding: 8px 12px;
      border-radius: 6px;
    }
    .deadline-urgent {
      background-color: #fff3e0;
      border-left: 4px solid #ff9800;
      padding: 8px 12px;
      border-radius: 6px;
    }
    .deadline-soon {
      background-color: #e3f2fd;
      border-left: 4px solid #2196f3;
      padding: 8px 12px;
      border-radius: 6px;
    }
    .deadline-normal {
      background-color: #f1f8e9;
      border-left: 4px solid #4caf50;
      padding: 8px 12px;
      border-radius: 6px;
    }

    .deadline-icon {
      font-size: 1.2em;
      margin-right: 8px;
    }

    .competency-level {
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .level-star {
      color: #ffc107;
      font-size: 16px;
    }
    .level-star.empty {
      color: #e0e0e0;
    }

    .summary-card {
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: none;
      padding: 1.5rem;
      text-align: center;
      background: white;
    }
    .summary-card h3 {
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .summary-card small {
      color: #6c757d;
      font-weight: 500;
    }

    .training-recommendation {
      background-color: #f8f9ff;
      border-left: 4px solid #667eea;
      padding: 1rem;
      border-radius: 0 8px 8px 0;
      margin-bottom: 0.5rem;
    }

    /* Admin dashboard stat card styling */
    .stat-card {
      border-radius: 12px;
      border: none;
      transition: transform 0.2s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
  </style>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">
    <div class="container-fluid">
      <!-- Page Header -->
      <div class="page-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center page-header">
          <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
              <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
              <h2 class="fw-bold mb-1">Competency Progress Tracker</h2>
              <p class="text-muted mb-0">
                View your competency levels, see gap areas, and check recommended trainings.
              </p>
            </div>
          </div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Competency Tracker</li>
            </ol>
          </nav>
        </div>
      </div>

      <!-- Summary Cards - Using admin dashboard style -->
      <div class="row g-4 mb-4">
        <div class="col-md-3">
          <div class="card stat-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                  <i class="bi bi-bar-chart"></i>
                </div>
                <div>
                  <h3 class="fw-bold mb-0">{{ $totalCompetencies }}</h3>
                  <p class="text-muted mb-0 small">Total Competencies</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                  <i class="bi bi-graph-up"></i>
                </div>
                <div>
                  <h3 class="fw-bold mb-0 text-primary">{{ number_format($averageProgress, 1) }}%</h3>
                  <p class="text-muted mb-0 small">Average Progress</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                  <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                  <h3 class="fw-bold mb-0">{{ $competenciesWithGaps }}</h3>
                  <p class="text-muted mb-0 small">Gap Areas</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                  <i class="bi bi-check-circle"></i>
                </div>
                <div>
                  <h3 class="fw-bold mb-0">{{ $onTrack }}</h3>
                  <p class="text-muted mb-0 small">On Track</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Competency Progress Table -->
      <div class="competency-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2"></i>My Competency Progress</h4>
          <button class="btn btn-light btn-sm" onclick="refreshData()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th class="fw-bold">Competency</th>
                  <th class="fw-bold">Category</th>
                  <th class="fw-bold">Current Level</th>
                  <th class="fw-bold">Target Level</th>
                  <th class="fw-bold">Progress</th>
                  <th class="fw-bold">Gap Status</th>
                  <th class="fw-bold">Manager Feedback</th>
                  <th class="fw-bold">Deadline</th>
                  <th class="fw-bold">Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                  // Ensure competencyTrackers is a collection and filter out invalid entries
                  $validTrackers = collect($competencyTrackers ?? [])->filter(function($tracker) {
                    return is_array($tracker) && (
                      isset($tracker['competency']) ||
                      isset($tracker['competency_name'])
                    );
                  });

                  // Group competency trackers by competency name to prevent duplicates
                  $uniqueTrackers = $validTrackers->groupBy(function($tracker) {
                    return $tracker['competency']->competency_name ?? $tracker['competency_name'] ?? 'Unknown';
                  })->map(function($group) {
                    // Return the most recent tracker for each competency
                    return $group->sortByDesc(function($tracker) {
                      return $tracker['assessment_date'] ?? $tracker['last_assessment_date'] ?? '1970-01-01';
                    })->first();
                  });
                @endphp
                @forelse($uniqueTrackers as $tracker)
                  @php
                    // Ensure tracker is properly formatted and has all required keys
                    $tracker = is_array($tracker) ? $tracker : [];
                    $competency = $tracker['competency'] ?? null;
                    $competencyName = $competency->competency_name ?? $tracker['competency_name'] ?? 'Unknown Competency';
                    $competencyDescription = $competency->description ?? '';
                    $competencyCategory = $competency->category ?? 'General';
                  @endphp
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="me-3">
                          <i class="bi bi-{{ $competencyCategory == 'Leadership' ? 'person-badge' : ($competencyCategory == 'Technical' ? 'gear' : 'lightbulb') }} text-primary"></i>
                        </div>
                        <div>
                          <div class="fw-semibold">{{ $competencyName }}</div>
                          <small class="text-muted">{{ Str::limit($competencyDescription, 50) }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      @php
                        $categoryClass = 'category-' . strtolower(str_replace([' ', '_'], '-', $competencyCategory));
                      @endphp
                      <span class="badge {{ $categoryClass }} px-3 py-2 fw-semibold">
                        <i class="bi bi-{{ $competencyCategory == 'Leadership' ? 'person-badge' : ($competencyCategory == 'Technical' ? 'gear' : ($competencyCategory == 'Behavioral' ? 'heart' : ($competencyCategory == 'Destination Knowledge' ? 'geo-alt' : ($competencyCategory == 'Communication' ? 'chat-dots' : 'lightbulb')))) }} me-1"></i>
                        {{ $competencyCategory }}
                      </span>
                    </td>
                    <td>
                      <div class="competency-level">
                        @for($i = 1; $i <= 5; $i++)
                          <i class="bi bi-star{{ $i <= ($tracker['current_level'] ?? 0) ? '-fill' : '' }} level-star{{ $i <= ($tracker['current_level'] ?? 0) ? '' : ' empty' }}"></i>
                        @endfor
                        <span class="ms-2 text-muted">({{ $tracker['current_level'] ?? 0 }}/5)</span>
                      </div>
                    </td>
                    <td>
                      <div class="competency-level">
                        @for($i = 1; $i <= 5; $i++)
                          <i class="bi bi-star{{ $i <= ($tracker['target_level'] ?? 0) ? '-fill' : '' }} level-star{{ $i <= ($tracker['target_level'] ?? 0) ? '' : ' empty' }}"></i>
                        @endfor
                        <span class="ms-2 text-muted">({{ $tracker['target_level'] ?? 0 }}/5)</span>
                      </div>
                    </td>
                    <td>
                      @php
                        $displayProgress = $tracker['progress_percentage'] ?? 0;
                        $progressStatus = $tracker['progress_status'] ?? 'Needs Improvement';
                      @endphp
                      <div class="d-flex align-items-center">
                        <div class="progress-ring progress-{{ strtolower(str_replace(' ', '-', $progressStatus)) }} me-2">
                          {{ number_format($displayProgress, 0) }}%
                        </div>
                        <small class="text-muted">{{ $progressStatus }}</small>
                      </div>
                    </td>
                    <td>
                      <span class="gap-badge gap-{{ strtolower(str_replace(' ', '-', $tracker['gap_status'] ?? 'moderate')) }}">
                        {{ $tracker['gap_status'] ?? 'Moderate' }}
                      </span>
                    </td>
                    <td>
                      @if($tracker['manager_feedback'] ?? null)
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $tracker['manager_feedback'] ?? '' }}">
                          {{ $tracker['manager_feedback'] ?? '' }}
                        </div>
                      @else
                        <span class="text-muted">No feedback yet</span>
                      @endif
                    </td>
                    <td>
                      @if($tracker['deadline'] ?? null)
                        @php
                          try {
                            $deadlineDate = \Carbon\Carbon::parse($tracker['deadline'] ?? null);
                            $now = \Carbon\Carbon::now();
                            $isOverdue = $deadlineDate->isPast();
                            $daysUntilDeadline = $now->diffInDays($deadlineDate, false);
                            $timeLeft = $deadlineDate->diffForHumans();

                            // Determine urgency level
                            if ($isOverdue) {
                              $urgencyClass = 'deadline-overdue';
                              $iconClass = 'bi-exclamation-triangle-fill text-danger';
                              $statusText = 'OVERDUE';
                            } elseif ($daysUntilDeadline <= 3) {
                              $urgencyClass = 'deadline-urgent';
                              $iconClass = 'bi-clock-fill text-warning';
                              $statusText = 'URGENT';
                            } elseif ($daysUntilDeadline <= 7) {
                              $urgencyClass = 'deadline-soon';
                              $iconClass = 'bi-calendar-event text-info';
                              $statusText = 'DUE SOON';
                            } else {
                              $urgencyClass = 'deadline-normal';
                              $iconClass = 'bi-calendar-check text-success';
                              $statusText = 'ON TRACK';
                            }
                          } catch (Exception $e) {
                            $deadlineDate = null;
                            $isOverdue = false;
                            $timeLeft = 'Invalid date';
                            $urgencyClass = 'deadline-normal';
                            $iconClass = 'bi-exclamation-triangle text-warning';
                            $statusText = 'INVALID';
                          }
                        @endphp
                        @if($deadlineDate)
                          <div class="{{ $urgencyClass }}">
                            <div class="d-flex align-items-center mb-1">
                              <i class="{{ $iconClass }} deadline-icon"></i>
                              <div class="flex-grow-1">
                                <div class="fw-semibold">
                                  {{ $deadlineDate->format('M d, Y') }}
                                </div>
                                <small class="opacity-75">
                                  {{ $timeLeft }}
                                </small>
                              </div>
                            </div>
                            <div class="text-center">
                              <span class="badge bg-dark bg-opacity-75 text-white px-2 py-1 small">
                                {{ $statusText }}
                              </span>
                            </div>
                          </div>
                        @else
                          <div class="text-center text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <small>Invalid deadline</small>
                          </div>
                        @endif
                      @else
                        <div class="text-center text-muted">
                          <i class="bi bi-calendar-x me-1"></i>
                          <small>No deadline set</small>
                        </div>
                      @endif
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewDetails({{ $tracker['id'] ?? 0 }})" title="View Details">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                      <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-graph-up display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No competency data found</h5>
                        <p class="text-muted mb-3">Your competency progress will appear here once assessments are completed.</p>
                        <a href="{{ route('employee.dashboard') }}" class="btn btn-primary">
                          <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                        </a>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Training Recommendations -->
      @if($recommendedTrainings->count() > 0)
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0"><i class="bi bi-lightbulb me-2"></i>Recommended Trainings</h5>
            </div>
            <div class="card-body">
              @foreach($recommendedTrainings as $training)
                <div class="training-recommendation">
                  <div class="d-flex align-items-start">
                    <i class="bi bi-book text-primary me-2 mt-1"></i>
                    <div>
                      <div class="fw-semibold">{{ $training }}</div>
                      <small class="text-muted">Recommended based on your competency gaps</small>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm border-0">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Upcoming Deadlines</h5>
            </div>
            <div class="card-body">
              @forelse($upcomingDeadlines as $deadline)
                <div class="d-flex align-items-center mb-3">
                  <div class="me-3">
                    <i class="bi bi-clock text-warning"></i>
                  </div>
                  <div>
                    <div class="fw-semibold">{{ $deadline->competency->competency_name ?? 'Unknown' }}</div>
                    <small class="text-muted">Due: {{ $deadline->deadline ? \Carbon\Carbon::parse($deadline->deadline)->format('M d, Y') : 'No deadline' }}</small>
                  </div>
                </div>
              @empty
                <div class="text-center text-muted">
                  <i class="bi bi-check-circle text-success display-6"></i>
                  <p class="mt-2 mb-0">No upcoming deadlines</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Refresh competency data
    function refreshData() {
      const refreshBtn = document.querySelector('button[onclick="refreshData()"]');
      const originalContent = refreshBtn.innerHTML;

      refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1 spinner-border spinner-border-sm"></i>Loading...';
      refreshBtn.disabled = true;

      fetch('{{ route("employee.competency_profile.progress_data") }}')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update summary cards
            updateSummaryCards(data.summary);

            // Show success message
            showToast('Data refreshed successfully', 'success');
          } else {
            showToast('Failed to refresh data', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error refreshing data', 'error');
        })
        .finally(() => {
          refreshBtn.innerHTML = originalContent;
          refreshBtn.disabled = false;
        });
    }

    // View competency details
    function viewDetails(trackerId) {
      window.location.href = `{{ url('/employee/competency-profile') }}/${trackerId}`;
    }

    // Global variable to store current tracker ID
    let currentTrackerId = null;

    // View training recommendations
    function viewTraining(trackerId) {
      currentTrackerId = trackerId;

      // Create modal for training details
      const modal = new bootstrap.Modal(document.getElementById('trainingModal') || createTrainingModal());

      // Load training details via AJAX
      fetch(`{{ url('/employee/competency-profile') }}/${trackerId}`)
        .then(response => response.text())
        .then(html => {
          // Extract training recommendations from response
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const training = doc.querySelector('[data-training-content]')?.textContent || 'No training details available';

          document.getElementById('trainingModalBody').innerHTML = `
            <div class="training-recommendation">
              <div class="d-flex align-items-start">
                <i class="bi bi-book text-primary me-2 mt-1"></i>
                <div>
                  <div class="fw-semibold">${training}</div>
                  <small class="text-muted">Recommended training for competency development</small>
                </div>
              </div>
              <div class="mt-3">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  <strong>Training Instructions:</strong>
                  <ul class="mb-0 mt-2">
                    <li>Click "Start Training" to begin the competency assessment</li>
                    <li>You will be presented with training questions</li>
                    <li>Complete all questions to improve your competency level</li>
                    <li>Your progress will be automatically updated upon completion</li>
                  </ul>
                </div>
              </div>
            </div>
          `;

          modal.show();
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error loading training details', 'error');
        });
    }

    // Start training function
    function startTraining() {
      if (!currentTrackerId) {
        showToast('No competency selected for training', 'error');
        return;
      }

      const startBtn = document.getElementById('startTrainingBtn');
      const originalContent = startBtn.innerHTML;

      // Show loading state
      startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Starting...';
      startBtn.disabled = true;

      // Call backend to start training
      fetch(`/employee/competency-profile/start-training/${currentTrackerId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          showToast(`Starting ${data.competency_name} training...`, 'success');

          // Close modal and redirect to exam
          const modal = bootstrap.Modal.getInstance(document.getElementById('trainingModal'));
          modal.hide();

          // Redirect to exam page
          window.location.href = data.redirect_url;
        } else {
          showToast(data.message || 'Failed to start training', 'error');
          console.error('Backend error:', data);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast(`Error starting training: ${error.message}`, 'error');
      })
      .finally(() => {
        startBtn.innerHTML = originalContent;
        startBtn.disabled = false;
      });
    }

    // Create training modal if it doesn't exist
    function createTrainingModal() {
      const modalHtml = `
        <div class="modal fade" id="trainingModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-book me-2"></i>Training Recommendations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body" id="trainingModalBody">
                <div class="text-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="startTrainingBtn" onclick="startTraining()">Start Training</button>
              </div>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHtml);
      return document.getElementById('trainingModal');
    }

    // Update summary cards with real-time data
    function updateSummaryCards(summary) {
      const cards = document.querySelectorAll('.stat-card h3');
      if (cards[0]) cards[0].textContent = summary.total_competencies;
      if (cards[1]) {
        cards[1].textContent = summary.average_progress + '%';
        // Update color based on progress
        const progressValue = parseFloat(summary.average_progress);
        if (progressValue >= 80) {
          cards[1].className = 'fw-bold mb-0 text-success';
        } else if (progressValue >= 60) {
          cards[1].className = 'fw-bold mb-0 text-primary';
        } else if (progressValue >= 40) {
          cards[1].className = 'fw-bold mb-0 text-warning';
        } else {
          cards[1].className = 'fw-bold mb-0 text-danger';
        }
      }
      if (cards[2]) cards[2].textContent = summary.needs_development;
      if (cards[3]) cards[3].textContent = summary.on_track;

      // Show additional metrics if available
      if (summary.training_completion_rate !== undefined) {
        showToast(`Training Completion Rate: ${summary.training_completion_rate}%`, 'info');
      }
      if (summary.certificate_count !== undefined) {
        showToast(`Certificates Earned: ${summary.certificate_count}`, 'success');
      }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
      // Create toast container if it doesn't exist
      let toastContainer = document.getElementById('toastContainer');
      if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
      }

      const toastId = 'toast-' + Date.now();
      const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';

      const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
          <div class="toast-header ${bgClass} text-white border-0">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <strong class="me-auto">Competency Tracker</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
          </div>
          <div class="toast-body">
            ${message}
          </div>
        </div>
      `;

      toastContainer.insertAdjacentHTML('beforeend', toastHtml);

      const toast = new bootstrap.Toast(document.getElementById(toastId));
      toast.show();

      // Remove toast element after it's hidden
      document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
      });
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
</body>
</html>
