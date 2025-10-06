<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Claim & Reimbursement - Employee Portal</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --light-bg: #f8f9fa;
    }

    body {
      background-color: #f8f9fa !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .simulation-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      transition: transform 0.3s ease;
    }

    .simulation-card:hover {
      transform: translateY(-5px);
    }

    .card-header-custom {
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
      border-radius: 12px 12px 0 0 !important;
    }

    .badge-simulation {
      padding: 0.5em 0.8em;
      font-weight: 500;
      letter-spacing: 0.5px;
      border-radius: 6px;
    }

    .table th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #495057;
    }

    .table-hover tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }

    /* Enhanced table styling for more columns */
    .table th, .table td {
      white-space: nowrap;
      font-size: 0.875rem;
      padding: 0.5rem;
    }

    .table th {
      font-size: 0.8rem;
      font-weight: 600;
    }

    /* Specific column widths */
    .table th:nth-child(1), .table td:nth-child(1) { width: 60px; } /* ID */
    .table th:nth-child(2), .table td:nth-child(2) { width: 100px; } /* Employee ID */
    .table th:nth-child(3), .table td:nth-child(3) { width: 120px; } /* Claim Type */
    .table th:nth-child(4), .table td:nth-child(4) { width: 100px; } /* Amount */
    .table th:nth-child(5), .table td:nth-child(5) { width: 100px; } /* Claim Date */
    .table th:nth-child(6), .table td:nth-child(6) { width: 200px; white-space: normal; } /* Description */
    .table th:nth-child(7), .table td:nth-child(7) { width: 150px; } /* Receipt Path */
    .table th:nth-child(8), .table td:nth-child(8) { width: 80px; } /* Status */
    .table th:nth-child(9), .table td:nth-child(9) { width: 120px; } /* Approved By */
    .table th:nth-child(10), .table td:nth-child(10) { width: 100px; } /* Approved At */
    .table th:nth-child(11), .table td:nth-child(11) { width: 150px; white-space: normal; } /* Admin Notes */
    .table th:nth-child(12), .table td:nth-child(12) { width: 100px; } /* Created At */
    .table th:nth-child(13), .table td:nth-child(13) { width: 100px; } /* Updated At */

    .status-pending {
      background-color: rgba(255,193,7,0.15);
      color: #856404;
    }

    .status-approved {
      background-color: rgba(40,167,69,0.15);
      color: #155724;
    }

    .status-rejected {
      background-color: rgba(220,53,69,0.15);
      color: #721c24;
    }

    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 2rem;
    }

    .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .page-link {
      color: var(--primary-color);
    }
    /* Color borders for claim types */
    .claim-type-border {
      display: inline-block;
      padding: 0.25em 1em;
      border-width: 3px;
      border-style: solid;
      border-radius: 8px;
      background: #fff;
      font-weight: 500;
    }
    .claim-type-transportation { border-color: #4cc9f0; }
    .claim-type-travel-expense { border-color: #4361ee; }
    .claim-type-meal-allowance { border-color: #f72585; }
    .claim-type-accommodation { border-color: #3f37c9; }
    .claim-type-medical-expense { border-color: #43aa8b; }
    .claim-type-office-supplies { border-color: #ffbe0b; }
    .claim-type-training-materials { border-color: #720026; }
    .claim-type-communication-expense { border-color: #b5179e; }
    .claim-type-other { border-color: #adb5bd; }
    .action-btn-group .btn {
      min-width: 100px;
      justify-content: center;
      align-items: center;
      display: flex;
      font-weight: 500;
    }
    .action-btn-group .btn i {
      margin-right: 0.4em;
    }
    .action-btn-group .btn-outline-info {
      border-color: #4cc9f0;
      color: #4361ee;
    }
    .action-btn-group .btn-outline-info:hover {
      background: #4cc9f0;
      color: #fff;
    }
    .action-btn-group .btn-outline-primary {
      border-color: #3f37c9;
      color: #3f37c9;
    }
    .action-btn-group .btn-outline-primary:hover {
      background: #3f37c9;
      color: #fff;
    }
    .action-btn-group .btn-outline-success {
      border-color: #43aa8b;
      color: #43aa8b;
    }
    .action-btn-group .btn-outline-success:hover {
      background: #43aa8b;
      color: #fff;
    }
    .action-btn-group .btn-outline-danger {
      border-color: #f72585;
      color: #f72585;
    }
    .action-btn-group .btn-outline-danger:hover {
      background: #f72585;
      color: #fff;
    }

    /* SweetAlert2 Custom Styling */
    .swal2-popup-custom {
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }

    .swal2-title-custom {
      color: var(--primary-color);
      font-weight: 600;
    }

    .swal2-html-container {
      font-size: 14px;
      line-height: 1.5;
    }

    .swal2-confirm {
      border-radius: 8px !important;
      padding: 10px 20px !important;
      font-weight: 500 !important;
    }

    .swal2-cancel {
      border-radius: 8px !important;
      padding: 10px 20px !important;
      font-weight: 500 !important;
    }

    /* Enhanced form styling in SweetAlert */
    .swal2-html-container .form-control {
      border-radius: 8px;
      border: 1px solid #dee2e6;
      padding: 10px 12px;
      font-size: 14px;
    }

    .swal2-html-container .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    .swal2-html-container .form-select {
      border-radius: 8px;
      border: 1px solid #dee2e6;
      padding: 10px 12px;
      font-size: 14px;
    }

    .swal2-html-container .btn {
      border-radius: 6px;
      font-weight: 500;
      padding: 8px 16px;
    }

    .swal2-html-container .alert {
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 16px;
    }

    /* Print Styles */
    @media print {
      body * {
        visibility: hidden;
      }

      .print-area, .print-area * {
        visibility: visible;
      }

      .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }

      .no-print {
        display: none !important;
      }

      .table {
        font-size: 12px;
      }

      .page-break {
        page-break-after: always;
      }

      @page {
        margin: 1cm;
        size: A4 landscape;
      }

      .print-header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
      }

      .print-header h2 {
        margin: 0;
        font-size: 18px;
      }

      .print-date {
        text-align: right;
        font-size: 10px;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<main id="main-content" class="expanded" style="margin-left: 280px; padding: 2rem; margin-top: 3.5rem; transition: margin-left 0.3s cubic-bezier(.4,2,.6,1);">
<style>
#main-content.expanded {
  margin-left: 0 !important;
  transition: margin-left 0.3s cubic-bezier(.4,2,.6,1);
}
#main-content.collapsed {
  margin-left: 280px !important;
  transition: margin-left 0.3s cubic-bezier(.4,2,.6,1);
}
</style>
<script>
// Sidebar toggle logic to expand/collapse main content
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.querySelector('.sidebar, #sidebar');
  const mainContent = document.getElementById('main-content');
  const toggleBtn = document.querySelector('.sidebar-toggle, #sidebarToggle, .toggle-sidebar');
  function updateMainContent() {
    if (sidebar && sidebar.classList.contains('collapsed')) {
      mainContent.classList.add('expanded');
      mainContent.classList.remove('collapsed');
      mainContent.style.marginLeft = '0';
    } else {
      mainContent.classList.remove('expanded');
      mainContent.classList.add('collapsed');
      mainContent.style.marginLeft = '280px';
    }
  }
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      setTimeout(updateMainContent, 10);
    });
  }
  // Initial state
  updateMainContent();
});
</script>
  <!-- Page Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">Claim & Reimbursement</h2>
          <p class="text-muted mb-0">
            Submit and track your claims and reimbursements.
          </p>
        </div>
      </div>
      <div class="d-flex align-items-center">
        <button class="btn btn-primary me-3" onclick="newClaimWithConfirmation()">
          <i class="bi bi-plus-circle me-1"></i> New Claim
        </button>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Claim & Reimbursement</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-primary mb-2">
            <i class="bi bi-file-earmark-text"></i>
          </div>
          <h5 class="card-title">Total Claims</h5>
          <h3 class="text-primary mb-0">{{ $totalClaims ?? 0 }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-warning mb-2">
            <i class="bi bi-clock-history"></i>
          </div>
          <h5 class="card-title">Pending</h5>
          <h3 class="text-warning mb-0">{{ $pendingClaims ?? 0 }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-success mb-2">
            <i class="bi bi-check-circle"></i>
          </div>
          <h5 class="card-title">Approved</h5>
          <h3 class="text-success mb-0">{{ $approvedClaims ?? 0 }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-info mb-2">
            <i class="bi bi-currency-dollar"></i>
          </div>
          <h5 class="card-title">Total Approved</h5>
          <h3 class="text-info mb-0">₱{{ number_format($totalAmount ?? 0, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="filter-container bg-white p-3 rounded shadow-sm mb-4">
    <div class="row">
      <div class="col-md-3 mb-2">
        <label for="month-filter" class="form-label">Month</label>
        <select class="form-select" id="month-filter">
          <option value="">All Months</option>
          <option value="1">January</option>
          <option value="2">February</option>
          <option value="3">March</option>
          <option value="4">April</option>
          <option value="5">May</option>
          <option value="6">June</option>
          <option value="7">July</option>
          <option value="8">August</option>
          <option value="9">September</option>
          <option value="10">October</option>
          <option value="11">November</option>
          <option value="12">December</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label for="year-filter" class="form-label">Year</label>
        <select class="form-select" id="year-filter">
          <option value="">All Years</option>
          <option value="2024" selected>2024</option>
          <option value="2023">2023</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label for="status-filter" class="form-label">Status</label>
        <select class="form-select" id="status-filter">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Approved">Approved</option>
          <option value="Rejected">Rejected</option>
        </select>
      </div>
      <div class="col-md-3 mb-2 d-flex align-items-end">
        <button id="reset-filters" class="btn btn-outline-secondary w-100">Reset Filters</button>
      </div>
    </div>
  </div>

  <!-- ✅ Claim & Reimbursement Table -->
  <div class="simulation-card card mb-4">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0">Claim & Reimbursement Records</h4>
      <div>
        <button class="btn btn-sm btn-outline-primary me-2" onclick="exportToCSV()">
          <i class="bi bi-download me-1"></i> Export
        </button>
        <button class="btn btn-sm btn-outline-secondary" onclick="printTable()">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="claim-table">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Employee ID</th>
              <th>Claim Type ID</th>
              <th>Amount</th>
              <th>Claim Date</th>
              <th>Description</th>
              <th>Receipt Path</th>
              <th>Status</th>
              <th>Approved By</th>
              <th>Approved At</th>
              <th>Admin Notes</th>
              <th>Created At</th>
              <th>Updated At</th>
            </tr>
          </thead>
          <tbody>
            @forelse($claims as $claim)
              <tr>
                <td>{{ $claim->id }}</td>
                <td>{{ $claim->employee_id }}</td>
                <td>{{ $claim->claim_type }}</td>
                <td>₱{{ number_format($claim->amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->claim_date)->format('M d, Y') }}</td>
                <td>{{ Str::limit($claim->description, 50) }}</td>
                <td>{{ $claim->receipt_file ?? '---' }}</td>
                <td>
                  <span class="badge badge-simulation status-{{ strtolower($claim->status) }}">
                    {{ $claim->status }}
                  </span>
                </td>
                <td>{{ $claim->approver ? $claim->approver->name : '---' }}</td>
                <td>{{ $claim->approved_date ? \Carbon\Carbon::parse($claim->approved_date)->format('M d, Y') : '---' }}</td>
                <td>{{ $claim->remarks ?? '---' }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->created_at)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->updated_at)->format('M d, Y') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="13" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No claims found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-container">
        @if(method_exists($claims, 'links'))
          {{ $claims->links() }}
        @endif
      </div>
    </div>
  </div>
</main>

<!-- Fallback New Claim Modal (kept for compatibility) -->
<div class="modal fade" id="newClaimModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="card-header modal-header">
        <h5 class="modal-title">Submit New Claim</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="newClaimForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Note:</strong> This form is now enhanced with SweetAlert. Use the "New Claim" button above for the improved experience.
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="claim_type" class="form-label">Claim Type <span class="text-danger">*</span></label>
              <select class="form-select" id="claim_type" name="claim_type" required>
                <option value="">Select claim type</option>
                <option value="Travel Expense">Travel Expense</option>
                <option value="Meal Allowance">Meal Allowance</option>
                <option value="Transportation">Transportation</option>
                <option value="Accommodation">Accommodation</option>
                <option value="Medical Expense">Medical Expense</option>
                <option value="Office Supplies">Office Supplies</option>
                <option value="Training Materials">Training Materials</option>
                <option value="Communication Expense">Communication Expense</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="999999.99" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="claim_date" class="form-label">Claim Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="claim_date" name="claim_date" max="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000" required placeholder="Provide detailed description of the expense..."></textarea>
            <div class="form-text">Maximum 1000 characters</div>
          </div>
          <div class="mb-3">
            <label for="receipt_file" class="form-label">Receipt/Document</label>
            <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" onchange="validateFileSize(this, 5)">
            <div class="form-text">Upload receipt or supporting document (JPG, PNG, PDF - Max 5MB)</div>
            <div class="invalid-feedback" id="fallback-file-error"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
            Submit Claim (with Password Verification)
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // CSRF Token Setup
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Toast notification function
  function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    const bgClass = type === 'error' || type === 'danger' ? 'bg-danger' : 'bg-success';
    toast.className = `toast align-items-center text-white ${bgClass} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
  }

  function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
  }

  // Filter functionality with null checks
  const monthFilter = document.getElementById('month-filter');
  const yearFilter = document.getElementById('year-filter');
  const statusFilter = document.getElementById('status-filter');
  const resetFilters = document.getElementById('reset-filters');

  if (monthFilter) monthFilter.addEventListener('change', filterTable);
  if (yearFilter) yearFilter.addEventListener('change', filterTable);
  if (statusFilter) statusFilter.addEventListener('change', filterTable);

  if (resetFilters) {
    resetFilters.addEventListener('click', function() {
      if (monthFilter) monthFilter.value = '';
      if (yearFilter) yearFilter.value = '2024';
      if (statusFilter) statusFilter.value = '';
      filterTable();
    });
  }

  function filterTable() {
    const month = monthFilter ? monthFilter.value : '';
    const year = yearFilter ? yearFilter.value : '';
    const status = statusFilter ? statusFilter.value : '';

    const rows = document.querySelectorAll('#claim-table tbody tr');
    rows.forEach(row => {
      let show = true;
      const dateCell = row.cells[1]?.textContent;
      const statusCell = row.cells[4]?.textContent.trim();

      if (month && dateCell) {
        const date = new Date(dateCell);
        if (date.getMonth() + 1 != month) show = false;
      }

      if (year && dateCell) {
        const date = new Date(dateCell);
        if (date.getFullYear() != year) show = false;
      }

      if (status && statusCell !== status) show = false;

      row.style.display = show ? '' : 'none';
    });
  }

  // Store form data temporarily for submission after password verification
  let pendingFormData = null;
  let pendingFormElement = null;
  let pendingAction = null;
  let pendingClaimId = null;

  // New Claim with Confirmation
  async function newClaimWithConfirmation() {
    const result = await Swal.fire({
      title: 'Submit New Claim',
      html: `
        <div class="alert alert-info text-start">
          <i class="bi bi-info-circle me-2"></i>
          You are about to submit a new claim request. Please ensure all information is accurate.
        </div>
        <p class="text-muted">Click "Continue" to proceed with the claim form.</p>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#4361ee',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-plus-circle me-1"></i>Continue',
      cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
      customClass: {
        popup: 'swal2-popup-custom'
      }
    });

    if (result.isConfirmed) {
      showNewClaimForm();
    }
  }

  // Show New Claim Form
  async function showNewClaimForm() {
    const { value: formValues } = await Swal.fire({
      title: 'Submit New Claim',
      html: `
        <form id="swalNewClaimForm" class="text-start">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="swal_claim_type" class="form-label">Claim Type <span class="text-danger">*</span></label>
              <select class="form-select" id="swal_claim_type" name="claim_type" required>
                <option value="">Select claim type</option>
                <option value="Travel Expense">Travel Expense</option>
                <option value="Meal Allowance">Meal Allowance</option>
                <option value="Transportation">Transportation</option>
                <option value="Accommodation">Accommodation</option>
                <option value="Medical Expense">Medical Expense</option>
                <option value="Office Supplies">Office Supplies</option>
                <option value="Training Materials">Training Materials</option>
                <option value="Communication Expense">Communication Expense</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="swal_amount" class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" class="form-control" id="swal_amount" name="amount" step="0.01" min="0.01" max="999999.99" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="swal_claim_date" class="form-label">Claim Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="swal_claim_date" name="claim_date" max="${new Date().toISOString().split('T')[0]}" required>
          </div>
          <div class="mb-3">
            <label for="swal_description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="swal_description" name="description" rows="3" maxlength="1000" required placeholder="Provide detailed description of the expense..."></textarea>
            <div class="form-text">Maximum 1000 characters</div>
          </div>
          <div class="mb-3">
            <label for="swal_receipt_file" class="form-label">Receipt/Document</label>
            <input type="file" class="form-control" id="swal_receipt_file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" onchange="validateFileSize(this, 5)">
            <div class="form-text">Upload receipt or supporting document (JPG, PNG, PDF - Max 5MB)</div>
            <div class="invalid-feedback" id="file-error"></div>
          </div>
          <div class="alert alert-warning">
            <i class="bi bi-shield-lock me-2"></i>
            <strong>Security Notice:</strong> You will need to verify your password to submit this claim.
          </div>
        </form>
      `,
      width: '800px',
      showCancelButton: true,
      confirmButtonColor: '#4361ee',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-shield-lock me-1"></i>Verify Password & Submit',
      cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
      preConfirm: () => {
        const form = document.getElementById('swalNewClaimForm');
        const formData = new FormData(form);
        
        // Validate required fields
        if (!formData.get('claim_type')) {
          Swal.showValidationMessage('Please select a claim type');
          return false;
        }
        if (!formData.get('amount') || parseFloat(formData.get('amount')) <= 0) {
          Swal.showValidationMessage('Please enter a valid amount');
          return false;
        }
        if (!formData.get('claim_date')) {
          Swal.showValidationMessage('Please select a claim date');
          return false;
        }
        if (!formData.get('description') || formData.get('description').trim().length < 10) {
          Swal.showValidationMessage('Please provide a detailed description (minimum 10 characters)');
          return false;
        }
        
        // Validate file size if file is selected
        const fileInput = document.getElementById('swal_receipt_file');
        if (fileInput && fileInput.files[0]) {
          const file = fileInput.files[0];
          const fileSizeMB = file.size / (1024 * 1024);
          if (fileSizeMB > 5) {
            Swal.showValidationMessage(`File size (${fileSizeMB.toFixed(2)}MB) exceeds the maximum limit of 5MB. Please choose a smaller file.`);
            return false;
          }
        }
        
        return formData;
      },
      didOpen: () => {
        // Set default date to today
        document.getElementById('swal_claim_date').value = new Date().toISOString().split('T')[0];
      }
    });

    if (formValues) {
      pendingFormData = formValues;
      pendingAction = 'create';
      await verifyEmployeePasswordForClaim();
    }
  }

  // New Claim Form Submission (keeping original for fallback)
  const newClaimFormElement = document.getElementById('newClaimForm');
  if (newClaimFormElement) {
    newClaimFormElement.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Store form data for later submission
      pendingFormData = new FormData(this);
      pendingFormElement = this;
      pendingAction = 'create';

      await verifyEmployeePasswordForClaim();
    });
  }

  // Employee Password Verification for Claims
  async function verifyEmployeePasswordForClaim() {
    const { value: password } = await Swal.fire({
      title: 'Password Verification Required',
      html: `
        <div class="alert alert-warning text-start">
          <i class="bi bi-shield-lock me-2"></i>
          <strong>Security Notice:</strong> Please enter your password to confirm this claim operation.
        </div>
        <div class="mb-3">
          <label for="swal_password" class="form-label">Enter Your Password</label>
          <input type="password" class="form-control" id="swal_password" placeholder="Enter your password" minlength="3">
          <div class="form-text">Minimum 3 characters required</div>
        </div>
      `,
      showCancelButton: true,
      confirmButtonColor: '#4361ee',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Verify & Continue',
      cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
      preConfirm: () => {
        const password = document.getElementById('swal_password').value;
        if (!password || password.length < 3) {
          Swal.showValidationMessage('Password is required (minimum 3 characters)');
          return false;
        }
        return password;
      },
      didOpen: () => {
        document.getElementById('swal_password').focus();
      }
    });

    if (password) {
      await submitWithPasswordVerification(password);
    }
  }

  // Submit with Password Verification
  async function submitWithPasswordVerification(password) {
    // Show loading
    Swal.fire({
      title: 'Verifying Password...',
      text: 'Please wait while we verify your credentials.',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      // Verify password with server
      const verifyResponse = await fetch('/employee/verify-password', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ password: password })
      });

      const verifyResult = await verifyResponse.json();

      if (verifyResponse.ok && (verifyResult.success || verifyResult.valid)) {
        // Password verified, proceed with action
        if (pendingAction === 'create') {
          await submitClaimAfterVerification();
        } else if (pendingAction === 'edit') {
          await submitEditClaimAfterVerification();
        } else if (pendingAction === 'cancel') {
          await submitCancelClaimAfterVerification();
        }
      } else {
        // Password verification failed
        await Swal.fire({
          title: 'Invalid Password',
          text: verifyResult.message || 'The password you entered is incorrect. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545',
          confirmButtonText: 'Try Again'
        });
        
        // Retry password verification
        await verifyEmployeePasswordForClaim();
      }
    } catch (error) {
      console.error('Password verification error:', error);
      await Swal.fire({
        title: 'Verification Error',
        text: 'Network error during password verification. Please check your connection and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    }
  }

  // Submit Edit Claim After Verification
  async function submitEditClaimAfterVerification() {
    // Show loading
    Swal.fire({
      title: 'Updating Claim...',
      text: 'Please wait while we process your changes.',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      pendingFormData.append('_method', 'PUT');

      const response = await fetch(`/employee/claim-reimbursements/${pendingClaimId}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: pendingFormData
      });

      let result;
      const contentType = response.headers.get('content-type');
      
      try {
        if (contentType && contentType.includes('application/json')) {
          result = await response.json();
        } else {
          const textResponse = await response.text();
          if (response.ok) {
            result = { success: true, message: 'Claim updated successfully!' };
          } else {
            throw new Error('Server returned invalid response format');
          }
        }
      } catch (parseError) {
        if (response.ok) {
          result = { success: true, message: 'Claim updated successfully!' };
        } else {
          throw new Error('Invalid response format');
        }
      }

      if (response.ok && result.success) {
        await Swal.fire({
          title: 'Claim Updated Successfully!',
          text: result.message || 'Your claim has been updated and is pending approval.',
          icon: 'success',
          confirmButtonColor: '#4361ee',
          confirmButtonText: 'OK'
        });

        // Reload page to show updated data
        setTimeout(() => location.reload(), 500);
      } else {
        let errorMessage = 'Error updating claim. Please try again.';
        let showValidationErrors = false;
        
        // Check for validation errors first
        if (result && result.errors) {
          console.error('Validation errors:', result.errors);
          showValidationErrors = true;
          
          // Display specific validation errors with user-friendly messages
          let validationErrors = [];
          for (const [field, messages] of Object.entries(result.errors)) {
            // Convert technical field names to user-friendly names
            const friendlyFieldNames = {
              'receipt_file': 'Receipt file',
              'claim_type': 'Claim type',
              'amount': 'Amount',
              'claim_date': 'Claim date',
              'description': 'Description'
            };
            
            const fieldName = friendlyFieldNames[field] || field;
            const friendlyMessages = messages.map(msg => {
              // Convert technical messages to user-friendly ones
              if (msg.includes('5120 kilobytes') || msg.includes('5120 KB') || msg.includes('greater than 5120')) {
                return `File size must not exceed 5MB. Please choose a smaller file.`;
              }
              if (msg.includes('mimes:jpg,jpeg,png,pdf')) {
                return `File must be a JPG, PNG, or PDF document.`;
              }
              if (msg.includes('max:5120')) {
                return `File size must not exceed 5MB. Please choose a smaller file.`;
              }
              return msg;
            });
            
            validationErrors = validationErrors.concat(friendlyMessages);
          }
          
          if (validationErrors.length > 0) {
            await Swal.fire({
              title: 'Validation Errors',
              html: '<ul class="text-start"><li>' + validationErrors.join('</li><li>') + '</li></ul>',
              icon: 'warning',
              confirmButtonColor: '#ffc107',
              confirmButtonText: 'OK'
            });
          }
        }
        
        // Show general error message only if no validation errors were shown
        if (!showValidationErrors) {
          if (result && result.message) {
            errorMessage = result.message;
          } else if (response.status === 422) {
            errorMessage = 'Validation failed. Please check your input and try again.';
          } else if (response.status === 401) {
            errorMessage = 'Authentication failed. Please refresh the page and log in again.';
          } else if (response.status === 500) {
            errorMessage = 'Server error occurred. Please try again later.';
          }
          
          await Swal.fire({
            title: 'Update Failed',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
          });
        }
      }
    } catch (error) {
      console.error('Edit claim error:', error);
      await Swal.fire({
        title: 'Update Error',
        text: 'Unable to update claim. Please check your connection and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    }
  }

  // Submit claim after password verification
  async function submitClaimAfterVerification() {
    // Show loading alert
    Swal.fire({
      title: 'Submitting Claim...',
      text: 'Please wait while we process your request.',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      // Debug the form data being sent
      console.log('CSRF Token:', csrfToken);
      console.log('Form data entries:');
      for (let [key, value] of pendingFormData.entries()) {
        console.log(key, ':', value);
      }

      const response = await fetch('{{ route("employee.claim_reimbursements.store") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: pendingFormData
      });

      // Enhanced debug logging for submission
      console.log('Submit response status:', response.status);
      console.log('Submit response ok:', response.ok);
      console.log('Submit response headers:', response.headers.get('content-type'));

      let result;
      const contentType = response.headers.get('content-type');
      
      try {
        // Check if response is JSON
        if (contentType && contentType.includes('application/json')) {
          result = await response.json();
          console.log('Submit result:', result);
        } else {
          // Handle non-JSON responses (like redirects or HTML)
          const textResponse = await response.text();
          console.log('Submit raw response:', textResponse);
          
          // Check if it's a session expiry redirect
          if (textResponse.includes('login') || textResponse.includes('session') || response.status === 302) {
            await Swal.fire({
              title: 'Session Expired',
              text: 'Your session has expired. Please refresh the page and log in again.',
              icon: 'warning',
              confirmButtonColor: '#ffc107',
              confirmButtonText: 'Refresh Page'
            });
            setTimeout(() => location.reload(), 500);
            return;
          }
          
          // If response is OK but not JSON, assume success
          if (response.ok) {
            result = { success: true, message: 'Claim submitted successfully!' };
          } else {
            throw new Error('Server returned invalid response format');
          }
        }
      } catch (parseError) {
        console.error('Submit JSON parse error:', parseError);
        const textResponse = await response.text();
        console.log('Submit raw response:', textResponse);
        
        // Check for session expiry in text response
        if (textResponse.includes('login') || textResponse.includes('session')) {
          await Swal.fire({
            title: 'Session Expired',
            text: 'Your session has expired. Please refresh the page and log in again.',
            icon: 'warning',
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Refresh Page'
          });
          setTimeout(() => location.reload(), 500);
          return;
        }
        
        throw new Error('Server returned invalid response format');
      }

      // Check if the submission was successful
      if (response.ok && result && result.success) {
        await Swal.fire({
          title: 'Claim Submitted Successfully!',
          text: result.message || 'Your claim has been submitted and is pending approval.',
          icon: 'success',
          confirmButtonColor: '#4361ee',
          confirmButtonText: 'OK'
        });

        // Close modal and reset form (if using fallback modal)
        const newClaimModalElement = document.getElementById('newClaimModal');
        if (newClaimModalElement) {
          const newClaimModal = bootstrap.Modal.getInstance(newClaimModalElement);
          if (newClaimModal) {
            newClaimModal.hide();
          }
        }
        
        const newClaimFormElement = document.getElementById('newClaimForm');
        if (newClaimFormElement) {
          newClaimFormElement.reset();
        }

        // Reload page to show updated data
        setTimeout(() => location.reload(), 500);
      } else {
        // Handle server-side errors
        let errorMessage = 'Error submitting claim. Please try again.';
        let showValidationErrors = false;
        
        // Check for validation errors first
        if (result && result.errors) {
          console.error('Validation errors:', result.errors);
          showValidationErrors = true;
          
          // Display specific validation errors with user-friendly messages
          let validationErrors = [];
          for (const [field, messages] of Object.entries(result.errors)) {
            // Convert technical field names to user-friendly names
            const friendlyFieldNames = {
              'receipt_file': 'Receipt file',
              'claim_type': 'Claim type',
              'amount': 'Amount',
              'claim_date': 'Claim date',
              'description': 'Description'
            };
            
            const fieldName = friendlyFieldNames[field] || field;
            const friendlyMessages = messages.map(msg => {
              // Convert technical messages to user-friendly ones
              if (msg.includes('5120 kilobytes') || msg.includes('5120 KB') || msg.includes('greater than 5120')) {
                return `File size must not exceed 5MB. Please choose a smaller file.`;
              }
              if (msg.includes('mimes:jpg,jpeg,png,pdf')) {
                return `File must be a JPG, PNG, or PDF document.`;
              }
              if (msg.includes('max:5120')) {
                return `File size must not exceed 5MB. Please choose a smaller file.`;
              }
              return msg;
            });
            
            validationErrors = validationErrors.concat(friendlyMessages);
          }
          
          if (validationErrors.length > 0) {
            await Swal.fire({
              title: 'Validation Errors',
              html: '<ul class="text-start"><li>' + validationErrors.join('</li><li>') + '</li></ul>',
              icon: 'warning',
              confirmButtonColor: '#ffc107',
              confirmButtonText: 'OK'
            });
          }
        }
        
        // Show general error message only if no validation errors were shown
        if (!showValidationErrors) {
          if (result && result.message) {
            errorMessage = result.message;
          } else if (response.status === 422) {
            errorMessage = 'Validation failed. Please check your input and try again.';
          } else if (response.status === 401) {
            errorMessage = 'Authentication failed. Please refresh the page and log in again.';
          } else if (response.status === 500) {
            errorMessage = 'Server error occurred. Please try again later.';
          }
          
          await Swal.fire({
            title: 'Submission Failed',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
          });
        }
      }
    } catch (error) {
      console.error('Claim submission error:', error);
      
      // Show appropriate error message
      await Swal.fire({
        title: 'Submission Error',
        text: error.message || 'Unable to submit claim. Please check your connection and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    } finally {
      // Reset password modal (if it exists)
      const verificationPassword = document.getElementById('verification_password');
      const passwordError = document.getElementById('passwordError');
      const confirmVerification = document.getElementById('confirmVerification');
      
      if (verificationPassword) {
        verificationPassword.value = '';
        verificationPassword.classList.remove('is-invalid');
      }
      if (passwordError) {
        passwordError.textContent = '';
      }
      if (confirmVerification) {
        confirmVerification.disabled = false;
        confirmVerification.innerHTML = '<i class="bi bi-check-circle me-1"></i>Verify & Submit';
      }
    }
  }

  // Legacy form handlers (kept for compatibility)
  // Set default claim date to today
  if (document.getElementById('claim_date')) {
    document.getElementById('claim_date').value = new Date().toISOString().split('T')[0];
  }

  // View Claim Details with Enhanced SweetAlert
  async function viewClaimDetails(claimId) {
    try {
      // Show loading
      Swal.fire({
        title: 'Loading Claim Details...',
        text: 'Please wait while we fetch the claim information.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const response = await fetch(`/employee/claim-reimbursements/${claimId}`);
      const result = await response.json();

      if (result.success) {
        const claim = result.claim;
        
        await Swal.fire({
          title: 'Claim Details',
          html: `
            <div class="text-start">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <strong><i class="bi bi-hash me-1"></i>Claim ID:</strong><br>
                  <span class="text-muted">${claim.claim_id}</span>
                </div>
                <div class="col-md-6 mb-3">
                  <strong><i class="bi bi-flag me-1"></i>Status:</strong><br>
                  <span class="badge ${getStatusBadgeClass(claim.status)}">${claim.status}</span>
                </div>
                <div class="col-md-6 mb-3">
                  <strong><i class="bi bi-tag me-1"></i>Type:</strong><br>
                  <span class="text-muted">${claim.claim_type}</span>
                </div>
                <div class="col-md-6 mb-3">
                  <strong><i class="bi bi-currency-dollar me-1"></i>Amount:</strong><br>
                  <span class="text-success fw-bold">${claim.amount}</span>
                </div>
                <div class="col-md-6 mb-3">
                  <strong><i class="bi bi-calendar me-1"></i>Claim Date:</strong><br>
                  <span class="text-muted">${claim.claim_date}</span>
                </div>
                <div class="col-md-6 mb-3">
                  <strong><i class="bi bi-clock me-1"></i>Date Filed:</strong><br>
                  <span class="text-muted">${claim.processed_date || 'N/A'}</span>
                </div>
                <div class="col-12 mb-3">
                  <strong><i class="bi bi-file-text me-1"></i>Description:</strong><br>
                  <div class="mt-2 p-2 bg-light rounded">${claim.description}</div>
                </div>
                ${claim.receipt_file ? `
                  <div class="col-12 mb-3">
                    <strong><i class="bi bi-paperclip me-1"></i>Receipt:</strong><br>
                    <a href="/employee/claim-reimbursements/${claim.id}/download-receipt" class="btn btn-sm btn-outline-primary mt-2">
                      <i class="bi bi-download me-1"></i> Download Receipt
                    </a>
                  </div>
                ` : ''}
                ${claim.approved_by ? `
                  <div class="col-md-6 mb-3">
                    <strong><i class="bi bi-person-check me-1"></i>Approved By:</strong><br>
                    <span class="text-success">${claim.approved_by}</span>
                  </div>
                  <div class="col-md-6 mb-3">
                    <strong><i class="bi bi-calendar-check me-1"></i>Approved Date:</strong><br>
                    <span class="text-success">${claim.approved_date}</span>
                  </div>
                ` : ''}
                ${claim.rejected_reason ? `
                  <div class="col-12 mb-3">
                    <strong><i class="bi bi-x-circle me-1"></i>Rejection Reason:</strong><br>
                    <div class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger rounded text-danger">${claim.rejected_reason}</div>
                  </div>
                ` : ''}
                ${claim.remarks ? `
                  <div class="col-12 mb-3">
                    <strong><i class="bi bi-chat-dots me-1"></i>Remarks:</strong><br>
                    <div class="mt-2 p-2 bg-info bg-opacity-10 border border-info rounded">${claim.remarks}</div>
                  </div>
                ` : ''}
              </div>
            </div>
          `,
          width: '700px',
          confirmButtonColor: '#4361ee',
          confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Close',
          customClass: {
            popup: 'swal2-popup-custom'
          }
        });
      } else {
        await Swal.fire({
          title: 'Error Loading Claim',
          text: result.message || 'Unable to load claim details. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545',
          confirmButtonText: 'OK'
        });
      }
    } catch (error) {
      console.error('Error:', error);
      await Swal.fire({
        title: 'Network Error',
        text: 'Unable to load claim details. Please check your connection and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    }
  }

  // Helper function to get status badge class
  function getStatusBadgeClass(status) {
    switch(status.toLowerCase()) {
      case 'pending': return 'badge bg-warning text-dark';
      case 'approved': return 'badge bg-success';
      case 'rejected': return 'badge bg-danger';
      default: return 'badge bg-secondary';
    }
  }

  // File size validation function
  function validateFileSize(input, maxSizeMB) {
    const file = input.files[0];
    const errorDiv = input.parentNode.querySelector('.invalid-feedback');
    
    if (file) {
      const fileSizeMB = file.size / (1024 * 1024);
      
      if (fileSizeMB > maxSizeMB) {
        input.classList.add('is-invalid');
        if (errorDiv) {
          errorDiv.textContent = `File size (${fileSizeMB.toFixed(2)}MB) exceeds the maximum limit of ${maxSizeMB}MB. Please choose a smaller file.`;
        }
        input.value = ''; // Clear the file input
        return false;
      } else {
        input.classList.remove('is-invalid');
        if (errorDiv) {
          errorDiv.textContent = '';
        }
        return true;
      }
    }
    return true;
  }

  // Edit Claim with Confirmation
  async function editClaimWithConfirmation(claimId) {
    try {
      // Show loading
      Swal.fire({
        title: 'Loading Claim Data...',
        text: 'Please wait while we fetch the claim information.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const response = await fetch(`/employee/claim-reimbursements/${claimId}`);
      const result = await response.json();

      if (result.success) {
        const claim = result.claim;
        
        const { value: formValues } = await Swal.fire({
          title: 'Edit Claim',
          html: `
            <form id="swalEditClaimForm" class="text-start">
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Editing Claim ID:</strong> ${claim.claim_id}
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="swal_edit_claim_type" class="form-label">Claim Type <span class="text-danger">*</span></label>
                  <select class="form-select" id="swal_edit_claim_type" name="claim_type" required>
                    <option value="Travel Expense" ${claim.claim_type === 'Travel Expense' ? 'selected' : ''}>Travel Expense</option>
                    <option value="Meal Allowance" ${claim.claim_type === 'Meal Allowance' ? 'selected' : ''}>Meal Allowance</option>
                    <option value="Transportation" ${claim.claim_type === 'Transportation' ? 'selected' : ''}>Transportation</option>
                    <option value="Accommodation" ${claim.claim_type === 'Accommodation' ? 'selected' : ''}>Accommodation</option>
                    <option value="Medical Expense" ${claim.claim_type === 'Medical Expense' ? 'selected' : ''}>Medical Expense</option>
                    <option value="Office Supplies" ${claim.claim_type === 'Office Supplies' ? 'selected' : ''}>Office Supplies</option>
                    <option value="Training Materials" ${claim.claim_type === 'Training Materials' ? 'selected' : ''}>Training Materials</option>
                    <option value="Communication Expense" ${claim.claim_type === 'Communication Expense' ? 'selected' : ''}>Communication Expense</option>
                    <option value="Other" ${claim.claim_type === 'Other' ? 'selected' : ''}>Other</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="swal_edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" id="swal_edit_amount" name="amount" step="0.01" min="0.01" max="999999.99" value="${claim.amount.replace('₱', '').replace(/,/g, '')}" required>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="swal_edit_claim_date" class="form-label">Claim Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="swal_edit_claim_date" name="claim_date" max="${new Date().toISOString().split('T')[0]}" value="${claim.claim_date}" required>
              </div>
              <div class="mb-3">
                <label for="swal_edit_description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="swal_edit_description" name="description" rows="3" maxlength="1000" required>${claim.description}</textarea>
              </div>
              <div class="mb-3">
                <label for="swal_edit_receipt_file" class="form-label">Receipt/Document</label>
                <input type="file" class="form-control" id="swal_edit_receipt_file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" onchange="validateFileSize(this, 5)">
                <div class="form-text">Upload new receipt to replace existing one (JPG, PNG, PDF - Max 5MB)</div>
                <div class="invalid-feedback" id="edit-file-error"></div>
                ${claim.receipt_file ? `
                  <div class="mt-2">
                    <small class="text-muted">
                      <i class="bi bi-paperclip"></i> Current receipt:
                      <a href="/employee/claim-reimbursements/${claim.id}/download-receipt" target="_blank">Download</a>
                    </small>
                  </div>
                ` : ''}
              </div>
              <div class="alert alert-warning">
                <i class="bi bi-shield-lock me-2"></i>
                <strong>Security Notice:</strong> You will need to verify your password to update this claim.
              </div>
            </form>
          `,
          width: '800px',
          showCancelButton: true,
          confirmButtonColor: '#4361ee',
          cancelButtonColor: '#6c757d',
          confirmButtonText: '<i class="bi bi-shield-lock me-1"></i>Verify Password & Update',
          cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
          preConfirm: () => {
            const form = document.getElementById('swalEditClaimForm');
            const formData = new FormData(form);
            
            // Validate required fields
            if (!formData.get('claim_type')) {
              Swal.showValidationMessage('Please select a claim type');
              return false;
            }
            if (!formData.get('amount') || parseFloat(formData.get('amount')) <= 0) {
              Swal.showValidationMessage('Please enter a valid amount');
              return false;
            }
            if (!formData.get('claim_date')) {
              Swal.showValidationMessage('Please select a claim date');
              return false;
            }
            if (!formData.get('description') || formData.get('description').trim().length < 10) {
              Swal.showValidationMessage('Please provide a detailed description (minimum 10 characters)');
              return false;
            }
            
            // Validate file size if file is selected
            const fileInput = document.getElementById('swal_edit_receipt_file');
            if (fileInput && fileInput.files[0]) {
              const file = fileInput.files[0];
              const fileSizeMB = file.size / (1024 * 1024);
              if (fileSizeMB > 5) {
                Swal.showValidationMessage(`File size (${fileSizeMB.toFixed(2)}MB) exceeds the maximum limit of 5MB. Please choose a smaller file.`);
                return false;
              }
            }
            
            return formData;
          }
        });

        if (formValues) {
          pendingFormData = formValues;
          pendingClaimId = claimId;
          pendingAction = 'edit';
          await verifyEmployeePasswordForClaim();
        }
      } else {
        await Swal.fire({
          title: 'Error Loading Claim',
          text: result.message || 'Unable to load claim details. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545',
          confirmButtonText: 'OK'
        });
      }
    } catch (error) {
      console.error('Error:', error);
      await Swal.fire({
        title: 'Network Error',
        text: 'Unable to load claim details. Please check your connection and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    }
  }

  // Legacy Edit Claim Form Submission (fallback)
  if (document.getElementById('editClaimForm')) {
    document.getElementById('editClaimForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Redirect to enhanced SweetAlert version
      await Swal.fire({
        title: 'Enhanced Form Available',
        text: 'Please use the enhanced edit functionality with password verification.',
        icon: 'info',
        confirmButtonColor: '#4361ee',
        confirmButtonText: 'OK'
      });
    });
  }

  // Cancel Claim with Confirmation and Password Verification
  async function cancelClaimWithConfirmation(claimId) {
    const result = await Swal.fire({
      title: 'Cancel Claim?',
      html: `
        <div class="alert alert-danger text-start">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>Warning:</strong> This action cannot be undone. The claim will be permanently cancelled.
        </div>
        <p class="text-muted">Are you sure you want to cancel this claim?</p>
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-shield-lock me-1"></i>Verify Password & Cancel',
      cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Keep Claim',
      customClass: {
        popup: 'swal2-popup-custom'
      }
    });

    if (result.isConfirmed) {
      pendingClaimId = claimId;
      pendingAction = 'cancel';
      await verifyEmployeePasswordForClaim();
    }
  }

  // Submit Cancel Claim After Verification
  async function submitCancelClaimAfterVerification() {
    // Show loading
    Swal.fire({
      title: 'Cancelling Claim...',
      text: 'Please wait while we process your request.',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      const response = await fetch(`/employee/claim-reimbursements/${pendingClaimId}/cancel`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });

      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      let result;
      
      try {
        if (contentType && contentType.includes('application/json')) {
          result = await response.json();
        } else {
          // Handle non-JSON responses
          const textResponse = await response.text();
          if (response.ok) {
            result = { success: true, message: 'Claim cancelled successfully!' };
          } else {
            throw new Error('Server returned invalid response format');
          }
        }
      } catch (parseError) {
        // If we can't parse JSON but status is OK, assume success
        if (response.ok) {
          result = { success: true, message: 'Claim cancelled successfully!' };
        } else {
          throw new Error('Invalid response format');
        }
      }

      // Show success message
      await Swal.fire({
        title: 'Claim Cancelled Successfully!',
        text: result.message || 'Your claim has been cancelled and removed from the system.',
        icon: 'success',
        confirmButtonColor: '#4361ee',
        confirmButtonText: 'OK'
      });

      // Reload page to show updated data
      setTimeout(() => location.reload(), 500);
    } catch (error) {
      console.error('Cancel error:', error);
      await Swal.fire({
        title: 'Cancellation Failed',
        text: 'Unable to cancel claim. Please check your connection and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    }
  }


  // Export to CSV function
  function exportToCSV() {
    const table = document.getElementById('claim-table');
    const rows = table.querySelectorAll('tr');
    let csvContent = '';

    // Add header with company info
    csvContent += 'Claim & Reimbursement Report\n';
    csvContent += 'Generated on: ' + new Date().toLocaleString() + '\n';
    csvContent += 'Employee: {{ auth()->user()->name ?? "N/A" }}\n\n';

    rows.forEach((row, index) => {
      // Skip hidden rows (filtered out)
      if (row.style.display === 'none') return;

      const cols = row.querySelectorAll('td, th');
      const rowData = [];

      cols.forEach((col, colIndex) => {
        // Skip the Actions column (last column)
        if (colIndex === cols.length - 1) return;

        let cellText = col.textContent.trim();

        // Clean up the cell text
        cellText = cellText.replace(/\s+/g, ' ');
        cellText = cellText.replace(/"/g, '""'); // Escape quotes

        // Handle special formatting for status badges
        if (col.querySelector('.badge')) {
          cellText = col.querySelector('.badge').textContent.trim();
        }

        // Handle claim type with borders
        if (col.querySelector('.claim-type-border')) {
          cellText = col.querySelector('.claim-type-border').textContent.trim();
        }

        rowData.push('"' + cellText + '"');
      });

      if (rowData.length > 0) {
        csvContent += rowData.join(',') + '\n';
      }
    });

    // Create and download the file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', 'claim_reimbursement_report_' + new Date().toISOString().split('T')[0] + '.csv');
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      showToast('Report exported successfully!', 'success');
    } else {
      showToast('Export not supported in this browser', 'error');
    }
  }

  // Print function
  function printTable() {
    // Get current filters with null checks
    const monthFilterEl = document.getElementById('month-filter');
    const yearFilterEl = document.getElementById('year-filter');
    const statusFilterEl = document.getElementById('status-filter');
    
    const monthFilter = monthFilterEl ? monthFilterEl.value : '';
    const yearFilter = yearFilterEl ? yearFilterEl.value : '';
    const statusFilter = statusFilterEl ? statusFilterEl.value : '';

    // Build filter description
    let filterDesc = 'All Records';
    const filters = [];
    if (monthFilter) {
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
      filters.push('Month: ' + monthNames[monthFilter - 1]);
    }
    if (yearFilter) filters.push('Year: ' + yearFilter);
    if (statusFilter) filters.push('Status: ' + statusFilter);

    if (filters.length > 0) {
      filterDesc = filters.join(', ');
    }

    // Create print content
    const printContent = `
      <div class="print-area">
        <div class="print-header">
          <h2>Claim & Reimbursement Report</h2>
          <p><strong>Employee:</strong> {{ auth()->user()->name ?? "N/A" }}</p>
          <p><strong>Filters Applied:</strong> ${filterDesc}</p>
        </div>
        <div class="print-date">
          Generated on: ${new Date().toLocaleString()}
        </div>
        ${getVisibleTableHTML()}
      </div>
    `;

    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Claim & Reimbursement Report</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          .print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
          .print-header h2 { margin: 0; font-size: 18px; }
          .print-date { text-align: right; font-size: 12px; margin-bottom: 10px; }
          .table { font-size: 11px; width: 100%; }
          .table th { background-color: #f8f9fa !important; }
          .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; }
          .status-pending { background-color: #fff3cd; color: #856404; }
          .status-approved { background-color: #d1e7dd; color: #155724; }
          .status-rejected { background-color: #f8d7da; color: #721c24; }
          .claim-type-border { padding: 2px 8px; border: 2px solid; border-radius: 4px; font-size: 10px; }
          @page { margin: 1cm; size: A4 landscape; }
        </style>
      </head>
      <body>
        ${printContent}
      </body>
      </html>
    `);

    printWindow.document.close();

    // Wait for content to load then print
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 500);

    showToast('Print dialog opened', 'success');
  }

  // Helper function to get visible table HTML
  function getVisibleTableHTML() {
    const table = document.getElementById('claim-table');
    const clonedTable = table.cloneNode(true);

    // Remove action column from header
    const headerRow = clonedTable.querySelector('thead tr');
    if (headerRow) {
      const lastTh = headerRow.querySelector('th:last-child');
      if (lastTh) lastTh.remove();
    }

    // Process each row
    const rows = clonedTable.querySelectorAll('tbody tr');
    rows.forEach(row => {
      // Remove hidden rows
      if (row.style.display === 'none') {
        row.remove();
        return;
      }

      // Remove action column
      const lastTd = row.querySelector('td:last-child');
      if (lastTd) lastTd.remove();
    });

    return '<table class="table table-bordered table-striped">' + clonedTable.innerHTML + '</table>';
  }
</script>
</body>
</html>
