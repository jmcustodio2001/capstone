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
    <!-- Success/Error Messages -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Certificate Tracking</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage employee training certificates here.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Certificates</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Certificate Tracking Content -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Training Records</h4>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertificateModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Record
          </button>
          <form action="{{ route('training_record_certificate_tracking.auto_generate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('This will create certificate tracking records for all completed trainings from all employees. Continue?')">
              <i class="bi bi-magic me-1"></i> Auto-Generate Missing Certificates
            </button>
          </form>
          <button class="btn btn-info" onclick="previewCertificateTemplate()">
            <i class="bi bi-eye me-1"></i> Preview Template
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-bordered" id="certificateTable">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">Record ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Course</th>
                <th class="fw-bold">Certificate Number</th>
                <th class="fw-bold">Expiry Date</th>
                <th class="fw-bold">Certificate URL</th>
                <th class="fw-bold">Completion Date</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Remarks</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="certificateTableBody">
              @forelse($certificates as $certificate)
              <tr>
                <td>{{ $certificate->id }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                      @php
                        $firstName = $certificate->employee->first_name ?? 'Unknown';
                        $lastName = $certificate->employee->last_name ?? 'Employee';
                        $fullName = $firstName . ' ' . $lastName;
                        $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
                        
                        // Check if profile picture exists
                        $profilePicUrl = null;
                        if ($certificate->employee->profile_picture) {
                            $profilePicUrl = asset('storage/' . $certificate->employee->profile_picture);
                        }
                        
                        // Generate consistent color based on employee ID for fallback
                        $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                        $employeeId = $certificate->employee_id ?? 'default';
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
                           style="width: 40px; height: 40px; object-fit: cover;"
                           onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($initials) }}&size=200&background={{ $bgColor }}&color=ffffff&bold=true&rounded=true'">
                    </div>
                    <div>
                      <span class="fw-semibold">{{ $firstName }} {{ $lastName }}</span>
                      <br>
                      <small class="text-muted">ID: {{ $certificate->employee_id }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  @if($certificate->course && isset($certificate->course->course_title))
                    {{ $certificate->course->course_title }}
                  @else
                    <span class="text-muted">No course</span>
                  @endif
                </td>
                <td>
                  @if($certificate->certificate_number)
                    {{ $certificate->certificate_number }}
                  @else
                    <span class="text-muted">No number</span>
                  @endif
                </td>
                <td>
                  @php
                    // Calculate accurate expiry date based on completion date and course type
                    $expiryDate = null;
                    
                    if ($certificate->training_date) {
                        $completionDate = \Carbon\Carbon::parse($certificate->training_date);
                        
                        // Different validity periods based on course type
                        $courseTitle = strtolower($certificate->course->course_title ?? '');
                        
                        if (strpos($courseTitle, 'safety') !== false || strpos($courseTitle, 'security') !== false) {
                            // Safety/Security courses: 1 year validity
                            $expiryDate = $completionDate->copy()->addYear();
                        } elseif (strpos($courseTitle, 'leadership') !== false || strpos($courseTitle, 'management') !== false) {
                            // Leadership/Management courses: 3 years validity
                            $expiryDate = $completionDate->copy()->addYears(3);
                        } elseif (strpos($courseTitle, 'technical') !== false || strpos($courseTitle, 'software') !== false) {
                            // Technical courses: 2 years validity
                            $expiryDate = $completionDate->copy()->addYears(2);
                        } elseif (strpos($courseTitle, 'destination') !== false || strpos($courseTitle, 'location') !== false) {
                            // Destination knowledge: 18 months validity
                            $expiryDate = $completionDate->copy()->addMonths(18);
                        } else {
                            // Default courses (Communication, Customer Service, etc.): 2 years validity
                            $expiryDate = $completionDate->copy()->addYears(2);
                        }
                    } elseif ($certificate->certificate_expiry) {
                        // Fallback to existing expiry date if available
                        $expiryDate = \Carbon\Carbon::parse($certificate->certificate_expiry);
                    }
                  @endphp
                  
                  @if($expiryDate)
                    @php
                      $now = \Carbon\Carbon::now();
                      $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                    @endphp
                    
                    @if($daysUntilExpiry < 0)
                      <span class="text-danger fw-bold">{{ $expiryDate->format('d/m/Y') }}</span>
                      <br><small class="text-danger">EXPIRED</small>
                    @elseif($daysUntilExpiry <= 30)
                      <span class="text-warning fw-bold">{{ $expiryDate->format('d/m/Y') }}</span>
                      <br><small class="text-warning">Expires soon</small>
                    @elseif($daysUntilExpiry <= 90)
                      <span class="text-info fw-bold">{{ $expiryDate->format('d/m/Y') }}</span>
                      <br><small class="text-info">{{ $daysUntilExpiry }} days left</small>
                    @else
                      <span class="text-success">{{ $expiryDate->format('d/m/Y') }}</span>
                      <br><small class="text-muted">Valid</small>
                    @endif
                  @else
                    <span class="text-muted">No expiry</span>
                  @endif
                </td>
                <td>
                  @if($certificate->certificate_url)
                    <div class="d-flex gap-2">
                      <a href="{{ route('certificates.view', $certificate->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                      </a>
                      <a href="{{ route('certificates.download', $certificate->id) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download"></i> Download
                      </a>
                    </div>
                  @else
                    <button class="btn btn-sm btn-outline-warning" onclick="generateCertificate('{{ $certificate->employee_id }}', '{{ $certificate->course_id }}', '{{ $certificate->id }}')">
                      <i class="bi bi-magic"></i> Generate
                    </button>
                  @endif
                </td>
                <td>
                  @if($certificate->training_date)
                    {{ \Carbon\Carbon::parse($certificate->training_date)->format('M d, Y') }}
                  @else
                    <span class="text-muted">Not set</span>
                  @endif
                </td>
                <td>
                  @if($certificate->status == 'Completed')
                    <span class="badge bg-success bg-opacity-10 text-success">{{ $certificate->status }}</span>
                  @elseif($certificate->status == 'Pending')
                    <span class="badge bg-warning bg-opacity-10 text-warning">{{ $certificate->status }}</span>
                  @else
                    <span class="badge bg-danger bg-opacity-10 text-danger">{{ $certificate->status }}</span>
                  @endif
                </td>
                <td>
                  @php
                    $remarkText = 'No remarks';
                    $remarkClass = 'text-muted';
                    
                    if($certificate->status) {
                      switch(strtolower($certificate->status)) {
                        case 'completed':
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
                          if($certificate->remarks && !empty($certificate->remarks)) {
                            $remarkText = $certificate->remarks;
                            $remarkClass = 'text-dark';
                          }
                      }
                    } elseif($certificate->remarks && !empty($certificate->remarks)) {
                      $remarkText = $certificate->remarks;
                      $remarkClass = 'text-dark';
                    }
                  @endphp
                  <span class="{{ $remarkClass }}">{{ $remarkText }}</span>
                </td>
                <td class="text-center">
                  <button class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editCertificateModal{{ $certificate->id }}">
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                  <form action="{{ route('training_record_certificate_tracking.destroy', $certificate->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?')">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center text-muted">
                  <i class="bi bi-file-earmark-text display-5 text-muted mb-2"></i>
                  <h5 class="mt-2">No Certificate Records Found</h5>
                  <p class="text-muted">Add your first record to get started</p>
                  <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addCertificateModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Record
                  </button>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Certificate Modal -->
  <div class="modal fade" id="addCertificateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Certificate Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('training_record_certificate_tracking.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Employee*</label>
                <select class="form-select" name="employee_id" required>
                  <option value="">Select Employee</option>
                  @foreach($employees as $employee)
                    <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }} (ID: {{ $employee->employee_id }})</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Course*</label>
                <select class="form-select" name="course_id" required>
                  <option value="">Select Course</option>
                  @foreach($courses as $course)
                    <option value="{{ $course->course_id }}">{{ $course->course_title }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Certificate File*</label>
              <input type="file" class="form-control" name="certificate_file" required>
            </div>
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Completion Date*</label>
                <input type="date" class="form-control" name="training_date" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status*</label>
                <select class="form-select" name="status" required>
                  <option value="Completed">Completed</option>
                  <option value="Pending">Pending</option>
                  <option value="Expired">Expired</option>
                </select>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Certificate Number*</label>
                <input type="text" class="form-control" name="certificate_number" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Expiry Date*</label>
                <input type="date" class="form-control" name="certificate_expiry" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Remarks</label>
              <textarea class="form-control" name="remarks" rows="3" placeholder="Optional remarks or notes"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Record
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Certificate Modals -->
  @foreach($certificates as $certificate)
  <div class="modal fade" id="editCertificateModal{{ $certificate->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Certificate Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('training_record_certificate_tracking.update', $certificate->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Employee*</label>
                <select class="form-select" name="employee_id" required>
                  @foreach($employees as $employee)
                    <option value="{{ $employee->employee_id }}" {{ $certificate->employee_id == $employee->employee_id ? 'selected' : '' }}>
                      {{ $employee->first_name }} {{ $employee->last_name }} (ID: {{ $employee->employee_id }})
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Course*</label>
                <select class="form-select" name="course_id" required>
                  @foreach($courses as $course)
                    <option value="{{ $course->course_id }}" {{ $certificate->course_id == $course->course_id ? 'selected' : '' }}>
                      {{ $course->course_title }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Certificate File</label>
              <input type="file" class="form-control" name="certificate_file">
              @if($certificate->certificate_url)
                <small class="text-muted">Current: <a href="{{ $certificate->certificate_url }}" target="_blank">View Certificate</a></small>
              @endif
            </div>
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Completion Date*</label>
                <input type="date" class="form-control" name="training_date" value="{{ $certificate->training_date }}" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status*</label>
                <select class="form-select" name="status" required>
                  <option value="Completed" {{ $certificate->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                  <option value="Pending" {{ $certificate->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                  <option value="Expired" {{ $certificate->status == 'Expired' ? 'selected' : '' }}>Expired</option>
                </select>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Certificate Number*</label>
                <input type="text" class="form-control" name="certificate_number" value="{{ $certificate->certificate_number }}" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Expiry Date*</label>
                <input type="date" class="form-control" name="certificate_expiry" value="{{ $certificate->certificate_expiry }}" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Remarks</label>
              <textarea class="form-control" name="remarks" rows="3" placeholder="Optional remarks or notes">{{ $certificate->remarks }}</textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-repeat me-1"></i> Update Record
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endforeach

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Form validation and submission handling
      const addForm = document.querySelector('#addCertificateModal form');
      if (addForm) {
        addForm.addEventListener('submit', function(e) {
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          
          // Show loading state
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
          
          // Re-enable after a delay if form doesn't submit properly
          setTimeout(() => {
            if (submitBtn.disabled) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = originalText;
            }
          }, 10000);
        });
      }

      // Edit form handling
      document.querySelectorAll('[id^="editCertificateModal"] form').forEach(form => {
        form.addEventListener('submit', function(e) {
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          
          // Show loading state
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
          
          // Re-enable after a delay if form doesn't submit properly
          setTimeout(() => {
            if (submitBtn.disabled) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = originalText;
            }
          }, 10000);
        });
      });

      // File input validation
      document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
          const file = this.files[0];
          if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            if (file.size > maxSize) {
              alert('File size must be less than 10MB');
              this.value = '';
              return;
            }
            
            if (!allowedTypes.includes(file.type)) {
              alert('Please select a valid file type (PDF, PNG, JPG, JPEG, DOC, DOCX)');
              this.value = '';
              return;
            }
          }
        });
      });

      // Auto-dismiss alerts after 5 seconds
      setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        });
      }, 5000);
    });

    // Certificate generation functions
    function generateCertificate(employeeId, courseId, certificateId) {
      if (!confirm('Generate AI certificate for this training completion?')) return;
      
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';
      
      fetch('/certificates/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          employee_id: employeeId,
          course_id: courseId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Certificate generated successfully!', 'success');
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast('Failed to generate certificate: ' + data.message, 'error');
        }
      })
      .catch(error => {
        showToast('Error generating certificate', 'error');
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
      });
    }

    function bulkGenerateCertificates() {
      if (!confirm('Generate certificates for all completed trainings without certificates?')) return;
      
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';
      
      fetch('/certificates/bulk-generate', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast(`Bulk generation completed! Generated: ${data.generated}, Failed: ${data.failed}`, 'success');
          setTimeout(() => location.reload(), 2000);
        } else {
          showToast('Bulk generation failed: ' + data.message, 'error');
        }
      })
      .catch(error => {
        showToast('Error in bulk generation', 'error');
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
      });
    }

    function previewCertificateTemplate() {
      const courseName = prompt('Enter course name for preview:', 'Communication Skills Training');
      if (courseName) {
        window.open('/certificates/preview?course_name=' + encodeURIComponent(courseName), '_blank');
      }
    }

    function showToast(message, type = 'info') {
      const toastContainer = document.querySelector('.toast-container') || createToastContainer();
      const toast = document.createElement('div');
      toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
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
      container.className = 'toast-container position-fixed top-0 end-0 p-3';
      container.style.zIndex = '9999';
      document.body.appendChild(container);
      return container;
    }
  </script>
</body>
</html>
