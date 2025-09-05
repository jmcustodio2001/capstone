<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">Requested Trainings</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTrainingRequestModal">
      <i class="bi bi-plus-circle me-1"></i> Request Training
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Request ID</th>
            <th>Training Title</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Requested Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $uniqueRequests = collect($trainingRequests)->unique('request_id');
          @endphp
          @forelse($uniqueRequests as $r)
            <tr>
              <td>{{ $r->request_id }}</td>
              <td>{{ $r->training_title }}</td>
              <td>{{ $r->reason }}</td>
              <td>
                <span class="badge {{ $r->status == 'Approved' ? 'bg-success' : ($r->status == 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                  {{ $r->status }}
                </span>
              </td>
              <td>{{ $r->requested_date }}</td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center flex-wrap">
                  @if(isset($r->course_id))
                    @if($r->status == 'Approved')
                      <a href="{{ route('employee.exam.start', $r->course_id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-clipboard-check me-1"></i>Take Exam
                      </a>
                    @else
                      <button class="btn btn-secondary btn-sm" disabled title="Exam available after approval">
                        <i class="fas fa-clipboard-check me-1"></i>Take Exam
                      </button>
                    @endif

                    {{-- Reviewer is always available --}}
                    <button class="btn btn-success btn-sm" onclick="openReviewer('{{ $r->training_title }}', '{{ $r->course_id }}')">
                      <i class="fas fa-book-open me-1"></i>Reviewer
                    </button>
                  @endif

                  {{-- Delete button - always show for all requests --}}
                  <button class="btn btn-danger btn-sm"
                          onclick="confirmDelete('{{ $r->request_id }}', '{{ $r->training_title }}')"
                          title="Delete Request">
                    <i class="fas fa-trash me-1"></i>Delete
                  </button>
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

<script>
// Load courses when modal opens - show only assigned upcoming trainings
document.getElementById('addTrainingRequestModal')?.addEventListener('show.bs.modal', function () {
  const courseSelect = document.getElementById('courseSelect');

  // Clear existing options
  courseSelect.innerHTML = '<option value="">Loading courses...</option>';

  // Get upcoming trainings assigned to this employee
  const upcomingTrainings = @json($upcomingTrainings ?? []);

  courseSelect.innerHTML = '<option value="">Choose a course...</option>';

  // Add assigned upcoming trainings as course options
  if (upcomingTrainings.length > 0) {
    const assignedGroup = document.createElement('optgroup');
    assignedGroup.label = 'Your Assigned Trainings';

    upcomingTrainings.forEach(training => {
      const option = document.createElement('option');
      // Use training title as both value and display text since these are assigned trainings
      option.value = training.training_title;
      option.textContent = training.training_title;
      option.setAttribute('data-description', `Assigned by: ${training.assigned_by || 'System'} | Source: ${training.source || 'Unknown'}`);
      option.setAttribute('data-training-id', training.upcoming_id || '');
      option.setAttribute('data-source', training.source || '');
      assignedGroup.appendChild(option);
    });

    courseSelect.appendChild(assignedGroup);
  }

  // Fallback: Add general courses if no upcoming trainings
  const availableCourses = @json($availableCourses ?? []);
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

    if (allCoursesGroup.children.length > 0) {
      courseSelect.appendChild(allCoursesGroup);
    }
  }

  // If no courses available
  if (upcomingTrainings.length === 0 && availableCourses.length === 0) {
    const option = document.createElement('option');
    option.value = '';
    option.textContent = 'No courses available';
    option.disabled = true;
    courseSelect.appendChild(option);
  }

  // Attach course selection handler after options are populated
  courseSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const trainingTitle = document.getElementById('trainingTitle');
    const courseDescription = document.getElementById('courseDescription');

    if (selectedOption.value) {
      // Remove "(Recommended)" suffix from training title if present
      let titleText = selectedOption.textContent;
      titleText = titleText.replace(' (Recommended)', '');
      trainingTitle.value = titleText;
      courseDescription.value = selectedOption.getAttribute('data-description') || '';

      const competency = selectedOption.getAttribute('data-competency');
      if (competency) {
        courseDescription.value += `\n\nRecommended for: ${competency} competency development`;
      }
    } else {
      trainingTitle.value = '';
      courseDescription.value = '';
    }
  });
});

document.getElementById('editTrainingRequestModal')?.addEventListener('show.bs.modal', function (e) {
  const b = e.relatedTarget, f = document.getElementById('editTrainingRequestForm');
  const id = b.getAttribute('data-id');
  f.action = "{{ url('employee/my-trainings') }}/" + id;

  f.querySelector('[name="training_title"]').value = b.getAttribute('data-title');
  f.querySelector('[name="reason"]').value = b.getAttribute('data-reason');
  f.querySelector('[name="status"]').value = b.getAttribute('data-status');
  f.querySelector('[name="requested_date"]').value = b.getAttribute('data-date');
});
// Robust handler to always set current_level on course selection
document.addEventListener('DOMContentLoaded', function() {
  var courseSelect = document.getElementById('courseSelect');
  var currentLevelInput = document.getElementById('currentLevelInput');
  if (courseSelect && currentLevelInput) {
    courseSelect.addEventListener('change', function() {
      var selectedOption = this.options[this.selectedIndex];
      var currentLevel = selectedOption.getAttribute('data-current-level');
      if (currentLevel !== null) {
        currentLevelInput.value = currentLevel;
      } else {
        currentLevelInput.value = 0;
      }
    });
  }
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

  // Optimized Dynamic Reviewer function with caching
  function openReviewer(trainingTitle, courseId) {
    // Check cache first
    const cacheKey = `reviewer_${courseId}`;
    const cachedData = localStorage.getItem(cacheKey);
    const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);
    const cacheExpiry = 30 * 60 * 1000; // 30 minutes

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
            ${getDefaultContent()}
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

    // Check if we have valid cached data
    if (cachedData && cacheTimestamp && (Date.now() - parseInt(cacheTimestamp)) < cacheExpiry) {
      try {
        const data = JSON.parse(cachedData);
        displayReviewerContent(data.study_materials, data.total_questions);
        return;
      } catch (e) {
        console.log('Cache data corrupted, fetching fresh data');
      }
    }

    // Show loading overlay on default content
    const contentDiv = document.getElementById('reviewerContent');
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75';
    loadingOverlay.innerHTML = '<div class="spinner-border text-success" role="status"></div>';
    contentDiv.style.position = 'relative';
    contentDiv.appendChild(loadingOverlay);

    // Fetch dynamic content with timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

    fetch(`/employee/training/reviewer/${courseId}`, {
      method: 'GET',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      signal: controller.signal
    })
    .then(response => {
      clearTimeout(timeoutId);
      return response.json();
    })
    .then(data => {
      loadingOverlay.remove();
      if (data.success) {
        // Cache the data
        localStorage.setItem(cacheKey, JSON.stringify(data));
        localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());

        displayReviewerContent(data.study_materials, data.total_questions);
      } else {
        console.log('API returned error, keeping default content');
      }
    })
    .catch(error => {
      clearTimeout(timeoutId);
      loadingOverlay.remove();
      displayErrorContent(error.message);
    });

    // Remove modal from DOM when hidden
    reviewerModal.addEventListener('hidden.bs.modal', () => {
      reviewerModal.remove();
    });
  }

  // Get default content to show immediately
  function getDefaultContent() {
    return `
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

  // Display dynamic reviewer content
  function displayReviewerContent(studyMaterials, totalQuestions) {
    const contentDiv = document.getElementById('reviewerContent');

    let keyLearningPointsHtml = '';
    if (studyMaterials.key_learning_points && studyMaterials.key_learning_points.length > 0) {
      studyMaterials.key_learning_points.forEach(point => {
        keyLearningPointsHtml += `<li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>${point}</li>`;
      });
    } else {
      keyLearningPointsHtml = '<li class="mb-2"><i class="fas fa-info-circle text-info me-2"></i>Core concepts and principles</li>';
    }

    let studyTipsHtml = '';
    if (studyMaterials.study_tips && studyMaterials.study_tips.length > 0) {
      studyMaterials.study_tips.forEach(tip => {
        studyTipsHtml += `<li>${tip}</li>`;
      });
    } else {
      studyTipsHtml = '<li>Review all materials before taking the exam</li><li>Practice with sample questions</li>';
    }

    let sampleQuestionsHtml = '';
    if (studyMaterials.sample_questions && studyMaterials.sample_questions.length > 0) {
      studyMaterials.sample_questions.forEach((q, index) => {
        sampleQuestionsHtml += `
          <div class="card mb-2">
            <div class="card-body">
              <h6 class="card-title">Sample Question ${index + 1}</h6>
              <p class="card-text">${q.question}</p>
              ${q.explanation ? `<small class="text-muted"><strong>Key Point:</strong> ${q.explanation}</small>` : ''}
            </div>
          </div>
        `;
      });
    }

    contentDiv.innerHTML = `
      <div class="alert alert-success mb-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Study materials generated from ${totalQuestions} exam questions</strong>
      </div>

      <div class="row">
        <div class="col-md-6">
          <h6 class="text-success"><i class="fas fa-lightbulb me-2"></i>Key Learning Points</h6>
          <ul class="list-unstyled">
            ${keyLearningPointsHtml}
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
              <i class="fas fa-question-circle text-warning me-2"></i> (${totalQuestions} available)
            </div>
          </div>
        </div>
      </div>

      ${sampleQuestionsHtml ? `
        <hr>
        <h6 class="text-primary"><i class="fas fa-lightbulb me-2"></i>Sample Questions Preview</h6>
        ${sampleQuestionsHtml}
      ` : ''}

      <hr>
      <div class="alert alert-info">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Study Tips</h6>
        <ul class="mb-0">
          ${studyTipsHtml}
        </ul>
      </div>
    `;
  }

  // Display error content
  function displayErrorContent(error) {
    const contentDiv = document.getElementById('reviewerContent');
    contentDiv.innerHTML = `
      <div class="alert alert-warning">
        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Unable to Load Dynamic Content</h6>
        <p>Error: ${error}</p>
        <p>Showing default study materials instead.</p>
      </div>
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-success"><i class="fas fa-lightbulb me-2"></i>Key Learning Points</h6>
          <ul class="list-unstyled">
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Core concepts and principles</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Best practices and methodologies</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Practical applications</li>
          </ul>
        </div>
        <div class="col-md-6">
          <h6 class="text-info"><i class="fas fa-clipboard-list me-2"></i>Study Materials</h6>
          <div class="list-group">
            <div class="list-group-item"><i class="fas fa-file-pdf text-danger me-2"></i></div>
            <div class="list-group-item"><i class="fas fa-video text-primary me-2"></i></div>
          </div>
        </div>
      </div>
    `;
  }

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

        // Reload page after short delay
        setTimeout(() => {
          window.location.reload();
        }, 1500);
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
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toastContainer';
      toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
      toastContainer.style.zIndex = '9999';
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast_' + Date.now();
    const toastHtml = `
      <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
          <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
          <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    // Show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();

    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
      toastElement.remove();
    });
  }

  // Mark training as reviewed
  function markAsReviewed(courseId, trainingTitle) {
    const button = document.getElementById('markReviewedBtn');
    const originalText = button.innerHTML;

    // Show loading state
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
        button.innerHTML = '<i class="fas fa-check me-1"></i>Reviewed!';
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-success');

        // Show success toast
        showToast('success', data.message);

        // Close modal after 2 seconds
        setTimeout(() => {
          const modal = bootstrap.Modal.getInstance(document.getElementById('reviewerModal'));
          modal.hide();
        }, 2000);
      } else {
        button.innerHTML = originalText;
        button.disabled = false;
        showToast('error', data.error || 'Failed to mark as reviewed');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      button.innerHTML = originalText;
      button.disabled = false;
      showToast('error', 'Network error occurred');
    });
  }
</script>
