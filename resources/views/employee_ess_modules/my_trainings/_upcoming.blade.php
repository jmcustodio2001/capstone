<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom">
    <h4 class="fw-bold mb-0">Upcoming Trainings</h4>
  </div>

      {{-- Flash Messages --}}
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Training ID</th>
            <th>Training Title</th>
            <th>Start Date</th>
            <th>Expired Date</th>
            <th>Status</th>
            <th>Source</th>
            <th>Assigned By</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $uniqueUpcoming = collect($upcoming)
              ->unique('upcoming_id')
              ->unique(function($item) {
                return isset($item->course_id) ? 'course_' . $item->course_id : $item->upcoming_id;
              });

            // Check if there are any admin-assigned or competency gap assigned courses
            $hasAdminAssigned = $uniqueUpcoming->contains(function($item) {
              $source = is_array($item) ? ($item['source'] ?? null) : ($item->source ?? null);
              return $source === 'admin_assigned' || $source === 'competency_gap' || $source === 'competency_assigned';
            });
          @endphp
          @forelse($uniqueUpcoming as $u)
            @php
              $source = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
            @endphp
            <tr>
              <td>
                @php
                  $upcomingId = is_array($u) ? ($u['upcoming_id'] ?? '') : ($u->upcoming_id ?? '');
                @endphp
                {{ $upcomingId }}
              </td>
              <td>
                @php
                  $trainingTitle = is_array($u) ? ($u['training_title'] ?? '') : ($u->training_title ?? '');
                @endphp
                {{ $trainingTitle }}
                @php
                  $showProgress = false;

                  // Access data safely from array or object
                  $source = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                  $deliveryMode = is_array($u) ? ($u['delivery_mode'] ?? null) : ($u->delivery_mode ?? null);
                  $progress = is_array($u) ? ($u['progress'] ?? 0) : ($u->progress ?? 0);

                  // For destination knowledge training, only show progress if delivery mode is "Online Training"
                  if($source == 'destination_assigned') {
                    if($deliveryMode === 'Online Training') {
                      $showProgress = true;
                    }
                  } elseif($source == 'competency_gap' || $source == 'competency_assigned') {
                    // For competency gap assignments, show progress if available
                    if($progress > 0) {
                      $showProgress = true;
                    }
                  } else {
                    // For other training types, show progress if it exists and has online-related delivery mode
                    if($progress > 0 && $deliveryMode && (strtolower($deliveryMode) == 'online' || strtolower($deliveryMode) == 'e-learning' || strtolower($deliveryMode) == 'virtual')) {
                      $showProgress = true;
                    }
                  }
                @endphp

                @php
                  $progressValue = is_array($u) ? ($u['progress'] ?? 0) : ($u->progress ?? 0);
                @endphp
                @if($showProgress && $progressValue > 0)
                  <div class="progress mt-1" style="height: 4px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $progressValue }}%"></div>
                  </div>
                  <small class="text-muted">{{ $progressValue }}% complete</small>
                @endif
              </td>
              <td>
                @php
                  $startDate = is_array($u) ? ($u['start_date'] ?? null) : ($u->start_date ?? null);
                @endphp
                {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : '' }}
              </td>
              <td>
                @php
                  // Get expired_date from multiple sources, not just the training data passed by controller
                  $finalExpiredDate = is_array($u) ? ($u['expired_date'] ?? null) : ($u->expired_date ?? null);
                  $employeeId = is_array($u) ? ($u['employee_id'] ?? null) : ($u->employee_id ?? null);
                  $courseId = is_array($u) ? ($u['course_id'] ?? null) : ($u->course_id ?? null);
                  $trainingTitle = is_array($u) ? ($u['training_title'] ?? '') : ($u->training_title ?? '');

                  // If we don't have an expired date yet, check other potential sources
                  if (!$finalExpiredDate && $courseId) {
                    // Try Course Management table
                    $course = \App\Models\CourseManagement::where('course_id', $courseId)->first();
                    if ($course && $course->expired_date) {
                      $finalExpiredDate = $course->expired_date;
                    }
                  }

                  // Try Destination Knowledge Training (for destination-specific courses)
                  if (!$finalExpiredDate && $trainingTitle && $employeeId) {
                    try {
                      // Clean up training title for matching
                      $cleanTitle = str_replace(['Training', 'Course', 'Program'], '', $trainingTitle);

                      // Check if this is a destination course by title
                      if (stripos($trainingTitle, 'destination') !== false) {
                        // Check if destination_knowledge_trainings table exists
                        if (\Illuminate\Support\Facades\Schema::hasTable('destination_knowledge_trainings')) {
                          $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                              ->where('destination_name', 'LIKE', '%' . trim($cleanTitle) . '%')
                              ->first();

                          if ($destinationTraining && $destinationTraining->expired_date) {
                            $finalExpiredDate = $destinationTraining->expired_date;
                          }
                        }
                      }
                    } catch (\Exception $e) {
                      // Silently handle any database errors
                      \Log::warning('Error querying destination knowledge trainings: ' . $e->getMessage());
                    }
                  }

                  // Try Competency Gap table (for competency-based training)
                  if (!$finalExpiredDate && $trainingTitle && $employeeId) {
                    try {
                      $competencyName = str_replace(['Training', 'Course', 'Program'], '', $trainingTitle);
                      
                      // Check if competency_gaps table exists before querying
                      if (\Illuminate\Support\Facades\Schema::hasTable('competency_gaps')) {
                        $competencyGap = \App\Models\CompetencyGap::where('employee_id', $employeeId)
                            ->whereHas('competency', function($query) use ($competencyName) {
                                $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                            })
                            ->first();

                        if ($competencyGap && $competencyGap->expired_date) {
                          $finalExpiredDate = $competencyGap->expired_date;
                        }
                      }
                    } catch (\Exception $e) {
                      // Silently handle any database errors
                      \Log::warning('Error querying competency gaps: ' . $e->getMessage());
                    }
                  }
                @endphp

                @if($finalExpiredDate && $finalExpiredDate != '' && $finalExpiredDate != '0000-00-00 00:00:00')
                  @php
                    $expiredDate = \Carbon\Carbon::parse($finalExpiredDate)->setTimezone('Asia/Shanghai');
                    $now = \Carbon\Carbon::now()->setTimezone('Asia/Shanghai');
                    $daysUntilExpiry = $now->diffInDays($expiredDate, false);

                    if ($daysUntilExpiry < 0) {
                      $colorClass = 'text-danger fw-bold';
                      $bgClass = 'bg-danger';
                      $status = 'EXPIRED';
                    } elseif ($daysUntilExpiry <= 7) {
                      $colorClass = 'text-warning fw-bold';
                      $bgClass = 'bg-warning';
                      $status = 'URGENT';
                    } elseif ($daysUntilExpiry <= 30) {
                      $colorClass = 'text-info fw-bold';
                      $bgClass = 'bg-info';
                      $status = 'SOON';
                    } else {
                      $colorClass = 'text-success fw-bold';
                      $bgClass = 'bg-success';
                      $status = 'ACTIVE';
                    }
                  @endphp
                  <div class="d-flex flex-column align-items-center justify-content-center text-center">
                    <span class="{{ $colorClass }}">{{ $expiredDate->format('M d, Y') }}</span>
                    <small class="badge {{ $bgClass }} text-white mt-1">{{ $status }}</small>
                    @if($daysUntilExpiry > 0)
                      <small class="text-muted">{{ floor($daysUntilExpiry) }} days left</small>
                    @elseif($daysUntilExpiry < 0)
                      @php $overdueDays = floor(abs($daysUntilExpiry)); @endphp
                      @if($overdueDays > 0)
                        <small class="text-danger">{{ $overdueDays }} days overdue</small>
                      @endif
                    @endif
                  </div>
                @else
                  <span class="badge bg-secondary"></span>
                @endif
              </td>
              <td>
                @php
                  // Access data safely from array or object
                  $currentProgress = 0;
                  $currentStatus = 'Not Started';
                  $courseIdForProgress = is_array($u) ? ($u['course_id'] ?? null) : ($u->course_id ?? null);
                  $employeeIdForProgress = is_array($u) ? ($u['employee_id'] ?? null) : ($u->employee_id ?? null);

                  if (is_array($u)) {
                    $currentProgress = $u['progress'] ?? 0;
                    $currentStatus = $u['status'] ?? 'Not Started';
                  } elseif (is_object($u)) {
                    $currentProgress = $u->progress ?? 0;
                    $currentStatus = $u->status ?? 'Not Started';
                  }

                  // Check for exam progress if we have employee_id and course_id
                  if ($employeeIdForProgress && $courseIdForProgress) {
                    // Use exam progress instead of raw progress if available
                    $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeIdForProgress, $courseIdForProgress);
                    if ($combinedProgress > 0) {
                      $currentProgress = $combinedProgress;
                    }
                  }

                  // Get source value for progress checking
                  $sourceValue = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                  
                  // For destination-specific training, check if we can get more accurate progress info
                  if ($sourceValue == 'destination_assigned' && $employeeIdForProgress && $trainingTitle) {
                    try {
                      // Clean up training title for matching
                      $cleanDestinationName = str_replace(['training', 'course', 'program'], '', strtolower($trainingTitle));

                      // Check if destination_knowledge_trainings table exists
                      if (\Illuminate\Support\Facades\Schema::hasTable('destination_knowledge_trainings')) {
                        // Check for destination knowledge specific progress
                        $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeIdForProgress)
                            ->where('destination_name', 'LIKE', '%' . trim($cleanDestinationName) . '%')
                            ->first();

                        if ($destinationRecord && $destinationRecord->progress > 0) {
                          $currentProgress = $destinationRecord->progress;
                        }
                      }
                    } catch (\Exception $e) {
                      // Silently handle any database errors
                      \Log::warning('Error querying destination progress: ' . $e->getMessage());
                    }
                  }

                  // Check if expired using the calculated expired date from above
                  $isTrainingExpired = false;
                  if ($finalExpiredDate) {
                    $expiredDate = \Carbon\Carbon::parse($finalExpiredDate);
                    $isTrainingExpired = \Carbon\Carbon::now()->gt($expiredDate);
                  }

                  // Determine final status with expiry consideration
                  if ($isTrainingExpired && $currentProgress < 100) {
                    $finalStatus = 'Expired';
                    $badgeClass = 'bg-danger';
                    $textClass = 'text-white';
                  } elseif ($currentProgress >= 100) {
                    $finalStatus = 'Completed';
                    $badgeClass = 'bg-success';
                    $textClass = 'text-white';
                  } elseif ($currentProgress > 0) {
                    $finalStatus = 'In Progress';
                    $badgeClass = 'bg-warning';
                    $textClass = 'text-dark';
                  } elseif ($currentStatus == 'Assigned') {
                    $finalStatus = 'Assigned';
                    $badgeClass = 'bg-info';
                    $textClass = 'text-dark';
                  } elseif ($currentStatus == 'Completed to Assign') {
                    $finalStatus = 'Completed to Assign';
                    $badgeClass = 'bg-success';
                    $textClass = 'text-white';
                  } elseif ($currentStatus == 'Scheduled') {
                    $finalStatus = 'Scheduled';
                    $badgeClass = 'bg-primary';
                    $textClass = 'text-white';
                  } elseif ($currentStatus == 'Ongoing') {
                    $finalStatus = 'Ongoing';
                    $badgeClass = 'bg-success';
                    $textClass = 'text-white';
                  } elseif ($currentStatus == 'Active') {
                    $finalStatus = 'Active';
                    $badgeClass = 'bg-success';
                    $textClass = 'text-white';
                  } else {
                    $finalStatus = 'Not Started';
                    $badgeClass = 'bg-secondary';
                    $textClass = 'text-white';
                  }
                @endphp
                <span class="badge {{ $badgeClass }} {{ $textClass }}">{{ $finalStatus }}</span>
              </td>
              <td>
                @php
                  $sourceValue = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                @endphp
                @if($sourceValue)
                  @if($sourceValue == 'admin_assigned')
                    <span class="badge bg-danger">Admin Assigned</span>
                  @elseif($sourceValue == 'competency_assigned' || $sourceValue == 'competency_gap')
                    <span class="badge bg-warning text-dark">Competency Gap</span>
                  @elseif($sourceValue == 'destination_assigned')
                    <span class="badge bg-info">Destination Training</span>
                  @elseif($sourceValue == 'auto_assigned')
                    <span class="badge bg-success">Auto Assigned</span>
                  @else
                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $sourceValue ?? 'Unknown')) }}</span>
                  @endif
                @else
                  <span class="badge bg-secondary"></span>
                @endif
              </td>
              <td>
                @php
                  $assignedByName = is_array($u) ? ($u['assigned_by'] ?? null) : ($u->assigned_by ?? null);
                  $assignedDate = is_array($u) ? ($u['assigned_date'] ?? null) : ($u->assigned_date ?? null);

                  // Clean up unwanted text from assigned_by
                  if ($assignedByName && str_contains($assignedByName, '(competency_auto_assigned')) {
                      $assignedByName = 'System Auto-Assign';
                  }
                  
                  // If still empty, try to get from other sources
                  if (!$assignedByName) {
                    $assignedByName = is_array($u) ? ($u['assigned_by_name'] ?? null) : ($u->assigned_by_name ?? null);
                  }
                @endphp
                @if($assignedByName)
                  <div class="d-flex flex-column">
                    <span class="fw-bold text-primary">{{ $assignedByName }}</span>
                    @if($assignedDate)
                      <small class="text-muted">{{ \Carbon\Carbon::parse($assignedDate)->format('M d, Y') }}</small>
                    @endif
                  </div>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="text-center">
                @php
                  $sourceCheck = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
                  $upcomingIdCheck = is_array($u) ? ($u['upcoming_id'] ?? null) : ($u->upcoming_id ?? null);
                  $trainingTitleCheck = is_array($u) ? ($u['training_title'] ?? null) : ($u->training_title ?? null);
                  $startDateCheck = is_array($u) ? ($u['start_date'] ?? null) : ($u->start_date ?? null);
                  $endDateCheck = is_array($u) ? ($u['end_date'] ?? null) : ($u->end_date ?? null);
                  $statusCheck = is_array($u) ? ($u['status'] ?? null) : ($u->status ?? '');
                  $assignedByCheck = is_array($u) ? ($u['assigned_by'] ?? null) : ($u->assigned_by ?? null);
                  $assignedDateCheck = is_array($u) ? ($u['assigned_date'] ?? null) : ($u->assigned_date ?? null);
                  $courseIdCheck = is_array($u) ? ($u['course_id'] ?? null) : ($u->course_id ?? null);
                  $destinationTrainingId = is_array($u) ? ($u['destination_training_id'] ?? null) : ($u->destination_training_id ?? null);
                @endphp
                
                <button class="btn btn-info btn-sm" 
                        data-bs-toggle="modal" 
                        data-bs-target="#trainingDetailsModal"
                        data-training-id="{{ $upcomingIdCheck }}"
                        data-training-title="{{ $trainingTitleCheck }}"
                        data-start-date="{{ $startDateCheck }}"
                        data-end-date="{{ $endDateCheck }}"
                        data-status="{{ $statusCheck }}"
                        data-source="{{ $sourceCheck }}"
                        data-assigned-by="{{ $assignedByCheck }}"
                        data-assigned-date="{{ $assignedDateCheck }}"
                        data-course-id="{{ $courseIdCheck }}"
                        data-destination-id="{{ $destinationTrainingId }}"
                        title="View Training Details">
                  <i class="fas fa-eye me-1"></i>View Details
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">No upcoming trainings</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
</div>
</div>

{{-- Training Details Modal --}}
<div class="modal fade" id="trainingDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="trainingDetailsModalLabel">Training Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label fw-bold">Training Title:</label>
              <p id="modalTrainingTitle" class="form-control-plaintext">-</p>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Status:</label>
              <p id="modalStatus" class="form-control-plaintext">-</p>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Source:</label>
              <p id="modalSource" class="form-control-plaintext">-</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label fw-bold">Start Date:</label>
              <p id="modalStartDate" class="form-control-plaintext">-</p>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">End Date:</label>
              <p id="modalEndDate" class="form-control-plaintext">-</p>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Assigned By:</label>
              <p id="modalAssignedBy" class="form-control-plaintext">-</p>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label fw-bold">Assigned Date:</label>
              <p id="modalAssignedDate" class="form-control-plaintext">-</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


{{-- Edit Modal --}}
<div class="modal fade" id="editUpcomingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="editUpcomingForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Upcoming Training</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          {{-- Validation Errors --}}
          @if (isset($errors) && is_object($errors) && $errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
          <div class="mb-3">
            <label class="form-label">Training Title</label>
            <input type="text" name="training_title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="Scheduled">Scheduled</option>
              <option value="Ongoing">Ongoing</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Decline Reason Modal --}}
<div class="modal fade" id="declineReasonModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Decline Training</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to decline this destination knowledge training?</p>
        <div class="mb-3">
          <label class="form-label">Reason for declining (optional):</label>
          <textarea id="declineReason" class="form-control" rows="3" placeholder="Please provide a reason for declining this training..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeclineBtn">
          <i class="fas fa-times me-1"></i>Decline Training
        </button>
      </div>
    </div>
  </div>
</div>


<script>
// --- Destination Knowledge Training JS ---
let currentTrainingId = null;

function respondToDestinationTraining(trainingId, action, btn) {
  currentTrainingId = trainingId;
  if (action === 'accept') {
    processDestinationTrainingResponse(trainingId, 'accept', null);
  } else if (action === 'decline') {
    const declineModal = new bootstrap.Modal(document.getElementById('declineReasonModal'));
    declineModal.show();
  }
}

document.getElementById('confirmDeclineBtn')?.addEventListener('click', function() {
  const reason = document.getElementById('declineReason').value;
  processDestinationTrainingResponse(currentTrainingId, 'decline', reason);
  const declineModal = bootstrap.Modal.getInstance(document.getElementById('declineReasonModal'));
  declineModal.hide();
  document.getElementById('declineReason').value = '';
});

function processDestinationTrainingResponse(trainingId, action, reason) {
  const endpoint = action === 'accept' ? '/employee/destination-training/accept' : '/employee/destination-training/decline';
  const buttons = document.querySelectorAll(`[onclick*="${trainingId}"]`);
  buttons.forEach(btn => {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
  });
  const payload = {
    training_id: trainingId,
    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  };
  if (action === 'decline' && reason) {
    payload.reason = reason;
  }
  fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('success', data.message);
      // Update the UI to reflect the new status
      const row = document.querySelector(`[onclick*="${trainingId}"]`).closest('tr');
      const statusCell = row.querySelector('td:nth-child(5)');
      const actionCell = row.querySelector('td:nth-child(8)');
      if (action === 'accept') {
        // Update status to "Active" for all accepted trainings
        statusCell.innerHTML = '<span class="badge bg-primary text-white">Active</span>';
        actionCell.innerHTML = '<span class="badge bg-primary">Active</span>';
      } else {
        statusCell.innerHTML = '<span class="badge bg-secondary">Declined</span>';
        actionCell.innerHTML = '<span class="badge bg-secondary">Declined</span>';
      }
      setTimeout(() => {
        const toastType = action === 'accept' ? 'success' : 'info';
        showToast(toastType, `Your ${action === 'accept' ? 'acceptance' : 'decline'} has been recorded and admin has been notified.`);
      }, 2000);
    } else {
      showToast('error', data.message || `Failed to ${action} training`);
      buttons.forEach(btn => {
        btn.disabled = false;
        if (action === 'accept') {
          btn.innerHTML = '<i class="fas fa-check"></i> Accept';
        } else {
          btn.innerHTML = '<i class="fas fa-times"></i> Decline';
        }
      });
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('error', `Network error occurred while ${action}ing training`);
    buttons.forEach(btn => {
      btn.disabled = false;
      if (action === 'accept') {
        btn.innerHTML = '<i class="fas fa-check"></i> Accept';
      } else {
        btn.innerHTML = '<i class="fas fa-times"></i> Decline';
      }
    });
  });
}

function viewTrainingDetails(trainingId) {
  const detailsModal = new bootstrap.Modal(document.getElementById('trainingDetailsModal'));
  detailsModal.show();
  document.getElementById('trainingDetailsContent').innerHTML = `
    <div class="text-center">
      <div class="spinner-border text-info" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading training details...</p>
    </div>
  `;
  fetch(`/employee/destination-training/details/${trainingId}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      displayTrainingDetails(data.training);
    } else {
      showTrainingDetailsError(data.message || 'Failed to load training details');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showTrainingDetailsError('Network error occurred while loading training details');
  });
}

function displayTrainingDetails(training) {
  const content = `
    <div class="row g-4 mb-3 flex-wrap">
  <div class="col-12 col-md-6 d-flex">
    <div class="card h-100 w-100 p-4" style="min-width: 320px; min-height: 320px; font-size: 1.15rem;">
  <div class="card-header bg-primary text-white" style="font-size: 1.25rem; padding: 1rem 1.25rem;">
    <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Destination Information</h6>
  </div>
  <h5 class="text-primary mt-4" style="font-size: 2rem;">${training.destination_name}</h5>
  <p class="text-muted mb-4" style="font-size: 1.1rem;">${training.details || 'No description available'}</p>
  <div class="mb-3">
    <strong>Delivery Mode:</strong>
    <span class="badge bg-info ms-2">${training.delivery_mode || 'Not specified'}</span>
  </div>
  <div class="mb-3">
    <strong>Status:</strong>
    <span class="badge ${getStatusBadgeClass(training.status)} ms-2">${training.status}</span>
  </div>
  <div class="mb-3">
    <strong>Progress:</strong>
    <div class="progress mt-1" style="height: 12px;">
      <div class="progress-bar" role="progressbar" style="width: ${training.progress || 0}%"></div>
    </div>
    <small class="text-muted" style="font-size: 1rem;">${training.progress || 0}% complete</small>
  </div>
</div>
  </div>
  <div class="col-12 col-md-6 d-flex">
    <div class="card h-100 w-100 p-4" style="min-width: 320px; min-height: 320px; font-size: 1.15rem;">
  <div class="card-header bg-success text-white" style="font-size: 1.25rem; padding: 1rem 1.25rem;">
    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Training Schedule</h6>
  </div>
  <div class="mb-4 mt-4">
    <strong>Assigned Date:</strong>
    <p class="mb-1">${formatDate(training.created_at)}</p>
  </div>
  ${training.date_completed ? `
    <div class="mb-4">
      <strong>Completion Date:</strong>
      <p class="mb-1 text-success">${formatDate(training.date_completed)}</p>
    </div>
  ` : ''}
  ${training.expired_date ? `
    <div class="mb-4">
      <strong>Expiry Date:</strong>
      <p class="mb-1 text-warning">${formatDate(training.expired_date)}</p>
    </div>
  ` : ''}
  ${training.remarks ? `
    <div class="mb-4">
      <strong>Remarks:</strong>
      <p class="mb-1 text-muted">${training.remarks}</p>
    </div>
  ` : ''}
</div>
  </div>
</div>
    <div class="row mt-3">
      <div class="col-12">
        <div class="card mt-3 p-4" style="min-height: 200px; font-size: 1.15rem;">
  <div class="card-header bg-warning text-dark" style="font-size: 1.25rem; padding: 1rem 1.25rem;">
    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Training Objectives</h6>
  </div>
  <ul class="list-unstyled mb-0 mt-3">
    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Learn about destination-specific information and requirements</li>
    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Understand local customs, culture, and travel guidelines</li>
    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Master destination-specific booking and service procedures</li>
    <li class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Develop expertise in destination recommendations and advice</li>
  </ul>
</div>
      </div>
    </div>
  `;
  document.getElementById('trainingDetailsContent').innerHTML = content;
}

function showTrainingDetailsError(message) {
  document.getElementById('trainingDetailsContent').innerHTML = `
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-triangle me-2"></i>
      ${message}
    </div>
  `;
}

function getStatusBadgeClass(status) {
  switch(status?.toLowerCase()) {
    case 'completed': return 'bg-success';
    case 'in-progress': return 'bg-warning';
    case 'active': return 'bg-success';
    case 'declined': return 'bg-danger';
    case 'not-started': return 'bg-secondary';
    default: return 'bg-secondary';
  }
}

function formatDate(dateString) {
  if (!dateString) return 'Not specified';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}
// --- END Destination Knowledge Training JS ---

</script>
<script>
document.getElementById('editUpcomingModal')?.addEventListener('show.bs.modal', function (e) {
  const btn = e.relatedTarget;
  const id = btn.getAttribute('data-id');
  const form = document.getElementById('editUpcomingForm');
  form.action = "{{ url('employee/my_trainings') }}/" + id;

  form.querySelector('[name="training_title"]').value = btn.getAttribute('data-title');
  form.querySelector('[name="start_date"]').value = btn.getAttribute('data-start');
  form.querySelector('[name="status"]').value = btn.getAttribute('data-status');
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

  // AI Question Generation Function
  function generateQuestions(courseId, type) {
    const button = event.target;
    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    button.disabled = true;

    fetch(`/employee/exam/generate/${courseId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        type: type,
        count: type === 'exam' ? 10 : 5
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success message
        showToast('success', data.message);

        // Update button to show success
        button.innerHTML = '<i class="fas fa-check"></i> Generated!';
        button.classList.remove('btn-success', 'btn-warning');
        button.classList.add('btn-outline-success');

        // Reset button after 3 seconds
        setTimeout(() => {
          button.innerHTML = originalText;
          button.disabled = false;
          button.classList.remove('btn-outline-success');
          button.classList.add(type === 'exam' ? 'btn-success' : 'btn-warning');
        }, 3000);
      } else {
        showToast('error', data.message || 'Failed to generate questions');
        button.innerHTML = originalText;
        button.disabled = false;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('error', 'Network error occurred while generating questions');
      button.innerHTML = originalText;
      button.disabled = false;
    });
  }

  // Simplified Dynamic Reviewer function
  function openReviewer(trainingTitle, courseId) {
    console.log('Opening reviewer for:', trainingTitle, 'Course ID:', courseId);

    // Create reviewer modal
    const reviewerModal = document.createElement('div');
    reviewerModal.className = 'modal fade';
    reviewerModal.id = 'reviewerModal';
    reviewerModal.innerHTML = `
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-book-open me-2"></i>Training Reviewer: ${trainingTitle}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="reviewerContent">
            <div class="text-center">
              <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading training materials...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" id="markReviewedBtn" onclick="markAsReviewed('${courseId}', '${trainingTitle}')">
              <i class="fas fa-check me-1"></i>Mark as Reviewed
            </button>
          </div>
        </div>
      </div>
    `;

    document.body.appendChild(reviewerModal);
    const modal = new bootstrap.Modal(reviewerModal);
    modal.show();

    // Load content immediately after modal is shown
    setTimeout(() => {
      loadReviewerContent(courseId, trainingTitle);
    }, 500);

    // Remove modal from DOM when hidden
    reviewerModal.addEventListener('hidden.bs.modal', () => {
      reviewerModal.remove();
    });
  }

  // Separate function to load reviewer content
  function loadReviewerContent(courseId, trainingTitle) {
    const contentDiv = document.getElementById('reviewerContent');
    if (!contentDiv) return;

    console.log('Loading content for course ID:', courseId);

    // Try to fetch dynamic content
    fetch(`/employee/training/reviewer/${courseId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => {
      console.log('API Response Status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('API Response Data:', data);
      console.log('Debug Info:', data.debug_info);

      if (data.success) {
        if (data.review_questions && data.review_questions.length > 0) {
          console.log('Found', data.review_questions.length, 'questions to display');
          displayActualQuestions(data.review_questions, data.exam_count);
        } else {
          console.log('No exam questions found in response, attempting to generate...');
          // Try to generate questions if none exist
          generateQuestionsForReviewer(courseId, trainingTitle);
        }
      } else {
        console.error('API Error:', data.error);
        showDefaultReviewerContent(trainingTitle);
      }
    })
    .catch(error => {
      console.error('API Error:', error);
      showDefaultReviewerContent(trainingTitle);
    });
  }

  // Generate questions for reviewer if none exist
  function generateQuestionsForReviewer(courseId, trainingTitle) {
    const contentDiv = document.getElementById('reviewerContent');
    if (!contentDiv) return;

    contentDiv.innerHTML = `
      <div class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Generating questions...</span>
        </div>
        <p class="mt-2">Generating exam questions for ${trainingTitle}...</p>
      </div>`;

    // Generate questions via API
    fetch(`/employee/exam/generate/${courseId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        type: 'exam',
        count: 10
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        console.log('Questions generated successfully, reloading reviewer content...');
        // Reload reviewer content after generation
        setTimeout(() => {
          loadReviewerContent(courseId, trainingTitle);
        }, 1000);
      } else {
        console.error('Failed to generate questions:', data.message);
        showDefaultReviewerContent(trainingTitle);
      }
    })
    .catch(error => {
      console.error('Error generating questions:', error);
      showDefaultReviewerContent(trainingTitle);
    });
  }

  // Show default content when API fails
  function showDefaultReviewerContent(trainingTitle) {
    const contentDiv = document.getElementById('reviewerContent');
    if (!contentDiv) return;

    contentDiv.innerHTML = getEnhancedDefaultContent(trainingTitle);
  }

  // Enhanced default content based on training title
  function getEnhancedDefaultContent(trainingTitle) {
    const title = trainingTitle.toUpperCase();
    let specificContent = '';

    // Generate content based on training type
    if (title.includes('CUSTOMER') || title.includes('SERVICE')) {
      specificContent = `
        <div class="alert alert-primary">
          <h6><i class="fas fa-users me-2"></i>Customer Service Excellence</h6>
          <p>Focus on building strong customer relationships and delivering exceptional service experiences.</p>
        </div>`;
    } else if (title.includes('LEADERSHIP') || title.includes('MANAGEMENT')) {
      specificContent = `
        <div class="alert alert-success">
          <h6><i class="fas fa-crown me-2"></i>Leadership Development</h6>
          <p>Develop essential leadership skills and learn effective team management strategies.</p>
        </div>`;
    } else if (title.includes('COMMUNICATION')) {
      specificContent = `
        <div class="alert alert-info">
          <h6><i class="fas fa-comments me-2"></i>Communication Skills</h6>
          <p>Master effective communication techniques for professional and personal success.</p>
        </div>`;
    } else {
      specificContent = `
        <div class="alert alert-warning">
          <h6><i class="fas fa-graduation-cap me-2"></i>Professional Training</h6>
          <p>Comprehensive training program designed to enhance your professional skills and knowledge.</p>
        </div>`;
    }

    return `
      ${specificContent}
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-success"><i class="fas fa-lightbulb me-2"></i>Key Learning Points</h6>
          <ul class="list-unstyled">
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Core concepts and principles</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Best practices and methodologies</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Practical applications</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Industry standards</li>
          </ul>
        </div>
        <div class="col-md-6">
          <h6 class="text-info"><i class="fas fa-clipboard-list me-2"></i>Study Materials</h6>
          <div class="list-group">
            <div class="list-group-item">
              <i class="fas fa-file-pdf text-danger me-2"></i>
            </div>
            <div class="list-group-item">
              <i class="fas fa-video text-primary me-2"></i>
            </div>
            <div class="list-group-item">
              <i class="fas fa-question-circle text-warning me-2"></i>
            </div>
          </div>
        </div>
      </div>
      <hr>
      <div class="alert alert-info">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Study Tips</h6>
        <ul class="mb-0">
          <li>Review all materials before taking the exam</li>
          <li>Practice with sample questions</li>
          <li>Focus on understanding concepts rather than memorization</li>
          <li>Take notes on key points for future reference</li>
        </ul>
      </div>
    `;
  }

  // Get default content to show immediately
  function getDefaultContent() {
    return getEnhancedDefaultContent('General Training');
  }

  // Display actual exam and quiz questions
  function displayActualQuestions(reviewQuestions, examCount, quizCount) {
    const contentDiv = document.getElementById('reviewerContent');
    if (!contentDiv) return;

    let content = `
      <div class="alert alert-success">
        <h6><i class="fas fa-graduation-cap me-2"></i>Exam & Quiz Review</h6>
        <p>Review the actual questions you'll encounter in your exam and quiz. Study these carefully!</p>
        <div class="row">
          <div class="col-md-6">
            <span class="badge bg-primary me-2">${examCount} Exam Questions</span>
          </div>
          <div class="col-md-6">
                      </div>
        </div>
      </div>`;

    // Group questions by type
    const examQuestions = reviewQuestions.filter(q => q.type === 'exam');
    // Quiz questions removed

    // Display Exam Questions
    if (examQuestions.length > 0) {
      content += `
        <div class="mt-4">
          <h6 class="text-primary"><i class="fas fa-clipboard-check me-2"></i>Exam Questions (${examQuestions.length})</h6>
          <div class="accordion" id="examQuestionsAccordion">`;

      examQuestions.forEach((question, index) => {
        const options = question.options || {};
        const optionKeys = Object.keys(options);

        content += `
          <div class="accordion-item">
            <h2 class="accordion-header" id="examHeading${index}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#examCollapse${index}">
                <i class="fas fa-question-circle text-primary me-2"></i>Exam Question ${index + 1}
              </button>
            </h2>
            <div id="examCollapse${index}" class="accordion-collapse collapse" data-bs-parent="#examQuestionsAccordion">
                                                                    <h6 class="card-title">Question:</h6>
                    <p class="card-text">${question.question}</p>

                    <h6 class="mt-3">Options:</h6>
                    <div class="list-group list-group-flush">`;

        optionKeys.forEach(key => {
          const isCorrect = options[key] === question.correct_answer;
          content += `
            <div class="list-group-item ${isCorrect ? 'list-group-item-success' : ''}">
              <strong>${key.toUpperCase()}.</strong> ${options[key]}
              ${isCorrect ? '<i class="fas fa-check-circle text-success float-end"></i>' : ''}
            </div>`;
        });

        content += `
                    </div>

                    <div class="mt-3">
                      <h6>Correct Answer:</h6>
                      <span class="badge bg-success">${question.correct_answer}</span>
                    </div>

                    ${question.explanation ? `
                    <div class="mt-3">
                      <h6>Explanation:</h6>
                      <p class="text-muted">${question.explanation}</p>
                    </div>` : ''}
                  </div>
                </div>
              </div>
            </div>
          </div>`;
      });

      content += `</div></div>`;
    }

    // Display Quiz Questions section removed

    // Study Tips
    content += `
      <div class="mt-4 alert alert-info">
        <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Study Tips</h6>
        <ul class="mb-0">
          <li>Review each question and understand why the correct answer is right</li>
          <li>Pay attention to the explanations provided</li>
          <li>Practice identifying key concepts in each question</li>
          <li>Take notes on areas where you need more study</li>
        </ul>
      </div>`;

    contentDiv.innerHTML = content;
  }

  // Mark as reviewed function
  function markAsReviewed(courseId, trainingTitle) {
    const button = document.getElementById('markReviewedBtn');
    const originalText = button.innerHTML;

    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Marking...';
    button.disabled = true;

    fetch('/employee/training/mark-reviewed', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        course_id: courseId,
        training_title: trainingTitle
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('success', data.message);
        button.innerHTML = '<i class="fas fa-check me-1"></i>Reviewed!';
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-success');
      } else {
        showToast('error', data.error || 'Failed to mark as reviewed');
        button.innerHTML = originalText;
        button.disabled = false;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('error', 'Network error occurred');
      button.innerHTML = originalText;
      button.disabled = false;
    });
  }

  // Toast notification function
  function showToast(type, message) {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
      toastContainer.style.zIndex = '9999';
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;

    toastContainer.appendChild(toast);

    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();

    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
      toast.remove();
    });
  }


  // Handle competency training response (accept/decline)
  function respondToCompetencyTraining(upcomingId, action, button) {
    // Show loading state
    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    // Make API call
    fetch('/employee/competency-training/respond', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        upcoming_id: upcomingId,
        action: action
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('success', data.message || `Training ${action}ed successfully`);
        
        // Update the row to show new status
        const row = button.closest('tr');
        const statusCell = row.querySelector('td:nth-child(5) .badge');
        const actionsCell = row.querySelector('td:nth-child(8)');
        
        if (action === 'accept') {
          statusCell.className = 'badge bg-success text-white';
          statusCell.textContent = 'Accepted';
          actionsCell.innerHTML = '<span class="badge bg-success">Accepted</span>';
        } else {
          statusCell.className = 'badge bg-danger text-white';
          statusCell.textContent = 'Declined';
          actionsCell.innerHTML = '<span class="badge bg-danger">Declined</span>';
        }
      } else {
        // Reset button state on error
        button.disabled = false;
        button.innerHTML = originalHtml;
        showToast('error', data.message || `Failed to ${action} training`);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      // Reset button state on error
      button.disabled = false;
      button.innerHTML = originalHtml;
      showToast('error', `Network error occurred while ${action}ing training`);
    });
  }

  // View Training Details Function
  function viewTrainingDetails(trainingId) {
    const detailsModal = new bootstrap.Modal(document.getElementById('trainingDetailsModal'));
    detailsModal.show();

    // Reset modal content to loading state
    document.getElementById('trainingDetailsContent').innerHTML = `
      <div class="text-center">
        <div class="spinner-border text-info" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading training details...</p>
      </div>
    `;

    // Fetch training details
    fetch(`/employee/destination-training/details/${trainingId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        displayTrainingDetails(data.training);
      } else {
        showTrainingDetailsError(data.message || 'Failed to load training details');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showTrainingDetailsError('Network error occurred while loading training details');
    });
  }

  function displayTrainingDetails(training) {
    const content = `
      <div class="row">
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Destination Information</h6>
            </div>
                          <h5 class="text-primary">${training.destination_name}</h5>
              <p class="text-muted mb-3">${training.details || 'No description available'}</p>

              <div class="mb-2">
                <strong>Delivery Mode:</strong>
                <span class="badge bg-info ms-2">${training.delivery_mode || 'Not specified'}</span>
              </div>

              <div class="mb-2">
                <strong>Status:</strong>
                <span class="badge ${getStatusBadgeClass(training.status)} ms-2">${training.status}</span>
              </div>

              <div class="mb-2">
                <strong>Progress:</strong>
                <div class="progress mt-1" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: ${training.progress || 0}%"></div>
                </div>
                <small class="text-muted">${training.progress || 0}% complete</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-success text-white">
              <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Training Schedule</h6>
            </div>
                          <div class="mb-3">
                <strong>Assigned Date:</strong>
                <p class="mb-1">${formatDate(training.created_at)}</p>
              </div>

              ${training.date_completed ? `
                <div class="mb-3">
                  <strong>Completion Date:</strong>
                  <p class="mb-1 text-success">${formatDate(training.date_completed)}</p>
                </div>
              ` : ''}

              ${training.expired_date ? `
                <div class="mb-3">
                  <strong>Expiry Date:</strong>
                  <p class="mb-1 text-warning">${formatDate(training.expired_date)}</p>
                </div>
              ` : ''}

              ${training.remarks ? `
                <div class="mb-3">
                  <strong>Remarks:</strong>
                  <p class="mb-1 text-muted">${training.remarks}</p>
                </div>
              ` : ''}
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
                      <div class="card-header bg-warning text-dark">
              <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Training Objectives</h6>
            </div>
                          <ul class="list-unstyled mb-0">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Learn about destination-specific information and requirements</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Understand local customs, culture, and travel guidelines</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Master destination-specific booking and service procedures</li>
                <li class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Develop expertise in destination recommendations and advice</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    `;

    document.getElementById('trainingDetailsContent').innerHTML = content;
  }

  function showTrainingDetailsError(message) {
    document.getElementById('trainingDetailsContent').innerHTML = `
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        ${message}
      </div>
    `;
  }

  function getStatusBadgeClass(status) {
    switch(status?.toLowerCase()) {
      case 'completed': return 'bg-success';
      case 'in-progress': return 'bg-warning';
      case 'active': return 'bg-success';
      case 'declined': return 'bg-danger';
      case 'not-started': return 'bg-secondary';
      default: return 'bg-secondary';
    }
  }

  function formatDate(dateString) {
    if (!dateString) return 'Not specified';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  // Handle training details modal
  document.addEventListener('DOMContentLoaded', function() {
    const trainingDetailsModal = document.getElementById('trainingDetailsModal');
    
    trainingDetailsModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      
      // Get data from button attributes
      const trainingTitle = button.getAttribute('data-training-title') || '-';
      const startDate = button.getAttribute('data-start-date') || '-';
      const endDate = button.getAttribute('data-end-date') || '-';
      const status = button.getAttribute('data-status') || '-';
      const source = button.getAttribute('data-source') || '-';
      const assignedBy = button.getAttribute('data-assigned-by') || '-';
      const assignedDate = button.getAttribute('data-assigned-date') || '-';
      
      // Populate modal fields
      document.getElementById('modalTrainingTitle').textContent = trainingTitle;
      document.getElementById('modalStatus').innerHTML = `<span class="badge ${getStatusBadgeClass(status)}">${status}</span>`;
      document.getElementById('modalSource').innerHTML = getSourceBadge(source);
      document.getElementById('modalStartDate').textContent = formatDate(startDate);
      document.getElementById('modalEndDate').textContent = formatDate(endDate);
      document.getElementById('modalAssignedBy').textContent = assignedBy;
      document.getElementById('modalAssignedDate').textContent = formatDate(assignedDate);
    });
  });

  function getSourceBadge(source) {
    switch(source) {
      case 'competency_gap':
        return '<span class="badge bg-warning text-dark"><i class="fas fa-chart-line me-1"></i>Competency Gap</span>';
      case 'destination_assigned':
        return '<span class="badge bg-info"><i class="fas fa-map-marker-alt me-1"></i>Destination Assigned</span>';
      case 'admin_assigned':
        return '<span class="badge bg-success"><i class="fas fa-user-shield me-1"></i>Admin Assigned</span>';
      case 'manual':
        return '<span class="badge bg-secondary"><i class="fas fa-hand-paper me-1"></i>Manual</span>';
      default:
        return '<span class="badge bg-secondary">Unknown</span>';
    }
  }

  function formatDate(dateString) {
    if (!dateString || dateString === '-') return 'Not specified';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }
</script>
