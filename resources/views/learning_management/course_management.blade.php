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
  <style>
    /* Card hover effects */
    .training-request-card, .course-card {
      transition: all 0.3s ease !important;
      border: 1px solid #e9ecef !important;
    }
    
    .training-request-card:hover, .course-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
      border-color: #007bff !important;
    }
    
    /* Button group styling */
    .btn-group .btn {
      border-radius: 0.375rem !important;
      margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
      margin-right: 0;
    }
    
    /* Empty state styling */
    .bi-inbox, .bi-book {
      opacity: 0.3;
    }
    
    /* Course card book icon */
    .course-card .bi-book-fill {
      opacity: 0.8;
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
            <li class="breadcrumb-item active" aria-current="page">Course Management</li>
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
                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification->id }})">
                  <i class="bi bi-trash"></i>Reject
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
        @forelse($trainingRequests as $request)
          @if($loop->first)
            <div class="row g-4">
          @endif
              <div class="col-lg-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 training-request-card" style="transition: all 0.3s ease;">
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
                  
                  <!-- Card Header with Employee Info -->
                  <div class="card-header text-white border-0 py-3" style="background-color: #{{ $bgColor }};">
                    <div class="d-flex align-items-center">
                      <img src="{{ $profilePicUrl }}"
                           alt="{{ $firstName }} {{ $lastName }}"
                           class="rounded-circle me-3"
                           style="width: 45px; height: 45px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ $firstName }} {{ $lastName }}</h6>
                        <small class="text-dark fw-bold">Request #{{ $request->request_id }}</small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card-body">
                    <!-- Course Requested -->
                    <div class="mb-3 p-3 bg-light rounded-3 border-start border-primary border-4">
                      <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-mortarboard-fill text-primary me-2" style="font-size: 1.2rem;"></i>
                        <h6 class="card-title fw-bold text-dark mb-0">{{ $request->training_title }}</h6>
                      </div>
                      @if($request->course)
                        <small class="text-muted">
                          <i class="bi bi-tag me-1"></i>Course ID: {{ $request->course->course_id }}
                        </small>
                      @endif
                    </div>

                    <!-- Employee ID -->
                    <div class="mb-3 p-2 bg-info bg-opacity-10 rounded-2">
                      <div class="d-flex align-items-center">
                        <i class="bi bi-person-badge text-info me-2"></i>
                        <div>
                          <small class="text-muted d-block">Employee ID</small>
                          <span class="fw-semibold text-dark">{{ $request->employee_id }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-3 p-3 bg-warning bg-opacity-10 rounded-2 border-start border-warning border-3">
                      <div class="d-flex align-items-start">
                        <i class="bi bi-chat-quote text-warning me-2 mt-1"></i>
                        <div class="flex-grow-1">
                          <small class="text-muted d-block mb-1">
                            <i class="bi bi-question-circle me-1"></i>Reason
                          </small>
                          <p class="mb-0 text-dark" style="min-height: 40px; font-style: italic;">
                            "{{ Str::limit($request->reason, 80) }}"
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Status and Date -->
                    <div class="row mb-3">
                      <div class="col-6">
                        <div class="p-2 bg-light rounded-2 text-center">
                          <small class="text-muted d-block mb-1">
                            <i class="bi bi-flag me-1"></i>Status
                          </small>
                          <span class="badge {{ $request->status == 'Approved' ? 'bg-success' : ($request->status == 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                            <i class="bi {{ $request->status == 'Approved' ? 'bi-check-circle' : ($request->status == 'Rejected' ? 'bi-x-circle' : 'bi-clock') }} me-1"></i>
                            {{ $request->status }}
                          </span>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="p-2 bg-light rounded-2 text-center">
                          <small class="text-muted d-block mb-1">
                            <i class="bi bi-calendar-event me-1"></i>Requested Date
                          </small>
                          <small class="fw-semibold text-dark">{{ date('M d, Y', strtotime($request->requested_date)) }}</small>
                        </div>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                      @if($request->status == 'Pending')
                        <div class="btn-group" role="group">
                          <button class="btn btn-success btn-sm" onclick="approveRequest({{ $request->request_id }})" title="Approve Request">
                            <i class="bi bi-check-circle"></i>
                          </button>
                          <button class="btn btn-danger btn-sm" onclick="rejectRequest({{ $request->request_id }})" title="Reject Request">
                            <i class="bi bi-x-circle"></i>
                          </button>
                        </div>
                        <div class="mt-1">
                          <small class="text-muted">
                            <i class="bi bi-check-circle me-1"></i>Approve &nbsp;&nbsp;
                            <i class="bi bi-x-circle me-1"></i>Reject
                          </small>
                        </div>
                      @else
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="viewRequestDetails('{{ $request->request_id }}', '{{ addslashes($request->employee->first_name ?? 'Unknown') }} {{ addslashes($request->employee->last_name ?? 'Employee') }}', '{{ $request->employee_id }}', '{{ addslashes($request->training_title) }}', '{{ addslashes($request->reason ?? 'N/A') }}', '{{ $request->status }}', '{{ date('M d, Y', strtotime($request->requested_date)) }}')">
                          <i class="bi bi-eye me-1"></i> View Details
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
          @if($loop->last)
            </div>
          @endif
        @empty
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-inbox display-1 text-muted"></i>
            </div>
            <h5 class="text-muted mb-2">No Training Requests Found</h5>
            <p class="text-muted mb-3">Training requests from employees will appear here.</p>
          </div>
        @endforelse
      </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Course List</h4>
        <button class="btn btn-primary btn-sm d-flex align-items-center" onclick="addCourseWithConfirmation()">
          <i class="bi bi-plus-lg me-1"></i> Add Course
        </button>
      </div>
      <div class="card-body">
        @forelse($courses as $course)
          @if($loop->first)
            <div class="row g-4">
          @endif
              <div class="col-lg-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 course-card" style="transition: all 0.3s ease;">
                  @php
                    // Generate course color based on course ID
                    $courseColors = ['4ECDC4', '45B7D1', 'FFA726', 'AB47BC', 'EF5350', '66BB6A', 'FFCA28', '26A69A', 'FF7043', '7E57C2'];
                    $courseIndex = abs(crc32($course->course_id)) % count($courseColors);
                    $courseColor = $courseColors[$courseIndex];
                  @endphp
                  
                  <!-- Card Header with Course Info -->
                  <div class="card-header text-white border-0 py-3" style="background-color: #{{ $courseColor }};">
                    <div class="d-flex align-items-center">
                      <div class="me-3">
                        <i class="bi bi-book-fill" style="font-size: 2rem;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ $course->course_title }}</h6>
                        <small class="text-dark fw-bold">Course ID: {{ $course->course_id }}</small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card-body">
                    <!-- Description -->
                    <div class="mb-3 p-3 bg-light rounded-3 border-start border-info border-4">
                      <div class="d-flex align-items-start">
                        <i class="bi bi-file-text text-info me-2 mt-1"></i>
                        <div class="flex-grow-1">
                          <small class="text-muted d-block mb-1">
                            <i class="bi bi-info-circle me-1"></i>Description
                          </small>
                          <p class="mb-0 text-dark" style="min-height: 60px; font-style: italic;">
                            "{{ Str::limit($course->description, 100) }}"
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Dates Section -->
                    <div class="row mb-3">
                      <div class="col-6">
                        <div class="p-2 bg-success bg-opacity-10 rounded-2 text-center">
                          <small class="text-muted d-block mb-1">
                            <i class="bi bi-calendar-plus text-success me-1"></i>Start Date
                          </small>
                          <small class="fw-semibold text-dark">{{ date('M d, Y', strtotime($course->start_date)) }}</small>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="p-2 bg-secondary bg-opacity-10 rounded-2 text-center">
                          <small class="text-muted d-block mb-1">
                            <i class="bi bi-calendar-check text-secondary me-1"></i>Created
                          </small>
                          <small class="fw-semibold text-dark">{{ date('M d, Y', strtotime($course->created_at)) }}</small>
                        </div>
                      </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3 p-3 {{ $course->status == 'Active' ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 rounded-2 text-center">
                      <small class="text-muted d-block mb-2">
                        <i class="bi bi-gear me-1"></i>Course Status
                      </small>
                      <span class="badge {{ $course->status == 'Active' ? 'bg-success' : 'bg-secondary' }} fs-6">
                        <i class="bi {{ $course->status == 'Active' ? 'bi-check-circle' : 'bi-pause-circle' }} me-1"></i>
                        {{ $course->status }}
                      </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                      <div class="btn-group" role="group">
                        <button class="btn btn-outline-info btn-sm" onclick="viewCourseDetails('{{ $course->course_id }}', '{{ addslashes($course->course_title) }}', '{{ addslashes($course->description) }}', '{{ $course->start_date }}', '{{ $course->status }}', '{{ $course->created_at->format('M d, Y H:i') }}')" title="View Details">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="editCourseWithConfirmation('{{ $course->course_id }}', '{{ addslashes($course->course_title) }}', '{{ addslashes($course->description) }}', '{{ $course->start_date }}', '{{ $course->status }}')" title="Edit Course">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteCourseWithConfirmation('{{ $course->course_id }}', '{{ addslashes($course->course_title) }}')" title="Delete Course">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          @if($loop->last)
            </div>
          @endif
        @empty
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-book display-1 text-muted"></i>
            </div>
            <h5 class="text-muted mb-2">No Courses Found</h5>
            <p class="text-muted mb-3">Get started by adding your first course.</p>
            <button class="btn btn-primary" onclick="addCourseWithConfirmation()">
              <i class="bi bi-plus-lg me-1"></i> Add Your First Course
            </button>
          </div>
        @endforelse

        <!-- Pagination (if needed) -->
        @if($courses->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4">
          <div class="text-muted small">
            Showing <span class="fw-semibold">{{ $courses->count() }}</span> course{{ $courses->count() != 1 ? 's' : '' }}
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
        @endif
      </div>
    </div>
  </main>

  <!-- Hidden form for course operations -->
  <form id="courseActionForm" action="" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="_method" id="courseMethod" value="">
    <input type="hidden" name="course_title" id="hiddenCourseTitle">
    <input type="hidden" name="description" id="hiddenDescription">
    <input type="hidden" name="start_date" id="hiddenStartDate">
    <input type="hidden" name="status" id="hiddenStatus">
  </form>

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
      // First check if requestId is valid
      if (!requestId || requestId === 'undefined' || requestId === 'null') {
        await Swal.fire({
          title: 'Error',
          text: 'Invalid request ID. Please refresh the page and try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
        return;
      }

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
        console.log('Attempting to approve request ID:', requestId);
        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);
        
        const response = await fetch(`/admin/training-requests/${requestId}/approve`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
          const errorText = await response.text();
          console.error('Response not OK:', response.status, errorText);
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const result = await response.json();
        console.log('Approval response:', result);
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
        console.error('Error details:', error.message);
        console.error('Error stack:', error.stack);
        
        let errorMessage = 'Error approving request';
        let technicalDetails = error.message;

        if (error.message.includes('404')) {
          errorMessage = 'Training request not found.';
        } else if (error.message.includes('500')) {
          errorMessage = 'Database error occurred. Please contact the administrator.';
        } else if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
          errorMessage = 'Network error. Please check your connection and try again.';
        }

        await Swal.fire({
          title: 'Error',
          text: errorMessage,
          icon: 'error',
          confirmButtonColor: '#dc3545',
          footer: `<small>Technical details: ${technicalDetails}</small>`
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

    // View training request details function
    async function viewRequestDetails(requestId, employeeName, employeeId, trainingTitle, reason, status, requestedDate) {
      // Create detailed view modal with status badge styling
      const statusBadgeClass = status === 'Approved' ? 'bg-success' : 
                              status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark';
      
      await Swal.fire({
        title: 'Training Request Details',
        html: `
          <div class="text-start">
            <div class="row mb-3">
              <div class="col-6"><strong>Request ID:</strong></div>
              <div class="col-6">${requestId}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Employee:</strong></div>
              <div class="col-6">${employeeName}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Employee ID:</strong></div>
              <div class="col-6">${employeeId}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Course:</strong></div>
              <div class="col-6">${trainingTitle}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Reason:</strong></div>
              <div class="col-6">${reason}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Status:</strong></div>
              <div class="col-6"><span class="badge ${statusBadgeClass}">${status}</span></div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Requested Date:</strong></div>
              <div class="col-6">${requestedDate}</div>
            </div>
          </div>
        `,
        icon: 'info',
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Close',
        width: '600px'
      });
    }

    async function deleteNotification(notificationId) {
      const result = await Swal.fire({
        title: 'Delete Notification',
        text: 'Are you sure you want to delete this notification?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
      });

      if (!result.isConfirmed) return;

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
          await Swal.fire({
            title: 'Deleted!',
            text: 'Notification deleted successfully!',
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 2000,
            timerProgressBar: true
          });
          
          // Remove notification from UI
          const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.remove();
          }
          // Reload page to update notification count
          setTimeout(() => location.reload(), 1000);
        } else {
          await Swal.fire({
            title: 'Error',
            text: 'Failed to delete notification: ' + result.message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        console.error('Error deleting notification:', error);
        await Swal.fire({
          title: 'Error',
          text: 'Error deleting notification',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Course Management Functions with SweetAlert and Password Confirmation
    async function addCourseWithConfirmation() {
      const { value: formValues } = await Swal.fire({
        title: 'Add New Course',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Course Title *</label>
              <input id="swal-course-title" class="form-control" placeholder="Enter course title" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Description</label>
              <textarea id="swal-description" class="form-control" rows="3" placeholder="Enter course description"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Start Date *</label>
              <input id="swal-start-date" type="date" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Status *</label>
              <select id="swal-status" class="form-select" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold text-danger">Admin Password *</label>
              <input id="swal-password" type="password" class="form-control" placeholder="Enter your admin password for confirmation" required>
              <small class="text-muted">Password confirmation required for security</small>
            </div>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-save me-1"></i> Create Course',
        cancelButtonText: 'Cancel',
        width: '600px',
        preConfirm: () => {
          const courseTitle = document.getElementById('swal-course-title').value;
          const description = document.getElementById('swal-description').value;
          const startDate = document.getElementById('swal-start-date').value;
          const status = document.getElementById('swal-status').value;
          const password = document.getElementById('swal-password').value;
          
          if (!courseTitle || !startDate || !status || !password) {
            Swal.showValidationMessage('Please fill in all required fields');
            return false;
          }
          
          return {
            course_title: courseTitle,
            description: description,
            start_date: startDate,
            status: status,
            password: password
          };
        }
      });

      if (formValues) {
        await submitCourseForm('{{ route('admin.course_management.store') }}', 'POST', formValues);
      }
    }

    async function editCourseWithConfirmation(courseId, currentTitle, currentDescription, currentStartDate, currentStatus) {
      const { value: formValues } = await Swal.fire({
        title: 'Edit Course',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label class="form-label fw-bold">Course Title *</label>
              <input id="swal-course-title" class="form-control" value="${currentTitle}" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Description</label>
              <textarea id="swal-description" class="form-control" rows="3">${currentDescription}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Start Date *</label>
              <input id="swal-start-date" type="date" class="form-control" value="${currentStartDate}" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Status *</label>
              <select id="swal-status" class="form-select" required>
                <option value="Active" ${currentStatus === 'Active' ? 'selected' : ''}>Active</option>
                <option value="Inactive" ${currentStatus === 'Inactive' ? 'selected' : ''}>Inactive</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold text-danger">Admin Password *</label>
              <input id="swal-password" type="password" class="form-control" placeholder="Enter your admin password for confirmation" required>
              <small class="text-muted">Password confirmation required for security</small>
            </div>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> Update Course',
        cancelButtonText: 'Cancel',
        width: '600px',
        preConfirm: () => {
          const courseTitle = document.getElementById('swal-course-title').value;
          const description = document.getElementById('swal-description').value;
          const startDate = document.getElementById('swal-start-date').value;
          const status = document.getElementById('swal-status').value;
          const password = document.getElementById('swal-password').value;
          
          if (!courseTitle || !startDate || !status || !password) {
            Swal.showValidationMessage('Please fill in all required fields');
            return false;
          }
          
          return {
            course_title: courseTitle,
            description: description,
            start_date: startDate,
            status: status,
            password: password
          };
        }
      });

      if (formValues) {
        await submitCourseForm(`{{ url('admin/course-management') }}/${courseId}`, 'PUT', formValues);
      }
    }

    async function deleteCourseWithConfirmation(courseId, courseTitle) {
      const { value: password } = await Swal.fire({
        title: 'Delete Course',
        html: `
          <div class="text-start">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Warning:</strong> This action cannot be undone!
            </div>
            <p class="mb-3">You are about to delete the course: <strong>${courseTitle}</strong></p>
            <div class="mb-3">
              <label class="form-label fw-bold text-danger">Admin Password *</label>
              <input id="swal-delete-password" type="password" class="form-control" placeholder="Enter your admin password to confirm deletion" required>
              <small class="text-muted">Password confirmation required for security</small>
            </div>
          </div>
        `,
        icon: 'warning',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Delete Course',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
          const password = document.getElementById('swal-delete-password').value;
          if (!password) {
            Swal.showValidationMessage('Password is required to confirm deletion');
            return false;
          }
          return password;
        }
      });

      if (password) {
        await submitCourseForm(`{{ url('admin/course-management') }}/${courseId}`, 'DELETE', { password: password });
      }
    }

    async function viewCourseDetails(courseId, title, description, startDate, status, createdAt) {
      await Swal.fire({
        title: 'Course Details',
        html: `
          <div class="text-start">
            <div class="row mb-3">
              <div class="col-4"><strong>Course ID:</strong></div>
              <div class="col-8">${courseId}</div>
            </div>
            <div class="row mb-3">
              <div class="col-4"><strong>Title:</strong></div>
              <div class="col-8">${title}</div>
            </div>
            <div class="row mb-3">
              <div class="col-4"><strong>Description:</strong></div>
              <div class="col-8">${description || 'No description provided'}</div>
            </div>
            <div class="row mb-3">
              <div class="col-4"><strong>Start Date:</strong></div>
              <div class="col-8">${new Date(startDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
            </div>
            <div class="row mb-3">
              <div class="col-4"><strong>Status:</strong></div>
              <div class="col-8"><span class="badge ${status === 'Active' ? 'bg-success' : 'bg-secondary'}">${status}</span></div>
            </div>
            <div class="row mb-3">
              <div class="col-4"><strong>Created:</strong></div>
              <div class="col-8">${createdAt}</div>
            </div>
          </div>
        `,
        icon: 'info',
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Close',
        width: '600px'
      });
    }

    async function submitCourseForm(url, method, formData) {
      try {
        // Show loading
        Swal.fire({
          title: 'Processing...',
          text: 'Please wait while we process your request.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const requestBody = {
          _token: document.querySelector('meta[name="csrf-token"]').content,
          ...formData
        };

        console.log('Submitting form data:', requestBody);

        if (method !== 'POST') {
          requestBody._method = method;
        }

        // Create FormData for traditional Laravel form handling
        const formDataToSend = new FormData();
        Object.keys(requestBody).forEach(key => {
          formDataToSend.append(key, requestBody[key]);
        });

        console.log('Making request to:', url);
        console.log('Request method:', method);
        console.log('Form data:', Object.fromEntries(formDataToSend));

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: formDataToSend
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let result;
        try {
          result = JSON.parse(responseText);
        } catch (e) {
          console.error('Failed to parse JSON response:', e);
          throw new Error('Server returned invalid JSON response: ' + responseText.substring(0, 200));
        }
        
        console.log('Parsed result:', result);

        if (response.ok && result.success) {
          await Swal.fire({
            title: 'Success!',
            text: result.message || 'Operation completed successfully!',
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
          });
          
          // Reload page to show changes
          location.reload();
        } else {
          let errorMessage = 'Operation failed';
          
          console.log('Operation failed. Response:', result);
          
          if (result.errors) {
            errorMessage = Object.values(result.errors).flat().join('\n');
          } else if (result.message) {
            errorMessage = result.message;
          } else {
            errorMessage = `Server error (${response.status}): ${responseText.substring(0, 200)}`;
          }
          
          await Swal.fire({
            title: 'Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        console.error('Error stack:', error.stack);
        
        let errorMessage = 'An unexpected error occurred. Please try again.';
        if (error.message) {
          errorMessage = error.message;
        }
        
        await Swal.fire({
          title: 'Error',
          text: errorMessage,
          icon: 'error',
          confirmButtonColor: '#dc3545',
          footer: '<small>Check browser console for detailed error information</small>'
        });
      }
    }
  </script>
</body>
</html>
