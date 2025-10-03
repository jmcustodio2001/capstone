@extends('layouts.employee')

@section('title', 'Exam Result - ' . $attempt->course->course_title)

@push('styles')
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header {{ $attempt->score >= 80 ? 'bg-success' : 'bg-danger' }} text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-{{ $attempt->status == 'completed' ? 'check-circle' : 'x-circle' }} me-2"></i>
                        {{ ucfirst($attempt->type) }} Result: {{ $attempt->course->course_title }}
                    </h4>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card {{ $attempt->score >= 80 ? 'border-success' : 'border-danger' }}">
                                <div class="card-body text-center">
                                    <h2 class="display-4 {{ $attempt->score >= 80 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($attempt->score, 1) }}%
                                    </h2>
                                    <p class="lead">Your Score</p>
                                    
                                    <div class="mt-3">
                                        <span class="badge {{ $attempt->score >= 80 ? 'bg-success' : 'bg-danger' }} fs-6">
                                            {{ $attempt->score >= 80 ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">Attempt Details</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Attempt:</strong> {{ $attempt->attempt_number }} of 3</li>
                                        <li><strong>Correct Answers:</strong> {{ $attempt->correct_answers }} / {{ $attempt->total_questions }}</li>
                                        <li><strong>Started:</strong> {{ $attempt->started_at->format('M d, Y h:i A') }}</li>
                                        <li><strong>Completed:</strong> {{ $attempt->completed_at->format('M d, Y h:i A') }}</li>
                                        <li><strong>Duration:</strong> {{ $attempt->started_at->diffForHumans($attempt->completed_at, true) }}</li>
                                        @if($attempt->type == 'exam')
                                        <li><strong>Passing Grade:</strong> 80%</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Exam Score Display --}}
                    @if($scores['exam_score'] > 0)
                    <div class="card mt-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clipboard-check me-2"></i>Training Progress
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-6 text-center">
                                    <div class="border rounded p-4 bg-success text-white">
                                        <h6>Training Progress</h6>
                                        <h2>{{ number_format($combinedProgress, 1) }}%</h2>
                                        <small>Based on Exam Score</small>
                                        @if($combinedProgress >= 100)
                                        <br><span class="badge bg-warning text-dark mt-2">Training Complete!</span>
                                        @elseif($combinedProgress >= 80)
                                        <br><span class="badge bg-info mt-2">Passed - Training Complete!</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>How it works:</strong> Your training progress is based on your exam score. Pass with 80% or higher to complete the training.
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($attempt->score < 80 && $remainingAttempts > 0)
                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Don't give up!</strong> You have <strong>{{ $remainingAttempts }} more attempt(s)</strong> remaining. 
                        Review the course materials and try again.
                    </div>
                    @elseif($remainingAttempts == 0 && $attempt->score < 80)
                    <div class="alert alert-danger mt-4">
                        <i class="bi bi-x-circle me-2"></i>
                        <strong>Maximum attempts reached.</strong> You have used all 3 attempts for this {{ $attempt->type }}. 
                        Please contact your supervisor or training coordinator for further assistance.
                    </div>
                    @endif

                    <div class="text-center mt-4">
                        @if($attempt->score >= 80)
                        {{-- Passed - Enhanced SweetAlert Actions --}}
                        <button type="button" class="btn btn-success btn-lg" onclick="showPassedActions()">
                            <i class="bi bi-check-circle me-1"></i> View Completed Training
                        </button>
                        <button type="button" class="btn btn-outline-primary ms-2" onclick="goBackToTrainings()">
                            <i class="bi bi-arrow-left me-1"></i> Back to My Trainings
                        </button>
                        <button type="button" class="btn btn-info ms-2" onclick="shareResult()">
                            <i class="bi bi-share me-1"></i> Share Result
                        </button>
                        @else
                        {{-- Failed - Enhanced SweetAlert Actions --}}
                        <button type="button" class="btn btn-primary" onclick="goBackToTrainings()">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Trainings
                        </button>
                        
                        @if($remainingAttempts > 0)
                        <button type="button" class="btn btn-warning ms-2" onclick="confirmRetakeExam()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Try Again ({{ $remainingAttempts }} attempts left)
                        </button>
                        @endif
                        
                        <button type="button" class="btn btn-outline-info ms-2" onclick="showStudyTips()">
                            <i class="bi bi-lightbulb me-1"></i> Study Tips
                        </button>
                        @endif
                        
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="showDetailedResults()">
                            <i class="bi bi-bar-chart me-1"></i> Detailed Analysis
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light-success {
    background-color: #d1e7dd !important;
}
.bg-light-danger {
    background-color: #f8d7da !important;
}
</style>
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update training progress immediately after exam completion
    updateTrainingProgress();
    
    // Show initial result notification with SweetAlert
    showInitialResultNotification();
    
    // Auto-redirect to Completed Training tab if exam passed (80% or higher)
    @if($attempt->score >= 80)
    setTimeout(function() {
        showSuccessRedirectNotification();
    }, 3000); // Wait 3 seconds to let user see their score first
    @else
    // For failed exams, also set completion flag to refresh progress
    localStorage.setItem('exam_completed', 'true');
    window.dispatchEvent(new StorageEvent('storage', {
        key: 'exam_completed', 
        newValue: 'true'
    }));
    @endif
});

// Show initial result notification
function showInitialResultNotification() {
    @if($attempt->score >= 80)
    Swal.fire({
        title: '🎉 Congratulations!',
        html: `
            <div class="text-center">
                <div class="mb-3">
                    <i class="bi bi-trophy-fill text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4 class="text-success">You Passed!</h4>
                <p class="mb-2">Your Score: <strong class="text-success">{{ number_format($attempt->score, 1) }}%</strong></p>
                <p class="text-muted">{{ $attempt->course->course_title }}</p>
                @if($combinedProgress >= 100)
                <div class="alert alert-success mt-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Training Complete!</strong> Your progress has been updated to 100%.
                </div>
                @endif
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Continue',
        confirmButtonColor: '#28a745',
        allowOutsideClick: false,
        customClass: {
            popup: 'swal-wide'
        }
    });
    @else
    Swal.fire({
        title: '📚 Keep Learning!',
        html: `
            <div class="text-center">
                <div class="mb-3">
                    <i class="bi bi-book text-primary" style="font-size: 3rem;"></i>
                </div>
                <h4 class="text-warning">Almost There!</h4>
                <p class="mb-2">Your Score: <strong class="text-danger">{{ number_format($attempt->score, 1) }}%</strong></p>
                <p class="text-muted">{{ $attempt->course->course_title }}</p>
                <p class="text-info">Passing Grade: <strong>80%</strong></p>
                @if($remainingAttempts > 0)
                <div class="alert alert-info mt-3">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    You have <strong>{{ $remainingAttempts }} more attempt(s)</strong> remaining.
                </div>
                @else
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Maximum attempts reached. Please contact your supervisor.
                </div>
                @endif
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Continue',
        confirmButtonColor: '#007bff',
        allowOutsideClick: false,
        customClass: {
            popup: 'swal-wide'
        }
    });
    @endif
}

// Show success redirect notification
function showSuccessRedirectNotification() {
    Swal.fire({
        title: '🚀 Redirecting...',
        html: `
            <div class="text-center">
                <div class="mb-3">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <p>Taking you to your completed trainings...</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                         role="progressbar" style="width: 0%" id="redirectProgress"></div>
                </div>
            </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: () => {
            // Animate progress bar
            let progress = 0;
            const interval = setInterval(() => {
                progress += 20;
                document.getElementById('redirectProgress').style.width = progress + '%';
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 200);
        },
        willClose: () => {
            // Set exam completion flag for progress refresh
            localStorage.setItem('exam_completed', 'true');
            
            // Trigger cross-tab communication
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'exam_completed',
                newValue: 'true'
            }));
            
            // Redirect with exam completion parameters
            window.location.href = '/employee/my-trainings?from_exam=true&exam_completed=true&tab=progress';
        }
    });
}

// Show passed actions modal
function showPassedActions() {
    Swal.fire({
        title: '🎯 What would you like to do next?',
        html: `
            <div class="row g-3">
                <div class="col-md-6">
                    <button class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" onclick="goToCompletedTraining()">
                        <i class="bi bi-check-circle-fill mb-2" style="font-size: 2rem;"></i>
                        <strong>View Completed</strong>
                        <small class="text-muted">See your achievement</small>
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" onclick="goToTrainingProgress()">
                        <i class="bi bi-graph-up mb-2" style="font-size: 2rem;"></i>
                        <strong>View Progress</strong>
                        <small class="text-muted">Track your learning</small>
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" onclick="shareResult()">
                        <i class="bi bi-share mb-2" style="font-size: 2rem;"></i>
                        <strong>Share Result</strong>
                        <small class="text-muted">Celebrate success</small>
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" onclick="showDetailedResults()">
                        <i class="bi bi-bar-chart mb-2" style="font-size: 2rem;"></i>
                        <strong>Analysis</strong>
                        <small class="text-muted">Detailed breakdown</small>
                    </button>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            popup: 'swal-wide'
        }
    });
}

// Go to completed training
function goToCompletedTraining() {
    Swal.close();
    Swal.fire({
        title: 'Loading...',
        text: 'Taking you to completed trainings',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        window.location.href = '{{ route('employee.my_trainings.index') }}?tab=completed&refresh={{ time() }}';
    }, 1000);
}

// Go to training progress
function goToTrainingProgress() {
    Swal.close();
    Swal.fire({
        title: 'Loading...',
        text: 'Taking you to training progress',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        window.location.href = '{{ route('employee.my_trainings.index') }}?tab=progress&refresh={{ time() }}';
    }, 1000);
}

// Go back to trainings
function goBackToTrainings() {
    Swal.fire({
        title: '🔄 Returning to Trainings',
        text: 'Taking you back to your training dashboard...',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        timer: 2000,
        didOpen: () => {
            Swal.showLoading();
        },
        willClose: () => {
            refreshTrainingDashboard();
        }
    });
}

// Confirm retake exam
function confirmRetakeExam() {
    Swal.fire({
        title: '🔄 Retake Exam?',
        html: `
            <div class="text-center">
                <p>Are you ready to try again?</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Remaining attempts:</strong> {{ $remainingAttempts }}
                </div>
                <p class="text-muted">Make sure to review the course materials before retaking.</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Retake Exam',
        cancelButtonText: 'Not Yet',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Good Luck! 🍀',
                text: 'Starting your exam retake...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                willClose: () => {
                    window.location.href = '{{ route('employee.exam.start', $attempt->course_id) }}';
                }
            });
        }
    });
}

// Show study tips
function showStudyTips() {
    Swal.fire({
        title: '💡 Study Tips for Success',
        html: `
            <div class="text-start">
                <div class="alert alert-primary mb-3">
                    <h6><i class="bi bi-target me-2"></i>Your Goal: 80% or higher</h6>
                </div>
                
                <h6><i class="bi bi-book me-2"></i>Study Strategies:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Review all course materials thoroughly</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Take notes on key concepts</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Practice with sample questions if available</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Focus on areas where you struggled</li>
                </ul>
                
                <h6><i class="bi bi-clock me-2"></i>Before Retaking:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-arrow-right text-primary me-2"></i>Ensure you have enough time</li>
                    <li class="mb-2"><i class="bi bi-arrow-right text-primary me-2"></i>Find a quiet environment</li>
                    <li class="mb-2"><i class="bi bi-arrow-right text-primary me-2"></i>Read questions carefully</li>
                </ul>
            </div>
        `,
        confirmButtonText: 'Got it!',
        confirmButtonColor: '#007bff',
        customClass: {
            popup: 'swal-wide text-start'
        }
    });
}

// Show detailed results
function showDetailedResults() {
    Swal.fire({
        title: '📊 Detailed Analysis',
        html: `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">Performance</h5>
                            <h2 class="display-6 {{ $attempt->score >= 80 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($attempt->score, 1) }}%
                            </h2>
                            <p class="text-muted">Your Score</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h5 class="card-title text-info">Accuracy</h5>
                            <h2 class="display-6 text-info">
                                {{ $attempt->correct_answers }}/{{ $attempt->total_questions }}
                            </h2>
                            <p class="text-muted">Correct Answers</p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <h6 class="card-title">Attempt Summary</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <strong>{{ $attempt->attempt_number }}/3</strong>
                                    <br><small class="text-muted">Attempts Used</small>
                                </div>
                                <div class="col-4">
                                    <strong>{{ $attempt->started_at->diffForHumans($attempt->completed_at, true) }}</strong>
                                    <br><small class="text-muted">Duration</small>
                                </div>
                                <div class="col-4">
                                    <strong>{{ $attempt->completed_at->format('M d, Y') }}</strong>
                                    <br><small class="text-muted">Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($scores['exam_score'] > 0)
                <div class="col-12">
                    <div class="alert alert-success">
                        <h6><i class="bi bi-graph-up me-2"></i>Training Progress Updated</h6>
                        <p class="mb-0">Your training progress has been updated to <strong>{{ number_format($combinedProgress, 1) }}%</strong> based on your exam performance.</p>
                    </div>
                </div>
                @endif
            </div>
        `,
        confirmButtonText: 'Close',
        confirmButtonColor: '#6c757d',
        customClass: {
            popup: 'swal-wide'
        }
    });
}

// Share result
function shareResult() {
    Swal.fire({
        title: '🎉 Share Your Achievement',
        html: `
            <div class="text-center">
                <div class="card border-success mb-3">
                    <div class="card-body">
                        <h5 class="text-success">{{ $attempt->course->course_title }}</h5>
                        <h2 class="display-6 text-success">{{ number_format($attempt->score, 1) }}% - PASSED</h2>
                        <p class="text-muted">Completed on {{ $attempt->completed_at->format('M d, Y') }}</p>
                    </div>
                </div>
                <p>Choose how you'd like to share your success:</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-clipboard me-1"></i> Copy Link',
        cancelButtonText: '<i class="bi bi-envelope me-1"></i> Email Result',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#007bff'
    }).then((result) => {
        if (result.isConfirmed) {
            // Copy to clipboard
            const shareText = `I just passed {{ $attempt->course->course_title }} with {{ number_format($attempt->score, 1) }}%! 🎉`;
            navigator.clipboard.writeText(shareText).then(() => {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Share text copied to clipboard',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Email result (mock functionality)
            Swal.fire({
                title: 'Email Sent!',
                text: 'Your result has been shared via email',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Function to update training progress after exam completion
function updateTrainingProgress() {
    fetch('/employee/training/update-progress-after-exam', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            course_id: {{ $attempt->course_id }},
            exam_score: {{ $attempt->score }}
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Training progress updated successfully:', data);
        } else {
            console.error('Failed to update training progress:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating training progress:', error);
    });
}

// Function to refresh training dashboard
function refreshTrainingDashboard() {
    // Set exam completion flag for progress refresh
    localStorage.setItem('exam_completed', 'true');
    
    // Update progress first, then redirect
    updateTrainingProgress();
    
    // Add a small delay to ensure database updates are complete
    setTimeout(function() {
        window.location.href = '{{ route('employee.my_trainings.index') }}?from_exam=true&exam_completed=true&tab=progress&refresh=' + Date.now();
    }, 1000);
}

// Auto-refresh progress every 5 seconds for real-time updates
setInterval(function() {
    updateTrainingProgress();
}, 5000);
</script>

<style>
.swal-wide {
    width: 600px !important;
}

.swal2-html-container .card {
    margin-bottom: 0;
}

.swal2-html-container .btn {
    transition: all 0.3s ease;
}

.swal2-html-container .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% { background-position: 1rem 0; }
    100% { background-position: 0 0; }
}
</style>
@endpush
