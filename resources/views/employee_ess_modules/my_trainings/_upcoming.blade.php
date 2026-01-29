{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
              ->filter(function($item) {
                $upcomingId = is_array($item) ? ($item['upcoming_id'] ?? '') : ($item->upcoming_id ?? '');
                return !str_starts_with((string)$upcomingId, 'TR');
              })
              ->groupBy(function($item) {
                $source = is_array($item) ? ($item['source'] ?? null) : ($item->source ?? null);
                $employeeId = is_array($item) ? ($item['employee_id'] ?? null) : ($item->employee_id ?? null);
                $trainingTitle = is_array($item) ? ($item['training_title'] ?? '') : ($item->training_title ?? '');
                return $trainingTitle . '_' . $employeeId;
              })
              ->map(function($group) {
                return $group->first();
              })
              ->values();

            // Check if there are any admin-assigned or competency gap assigned courses
            $hasAdminAssigned = $uniqueUpcoming->contains(function($item) {
              $source = is_array($item) ? ($item['source'] ?? null) : ($item->source ?? null);
              return $source === 'admin_assigned' || $source === 'competency_gap' || $source === 'competency_assigned';
            });
            
            $sequentialId = 1; // Start sequential numbering from 1
          @endphp
          @forelse($uniqueUpcoming as $u)
            @php
              $source = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);
            @endphp
            <tr>
              <td>
                {{ $sequentialId++ }}
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
                  $finalExpiredDate = null; // Initialize as null to force competency gap lookup
                  $sourceValue = is_array($u) ? ($u['source'] ?? null) : ($u->source ?? null);

                  // For competency gap trainings, always get expiration date from competency gap table first
                  if ($sourceValue === 'competency_assigned' || $sourceValue === 'competency_gap' || $sourceValue === 'admin_assigned') {
                    $employeeId = is_array($u) ? ($u['employee_id'] ?? null) : ($u->employee_id ?? null);
                    $trainingTitle = is_array($u) ? ($u['training_title'] ?? '') : ($u->training_title ?? '');

                    if ($employeeId && $trainingTitle) {
                      // Enhanced matching logic for competency gaps
                      // Normalize employee ID to handle potential zero-padding mismatches
                      $rawEmployeeId = $employeeId;
                      $unpaddedId = ltrim($rawEmployeeId, '0');
                      
                      $competencyGaps = \App\Models\CompetencyGap::with('competency')
                        ->where(function($q) use ($rawEmployeeId, $unpaddedId) {
                            $q->where('employee_id', $rawEmployeeId)
                              ->orWhere('employee_id', $unpaddedId)
                              ->orWhere('employee_id', str_pad($unpaddedId, 3, '0', STR_PAD_LEFT))
                              ->orWhere('employee_id', str_pad($unpaddedId, 4, '0', STR_PAD_LEFT))
                              ->orWhere('employee_id', str_pad($unpaddedId, 5, '0', STR_PAD_LEFT))
                              ->orWhere('employee_id', str_pad($unpaddedId, 6, '0', STR_PAD_LEFT));
                        })
                        ->get();

                      $matchedGap = null;

                      foreach ($competencyGaps as $gap) {
                        if ($gap->competency) {
                          $competencyName = $gap->competency->competency_name;

                          // Try multiple matching strategies
                          // Normalize strings: lowercase, remove extra spaces, handle ampersands
                          $cleanTrainingTitle = strtolower(trim(str_replace([' Training', ' Course', ' Program', ' Skills'], '', $trainingTitle)));
                          $cleanTrainingTitle = str_replace('&', 'and', $cleanTrainingTitle);
                          $cleanTrainingTitle = preg_replace('/[^a-z0-9\s]/', '', $cleanTrainingTitle); // Remove special chars
                          $cleanTrainingTitle = preg_replace('/\s+/', ' ', $cleanTrainingTitle);
                          
                          $cleanCompetencyName = strtolower(trim($competencyName));
                          $cleanCompetencyName = str_replace('&', 'and', $cleanCompetencyName);
                          $cleanCompetencyName = preg_replace('/[^a-z0-9\s]/', '', $cleanCompetencyName); // Remove special chars
                          $cleanCompetencyName = preg_replace('/\s+/', ' ', $cleanCompetencyName);

                          // Strategy 1: Exact match after cleaning
                          if ($cleanTrainingTitle === $cleanCompetencyName) {
                            $matchedGap = $gap;
                            break;
                          }

                          // Strategy 2: Check if competency name is contained in training title
                          if (str_contains($cleanTrainingTitle, $cleanCompetencyName)) {
                            $matchedGap = $gap;
                            break;
                          }

                          // Strategy 3: Check if training title is contained in competency name
                          if (str_contains($cleanCompetencyName, $cleanTrainingTitle)) {
                            $matchedGap = $gap;
                            break;
                          }

                          // Strategy 4: Word-by-word matching (at least 50% match)
                          $trainingWords = explode(' ', $cleanTrainingTitle);
                          $competencyWords = explode(' ', $cleanCompetencyName);
                          $matchCount = 0;

                          foreach ($trainingWords as $word) {
                            if (strlen($word) > 2) { // Skip short words
                              foreach ($competencyWords as $compWord) {
                                if (str_contains($compWord, $word) || str_contains($word, $compWord)) {
                                  $matchCount++;
                                  break;
                                }
                              }
                            }
                          }

                          if ($matchCount > 0 && ($matchCount / max(count($trainingWords), count($competencyWords))) >= 0.5) {
                            $matchedGap = $gap;
                            break;
                          }
                        }
                      }

                      if ($matchedGap && $matchedGap->expired_date) {
                        $finalExpiredDate = $matchedGap->expired_date;
                        // Debug: Log the matched gap details
                        \Log::info("Matched Gap Found", [
                          'employee_id' => $employeeId,
                          'training_title' => $trainingTitle,
                          'competency_name' => $matchedGap->competency->competency_name ?? 'N/A',
                          'expired_date' => $matchedGap->expired_date,
                          'gap_id' => $matchedGap->id
                        ]);
                      } else {
                        // Debug: Log when no match is found
                        \Log::info("No Gap Match Found", [
                          'employee_id' => $employeeId,
                          'training_title' => $trainingTitle,
                          'available_gaps' => $competencyGaps->map(function($gap) {
                            return [
                              'id' => $gap->id,
                              'competency_name' => $gap->competency->competency_name ?? 'N/A',
                              'expired_date' => $gap->expired_date,
                              'assigned_to_training' => $gap->assigned_to_training
                            ];
                          })->toArray()
                        ]);
                      }
                    }
                  }

                  // If no competency gap date found, use the original expired_date from the record
                  if (!$finalExpiredDate) {
                    $finalExpiredDate = is_array($u) ? ($u['expired_date'] ?? null) : ($u->expired_date ?? null);
                  }
                @endphp

                @if($finalExpiredDate && !empty(trim($finalExpiredDate)) && trim($finalExpiredDate) !== '0000-00-00 00:00:00' && trim($finalExpiredDate) !== '0000-00-00')
                  @php
                    try {
                      $expiredDateRaw = trim($finalExpiredDate);
                      $expiredDateObj = \Carbon\Carbon::parse($expiredDateRaw)->startOfDay();
                      $now = \Carbon\Carbon::now()->startOfDay();
                      $dateFormatted = $expiredDateObj->format('M d, Y');
                      $daysLeft = $now->diffInDays($expiredDateObj, false);
                      $isExpired = $now->gt($expiredDateObj);
                      $showExpiredDate = true;
                    } catch (Exception $e) {
                      $showExpiredDate = false;
                    }
                  @endphp
                  @if($showExpiredDate)
                    <div class="d-flex flex-column align-items-start">
                      <div class="fw-semibold">{{ $dateFormatted }}</div>
                      <div class="mt-1">
                        @if(!$isExpired)
                          <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="fas fa-clock me-1"></i>{{ floor(abs($daysLeft)) }} day{{ floor(abs($daysLeft)) == 1 ? '' : 's' }} left
                          </span>
                        @else
                          <span class="badge bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>Expired {{ floor(abs($daysLeft)) }} day{{ floor(abs($daysLeft)) == 1 ? '' : 's' }} ago
                          </span>
                        @endif
                      </div>
                    </div>
                  @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                      <i class="fas fa-calendar-times me-1"></i>Invalid Date
                    </span>
                  @endif
                @else
                  <span class="badge bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-calendar-times me-1"></i>Not Set
                  </span>
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

                  // Additional progress checks from other sources
                  if ($currentProgress == 0 && $employeeIdForProgress) {
                    // Check training dashboard for progress
                    $dashboardRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeIdForProgress)
                      ->where('course_id', $courseIdForProgress)
                      ->first();
                    if ($dashboardRecord && $dashboardRecord->progress > 0) {
                      $currentProgress = $dashboardRecord->progress;
                    }

                    // Check competency profile for progress if training title matches
                    $trainingTitleForProgress = is_array($u) ? ($u['training_title'] ?? '') : ($u->training_title ?? '');
                    if ($currentProgress == 0 && $trainingTitleForProgress) {
                      $competencyProfile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeIdForProgress)
                        ->whereHas('competency', function($query) use ($trainingTitleForProgress) {
                          $query->where('competency_name', 'LIKE', '%' . $trainingTitleForProgress . '%')
                                ->orWhere('competency_name', 'LIKE', '%' . str_replace(' Training', '', $trainingTitleForProgress) . '%');
                        })
                        ->first();
                      if ($competencyProfile && $competencyProfile->proficiency_level > 0) {
                        $currentProgress = ($competencyProfile->proficiency_level / 5) * 100; // Convert 1-5 scale to percentage
                      }
                    }
                  }



                  // Check if expired using the calculated expired date from above
                  $isTrainingExpired = false;
                  if ($finalExpiredDate && !empty(trim($finalExpiredDate)) && trim($finalExpiredDate) !== '0000-00-00 00:00:00' && trim($finalExpiredDate) !== '0000-00-00') {
                    try {
                        $expiredDateObj = \Carbon\Carbon::parse(trim($finalExpiredDate))->startOfDay();
                        $now = \Carbon\Carbon::now()->startOfDay();
                        $isTrainingExpired = $now->gt($expiredDateObj);
                    } catch (Exception $e) {
                        $isTrainingExpired = false;
                    }
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
                  $assignedByName = is_array($u) ? ($u['assigned_by_name'] ?? null) : ($u->assigned_by_name ?? null);
                  $assignedDate = is_array($u) ? ($u['assigned_date'] ?? null) : ($u->assigned_date ?? null);
                  
                  if (empty($assignedByName)) {
                    $assignedById = is_array($u) ? ($u['assigned_by'] ?? null) : ($u->assigned_by ?? null);
                    if ($assignedById && is_numeric($assignedById)) {
                      $adminUser = \App\Models\User::find($assignedById);
                      if ($adminUser && !empty($adminUser->name)) {
                        $assignedByName = $adminUser->name;
                      }
                    }
                  }
                @endphp
                @if(!empty($assignedByName))
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

                  // Use assigned_by_name first, fallback to assigned_by for display
                  $assignedByCheck = is_array($u) ? ($u['assigned_by_name'] ?? $u['assigned_by'] ?? null) : ($u->assigned_by_name ?? $u->assigned_by ?? null);

                  $assignedDateCheck = is_array($u) ? ($u['assigned_date'] ?? null) : ($u->assigned_date ?? null);
                  $courseIdCheck = is_array($u) ? ($u['course_id'] ?? null) : ($u->course_id ?? null);
                  $destinationTrainingId = is_array($u) ? ($u['destination_training_id'] ?? null) : ($u->destination_training_id ?? null);
                  $needsResponse = is_array($u) ? ($u['needs_response'] ?? false) : ($u->needs_response ?? false);
                @endphp

                @if($sourceCheck === 'destination_assigned' && $needsResponse)
                  {{-- Show Accept/Decline buttons for destination training that needs response --}}
                  <div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-success btn-sm"
                            onclick="acceptDestinationTrainingWithConfirmation('{{ $destinationTrainingId }}', '{{ $trainingTitleCheck }}', this)"
                            title="Accept this destination training">
                      <i class="fas fa-check me-1"></i>Accept
                    </button>
                    <button class="btn btn-danger btn-sm"
                            onclick="declineDestinationTrainingWithConfirmation('{{ $destinationTrainingId }}', '{{ $trainingTitleCheck }}', this)"
                            title="Decline this destination training">
                      <i class="fas fa-times me-1"></i>Decline
                    </button>
                  </div>
                @else
                  {{-- Show View Details button for other trainings --}}
                  <div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-info btn-sm"
                            onclick="viewTrainingDetailsWithSweetAlert('{{ $upcomingIdCheck }}', '{{ $trainingTitleCheck }}', '{{ $startDateCheck }}', '{{ $endDateCheck }}', '{{ $statusCheck }}', '{{ $sourceCheck }}', '{{ $assignedByCheck }}', '{{ $assignedDateCheck }}', {{ $currentProgress }})"
                            title="View Training Details (Progress: {{ $currentProgress }}%)">
                      <i class="fas fa-eye me-1"></i>View Details ({{ $currentProgress }}%)
                    </button>
                    @if($sourceCheck === 'competency_gap' || $sourceCheck === 'competency_assigned')
                      <button class="btn btn-warning btn-sm"
                              onclick="requestTrainingExtensionWithConfirmation('{{ $upcomingIdCheck }}', '{{ $trainingTitleCheck }}')"
                              title="Request Extension">
                        <i class="fas fa-clock me-1"></i>Extend
                      </button>
                    @endif
                  </div>
                @endif
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
      <div class="modal-body" id="trainingDetailsContent">
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading training details...</p>
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
// --- Enhanced SweetAlert Destination Knowledge Training JS ---
let currentTrainingId = null;

// Accept Destination Training with SweetAlert Confirmation
function acceptDestinationTrainingWithConfirmation(trainingId, trainingTitle, btn) {
  Swal.fire({
    title: 'Accept Training Assignment?',
    html: `
      <div class="text-start">
        <p><strong>Training:</strong> ${trainingTitle}</p>
        <p class="text-muted">Are you sure you want to accept this destination training assignment?</p>
        <div class="alert alert-info mt-3">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Note:</strong> Once accepted, this training will be added to your active training list.
        </div>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-check me-1"></i>Yes, Accept Training',
    cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
    reverseButtons: true,
    customClass: {
      popup: 'swal2-popup-large',
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-secondary'
    },
    buttonsStyling: false
  }).then((result) => {
    if (result.isConfirmed) {
      processDestinationTrainingResponse(trainingId, 'accept', null, btn, trainingTitle);
    }
  });
}

// Decline Destination Training with SweetAlert Confirmation and Reason
function declineDestinationTrainingWithConfirmation(trainingId, trainingTitle, btn) {
  Swal.fire({
    title: 'Decline Training Assignment',
    html: `
      <div class="text-start">
        <p><strong>Training:</strong> ${trainingTitle}</p>
        <p class="text-muted mb-3">Please provide a reason for declining this training assignment:</p>
        <textarea id="declineReason" class="form-control" rows="3" placeholder="Enter your reason for declining (optional)..."></textarea>
        <div class="alert alert-warning mt-3">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>Warning:</strong> This action cannot be undone. The training will be removed from your assignments.
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-times me-1"></i>Yes, Decline Training',
    cancelButtonText: '<i class="fas fa-arrow-left me-1"></i>Go Back',
    reverseButtons: true,
    customClass: {
      popup: 'swal2-popup-large',
      confirmButton: 'btn btn-danger',
      cancelButton: 'btn btn-secondary'
    },
    buttonsStyling: false,
    preConfirm: () => {
      const reason = document.getElementById('declineReason').value;
      return reason;
    }
  }).then((result) => {
    if (result.isConfirmed) {
      processDestinationTrainingResponse(trainingId, 'decline', result.value, btn, trainingTitle);
    }
  });
}

document.getElementById('confirmDeclineBtn')?.addEventListener('click', function() {
  const reason = document.getElementById('declineReason').value;
  processDestinationTrainingResponse(currentTrainingId, 'decline', reason);
  const declineModal = bootstrap.Modal.getInstance(document.getElementById('declineReasonModal'));
  declineModal.hide();
  document.getElementById('declineReason').value = '';
});

// Enhanced process function with SweetAlert notifications
function processDestinationTrainingResponse(trainingId, action, reason, btn, trainingTitle) {
  const endpoint = action === 'accept' ? '/employee/destination-training/accept' : '/employee/destination-training/decline';

  // Show loading SweetAlert
  Swal.fire({
    title: `${action === 'accept' ? 'Accepting' : 'Declining'} Training...`,
    html: `
      <div class="text-center">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p>Processing your ${action === 'accept' ? 'acceptance' : 'decline'} of <strong>${trainingTitle}</strong></p>
      </div>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    customClass: {
      popup: 'swal2-popup-large'
    }
  });

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
      if (action === 'accept' && data.auto_completed) {
        // Show success with auto-completion message
        Swal.fire({
          title: 'Training Accepted & Completed!',
          html: `
            <div class="text-center">
              <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
              <p class="mt-3">${data.message}</p>
              <div class="alert alert-success mt-3">
                <i class="fas fa-trophy me-2"></i>
                This training has been automatically completed and moved to your completed trainings.
              </div>
            </div>
          `,
          icon: 'success',
          confirmButtonText: '<i class="fas fa-refresh me-1"></i>Refresh Page',
          confirmButtonColor: '#28a745',
          customClass: {
            confirmButton: 'btn btn-success'
          },
          buttonsStyling: false
        }).then(() => {
          window.location.reload();
        });
      } else if (action === 'accept') {
        // Show success for accepted training
        Swal.fire({
          title: 'Training Accepted!',
          html: `
            <div class="text-center">
              <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
              <p class="mt-3">${data.message}</p>
              <div class="alert alert-info mt-3">
                <i class="fas fa-play-circle me-2"></i>
                You can now start this training from your active trainings list.
              </div>
            </div>
          `,
          icon: 'success',
          confirmButtonText: '<i class="fas fa-check me-1"></i>Got it!',
          confirmButtonColor: '#28a745',
          customClass: {
            confirmButton: 'btn btn-success'
          },
          buttonsStyling: false
        });

        // Update the UI
        const row = document.querySelector(`[onclick*="${trainingId}"]`).closest('tr');
        const statusCell = row.querySelector('td:nth-child(5)');
        const actionCell = row.querySelector('td:nth-child(8)');
        statusCell.innerHTML = '<span class="badge bg-primary text-white">In Progress</span>';
        actionCell.innerHTML = '<span class="badge bg-primary">In Progress</span>';
      } else {
        // Show success for declined training
        Swal.fire({
          title: 'Training Declined',
          html: `
            <div class="text-center">
              <i class="fas fa-times-circle text-warning" style="font-size: 3rem;"></i>
              <p class="mt-3">${data.message}</p>
              <div class="alert alert-warning mt-3">
                <i class="fas fa-info-circle me-2"></i>
                This training has been removed from your upcoming assignments.
              </div>
            </div>
          `,
          icon: 'info',
          confirmButtonText: '<i class="fas fa-check me-1"></i>Understood',
          confirmButtonColor: '#ffc107',
          customClass: {
            confirmButton: 'btn btn-warning'
          },
          buttonsStyling: false
        });

        // Update the UI
        const row = document.querySelector(`[onclick*="${trainingId}"]`).closest('tr');
        row.style.opacity = '0.5';
        const statusCell = row.querySelector('td:nth-child(5)');
        const actionCell = row.querySelector('td:nth-child(8)');
        statusCell.innerHTML = '<span class="badge bg-secondary">Declined</span>';
        actionCell.innerHTML = '<span class="badge bg-secondary">Declined</span>';

        setTimeout(() => {
          row.remove();
        }, 2000);
      }
    } else {
      // Show error message
      Swal.fire({
        title: 'Action Failed',
        html: `
          <div class="text-center">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <p class="mt-3">${data.message || `Failed to ${action} training`}</p>
            <div class="alert alert-danger mt-3">
              <i class="fas fa-bug me-2"></i>
              Please try again or contact support if the problem persists.
            </div>
          </div>
        `,
        icon: 'error',
        confirmButtonText: '<i class="fas fa-redo me-1"></i>Try Again',
        confirmButtonColor: '#dc3545',
        customClass: {
          confirmButton: 'btn btn-danger'
        },
        buttonsStyling: false
      });

      // Reset buttons
      buttons.forEach(btn => {
        btn.disabled = false;
        if (action === 'accept') {
          btn.innerHTML = '<i class="fas fa-check me-1"></i>Accept';
        } else {
          btn.innerHTML = '<i class="fas fa-times me-1"></i>Decline';
        }
      });
    }
  })
  .catch(error => {
    console.error('Error:', error);

    // Show network error
    Swal.fire({
      title: 'Network Error',
      html: `
        <div class="text-center">
          <i class="fas fa-wifi text-danger" style="font-size: 3rem;"></i>
          <p class="mt-3">A network error occurred while ${action}ing the training.</p>
          <div class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Please check your internet connection and try again.
          </div>
        </div>
      `,
      icon: 'error',
      confirmButtonText: '<i class="fas fa-redo me-1"></i>Retry',
      confirmButtonColor: '#dc3545',
      customClass: {
        confirmButton: 'btn btn-danger'
      },
      buttonsStyling: false
    });

    // Reset buttons
    buttons.forEach(btn => {
      btn.disabled = false;
      if (action === 'accept') {
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Accept';
      } else {
        btn.innerHTML = '<i class="fas fa-times me-1"></i>Decline';
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
// Enhanced View Training Details with SweetAlert - Fixed Progress Tracking
function viewTrainingDetailsWithSweetAlert(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, progress) {
  try {
    console.log('=== VIEW TRAINING DETAILS DEBUG ===');
    console.log('Function called with parameters:', {
      trainingId: trainingId,
      trainingTitle: trainingTitle,
      startDate: startDate,
      endDate: endDate,
      status: status,
      source: source,
      assignedBy: assignedBy,
      assignedDate: assignedDate,
      progress: progress,
      progressType: typeof progress
    });

    // Convert progress to number if it's a string
    let numericProgress = parseInt(progress) || 0;
    console.log('Converted progress to numeric:', numericProgress);

    // Get actual progress from the page data
    let actualProgress = getActualTrainingProgress(trainingId, trainingTitle, numericProgress, source);
    let progressSource = actualProgress.source;

    console.log('Final progress data:', actualProgress);
    console.log('=== END DEBUG ===');

    // Display the training details with accurate progress
    displayTrainingDetailsModal(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, actualProgress.progress, progressSource);
  } catch (error) {
    console.error('Error in viewTrainingDetailsWithSweetAlert:', error);

    // Fallback to basic modal display instead of error
    displayBasicTrainingDetails(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, progress || 0);
  }
}

// Fallback function for basic training details display
function displayBasicTrainingDetails(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, progress) {
  const modal = new bootstrap.Modal(document.getElementById('trainingDetailsModal'));
  const contentDiv = document.getElementById('trainingDetailsContent');

  if (!contentDiv) {
    console.error('Training details content div not found');
    return;
  }

  // Format dates safely
  const formatDateSafe = (dateString) => {
    if (!dateString || dateString === '-' || dateString === 'null' || dateString === 'undefined') {
      return 'Not specified';
    }
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        return dateString;
      }
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    } catch (e) {
      return dateString || 'Not specified';
    }
  };

  // Format source badge
  const formatSourceBadge = (sourceValue) => {
    switch(sourceValue) {
      case 'admin_assigned':
        return '<span class="badge bg-danger text-white"><i class="fas fa-user-shield me-1"></i>Admin Assigned</span>';
      case 'competency_assigned':
      case 'competency_gap':
        return '<span class="badge bg-warning text-dark"><i class="fas fa-chart-line me-1"></i>Competency Gap</span>';
      case 'destination_assigned':
        return '<span class="badge bg-info text-white"><i class="fas fa-map-marker-alt me-1"></i>Destination Training</span>';
      case 'auto_assigned':
        return '<span class="badge bg-success text-white"><i class="fas fa-robot me-1"></i>Auto Assigned</span>';
      default:
        return `<span class="badge bg-secondary text-white"><i class="fas fa-question me-1"></i>${sourceValue ? sourceValue.replace('_', ' ').toUpperCase() : 'UNKNOWN'}</span>`;
    }
  };

  // Determine status styling
  let statusBadgeColor = 'bg-secondary';
  let progressColor = 'bg-secondary';
  const progressValue = parseInt(progress) || 0;

  if (progressValue >= 100 || status === 'Completed') {
    statusBadgeColor = 'bg-success';
    progressColor = 'bg-success';
  } else if (progressValue >= 50) {
    statusBadgeColor = 'bg-warning';
    progressColor = 'bg-warning';
  } else if (progressValue > 0) {
    statusBadgeColor = 'bg-info';
    progressColor = 'bg-info';
  }

  // Build content HTML
  const content = `
    <div class="container-fluid">
      <div class="row g-3">
        <div class="col-12">
          <div class="card border-primary">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Training Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Training Title:</label>
                    <p class="mb-1 fs-6">${trainingTitle || 'Not specified'}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Status:</label>
                    <div class="d-flex align-items-center">
                      <span class="badge ${statusBadgeColor} text-white me-2"><i class="fas fa-info-circle me-1"></i>${status || 'Not Started'}</span>
                      <small class="text-muted">(${progressValue}% complete)</small>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Source:</label>
                    <div>${formatSourceBadge(source)}</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Start Date:</label>
                    <p class="mb-1 fs-6"><i class="fas fa-calendar-alt text-success me-1"></i>${formatDateSafe(startDate)}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">End Date:</label>
                    <p class="mb-1 fs-6"><i class="fas fa-calendar-check text-warning me-1"></i>${formatDateSafe(endDate)}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Assigned By:</label>
                    <p class="mb-1 fs-6"><i class="fas fa-user text-info me-1"></i>${assignedBy || 'System'}</p>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Assignment Date:</label>
                    <p class="mb-1 fs-6"><i class="fas fa-calendar-plus text-secondary me-1"></i>${formatDateSafe(assignedDate)}</p>
                  </div>
                  <div class="mb-0">
                    <label class="form-label fw-bold text-primary">Progress:</label>
                    <div class="progress mb-2" style="height: 20px;">
                      <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${progressValue}%" aria-valuenow="${progressValue}" aria-valuemin="0" aria-valuemax="100">
                        <span class="fw-bold">${progressValue}%</span>
                      </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <small class="text-muted">Training completion progress</small>
                      <small class="text-info"><i class="fas fa-info-circle me-1"></i>ID: ${trainingId}</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  contentDiv.innerHTML = content;
  modal.show();
}

// Get actual training progress from page data
function getActualTrainingProgress(trainingId, trainingTitle, initialProgress, source) {
  let bestProgress = parseInt(initialProgress) || 0;
  let progressSource = 'Initial Parameter';

  console.log('Looking for training:', trainingTitle, 'with initial progress:', initialProgress);

  // If we already have a valid progress from the initial parameter, use it
  if (bestProgress > 0) {
    progressSource = 'Training Data';
    console.log('Using initial progress:', bestProgress);
  }

  // Try to find the training row in the current page for additional verification
  const allRows = document.querySelectorAll('tbody tr');
  console.log('Found', allRows.length, 'rows to search');

  for (const row of allRows) {
    // Check if this row matches our training
    const titleCell = row.querySelector('td:nth-child(2)');
    const progressCell = row.querySelector('.progress-bar');
    const statusCell = row.querySelector('td:nth-child(5)'); // Status is column 5
    const progressText = row.querySelector('small:contains("% complete")');

    if (titleCell) {
      const rowTitle = titleCell.textContent.trim().split('\n')[0].trim(); // Get first line only
      console.log('Checking row title:', rowTitle, 'against:', trainingTitle.trim());

      if (rowTitle === trainingTitle.trim() || rowTitle.includes(trainingTitle.trim())) {
        console.log('Found matching row!');

        // Extract progress from progress bar
        if (progressCell) {
          const progressWidth = progressCell.style.width || progressCell.getAttribute('aria-valuenow');
          console.log('Progress width found:', progressWidth);

          if (progressWidth) {
            const extractedProgress = parseInt(progressWidth.replace('%', ''));
            if (!isNaN(extractedProgress) && extractedProgress > bestProgress) {
              bestProgress = extractedProgress;
              progressSource = 'Page Progress Bar';
              console.log('Updated progress to:', bestProgress, 'from progress bar');
            }
          }
        }

        // Check for progress text in the title cell
        const progressTextInCell = titleCell.textContent.match(/(\d+)% complete/);
        if (progressTextInCell) {
          const textProgress = parseInt(progressTextInCell[1]);
          if (!isNaN(textProgress) && textProgress > bestProgress) {
            bestProgress = textProgress;
            progressSource = 'Progress Text';
            console.log('Updated progress to:', bestProgress, 'from progress text');
          }
        }

        // Check status for completion indicators
        if (statusCell) {
          const statusText = statusCell.textContent.trim().toLowerCase();
          console.log('Status text:', statusText);

          if (statusText.includes('completed') || statusText.includes('100%')) {
            bestProgress = 100;
            progressSource = 'Status Indicator';
            console.log('Updated progress to 100% from status');
          } else if (statusText.includes('in progress') && bestProgress === 0) {
            bestProgress = 25; // Default progress for "in progress" status
            progressSource = 'Status Estimation';
            console.log('Estimated progress as 25% from "in progress" status');
          }
        }
        break;
      }
    }
  }

  // Check for destination training specific progress
  if (source === 'destination_assigned') {
    console.log('Checking destination training progress');
    const destinationRows = document.querySelectorAll('.destination-training-row, [data-training-title]');

    for (const row of destinationRows) {
      const titleElement = row.querySelector('[data-training-title]') || row;
      if (titleElement && titleElement.getAttribute('data-training-title') === trainingTitle) {
        const progressElement = row.querySelector('[data-progress]');
        if (progressElement) {
          const destProgress = parseInt(progressElement.getAttribute('data-progress'));
          if (!isNaN(destProgress) && destProgress >= bestProgress) {
            bestProgress = destProgress;
            progressSource = 'Destination Training Data';
            console.log('Updated progress to:', bestProgress, 'from destination data');
          }
        }
        break;
      }
    }
  }

  // Additional fallback: check for any progress indicators in onclick attributes
  const clickableElements = document.querySelectorAll('[onclick*="viewTrainingDetailsWithSweetAlert"]');
  for (const element of clickableElements) {
    const onclickAttr = element.getAttribute('onclick');
    if (onclickAttr && onclickAttr.includes(trainingTitle)) {
      // Extract progress from onclick parameters
      const progressMatch = onclickAttr.match(/,\s*(\d+)\s*\)\s*$/);
      if (progressMatch) {
        const onclickProgress = parseInt(progressMatch[1]);
        if (!isNaN(onclickProgress) && onclickProgress >= bestProgress) {
          bestProgress = onclickProgress;
          progressSource = 'Onclick Parameter';
          console.log('Updated progress to:', bestProgress, 'from onclick');
        }
      }
      break;
    }
  }

  console.log('Final progress:', bestProgress, 'from source:', progressSource);

  return {
    progress: bestProgress,
    source: progressSource
  };
}

// Display the actual training details modal
function displayTrainingDetailsModal(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, progress, progressSource) {
  console.log('displayTrainingDetailsModal called with progress:', progress, 'from source:', progressSource);

  try {
    // Check if SweetAlert is available, otherwise fall back to basic modal
    if (typeof Swal === 'undefined') {
      console.log('SweetAlert not available, using basic modal');
      displayBasicTrainingDetails(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, progress);
      return;
    }

    // Format dates
    const formatDate = (dateString) => {
      if (!dateString || dateString === '-' || dateString === 'null') return 'Not specified';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      } catch (e) {
        return dateString;
      }
    };

  // Format source display with enhanced badges
  const formatSource = (sourceValue) => {
    switch(sourceValue) {
      case 'admin_assigned':
        return '<span class="badge bg-danger text-white" style="border: 2px solid #dc3545; font-size: 0.9rem;"><i class="fas fa-user-shield me-1"></i>Admin Assigned</span>';
      case 'competency_assigned':
      case 'competency_gap':
        return '<span class="badge bg-warning text-dark" style="border: 2px solid #ffc107; font-size: 0.9rem;"><i class="fas fa-chart-line me-1"></i>Competency Gap</span>';
      case 'destination_assigned':
        return '<span class="badge bg-info text-white" style="border: 2px solid #0dcaf0; font-size: 0.9rem;"><i class="fas fa-map-marker-alt me-1"></i>Destination Training</span>';
      case 'auto_assigned':
        return '<span class="badge bg-success text-white" style="border: 2px solid #198754; font-size: 0.9rem;"><i class="fas fa-robot me-1"></i>Auto Assigned</span>';
      default:
        return `<span class="badge bg-secondary text-white" style="border: 2px solid #6c757d; font-size: 0.9rem;"><i class="fas fa-question me-1"></i>${sourceValue ? sourceValue.replace('_', ' ').toUpperCase() : 'UNKNOWN'}</span>`;
    }
  };

  // Enhanced progress and status determination with accurate data
  let displayProgress = parseInt(progress) || 0;
  let actualStatus = status || 'Not Started';
  let progressColor = 'bg-secondary';
  let statusIcon = 'fas fa-clock';
  let statusBadgeColor = 'bg-secondary';

  console.log('Modal display - Raw progress:', progress, 'Parsed progress:', displayProgress, 'Status:', actualStatus);

  // If progress is still 0, try to get it from the current page
  if (displayProgress === 0) {
    // Look for the training in the current table to get progress
    const tableRows = document.querySelectorAll('tbody tr');
    for (const row of tableRows) {
      const titleCell = row.querySelector('td:nth-child(2)');
      if (titleCell && titleCell.textContent.trim().includes(trainingTitle)) {
        // Check for progress bar in this row
        const progressBar = row.querySelector('.progress-bar');
        if (progressBar) {
          const width = progressBar.style.width;
          if (width) {
            displayProgress = parseInt(width.replace('%', '')) || 0;
            console.log('Found progress from table:', displayProgress);
            break;
          }
        }

        // Check for progress text
        const progressText = titleCell.textContent.match(/(\d+)% complete/);
        if (progressText) {
          displayProgress = parseInt(progressText[1]) || 0;
          console.log('Found progress from text:', displayProgress);
          break;
        }
      }
    }
  }

  // Determine status and styling based on actual progress
  if (actualStatus === 'Completed to Assign' || actualStatus === 'Completed' || actualStatus.toLowerCase().includes('completed')) {
    displayProgress = Math.max(displayProgress, 100); // Ensure completed shows 100%
    progressColor = 'bg-success';
    statusIcon = 'fas fa-check-circle';
    statusBadgeColor = 'bg-success';
    actualStatus = 'Completed';
  } else if (displayProgress >= 100) {
    progressColor = 'bg-success';
    statusIcon = 'fas fa-check-circle';
    statusBadgeColor = 'bg-success';
    actualStatus = 'Completed';
  } else if (displayProgress >= 80) {
    progressColor = 'bg-success';
    statusIcon = 'fas fa-check-circle';
    statusBadgeColor = 'bg-success';
    actualStatus = 'Nearly Complete';
  } else if (displayProgress >= 50) {
    progressColor = 'bg-warning';
    statusIcon = 'fas fa-play-circle';
    statusBadgeColor = 'bg-warning';
    actualStatus = 'In Progress';
  } else if (displayProgress > 0) {
    progressColor = 'bg-info';
    statusIcon = 'fas fa-play-circle';
    statusBadgeColor = 'bg-info';
    actualStatus = 'Started';
  } else {
    // Check if training is assigned but not started
    if (actualStatus === 'Assigned' || actualStatus === 'Scheduled' || actualStatus === 'Active') {
      statusBadgeColor = 'bg-primary';
      statusIcon = 'fas fa-calendar-check';
    } else {
      statusBadgeColor = 'bg-secondary';
      statusIcon = 'fas fa-clock';
      actualStatus = 'Not Started';
    }
  }

  console.log('Final display progress:', displayProgress, 'Status:', actualStatus);

  Swal.fire({
    title: '<i class="fas fa-info-circle text-primary me-2"></i>Training Details',
    html: `
      <div class="container-fluid">
        <div class="row g-3">
          <div class="col-12">
            <div class="card border-primary">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Training Information</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">Training Title:</label>
                      <p class="mb-1 fs-6">${trainingTitle || 'Not specified'}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">Status:</label>
                      <div class="d-flex align-items-center">
                        <span class="badge ${statusBadgeColor} text-white me-2"><i class="${statusIcon} me-1"></i>${actualStatus}</span>
                        <small class="text-muted">(${displayProgress}% complete)</small>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">Source:</label>
                      <div>${formatSource(source)}</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">Start Date:</label>
                      <p class="mb-1 fs-6"><i class="fas fa-calendar-alt text-success me-1"></i>${formatDate(startDate)}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">End Date:</label>
                      <p class="mb-1 fs-6"><i class="fas fa-calendar-check text-warning me-1"></i>${formatDate(endDate)}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">Assigned By:</label>
                      <p class="mb-1 fs-6"><i class="fas fa-user text-info me-1"></i>${assignedBy || 'System'}</p>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12">
                    <div class="mb-3">
                      <label class="form-label fw-bold text-primary">Assignment Date:</label>
                      <p class="mb-1 fs-6"><i class="fas fa-calendar-plus text-secondary me-1"></i>${formatDate(assignedDate)}</p>
                    </div>
                    <div class="mb-0">
                      <label class="form-label fw-bold text-primary">Progress:</label>
                      <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${displayProgress}%" aria-valuenow="${displayProgress}" aria-valuemin="0" aria-valuemax="100">
                          <span class="fw-bold">${displayProgress}%</span>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Training completion progress</small>
                        <small class="text-info"><i class="fas fa-database me-1"></i>Source: ${progressSource}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    width: '800px',
    showCloseButton: true,
    showCancelButton: false,
    confirmButtonText: '<i class="fas fa-check me-1"></i>Got it!',
    confirmButtonColor: '#0d6efd',
    customClass: {
      popup: 'swal2-popup-large',
      confirmButton: 'btn btn-primary',
      title: 'fs-4'
    },
    buttonsStyling: false
  });
  } catch (error) {
    console.error('Error in displayTrainingDetailsModal:', error);

    // Fallback to basic modal display instead of error message
    displayBasicTrainingDetails(trainingId, trainingTitle, startDate, endDate, status, source, assignedBy, assignedDate, progress);
  }
}

// Request Training Extension with SweetAlert
function requestTrainingExtensionWithConfirmation(trainingId, trainingTitle) {
  Swal.fire({
    title: 'Request Training Extension',
    html: `
      <div class="text-start">
        <p><strong>Training:</strong> ${trainingTitle}</p>
        <p class="text-muted mb-3">Request additional time to complete this training:</p>

        <div class="mb-3">
          <label class="form-label fw-bold">Extension Period:</label>
          <select id="extensionPeriod" class="form-select">
            <option value="7">7 days</option>
            <option value="14">14 days</option>
            <option value="30" selected>30 days</option>
            <option value="60">60 days</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Reason for Extension:</label>
          <textarea id="extensionReason" class="form-control" rows="3" placeholder="Please explain why you need additional time..."></textarea>
        </div>

        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Note:</strong> Extension requests are subject to approval by your supervisor or training administrator.
        </div>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#ffc107',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-paper-plane me-1"></i>Submit Request',
    cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
    customClass: {
      popup: 'swal2-popup-large',
      confirmButton: 'btn btn-warning',
      cancelButton: 'btn btn-secondary'
    },
    buttonsStyling: false,
    preConfirm: () => {
      const period = document.getElementById('extensionPeriod').value;
      const reason = document.getElementById('extensionReason').value.trim();

      if (!reason) {
        Swal.showValidationMessage('Please provide a reason for the extension request');
        return false;
      }

      return { period: period, reason: reason };
    }
  }).then((result) => {
    if (result.isConfirmed) {
      submitTrainingExtensionRequest(trainingId, trainingTitle, result.value.period, result.value.reason);
    }
  });
}

// Submit Training Extension Request
function submitTrainingExtensionRequest(trainingId, trainingTitle, period, reason) {
  // Show loading
  Swal.fire({
    title: 'Submitting Extension Request...',
    html: `
      <div class="text-center">
        <div class="spinner-border text-warning mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p>Submitting your extension request for <strong>${trainingTitle}</strong></p>
      </div>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false
  });

  // Simulate API call (replace with actual endpoint)
  fetch('/employee/training/request-extension', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      training_id: trainingId,
      extension_days: period,
      reason: reason
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        title: 'Extension Request Submitted!',
        html: `
          <div class="text-center">
            <i class="fas fa-paper-plane text-success" style="font-size: 3rem;"></i>
            <p class="mt-3">Your extension request has been submitted successfully.</p>
            <div class="alert alert-success mt-3">
              <i class="fas fa-clock me-2"></i>
              <strong>Requested Extension:</strong> ${period} days<br>
              <strong>Status:</strong> Pending Approval
            </div>
          </div>
        `,
        icon: 'success',
        confirmButtonText: '<i class="fas fa-check me-1"></i>Understood',
        confirmButtonColor: '#28a745',
        customClass: {
          confirmButton: 'btn btn-success'
        },
        buttonsStyling: false
      });
    } else {
      Swal.fire({
        title: 'Request Failed',
        html: `
          <div class="text-center">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <p class="mt-3">${data.message || 'Failed to submit extension request'}</p>
          </div>
        `,
        icon: 'error',
        confirmButtonText: '<i class="fas fa-redo me-1"></i>Try Again',
        confirmButtonColor: '#dc3545',
        customClass: {
          confirmButton: 'btn btn-danger'
        },
        buttonsStyling: false
      });
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      title: 'Network Error',
      html: `
        <div class="text-center">
          <i class="fas fa-wifi text-danger" style="font-size: 3rem;"></i>
          <p class="mt-3">A network error occurred while submitting your request.</p>
        </div>
      `,
      icon: 'error',
      confirmButtonText: '<i class="fas fa-redo me-1"></i>Retry',
      confirmButtonColor: '#dc3545',
      customClass: {
        confirmButton: 'btn btn-danger'
      },
      buttonsStyling: false
    });
  });
}

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

  // Enhanced SweetAlert Toast Notifications
  function showSweetAlertToast(type, message, duration = 3000) {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: duration,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      },
      customClass: {
        popup: 'swal2-toast-custom'
      }
    });

    const iconMap = {
      'success': 'success',
      'error': 'error',
      'warning': 'warning',
      'info': 'info'
    };

    Toast.fire({
      icon: iconMap[type] || 'info',
      title: message
    });
  }

  // Backward compatibility function
  function showToast(type, message) {
    showSweetAlertToast(type, message);
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
      const progress = parseInt(button.getAttribute('data-progress')) || 0;

      // Populate modal fields

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

// Add custom CSS for SweetAlert enhancements
const style = document.createElement('style');
style.textContent = `
  .swal2-popup-large {
    width: 90% !important;
    max-width: 800px !important;
  }

  .swal2-toast-custom {
    font-size: 14px !important;
  }

  .swal2-html-container {
    overflow: visible !important;
  }

  .swal2-popup .progress {
    background-color: #e9ecef;
  }

  .swal2-popup .badge {
    font-size: 0.8rem;
  }

  .swal2-popup .alert {
    margin-bottom: 0;
  }

  .swal2-popup .form-control {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
  }

  .swal2-popup .form-select {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
  }

  .swal2-popup .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  }

  .swal2-popup .text-start {
    text-align: left !important;
  }
`;
document.head.appendChild(style);

</script>
