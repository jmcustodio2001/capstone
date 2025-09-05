<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Request Forms</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    .simulation-card {
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      border: none;
    }
    .card-header-custom {
      background-color: #f8f9fa;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
    }
    .table th {
      background-color: #f8f9fa;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

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
          <h2 class="fw-bold mb-1">Document Request Forms</h2>
          <p class="text-muted mb-0">Request official documents for personal, financial, or legal purposes.</p>
        </div>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Request Forms</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- ✅ Request Forms Table -->
  <div class="simulation-card card mb-4">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0">Document Request Records</h4>
      <!-- Add New Request Button -->
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRequestModal">
        <i class="bi bi-file-earmark-plus me-1"></i> Request Document
      </button>
    </div>
    <div class="card-body">

      <!-- 🔍 Search/Filter Bar -->
      <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="input-group w-50">
          <span class="input-group-text">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" id="requestSearch" class="form-control" placeholder="Search by document type, purpose, status, or date...">
          <button class="btn btn-outline-secondary" type="button" id="clearSearch" onclick="clearSearch()" style="display: none;">
            <i class="bi bi-x-circle"></i>
          </button>
        </div>
        <div id="searchResults" class="text-muted small" style="display: none;">
          <span id="resultCount">0</span> results found
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle" id="requestTable">
          <thead class="table-light">
            <tr>
              <th class="fw-bold">Request ID</th>
              <th class="fw-bold">Employee ID</th>
              <th class="fw-bold">Document Type</th>
              <th class="fw-bold">Purpose</th>
              <th class="fw-bold">Status</th>
              <th class="fw-bold">Requested Date</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($requests as $request)
              <tr>
                <td>{{ $request->request_id }}</td>
                <td>{{ $request->employee_id }}</td>
                <td>{{ $request->request_type }}</td>
                <td>{{ $request->reason }}</td>
                <td>
                  @if(strtolower($request->status) == 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                  @elseif(strtolower($request->status) == 'approved')
                    <span class="badge bg-success">Approved</span>
                  @else
                    <span class="badge bg-danger">Rejected</span>
                  @endif
                </td>
                <td>{{ $request->requested_date }}</td>
                <td class="text-center">
                  <!-- View Button -->
                  <button class="btn btn-info btn-sm text-white"
                    data-bs-toggle="modal"
                    data-bs-target="#viewRequestModal{{ $request->request_id }}">
                    <i class="bi bi-eye me-1"></i>View
                  </button>
                </td>
              </tr>

              <!-- ✅ View Request Modal -->
              <div class="modal fade" id="viewRequestModal{{ $request->request_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Request Details</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Request ID:</strong> {{ $request->request_id }}</li>
                        <li class="list-group-item"><strong>Employee ID:</strong> {{ $request->employee_id }}</li>
                        <li class="list-group-item"><strong>Document Type:</strong> {{ $request->request_type }}</li>
                        <li class="list-group-item"><strong>Purpose:</strong> {{ $request->reason }}</li>
                        <li class="list-group-item"><strong>Status:</strong> 
                          @if(strtolower($request->status) == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                          @elseif(strtolower($request->status) == 'approved')
                            <span class="badge bg-success">Approved</span>
                          @else
                            <span class="badge bg-danger">Rejected</span>
                          @endif
                        </li>
                        <li class="list-group-item"><strong>Requested Date:</strong> {{ $request->requested_date }}</li>
                      </ul>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="bi bi-file-earmark me-2"></i>No document requests found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- ✅ Add Request Modal -->
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Request Official Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('employee.requests.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <!-- Auto-fill Employee ID -->
          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">

          <!-- Auto-set Status as Pending -->
          <input type="hidden" name="status" value="Pending">

          <!-- Auto-set Requested Date -->
          <input type="hidden" name="requested_date" value="{{ now()->format('Y-m-d') }}">

          <div class="mb-3">
            <label for="requestType" class="form-label">Document Type</label>
            <select name="request_type" id="requestType" class="form-select" required>
              <option value="">-- Select Document Type --</option>
              <option value="Certificate of Employment (COE)">Certificate of Employment (COE)</option>
              <option value="Employment Verification Letter">Employment Verification Letter</option>
              <option value="Salary Certificate">Salary Certificate</option>
              <option value="Experience Letter">Experience Letter</option>
              <option value="Government-related forms (SSS)">Government-related forms (SSS)</option>
              <option value="Government-related forms (PhilHealth)">Government-related forms (PhilHealth)</option>
              <option value="Government-related forms (Pag-IBIG)">Government-related forms (Pag-IBIG)</option>
              <option value="Tax Certificate (BIR 2316)">Tax Certificate (BIR 2316)</option>
              <option value="Clearance Certificate">Clearance Certificate</option>
              <option value="Service Record">Service Record</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="reason" class="form-label">Purpose</label>
            <textarea name="reason" id="reason" rows="3" class="form-control" placeholder="Enter purpose for requesting this document (e.g., loan application, visa processing, new employment, etc.)..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Request Document</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 🔍 Search Filter Script -->
<script>
  // Enhanced search functionality with better filtering
  let searchTimeout;
  const searchInput = document.getElementById('requestSearch');
  const clearButton = document.getElementById('clearSearch');
  const searchResults = document.getElementById('searchResults');
  const resultCount = document.getElementById('resultCount');

  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const filter = this.value.toLowerCase().trim();
    
    // Show/hide clear button
    clearButton.style.display = filter ? 'block' : 'none';
    
    searchTimeout = setTimeout(() => {
      performSearch(filter);
    }, 300);
  });

  function performSearch(filter) {
    const rows = document.querySelectorAll("#requestTable tbody tr");
    let visibleCount = 0;
    let totalRows = 0;
    
    rows.forEach(row => {
      // Skip the "no records" row
      if (row.querySelector('td[colspan]')) {
        return;
      }
      
      totalRows++;
      const text = row.textContent.toLowerCase();
      const isVisible = !filter || text.includes(filter);
      row.style.display = isVisible ? "" : "none";
      if (isVisible) visibleCount++;
    });
    
    // Update search results counter
    if (filter) {
      searchResults.style.display = 'block';
      resultCount.textContent = visibleCount;
    } else {
      searchResults.style.display = 'none';
    }
    
    // Handle no results message
    const noResultsRow = document.querySelector("#requestTable tbody tr td[colspan]");
    if (noResultsRow) {
      if (filter && visibleCount === 0 && totalRows > 0) {
        noResultsRow.parentElement.style.display = "";
        noResultsRow.innerHTML = '<i class="bi bi-search me-2"></i>No document requests match your search criteria.';
      } else if (filter && visibleCount > 0) {
        noResultsRow.parentElement.style.display = "none";
      } else if (!filter) {
        // Reset to original message when no filter
        noResultsRow.innerHTML = '<i class="bi bi-file-earmark me-2"></i>No document requests found.';
        noResultsRow.parentElement.style.display = totalRows === 0 ? "" : "none";
      }
    }
  }

  // Clear search functionality
  function clearSearch() {
    searchInput.value = '';
    clearButton.style.display = 'none';
    searchResults.style.display = 'none';
    performSearch('');
  }

  // Initialize search on page load
  document.addEventListener('DOMContentLoaded', function() {
    performSearch('');
  });

  // Modal backdrop cleanup
  function removeAllModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
      backdrop.remove();
    });
    document.body.classList.remove('modal-open');
    document.body.style = '';
  }
  
  // Event listeners for modal cleanup
  window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
  document.querySelectorAll('.modal').forEach(function(modal) {
    modal.addEventListener('hidden.bs.modal', removeAllModalBackdrops);
  });
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
</script>

</body>
</html>
