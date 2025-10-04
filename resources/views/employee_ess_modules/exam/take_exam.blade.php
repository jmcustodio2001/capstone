@extends('layouts.employee')

@section('title', 'Take ' . ucfirst($attempt->type) . ' - ' . $course->course_title)

@push('styles')
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    body {
        padding-top: 3.5rem !important;
    }
    .navbar {
        position: fixed !important;
        top: 0 !important;
        width: 100% !important;
        z-index: 1030 !important;
    }
    .container-fluid {
        margin-top: 0 !important;
        padding-top: 10px !important;
    }
    .question-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .question-card.unanswered {
        border: 3px solid #dc3545;
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
    }
    .question-header {
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
        transition: all 0.3s ease;
    }
    .question-header.unanswered {
        background-color: #f8d7da;
        color: #721c24;
    }
    .question-body {
        padding: 20px;
    }
    .option-item {
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #e9ecef;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .option-item:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }
    .option-item.selected {
        background-color: #e3f2fd;
        border-color: #007bff;
    }
    .form-check-input {
        margin-top: 0.25rem;
    }
    
    /* SweetAlert2 Custom Styling */
    .swal2-popup {
        border-radius: 15px;
        font-family: inherit;
    }
    .swal2-title {
        color: #2c3e50;
        font-weight: 600;
    }
    .swal2-content {
        color: #34495e;
    }
    .swal2-confirm {
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 500;
    }
    .swal2-cancel {
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    /* Progress indicator styling */
    .exam-progress {
        margin: 15px 0;
    }
    .progress-text {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="margin-top: 10px; padding-top: 10px;">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-{{ $attempt->type == 'exam' ? 'clipboard-check' : 'question-circle' }} me-2"></i>
                            {{ ucfirst($attempt->type) }}: {{ $course->course_title }}
                        </h4>
                        <div class="text-end">
                            <div class="badge bg-light text-dark">
                                Attempt {{ $attempt->attempt_number }} of 3
                            </div>
                            <div class="small mt-1">
                                Remaining: {{ $remainingAttempts }} attempts
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1"><i class="bi bi-info-circle me-2"></i>{{ ucfirst($attempt->type) }} Instructions</h6>
                                <ul class="mb-0 small">
                                    <li>{{ count($questions) }} questions total</li>
                                    <li>{{ $attempt->type == 'exam' ? '80%' : '60%' }} passing grade required</li>
                                    <li>Maximum 3 attempts allowed</li>
                                    <li>Select the best answer for each question</li>
                                </ul>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="badge bg-success fs-6">{{ count($questions) }} Questions</div>
                            </div>
                        </div>
                    </div>

                    <form id="examForm" onsubmit="return false;">
                        <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
                        <input type="hidden" name="course_id" value="{{ $course->id }}">

                        @foreach($questions as $index => $question)
                        <div class="question-card">
                            <div class="question-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-primary">
                                        Question {{ $index + 1 }} of {{ count($questions) }}
                                    </h6>
                                    <span class="badge bg-secondary question-status" id="status_{{ $question->id }}">Required</span>
                                </div>
                            </div>
                            <div class="question-body">
                                <h5 class="mb-4">{{ $question->question }}</h5>

                                @php
                                    $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                                    $letterIndex = 0;
                                @endphp

                                @foreach($question->options as $optionKey => $optionValue)
                                <div class="option-item" onclick="selectOption('{{ $question->id }}', '{{ $optionKey }}')">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="answers[{{ $question->id }}]"
                                            id="question_{{ $question->id }}_option_{{ $optionKey }}"
                                            value="{{ $optionKey }}"
                                            required
                                        >
                                        <label class="form-check-label w-100" for="question_{{ $question->id }}_option_{{ $optionKey }}">
                                            <strong class="text-primary">{{ $letters[$letterIndex] }}.</strong>
                                            <span class="ms-2">{{ $optionValue }}</span>
                                        </label>
                                    </div>
                                </div>
                                @php $letterIndex++; @endphp
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="text-center mt-5">
                            <button type="button" class="btn btn-outline-secondary btn-lg me-3" onclick="cancelExamWithConfirmation()">
                                <i class="bi bi-arrow-left me-2"></i> Cancel {{ ucfirst($attempt->type) }}
                            </button>
                            <button type="button" class="btn btn-primary btn-lg" onclick="submitExamWithConfirmation(event)">
                                <i class="bi bi-check-circle me-2"></i> Submit {{ ucfirst($attempt->type) }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Set total questions count for JavaScript access
window.totalQuestions = {{ count($questions) }};

// Session-free exam submission - NO CSRF TOKENS NEEDED
console.log('Exam interface loaded - Session-free mode activated');

// Function to handle option selection with visual feedback
function selectOption(questionId, optionKey) {
    console.log(`selectOption called: Question ${questionId}, Option ${optionKey}`);

    const radio = document.getElementById(`question_${questionId}_option_${optionKey}`);
    if (radio) {
        radio.checked = true;

        // Trigger change event for visual feedback
        radio.dispatchEvent(new Event('change'));

        console.log(`✓ Successfully selected: Question ${questionId} = ${optionKey}`);
        console.log('Radio element:', radio);
        console.log('Radio checked:', radio.checked);
        console.log('Radio name:', radio.name);
        console.log('Radio value:', radio.value);
    } else {
        console.error(`✗ Radio element not found: question_${questionId}_option_${optionKey}`);
    }
}

// Function to update question status badge
function updateQuestionStatus(questionId) {
    const statusBadge = document.getElementById(`status_${questionId}`);
    if (statusBadge) {
        statusBadge.textContent = 'Done';
        statusBadge.classList.remove('bg-secondary');
        statusBadge.classList.add('bg-success');
    }
}

// Enhanced SweetAlert submission with confirmation
function submitExamWithConfirmation(event) {
    event.preventDefault();

    const form = document.getElementById('examForm');
    const questions = window.totalQuestions || 0;
    const answered = form.querySelectorAll('input[type="radio"]:checked').length;
    const examType = '{{ $attempt->type }}';

    // Validate if all questions are answered
    if (answered < questions) {
        showIncompleteExamWarning(answered, questions);
        return false;
    }

    // Show confirmation dialog with SweetAlert
    Swal.fire({
        title: `Submit ${examType.charAt(0).toUpperCase() + examType.slice(1)}?`,
        html: `
            <div class="text-start">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Submission Summary:</strong>
                </div>
                <ul class="list-unstyled mb-3">
                    <li><i class="bi bi-check-circle text-success me-2"></i>Questions answered: <strong>${answered} / ${questions}</strong></li>
                    <li><i class="bi bi-clock text-warning me-2"></i>Attempt: <strong>{{ $attempt->attempt_number }} of 3</strong></li>
                    <li><i class="bi bi-target text-primary me-2"></i>Passing grade: <strong>${examType === 'exam' ? '80%' : '60%'}</strong></li>
                </ul>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> You cannot change your answers after submission.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i class="bi bi-check-circle me-2"></i>Submit ${examType.charAt(0).toUpperCase() + examType.slice(1)}`,
        cancelButtonText: '<i class="bi bi-arrow-left me-2"></i>Review Answers',
        width: '600px',
        customClass: {
            popup: 'text-start'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitExamWithProgress();
        }
    });
}

// Show incomplete exam warning with SweetAlert
function showIncompleteExamWarning(answered, questions) {
    const unanswered = questions - answered;
    
    Swal.fire({
        title: 'Incomplete Exam',
        html: `
            <div class="text-start">
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Please answer all questions before submitting.</strong>
                </div>
                <div class="exam-progress">
                    <div class="progress-text">Progress: ${answered} / ${questions} questions answered</div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: ${(answered/questions)*100}%" 
                             aria-valuenow="${answered}" aria-valuemin="0" aria-valuemax="${questions}">
                            ${Math.round((answered/questions)*100)}%
                        </div>
                    </div>
                </div>
                <p class="mb-0">
                    <i class="bi bi-arrow-down-circle text-primary me-2"></i>
                    <strong>${unanswered} question${unanswered > 1 ? 's' : ''} remaining</strong>
                </p>
            </div>
        `,
        icon: 'warning',
        confirmButtonColor: '#007bff',
        confirmButtonText: '<i class="bi bi-search me-2"></i>Find Unanswered Questions',
        width: '500px'
    }).then(() => {
        highlightUnansweredQuestions();
    });
}

// Highlight unanswered questions and scroll to first
function highlightUnansweredQuestions() {
    const questionCards = document.querySelectorAll('.question-card');
    let scrolled = false;

    questionCards.forEach(card => {
        const questionInputs = card.querySelectorAll('input[type="radio"]');
        const hasAnswer = Array.from(questionInputs).some(input => input.checked);

        if (!hasAnswer) {
            // Apply warning styles with animation
            card.classList.add('unanswered');
            const header = card.querySelector('.question-header');
            if (header) {
                header.classList.add('unanswered');
            }

            // Scroll to first unanswered question only
            if (!scrolled) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                scrolled = true;
            }
        } else {
            // Reset styles if answered
            card.classList.remove('unanswered');
            const header = card.querySelector('.question-header');
            if (header) {
                header.classList.remove('unanswered');
            }
        }
    });
}

// Submit exam with progress tracking
function submitExamWithProgress() {
    const examType = '{{ $attempt->type }}';
    
    // Show progress dialog
    Swal.fire({
        title: `Submitting ${examType.charAt(0).toUpperCase() + examType.slice(1)}...`,
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-2">Processing your answers...</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="submitProgress"></div>
                </div>
                <small class="text-muted mt-2 d-block">Please wait, do not close this window.</small>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        width: '400px'
    });

    // Animate progress bar
    let progress = 0;
    const progressBar = document.getElementById('submitProgress');
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
    }, 200);

    // Disable form elements
    const form = document.getElementById('examForm');
    form.querySelectorAll('input, button').forEach(input => input.disabled = true);

    // Remove beforeunload warning before submitting
    window.removeEventListener('beforeunload', beforeUnloadHandler);

    // Submit exam using session-free AJAX
    submitExamSessionFree(progressInterval);
}

// Session-free submission function - NEVER EXPIRES
function submitExamSessionFree(progressInterval) {
    const form = document.getElementById('examForm');
    const examType = '{{ $attempt->type }}';

    // Collect answers in proper format - ENHANCED DEBUGGING
    const answers = {};
    const inputs = form.querySelectorAll('input[type="radio"]:checked');

    console.log('=== ANSWER COLLECTION DEBUG ===');
    console.log('Total checked inputs found:', inputs.length);

    inputs.forEach(input => {
        console.log('Processing input:', {
            name: input.name,
            value: input.value,
            checked: input.checked
        });

        // Extract question ID from name attribute: answers[123] -> 123
        const match = input.name.match(/answers\[(\d+)\]/);
        if (match) {
            const questionId = match[1];
            answers[questionId] = input.value;
            console.log(`✓ Captured answer: Question ${questionId} = ${input.value}`);
        } else {
            console.log('✗ Failed to extract question ID from:', input.name);
        }
    });

    console.log('Final answers object:', answers);
    console.log('Total answers collected:', Object.keys(answers).length);

    // Get attempt and course IDs
    const attemptId = form.querySelector('input[name="attempt_id"]').value;
    const courseId = form.querySelector('input[name="course_id"]').value;

    console.log('Session-free submission starting...');
    console.log('Answers:', answers);
    console.log('Attempt ID:', attemptId);

    // Use authenticated AJAX endpoint within employee middleware - EXAM ONLY
    const submitUrl = `/employee/exam/submit-ajax/${attemptId}`;

    fetch(submitUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            answers: answers,
            attempt_id: attemptId,
            course_id: courseId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Submission response:', data);
        
        // Clear progress interval
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        
        if (data.success) {
            console.log('✅ Exam submitted successfully - redirecting to results...');

            // Show success message with SweetAlert
            Swal.fire({
                title: 'Submission Successful!',
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                        </div>
                        <p class="mb-2">Your ${examType} has been submitted successfully!</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Redirecting to results page...
                        </div>
                    </div>
                `,
                icon: 'success',
                timer: 2000,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '400px'
            }).then(() => {
                // Redirect to simple results page
                const resultUrl = `/employee/exam/simple-result/${attemptId}`;
                window.location.replace(resultUrl);
            });
        } else {
            throw new Error(data.message || 'Submission failed');
        }
    })
    .catch(error => {
        console.error('❌ Session-free submission failed:', error);
        
        // Clear progress interval
        if (progressInterval) {
            clearInterval(progressInterval);
        }

        // Show error with SweetAlert and retry option
        Swal.fire({
            title: 'Submission Error',
            html: `
                <div class="text-start">
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Submission Failed</strong>
                    </div>
                    <p class="mb-3">Error: ${error.message}</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Your answers are saved. You can try submitting again.
                    </div>
                </div>
            `,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-repeat me-2"></i>Try Again',
            cancelButtonText: '<i class="bi bi-arrow-left me-2"></i>Review Answers',
            width: '500px'
        }).then((result) => {
            // Re-enable form for retry
            form.querySelectorAll('input, button').forEach(input => input.disabled = false);
            
            if (result.isConfirmed) {
                // Retry submission
                submitExamWithConfirmation(event);
            }
        });
    });
}

// Add warning before page unload to prevent accidental navigation
function beforeUnloadHandler(e) {
    const form = document.getElementById('examForm');
    const answered = form.querySelectorAll('input[type="radio"]:checked').length;
    if (answered > 0) {
        e.preventDefault();
        e.returnValue = 'You have answered some questions. Are you sure you want to leave this page? Your progress will be lost.';
        return e.returnValue;
    }
}
window.addEventListener('beforeunload', beforeUnloadHandler);

// Cancel exam with SweetAlert confirmation
function cancelExamWithConfirmation() {
    const form = document.getElementById('examForm');
    const answered = form.querySelectorAll('input[type="radio"]:checked').length;
    const examType = '{{ $attempt->type }}';
    
    Swal.fire({
        title: `Cancel ${examType.charAt(0).toUpperCase() + examType.slice(1)}?`,
        html: `
            <div class="text-start">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> You are about to cancel this ${examType}.
                </div>
                ${answered > 0 ? `
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        You have answered <strong>${answered} question${answered > 1 ? 's' : ''}</strong>. 
                        Your progress will be lost.
                    </div>
                ` : ''}
                <p class="mb-3">
                    <i class="bi bi-arrow-left-circle text-primary me-2"></i>
                    You will be returned to the previous page.
                </p>
                <div class="alert alert-secondary">
                    <small>
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Tip:</strong> You can continue this ${examType} later if you don't cancel.
                    </small>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#007bff',
        confirmButtonText: `<i class="bi bi-x-circle me-2"></i>Yes, Cancel ${examType.charAt(0).toUpperCase() + examType.slice(1)}`,
        cancelButtonText: `<i class="bi bi-arrow-right me-2"></i>Continue ${examType.charAt(0).toUpperCase() + examType.slice(1)}`,
        width: '500px',
        customClass: {
            popup: 'text-start'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Remove beforeunload warning
            window.removeEventListener('beforeunload', beforeUnloadHandler);
            
            // Show cancellation confirmation
            Swal.fire({
                title: `${examType.charAt(0).toUpperCase() + examType.slice(1)} Cancelled`,
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-x-circle-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <p class="mb-2">Your ${examType} has been cancelled.</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Returning to previous page...
                        </div>
                    </div>
                `,
                icon: 'info',
                timer: 1500,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '350px'
            }).then(() => {
                window.history.back();
            });
        }
    });
}

// Session-free mode - no validation needed
console.log('Session validation disabled - using session-free submission');

// Add click handlers for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to option items
    document.querySelectorAll('.option-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'radio') {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.click();
                }
            }
        });
    });

    // Add change handlers to radio buttons for visual feedback
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const questionCard = this.closest('.question-card');

            // Remove selected class from all options in this question
            questionCard.querySelectorAll('.option-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Add selected class to chosen option
            this.closest('.option-item').classList.add('selected');

            // Update question status badge to "Done"
            const questionId = this.name.match(/answers\[(\d+)\]/)[1];
            updateQuestionStatus(questionId);
            
            // Show progress toast notification
            showProgressToast();
        });
    });
    
    // Show progress toast notification
    function showProgressToast() {
        const form = document.getElementById('examForm');
        const questions = window.totalQuestions || 0;
        const answered = form.querySelectorAll('input[type="radio"]:checked').length;
        const percentage = Math.round((answered / questions) * 100);
        
        // Only show toast for every 25% progress or when complete
        if (percentage % 25 === 0 || answered === questions) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            if (answered === questions) {
                Toast.fire({
                    icon: 'success',
                    title: `All questions answered! (${answered}/${questions})`,
                    text: 'Ready to submit your exam'
                });
            } else {
                Toast.fire({
                    icon: 'info',
                    title: `Progress: ${percentage}%`,
                    text: `${answered} of ${questions} questions answered`
                });
            }
        }
    }

    console.log('Exam interface ready - Session-free mode active');
});
</script>
<!-- Session-free exam submission - No session expiration possible -->

@endsection
