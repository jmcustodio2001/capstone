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
            <h2 class="fw-bold mb-1">Competency Feedback Requests</h2>
            <p class="text-muted mb-0">
              Monitor and respond to employee competency feedback requests
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Admin Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Competency Feedback</li>
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
            <h3 class="fw-bold mb-1" id="totalRequests">{{ $totalRequests ?? 0 }}</h3>
            <p class="text-muted mb-0">Total Requests</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-clock-fill fs-4 text-warning"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="pendingRequests">{{ $pendingRequests ?? 0 }}</h3>
            <p class="text-muted mb-0">Pending</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-success bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-check-circle-fill fs-4 text-success"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="respondedRequests">{{ $respondedRequests ?? 0 }}</h3>
            <p class="text-muted mb-0">Responded</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="bg-info bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-calendar-week fs-4 text-info"></i>
              </div>
            </div>
            <h3 class="fw-bold mb-1" id="thisWeekRequests">{{ $thisWeekRequests ?? 0 }}</h3>
            <p class="text-muted mb-0">This Week</p>
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
            <label class="form-label fw-bold">Status</label>
            <select class="form-select" id="statusFilter">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="responded">Responded</option>
              <option value="closed">Closed</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Date Range</label>
            <select class="form-select" id="dateFilter">
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">&nbsp;</label>
            <button class="btn btn-primary w-100" onclick="applyFilters()">
              <i class="bi bi-funnel me-1"></i>Filter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Competency Feedback Requests Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>Competency Feedback Requests</h5>
        <div class="d-flex gap-2">
          <button class="btn btn-success btn-sm" onclick="exportRequests()">
            <i class="bi bi-download me-1"></i>Export
          </button>
          <button class="btn btn-info btn-sm" onclick="refreshData()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="requestsTable">
            <thead class="table-light">
              <tr>
                <th>Request ID</th>
                <th>Employee</th>
                <th>Competency</th>
                <th>Category</th>
                <th>Request Message</th>
                <th>Status</th>
                <th>Requested Date</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allRequests ?? [] as $request)
                <tr>
                  <td><span class="badge bg-primary">{{ $request->id }}</span></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                        <span class="text-primary fw-bold">{{ substr($request->employee->first_name ?? 'U', 0, 1) }}{{ substr($request->employee->last_name ?? 'U', 0, 1) }}</span>
                      </div>
                      <div>
                        <div class="fw-bold">{{ $request->employee->first_name ?? 'Unknown' }} {{ $request->employee->last_name ?? 'User' }}</div>
                        <small class="text-muted">{{ $request->employee->employee_id ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <strong>{{ $request->competency->competency_name ?? 'Unknown Competency' }}</strong>
                    @if($request->competency->description ?? null)
                      <br><small class="text-muted">{{ Str::limit($request->competency->description, 50) }}</small>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-info">{{ $request->competency->category ?? 'General' }}</span>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width: 200px;" title="{{ $request->request_message }}">
                      {{ $request->request_message ?? 'No message provided' }}
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'responded' ? 'success' : 'secondary') }}">
                      {{ ucfirst($request->status) }}
                    </span>
                  </td>
                  <td>
                    {{ $request->created_at->format('M d, Y') }}<br>
                    <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button class="btn btn-info btn-sm" onclick="viewRequestDetails({{ $request->id }})" title="View Details">
                        <i class="bi bi-eye"></i>
                      </button>
                      @if($request->status == 'pending')
                        <button class="btn btn-warning btn-sm" onclick="respondToRequest({{ $request->id }})" title="Respond">
                          <i class="bi bi-reply"></i>
                        </button>
                      @endif
                      <button class="btn btn-success btn-sm" onclick="markAsReviewed({{ $request->id }})" title="Mark as Reviewed">
                        <i class="bi bi-check-circle"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-chat-square-text fs-1 text-muted d-block mb-2"></i>
                    No competency feedback requests submitted yet.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- View Request Details Modal -->
  <div class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Request Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="viewRequestContent">
          <!-- Content loaded via AJAX -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-success" onclick="markCurrentAsReviewed()">Mark as Reviewed</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Respond to Request Modal -->
  <div class="modal fade" id="respondModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="respondForm" method="POST">
          @csrf
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title"><i class="bi bi-reply me-2"></i>Respond to Feedback Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Manager Response</label>
              <textarea name="manager_response" class="form-control" rows="4" placeholder="Provide feedback on the employee's competency progress..." required></textarea>
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
    let currentRequestId = null;

    // View Request Details
    function viewRequestDetails(requestId) {
      currentRequestId = requestId;
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
                
                <h6 class="fw-bold text-primary mt-4">Request Details</h6>
                <table class="table table-borderless">
                  <tr><td><strong>Status:</strong></td><td><span class="badge bg-${data.status == 'pending' ? 'warning' : (data.status == 'responded' ? 'success' : 'secondary')}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span></td></tr>
                  <tr><td><strong>Requested:</strong></td><td>${new Date(data.created_at).toLocaleDateString()}</td></tr>
                  <tr><td><strong>Responded:</strong></td><td>${data.responded_at ? new Date(data.responded_at).toLocaleDateString() : 'Not yet'}</td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6 class="fw-bold text-primary">Request Message</h6>
                <div class="mb-3">
                  <p class="text-muted border-start border-3 border-primary ps-3">${data.request_message || 'No message provided'}</p>
                </div>
                
                ${data.manager_response ? `
                  <h6 class="fw-bold text-success mt-4">Manager Response</h6>
                  <div class="alert alert-success">
                    <p class="mb-1">${data.manager_response}</p>
                    ${data.manager ? `<small><strong>Responded by:</strong> ${data.manager.name}</small>` : ''}
                  </div>
                ` : `
                  <div class="alert alert-warning">
                    <i class="bi bi-clock me-2"></i>
                    <strong>Pending Response</strong>
                    <p class="mb-0 mt-2">This request is waiting for manager feedback.</p>
                  </div>
                `}
              </div>
            </div>
          `;
          document.getElementById('viewRequestContent').innerHTML = content;
          
          // Show modal
          const modal = new bootstrap.Modal(document.getElementById('viewRequestModal'));
          modal.show();
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('viewRequestContent').innerHTML = '<div class="alert alert-danger">Error loading request details.</div>';
        });
    }

    // Respond to Request
    function respondToRequest(requestId) {
      document.getElementById('respondForm').action = `/admin/competency-feedback/${requestId}/respond`;
      const modal = new bootstrap.Modal(document.getElementById('respondModal'));
      modal.show();
    }

    // Mark as Reviewed
    function markAsReviewed(requestId) {
      if (confirm('Mark this request as reviewed?')) {
        fetch(`/admin/competency-feedback/${requestId}/review`, {
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
            alert('Error marking request as reviewed');
          }
        });
      }
    }

    function markCurrentAsReviewed() {
      if (currentRequestId) {
        markAsReviewed(currentRequestId);
      }
    }

    // Apply Filters
    function applyFilters() {
      const employee = document.getElementById('employeeFilter').value;
      const status = document.getElementById('statusFilter').value;
      const dateRange = document.getElementById('dateFilter').value;
      
      const params = new URLSearchParams();
      if (employee) params.append('employee', employee);
      if (status) params.append('status', status);
      if (dateRange) params.append('date_range', dateRange);
      
      window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    // Export Requests
    function exportRequests() {
      window.location.href = '/admin/competency-feedback/export';
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
