@extends('layouts.employee')

@section('title', 'Exam Result - ' . $attempt->course->course_title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header {{ $attempt->status == 'completed' ? 'bg-success' : 'bg-danger' }} text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-{{ $attempt->status == 'completed' ? 'check-circle' : 'x-circle' }} me-2"></i>
                        {{ ucfirst($attempt->type) }} Result: {{ $attempt->course->course_title }}
                    </h4>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card {{ $attempt->status == 'completed' ? 'border-success' : 'border-danger' }}">
                                <div class="card-body text-center">
                                    <h2 class="display-4 {{ $attempt->status == 'completed' ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($attempt->score, 1) }}%
                                    </h2>
                                    <p class="lead">Your Score</p>
                                    
                                    <div class="mt-3">
                                        <span class="badge {{ $attempt->status == 'completed' ? 'bg-success' : 'bg-danger' }} fs-6">
                                            {{ $attempt->status == 'completed' ? 'PASSED' : 'FAILED' }}
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

                    @if($attempt->status == 'failed' && $remainingAttempts > 0)
                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Don't give up!</strong> You have <strong>{{ $remainingAttempts }} more attempt(s)</strong> remaining. 
                        Review the course materials and try again.
                    </div>
                    @elseif($remainingAttempts == 0 && $attempt->status == 'failed')
                    <div class="alert alert-danger mt-4">
                        <i class="bi bi-x-circle me-2"></i>
                        <strong>Maximum attempts reached.</strong> You have used all 3 attempts for this {{ $attempt->type }}. 
                        Please contact your supervisor or training coordinator for further assistance.
                    </div>
                    @endif

                    <div class="text-center mt-4">
                        @if($attempt->score >= 80)
                        {{-- Passed - Redirect to Completed Training --}}
                        <a href="{{ route('employee.my_trainings.index') }}?tab=completed&refresh={{ time() }}" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-1"></i> View Completed Training
                        </a>
                        <a href="{{ route('employee.my_trainings.index') }}?refresh={{ time() }}" class="btn btn-outline-primary ms-2">
                            <i class="bi bi-arrow-left me-1"></i> Back to My Trainings
                        </a>
                        @else
                        {{-- Failed - Stay on current page with retry option --}}
                        <button type="button" class="btn btn-primary" onclick="refreshTrainingDashboard()">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Trainings
                        </button>
                        
                        <script>
                        function refreshTrainingDashboard() {
                            // Add a small delay to ensure database updates are complete
                            setTimeout(function() {
                                window.location.href = '{{ route('employee.my_trainings.index') }}?refresh=' + Date.now();
                            }, 1000);
                        }
                        </script>
                        
                        @if($remainingAttempts > 0)
                        <a href="{{ route('employee.exam.start', $attempt->course_id) }}" class="btn btn-warning ms-2">
                            <i class="bi bi-arrow-clockwise me-1"></i> Try Again ({{ $remainingAttempts }} attempts left)
                        </a>
                        @endif
                        @endif
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-redirect to Completed Training tab if exam passed
    @if($attempt->score >= 80)
    setTimeout(function() {
        // Show success message before redirect
        const successMessage = document.createElement('div');
        successMessage.className = 'alert alert-success alert-dismissible fade show position-fixed';
        successMessage.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        successMessage.innerHTML = `
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Congratulations!</strong> Training completed successfully! 
            Redirecting to Completed Training...
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(successMessage);
        
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "{{ route('employee.my_trainings.index') }}?tab=completed";
        }, 3000);
    }, 2000); // Wait 2 seconds to let user see their score first
    @endif
});
</script>
@endpush
