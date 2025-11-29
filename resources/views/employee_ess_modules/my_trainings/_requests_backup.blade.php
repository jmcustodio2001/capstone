<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-bold mb-2">
        Training Requests
      </h4>
      @php
        // Initialize variables with proper counting
        $trainingRequests = isset($trainingRequests) ? collect($trainingRequests) : collect();
        $upcomingTrainings = isset($upcomingTrainings) ? collect($upcomingTrainings) : collect();
        $autoCreatedCount = 0;
        $existingCount = $trainingRequests->where('is_auto', false)->count();
        $totalRequests = 0;

        // Combine existing requests with upcoming trainings
        foreach ($upcomingTrainings as $upcoming) {
            // CRITICAL FIX: Skip accredited training centers (destination_assigned)
            // These should only appear in _upcoming.blade.php with Accept/Decline buttons
            if (isset($upcoming->source) && $upcoming->source === 'destination_assigned') {
                continue; // Skip destination training centers completely
            }
            
            // ADDITIONAL FILTER: Skip any training that looks like destination knowledge training
            $trainingTitle = $upcoming->training_title ?? '';
            if (stripos($trainingTitle, 'destination') !== false || 
                stripos($trainingTitle, 'accredited') !== false ||
                stripos($trainingTitle, 'training center') !== false) {
                continue; // Skip destination-related trainings
            }
            
            // Check if request doesn't exist by both title and course_id
            $exists = $trainingRequests->contains(function ($request) use ($upcoming) {
                return ($request->training_title === $upcoming->training_title) ||
                       (isset($request->course_id) && isset($upcoming->course_id) && $request->course_id === $upcoming->course_id);
            });

            // Also check if database record already exists to prevent duplicates
            $dbExists = \App\Models\TrainingRequest::where('employee_id', Auth::user()->employee_id)
                ->where('training_title', $upcoming->training_title ?? '')
                ->exists();

            // Check if this training was recently unassigned from competency gaps
            $wasRecentlyUnassigned = false;
            if (isset($upcoming->source) && in_array($upcoming->source, ['competency_gap', 'competency_assigned', 'admin_assigned'])) {
                // Check if there's a competency gap that was recently unassigned for this training
                $competencyGap = \App\Models\CompetencyGap::with('competency')
                    ->where('employee_id', Auth::user()->employee_id)
                    ->where('assigned_to_training', false) // Recently unassigned
                    ->whereHas('competency', function($query) use ($upcoming) {
                        $trainingTitle = $upcoming->training_title ?? '';
                        $cleanTitle = str_replace([' Training', ' Course', ' Program', ' Skills'], '', $trainingTitle);
                        $query->where('competency_name', 'LIKE', '%' . $cleanTitle . '%')
                              ->orWhere('competency_name', $trainingTitle)
                              ->orWhere('competency_name', $cleanTitle);
                    })
                    ->where('updated_at', '>', now()->subMinutes(5)) // Updated in last 5 minutes
                    ->exists();
                
                $wasRecentlyUnassigned = $competencyGap;
            }

            if (!$exists && !$dbExists && !$wasRecentlyUnassigned) {
                // Auto-create actual database records for seamless flow
                try {
                    // Create the training request in database
                    $dbRequest = \App\Models\TrainingRequest::create([
                        'employee_id' => Auth::user()->employee_id,
                        'course_id' => $upcoming->course_id ?? null,
                        'training_title' => $upcoming->training_title ?? '',
                        'reason' => 'Automatically enrolled from upcoming trainings',
                        'status' => 'Approved',
                        'requested_date' => now()->format('Y-m-d')
                    ]);

                    // Create corresponding progress record
                    \App\Models\TrainingProgress::create([
                        'employee_id' => Auth::user()->employee_id,
                        'course_id' => $upcoming->course_id ?? null,
                        'training_title' => $upcoming->training_title ?? '',
                        'progress' => 0,
                        'status' => 'Not Started',
                        'source' => 'auto_approved_request',
                        'request_id' => $dbRequest->request_id,
                        'last_accessed' => now()
                    ]);

                    // Create notification record
                    \App\Models\TrainingNotification::create([
                        'employee_id' => Auth::user()->employee_id,
                        'message' => "You have been automatically enrolled in '{$upcoming->training_title}' training.",
                        'sent_at' => now()
                    ]);

                    $newRequest = (object)[
                        'request_id' => $dbRequest->request_id,
                        'training_title' => $upcoming->training_title ?? '',
                        'course_id' => $upcoming->course_id ?? null,
                        'reason' => 'Automatically enrolled from upcoming trainings',
                        'status' => 'Approved',
                        'requested_date' => now()->format('Y-m-d'),
                        'current_level' => $upcoming->current_level ?? 0,
                        'is_auto' => true
                    ];
                } catch (\Exception $e) {
                    // Fallback to view-only record if database creation fails
                    $newRequest = (object)[
                        'request_id' => 'AUTO-' . time() . rand(1000, 9999),
                        'training_title' => $upcoming->training_title ?? '',
                        'course_id' => $upcoming->course_id ?? null,
                        'reason' => 'Automatically enrolled from upcoming trainings',
                        'status' => 'Approved',
                        'requested_date' => now()->format('Y-m-d'),
                        'current_level' => $upcoming->current_level ?? 0,
                        'is_auto' => true
                    ];
                }
                
                $trainingRequests->push($newRequest);
                $autoCreatedCount++;
            } elseif ($dbExists && !$exists) {
                // If database record exists but not in collection, add it to display
                $existingDbRequest = \App\Models\TrainingRequest::where('employee_id', Auth::user()->employee_id)
                    ->where('training_title', $upcoming->training_title ?? '')
                    ->first();
                
                if ($existingDbRequest) {
                    $newRequest = (object)[
                        'request_id' => $existingDbRequest->request_id,
                        'training_title' => $existingDbRequest->training_title,
                        'course_id' => $existingDbRequest->course_id,
                        'reason' => $existingDbRequest->reason,
                        'status' => $existingDbRequest->status,
                        'requested_date' => $existingDbRequest->requested_date,
                        'current_level' => $upcoming->current_level ?? 0,
                        'is_auto' => true
                    ];
                    $trainingRequests->push($newRequest);
                    $autoCreatedCount++;
                }
            }
        }

        // Calculate total after all processing
        $totalRequests = $existingCount + $autoCreatedCount;
        
        // If we auto-created any records, trigger a data refresh
        $shouldRefreshData = $autoCreatedCount > 0;
      @endphp
      
      @if($shouldRefreshData)
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            if (typeof refreshTrainingData === "function") {
              setTimeout(function() { refreshTrainingData(); }, 1000);
            }
          });
        </script>
      @endif
      
      @php
        // Update the total after all processing
        $totalRequests = $existingCount + $autoCreatedCount;
      @endphp
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Training ID</th>
            <th>Training Title</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Requested Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $uniqueRequests = $trainingRequests->unique('request_id');
            $isAutoCreated = [];
            $sequentialId = 1; // Start sequential numbering from 1
          @endphp
          @forelse($uniqueRequests as $r)
            @php
              // Check if this is an auto-created request
              $isAutoCreated[$r->request_id] = substr($r->request_id, 0, 5) === 'AUTO-';
            @endphp
            <tr data-training-title="{{ $r->training_title }}"
                data-course-id="{{ $r->course_id ?? '' }}"
                class="{{ $isAutoCreated[$r->request_id] ? 'table-info' : '' }}">
              <td>
                {{ $sequentialId++ }}
                @if($isAutoCreated[$r->request_id])
                  <span class="badge bg-info">Auto</span>
                @endif
              </td>
              <td>{{ $r->training_title }}</td>
              <td>{{ $r->reason }}</td>
              <td>
                @if($r->is_auto ?? false)
                  <span class="badge bg-success">Active</span>
                @else
                  <span class="badge {{ $r->status == 'Approved' ? 'bg-success' : ($r->status == 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $r->status }}
                  </span>
                @endif
              </td>
              <td>{{ $r->requested_date }}</td>
              <td class="text-center">
                <div class="btn-group" role="group">
                  {{-- View Details Button --}}
                  <button class="btn btn-outline-info btn-sm"
                          onclick="viewRequestDetails('{{ $r->request_id }}', '{{ addslashes($r->training_title) }}', '{{ addslashes($r->reason) }}', '{{ $r->status }}', '{{ $r->requested_date }}')"
                          title="View Details">
                    <i class="bi bi-eye"></i>
                  </button>

                  {{-- Take Exam Button - Always available for auto-created or approved requests --}}
                  @php
                    $showExamButton = $r->status == 'Approved' || ($r->is_auto ?? false);
                    $courseId = null;

                    // Get course_id directly if available
                    if (!empty($r->course_id)) {
                      $courseId = $r->course_id;
                    } else {
                      // Try to find course by title
                      $course = \App\Models\CourseManagement::where('course_title', $r->training_title)
                          ->first();
                      if ($course) {
                        $courseId = $course->course_id;
                      }
                    }

                      // Strategy 4: FALLBACK - Create course if none exists (for approved requests)
                      if (!$courseId && $r->status == 'Approved') {
                        try {
                          $newCourse = \App\Models\CourseManagement::create([
                            'course_title' => $r->training_title,
                            'description' => 'Auto-created course for approved training request',
                            'status' => 'Active'
                          ]);
                          $courseId = $newCourse->course_id;
                          $debugInfo[] = "Auto-created course ID: {$courseId}";
                        } catch (\Exception $e) {
                          $debugInfo[] = "Failed to create course: " . $e->getMessage();
                        }
                      }

                      // ENHANCED: Check if exam questions exist and auto-generate if needed
                      $hasExamQuestions = false;
                      $questionCount = 0;
                      if ($courseId) {
                        $questionCount = \App\Models\ExamQuestion::where('course_id', $courseId)->count();
                        $hasExamQuestions = $questionCount > 0;

                        // If no questions exist, try to auto-generate for destination courses
                        if (!$hasExamQuestions && $course) {
                          $courseTitle = strtolower($course->course_title);
                          $isDestinationCourse = strpos($courseTitle, 'destination') !== false ||
                                                 strpos($courseTitle, 'location') !== false ||
                                                 strpos($courseTitle, 'knowledge') !== false;

                          if ($isDestinationCourse) {
                            try {
                              // Auto-generate basic questions for destination courses
                              $location = preg_replace('/\b(destination|knowledge|training|course)\b/i', '', $course->course_title);
                              $location = trim($location);

                              if (!empty($location)) {
                                $questions = [
                                  [
                                    'question' => "What is the primary purpose of learning about {$location}?",
                                    'options' => json_encode([
                                      'a' => 'To provide accurate travel information to clients',
                                      'b' => 'For personal vacation planning',
                                      'c' => 'To memorize facts',
                                      'd' => 'For academic purposes only'
                                    ]),
                                    'correct_answer' => 'To provide accurate travel information to clients'
                                  ],
                                  [
                                    'question' => "When advising clients about {$location}, what should be your priority?",
                                    'options' => json_encode([
                                      'a' => 'Selling the most expensive package',
                                      'b' => 'Understanding client needs and preferences',
                                      'c' => 'Promoting only popular destinations',
                                      'd' => 'Following a standard script'
                                    ]),
                                    'correct_answer' => 'Understanding client needs and preferences'
                                  ]
                                ];

                                foreach ($questions as $q) {
                                  \App\Models\ExamQuestion::create([
                                    'course_id' => $courseId,
                                    'question' => $q['question'],
                                    'options' => $q['options'],
                                    'correct_answer' => $q['correct_answer'],
                                    'type' => 'exam',
                                    'difficulty' => 'medium'
                                  ]);
                                }

                                $questionCount = count($questions);
                                $hasExamQuestions = true;
                                $debugInfo[] = "Auto-generated {$questionCount} questions for {$location}";
                              }
                            } catch (\Exception $e) {
                              $debugInfo[] = "Failed to auto-generate questions: " . $e->getMessage();
                            }
                          }
                        }

                        $debugInfo[] = $hasExamQuestions ? "Exam available ({$questionCount} questions)" : "No exam questions available";
                      }

                      // ENHANCED: Auto-create dashboard record if missing for approved requests
                      if ($courseId && $r->status == 'Approved') {
                        // Check for existing dashboard record with multiple criteria
                        $dashboardRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', Auth::user()->employee_id)
                          ->where(function($query) use ($courseId, $r) {
                              $query->where('course_id', $courseId)
                                    ->orWhere('training_title', $r->training_title);
                          })
                          ->first();

                        if (!$dashboardRecord) {
                          try {
                            // Ensure we have all required fields
                            $createData = [
                              'employee_id' => Auth::user()->employee_id,
                              'course_id' => $courseId,
                              'training_title' => $r->training_title,
                              'training_date' => now(),
                              'status' => 'Assigned',
                              'progress' => 0,
                              'assigned_by' => 1,
                              'last_accessed' => now(),
                              'expired_date' => now()->addDays(90),
                              'source' => 'approved_request',
                              'remarks' => 'Auto-created for approved request #' . $r->request_id
                            ];

                            $newRecord = \App\Models\EmployeeTrainingDashboard::create($createData);
                            $debugInfo[] = "Dashboard record auto-created (ID: {$newRecord->id})";
                          } catch (\Exception $e) {
                            $debugInfo[] = "Failed to create dashboard record: " . $e->getMessage();
                            \Log::error('Failed to auto-create dashboard record', [
                              'employee_id' => Auth::user()->employee_id,
                              'course_id' => $courseId,
                              'training_title' => $r->training_title,
                              'error' => $e->getMessage()
                            ]);
                          }
                        } else {
                          $debugInfo[] = "Dashboard record exists (ID: {$dashboardRecord->id})";

                          // Update existing record to ensure it's properly configured
                          try {
                            $dashboardRecord->update([
                              'status' => 'Assigned',
                              'source' => 'approved_request',
                              'last_accessed' => now(),
                              'remarks' => 'Updated for approved request #' . $r->request_id
                            ]);
                            $debugInfo[] = "Dashboard record updated";
                          } catch (\Exception $e) {
                            $debugInfo[] = "Failed to update dashboard record: " . $e->getMessage();
                          }
                        }
                      }

                      $debugString = implode(' | ', $debugInfo);

                      // Add final debug info
                      $debugInfo[] = "Final: courseId={$courseId}, hasQuestions={$hasExamQuestions}, questionCount={$questionCount}";
                      $debugString = implode(' | ', $debugInfo);
                    @endphp
                    {{-- Exam button - Always enabled --}}
                    @if($courseId)
                      <a href="/employee/exam/start/{{ $courseId }}"
                         class="btn btn-primary btn-sm"
                         title="Take Exam"
                         target="_blank">
                        <i class="fas fa-edit"></i> Take Exam
                      </a>
                    @else
                      <button class="btn btn-primary btn-sm"
                              onclick="startExam('{{ $r->training_title }}', '{{ $r->course_id ?? '' }}')">
                        <i class="fas fa-edit"></i> Take Exam
                      </button>
                    @endif


                  {{-- Edit button - only for pending requests --}}
                  @if($r->status == 'Pending')
                    <button class="btn btn-outline-warning btn-sm"
                            onclick="editRequestWithConfirmation('{{ $r->request_id }}', '{{ addslashes($r->training_title) }}', '{{ addslashes($r->reason) }}', '{{ $r->status }}', '{{ $r->requested_date }}')"
                            title="Edit Request">
                      <i class="bi bi-pencil"></i>
                    </button>
                  @endif

                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">No training requests</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Are you sure you want to delete this training request?</p>
        <div class="alert alert-warning">
          <strong>Training:</strong> <span id="deleteTrainingTitle"></span><br>
          <strong>Request ID:</strong> <span id="deleteRequestId"></span>
        </div>
        <p class="text-muted small">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteTrainingRequest()">
          <i class="fas fa-trash me-1"></i>Delete Request
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add --}}
<div class="modal fade" id="addTrainingRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
  <form action="{{ route('employee.my_trainings.store') }}" method="POST">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Request Training</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
          <input type="hidden" name="status" value="Pending">
          <input type="hidden" name="requested_date" value="{{ now()->format('Y-m-d') }}">
          <!-- Ensure current_level is always sent -->
          <input type="hidden" name="current_level" id="currentLevelInput" value="0">

          <!-- Course Selection Based on Assigned Trainings -->
          <div class="mb-3">
            <label class="form-label">Select Training</label>
            <select name="course_id" id="courseSelect" class="form-select" required>
              <option value="">Choose a training...</option>
            </select>
            <small class="text-muted">Select from your assigned upcoming trainings</small>
          </div>

          <!-- Training Title (auto-filled from course selection or manual entry) -->
          <div class="mb-3">
            <label class="form-label">Training Title</label>
            <input type="text" name="training_title" id="trainingTitle" class="form-control" required placeholder="Enter training title or select a course above">
          </div>

          <!-- Course Description (auto-filled) -->
          <div class="mb-3">
            <label class="form-label">Course Description</label>
            <textarea id="courseDescription" class="form-control" rows="2" readonly></textarea>
          </div>

          <div class="mb-3"><label class="form-label">Reason for Request</label>
            <textarea name="reason" class="form-control" rows="2" required placeholder="Please explain why you need this training..."></textarea></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit --}}
<div class="modal fade" id="editTrainingRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="editTrainingRequestForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Edit Training Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
          <div class="mb-3"><label class="form-label">Training Title</label>
            <input type="text" name="training_title" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Reason</label>
            <textarea name="reason" class="form-control" rows="2" required></textarea></div>
          <div class="mb-3"><label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="Pending">Pending</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
            </select></div>
          <div class="mb-3"><label class="form-label">Requested Date</label>
            <input type="date" name="requested_date" class="form-control" required></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- FontAwesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jQuery for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Initialize SweetAlert2 Toast configuration - Fixed syntax error
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

// Check for new upcoming trainings periodically
setInterval(() => {
  checkNewUpcomingTrainings();
}, 300000); // Check every 5 minutes

// Function to check for new upcoming trainings
function checkNewUpcomingTrainings() {
  fetch('{{ route("employee.my_trainings.index") }}')
    .then(response => response.json())
    .then(data => {
      if (data.upcomingTrainings && data.upcomingTrainings.length > 0) {
        // Filter out destination_assigned (accredited training centers)
        const filteredTrainings = data.upcomingTrainings.filter(training => training.source !== 'destination_assigned');
        
        filteredTrainings.forEach(training => {
          const existingRequest = document.querySelector(`tr[data-course-id="${training.course_id}"]`);
          if (!existingRequest) {
            autoCreateTrainingRequest(training);
          }
        });
      }
    })
    .catch(error => console.error('Error checking new trainings:', error));
}

// View Request Details
function viewRequestDetails(requestId, trainingTitle, reason, status, requestedDate) {
  const statusBadge = status === 'Approved' ?
    '<span class="badge bg-success">Approved</span>' :
    status === 'Rejected' ?
    '<span class="badge bg-danger">Rejected</span>' :
    '<span class="badge bg-warning text-dark">Pending</span>';

  // Escape special characters to prevent JavaScript syntax errors
  const safeRequestId = String(requestId).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const safeTrainingTitle = String(trainingTitle).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const safeReason = String(reason).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const safeRequestedDate = String(requestedDate).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

  Swal.fire({
    title: '<i class="fas fa-eye text-info"></i> Training Request Details',
    html: `
      <div class="text-start">
        <div class="row mb-3">
          <div class="col-4"><strong>Request ID:</strong></div>
          <div class="col-8">${safeRequestId}</div>
        </div>
        <div class="row mb-3">
          <div class="col-4"><strong>Training:</strong></div>
          <div class="col-8">${safeTrainingTitle}</div>
        </div>
        <div class="row mb-3">
          <div class="col-4"><strong>Reason:</strong></div>
          <div class="col-8">${safeReason}</div>
        </div>
        <div class="row mb-3">
          <div class="col-4"><strong>Status:</strong></div>
          <div class="col-8">${statusBadge}</div>
        </div>
        <div class="row mb-3">
          <div class="col-4"><strong>Requested Date:</strong></div>
          <div class="col-8">${safeRequestedDate}</div>
        </div>
      </div>
    `,
    width: '500px',
    showConfirmButton: true,
    confirmButtonText: '<i class="fas fa-times"></i> Close',
    confirmButtonColor: '#6c757d'
  });
}

// Request Training - Simplified without password verification
function requestTrainingWithConfirmation() {
  showRequestTrainingForm();
}

// Show Request Training Form
function showRequestTrainingForm() {
  const upcomingTrainings = @json($upcomingTrainings ?? []);
  const availableCourses = @json($availableCourses ?? []);

  let courseOptions = '<option value="">Choose a training...</option>';

  if (upcomingTrainings.length > 0) {
    // Filter out destination_assigned (accredited training centers) from dropdown
    const filteredTrainings = upcomingTrainings.filter(training => training.source !== 'destination_assigned');
    
    if (filteredTrainings.length > 0) {
      courseOptions += '<optgroup label="Your Assigned Trainings">';
      filteredTrainings.forEach(training => {
        courseOptions += `<option value="${training.course_id || training.training_title}"
                                  data-description="${training.assigned_by || 'System'} | Source: ${training.source || 'Unknown'}"
                                  data-training-id="${training.upcoming_id || ''}"
                                  data-source="${training.source || ''}">${training.training_title}</option>`;
      });
      courseOptions += '</optgroup>';
    }
  }

  if (upcomingTrainings.length === 0 && availableCourses.length > 0) {
    courseOptions += '<optgroup label="Available Courses">';
    availableCourses.forEach(course => {
      courseOptions += `<option value="${course.course_id}" data-description="${course.description || ''}">${course.course_title}</option>`;
    });
    courseOptions += '</optgroup>';
  }

  Swal.fire({
    title: '<i class="fas fa-plus-circle text-primary"></i> Request Training',
    html: `
      <form id="requestTrainingForm">
        <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
        <input type="hidden" name="status" value="Pending">
        <input type="hidden" name="requested_date" value="{{ now()->format('Y-m-d') }}">
        <input type="hidden" name="current_level" id="currentLevelInput" value="0">

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Select Training:</strong></label>
          <select name="course_id" id="courseSelectSwal" class="form-select" required>
            ${courseOptions}
          </select>
          <small class="text-muted">Select from your assigned upcoming trainings</small>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Training Title:</strong></label>
          <input type="text" name="training_title" id="trainingTitleSwal" class="form-control" required placeholder="Enter training title or select a course above">
        </div>

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Course Description:</strong></label>
          <textarea id="courseDescriptionSwal" class="form-control" rows="2" readonly></textarea>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Reason for Request:</strong></label>
          <textarea name="reason" class="form-control" rows="3" required placeholder="Please explain why you need this training..."></textarea>
        </div>
      </form>
    `,
    width: '600px',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-paper-plane"></i> Submit Request',
    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
    confirmButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
    didOpen: () => {
      // Add course selection handler
      document.getElementById('courseSelectSwal').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const trainingTitle = document.getElementById('trainingTitleSwal');
        const courseDescription = document.getElementById('courseDescriptionSwal');

        if (selectedOption.value) {
          let titleText = selectedOption.textContent.replace(' (Recommended)', '');
          trainingTitle.value = titleText;
          courseDescription.value = selectedOption.getAttribute('data-description') || '';
        } else {
          trainingTitle.value = '';
          courseDescription.value = '';
        }
      });
    },
    preConfirm: () => {
      const form = document.getElementById('requestTrainingForm');
      const formData = new FormData(form);

      if (!formData.get('training_title')) {
        Swal.showValidationMessage('Training title is required');
        return false;
      }
      if (!formData.get('reason')) {
        Swal.showValidationMessage('Reason is required');
        return false;
      }

      return formData;
    }
  }).then((result) => {
    if (result.isConfirmed) {
      submitRequestForm(result.value);
    }
  });
}

// Submit Request Form
function submitRequestForm(formData) {
  Swal.fire({
    title: 'Processing...',
    html: 'Submitting your training request...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch('{{ route("employee.my_trainings.store") }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: formData
  })
  .then(response => {
    // Check if response is ok (status 200-299)
    if (!response.ok) {
      return response.json().then(errorData => {
        // Handle specific error cases
        if (response.status === 401) {
          throw new Error(errorData.message || 'Invalid password. Please enter your correct password.');
        }
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }).catch(jsonError => {
        // If response is not JSON, create a generic error
        if (response.status === 401) {
          throw new Error('Invalid password. Please enter your correct password.');
        }
        throw new Error(`HTTP error! status: ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '<i class="fas fa-check-circle text-success"></i> Request Submitted!',
        html: `
          <div class="alert alert-success">
            <strong>Training request submitted successfully!</strong><br>
            Your request is now pending approval.
          </div>
        `,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        // Refresh training data to update counts
        if (typeof refreshTrainingData === 'function') {
          refreshTrainingData();
        }
        // Update UI instead of full page reload
        if (typeof refreshTrainingData === 'function') {
          setTimeout(() => {
            refreshTrainingData();
          }, 500);
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Submission Failed',
        html: `<div class="alert alert-danger">${data.message || 'Failed to submit training request'}</div>`,
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#dc3545'
      });
    }
  })
  .catch(error => {
    // Enhanced error handling for password verification
    const errorMessage = error.message;
    let title = 'Error';
    let icon = 'error';

    if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
      title = '<i class="fas fa-lock text-danger"></i> Password Verification Failed';
      icon = 'error';
    } else if (errorMessage.includes('Network')) {
      title = 'Network Error';
      icon = 'error';
    }

    Swal.fire({
      icon: icon,
      title: title,
      html: `<div class="alert alert-danger">${errorMessage}</div>`,
      confirmButtonText: 'Try Again',
      confirmButtonColor: '#dc3545'
    });
  });
}

// Edit Request - Simplified without password verification
function editRequestWithConfirmation(requestId, trainingTitle, reason, status, requestedDate) {
  showEditRequestForm(requestId, trainingTitle, reason, status, requestedDate);
}

// Show Edit Request Form
function showEditRequestForm(requestId, trainingTitle, reason, status, requestedDate) {
  // Escape special characters to prevent JavaScript syntax errors
  const safeTrainingTitle = String(trainingTitle).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const safeReason = String(reason).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const safeRequestedDate = String(requestedDate).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

  Swal.fire({
    title: '<i class="fas fa-edit text-warning"></i> Edit Training Request',
    html: `
      <form id="editRequestForm">
        <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Training Title:</strong></label>
          <input type="text" name="training_title" class="form-control" value="${safeTrainingTitle}" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Reason:</strong></label>
          <textarea name="reason" class="form-control" rows="3" required>${safeReason}</textarea>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Status:</strong></label>
          <select name="status" class="form-select" required>
            <option value="Pending" ${status === 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="Approved" ${status === 'Approved' ? 'selected' : ''}>Approved</option>
            <option value="Rejected" ${status === 'Rejected' ? 'selected' : ''}>Rejected</option>
          </select>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label"><strong>Requested Date:</strong></label>
          <input type="date" name="requested_date" class="form-control" value="${safeRequestedDate}" required>
        </div>
      </form>
    `,
    width: '500px',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-save"></i> Update Request',
    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
    confirmButtonColor: '#ffc107',
    cancelButtonColor: '#6c757d',
    preConfirm: () => {
      const form = document.getElementById('editRequestForm');
      const formData = new FormData(form);

      if (!formData.get('training_title')) {
        Swal.showValidationMessage('Training title is required');
        return false;
      }
      if (!formData.get('reason')) {
        Swal.showValidationMessage('Reason is required');
        return false;
      }

      return formData;
    }
  }).then((result) => {
    if (result.isConfirmed) {
      submitEditForm(requestId, result.value);
    }
  });
}

// Submit Edit Form
function submitEditForm(requestId, formData) {
  Swal.fire({
    title: 'Processing...',
    html: 'Updating your training request...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Add method override for PUT
  formData.append('_method', 'PUT');

  fetch(`{{ url('employee/my-trainings') }}/${requestId}`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: formData
  })
  .then(response => {
    // Check if response is ok (status 200-299)
    if (!response.ok) {
      return response.json().then(errorData => {
        // Handle specific error cases
        if (response.status === 401) {
          throw new Error(errorData.message || 'Invalid password. Please enter your correct password.');
        }
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }).catch(jsonError => {
        // If response is not JSON, create a generic error
        if (response.status === 401) {
          throw new Error('Invalid password. Please enter your correct password.');
        }
        throw new Error(`HTTP error! status: ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '<i class="fas fa-check-circle text-success"></i> Request Updated!',
        html: `
          <div class="alert alert-success">
            <strong>Training request updated successfully!</strong><br>
            Your changes have been saved.
          </div>
        `,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        // Update counts instead of full reload
        if (typeof refreshTrainingData === 'function') {
          refreshTrainingData();
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        html: `<div class="alert alert-danger">${data.message || 'Failed to update training request'}</div>`,
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#dc3545'
      });
    }
  })
  .catch(error => {
    // Enhanced error handling for password verification
    const errorMessage = error.message;
    let title = 'Error';
    let icon = 'error';

    if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
      title = '<i class="fas fa-lock text-danger"></i> Password Verification Failed';
      icon = 'error';
    } else if (errorMessage.includes('Network')) {
      title = 'Network Error';
      icon = 'error';
    }

    Swal.fire({
      icon: icon,
      title: title,
      html: `<div class="alert alert-danger">${errorMessage}</div>`,
      confirmButtonText: 'Try Again',
      confirmButtonColor: '#dc3545'
    });
  });
}

// Delete Request with Confirmation
function deleteRequestWithConfirmation(requestId, trainingTitle) {
  confirmDeleteRequest(requestId, trainingTitle);
}

// Confirm Delete Request
function confirmDeleteRequest(requestId, trainingTitle) {
  // Escape special characters to prevent JavaScript syntax errors
  const safeTrainingTitle = String(trainingTitle).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const safeRequestId = String(requestId).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

  Swal.fire({
    title: '<i class="fas fa-exclamation-triangle text-danger"></i> Confirm Deletion',
    html: `
      <div class="text-start">
        <div class="alert alert-danger">
          <strong><i class="fas fa-warning"></i> Warning:</strong><br>
          This action cannot be undone. The training request will be permanently deleted.
        </div>
        <div class="mb-3">
          <strong>Training:</strong> ${safeTrainingTitle}<br>
          <strong>Request ID:</strong> ${safeRequestId}
        </div>
        <p class="text-muted">Are you sure you want to delete this training request?</p>
      </div>
    `,
    width: '500px',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash"></i> Delete Request',
    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d'
  }).then((result) => {
    if (result.isConfirmed) {
      submitDeleteRequest(requestId);
    }
  });
}

// Submit Delete Request
function submitDeleteRequest(requestId) {
  Swal.fire({
    title: 'Processing...',
    html: 'Deleting training request...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch(`{{ url('employee/my-trainings') }}/${requestId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    }
  })
  .then(response => {
    // Check if response is ok (status 200-299)
    if (!response.ok) {
      return response.json().then(errorData => {
        // Handle specific error cases
        if (response.status === 401) {
          throw new Error(errorData.message || 'Invalid password. Please enter your correct password.');
        }
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }).catch(jsonError => {
        // If response is not JSON, create a generic error
        if (response.status === 401) {
          throw new Error('Invalid password. Please enter your correct password.');
        }
        throw new Error(`HTTP error! status: ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '<i class="fas fa-check-circle text-success"></i> Request Deleted!',
        html: `
          <div class="alert alert-success">
            <strong>Training request deleted successfully!</strong><br>
            The request has been removed from your records.
          </div>
        `,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        // Update counts instead of full reload
        if (typeof refreshTrainingData === 'function') {
          refreshTrainingData();
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Deletion Failed',
        html: `<div class="alert alert-danger">${data.message || 'Failed to delete training request'}</div>`,
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#dc3545'
      });
    }
  })
  .catch(error => {
    // Enhanced error handling for password verification
    const errorMessage = error.message;
    let title = 'Error';
    let icon = 'error';

    if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
      title = '<i class="fas fa-lock text-danger"></i> Password Verification Failed';
      icon = 'error';
    } else if (errorMessage.includes('Network')) {
      title = 'Network Error';
      icon = 'error';
    }

    Swal.fire({
      icon: icon,
      title: title,
      html: `<div class="alert alert-danger">${errorMessage}</div>`,
      confirmButtonText: 'Try Again',
      confirmButtonColor: '#dc3545'
    });
  });
}

// Delete Approved Request with Enhanced Warning
function deleteApprovedRequestWithConfirmation(requestId, trainingTitle) {
  Swal.fire({
    title: '<i class="fas fa-exclamation-triangle text-danger"></i> Delete Approved Training Request',
    html: `
      <div class="text-start mb-3">
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i> <strong>CRITICAL WARNING:</strong><br>
          You are about to delete an APPROVED training request. This will:
          <ul class="mt-2 mb-0">
            <li>Remove your training progress</li>
            <li>Delete any exam attempts</li>
            <li>Remove dashboard records</li>
            <li>Cannot be undone</li>
          </ul>
        </div>
        <div class="mb-3">
          <strong>Training:</strong> ${String(trainingTitle).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}<br>
          <strong>Request ID:</strong> ${String(requestId).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}
        </div>
        <div class="mb-3">
          <label class="form-label"><strong>Enter your password to confirm:</strong></label>
          <input type="password" id="deleteApprovedPassword" class="form-control" placeholder="Enter your password" required>
          <small class="text-muted">Password verification required for this critical action.</small>
        </div>
      </div>
    `,
    width: '500px',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete Everything',
    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    preConfirm: async function() {
      const password = document.getElementById('deleteApprovedPassword').value;
      if (!password) {
        Swal.showValidationMessage('Password is required');
        return false;
      }
      if (password.length < 3) {
        Swal.showValidationMessage('Password must be at least 3 characters');
        return false;
      }

      // Verify password with backend immediately
      try {
        Swal.showLoading();
        const response = await fetch('{{ route("employee.verify_password") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ password: password })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          Swal.showValidationMessage(data.message || 'Invalid password. Please enter your correct password.');
          return false;
        }

        return password;
      } catch (error) {
        Swal.showValidationMessage('Network error. Please try again.');
        return false;
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      submitApprovedRequestDeletion(requestId, trainingTitle, result.value);
    }
  });
}

// Submit Approved Request Deletion
function submitApprovedRequestDeletion(requestId, trainingTitle, password) {
  Swal.fire({
    title: 'Processing...',
    html: 'Deleting approved training request and related records...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch(`{{ url('employee/my-trainings') }}/${requestId}`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      _method: 'DELETE',
      password: password,
      delete_approved: true // Flag to indicate this is an approved request deletion
    })
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(errorData => {
        if (response.status === 401) {
          throw new Error(errorData.message || 'Invalid password. Please enter your correct password.');
        }
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }).catch(jsonError => {
        if (response.status === 401) {
          throw new Error('Invalid password. Please enter your correct password.');
        }
        throw new Error(`HTTP error! status: ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '<i class="fas fa-check-circle text-success"></i> Approved Request Deleted!',
        html: `
          <div class="alert alert-success">
            <strong>Approved training request deleted successfully!</strong><br>
            All related progress and dashboard records have been removed.
          </div>
        `,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        // Update counts instead of full reload
        if (typeof refreshTrainingData === 'function') {
          refreshTrainingData();
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Deletion Failed',
        html: `<div class="alert alert-danger">${data.message || 'Failed to delete approved training request'}</div>`,
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#dc3545'
      });
    }
  })
  .catch(error => {
    const errorMessage = error.message;
    let title = 'Error';
    let icon = 'error';

    if (errorMessage.includes('Invalid password') || errorMessage.includes('password')) {
      title = '<i class="fas fa-lock text-danger"></i> Password Verification Failed';
      icon = 'error';
    } else if (errorMessage.includes('Network')) {
      title = 'Network Error';
      icon = 'error';
    }

    Swal.fire({
      icon: icon,
      title: title,
      html: `<div class="alert alert-danger">${errorMessage}</div>`,
      confirmButtonText: 'Try Again',
      confirmButtonColor: '#dc3545'
    });
  });
}

// Automatically create training requests for all upcoming trainings
document.addEventListener('DOMContentLoaded', function() {
  const upcomingTrainings = @json($upcomingTrainings ?? []);

  // Process all upcoming trainings
  if (upcomingTrainings && upcomingTrainings.length > 0) {
    // Filter trainings that need requests and apply deduplication
    const seenTrainings = new Set();
    const trainingsNeedingRequests = upcomingTrainings.filter(training => {
      // Skip if already has request_id
      if (training.request_id) {
        return false;
      }
      
      // Skip if training title is empty or generic
      const rawTitle = training.training_title || '';
      if (!rawTitle.trim() || ['training course', 'unknown course', 'unknown', 'course', 'n/a'].includes(rawTitle.toLowerCase().trim())) {
        return false;
      }
      
      // Apply deduplication logic similar to PHP controller
      const normalizedTitle = rawTitle.toLowerCase()
        .replace(/\b(training|course|program|skills|knowledge|development|workshop|seminar)\b/gi, '')
        .replace(/\s+/g, ' ')
        .trim();
      
      // Create deduplication key
      const deduplicationKey = training.course_id ? 
        `course_${training.course_id}` : 
        `title_${normalizedTitle}`;
      
      // Check if already seen
      if (seenTrainings.has(deduplicationKey)) {
        return false;
      }
      
      // Skip if this is from a recently unassigned competency gap
      if (training.source && ['competency_gap', 'competency_assigned', 'admin_assigned'].includes(training.source)) {
        // This would require an AJAX call to check, but for now we'll rely on the PHP-side filtering
        // The PHP side already handles the $wasRecentlyUnassigned check
      }
      
      // Add to seen set
      seenTrainings.add(deduplicationKey);
      return true;
    });

    // Process unique trainings that need requests
    trainingsNeedingRequests.forEach(training => {
      // Add a small delay between requests to prevent overwhelming the server
      setTimeout(() => {
        autoCreateTrainingRequest(training);
      }, Math.random() * 1000); // Random delay up to 1 second
    });

    // Notification popup removed per user request
    // Process trainings silently without showing popup
  }
});

// Function to create or update training progress
  function createOrUpdateProgress(training) {
    const formData = new FormData();
    formData.append('employee_id', '{{ Auth::user()->employee_id }}');
    formData.append('training_title', training.training_title);
    formData.append('course_id', training.course_id || '');
    formData.append('status', 'In Progress');
    formData.append('progress', '0');
    formData.append('source', 'auto_request');
    formData.append('_token', '{{ csrf_token() }}');

    // Create or update progress
    fetch('/employee/training-progress/create-or-update', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Progress tracking initialized silently
        // Update progress data instead of full reload
        if (typeof refreshTrainingData === 'function') {
          refreshTrainingData();
        }
      }
    })
    .catch(error => console.error('Error updating progress:', error));
  }

// Function to show success notification for automatic request
function showAutoRequestSuccess(training) {
  const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });

  toast.fire({
    icon: 'success',
    title: `Automatic request created for: ${training.training_title}`
  });
}

// Function to automatically create training request and progress with enhanced synchronization
async function autoCreateTrainingRequest(training) {
  // Initialize SweetAlert2 toast
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  });

  try {
    // 1. First check for duplicates with enhanced checking
    const existingRequests = Array.from(document.querySelectorAll('tr[data-training-title], tr[data-course-id]'));
    const isDuplicate = existingRequests.some(tr => {
      const trainingTitle = tr.getAttribute('data-training-title');
      const courseId = tr.getAttribute('data-course-id');
      return (training.training_title && trainingTitle === training.training_title) ||
             (training.course_id && courseId === training.course_id?.toString());
    });

    // If duplicate found, update progress and return
    if (isDuplicate) {
      // Training update handled silently
      await createOrUpdateProgress(training);
      return;
    }

    // 2. Processing training silently
    // 3. Create the training request
    const formData = new FormData();
    formData.append('employee_id', '{{ Auth::user()->employee_id }}');
    formData.append('training_title', training.training_title);
    formData.append('course_id', training.course_id || '');
    formData.append('status', 'Approved'); // Auto-approve upcoming trainings
    formData.append('requested_date', '{{ now()->format("Y-m-d") }}');
    formData.append('reason', 'Automatically enrolled from upcoming trainings');
    formData.append('current_level', training.current_level || '0');
    formData.append('source', training.source || 'upcoming_training');

    const response = await fetch('{{ route("employee.my_trainings.store") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: formData
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success) {
      // 4. Create progress tracking
      const progressCreated = await createOrUpdateProgress(training);
      if (!progressCreated) {
        throw new Error('Failed to initialize progress tracking');
      }

      // 5. Create notification
      try {
        const notifFormData = new FormData();
        notifFormData.append('message', `New training automatically enrolled: ${training.training_title}`);
        notifFormData.append('type', 'training');
        notifFormData.append('_token', '{{ csrf_token() }}');

        await fetch('/employee/my-trainings/notifications/store', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: notifFormData
        });
      } catch (notifError) {
        console.warn('Notification creation failed:', notifError);
        // Continue execution even if notification fails
      }

      // 6. UI counters removed - no longer needed since counters are hidden

      // 7. Show success notification
      await Toast.fire({
        icon: 'success',
        title: 'Success!',
        text: `Successfully enrolled in: ${training.training_title}`,
        background: '#d1e7dd',
        color: '#0a3622'
      });

      // 8. Update data instead of full page reload
      if (typeof refreshTrainingData === 'function') {
        setTimeout(() => {
          refreshTrainingData();
        }, 1000);
      }
    } else {
      throw new Error(data.message || 'Failed to create training request');
    }
  } catch (error) {
    console.error('Error in autoCreateTrainingRequest:', error);

    await Toast.fire({
      icon: 'error',
      title: 'Error',
      text: `Failed to process training: ${error.message}`,
      background: '#f8d7da',
      color: '#842029'
    });
  }
}

// Handle automatic training requests and modal functionality
document.getElementById('addTrainingRequestModal')?.addEventListener('show.bs.modal', function () {
  const courseSelect = document.getElementById('courseSelect');
  if (!courseSelect) return;

  const upcomingTrainings = @json($upcomingTrainings ?? []);
  const availableCourses = @json($availableCourses ?? []);

  courseSelect.innerHTML = '<option value="">Choose a course...</option>';

  if (upcomingTrainings.length > 0) {
    const assignedGroup = document.createElement('optgroup');
    assignedGroup.label = 'Your Assigned Trainings';

    upcomingTrainings.forEach(training => {
      const option = document.createElement('option');
      option.value = training.course_id || training.training_title;
      option.textContent = training.training_title;
      option.setAttribute('data-description', `Assigned by: ${training.assigned_by || 'System'} | Source: ${training.source || 'Unknown'}`);
      assignedGroup.appendChild(option);
    });

    courseSelect.appendChild(assignedGroup);
  }

  if (upcomingTrainings.length === 0 && availableCourses.length > 0) {
    const allCoursesGroup = document.createElement('optgroup');
    allCoursesGroup.label = 'Available Courses';

    availableCourses.forEach(course => {
      const option = document.createElement('option');
      option.value = course.course_id;
      option.textContent = course.course_title;
      option.setAttribute('data-description', course.description || '');
      allCoursesGroup.appendChild(option);
    });

    courseSelect.appendChild(allCoursesGroup);
  }

  courseSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const trainingTitle = document.getElementById('trainingTitle');
    const courseDescription = document.getElementById('courseDescription');

    if (selectedOption.value) {
      let titleText = selectedOption.textContent.replace(' (Recommended)', '');
      trainingTitle.value = titleText;
      courseDescription.value = selectedOption.getAttribute('data-description') || '';
    } else {
      trainingTitle.value = '';
      courseDescription.value = '';
    }
  });
});

document.getElementById('editTrainingRequestModal')?.addEventListener('show.bs.modal', function (e) {
  const b = e.relatedTarget;
  const f = document.getElementById('editTrainingRequestForm');
  if (!b || !f) return;
  
  const id = b.getAttribute('data-id');
  f.action = "{{ url('employee/my-trainings') }}/" + id;

  f.querySelector('[name="training_title"]').value = b.getAttribute('data-title');
  f.querySelector('[name="reason"]').value = b.getAttribute('data-reason');
  f.querySelector('[name="status"]').value = b.getAttribute('data-status');
  f.querySelector('[name="requested_date"]').value = b.getAttribute('data-date');
});
// Initialize global objects to prevent undefined errors - MUST BE FIRST
try {
  if (typeof window.translationService === 'undefined') {
    window.translationService = {
      translate: function(key, params) { return key; },
      get: function(key, params) { return key; },
      trans: function(key, params) { return key; },
      choice: function(key, count, params) { return key; }
    };
  }

  // Add global trans function
  if (typeof window.trans === 'undefined') {
    window.trans = function(key, params) { return key; };
  }

  // Add app object if missing
  if (typeof window.app === 'undefined') {
    window.app = {};
  }

  console.log('Global objects initialized successfully');
} catch (error) {
  console.error('Error initializing global objects:', error);
}

// Robust handler to always set current_level on course selection
document.addEventListener('DOMContentLoaded', function() {
  
  console.log('🔧 Setting up course selection handler...');
  
  // Use a more robust element selection with retry
  function setupCourseHandler() {
    const courseSelect = document.getElementById('courseSelect');
    const trainingTitle = document.getElementById('trainingTitle');
    
    console.log('Course select element:', courseSelect);
    console.log('Training title input:', trainingTitle);

    if (courseSelect && trainingTitle) {
      console.log('✅ Both elements found, adding event listener');
      
      // Remove any existing event listeners to prevent duplicates
      const newCourseSelect = courseSelect.cloneNode(true);
      courseSelect.parentNode.replaceChild(newCourseSelect, courseSelect);
      
      newCourseSelect.addEventListener('change', function() {
        try {
          console.log('📝 Course selection changed');
          const selectedOption = this.options[this.selectedIndex];
          const courseDescription = document.getElementById('courseDescription');
          const currentLevelInput = document.getElementById('currentLevelInput');
          
          if (selectedOption && selectedOption.value) {
            let titleText = selectedOption.textContent.replace(' (Recommended)', '');
            trainingTitle.value = titleText;
            
            if (courseDescription) {
              courseDescription.value = selectedOption.getAttribute('data-description') || '';
            }
            
            if (currentLevelInput) {
              const currentLevel = selectedOption.getAttribute('data-current-level');
              currentLevelInput.value = currentLevel !== null ? currentLevel : 0;
            }
            
            console.log('📋 Updated training title:', titleText);
          } else {
            trainingTitle.value = '';
            if (courseDescription) {
              courseDescription.value = '';
            }
            if (currentLevelInput) {
              currentLevelInput.value = 0;
            }
          }
        } catch (error) {
          console.error('Error in course selection handler:', error);
        }
      });
      
      console.log('✅ Event listener added successfully');
      return true;
    } else {
      console.log('❌ Elements not found:', { courseSelect: !!courseSelect, trainingTitle: !!trainingTitle });
      return false;
    }
  }
  
  // Try to setup the handler, with retries if elements are not ready
  if (!setupCourseHandler()) {
    // Retry after a short delay
    setTimeout(function() {
      if (!setupCourseHandler()) {
        // Final retry after longer delay
        setTimeout(setupCourseHandler, 2000);
      }
    }, 500);
  }
});
</script>

<script>
  // Function to start exam
  function startExam(trainingTitle, existingCourseId) {
    if (existingCourseId && existingCourseId !== '') {
      // If we have a course ID, redirect directly to start exam
      window.open(`/employee/exam/start/${existingCourseId}`, '_blank');
    } else {
      // If no course ID, show error message
      Swal.fire({
        icon: 'error',
        title: 'Cannot Start Exam',
        html: `
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>No Course Found</strong><br>
            Unable to find a course for "${trainingTitle}". Please contact your administrator.
          </div>
        `,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    }
  }

// Function to create or update training progress with enhanced error handling and notifications
async function createOrUpdateProgress(training) {
  // Configure Toast notifications
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  });

  try {
    // Create FormData object with enhanced error checking
    const formData = new FormData();

    if (!training || !training.training_title) {
      throw new Error('Invalid training data provided');
    }

    formData.append('employee_id', '{{ Auth::user()->employee_id }}');
    formData.append('training_title', training.training_title);
    formData.append('course_id', training.course_id || '');
    formData.append('status', 'Not Started');
    formData.append('progress', '0');
    formData.append('source', training.source || 'auto_request');
    formData.append('_token', '{{ csrf_token() }}');

    // Add additional training info if available
    if (training.start_date) formData.append('start_date', training.start_date);
    if (training.end_date) formData.append('end_date', training.end_date);
    if (training.assigned_by) formData.append('assigned_by', training.assigned_by);
    if (training.current_level) formData.append('current_level', training.current_level);

    // Create or update progress
    const response = await fetch('/employee/training/progress', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: formData
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to update progress');
    }

    const data = await response.json();

    if (data.success) {
      // Update progress data instead of full reload
      if (typeof refreshTrainingData === 'function') {
        setTimeout(() => {
          refreshTrainingData();
        }, 500);
      }
      return true;
    } else {
      throw new Error(data.message || 'Failed to update progress');
    }
  } catch (error) {
    console.error('Error in createOrUpdateProgress:', error);
    return false;
  }
}  // Function to refresh training requests (silent refresh without loading popup)
  function refreshTrainingRequests() {
    // Silent refresh without intrusive loading popup
    if (typeof refreshTrainingData === 'function') {
      refreshTrainingData();
    }
  }

  // Remove all .modal-backdrop elements on page load and after any modal event
  function removeAllModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
      backdrop.remove();
    });
  }
  window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
  document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
  document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);


  // Delete confirmation and execution
  let deleteRequestId = null;

  function confirmDelete(requestId, trainingTitle) {
    deleteRequestId = requestId;
    document.getElementById('deleteRequestId').textContent = requestId;
    document.getElementById('deleteTrainingTitle').textContent = trainingTitle;

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
  }

  function deleteTrainingRequest() {
    if (!deleteRequestId) {
      console.error('No deleteRequestId found');
      return;
    }

    const button = document.getElementById('confirmDeleteBtn');
    if (!button) {
      console.error('Delete button not found');
      return;
    }

    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
    button.disabled = true;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
      console.error('CSRF token not found');
      button.innerHTML = originalText;
      button.disabled = false;
      showToast('error', 'CSRF token not found');
      return;
    }

    console.log('Deleting request ID:', deleteRequestId);
    console.log('URL:', `{{ url('employee/my-trainings') }}/${deleteRequestId}`);

    fetch(`{{ url('employee/my-trainings') }}/${deleteRequestId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);

      if (data.success) {
        // Show success message
        showToast('success', data.message || 'Training request deleted successfully');

        // Close modal
        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
        if (deleteModal) {
          deleteModal.hide();
        }

        // Update data instead of full page reload
        if (typeof refreshTrainingData === 'function') {
          setTimeout(() => {
            refreshTrainingData();
          }, 500);
        }
      } else {
        button.innerHTML = originalText;
        button.disabled = false;
        showToast('error', data.error || data.message || 'Failed to delete training request');
      }
    })
    .catch(error => {
      console.error('Delete error:', error);
      button.innerHTML = originalText;
      button.disabled = false;
      showToast('error', 'Network error: ' + error.message);
    });
  }

  // Toast notification function
  function showToast(type, message) {
    // Use SweetAlert2 for toasts
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    // Map type to icon
    let icon = 'success';
    if (type === 'error') icon = 'error';
    else if (type === 'info') icon = 'info';
    else if (type === 'warning') icon = 'warning';

    Toast.fire({
      icon: icon,
      title: message
    });
  }

</script>
