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
            <h2 class="fw-bold mb-1">Course Management</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage your training courses here.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Courses</li>
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
      <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Activated Request Section -->
    <div id="activatedRequestSection" class="card shadow-sm mb-4 border-start border-primary border-4" style="display: none;">
      <div class="card-header bg-primary bg-opacity-10">
        <h4 class="fw-bold mb-0 text-primary">
          <i class="bi bi-lightning-charge me-2"></i>Request Activated - Course Management
        </h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-2"><strong>Request ID:</strong> <span id="requestId"></span></p>
            <p class="mb-2"><strong>Employee ID:</strong> <span id="employeeId"></span></p>
            <p class="mb-2"><strong>Request Type:</strong> <span id="requestType"></span></p>
          </div>
          <div class="col-md-6">
            <p class="mb-2"><strong>Status:</strong>
              <span class="badge bg-warning" id="requestStatus"></span>
            </p>
            <p class="mb-2"><strong>Requested Date:</strong> <span id="requestedDate"></span></p>
            <p class="mb-0"><strong>Reason:</strong> <span id="requestReason"></span></p>
          </div>
        </div>
        <div class="mt-3">
          <p class="text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            A new course "<strong id="destinationName"></strong>" has been created with "Pending" status. Please activate it below to enable auto-assign functionality.
          </p>
        </div>
        <div class="mt-3">
          <button class="btn btn-success btn-sm" id="activateCourseBtn">
            <i class="bi bi-check-circle me-1"></i> Activate Course Now
          </button>
          <button class="btn btn-outline-secondary btn-sm ms-2" id="dismissRequestBtn">
            <i class="bi bi-x-circle me-1"></i> Dismiss
          </button>
        </div>
      </div>
    </div>

    @if(session('activated_request'))
    <div class="card shadow-sm mb-4 border-start border-primary border-4">
      <div class="card-header bg-primary bg-opacity-10">
        <h4 class="fw-bold mb-0 text-primary">
          <i class="bi bi-lightning-charge me-2"></i>Request Activated - Course Management
        </h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-2"><strong>Request ID:</strong> {{ session('activated_request')->request_id }}</p>
            <p class="mb-2"><strong>Employee ID:</strong> {{ session('activated_request')->employee_id }}</p>
            <p class="mb-2"><strong>Request Type:</strong> {{ session('activated_request')->request_type }}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-2"><strong>Status:</strong>
              <span class="badge bg-success">{{ session('activated_request')->status }}</span>
            </p>
            <p class="mb-2"><strong>Requested Date:</strong> {{ date('M d, Y', strtotime(session('activated_request')->requested_date)) }}</p>
            <p class="mb-0"><strong>Reason:</strong> {{ session('activated_request')->reason }}</p>
          </div>
        </div>
        <div class="mt-3">
          <p class="text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            You can now manage course activation for this request. Use the course list below to activate/deactivate courses as needed.
          </p>
        </div>
      </div>
    </div>
    @endif

    <!-- Competency Notifications Section -->
    @if($competencyNotifications->count() > 0)
    <div class="card shadow-sm border-start border-info border-4 mb-4">
      <div class="card-header bg-info bg-opacity-10 d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0 text-info">
          <i class="bi bi-bell me-2"></i>Competency Notifications
        </h4>
        <span class="badge bg-info">{{ $competencyNotifications->count() }} Notifications</span>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          <i class="bi bi-info-circle me-1"></i>
          Recent notifications from Competency Library about competency updates that may affect your courses.
        </p>
        <div class="notifications-list">
          @foreach($competencyNotifications as $notification)
          <div class="notification-item border rounded p-3 mb-3 {{ $notification->is_read ? 'bg-light' : 'bg-info bg-opacity-10' }}" data-notification-id="{{ $notification->id }}">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h6 class="mb-1">
                  <i class="bi bi-lightbulb text-warning me-1"></i>
                  Competency Update: {{ str_replace('(Training:) ', '', $notification->competency_name) }}
                </h6>
                <p class="mb-2 text-muted small">{{ $notification->message }}</p>
                <div class="d-flex align-items-center">
                  <small class="text-muted me-3">
                    <i class="bi bi-clock me-1"></i>
                    {{ $notification->created_at->diffForHumans() }}
                  </small>
                  @if(!$notification->is_read)
                  <span class="badge bg-info">New</span>
                  @endif
                </div>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-success" onclick="acceptAndCreateCourse({{ $notification->id }})">
                  <i class="bi bi-check-circle"></i> Accept & Create Course
                </button>
                @if(!$notification->is_read)
                <button class="btn btn-sm btn-outline-info" onclick="markAsRead({{ $notification->id }})">
                  <i class="bi bi-check2"></i> Mark Read
                </button>
                @endif
                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification->id }})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    <!-- Pending Activation Requests Section -->
    @if($pendingActivationRequests->count() > 0)
    <div class="card shadow-sm border-start border-warning border-4 mb-4">
      <div class="card-header bg-warning bg-opacity-10">
        <h4 class="fw-bold mb-0 text-warning">
          <i class="bi bi-exclamation-triangle me-2"></i>Pending Course Activation Requests
        </h4>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          <i class="bi bi-info-circle me-1"></i>
          The following courses are waiting for your approval from Destination Knowledge Training requests.
        </p>
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead class="table-warning">
              <tr>
                <th class="fw-bold">Course Title</th>
                <th class="fw-bold">Description</th>
                <th class="fw-bold">Requested By</th>
                <th class="fw-bold">Requested Date</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pendingActivationRequests as $request)
              <tr>
                <td class="fw-semibold">{{ $request->course_title }}</td>
                <td>{{ Str::limit($request->description, 50) }}</td>
                <td>
                  @if($request->requestedBy)
                    {{ $request->requestedBy->name }}
                  @else
                    System
                  @endif
                </td>
                <td>{{ $request->requested_at ? $request->requested_at->format('M d, Y H:i') : 'N/A' }}</td>
                <td>
                  <span class="badge bg-warning text-dark">{{ $request->status }}</span>
                </td>
                <td class="text-center">
                  <button class="btn btn-success btn-sm me-1" onclick="approveCourseRequest({{ $request->course_id }})">
                    <i class="bi bi-check-circle"></i> Approve
                  </button>
                  <button class="btn btn-danger btn-sm" onclick="rejectCourseRequest({{ $request->course_id }})">
                    <i class="bi bi-x-circle"></i> Reject
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

    <!-- Training Requests Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Training Requests</h4>
        <span class="badge bg-primary">{{ $trainingRequests->count() }} Requests</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead class="table-warning">
              <tr>
                <th class="fw-bold">Request ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Course Requested</th>
                <th class="fw-bold">Reason</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Requested Date</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($trainingRequests as $request)
              <tr>
                <td>{{ $request->request_id }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                      @php
                        $firstName = $request->employee->first_name ?? 'Unknown';
                        $lastName = $request->employee->last_name ?? 'Employee';
                        $fullName = $firstName . ' ' . $lastName;

                        // Check if profile picture exists - simplified approach
                        $profilePicUrl = null;
                        if ($request->employee && $request->employee->profile_picture) {
                            // Direct asset URL generation - Laravel handles the storage symlink
                            $profilePicUrl = asset('storage/' . $request->employee->profile_picture);
                        }

                        // Generate consistent color based on employee name for fallback
                        $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                        $employeeId = $request->employee_id ?? 'default';
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
                    <div>
                      <strong>{{ $firstName }} {{ $lastName }}</strong>
                      <br><small class="text-muted">ID: {{ $request->employee_id }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <strong>{{ $request->training_title }}</strong>
                  @if($request->course)
                    <br><small class="text-muted">Course ID: {{ $request->course->course_id }}</small>
                  @endif
                </td>
                <td>{{ Str::limit($request->reason, 50) }}</td>
                <td>
                  <span class="badge {{ $request->status == 'Approved' ? 'bg-success' : ($request->status == 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $request->status }}
                  </span>
                </td>
                <td>{{ date('M d, Y', strtotime($request->requested_date)) }}</td>
                <td class="text-center">
                  @if($request->status == 'Pending')
                    <button class="btn btn-success btn-sm me-1" onclick="approveRequest({{ $request->request_id }})">
                      <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="rejectRequest({{ $request->request_id }})">
                      <i class="bi bi-x-circle"></i> Reject
                    </button>
                  @else
                    <span class="text-muted small">{{ $request->status }}</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No training requests found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Course List</h4>
        <button class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addCourseModal">
          <i class="bi bi-plus-lg me-1"></i> Add Course
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">Course ID</th>
                <th class="fw-bold">Course Title</th>
                <th class="fw-bold">Description</th>
                <th class="fw-bold">Start Date</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Created At</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($courses as $course)
              <tr>
                <td>{{ $course->course_id }}</td>
                <td class="fw-semibold">{{ $course->course_title }}</td>
                <td>{{ Str::limit($course->description, 50) }}</td>
                <td>{{ date('M d, Y', strtotime($course->start_date)) }}</td>
                <td>
                  <span class="badge {{ $course->status == 'Active' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                    {{ $course->status }}
                  </span>
                </td>
                <td>{{ date('M d, Y', strtotime($course->created_at)) }}</td>
                <td class="text-center">
                  <button class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editCourseModal{{ $course->course_id }}">
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                  <form action="{{ route('admin.course_management.destroy', $course->course_id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?')">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No courses found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">
            Showing <span class="fw-semibold">1</span> to <span class="fw-semibold">2</span> of <span class="fw-semibold">2</span> entries
          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
              </li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item disabled">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Course Modal -->
  <div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="card-header modal-header">
          <h5 class="modal-title">Add New Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.course_management.store') }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Course Title*</label>
              <input type="text" class="form-control" name="course_title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="4"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Start Date*</label>
              <input type="date" class="form-control" name="start_date" required>
            </div>
            <div class="mb-3 mt-3">
              <label class="form-label">Status*</label>
              <select class="form-select" name="status" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Course
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Course Modals -->
  @foreach($courses as $course)
  <div class="modal fade" id="editCourseModal{{ $course->course_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.course_management.update', $course->course_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
              <label class="form-label">Course Title*</label>
              <input type="text" class="form-control" name="course_title" value="{{ $course->course_title }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="4">{{ $course->description }}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Start Date*</label>
              <input type="date" class="form-control" name="start_date" value="{{ $course->start_date }}" required>
            </div>
            <div class="mb-3 mt-3">
              <label class="form-label">Status*</label>
              <select class="form-select" name="status" required>
                <option value="Active" {{ $course->status == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $course->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-repeat me-1"></i> Update Course
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endforeach


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Check for activated request from destination knowledge training
      const activatedRequestData = sessionStorage.getItem('activated_request');
      if (activatedRequestData) {
        try {
          const requestData = JSON.parse(activatedRequestData);
          displayActivatedRequest(requestData);
          // Clear the session storage after displaying
          sessionStorage.removeItem('activated_request');
        } catch (error) {
          console.error('Error parsing activated request data:', error);
        }
      }

      // Function to display activated request
      function displayActivatedRequest(data) {
        const section = document.getElementById('activatedRequestSection');
        document.getElementById('requestId').textContent = data.request_id;
        document.getElementById('employeeId').textContent = data.employee_id;
        document.getElementById('requestType').textContent = data.request_type;
        document.getElementById('requestStatus').textContent = data.status;
        document.getElementById('requestedDate').textContent = new Date(data.requested_date).toLocaleDateString();
        document.getElementById('requestReason').textContent = data.reason;
        document.getElementById('destinationName').textContent = data.destination_name;

        section.style.display = 'block';

        // Store course ID for activation
        section.setAttribute('data-course-id', data.course_id);

        // Show SweetAlert notification for new request activation
        Swal.fire({
          title: 'New Training Request Received!',
          html: `
            <div class="text-start">
              <p><strong>Employee:</strong> ${data.employee_id}</p>
              <p><strong>Training:</strong> ${data.destination_name}</p>
              <p><strong>Status:</strong> <span class="badge bg-warning">${data.status}</span></p>
              <p><strong>Requested:</strong> ${new Date(data.requested_date).toLocaleDateString()}</p>
            </div>
          `,
          icon: 'info',
          confirmButtonColor: '#0d6efd',
          confirmButtonText: 'Review Request',
          timer: 5000,
          timerProgressBar: true,
          showCloseButton: true
        });
      }

      // Activate course button handler
      document.getElementById('activateCourseBtn').addEventListener('click', async function() {
        const section = document.getElementById('activatedRequestSection');
        const courseId = section.getAttribute('data-course-id');
        const button = this;

        if (!courseId) {
          await Swal.fire({
            title: 'Error',
            text: 'Course ID not found',
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
          return;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Activating...';

        try {
          const response = await fetch(`{{ route('admin.course_management.update', ['id' => '__COURSE_ID__']) }}`.replace('__COURSE_ID__', courseId), {
            method: 'PUT',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              status: 'Active'
            })
          });

          const result = await response.json();

          if (response.ok && result.success) {
            // Show SweetAlert success message
            await Swal.fire({
              title: 'Course Activated!',
              text: 'Course activated successfully! Auto-assign is now available.',
              icon: 'success',
              confirmButtonColor: '#28a745',
              timer: 3000,
              timerProgressBar: true
            });

            // Hide the activated request section
            section.style.display = 'none';

            // Refresh the page to show updated course status
            window.location.reload();
          } else {
            throw new Error(result.message || 'Failed to activate course');
          }
        } catch (error) {
          console.error('Error activating course:', error);
          const errorMessage = error.message || 'Failed to activate course. Please try manually updating the course status to "Active".';
          await Swal.fire({
            title: 'Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        } finally {
          button.disabled = false;
          button.innerHTML = '<i class="bi bi-check-circle me-1"></i> Activate Course Now';
        }
      });

      // Dismiss request button handler
      document.getElementById('dismissRequestBtn').addEventListener('click', function() {
        document.getElementById('activatedRequestSection').style.display = 'none';
      });
    });

    // Training request approval/rejection functions
    async function approveRequest(requestId) {
      const result = await Swal.fire({
        title: 'Approve Training Request',
        text: 'Are you sure you want to approve this training request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
      });

      if (!result.isConfirmed) return;

      try {
        // First check if training_requests table exists
        const checkResponse = await fetch('/admin/check-training-requests-table', {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          }
        });

        if (!checkResponse.ok) {
          throw new Error('Unable to verify database table');
        }

        const response = await fetch(`/admin/training-requests/${requestId}/approve`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          }
        });

        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const result = await response.json();
        if (result.success) {
          // Show SweetAlert success notification
          await Swal.fire({
            title: 'Approved!',
            text: result.message || 'Training request approved successfully!',
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
          });

          // Reload page after notification
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error',
            text: 'Failed to approve request: ' + (result.message || 'Unknown error'),
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        console.error('Error approving request:', error);
        let errorMessage = 'Error approving request';

        if (error.message.includes('404')) {
          errorMessage = 'Training request not found.';
        } else if (error.message.includes('500')) {
          errorMessage = 'Database error. Creating training_requests table automatically...';
          
          // Try to create the table automatically
          try {
            const createResponse = await fetch('/admin/create-training-requests-table', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
              }
            });
            
            if (createResponse.ok) {
              errorMessage = 'Table created successfully. Please try again.';
            }
          } catch (createError) {
            console.error('Failed to create table:', createError);
          }
        } else if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
          errorMessage = 'Network error. Please check your connection and try again.';
        }

        await Swal.fire({
          title: 'Error',
          text: errorMessage,
          icon: 'error',
          confirmButtonColor: '#dc3545',
          footer: '<small>Technical details: ' + error.message + '</small>'
        });
      }
    }

    async function rejectRequest(requestId) {
      const { value: reason } = await Swal.fire({
        title: 'Reject Training Request',
        text: 'Please provide a reason for rejection:',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Reject Request',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
          if (!value) {
            return 'You need to provide a reason for rejection!'
          }
        }
      });

      if (!reason) return;

      try {
        const response = await fetch(`/admin/training-requests/${requestId}/reject`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ reason: reason })
        });

        const result = await response.json();
        if (result.success) {
          await Swal.fire({
            title: 'Rejected!',
            text: result.message || 'Training request rejected successfully!',
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
          });
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error',
            text: 'Failed to reject request: ' + result.message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        console.error('Error rejecting request:', error);
        await Swal.fire({
          title: 'Error',
          text: 'Error rejecting request',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Course activation request approval/rejection functions
    async function approveCourseRequest(courseId) {
      const result = await Swal.fire({
        title: 'Approve Course Activation',
        text: 'Are you sure you want to approve this course activation request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
      });

      if (!result.isConfirmed) return;

      try {
        const response = await fetch(`{{ url('admin/course-management') }}/${courseId}/approve`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          }
        });

        const result = await response.json();
        if (result.success) {
          // Show SweetAlert success notification
          await Swal.fire({
            title: 'Course Approved!',
            text: result.message,
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
          });

          // Reload page to update UI
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error',
            text: 'Failed to approve course: ' + result.message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        console.error('Error approving course:', error);
        await Swal.fire({
          title: 'Error',
          text: 'Error approving course request',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    async function rejectCourseRequest(courseId) {
      const { value: reason } = await Swal.fire({
        title: 'Reject Course Activation',
        text: 'Please provide a reason for rejection:',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Reject Course',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
          if (!value) {
            return 'You need to provide a reason for rejection!'
          }
        }
      });

      if (!reason) return;

      try {
        const response = await fetch(`{{ url('admin/course-management') }}/${courseId}/reject`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ reason: reason })
        });

        const result = await response.json();
        if (result.success) {
          // Show SweetAlert success notification
          await Swal.fire({
            title: 'Course Rejected!',
            text: result.message,
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
          });

          // Reload page to update UI
          location.reload();
        } else {
          await Swal.fire({
            title: 'Error',
            text: 'Failed to reject course: ' + result.message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        console.error('Error rejecting course:', error);
        await Swal.fire({
          title: 'Error',
          text: 'Error rejecting course request',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Competency notification functions
    async function acceptAndCreateCourse(notificationId) {
      console.log('acceptAndCreateCourse called with ID:', notificationId);

      const result = await Swal.fire({
        title: 'Accept & Create Course',
        text: 'Are you sure you want to accept this notification and create a course? The course will be automatically set to ACTIVE status.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Create Course',
        cancelButtonText: 'Cancel'
      });

      console.log('SweetAlert result:', result);

      if (!result.isConfirmed) {
        console.log('User cancelled the operation');
        return;
      }

      try {
        console.log('Making AJAX request to:', `/admin/course-management/notifications/${notificationId}/accept-create-course`);
        const response = await fetch(`/admin/course-management/notifications/${notificationId}/accept-create-course`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          }
        });

        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Response result:', result);
        if (result.success) {
          // Remove (TRAINING:) prefix from message if present
          const cleanMessage = result.message.replace('(TRAINING:) ', '').replace('(TRAINING:)', '');
          // Show success message
          const alert = document.createElement('div');
          alert.className = 'alert alert-success alert-dismissible fade show';
          alert.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>${cleanMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;
          document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);

          // Remove the notification from UI
          const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.remove();
          }

          // Update notification count in header
          const badge = document.querySelector('.badge.bg-info');
          if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            if (currentCount > 1) {
              badge.textContent = currentCount - 1;
            } else {
              badge.remove();
            }
          }

          // Reload page after 2 seconds to show the new course
          setTimeout(() => location.reload(), 2000);
        } else {
          Swal.fire({
            title: 'Error',
            text: 'Failed to create course: ' + result.message,
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      } catch (error) {
        console.error('Error accepting notification and creating course:', error);
        alert('Error creating course from notification');
      }
    }

    async function markAsRead(notificationId) {
      try {
        const response = await fetch(`/admin/course-management/notifications/${notificationId}/mark-read`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          }
        });

        const result = await response.json();
        if (result.success) {
          // Update UI to show notification as read
          const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.classList.remove('bg-info', 'bg-opacity-10');
            notificationItem.classList.add('bg-light');
            const badge = notificationItem.querySelector('.badge');
            if (badge) badge.remove();
          }
        } else {
          alert('Failed to mark notification as read: ' + result.message);
        }
      } catch (error) {
        console.error('Error marking notification as read:', error);
        alert('Error marking notification as read');
      }
    }

    async function deleteNotification(notificationId) {
      if (!confirm('Are you sure you want to delete this notification?')) return;

      try {
        const response = await fetch(`/admin/course-management/notifications/${notificationId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
          }
        });

        const result = await response.json();
        if (result.success) {
          // Remove notification from UI
          const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.remove();
          }
          // Reload page to update notification count
          setTimeout(() => location.reload(), 1000);
        } else {
          alert('Failed to delete notification: ' + result.message);
        }
      } catch (error) {
        console.error('Error deleting notification:', error);
        alert('Error deleting notification');
      }
    }
  </script>
</body>
</html>
