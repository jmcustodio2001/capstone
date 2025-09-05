<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
            <h2 class="fw-bold mb-1">Employee Training Feedback Tracking</h2>
            <p class="text-muted mb-0">
              Monitor and analyze training feedback submitted by employees
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Admin Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Training Feedback</li>
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
              <div class="bg-success bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-star-fill fs-4 text-success"></i>
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
              <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-calendar-week fs-4 text-warning"></i>
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
              <div class="bg-info bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-hand-thumbs-up fs-4 text-info"></i>
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

    <!-- Training Feedback Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>Training Feedback Records</h5>
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
                      <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                        <span class="text-primary fw-bold">{{ substr(optional(optional($feedback)->employee)->first_name ?? 'U', 0, 1) }}{{ substr(optional(optional($feedback)->employee)->last_name ?? 'U', 0, 1) }}</span>
                      </div>
                      <div>
                        <div class="fw-bold">{{ optional(optional($feedback)->employee)->first_name ?? 'Unknown' }} {{ optional(optional($feedback)->employee)->last_name ?? 'User' }}</div>
                        <small class="text-muted">{{ optional(optional($feedback)->employee)->employee_id ?? 'N/A' }}</small>
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
                      <button class="btn btn-success btn-sm" onclick="markAsReviewed({{ optional($feedback)->id ?? 0 }})" title="Mark as Reviewed">
                        <i class="bi bi-check-circle"></i>
                      </button>
                      <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#respondModal" onclick="respondToFeedback({{ optional($feedback)->id ?? 0 }})" title="Respond">
                        <i class="bi bi-reply"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-chat-square-text fs-1 text-muted d-block mb-2"></i>
                    No training feedback submitted yet.
                  </td>
                </tr>
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
          <button class="btn btn-success" onclick="markCurrentAsReviewed()">Mark as Reviewed</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Respond to Feedback Modal -->
  <div class="modal fade" id="respondModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="respondForm" method="POST">
          @csrf
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title"><i class="bi bi-reply me-2"></i>Respond to Feedback</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Response</label>
              <textarea name="admin_response" class="form-control" rows="4" placeholder="Enter your response to this feedback..." required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Action Taken</label>
              <select name="action_taken" class="form-select">
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
                <input class="form-check-input" type="checkbox" name="notify_employee" id="notifyEmployee" checked>
                <label class="form-check-label" for="notifyEmployee">
                  Notify employee of response
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-warning" type="submit">Send Response</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentFeedbackId = null;

    // View Feedback Details
    function viewFeedbackDetails(feedbackId) {
      currentFeedbackId = feedbackId;
      fetch(`/admin/training-feedback/${feedbackId}`)
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
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('viewFeedbackContent').innerHTML = '<div class="alert alert-danger">Error loading feedback details.</div>';
        });
    }

    // Mark as Reviewed
    function markAsReviewed(feedbackId) {
      if (confirm('Mark this feedback as reviewed?')) {
        fetch(`/admin/training-feedback/${feedbackId}/review`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error marking feedback as reviewed');
          }
        });
      }
    }

    function markCurrentAsReviewed() {
      if (currentFeedbackId) {
        markAsReviewed(currentFeedbackId);
      }
    }

    // Respond to Feedback
    function respondToFeedback(feedbackId) {
      document.getElementById('respondForm').action = `/admin/training-feedback/${feedbackId}/respond`;
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

    // Export Feedback
    function exportFeedback() {
      window.location.href = '/admin/training-feedback/export';
    }

    // Refresh Data
    function refreshData() {
      location.reload();
    }

    // Form Submission
    document.getElementById('respondForm')?.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          bootstrap.Modal.getInstance(document.getElementById('respondModal')).hide();
          location.reload();
        } else {
          alert('Error sending response');
        }
      });
    });
  </script>

</body>
</html>