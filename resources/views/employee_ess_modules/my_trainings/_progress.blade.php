{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">Training Progress</h4>
    <button type="button" class="btn btn-outline-primary btn-sm" id="refresh-progress-btn" onclick="manualRefreshProgress()">
      <i class="bi bi-arrow-clockwise"></i> Refresh Progress
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Training ID</th>
            <th>Training Title</th>
            <th>Progress (%)</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th>Expired Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            // Show progress for both approved training requests AND competency gap trainings
            $currentEmployeeId = Auth::user()->employee_id;

            // Filter to include BOTH:
            // 1. Approved training requests from _requests.blade.php
            // 2. Trainings from competency gaps
            $trainingProgressItems = collect($progress)->filter(function ($item) use ($currentEmployeeId) {
                // Include approved requests
                if(isset($item->source) && $item->source == 'approved_request') {
                    return ($item->employee_id ?? $currentEmployeeId) == $currentEmployeeId;
                }

                // Also include competency gap trainings
                if(isset($item->source) && $item->source == 'competency_gap') {
                    return ($item->employee_id ?? $currentEmployeeId) == $currentEmployeeId;
                }

                // Include other training progress sources
                return isset($item->source) && ($item->employee_id ?? $currentEmployeeId) == $currentEmployeeId;
            });

            // Group by training title to eliminate any remaining duplicates
            $groupedProgress = $trainingProgressItems->groupBy(function ($item) {
                $trainingTitle = strtolower(trim($item->training_title ?? ''));

                // Normalize training title
                $normalizedTitle = preg_replace('/\s+/', ' ', $trainingTitle);
                $normalizedTitle = str_replace([' training', ' course', ' program', ' skills'], '', $normalizedTitle);
                $normalizedTitle = trim($normalizedTitle);

                return $normalizedTitle;
            });

            // Take only one item per unique training and add sequential ID
            $uniqueProgress = collect();
                $sequentialId = 1;

                foreach ($groupedProgress as $group) {
                    // Get the item with the HIGHEST progress percentage
                    // If progress is equal, pick the most recently updated one
                    $bestItem = $group->sort(function($a, $b) {
                        // First compare progress percentage (descending)
                        $progressA = isset($a->progress_percentage) ? $a->progress_percentage : ($a->progress ?? 0);
                        $progressB = isset($b->progress_percentage) ? $b->progress_percentage : ($b->progress ?? 0);
                        
                        // Check for explicit "Completed" or "Passed" status which implies 100%
                        if (isset($a->status) && in_array($a->status, ['Completed', 'Passed'])) $progressA = 100;
                        if (isset($b->status) && in_array($b->status, ['Completed', 'Passed'])) $progressB = 100;
                        
                        if ($progressA != $progressB) {
                            return $progressB <=> $progressA; // Descending order of progress
                        }
                        
                        // If progress is equal, compare timestamp (descending)
                        $updatedA = $a->last_updated ?? $a->updated_at ?? now();
                        $updatedB = $b->last_updated ?? $b->updated_at ?? now();
                        return strtotime($updatedB) <=> strtotime($updatedA);
                    })->first();

                    // Add sequential ID for display
                    $bestItem->display_id = $sequentialId++;
                    $uniqueProgress->push($bestItem);
                }

            // Sort by display ID to maintain consistent order
            $uniqueProgress = $uniqueProgress->sortBy('display_id')->values();
          @endphp
          @forelse($uniqueProgress as $p)
            <tr>
              <td>{{ $p->display_id }}</td>
              <td>
                @php
                  // Show only the requested training title, not duplicated course titles
                  $displayTitle = $p->training_title;

                  // If this is from approved request, use the original request title
                  if(isset($p->source) && $p->source == 'approved_request') {
                    $request = \App\Models\TrainingRequest::where('request_id', str_replace('request_', '', $p->progress_id))->first();
                    if($request) {
                      $displayTitle = $request->training_title;
                    }
                  }

                  // Get additional training metadata for enhanced display
                  $trainingMetadata = null;
                  if(isset($p->course_id) && $p->course_id) {
                    $course = \App\Models\CourseManagement::find($p->course_id);
                    if($course) {
                      $trainingMetadata = [
                        'duration' => $course->duration ?? null,
                        'category' => $course->category ?? null,
                        'instructor' => $course->instructor ?? null,
                        'start_date' => $course->start_date ?? null,
                        'end_date' => $course->end_date ?? null
                      ];
                    }
                  }
                @endphp

                <div class="d-flex flex-column">
                  <div class="fw-semibold">{{ $displayTitle }}</div>

                  {{-- Training metadata badges only --}}
                  @if($trainingMetadata && ($trainingMetadata['category'] || $trainingMetadata['duration']))
                  <div class="mt-1">
                    @if($trainingMetadata['category'])
                      <span class="badge bg-secondary me-1">{{ $trainingMetadata['category'] }}</span>
                    @endif
                    @if($trainingMetadata['duration'])
                      <span class="badge bg-light text-dark me-1">{{ $trainingMetadata['duration'] }}</span>
                    @endif
                  </div>
                  @endif

                   {{-- Enhanced remarks and metadata --}}
                   @php
                     $remarksHtml = '';
                     if(isset($p->remarks)) {
                       $remarksHtml .= '<i class="bi bi-chat-text me-1"></i>' . Str::limit($p->remarks, 50);
                     }
                     if($trainingMetadata) {
                       if($trainingMetadata['instructor']) {
                         $remarksHtml .= '<br><i class="bi bi-person me-1"></i>Instructor: ' . $trainingMetadata['instructor'];
                       }
                       if($trainingMetadata['start_date']) {
                         $remarksHtml .= '<br><i class="bi bi-calendar me-1"></i>' . \Carbon\Carbon::parse($trainingMetadata['start_date'])->format('M d, Y');
                         if($trainingMetadata['end_date']) {
                           $remarksHtml .= ' - ' . \Carbon\Carbon::parse($trainingMetadata['end_date'])->format('M d, Y');
                         }
                       }
                     }
                   @endphp
                   @if(!empty($remarksHtml))
                     <small class="text-muted mt-1">{!! $remarksHtml !!}</small>
                   @endif
                  </div>
              </td>
              <td>
                @php
                  // Get progress value from available data sources with proper employee tracking
                  $progressValue = 0;
                  $progressSource = 'none';
                  $employeeId = Auth::user()->employee_id;

                  // Priority 0: Check if status is explicitly completed/passed (highest priority)
                  if (isset($p->status) && in_array($p->status, ['Completed', 'Passed'])) {
                    $progressValue = 100;
                    $progressSource = 'status_check';
                  }

                  // Priority 1: Check for exam progress (highest priority for real-time updates)
                  $examAttempt = null;
                  $effectiveCourseId = $p->course_id ?? null;

                  // Fallback: If course_id is missing, try to find it by title
                  if (!$effectiveCourseId && isset($p->training_title)) {
                    $foundCourse = \App\Models\CourseManagement::where('course_title', $p->training_title)
                      ->orWhere('course_title', 'LIKE', '%' . $p->training_title . '%')
                      ->first();
                    if ($foundCourse) {
                      $effectiveCourseId = $foundCourse->course_id;
                    }
                  }

                  if ($effectiveCourseId) {
                    $examAttempt = \App\Models\ExamAttempt::where('employee_id', $employeeId)
                      ->where('course_id', $effectiveCourseId)
                      ->whereIn('status', ['completed', 'failed']) // Include both passed and failed attempts
                      ->orderBy('completed_at', 'desc')
                      ->first();

                    if ($examAttempt) {
                      // Use actual exam score for progress, but set to 100% if passed (>=80%)
                      $actualScore = round($examAttempt->score);
                      
                      // Only overwrite if we don't already have a 100% status from Priority 0
                      // OR if the exam is passed (which is also 100%)
                      if ($progressValue < 100 || $actualScore >= 80) {
                        $progressValue = $actualScore >= 80 ? 100 : $actualScore;
                        $progressSource = 'exam';
                      }
                    }
                  }

                  // Priority 2: Check training dashboard for this specific employee (updated by ExamController)
                  if ($progressValue == 0 && isset($p->course_id) && $p->course_id) {
                    $trainingRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                      ->where('course_id', $p->course_id)
                      ->orderBy('updated_at', 'desc') // Get most recent update
                      ->first();
                    if ($trainingRecord && $trainingRecord->progress > 0) {
                      $progressValue = max(0, min(100, (float)$trainingRecord->progress));
                      $progressSource = 'dashboard';
                    }
                  }

                  // Priority 3: Check for competency-based progress
                  if ($progressValue == 0 && isset($p->training_title)) {
                    // Find matching competency profile for this employee
                    $competencyName = str_replace([' Training', ' Course', ' Program'], '', $p->training_title);
                    $competencyProfile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)
                      ->whereHas('competency', function($query) use ($competencyName) {
                        $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                      })->first();

                    if ($competencyProfile && $competencyProfile->proficiency_level > 0) {
                      $progressValue = min(100, round(($competencyProfile->proficiency_level / 5) * 100));
                      $progressSource = 'competency';
                    }
                  }

                  // Priority 4: Check approved training requests with dashboard records
                  if ($progressValue == 0 && isset($p->source) && $p->source == 'approved_request') {
                    // For approved requests, check if there's a dashboard record
                    if (isset($p->course_id) && $p->course_id) {
                      $dashboardRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                        ->where('course_id', $p->course_id)
                        ->first();

                      if ($dashboardRecord) {
                        $progressValue = max(0, min(100, (float)$dashboardRecord->progress));
                        $progressSource = 'approved_request_dashboard';
                      }
                    }
                  }

                  // Priority 5: Use controller provided progress only if it matches this employee
                  if ($progressValue == 0 && isset($p->progress_percentage) && is_numeric($p->progress_percentage)) {
                    // Verify this progress belongs to the current employee
                    if (isset($p->employee_id) && $p->employee_id == $employeeId) {
                      $progressValue = max(0, min(100, (float)$p->progress_percentage));
                      $progressSource = 'system';
                    }
                  }

                  // Priority 6: Check destination knowledge training progress
                  if ($progressValue == 0 && isset($p->training_title)) {
                    $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                      ->where('destination_name', 'LIKE', '%' . $p->training_title . '%')
                      ->first();

                    if ($destinationRecord && $destinationRecord->progress > 0) {
                      $progressValue = min(100, round($destinationRecord->progress));
                      $progressSource = 'destination';
                    }
                  }

                  // Calculate expired date from available sources
                  $expiredDate = null;
                  $expiredDateSource = 'none';

                  // Priority 1: Check competency gap
                  $competencyGap = \App\Models\CompetencyGap::where('employee_id', $employeeId)->first();
                  if ($competencyGap && $competencyGap->expired_date) {
                    $expiredDate = $competencyGap->expired_date;
                    $expiredDateSource = 'competency_gap';
                  }

                  // Priority 2: Check training dashboard
                  if (!$expiredDate && isset($p->course_id)) {
                    $trainingRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                      ->where('course_id', $p->course_id)
                      ->first();
                    if ($trainingRecord && $trainingRecord->expired_date) {
                      $expiredDate = $trainingRecord->expired_date;
                      $expiredDateSource = 'training_dashboard';
                    }
                  }

                  // Priority 3: Use request data if available
                  if (!$expiredDate && isset($p->expired_date)) {
                    $expiredDate = $p->expired_date;
                    $expiredDateSource = 'request_data';
                  }

                  // Enhanced progress bar colors based on exam results and thresholds
                  if ($progressSource === 'exam' && $examAttempt) {
                    // Exam-specific coloring (80% pass threshold)
                    if ($examAttempt->score >= 80) {
                      $progressColor = 'bg-success'; // Green for passed
                    } else {
                      $progressColor = 'bg-danger'; // Red for failed
                    }
                  } else {
                    // Standard progress coloring
                    if ($progressValue >= 80) $progressColor = 'bg-success';
                    elseif ($progressValue >= 60) $progressColor = 'bg-info';
                    elseif ($progressValue >= 40) $progressColor = 'bg-warning';
                    elseif ($progressValue > 0) $progressColor = 'bg-primary';
                    else $progressColor = 'bg-secondary';
                  }
                @endphp

                <div class="progress" style="height: 25px; border-radius: 15px; overflow: hidden; background-color: #f0f0f0; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                  <div class="progress-bar {{ $progressColor }} fw-bold" 
                       role="progressbar" 
                       style="width: {{ $progressValue }}%; transition: width 0.8s ease;">
                    <div class="d-flex align-items-center justify-content-center w-100 h-100" style="gap: 6px; font-size: 0.9rem; text-shadow: 0 1px 1px rgba(0,0,0,0.2);">
                      <span>{{ $progressValue }}%</span>
                      @if($progressSource === 'exam' && $examAttempt)
                        <i class="bi bi-{{ $examAttempt->score >= 80 ? 'check-circle-fill' : 'x-circle-fill' }}" style="font-size: 1rem;"></i>
                      @elseif($progressValue >= 100)
                        <i class="bi bi-check-circle-fill" style="font-size: 1rem;"></i>
                      @endif
                    </div>
                  </div>
                </div>
              </td>
              <td>
                @php
                  // Determine status based on REAL-TIME progress percentage and exam results
                  $currentProgressValue = $progressValue;
                  $improvementMessage = '';

                  // Enhanced status logic with exam-specific handling
                  if ($progressSource === 'exam' && $examAttempt) {
                    // Exam-based status determination (80% threshold)
                    if ($examAttempt->score >= 80) {
                      $displayStatus = 'Passed';
                      $statusClass = 'bg-success';
                      $currentProgressValue = 100; // Set to 100% for passed exams
                    } else {
                      $displayStatus = 'Failed';
                      $statusClass = 'bg-danger';
                      $improvementMessage = 'Needs Improvement - Retake Required';
                    }
                  } else {
                    // Non-exam status determination
                    if ($currentProgressValue >= 100) {
                      $displayStatus = 'Completed';
                      $statusClass = 'bg-success';
                    } elseif ($currentProgressValue >= 80) {
                      $displayStatus = 'Passed';
                      $statusClass = 'bg-success';
                    } elseif ($currentProgressValue >= 40 && $currentProgressValue < 80) {
                      $displayStatus = 'In Progress';
                      $statusClass = 'bg-warning';
                      $improvementMessage = 'Needs More Improvement';
                    } elseif ($currentProgressValue > 0) {
                      $displayStatus = 'Started';
                      $statusClass = 'bg-primary';
                      $improvementMessage = 'Continue Learning';
                    } else {
                      $displayStatus = 'Ready to Start';
                      $statusClass = 'bg-secondary';
                    }
                  }
                @endphp
                <span class="badge {{ $statusClass }}">{{ $displayStatus }}</span>
              </td>
              <td>
                @php
                  $lastUpdated = $p->last_updated ?? now();
                  $updateSource = 'System';

                  if(isset($p->source)) {
                    switch($p->source) {
                      case 'approved_request':
                        $updateSource = 'Training Request';
                        break;
                      case 'dashboard_progress':
                        $updateSource = 'Training Dashboard';
                        break;
                      default:
                        $updateSource = 'System';
                    }
                  }

                  try {
                    $mostRecentUpdate = \Carbon\Carbon::parse($lastUpdated);
                  } catch (Exception $e) {
                    $mostRecentUpdate = \Carbon\Carbon::now();
                  }
                @endphp

                <div class="d-flex flex-column">
                  <span class="fw-semibold">{{ $mostRecentUpdate->format('M d, Y') }}</span>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $mostRecentUpdate->diffForHumans() }}
                  </small>
                </div>
              </td>
              <td>
                @php
                  $showExpiredDate = $expiredDate && !empty(trim($expiredDate)) && trim($expiredDate) !== '0000-00-00 00:00:00' && trim($expiredDate) !== '0000-00-00';
                @endphp
                @if($showExpiredDate)
                  @php
                    try {
                      $expiredDateRaw = trim($expiredDate);
                      $expiredDateObj = \Carbon\Carbon::parse($expiredDateRaw);
                      $now = \Carbon\Carbon::now();
                      $dateFormatted = $expiredDateObj->format('M d, Y');
                      $timeFormatted = $expiredDateObj->format('h:i A');
                      $daysLeft = $now->diffInDays($expiredDateObj, false);
                      $isExpired = $now->gt($expiredDateObj);
                    } catch (Exception $e) {
                      $showExpiredDate = false;
                    }
                  @endphp
                  @if($showExpiredDate)
                    <div class="d-flex flex-column align-items-start">
                      <div><strong>{{ $dateFormatted }}</strong></div>
                      <div class="w-100 mt-1">
                        @if(!$isExpired)
                          <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock"></i> {{ floor(abs($daysLeft)) }} day{{ floor(abs($daysLeft)) == 1 ? '' : 's' }} left
                          </span>
                        @else
                          <span class="badge bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle"></i> Expired {{ floor(abs($daysLeft)) }} day{{ floor(abs($daysLeft)) == 1 ? '' : 's' }} ago
                          </span>
                        @endif
                      </div>
                     </div>
                  @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                      <i class="bi bi-calendar-x"></i> Invalid Date
                    </span>
                  @endif
                @else
                  <span class="badge bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-calendar-x"></i> Not Set
                  </span>
                @endif
              </td>
              <td class="text-center">
                @php
                  $currentProgressValue = $progressValue;
                  $isCompleted = $currentProgressValue >= 100;
                  $finalProgressValue = $progressValue;
                @endphp

                <div class="d-flex gap-1 justify-content-center">
                  @if($isCompleted)
                    {{-- Training completed - redirect to completed section --}}
                    <button class="btn btn-success btn-sm" onclick="redirectToCompleted('{{ $displayTitle }}', 0)">
                      <i class="bi bi-check-circle"></i> View in Completed
                    </button>
                    @php
                      $certificate = \App\Models\TrainingRecordCertificateTracking::where('employee_id', Auth::user()->employee_id)
                          ->where('course_id', $p->course_id ?? 0)
                          ->first();
                    @endphp
                    @if($certificate)
                      <a href="{{ route('certificates.view', $certificate->id) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="bi bi-award"></i> Certificate
                      </a>
                    @endif
                  @else
                    {{-- View Details button --}}
                    <button
                      class="btn btn-outline-primary btn-sm"
                      onclick="viewProgressDetails('{{ $p->progress_id }}', '{{ $displayTitle }}', {{ $finalProgressValue }}, '{{ $displayStatus }}', '{{ $mostRecentUpdate->format('M d, Y') }}', '{{ $p->remarks ?? 'No remarks' }}', '{{ $progressSource ?? ($p->source ?? 'Not specified') }}', '{{ $showExpiredDate ? \Carbon\Carbon::parse($expiredDate)->format('M d, Y') : 'Not Set' }}', '{{ $p->course_id ?? '' }}')"
                      title="View detailed progress information"
                    >
                      <i class="bi bi-eye"></i> View Details
                    </button>

                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No progress records</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- View Details Modal --}}
<div class="modal fade" id="viewProgressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Training Progress Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">Training Title</label>
            <p class="form-control-plaintext" id="viewTitle"></p>
          </div>
          <div class="col-8 mb-3">
            <label class="form-label fw-bold">Progress</label>
            <div id="viewProgressBar"></div>
          </div>
          <div class="col-4 mb-3">
            <label class="form-label fw-bold">Status</label>
            <p class="form-control-plaintext" id="viewStatus"></p>
          </div>
          <div class="col-6 mb-3">
            <label class="form-label fw-bold">Last Updated</label>
            <p class="form-control-plaintext" id="viewUpdated"></p>
          </div>
          <div class="col-6 mb-3">
            <label class="form-label fw-bold">Expired Date</label>
            <p class="form-control-plaintext" id="viewExpired"></p>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">Progress Source</label>
            <p class="form-control-plaintext" id="viewSource"></p>
          </div>
          <div class="col-12 mb-3" id="examScoreSection" style="display: none;">
            <label class="form-label fw-bold">Exam Score</label>
            <p class="form-control-plaintext" id="viewExamScore"></p>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">Remarks</label>
            <p class="form-control-plaintext" id="viewRemarks"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Add --}}
<div class="modal fade" id="addProgressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
  <form action="{{ route('employee.my_trainings.store') }}" method="POST">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Add Progress</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
          <div class="mb-3"><label class="form-label">Training Title</label>
            <input type="text" name="training_title" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Progress (%)</label>
            <input type="number" min="0" max="100" name="progress_percentage" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Last Updated</label>
            <input type="datetime-local" name="last_updated" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
// Handle View Progress Modal
document.getElementById('viewProgressModal')?.addEventListener('show.bs.modal', function (e) {
  const button = e.relatedTarget;

  // Get data from button attributes
  const title = button.getAttribute('data-title');
  const percent = button.getAttribute('data-percent');
  const status = button.getAttribute('data-status');
  const updated = button.getAttribute('data-updated');
  const remarks = button.getAttribute('data-remarks');
  const source = button.getAttribute('data-source');
  const expired = button.getAttribute('data-expired');
  const courseId = button.getAttribute('data-course-id');

  // Debug: Log all values to console for troubleshooting
  console.log('Modal Data:', {
    title: title,
    percent: percent,
    status: status,
    updated: updated,
    remarks: remarks,
    source: source,
    expired: expired
  });

  // Update modal content
  document.getElementById('viewTitle').textContent = title || 'N/A';
  document.getElementById('viewUpdated').textContent = updated || 'N/A';
  document.getElementById('viewRemarks').textContent = remarks || 'No remarks available';
  document.getElementById('viewExpired').textContent = expired || 'Not Set';

  // Format status display with appropriate badge
  let statusBadgeClass = 'bg-secondary';
  if (status === 'Completed') statusBadgeClass = 'bg-success';
  else if (status === 'Started') statusBadgeClass = 'bg-primary';
  else if (status === 'Failed') statusBadgeClass = 'bg-danger';
  else if (status === 'Ready to Start') statusBadgeClass = 'bg-info';

  document.getElementById('viewStatus').innerHTML = `<span class="badge ${statusBadgeClass}">${status || 'Unknown'}</span>`;

  // Format source display with icons
  let sourceDisplay = source || 'Not specified';
  let sourceIcon = 'bi-info-circle';

  if (source === 'approved_request') {
    sourceDisplay = 'From Training Request';
    sourceIcon = 'bi-check-circle';
  } else if (source === 'employee_training_dashboard') {
    sourceDisplay = 'Admin Assigned Training';
    sourceIcon = 'bi-person-gear';
  } else if (source === 'competency_profile') {
    sourceDisplay = 'From Competency Profile';
    sourceIcon = 'bi-award';
  } else if (source === 'training_dashboard') {
    sourceDisplay = 'From Training Dashboard';
    sourceIcon = 'bi-book';
  } else if (source === 'exam_progress') {
    sourceDisplay = 'From Exam Progress';
    sourceIcon = 'bi-mortarboard';
  } else if (source === 'destination_knowledge' || source === 'destination_completed' || source === 'destination_progress') {
    sourceDisplay = 'From Destination Knowledge Training';
    sourceIcon = 'bi-geo-alt';
  } else if (source === 'training_matched') {
    sourceDisplay = 'From Matched Training Record';
    sourceIcon = 'bi-book-half';
  } else if (source === 'controller_data' || source === 'controller_progress') {
    sourceDisplay = 'From System Data';
    sourceIcon = 'bi-database';
  }

  document.getElementById('viewSource').innerHTML = `<i class="${sourceIcon} me-2"></i>${sourceDisplay}`;

  // Create progress bar with proper value handling
  const progressValue = Math.max(0, Math.min(100, parseInt(percent) || 0));
  let progressColor = 'bg-secondary';
  if (progressValue >= 80) progressColor = 'bg-success';
  else if (progressValue >= 60) progressColor = 'bg-info';
  else if (progressValue >= 40) progressColor = 'bg-warning';
  else if (progressValue > 0) progressColor = 'bg-primary';

  // Enhanced progress bar with percentage text
  document.getElementById('viewProgressBar').innerHTML = `
    <div class="progress mb-2" style="height: 25px;">
      <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${progressValue}%" aria-valuenow="${progressValue}" aria-valuemin="0" aria-valuemax="100">
        <span class="fw-bold">${progressValue}%</span>
      </div>
    </div>
    <small class="text-muted">Progress: ${progressValue}% of 100%</small>
  `;

  // Show exam score if available
  if (courseId) {
    fetch(`/employee/exam-score/${courseId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.score) {
          document.getElementById('examScoreSection').style.display = 'block';
          const scoreColor = data.score >= 80 ? 'text-success' : 'text-danger';
          const scoreIcon = data.score >= 80 ? 'bi-check-circle' : 'bi-x-circle';
          document.getElementById('viewExamScore').innerHTML = `
            <span class="${scoreColor}">
              <i class="bi ${scoreIcon} me-2"></i>${data.score}%
              <small class="text-muted">(${data.date})</small>
            </span>
          `;
        } else {
          document.getElementById('examScoreSection').style.display = 'none';
        }
      })
      .catch(() => {
        document.getElementById('examScoreSection').style.display = 'none';
      });
  } else {
    document.getElementById('examScoreSection').style.display = 'none';
  }

  console.log('Final Modal Progress Value:', progressValue, 'Color:', progressColor);
});

// Remove all .modal-backdrop elements on page load and after any modal event
function removeAllModalBackdrops() {
  document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
    backdrop.remove();
  });
}
window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);

// Initialize tooltips for score breakdown
window.addEventListener('DOMContentLoaded', function() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

// Auto-refresh progress data after exam completion
function refreshProgressData() {
  // Check if we're coming from an exam result page
  const urlParams = new URLSearchParams(window.location.search);
  const fromExam = urlParams.get('from_exam');
  const examCompleted = urlParams.get('exam_completed');

  if (fromExam === 'true' || examCompleted === 'true') {
    // Wait a moment for database updates to complete, then refresh
    setTimeout(() => {
      window.location.reload();
    }, 2000);
  }

  // Also check localStorage for exam completion flag
  const examCompletedFlag = localStorage.getItem('exam_completed');
  if (examCompletedFlag === 'true') {
    localStorage.removeItem('exam_completed'); // Clear the flag
    setTimeout(() => {
      window.location.reload();
    }, 1500);
  }
}

// Manual refresh function for the refresh button with SweetAlert
function manualRefreshProgress() {
  // Show loading state
  const refreshBtn = document.getElementById('refresh-progress-btn');
  const originalText = refreshBtn.innerHTML;
  refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';
  refreshBtn.disabled = true;

  // Show SweetAlert loading
  Swal.fire({
    title: 'Refreshing Progress',
    text: 'Updating training progress data...',
    icon: 'info',
    allowOutsideClick: false,
    showConfirmButton: false,
    willOpen: () => {
      Swal.showLoading();
    }
  });

  // Force refresh after short delay
  setTimeout(() => {
    window.location.reload();
  }, 1000);
}

// Listen for cross-tab communication about exam completion
window.addEventListener('storage', function(e) {
  if (e.key === 'exam_completed' && e.newValue === 'true') {
    setTimeout(() => {
      window.location.reload();
    }, 2000);
  }
});

// Call refresh function on page load
window.addEventListener('DOMContentLoaded', refreshProgressData);

// Listen for storage events (cross-tab communication for exam completion)
window.addEventListener('storage', function(e) {
  if (e.key === 'exam_completed' && e.newValue === 'true') {
    // Another tab completed an exam, refresh this page
    setTimeout(() => {
      window.location.reload();
    }, 1500);
    // Clear the flag
    localStorage.removeItem('exam_completed');
  }
});

// Call refresh function on page load
window.addEventListener('DOMContentLoaded', refreshProgressData);
</script>

<script>
// Removed START EXAM functionality as requested

// Redirect to completed section with SweetAlert
function redirectToCompleted(trainingTitle, examScore) {
  const message = examScore >= 80 ?
    `Congratulations! You completed "${trainingTitle}" with ${examScore}% score.` :
    `Training "${trainingTitle}" completed.`;

  Swal.fire({
    title: 'Training Completed!',
    text: message + ' Would you like to view it in Completed Trainings?',
    icon: examScore >= 80 ? 'success' : 'info',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, view completed',
    cancelButtonText: 'Stay here'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '{{ route('employee.my_trainings.index') }}?tab=completed';
    }
  });
}


// Delete Progress Function with SweetAlert and Password Verification
function deleteProgressWithConfirmation(progressId, trainingTitle) {
  Swal.fire({
    title: 'Delete Training Progress',
    html: `
      <div class="text-start">
        <p class="mb-3">You are about to delete the progress for:</p>
        <div class="alert alert-warning mb-3">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>${trainingTitle}</strong>
        </div>
        <div class="alert alert-danger mb-3">
          <i class="bi bi-shield-exclamation me-2"></i>
          <strong>Warning:</strong> This action cannot be undone and will permanently remove all progress data.
        </div>
        <div class="mb-3">
          <label for="delete-password" class="form-label fw-bold">Enter your password to confirm:</label>
          <input type="password" id="delete-password" class="form-control" placeholder="Your password" minlength="3">
          <div class="form-text">
            <i class="bi bi-info-circle me-1"></i>
            Password verification is required for security purposes.
          </div>
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete Progress',
    cancelButtonText: 'Cancel',
    focusConfirm: false,
    preConfirm: () => {
      const password = document.getElementById('delete-password').value;
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
      submitDeleteProgress(progressId, trainingTitle, result.value);
    }
  });
}

// Submit delete progress with password verification
function submitDeleteProgress(progressId, trainingTitle, password) {
  // Show loading
  Swal.fire({
    title: 'Processing...',
    text: 'Deleting training progress',
    icon: 'info',
    allowOutsideClick: false,
    showConfirmButton: false,
    willOpen: () => {
      Swal.showLoading();
    }
  });

  // Create form data
  const formData = new FormData();
  formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
  formData.append('_method', 'DELETE');
  formData.append('password_verification', password);

  // Submit request
  fetch(`/employee/my_trainings/${progressId}`, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        title: 'Deleted Successfully!',
        text: `Training progress for "${trainingTitle}" has been deleted.`,
        icon: 'success',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        window.location.reload();
      });
    } else {
      Swal.fire({
        title: 'Delete Failed',
        text: data.message || 'Failed to delete training progress. Please try again.',
        icon: 'error',
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#dc3545'
      }).then(() => {
        // Retry the deletion
        deleteProgressWithConfirmation(progressId, trainingTitle);
      });
    }
  })
  .catch(error => {
    console.error('Delete error:', error);
    Swal.fire({
      title: 'Network Error',
      text: 'Unable to connect to server. Please check your connection and try again.',
      icon: 'error',
      confirmButtonText: 'Retry',
      confirmButtonColor: '#dc3545'
    }).then(() => {
      deleteProgressWithConfirmation(progressId, trainingTitle);
    });
  });
}

// Enhanced View Progress Details with SweetAlert
function viewProgressDetails(progressId, title, percent, status, updated, remarks, source, expired, courseId) {
  // Format source display with icons
  let sourceDisplay = source || 'Not specified';
  let sourceIcon = 'bi-info-circle';

  if (source === 'exam') {
    sourceDisplay = 'From Exam Score';
    sourceIcon = 'bi-mortarboard';
  } else if (source === 'dashboard') {
    sourceDisplay = 'From Training Dashboard';
    sourceIcon = 'bi-book';
  } else if (source === 'approved_request_dashboard') {
    sourceDisplay = 'From Approved Request';
    sourceIcon = 'bi-check-circle';
  } else if (source === 'competency') {
    sourceDisplay = 'From Competency Profile';
    sourceIcon = 'bi-award';
  } else if (source === 'destination') {
    sourceDisplay = 'From Destination Training';
    sourceIcon = 'bi-geo-alt';
  } else if (source === 'system') {
    sourceDisplay = 'From System Data';
    sourceIcon = 'bi-database';
  }

  // Format status badge
  let statusBadgeClass = 'bg-secondary';
  if (status === 'Completed' || status === 'Passed') statusBadgeClass = 'bg-success';
  else if (status === 'Started' || status === 'In Progress') statusBadgeClass = 'bg-primary';
  else if (status === 'Failed') statusBadgeClass = 'bg-danger';
  else if (status === 'Ready to Start') statusBadgeClass = 'bg-info';

  // Format progress bar
  const progressValue = Math.max(0, Math.min(100, parseInt(percent) || 0));
  let progressColor = 'bg-secondary';
  if (progressValue >= 80) progressColor = 'bg-success';
  else if (progressValue >= 60) progressColor = 'bg-info';
  else if (progressValue >= 40) progressColor = 'bg-warning';
  else if (progressValue > 0) progressColor = 'bg-primary';

  Swal.fire({
    title: '<i class="bi bi-eye me-2"></i>Training Progress Details',
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-12">
            <div class="card border-primary">
              <div class="card-header bg-primary bg-opacity-10">
                <h6 class="card-title mb-0 text-primary">
                  <i class="bi bi-book me-2"></i>Training Information
                </h6>
              </div>
              <div class="card-body">
                <h5 class="text-primary">${title}</h5>
                <p class="text-muted mb-0">Progress ID: ${progressId}</p>
              </div>
            </div>
          </div>

          <div class="col-8">
            <div class="card border-success">
              <div class="card-header bg-success bg-opacity-10">
                <h6 class="card-title mb-0 text-success">
                  <i class="bi bi-graph-up me-2"></i>Progress Status
                </h6>
              </div>
              <div class="card-body">
                <div class="progress mb-3" style="height: 25px;">
                  <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${progressValue}%">
                    <span class="fw-bold">${progressValue}%</span>
                  </div>
                </div>
                <p class="mb-0">Progress: ${progressValue}% of 100%</p>
              </div>
            </div>
          </div>

          <div class="col-4">
            <div class="card border-info">
              <div class="card-header bg-info bg-opacity-10">
                <h6 class="card-title mb-0 text-info">
                  <i class="bi bi-flag me-2"></i>Current Status
                </h6>
              </div>
              <div class="card-body text-center">
                <span class="badge ${statusBadgeClass} fs-6">${status}</span>
              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="card border-warning">
              <div class="card-header bg-warning bg-opacity-10">
                <h6 class="card-title mb-0 text-warning">
                  <i class="bi bi-clock me-2"></i>Last Updated
                </h6>
              </div>
              <div class="card-body">
                <p class="mb-0">${updated}</p>
              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="card border-danger">
              <div class="card-header bg-danger bg-opacity-10">
                <h6 class="card-title mb-0 text-danger">
                  <i class="bi bi-calendar-x me-2"></i>Expiration Date
                </h6>
              </div>
              <div class="card-body">
                <p class="mb-0">${expired}</p>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card border-secondary">
              <div class="card-header bg-secondary bg-opacity-10">
                <h6 class="card-title mb-0 text-secondary">
                  <i class="${sourceIcon} me-2"></i>Progress Source
                </h6>
              </div>
              <div class="card-body">
                <p class="mb-0">${sourceDisplay}</p>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card border-light">
              <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                  <i class="bi bi-chat-text me-2"></i>Remarks
                </h6>
              </div>
              <div class="card-body">
                <p class="mb-0">${remarks || 'No remarks available'}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    width: '800px',
    showCloseButton: true,
    showConfirmButton: true,
    confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Close',
    confirmButtonColor: '#6c757d',
    customClass: {
      popup: 'text-start'
    }
  });

  // Fetch and display exam score if available
  if (courseId) {
    fetch(`/employee/exam-score/${courseId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.score) {
          const scoreColor = data.score >= 80 ? 'text-success' : 'text-danger';
          const scoreIcon = data.score >= 80 ? 'bi-check-circle' : 'bi-x-circle';
          const scoreBadge = data.score >= 80 ? 'bg-success' : 'bg-danger';

          // Update the SweetAlert content to include exam score
          const examScoreHtml = `
            <div class="col-12 mt-3">
              <div class="card border-primary">
                <div class="card-header bg-primary bg-opacity-10">
                  <h6 class="card-title mb-0 text-primary">
                    <i class="bi bi-mortarboard me-2"></i>Exam Results
                  </h6>
                </div>
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <h5 class="${scoreColor} mb-1">
                        <i class="bi ${scoreIcon} me-2"></i>${data.score}%
                      </h5>
                      <small class="text-muted">Exam Date: ${data.date}</small>
                    </div>
                    <span class="badge ${scoreBadge} fs-6">
                      ${data.score >= 80 ? 'PASSED' : 'FAILED'}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          `;

          // Note: We can't easily update the existing SweetAlert content,
          // but the exam score info is already shown in the main table
        }
      })
      .catch(() => {
        // Silently handle error - exam score not critical for this view
      });
  }
}
</script>
