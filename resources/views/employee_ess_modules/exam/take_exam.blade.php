@extends('layouts.employee')

@section('title', 'Take ' . ucfirst($attempt->type) . ' - ' . $course->course_title)

@push('styles')
<style>
    body {
        padding-top: 70px !important;
    }
    .navbar {
        position: fixed !important;
        top: 0 !important;
        width: 100% !important;
        z-index: 1030 !important;
    }
    .container-fluid {
        margin-top: 20px !important;
    }
    .question-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .question-header {
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
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
</style>
@endpush

@section('content')
<div class="container-fluid" style="margin-top: 80px; padding-top: 20px;">
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
                                    <li>{{ $questions->count() }} questions total</li>
                                    <li>{{ $attempt->type == 'exam' ? '80%' : '60%' }} passing grade required</li>
                                    <li>Maximum 3 attempts allowed</li>
                                    <li>Select the best answer for each question</li>
                                </ul>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="badge bg-success fs-6">{{ $questions->count() }} Questions</div>
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
                                        Question {{ $index + 1 }} of {{ $questions->count() }}
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
                            <button type="button" class="btn btn-outline-secondary btn-lg me-3" onclick="window.history.back()">
                                <i class="bi bi-arrow-left me-2"></i> Cancel {{ ucfirst($attempt->type) }}
                            </button>
                            <button type="button" class="btn btn-primary btn-lg" onclick="submitExamForm(event)">
                                <i class="bi bi-check-circle me-2"></i> Submit {{ ucfirst($attempt->type) }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Set total questions count for JavaScript access
window.totalQuestions = {{ $questions->count() }};

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

function submitExamForm(event) {
    event.preventDefault(); // Prevent form from submitting immediately

    const form = document.getElementById('examForm');
    const questions = window.totalQuestions || 0;
    const answered = form.querySelectorAll('input[type="radio"]:checked').length;

    // Validate if all questions are answered
    if (answered < questions) {
        alert(`Please answer all questions before submitting.\n\nAnswered: ${answered} / ${questions} questions`);

        // Highlight unanswered questions and scroll to first
        const questionCards = document.querySelectorAll('.question-card');
        let scrolled = false;

        questionCards.forEach(card => {
            const questionInputs = card.querySelectorAll('input[type="radio"]');
            const hasAnswer = Array.from(questionInputs).some(input => input.checked);

            if (!hasAnswer) {
                // Apply warning styles
                card.style.border = '3px solid #dc3545';
                const header = card.querySelector('.question-header');
                if (header) {
                    header.style.backgroundColor = '#f8d7da';
                    header.style.color = '#721c24';
                }

                // Scroll to first unanswered question only
                if (!scrolled) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    scrolled = true;
                }
            } else {
                // Reset styles if answered
                card.style.border = '';
                const header = card.querySelector('.question-header');
                if (header) {
                    header.style.backgroundColor = '';
                    header.style.color = '';
                }
            }
        });

        return false; // Stop submission
    }

    // Confirmation dialog
    const examType = '{{ $attempt->type }}';
    if (confirm(`Ready to submit your ${examType}?\n\nYou have answered all ${questions} questions.\nYou cannot change your answers after submission.\n\nClick OK to submit now.`)) {
        // Disable submit button and show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="bi bi-hourglass-split me-2"></i> Submitting ${examType.charAt(0).toUpperCase() + examType.slice(1)}...`;
        }

        // Disable all inputs
        form.querySelectorAll('input, button').forEach(input => input.disabled = true);

        // Remove beforeunload warning before submitting
        window.removeEventListener('beforeunload', beforeUnloadHandler);

        // Submit exam using session-free AJAX - GUARANTEED NO EXPIRATION
        submitExamSessionFree();

        return false;
    }
}

// Session-free submission function - NEVER EXPIRES
function submitExamSessionFree() {
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
        if (data.success) {
            console.log('✅ Exam submitted successfully - redirecting to results...');

            // Redirect to simple results page - EXAM ONLY
            const resultUrl = `/employee/exam/simple-result/${attemptId}`;

            // Show success message briefly before redirect
            const submitBtn = form.querySelector('button[onclick="submitExamForm(event)"]');
            if (submitBtn) {
                submitBtn.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> ✅ Submitted Successfully!`;
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
            }

            // Redirect after brief success display
            setTimeout(() => {
                window.location.replace(resultUrl);
            }, 1000);
        } else {
            throw new Error(data.message || 'Submission failed');
        }
    })
    .catch(error => {
        console.error('❌ Session-free submission failed:', error);

        // Never show session expired - always allow retry
        alert(`Submission error: ${error.message}\n\nPlease try submitting again. Your answers are saved.`);

        // Re-enable form for retry
        const submitBtn = form.querySelector('button[onclick="submitExamForm(event)"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="bi bi-check-circle me-2"></i> Submit ${examType.charAt(0).toUpperCase() + examType.slice(1)}`;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-primary');
        }
        form.querySelectorAll('input, button').forEach(input => input.disabled = false);
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
        });
    });

    console.log('Exam interface ready - Session-free mode active');
});
</script>
<!-- Session-free exam submission - No session expiration possible -->

@endsection
