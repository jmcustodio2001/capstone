<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">Training Progress</h4>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Progress ID</th>
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
            $uniqueProgress = collect($progress)->unique('progress_id');
          @endphp
          @forelse($uniqueProgress as $p)
            <tr>
              <td>{{ $p->progress_id }}</td>
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

                  {{-- Source badges --}}
                  <div class="mt-1">
                    @if(isset($p->source))
                      @if($p->source == 'approved_request')
                        <span class="badge bg-info me-1">From Request</span>
                      @elseif($p->source == 'employee_training_dashboard')
                        <span class="badge bg-success me-1">Admin Assigned</span>
                      @elseif($p->source == 'competency_assigned')
                        <span class="badge bg-warning me-1">Competency Based</span>
                      @endif
                    @endif

                    {{-- Training metadata badges --}}
                    @if($trainingMetadata)
                      @if($trainingMetadata['category'])
                        <span class="badge bg-secondary me-1">{{ $trainingMetadata['category'] }}</span>
                      @endif
                      @if($trainingMetadata['duration'])
                        <span class="badge bg-light text-dark me-1">{{ $trainingMetadata['duration'] }}</span>
                      @endif
                    @endif
                  </div>

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
                  $progressValue = 0;
                  $progressSource = 'none';
                  $actualProgress = 0;
                  $employeeId = Auth::user()->employee_id;
                  $trainingTitle = $p->training_title;

                  // Check if this is a destination knowledge competency
                  $isDestinationCompetency = stripos($trainingTitle, 'Destination Knowledge') !== false;

                  if ($isDestinationCompetency) {
                    // Extract location name
                    $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $trainingTitle);
                    $locationName = trim($locationName);

                    if (!empty($locationName)) {
                      // Find matching destination knowledge training record
                      $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                        ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                        ->first();
                      if ($destinationRecord) {
                        // Use the same progress calculation as destination knowledge training view
                        $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);

                        // Find matching course ID for this destination
                        $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                        $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                        // Get exam progress (same as destination training view)
                        $combinedProgress = 0;
                        if ($courseId) {
                          $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                        }

                        // Fall back to training dashboard progress if no exam data
                        if ($combinedProgress == 0) {
                          $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                            ->where('course_id', $courseId)
                            ->value('progress');
                          $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                        }

                        $actualProgress = min(100, round($combinedProgress));
                        $progressSource = 'destination';
                      }
                    }
                  } else {
                    // For non-destination competencies, check if this is from approved request first
                    if (isset($p->source) && $p->source == 'approved_request' && isset($p->course_id)) {
                      // Use the progress from the controller calculation for approved requests
                      $actualProgress = $p->progress_percentage ?? 0;
                      $progressSource = 'approved_request';
                    } else {
                      // For other cases, use employee training dashboard
                      $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)->get();

                      foreach ($trainingRecords as $record) {
                        $courseTitle = $record->training_title ?? '';

                        // General competency matching
                        $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $trainingTitle);
                        $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                        if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                          // Get progress from this training record
                          $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $record->course_id);
                          $trainingProgress = $record->progress ?? 0;

                          // Priority: Exam progress > Training record progress
                          $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                          $progressSource = 'training';
                          break;
                        }
                      }
                    }
                  }

                  // Now, check if we have competency profile
                  $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($trainingTitle) {
                    $q->where('competency_name', 'LIKE', '%' . $trainingTitle . '%');
                  })->where('employee_id', $employeeId)->first();

                  if ($competencyProfile) {
                    $storedProficiency = ($competencyProfile->proficiency_level / 5) * 100;
                    $isManuallySet = $competencyProfile->proficiency_level > 1 ||
                                      ($competencyProfile->proficiency_level == 1 && $competencyProfile->assessment_date &&
                                       \Carbon\Carbon::parse($competencyProfile->assessment_date)->diffInDays(now()) < 30);

                    if ($isManuallySet) {
                      $progressValue = $storedProficiency;
                      $progressSource = 'manual';
                    } else {
                      $progressValue = $actualProgress > 0 ? $actualProgress : $storedProficiency;
                    }
                  } else {
                    $progressValue = $actualProgress;
                  }

                  // Calculate expired date
                  $expiredDate = null;
                  
                  // For approved requests, check if expired date is provided from controller
                  if (isset($p->source) && $p->source == 'approved_request' && isset($p->expired_date)) {
                    $expiredDate = $p->expired_date;
                  } else {
                    // For competency profiles, check competency gap
                    if ($competencyProfile) {
                      $competencyGap = \App\Models\CompetencyGap::where('employee_id', $employeeId)
                        ->where('competency_id', $competencyProfile->competency_id)
                        ->first();
                      if ($competencyGap && $competencyGap->expired_date) {
                        $expiredDate = $competencyGap->expired_date;
                      }
                    }
                    
                    // If still no expired date, check training dashboard records
                    if (!$expiredDate && isset($p->course_id)) {
                      $trainingRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                        ->where('course_id', $p->course_id)
                        ->first();
                      if ($trainingRecord && $trainingRecord->expired_date) {
                        $expiredDate = $trainingRecord->expired_date;
                      }
                    }
                  }

                  $progressColor = 'bg-primary';
                  if ($progressValue >= 75) $progressColor = 'bg-success';
                  elseif ($progressValue >= 50) $progressColor = 'bg-info';
                  elseif ($progressValue >= 25) $progressColor = 'bg-warning';

                  // Update progress sources for badges
                  $progressSources = [];
                  if ($progressSource == 'destination') $progressSources['destination'] = $actualProgress;
                  if ($progressSource == 'training') $progressSources['training'] = $actualProgress;
                  if ($competencyProfile && $storedProficiency != $progressValue) $progressSources['competency'] = $storedProficiency;
                @endphp

                <div class="progress" style="height: 20px;">
                  <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ $progressValue }}%">
                    {{ $progressValue }}%
                  </div>
                </div>

                {{-- Show exam score --}}
                @if(isset($p->exam_quiz_scores) && $p->exam_quiz_scores['exam_score'] > 0)
                  @php
                    $breakdown = \App\Models\ExamAttempt::getScoreBreakdown(Auth::user()->employee_id, $p->course_id);
                  @endphp
                  <small class="text-muted d-block mt-1"
                         data-bs-toggle="tooltip"
                         data-bs-placement="top"
                         title="Exam Score: {{ $breakdown['exam_score'] }}% = {{ $breakdown['combined_progress'] }}% progress">
                    <i class="bi bi-mortarboard me-1"></i>Exam: {{ $p->exam_quiz_scores['exam_score'] }}%
                    <i class="bi bi-info-circle ms-1" style="cursor: help;"></i>
                  </small>
                @endif

                {{-- Show progress from multiple sources --}}
                @if(!empty($progressSources))
                  <div class="mt-2">
                    @foreach($progressSources as $source => $sourceProgress)
                      @if($sourceProgress != $progressValue)
                        <small class="badge bg-light text-dark me-1"
                               data-bs-toggle="tooltip"
                               title="{{ ucfirst($source) }}: {{ $sourceProgress }}% progress">
                          @if($source == 'destination')
                            <i class="bi bi-geo-alt"></i>
                          @elseif($source == 'competency')
                            <i class="bi bi-award"></i>
                          @elseif($source == 'training')
                            <i class="bi bi-book"></i>
                          @elseif($source == 'manual')
                            <i class="bi bi-pencil"></i>
                          @endif
                          {{ ucfirst($source) }}: {{ $sourceProgress }}%
                        </small>
                      @endif
                    @endforeach
                  </div>
                @endif
              </td>
              <td>
                @php
                  // Determine status based on progress percentage - ALWAYS use progress over stored status
                  $progressValue = $p->progress_percentage ?? 0;

                  // Force status based on actual progress percentage
                  if ($progressValue >= 80) {
                    $displayStatus = 'Completed';
                    $statusClass = 'bg-success';
                  } elseif ($progressValue < 50 && $progressValue > 0) {
                    $displayStatus = 'Failed';
                    $statusClass = 'bg-danger';
                  }  elseif ($progressValue > 0) {
                    $displayStatus = 'Started';
                    $statusClass = 'bg-primary';
                  } else {
                    $displayStatus = 'Ready to Start';
                    $statusClass = 'bg-secondary';
                  }
                @endphp
                <span class="badge {{ $statusClass }}">{{ $displayStatus }}</span>
              </td>
              <td>
                @php
                  $lastUpdated = $p->last_updated;
                  $updateSource = 'Training Dashboard';

                  // Check for more recent updates from other sources
                  $employeeId = Auth::user()->employee_id;
                  $recentUpdates = [];

                  // Check destination knowledge updates
                  if(isset($p->course_id)) {
                    $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                      ->where('destination_name', 'LIKE', '%' . $p->training_title . '%')
                      ->first();
                    if($destinationRecord && $destinationRecord->updated_at) {
                      $recentUpdates['destination'] = $destinationRecord->updated_at;
                    }
                  }

                  // Check competency profile updates
                  if(isset($p->course_id)) {
                    $courseTitle = str_replace(' Training', '', $p->training_title);
                    $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($courseTitle) {
                      $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%');
                    })->where('employee_id', $employeeId)->first();

                    if($competencyProfile && $competencyProfile->assessment_date) {
                      $recentUpdates['competency'] = $competencyProfile->assessment_date;
                    }
                  }

                  // Find most recent update
                  $mostRecentUpdate = \Carbon\Carbon::parse($lastUpdated);
                  $mostRecentSource = $updateSource;

                  foreach($recentUpdates as $source => $updateTime) {
                    $updateCarbon = \Carbon\Carbon::parse($updateTime);
                    if($updateCarbon->gt($mostRecentUpdate)) {
                      $mostRecentUpdate = $updateCarbon;
                      $mostRecentSource = ucfirst($source);
                    }
                  }
                @endphp

                <div class="d-flex flex-column">
                  <span class="fw-semibold">{{ $mostRecentUpdate->format('Y-m-d H:i') }}</span>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $mostRecentUpdate->diffForHumans() }}
                    @if($mostRecentSource != 'Training Dashboard')
                      <br><i class="bi bi-arrow-repeat me-1"></i>via {{ $mostRecentSource }}
                    @endif
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
                      <div class="text-muted small">{{ $timeFormatted }}</div>
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
                  $progressValue = $p->progress_percentage ?? 0;
                  $isCompleted = $progressValue >= 100;
                @endphp

                <div class="d-flex gap-1 justify-content-center">
                  @if($isCompleted)
                    {{-- Show certificate download for completed trainings --}}
                    @php
                      $employeeId = Auth::user()->employee_id;
                      $certificate = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)
                          ->where('course_id', $p->course_id ?? 0)
                          ->first();
                    @endphp

                    @if($certificate)
                      <a href="{{ route('certificates.view', $certificate->id) }}" class="btn btn-success btn-sm" target="_blank">
                        <i class="bi bi-award"></i> View Certificate
                      </a>
                      <a href="{{ route('certificates.download', $certificate->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-download"></i> Download
                      </a>
                    @else
                      <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Completed
                      </span>
                    @endif
                  @elseif(isset($p->can_start_exam) && $p->can_start_exam)
                    @php
                      $employeeId = Auth::user()->employee_id;

                      // All courses in Employee Training Dashboard can now have exams
                      // Removed destination knowledge restriction

                      $examAttempts = \App\Models\ExamAttempt::where('employee_id', $employeeId)
                          ->where('course_id', $p->course_id)
                          ->where('type', 'exam')
                          ->count();
                    @endphp

                    {{-- Show exam button for all courses --}}
                    @if($examAttempts < 3)
                      <button class="btn btn-success btn-sm" onclick="startExam({{ $p->course_id }}, '{{ $displayTitle }}')">
                        <i class="bi bi-play-circle"></i> Start Exam
                        <small class="d-block">({{ 3 - $examAttempts }} left)</small>
                      </button>
                    @else
                      <button class="btn btn-secondary btn-sm" disabled>
                        <i class="bi bi-x-circle"></i> Exam
                        <small class="d-block">(Max attempts)</small>
                      </button>
                    @endif

                    {{-- View Details button next to exam button --}}
                    <button
                      class="btn btn-outline-primary btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#viewProgressModal"
                      data-id="{{ $p->progress_id }}"
                      data-title="{{ $displayTitle }}"
                      data-percent="{{ $p->progress_percentage }}"
                      data-status="{{ $displayStatus }}"
                      data-updated="{{ $p->last_updated }}"
                      data-remarks="{{ $p->remarks ?? 'No remarks' }}"
                      data-source="{{ $p->source ?? 'Not specified' }}"
                    >
                      <i class="bi bi-eye"></i> View Details
                    </button>

                    {{-- Delete button --}}
                    <button
                      class="btn btn-outline-danger btn-sm"
                      onclick="deleteProgress('{{ $p->progress_id }}', '{{ $displayTitle }}')"
                      title="Delete this training progress"
                    >
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  @else
                    <button
                      class="btn btn-outline-primary btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#viewProgressModal"
                      data-id="{{ $p->progress_id }}"
                      data-title="{{ $displayTitle }}"
                      data-percent="{{ $p->progress_percentage }}"
                      data-status="{{ $displayStatus }}"
                      data-updated="{{ $p->last_updated }}"
                      data-remarks="{{ $p->remarks ?? 'No remarks' }}"
                      data-source="{{ $p->source ?? 'Not specified' }}"
                    >
                      <i class="bi bi-eye"></i> View Details
                    </button>

                    {{-- Delete button --}}
                    <button
                      class="btn btn-outline-danger btn-sm"
                      onclick="deleteProgress('{{ $p->progress_id }}', '{{ $displayTitle }}')"
                      title="Delete this training progress"
                    >
                      <i class="bi bi-trash"></i> Delete
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
  <div class="modal-dialog modal-md modal-dialog-centered">
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
          <div class="col-6 mb-3">
            <label class="form-label fw-bold">Progress</label>
            <div id="viewProgressBar"></div>
          </div>
          <div class="col-6 mb-3">
            <label class="form-label fw-bold">Status</label>
            <p class="form-control-plaintext" id="viewStatus"></p>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">Last Updated</label>
            <p class="form-control-plaintext" id="viewUpdated"></p>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">Source</label>
            <p class="form-control-plaintext" id="viewSource"></p>
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

  // Update modal content
  document.getElementById('viewTitle').textContent = title;
  document.getElementById('viewStatus').innerHTML = `<span class="badge bg-info">${status}</span>`;
  document.getElementById('viewUpdated').textContent = updated;
  document.getElementById('viewRemarks').textContent = remarks;

  // Format source display
  let sourceDisplay = source;
  if (source === 'approved_request') {
    sourceDisplay = 'From Request';
  } else if (source === 'employee_training_dashboard') {
    sourceDisplay = 'Admin Assigned';
  } else if (source === 'Not specified') {
    sourceDisplay = 'Not specified';
  }
  document.getElementById('viewSource').textContent = sourceDisplay;

  // Create progress bar
  const progressValue = parseInt(percent) || 0;
  let progressColor = 'bg-primary';
  if (progressValue >= 75) progressColor = 'bg-success';
  else if (progressValue >= 50) progressColor = 'bg-info';
  else if (progressValue >= 25) progressColor = 'bg-warning';

  document.getElementById('viewProgressBar').innerHTML = `
    <div class="progress" style="height: 20px;">
      <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${progressValue}%">
        ${progressValue}%
      </div>
    </div>
  `;
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
</script>

<script>
// Start Exam Function
function startExam(courseId, trainingTitle) {
  if (confirm(`Start exam for "${trainingTitle}"?`)) {
    // Create exam modal or redirect to exam page
    window.location.href = `/employee/exam/start/${courseId}`;
  }
}


// Delete Progress Function
function deleteProgress(progressId, trainingTitle) {
  if (confirm(`Are you sure you want to delete the progress for "${trainingTitle}"? This action cannot be undone.`)) {
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/employee/my_trainings/${progressId}`;

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);

    // Add method spoofing for DELETE
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);

    // Submit the form
    document.body.appendChild(form);
    form.submit();
  }
}
</script>
