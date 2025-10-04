<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Employee Request Forms</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- jQuery CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
      <button class="btn btn-primary btn-sm" onclick="requestDocumentWithConfirmation()">
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
                  <div class="btn-group" role="group">
                    <!-- View Button -->
                    <button class="btn btn-info btn-sm text-white"
                      onclick="viewRequestDetails('{{ $request->request_id }}', '{{ $request->employee_id }}', '{{ addslashes($request->request_type) }}', '{{ addslashes($request->reason) }}', '{{ $request->status }}', '{{ $request->requested_date }}')"
                      title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    @if(strtolower($request->status) == 'pending')
                      <!-- Edit Button (only for pending requests) -->
                      <button class="btn btn-warning btn-sm text-white"
                        onclick="editRequestWithConfirmation('{{ $request->request_id }}', '{{ addslashes($request->request_type) }}', '{{ addslashes($request->reason) }}')"
                        title="Edit Request">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <!-- Delete Button (only for pending requests) -->
                      <button class="btn btn-danger btn-sm"
                        onclick="deleteRequestWithConfirmation('{{ $request->request_id }}')"
                        title="Delete Request">
                        <i class="bi bi-trash"></i>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Set up CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// ============================================
// SWEETALERT FUNCTIONS WITH PASSWORD VERIFICATION
// ============================================

/**
 * View Request Details with SweetAlert
 */
function viewRequestDetails(requestId, employeeId, requestType, reason, status, requestedDate) {
    // Format status badge
    let statusBadge = '';
    if (status.toLowerCase() === 'pending') {
        statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
    } else if (status.toLowerCase() === 'approved') {
        statusBadge = '<span class="badge bg-success">Approved</span>';
    } else {
        statusBadge = '<span class="badge bg-danger">Rejected</span>';
    }

    Swal.fire({
        title: '<i class="bi bi-eye me-2"></i>Request Details',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <strong>Request ID:</strong> ${requestId}<br>
                    <strong>Employee ID:</strong> ${employeeId}<br>
                    <strong>Document Type:</strong> ${requestType}<br>
                    <strong>Purpose:</strong> ${reason}<br>
                    <strong>Status:</strong> ${statusBadge}<br>
                    <strong>Requested Date:</strong> ${requestedDate}
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close',
        confirmButtonColor: '#6c757d',
        width: '500px'
    });
}

/**
 * Request Document with Password Confirmation
 */
function requestDocumentWithConfirmation() {
    Swal.fire({
        title: '<i class="bi bi-shield-lock me-2"></i>Security Verification',
        html: `
            <div class="text-start mb-3">
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    For security purposes, please enter your password to request a document.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter Your Password:</label>
                    <input type="password" id="requestPassword" class="form-control" placeholder="Enter your password" minlength="3">
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Verify & Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const password = document.getElementById('requestPassword').value;
            if (!password) {
                Swal.showValidationMessage('Password is required');
                return false;
            }
            if (password.length < 3) {
                Swal.showValidationMessage('Password must be at least 3 characters');
                return false;
            }
            return password;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showRequestDocumentForm(result.value);
        }
    });
}

/**
 * Show Request Document Form after password verification
 */
function showRequestDocumentForm(password) {
    Swal.fire({
        title: '<i class="bi bi-file-earmark-text me-2"></i>Request Official Document',
        html: `
            <form id="requestDocumentForm">
                <input type="hidden" name="password" value="${password}">
                <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
                <input type="hidden" name="status" value="Pending">
                <input type="hidden" name="requested_date" value="{{ now()->format('Y-m-d') }}">

                <div class="mb-3 text-start">
                    <label for="swalRequestType" class="form-label fw-bold">Document Type</label>
                    <select name="request_type" id="swalRequestType" class="form-select" required>
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
                <div class="mb-3 text-start">
                    <label for="swalReason" class="form-label fw-bold">Purpose</label>
                    <textarea name="reason" id="swalReason" rows="3" class="form-control" placeholder="Enter purpose for requesting this document (e.g., loan application, visa processing, new employment, etc.)..." required></textarea>
                </div>
            </form>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Submit Request',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: '600px',
        preConfirm: () => {
            const requestType = document.getElementById('swalRequestType').value;
            const reason = document.getElementById('swalReason').value;

            if (!requestType) {
                Swal.showValidationMessage('Please select a document type');
                return false;
            }
            if (!reason.trim()) {
                Swal.showValidationMessage('Please enter the purpose for this request');
                return false;
            }

            return {
                password: password,
                employee_id: '{{ Auth::user()->employee_id }}',
                request_type: requestType,
                reason: reason.trim(),
                status: 'Pending',
                requested_date: '{{ now()->format('Y-m-d') }}'
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitRequestForm(result.value);
        }
    });
}

/**
 * Edit Request with Password Confirmation
 */
function editRequestWithConfirmation(requestId, currentRequestType, currentReason) {
    Swal.fire({
        title: '<i class="bi bi-shield-lock me-2"></i>Security Verification',
        html: `
            <div class="text-start mb-3">
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    For security purposes, please enter your password to edit this request.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter Your Password:</label>
                    <input type="password" id="editPassword" class="form-control" placeholder="Enter your password" minlength="3">
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Verify & Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const password = document.getElementById('editPassword').value;
            if (!password) {
                Swal.showValidationMessage('Password is required');
                return false;
            }
            if (password.length < 3) {
                Swal.showValidationMessage('Password must be at least 3 characters');
                return false;
            }
            return password;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showEditRequestForm(requestId, currentRequestType, currentReason, result.value);
        }
    });
}

/**
 * Show Edit Request Form after password verification
 */
function showEditRequestForm(requestId, currentRequestType, currentReason, password) {
    Swal.fire({
        title: '<i class="bi bi-pencil me-2"></i>Edit Request',
        html: `
            <form id="editRequestForm">
                <input type="hidden" name="password" value="${password}">
                <input type="hidden" name="request_id" value="${requestId}">

                <div class="mb-3 text-start">
                    <label for="swalEditRequestType" class="form-label fw-bold">Document Type</label>
                    <select name="request_type" id="swalEditRequestType" class="form-select" required>
                        <option value="">-- Select Document Type --</option>
                        <option value="Certificate of Employment (COE)" ${currentRequestType === 'Certificate of Employment (COE)' ? 'selected' : ''}>Certificate of Employment (COE)</option>
                        <option value="Employment Verification Letter" ${currentRequestType === 'Employment Verification Letter' ? 'selected' : ''}>Employment Verification Letter</option>
                        <option value="Salary Certificate" ${currentRequestType === 'Salary Certificate' ? 'selected' : ''}>Salary Certificate</option>
                        <option value="Experience Letter" ${currentRequestType === 'Experience Letter' ? 'selected' : ''}>Experience Letter</option>
                        <option value="Government-related forms (SSS)" ${currentRequestType === 'Government-related forms (SSS)' ? 'selected' : ''}>Government-related forms (SSS)</option>
                        <option value="Government-related forms (PhilHealth)" ${currentRequestType === 'Government-related forms (PhilHealth)' ? 'selected' : ''}>Government-related forms (PhilHealth)</option>
                        <option value="Government-related forms (Pag-IBIG)" ${currentRequestType === 'Government-related forms (Pag-IBIG)' ? 'selected' : ''}>Government-related forms (Pag-IBIG)</option>
                        <option value="Tax Certificate (BIR 2316)" ${currentRequestType === 'Tax Certificate (BIR 2316)' ? 'selected' : ''}>Tax Certificate (BIR 2316)</option>
                        <option value="Clearance Certificate" ${currentRequestType === 'Clearance Certificate' ? 'selected' : ''}>Clearance Certificate</option>
                        <option value="Service Record" ${currentRequestType === 'Service Record' ? 'selected' : ''}>Service Record</option>
                    </select>
                </div>
                <div class="mb-3 text-start">
                    <label for="swalEditReason" class="form-label fw-bold">Purpose</label>
                    <textarea name="reason" id="swalEditReason" rows="3" class="form-control" placeholder="Enter purpose for requesting this document..." required>${currentReason}</textarea>
                </div>
            </form>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Update Request',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        width: '600px',
        preConfirm: () => {
            const requestType = document.getElementById('swalEditRequestType').value;
            const reason = document.getElementById('swalEditReason').value;

            if (!requestType) {
                Swal.showValidationMessage('Please select a document type');
                return false;
            }
            if (!reason.trim()) {
                Swal.showValidationMessage('Please enter the purpose for this request');
                return false;
            }

            return {
                password: password,
                request_id: requestId,
                request_type: requestType,
                reason: reason.trim()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitEditForm(result.value);
        }
    });
}

/**
 * Delete Request with Password Confirmation
 */
function deleteRequestWithConfirmation(requestId) {
    Swal.fire({
        title: '<i class="bi bi-exclamation-triangle me-2"></i>Delete Request',
        html: `
            <div class="text-start mb-3">
                <p class="text-danger fw-bold mb-2">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Warning: This action cannot be undone!
                </p>
                <p class="text-muted small mb-3">
                    You are about to permanently delete this document request. Please enter your password to confirm this action.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter Your Password:</label>
                    <input type="password" id="deletePassword" class="form-control" placeholder="Enter your password" minlength="3">
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete Request',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const password = document.getElementById('deletePassword').value;
            if (!password) {
                Swal.showValidationMessage('Password is required');
                return false;
            }
            if (password.length < 3) {
                Swal.showValidationMessage('Password must be at least 3 characters');
                return false;
            }
            return password;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitDeleteRequest(requestId, result.value);
        }
    });
}

/**
 * Submit Request Form with AJAX
 */
function submitRequestForm(formData) {
    console.log('Submitting form data:', formData);
    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
    console.log('Route URL:', '{{ route("employee.requests.store") }}');

    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Submitting your document request...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit via AJAX
    fetch('{{ route("employee.requests.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        console.log('Response content-type:', response.headers.get('content-type'));

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Non-JSON response received:', contentType);
            // Try to get the response text for debugging
            return response.text().then(text => {
                console.error('Response body:', text);
                // Check if it's a redirect to login page
                if (text.includes('login') || text.includes('Login') || response.status === 302) {
                    throw new Error('Your session has expired. Please refresh the page and log in again.');
                }
                throw new Error('Server returned an unexpected response. Please try again.');
            });
        }

        // Always parse JSON, even for error responses (401, 422, etc.)
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message || 'Document request submitted successfully!',
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            // Handle error responses (like wrong password)
            let errorMessage = data.message || 'Failed to submit request';

            // Check if it's a password error
            if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
                Swal.fire({
                    title: 'Invalid Password',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            } else if (errorMessage.includes('session has expired') || errorMessage.includes('Authentication required')) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. The page will refresh automatically.',
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
            return; // Don't throw error, we've handled it
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = error.message || 'Failed to submit document request. Please try again.';

        // Handle authentication errors
        if (errorMessage.includes('session has expired') || errorMessage.includes('logged out') || errorMessage.includes('Unauthenticated') || errorMessage.includes('Authentication required')) {
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. The page will refresh automatically.',
                icon: 'warning',
                confirmButtonColor: '#ffc107',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                window.location.reload();
            });
            return;
        }

        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
}

/**
 * Submit Edit Form with AJAX
 */
function submitEditForm(formData) {
    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Updating your document request...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit via AJAX
    fetch(`/employee/requests/${formData.request_id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. You may have been logged out.');
        }

        // Always parse JSON, even for error responses (401, 422, etc.)
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message || 'Document request updated successfully!',
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            // Handle error responses (like wrong password)
            let errorMessage = data.message || 'Failed to update request';

            // Check if it's a password error
            if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
                Swal.fire({
                    title: 'Invalid Password',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            } else if (errorMessage.includes('session has expired') || errorMessage.includes('Authentication required')) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. The page will refresh automatically.',
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
            return; // Don't throw error, we've handled it
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = error.message || 'Failed to update document request. Please try again.';

        // Handle authentication errors
        if (errorMessage.includes('session has expired') || errorMessage.includes('logged out') || errorMessage.includes('Unauthenticated') || errorMessage.includes('Authentication required')) {
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. The page will refresh automatically.',
                icon: 'warning',
                confirmButtonColor: '#ffc107',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                window.location.reload();
            });
            return;
        }

        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
}

/**
 * Submit Delete Request with AJAX
 */
function submitDeleteRequest(requestId, password) {
    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Deleting your document request...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit via AJAX
    fetch(`/employee/requests/${requestId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. You may have been logged out.');
        }

        // Always parse JSON, even for error responses (401, 422, etc.)
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Deleted!',
                text: data.message || 'Document request deleted successfully!',
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            // Handle error responses (like wrong password)
            let errorMessage = data.message || 'Failed to delete request';

            // Check if it's a password error
            if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
                Swal.fire({
                    title: 'Invalid Password',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            } else if (errorMessage.includes('session has expired') || errorMessage.includes('Authentication required')) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. The page will refresh automatically.',
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
            return; // Don't throw error, we've handled it
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = error.message || 'Failed to delete document request. Please try again.';

        // Handle authentication errors
        if (errorMessage.includes('session has expired') || errorMessage.includes('logged out') || errorMessage.includes('Unauthenticated') || errorMessage.includes('Authentication required')) {
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. The page will refresh automatically.',
                icon: 'warning',
                confirmButtonColor: '#ffc107',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                window.location.reload();
            });
            return;
        }

        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
}
</script>

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
