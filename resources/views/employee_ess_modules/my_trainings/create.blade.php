@extends('layouts.employee_app')

@section('title', 'Add New Training')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Training Record</h3>
                    <div class="card-tools">
                        <a href="{{ route('employee.my_trainings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to My Trainings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This form is for adding completed training records. For requesting new trainings, please use the Training Requests tab in the main training page.
                    </div>

                    <form action="{{ route('employee.trainings.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="training_title">Training Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('training_title') is-invalid @enderror" 
                                           id="training_title" name="training_title" 
                                           value="{{ old('training_title') }}" required>
                                    @error('training_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="training_date">Training Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('training_date') is-invalid @enderror" 
                                           id="training_date" name="training_date" 
                                           value="{{ old('training_date') }}" required>
                                    @error('training_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="In Progress" {{ old('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Upcoming" {{ old('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="progress">Progress (%)</label>
                                    <input type="number" class="form-control @error('progress') is-invalid @enderror" 
                                           id="progress" name="progress" min="0" max="100"
                                           value="{{ old('progress') }}" placeholder="0-100">
                                    @error('progress')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="feedback">Training Feedback</label>
                            <textarea class="form-control @error('feedback') is-invalid @enderror" 
                                      id="feedback" name="feedback" rows="3" 
                                      placeholder="Optional: Add your feedback about this training">{{ old('feedback') }}</textarea>
                            @error('feedback')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notification_type">Notification Type</label>
                                    <select class="form-control @error('notification_type') is-invalid @enderror" 
                                            id="notification_type" name="notification_type">
                                        <option value="">No Notification</option>
                                        <option value="Email" {{ old('notification_type') == 'Email' ? 'selected' : '' }}>Email</option>
                                        <option value="SMS" {{ old('notification_type') == 'SMS' ? 'selected' : '' }}>SMS</option>
                                        <option value="System" {{ old('notification_type') == 'System' ? 'selected' : '' }}>System Notification</option>
                                    </select>
                                    @error('notification_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notification_message">Notification Message</label>
                                    <input type="text" class="form-control @error('notification_message') is-invalid @enderror" 
                                           id="notification_message" name="notification_message" 
                                           value="{{ old('notification_message') }}" 
                                           placeholder="Optional notification message">
                                    @error('notification_message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Training Record
                            </button>
                            <a href="{{ route('employee.my_trainings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-set progress to 100% when status is "Completed"
    $('#status').change(function() {
        if ($(this).val() === 'Completed') {
            $('#progress').val(100);
        } else if ($(this).val() === 'Upcoming') {
            $('#progress').val(0);
        }
    });

    // Enable/disable notification message based on notification type
    $('#notification_type').change(function() {
        if ($(this).val() === '') {
            $('#notification_message').prop('disabled', true).val('');
        } else {
            $('#notification_message').prop('disabled', false);
        }
    });

    // Initialize notification message state
    $('#notification_type').trigger('change');
});
</script>
@endsection
