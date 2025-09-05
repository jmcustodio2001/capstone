<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels - Profile Update Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --light-bg: #f8f9fa;
    }
    
    body {
      background-color: #f8f9fa !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .main-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      background: white;
    }
    
    .card-header-custom {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border-radius: 12px 12px 0 0 !important;
      padding: 1.5rem 2rem;
    }
    
    .status-badge {
      font-size: 0.875rem;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
    }
    
    .detail-row {
      padding: 1rem 0;
      border-bottom: 1px solid #e9ecef;
    }
    
    .detail-row:last-child {
      border-bottom: none;
    }
    
    .detail-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
    }
    
    .detail-value {
      color: #212529;
      word-wrap: break-word;
    }
    
    .value-comparison {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      margin: 0.5rem 0;
    }
    
    .old-value {
      color: #dc3545;
      text-decoration: line-through;
      opacity: 0.7;
    }
    
    .new-value {
      color: #198754;
      font-weight: 600;
    }
    
    .btn-custom {
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-primary-custom {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      color: white;
    }
    
    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }
    
    .timeline-item {
      position: relative;
      padding-left: 2rem;
      margin-bottom: 1rem;
    }
    
    .timeline-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0.5rem;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: var(--primary-color);
    }
    
    .timeline-item::after {
      content: '';
      position: absolute;
      left: 5px;
      top: 1.2rem;
      width: 2px;
      height: calc(100% - 0.5rem);
      background: #e9ecef;
    }
    
    .timeline-item:last-child::after {
      display: none;
    }
  </style>
</head>
<body>
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="mb-1">Profile Update Details</h2>
            <p class="text-muted mb-0">View your profile update request information</p>
          </div>
          <a href="{{ route('employee.profile-updates.index') }}" class="btn btn-outline-primary btn-custom">
            <i class="bi bi-arrow-left me-2"></i>Back to Updates
          </a>
        </div>

        <!-- Main Content Card -->
        <div class="card main-card">
          <div class="card-header card-header-custom">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h4 class="mb-1">
                  <i class="bi bi-person-gear me-2"></i>
                  {{ $profileUpdate->formatted_field_name }}
                </h4>
                <p class="mb-0 opacity-75">Request ID: #{{ $profileUpdate->id }}</p>
              </div>
              <span class="badge status-badge {{ $profileUpdate->status_badge_class }}">
                @if($profileUpdate->status === 'pending')
                  <i class="bi bi-clock me-1"></i>Pending
                @elseif($profileUpdate->status === 'approved')
                  <i class="bi bi-check-circle me-1"></i>Approved
                @elseif($profileUpdate->status === 'rejected')
                  <i class="bi bi-x-circle me-1"></i>Rejected
                @endif
              </span>
            </div>
          </div>

          <div class="card-body p-4">
            <div class="row">
              <!-- Request Details -->
              <div class="col-12 col-lg-8">
                <h5 class="mb-3">
                  <i class="bi bi-info-circle me-2"></i>Request Details
                </h5>

                <div class="detail-row">
                  <div class="detail-label">Field Being Updated</div>
                  <div class="detail-value">{{ $profileUpdate->formatted_field_name }}</div>
                </div>

                <div class="detail-row">
                  <div class="detail-label">Value Changes</div>
                  <div class="value-comparison">
                    <div class="row">
                      <div class="col-md-6">
                        <small class="text-muted">Current Value:</small>
                        <div class="old-value mt-1">
                          @if($profileUpdate->field_name === 'profile_picture' && $profileUpdate->old_value && $profileUpdate->old_value !== 'N/A')
                            <img src="{{ asset('storage/' . $profileUpdate->old_value) }}" alt="Current Profile Picture" style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                          @else
                            {{ $profileUpdate->old_value ?: 'Not set' }}
                          @endif
                        </div>
                      </div>
                      <div class="col-md-6">
                        <small class="text-muted">Requested Value:</small>
                        <div class="new-value mt-1">
                          @if($profileUpdate->field_name === 'profile_picture' && $profileUpdate->new_value)
                            <img src="{{ asset('storage/' . $profileUpdate->new_value) }}" alt="New Profile Picture" style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                          @else
                            {{ $profileUpdate->new_value }}
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                @if($profileUpdate->reason)
                <div class="detail-row">
                  <div class="detail-label">Reason for Change</div>
                  <div class="detail-value">{{ $profileUpdate->reason }}</div>
                </div>
                @endif

                @if($profileUpdate->status === 'rejected' && $profileUpdate->rejection_reason)
                <div class="detail-row">
                  <div class="detail-label">Rejection Reason</div>
                  <div class="detail-value text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ $profileUpdate->rejection_reason }}
                  </div>
                </div>
                @endif
              </div>

              <!-- Timeline -->
              <div class="col-12 col-lg-4">
                <h5 class="mb-3">
                  <i class="bi bi-clock-history me-2"></i>Timeline
                </h5>

                <div class="timeline-item">
                  <div class="detail-label">Request Submitted</div>
                  <div class="detail-value">
                    {{ $profileUpdate->requested_at ? $profileUpdate->requested_at->format('M d, Y g:i A') : $profileUpdate->created_at->format('M d, Y g:i A') }}
                  </div>
                </div>

                @if($profileUpdate->status === 'approved' && $profileUpdate->approved_at)
                <div class="timeline-item">
                  <div class="detail-label text-success">Approved</div>
                  <div class="detail-value">
                    {{ $profileUpdate->approved_at->format('M d, Y g:i A') }}
                    @if($profileUpdate->approver)
                      <br><small class="text-muted">by {{ $profileUpdate->approver->name }}</small>
                    @endif
                  </div>
                </div>
                @elseif($profileUpdate->status === 'rejected' && $profileUpdate->approved_at)
                <div class="timeline-item">
                  <div class="detail-label text-danger">Rejected</div>
                  <div class="detail-value">
                    {{ $profileUpdate->approved_at->format('M d, Y g:i A') }}
                    @if($profileUpdate->approver)
                      <br><small class="text-muted">by {{ $profileUpdate->approver->name }}</small>
                    @endif
                  </div>
                </div>
                @endif
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
              <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                  @if($profileUpdate->status === 'pending')
                    <a href="{{ route('employee.profile-updates.edit', $profileUpdate) }}" class="btn btn-warning btn-custom">
                      <i class="bi bi-pencil me-2"></i>Edit Request
                    </a>
                  @endif
                  
                  @if($profileUpdate->status === 'rejected')
                    <a href="{{ route('employee.profile-updates.create') }}?retry={{ $profileUpdate->id }}" class="btn btn-primary-custom btn-custom">
                      <i class="bi bi-arrow-repeat me-2"></i>Submit New Request
                    </a>
                  @endif
                  
                  <button type="button" class="btn btn-outline-secondary btn-custom" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print Details
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
