<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0"><i class="bi bi-chat-square-text me-2"></i>Post-Training Feedback</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFeedbackModal">
      <i class="bi bi-plus-circle me-1"></i> Submit Feedback
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Feedback ID</th>
            <th>Training Title</th>
            <th>Overall Rating</th>
            <th>Recommend</th>
            <th>Format</th>
            <th>Submitted Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $uniqueFeedback = collect($feedback)->unique('feedback_id');
          @endphp
          @forelse($uniqueFeedback as $f)
            <tr>
              <td><span class="badge bg-primary">{{ $f->feedback_id }}</span></td>
              <td>
                <strong>{{ $f->training_title }}</strong>
                @if($f->training_completion_date)
                  <br><small class="text-muted">Completed: {{ $f->training_completion_date->format('M d, Y') }}</small>
                @endif
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <span class="text-warning me-2">{{ str_repeat('★', $f->overall_rating) }}{{ str_repeat('☆', 5 - $f->overall_rating) }}</span>
                  <span class="badge bg-{{ $f->overall_rating >= 4 ? 'success' : ($f->overall_rating >= 3 ? 'warning' : 'danger') }}">{{ $f->overall_rating }}/5</span>
                </div>
              </td>
              <td>
                @if($f->recommend_training)
                  <span class="badge bg-success"><i class="bi bi-check-circle"></i> Yes</span>
                @else
                  <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> No</span>
                @endif
              </td>
              <td>
                @if($f->training_format)
                  <span class="badge bg-info">{{ $f->training_format }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>{{ $f->submitted_at->format('M d, Y') }}<br><small class="text-muted">{{ $f->submitted_at->format('h:i A') }}</small></td>
              <td class="text-center">
                <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#viewFeedbackModal" onclick="viewFeedback({{ $f->id }})" title="View Details">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editFeedbackModal" onclick="editFeedback({{ $f->id }})" title="Edit">
                  <i class="bi bi-pencil-square"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">
              <i class="bi bi-chat-square-text fs-1 text-muted d-block mb-2"></i>
              No training feedback submitted yet. Complete a training to provide feedback!
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Add Comprehensive Feedback --}}
<div class="modal fade" id="addFeedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="margin-top: 80px;">
    <div class="modal-content">
      <form action="{{ route('employee.training_feedback.store') }}" method="POST" id="feedbackForm">
        @csrf
        
        @if (isset($errors) && is_object($errors) && $errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-chat-square-text me-2"></i>Post-Training Feedback Submission</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
              <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-1"></i>Training Information</h6>

              <div class="mb-3">
                <label class="form-label fw-bold">Select Completed Training <span class="text-danger">*</span></label>
                <select name="course_id" id="courseSelect" class="form-select" required>
                  <option value="">Choose a completed training...</option>
                  @if(isset($completedTrainings) && $completedTrainings->count() > 0)
                    @foreach($completedTrainings as $training)
                      <option value="{{ $training->course_id ?? $training->id ?? 'manual_' . $loop->index }}" 
                              data-title="{{ $training->course_title ?? $training->training_title ?? 'Unknown Training' }}"
                              data-completion-date="{{ $training->completion_date ? (is_string($training->completion_date) ? $training->completion_date : $training->completion_date->format('Y-m-d')) : '' }}">
                        {{ $training->course_title ?? $training->training_title ?? 'Unknown Training' }} ({{ $training->progress ?? 100 }}%)
                      </option>
                    @endforeach
                  @else
                    <option value="" disabled>No completed trainings available for feedback</option>
                  @endif
                </select>
                <input type="hidden" name="training_title" id="trainingTitle" value="">
              </div>


              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Training Format</label>
                    <select name="training_format" class="form-select">
                      <option value="">Select format...</option>
                      <option value="Online">Online</option>
                      <option value="In-Person">In-Person</option>
                      <option value="Hybrid">Hybrid</option>
                      <option value="Self-Paced">Self-Paced</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Completion Date</label>
                    <input type="date" name="training_completion_date" class="form-control" value="{{ date('Y-m-d') }}">
                  </div>
                </div>
              </div>

              <h6 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-star me-1"></i>Rating & Recommendation</h6>

              <div class="mb-3">
                <label class="form-label fw-bold">Overall Rating <span class="text-danger">*</span></label>
                <div class="rating-container">
                  <div class="star-rating" data-rating="0">
                    <span class="star" data-value="1">☆</span>
                    <span class="star" data-value="2">☆</span>
                    <span class="star" data-value="3">☆</span>
                    <span class="star" data-value="4">☆</span>
                    <span class="star" data-value="5">☆</span>
                  </div>
                  <input type="hidden" name="overall_rating" id="overallRating" required>
                  <small class="form-text text-muted">Click stars to rate (1 = Poor, 5 = Excellent)</small>
                </div>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input type="hidden" name="recommend_training" value="0">
                  <input class="form-check-input" type="checkbox" name="recommend_training" id="recommendTraining" value="1" checked>
                  <label class="form-check-label fw-bold" for="recommendTraining">
                    I would recommend this training to others
                  </label>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
              <h6 class="fw-bold text-primary mb-3"><i class="bi bi-clipboard-check me-1"></i>Detailed Assessment</h6>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Content Quality</label>
                    <select name="content_quality" class="form-select">
                      <option value="">Rate...</option>
                      <option value="1">1 - Poor</option>
                      <option value="2">2 - Fair</option>
                      <option value="3">3 - Good</option>
                      <option value="4">4 - Very Good</option>
                      <option value="5">5 - Excellent</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Instructor Effectiveness</label>
                    <select name="instructor_effectiveness" class="form-select">
                      <option value="">Rate...</option>
                      <option value="1">1 - Poor</option>
                      <option value="2">2 - Fair</option>
                      <option value="3">3 - Good</option>
                      <option value="4">4 - Very Good</option>
                      <option value="5">5 - Excellent</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Material Relevance</label>
                    <select name="material_relevance" class="form-select">
                      <option value="">Rate...</option>
                      <option value="1">1 - Poor</option>
                      <option value="2">2 - Fair</option>
                      <option value="3">3 - Good</option>
                      <option value="4">4 - Very Good</option>
                      <option value="5">5 - Excellent</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Training Duration</label>
                    <select name="training_duration" class="form-select">
                      <option value="">Rate...</option>
                      <option value="1">1 - Too Short</option>
                      <option value="2">2 - Somewhat Short</option>
                      <option value="3">3 - Just Right</option>
                      <option value="4">4 - Somewhat Long</option>
                      <option value="5">5 - Too Long</option>
                    </select>
                  </div>
                </div>
              </div>

              <h6 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-chat-dots me-1"></i>Detailed Feedback</h6>

              <div class="mb-3">
                <label class="form-label fw-bold">What did you learn from this training?</label>
                <textarea name="what_learned" class="form-control" rows="3" placeholder="Describe the key skills, knowledge, or insights you gained..."></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">What was most valuable about this training?</label>
                <textarea name="most_valuable" class="form-control" rows="2" placeholder="Highlight the most beneficial aspects..."></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Suggestions for improvement</label>
                <textarea name="improvements" class="form-control" rows="2" placeholder="How could this training be enhanced?"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Additional topics you'd like to see</label>
                <textarea name="additional_topics" class="form-control" rows="2" placeholder="What related topics would be helpful?"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Additional Comments</label>
                <textarea name="comments" class="form-control" rows="2" placeholder="Any other feedback or comments..."></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button class="btn btn-primary" type="submit" id="submitFeedback">
            <i class="bi bi-send me-1"></i>Submit Feedback
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- View Feedback Details --}}
<div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Feedback Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewFeedbackContent">
        <!-- Content loaded via AJAX -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Edit Feedback --}}
<div class="modal fade" id="editFeedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form id="editFeedbackForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Feedback</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editFeedbackContent">
          <!-- Content loaded via AJAX -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button class="btn btn-warning" type="submit">
            <i class="bi bi-check-circle me-1"></i>Update Feedback
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.star-rating {
  font-size: 2rem;
  color: #ddd;
  cursor: pointer;
}

.star-rating .star {
  transition: color 0.2s;
}

.star-rating .star:hover,
.star-rating .star.active {
  color: #ffc107;
}

.rating-container {
  margin: 10px 0;
}

.feedback-stats {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
}

/* Admin Response Styling */
.bg-light-green {
  background-color: #d1ecf1 !important;
}

.bg-light-success {
  background-color: #d4edda !important;
}

.card.border-success {
  border-color: #28a745 !important;
}

.card-header.bg-light-green {
  background-color: #bee5eb !important;
  border-bottom: 1px solid #28a745;
}

.text-success {
  color: #28a745 !important;
}
</style>

<script>
// Star Rating Functionality
document.addEventListener('DOMContentLoaded', function() {
  const starRating = document.querySelector('.star-rating');
  const stars = starRating.querySelectorAll('.star');
  const ratingInput = document.getElementById('overallRating');

  stars.forEach((star, index) => {
    star.addEventListener('click', function() {
      const rating = index + 1;
      ratingInput.value = rating;

      // Update star display
      stars.forEach((s, i) => {
        if (i < rating) {
          s.textContent = '★';
          s.classList.add('active');
        } else {
          s.textContent = '☆';
          s.classList.remove('active');
        }
      });
    });

    star.addEventListener('mouseover', function() {
      const rating = index + 1;
      stars.forEach((s, i) => {
        if (i < rating) {
          s.textContent = '★';
        } else {
          s.textContent = '☆';
        }
      });
    });
  });

  starRating.addEventListener('mouseleave', function() {
    const currentRating = parseInt(ratingInput.value) || 0;
    stars.forEach((s, i) => {
      if (i < currentRating) {
        s.textContent = '★';
      } else {
        s.textContent = '☆';
      }
    });
  });
});

// Simple and Direct Course Selection Handler
function setupCourseSelectionHandler() {
  console.log('🔧 Setting up course selection handler...');
  
  // Use jQuery-style selector as backup
  const courseSelect = document.getElementById('courseSelect') || document.querySelector('#courseSelect');
  const trainingTitleInput = document.getElementById('trainingTitle') || document.querySelector('#trainingTitle');
  
  console.log('Course select element:', courseSelect);
  console.log('Training title input:', trainingTitleInput);
  
  if (!courseSelect) {
    console.error('❌ Course select element not found!');
    return;
  }
  
  if (!trainingTitleInput) {
    console.error('❌ Training title input element not found!');
    return;
  }
  
  // Remove any existing event listeners
  courseSelect.removeEventListener('change', handleCourseChange);
  
  // Add the event listener
  courseSelect.addEventListener('change', handleCourseChange);
  
  console.log('✅ Event listener added successfully');
}

// Separate function to handle course change
function handleCourseChange(event) {
  console.log('🎯 Course selection changed!');
  
  const selectElement = event.target;
  const selectedOption = selectElement.options[selectElement.selectedIndex];
  const trainingTitleInput = document.getElementById('trainingTitle');
  
  console.log('Selected option:', selectedOption);
  console.log('Selected option text:', selectedOption.textContent);
  
  if (!selectedOption || !selectedOption.value) {
    console.log('No valid option selected');
    if (trainingTitleInput) trainingTitleInput.value = '';
    return;
  }
  
  // Get training title - try multiple methods
  let trainingTitle = selectedOption.getAttribute('data-title');
  console.log('Training title from data-title:', trainingTitle);
  
  // Fallback 1: Extract from option text (remove percentage part)
  if (!trainingTitle) {
    const optionText = selectedOption.textContent.trim();
    console.log('Option text:', optionText);
    
    if (optionText.includes('(') && optionText.includes('%')) {
      trainingTitle = optionText.split('(')[0].trim();
      console.log('Extracted from option text:', trainingTitle);
    } else {
      trainingTitle = optionText;
    }
  }
  
  // Fallback 2: Use the visible text as-is (clean it up)
  if (!trainingTitle) {
    let fullText = selectedOption.textContent.trim();
    // Remove percentage part if exists
    if (fullText.includes('(') && fullText.includes('%')) {
      trainingTitle = fullText.split('(')[0].trim();
    } else {
      trainingTitle = fullText;
    }
    console.log('Using cleaned option text:', trainingTitle);
  }
  
  // Set the training title
  if (trainingTitle && trainingTitleInput) {
    trainingTitleInput.value = trainingTitle;
    console.log('✅ Training title set to:', trainingTitle);
    
    // Visual feedback
    trainingTitleInput.style.backgroundColor = '#d4edda';
    trainingTitleInput.style.border = '2px solid #28a745';
    
    setTimeout(() => {
      trainingTitleInput.style.backgroundColor = '';
      trainingTitleInput.style.border = '';
    }, 2000);
    
    // Also set completion date if available
    const completionDate = selectedOption.getAttribute('data-completion-date');
    if (completionDate) {
      const completionDateInput = document.querySelector('input[name="training_completion_date"]');
      if (completionDateInput) {
        completionDateInput.value = completionDate;
        console.log('✅ Completion date set to:', completionDate);
      }
    }
  } else {
    console.warn('❌ Could not set training title');
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM Content Loaded - Setting up course selection handler');
  
  // Debug: Check if completedTrainings data is available
  const courseSelect = document.getElementById('courseSelect');
  if (courseSelect) {
    console.log('Course select element found:', courseSelect);
    console.log('Total options:', courseSelect.options.length);
    console.log('Available training options:');
    for (let i = 0; i < courseSelect.options.length; i++) {
      const option = courseSelect.options[i];
      console.log(`Option ${i}:`, {
        value: option.value,
        text: option.textContent,
        dataTitle: option.getAttribute('data-title'),
        dataCompletionDate: option.getAttribute('data-completion-date')
      });
    }
  } else {
    console.error('❌ Course select element not found!');
  }
  
  setupCourseSelectionHandler();
});

// Also try to initialize immediately in case DOMContentLoaded already fired
setupCourseSelectionHandler();


// View Feedback Function
function viewFeedback(feedbackId) {
  console.log('Loading feedback for viewing:', feedbackId);
  
  // Show loading state
  document.getElementById('viewFeedbackContent').innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading feedback data...</div>';
  
  fetch(`{{ url('employee/training-feedback') }}/${feedbackId}`, {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Feedback data loaded for viewing:', data);
      const content = `
        <div class="row">
          <div class="col-md-6">
            <h6 class="fw-bold text-primary">Training Information</h6>
            <table class="table table-borderless">
              <tr><td><strong>Training:</strong></td><td>${data.training_title}</td></tr>
              <tr><td><strong>Format:</strong></td><td>${data.training_format || 'N/A'}</td></tr>
              <tr><td><strong>Completed:</strong></td><td>${data.training_completion_date || 'N/A'}</td></tr>
              <tr><td><strong>Submitted:</strong></td><td>${new Date(data.submitted_at).toLocaleDateString()}</td></tr>
            </table>

            <h6 class="fw-bold text-primary mt-4">Ratings</h6>
            <table class="table table-borderless">
              <tr><td><strong>Overall:</strong></td><td>${'★'.repeat(data.overall_rating)}${'☆'.repeat(5-data.overall_rating)} (${data.overall_rating}/5)</td></tr>
              <tr><td><strong>Content Quality:</strong></td><td>${data.content_quality ? data.content_quality + '/5' : 'N/A'}</td></tr>
              <tr><td><strong>Instructor:</strong></td><td>${data.instructor_effectiveness ? data.instructor_effectiveness + '/5' : 'N/A'}</td></tr>
              <tr><td><strong>Material Relevance:</strong></td><td>${data.material_relevance ? data.material_relevance + '/5' : 'N/A'}</td></tr>
              <tr><td><strong>Duration:</strong></td><td>${data.training_duration ? data.training_duration + '/5' : 'N/A'}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold text-primary">Detailed Feedback</h6>
            <div class="mb-3">
              <strong>What you learned:</strong>
              <p class="text-muted">${data.what_learned || 'No response provided'}</p>
            </div>
            <div class="mb-3">
              <strong>Most valuable aspect:</strong>
              <p class="text-muted">${data.most_valuable || 'No response provided'}</p>
            </div>
            <div class="mb-3">
              <strong>Suggestions for improvement:</strong>
              <p class="text-muted">${data.improvements || 'No response provided'}</p>
            </div>
            <div class="mb-3">
              <strong>Additional topics:</strong>
              <p class="text-muted">${data.additional_topics || 'No response provided'}</p>
            </div>
            <div class="mb-3">
              <strong>Additional comments:</strong>
              <p class="text-muted">${data.comments || 'No response provided'}</p>
            </div>
            <div class="mb-3">
              <strong>Recommend to others:</strong>
              <span class="badge bg-${data.recommend_training ? 'success' : 'secondary'}">
                ${data.recommend_training ? 'Yes' : 'No'}
              </span>
            </div>
          </div>
        </div>
        
        ${data.admin_response || data.action_taken ? `
        <div class="row mt-4">
          <div class="col-12">
            <div class="card border-success">
              <div class="card-header bg-light-green text-dark">
                <h6 class="fw-bold mb-0 text-success">
                  <i class="bi bi-shield-check me-2"></i>Admin Response
                </h6>
              </div>
              <div class="card-body bg-light-success">
                ${data.admin_response ? `
                <div class="mb-3">
                  <p class="mb-2 fw-bold text-dark">${data.admin_response}</p>
                </div>
                ` : ''}
                ${data.action_taken ? `
                <div class="mb-2">
                  <strong class="text-dark">Action:</strong> 
                  <span class="badge bg-success">${data.action_taken}</span>
                </div>
                ` : ''}
                ${data.response_date ? `
                <div class="mb-0">
                  <small class="text-muted">
                    <i class="bi bi-calendar-check me-1"></i>
                    Responded on: ${new Date(data.response_date).toLocaleDateString()}
                  </small>
                </div>
                ` : ''}
              </div>
            </div>
          </div>
        </div>
        ` : ''}
      `;
      document.getElementById('viewFeedbackContent').innerHTML = content;
    })
    .catch(error => {
      console.error('Error loading feedback for viewing:', error);
      document.getElementById('viewFeedbackContent').innerHTML = `
        <div class="alert alert-danger">
          <h6>Error loading feedback details</h6>
          <p>Details: ${error.message}</p>
          <small>Please try again or contact support if the issue persists.</small>
        </div>
      `;
    });
}

// Edit Feedback Function
function editFeedback(feedbackId) {
  console.log('Loading feedback for editing:', feedbackId);
  
  // Show loading state
  document.getElementById('editFeedbackContent').innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading feedback data...</div>';
  
  fetch(`{{ url('employee/training-feedback') }}/${feedbackId}`, {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Feedback data loaded:', data);
      
      // Set form action for update
      const editForm = document.getElementById('editFeedbackForm');
      if (editForm) {
        editForm.action = `{{ url('employee/training-feedback') }}/${feedbackId}`;
      }

      // Populate edit form with current data
      const editContent = `
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label fw-bold">Training Title</label>
              <input type="text" name="training_title" class="form-control" value="${data.training_title || ''}" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Overall Rating <span class="text-danger">*</span></label>
              <select name="overall_rating" class="form-select" required>
                <option value="">Select rating...</option>
                <option value="1" ${data.overall_rating == 1 ? 'selected' : ''}>1 - Poor</option>
                <option value="2" ${data.overall_rating == 2 ? 'selected' : ''}>2 - Fair</option>
                <option value="3" ${data.overall_rating == 3 ? 'selected' : ''}>3 - Good</option>
                <option value="4" ${data.overall_rating == 4 ? 'selected' : ''}>4 - Very Good</option>
                <option value="5" ${data.overall_rating == 5 ? 'selected' : ''}>5 - Excellent</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Training Format</label>
              <select name="training_format" class="form-select">
                <option value="">Select format...</option>
                <option value="Online" ${data.training_format == 'Online' ? 'selected' : ''}>Online</option>
                <option value="In-Person" ${data.training_format == 'In-Person' ? 'selected' : ''}>In-Person</option>
                <option value="Hybrid" ${data.training_format == 'Hybrid' ? 'selected' : ''}>Hybrid</option>
                <option value="Self-Paced" ${data.training_format == 'Self-Paced' ? 'selected' : ''}>Self-Paced</option>
              </select>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input type="hidden" name="recommend_training" value="0">
                <input class="form-check-input" type="checkbox" name="recommend_training" value="1" ${data.recommend_training ? 'checked' : ''}>
                <label class="form-check-label fw-bold">
                  I would recommend this training to others
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label fw-bold">What did you learn?</label>
              <textarea name="what_learned" class="form-control" rows="3">${data.what_learned || ''}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Most valuable aspect</label>
              <textarea name="most_valuable" class="form-control" rows="2">${data.most_valuable || ''}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Suggestions for improvement</label>
              <textarea name="improvements" class="form-control" rows="2">${data.improvements || ''}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Additional Comments</label>
              <textarea name="comments" class="form-control" rows="2">${data.comments || ''}</textarea>
            </div>
          </div>
        </div>
      `;
      document.getElementById('editFeedbackContent').innerHTML = editContent;
    })
    .catch(error => {
      console.error('Error loading feedback:', error);
      document.getElementById('editFeedbackContent').innerHTML = `
        <div class="alert alert-danger">
          <h6>Error loading feedback for editing</h6>
          <p>Details: ${error.message}</p>
          <small>Please try again or contact support if the issue persists.</small>
        </div>
      `;
    });
}

// Enhanced Form Validation and Auto-completion
document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
  console.log('🚀 Form submission started');
  
  try {
    // Get form elements
    const rating = document.getElementById('overallRating');
    const courseSelect = document.getElementById('courseSelect');
    const trainingTitleInput = document.getElementById('trainingTitle');
    const submitButton = document.getElementById('submitFeedback');
    
    // Show loading state
    if (submitButton) {
      submitButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Submitting...';
      submitButton.disabled = true;
    }
    
    // Ensure rating is set
    if (rating && !rating.value) {
      rating.value = 5;
      console.log('✅ Set default rating: 5');
    }
    
    // Ensure course is selected
    if (courseSelect && (!courseSelect.value || courseSelect.value === '')) {
      if (courseSelect.options.length > 1) {
        courseSelect.selectedIndex = 1;
        console.log('✅ Auto-selected first course');
        
        // Set training title
        const selectedOption = courseSelect.selectedOptions[0];
        if (selectedOption && trainingTitleInput) {
          let title = selectedOption.getAttribute('data-title') || selectedOption.textContent.trim();
          if (title.includes('(') && title.includes('%')) {
            title = title.split('(')[0].trim();
          }
          trainingTitleInput.value = title;
          console.log('✅ Set training title:', title);
        }
      }
    }
    
    // Ensure recommend_training has proper value
    const recommendCheckbox = document.getElementById('recommendTraining');
    if (recommendCheckbox) {
      console.log('✅ Recommend training checkbox value:', recommendCheckbox.checked ? '1' : '0');
    }
    
    // Log all form data for debugging
    const formData = new FormData(this);
    console.log('📋 Final form data being submitted:');
    for (let [key, value] of formData.entries()) {
      console.log(`  ${key}: ${value}`);
    }
    
    console.log('✅ Form validation complete, submitting to server...');
    
  } catch (error) {
    console.error('❌ Form validation error:', error);
    // Still allow submission even if JavaScript fails
  }
  
  // Allow natural form submission
  return true;
});

// Simple DOM ready handler
document.addEventListener('DOMContentLoaded', function() {
  console.log('✅ Feedback form initialized');
  
  // Reset button state if needed
  const submitButton = document.getElementById('submitFeedback');
  if (submitButton) {
    submitButton.innerHTML = '<i class="bi bi-send me-1"></i>Submit Feedback';
    submitButton.disabled = false;
  }
});


// Remove modal backdrops
function removeAllModalBackdrops() {
  document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
    backdrop.remove();
  });
}

window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);
</script>
