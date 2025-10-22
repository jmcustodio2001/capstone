<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- Custom SweetAlert Styling -->
  <style>
    .swal-wide {
      width: 800px !important;
    }
    
    .swal2-popup {
      border-radius: 15px !important;
    }
    
    .swal2-title {
      font-size: 1.5rem !important;
      font-weight: 600 !important;
    }
    
    .swal2-html-container {
      font-size: 0.95rem !important;
      line-height: 1.5 !important;
    }
    
    .swal2-confirm {
      border-radius: 8px !important;
      font-weight: 500 !important;
      padding: 8px 20px !important;
    }
    
    .swal2-cancel {
      border-radius: 8px !important;
      font-weight: 500 !important;
      padding: 8px 20px !important;
    }
    
    .swal2-input {
      border-radius: 8px !important;
      border: 2px solid #e9ecef !important;
      padding: 10px 15px !important;
      font-size: 0.95rem !important;
    }
    
    .swal2-input:focus {
      border-color: #0d6efd !important;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
    }
    
    .swal2-validation-message {
      background: #f8d7da !important;
      color: #721c24 !important;
      border-radius: 6px !important;
      padding: 8px 12px !important;
      margin-top: 8px !important;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">

    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Succession Readiness Rating</h2>
            <p class="text-muted mb-0">
              Welcome back, Admin! Here's your readiness ratings list.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Succession Readiness Rating</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Add Rating Card -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Add Readiness Rating</h4>
        <button type="button" class="btn btn-success" onclick="showAIAnalysis()" style="cursor: pointer;">
          <i class="bi bi-robot me-1"></i>LeadGen AI
        </button>
      </div>
      <div class="card-body">
        <form method="POST"
          @if(isset($editMode) && isset($rating))
            action="/admin/succession-readiness-ratings/{{ $rating->id }}"
          @else
            action="/admin/succession-readiness-ratings"
          @endif
          class="mb-4">
          @csrf
          @if(isset($editMode) && isset($rating))
            @method('PUT')
          @endif
          <div class="row">
            <div class="col-md-4">
              <input type="text" name="employee_display" class="form-control" placeholder="Select Employee" readonly 
                @if(isset($rating) && $rating->employee) value="{{ $rating->employee->first_name }} {{ $rating->employee->last_name }} ({{ $rating->employee_id }})" @endif
                @if(isset($showMode)) disabled @endif>
              <input type="hidden" name="employee_id" required
                @if(isset($rating)) value="{{ $rating->employee_id }}" @endif>
            </div>
            <div class="col-md-3">
              <input type="text" name="readiness_level" class="form-control" placeholder="Readiness Level" readonly required 
                @if(isset($rating)) value="{{ $rating->readiness_level }}" @endif
                @if(isset($showMode)) disabled @endif>
            </div>
            <div class="col-md-3">
              <input type="date" name="assessment_date" class="form-control" required
                @if(isset($rating)) value="{{ $rating->assessment_date }}" @endif
                @if(isset($showMode)) disabled @endif>
            </div>
            <div class="col-md-2">
              @if(isset($showMode))
                <button class="btn btn-secondary w-100" disabled>View</button>
              @elseif(isset($editMode))
                <button type="button" class="btn btn-primary w-100" onclick="updateRatingWithConfirmation()">Update</button>
              @else
                <button type="button" class="btn btn-primary w-100" onclick="addRatingWithConfirmation()">Add</button>
              @endif
            </div>
          </div>
        </form>

        <!-- Enhanced AI Analysis Panel -->
        <div class="card mb-4" id="aiAnalysisPanel" style="display: block;">
          <div class="card-header bg-success bg-opacity-10 border-success">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="fw-bold mb-0 text-success"><i class="bi bi-robot me-2"></i>LeadGen AI - Advanced Readiness Intelligence</h5>
            </div>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label fw-bold">Select Employee to Analyze</label>
                <select id="analyzeEmployee" class="form-select" onchange="analyzeEmployeeReadiness()">
                  <option value="">Choose Employee</option>
                  @foreach($employees as $emp)
                    <option value="{{ $emp->employee_id }}" data-name="{{ $emp->first_name }} {{ $emp->last_name }}">
                      {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_id }})
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <!-- AI Analysis Header -->
            <div class="mb-3">
              <h6 class="fw-bold text-success">
                <i class="bi bi-person me-1"></i>Individual Analysis
              </h6>
            </div>
            
            <!-- Individual Analysis Content -->
            <div id="analysisResults">
                  <div class="text-center py-4">
                    <i class="bi bi-robot display-4 text-success mb-3"></i>
                    <h5 class="text-success">AI Succession Readiness Analysis</h5>
                    <p class="text-muted">Select an employee above to analyze their succession readiness using advanced AI algorithms.</p>
                    <div class="row mt-4">
                      <div class="col-md-4">
                        <div class="card border-success border-opacity-25">
                          <div class="card-body text-center">
                            <i class="bi bi-graph-up text-success display-6 mb-2"></i>
                            <h6>Competency Analysis</h6>
                            <small class="text-muted">Skills & proficiency assessment</small>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="card border-primary border-opacity-25">
                          <div class="card-body text-center">
                            <i class="bi bi-trophy text-primary display-6 mb-2"></i>
                            <h6>Performance Prediction</h6>
                            <small class="text-muted">Future success probability</small>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="card border-warning border-opacity-25">
                          <div class="card-body text-center">
                            <i class="bi bi-shield-check text-warning display-6 mb-2"></i>
                            <h6>Risk Assessment</h6>
                            <small class="text-muted">Succession risk evaluation</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
          </div>
        </div>

        <!-- Readiness Ratings Table -->
        <table class="table table-bordered">
          <thead class="table-primary">
            <tr>
              <th class="fw-bold">Employee</th>
              <th class="fw-bold">Readiness Level & Reasoning</th>
              <th class="fw-bold">Assessment Date</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ratings as $rating)
            <tr>
              <td>
                @if($rating->employee)
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                      @php
                        $firstName = $rating->employee->first_name ?? 'Unknown';
                        $lastName = $rating->employee->last_name ?? 'Employee';
                        $fullName = $firstName . ' ' . $lastName;
                        
                        // Check if profile picture exists - simplified approach
                        $profilePicUrl = null;
                        if ($rating->employee->profile_picture) {
                            // Direct asset URL generation - Laravel handles the storage symlink
                            $profilePicUrl = asset('storage/' . $rating->employee->profile_picture);
                        }
                        
                        // Generate consistent color based on employee name for fallback
                        $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                        $employeeId = $rating->employee->employee_id ?? 'default';
                        $colorIndex = abs(crc32($employeeId)) % count($colors);
                        $bgColor = $colors[$colorIndex];
                        
                        // Fallback to UI Avatars if no profile picture found
                        if (!$profilePicUrl) {
                            $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . 
                                           "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
                        }
                      @endphp
                      
                      <img src="{{ $profilePicUrl }}" 
                           alt="{{ $firstName }} {{ $lastName }}" 
                           class="rounded-circle" 
                           style="width: 40px; height: 40px; object-fit: cover;">
                    </div>
                    <div>
                      <span class="fw-semibold">{{ $firstName }} {{ $lastName }}</span>
                      <br><small class="text-muted">ID: {{ $rating->employee->employee_id }}</small>
                    </div>
                  </div>
                @else
                  N/A
                @endif
              </td>
              <td>
                @php
                  $level = $rating->readiness_level ?? 'Needs Development';
                  $badgeClass = match($level) {
                    'Ready Now' => 'bg-success',
                    'Ready Soon' => 'bg-warning', 
                    'Needs Development' => 'bg-danger',
                    default => 'bg-secondary'
                  };
                @endphp
                <!-- Enhanced Readiness Level Card -->
                <div class="card border-0 shadow-sm">
                  <div class="card-header bg-gradient {{ $badgeClass }} bg-opacity-10 border-0 py-2">
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center">
                        @php
                          $icon = match($level) {
                            'Ready Now' => 'bi-check-circle-fill text-success',
                            'Ready Soon' => 'bi-clock-fill text-warning',
                            'Needs Development' => 'bi-exclamation-triangle-fill text-danger',
                            default => 'bi-question-circle-fill text-secondary'
                          };
                        @endphp
                        <i class="bi {{ $icon }} me-2"></i>
                        <span class="fw-bold text-{{ str_replace('bg-', '', $badgeClass) }}">{{ $level }}</span>
                      </div>
                      <span class="badge {{ $badgeClass }} text-white">
                        {{ $level === 'Ready Now' ? 'HIGH' : ($level === 'Ready Soon' ? 'MEDIUM' : 'LOW') }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="card-body p-3">
                    <!-- Reasoning Section -->
                    <div class="readiness-reasoning mb-3" id="reasoning-{{ $rating->id }}">
                      <div class="d-flex align-items-start">
                        <div class="spinner-border spinner-border-sm text-primary me-2 mt-1" role="status" style="width: 1rem; height: 1rem;"></div>
                        <div class="flex-grow-1">
                          <div class="text-muted small loading-text">
                            <i class="bi bi-cpu me-1"></i>AI analyzing employee readiness...
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                      <button class="btn btn-sm btn-outline-primary flex-grow-1" type="button" 
                              onclick="toggleReadinessDetails({{ $rating->id }}, '{{ $rating->employee ? $rating->employee->employee_id : '' }}')">
                        <i class="bi bi-chevron-down"></i> <span>View Analysis</span>
                      </button>
                      <button class="btn btn-sm btn-outline-success" type="button" 
                              onclick="applyToForm('{{ $rating->employee ? $rating->employee->employee_id : '' }}', '{{ $level }}')" 
                              title="Apply to Form">
                        <i class="bi bi-arrow-up-circle"></i>
                      </button>
                    </div>
                  </div>
                </div>
                
                <!-- Enhanced Expandable Details -->
                <div class="collapse mt-3" id="details-{{ $rating->id }}">
                  <div class="card border-primary border-opacity-25 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-5 border-0">
                      <h6 class="mb-0 text-primary">
                        <i class="bi bi-graph-up me-2"></i>Detailed Competency Analysis
                      </h6>
                    </div>
                    <div class="card-body">
                      <div id="detailed-analysis-{{ $rating->id }}">
                        <div class="text-center py-4">
                          <div class="spinner-border text-primary mb-2" role="status"></div>
                          <div class="text-muted">
                            <i class="bi bi-cpu me-1"></i>Analyzing competency data and training records...
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
              <td>{{ \Carbon\Carbon::parse($rating->assessment_date)->format('Y-m-d') }}</td>
              <td class="text-center">
                <div class="btn-group" role="group">
                  <button class="btn btn-sm btn-outline-info" onclick="viewRatingDetails({{ $rating->id }})" title="View">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary" onclick="editRatingWithConfirmation({{ $rating->id }})" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" onclick="deleteRatingWithConfirmation({{ $rating->id }})" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center text-muted">No readiness ratings found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="d-flex justify-content-end mt-3">
          {{ $ratings->links() }}
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Simple AI Analysis System
    function showAIAnalysis() {
      const panel = document.getElementById('aiAnalysisPanel');
      if (panel) {
        panel.style.display = 'block';
        panel.scrollIntoView({ behavior: 'smooth' });
      }
    }

    function analyzeEmployeeReadiness() {
      const employeeSelect = document.getElementById('analyzeEmployee');
      const employeeId = employeeSelect.value;
      const employeeName = employeeSelect.options[employeeSelect.selectedIndex].getAttribute('data-name');
      
      if (!employeeId) return;

      const resultsContainer = document.getElementById('analysisResults');
      resultsContainer.innerHTML = `
        <div class="text-center py-3">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Analyzing ${employeeName}'s readiness...</p>
        </div>
      `;

      // Get readiness score from Employee Training Dashboard API
      console.log(`Fetching readiness score for employee: ${employeeId}`);
      
      // Get competency data directly from succession readiness API (which includes all data)
      fetch(`/succession_readiness_ratings/competency-data/${employeeId}`)
        .then(response => {
          console.log('API Response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
          }
          return response.json();
        })
        .then(competencyData => {
          console.log('Competency data received:', competencyData);
          
          // Use the calculated readiness score from backend instead of frontend calculation
          if (competencyData.calculated_readiness_score !== undefined) {
            // Use the backend-calculated score directly
            competencyData.overall_readiness = competencyData.calculated_readiness_score;
          }
          displaySimpleAnalysis(competencyData, employeeName);
        })
        .catch(error => {
          console.error('API Error:', error);
          resultsContainer.innerHTML = `
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Competency Data API Error</strong><br>
              <strong>Error:</strong> ${error.message}<br>
              <strong>Endpoint:</strong> /succession_readiness_ratings/competency-data/${employeeId}<br>
              <strong>Employee ID:</strong> ${employeeId}
              <div class="mt-3">
                <button class="btn btn-sm btn-outline-primary" onclick="analyzeEmployeeReadiness()">
                  <i class="bi bi-arrow-clockwise me-1"></i>Retry
                </button>
              </div>
            </div>
          `;
        });
    }

    function displaySimpleAnalysis(data, employeeName) {
      console.log('Display analysis called with data:', data);
      
      // Use backend-calculated score (new simplified calculation)
      let overallReadiness;
      if (data.calculated_readiness_score !== undefined) {
        overallReadiness = data.calculated_readiness_score;
        console.log('Using backend-calculated readiness score:', overallReadiness);
      } else {
        // Fallback to simplified frontend calculation matching backend
        const yearsOfService = data.years_of_service || 0;
        const certificates = data.certificates_earned || 0;
        const totalCompetencies = data.total_competencies_assessed || 0;
        const avgProficiency = data.avg_proficiency_level || 0;
        
        // 1. HIRE DATE COMPONENT (10%)
        const hireDateScore = Math.min(10, yearsOfService * 1); // 1% per year, max 10%
        
        // 2. TRAINING RECORDS COMPONENT (3%)
        const trainingRecordsScore = Math.min(3, certificates * 0.5); // 0.5% per certificate, max 3%
        
        // 3. COMPETENCY PROFILES COMPONENT (Additive)
        // Each competency adds 2% per proficiency level (Level 1=2%, Level 2=4%, etc.)
        const competencyScore = totalCompetencies * avgProficiency * 2;
        
        // CALCULATE FINAL SCORE
        const totalScore = hireDateScore + trainingRecordsScore + competencyScore;
        
        // Set minimum score
        const minimumScore = yearsOfService < 1 ? 5 : 15;
        
        // Final score
        overallReadiness = Math.max(minimumScore, Math.min(100, Math.round(totalScore)));
        
        console.log('Simplified calculation:', {
          hireDateScore,
          trainingRecordsScore,
          competencyScore,
          totalScore,
          overallReadiness
        });
      }
      
      // Determine readiness level based on calculated score (matching dropdown options)
      let readinessLevel, readinessClass, timeline;
      if (overallReadiness >= 80) {
        readinessLevel = "Ready Now";
        readinessClass = "success";
        timeline = "0-3 months";
      } else if (overallReadiness >= 50) {
        readinessLevel = "Ready Soon";
        readinessClass = "warning";
        timeline = "3-6 months";
      } else {
        readinessLevel = "Needs Development";
        readinessClass = "danger";
        timeline = "6-12 months";
      }
      
      console.log(`Final Analysis Score: ${overallReadiness}% for ${employeeName}`);
      
      // Get data for display
      const proficiency = data.avg_proficiency_level || 0;
      const leadership = data.leadership_competencies_count || 0;
      const trainingProgress = data.training_progress || 0;
      const certificates = data.certificates_earned || 0;
      const yearsOfService = data.years_of_service || 0;
      
      // Generate strengths and development areas
      const strengths = [];
      const developmentAreas = [];
      
      if (proficiency >= 4) strengths.push('High Competency Level');
      if (leadership >= 3) strengths.push('Leadership Skills');
      if (trainingProgress >= 80) strengths.push('Training Excellence');
      if (certificates >= 2) strengths.push('Professional Certifications');
      // Removed destination training reference
      if (yearsOfService >= 3) strengths.push('Experienced Employee');
      
      if (proficiency < 3) developmentAreas.push('Skill Development');
      if (leadership < 2) developmentAreas.push('Leadership Training');
      if (trainingProgress < 60) developmentAreas.push('Complete Training');
      if (certificates === 0) developmentAreas.push('Earn Certifications');
      const destinationTrainingsCompleted = data.destination_trainings_completed || 0;
      if (destinationTrainingsCompleted === 0) developmentAreas.push('Gain Destination Knowledge');
      
      if (strengths.length === 0) strengths.push('Ready for Assessment');
      if (developmentAreas.length === 0) developmentAreas.push('Continue Development');
      
      const resultsContainer = document.getElementById('analysisResults');
      resultsContainer.innerHTML = `
        <div class="card border-${readinessClass}">
          <div class="card-header bg-${readinessClass} bg-opacity-10">
            <h5 class="mb-0 text-${readinessClass}">
              <i class="bi bi-person-check me-2"></i>${employeeName} - AI Analysis
              <span class="badge bg-success ms-2">Real Data</span>
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-3 text-center">
                <div class="display-6 text-${readinessClass} fw-bold">${Math.round(overallReadiness)}%</div>
                <small class="text-muted">Overall Readiness</small>
              </div>
              <div class="col-md-3 text-center">
                <div class="h4 text-${readinessClass}">${readinessLevel}</div>
                <small class="text-muted">Readiness Level</small>
              </div>
              <div class="col-md-3 text-center">
                <div class="h4 text-primary">${timeline}</div>
                <small class="text-muted">Timeline</small>
              </div>
              <div class="col-md-3 text-center">
                <div class="h4 text-info">${data.destination_trainings_completed || 0}</div>
                <small class="text-muted">Destination Trainings</small>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <h6 class="fw-bold text-success"><i class="bi bi-award me-1"></i>Strengths</h6>
                <div class="mb-3">
                  ${strengths.map(s => `<span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">${s}</span>`).join('')}
                </div>
              </div>
              <div class="col-md-6">
                <h6 class="fw-bold text-warning"><i class="bi bi-exclamation-circle me-1"></i>Development Areas</h6>
                <div class="mb-3">
                  ${developmentAreas.map(d => `<span class="badge bg-warning bg-opacity-10 text-warning me-1 mb-1">${d}</span>`).join('')}
                </div>
              </div>
            </div>
            
            <div class="alert alert-info">
              <h6><i class="bi bi-lightbulb me-2"></i>AI Recommendation</h6>
              <p class="mb-2">${getSimpleRecommendation(readinessLevel)}</p>
              ${yearsOfService > 0 ? `<div class="small text-muted mb-2">
                <i class="bi bi-calendar-check me-1"></i>
                <strong>Tenure Advantage:</strong> ${yearsOfService} years of service contributes to overall readiness
              </div>` : ''}
              <button class="btn btn-primary btn-sm" onclick="applyToForm('${data.employee_id || ''}', '${readinessLevel}')">
                <i class="bi bi-check-lg me-1"></i>Apply to Form
              </button>
            </div>
          </div>
        </div>
      `;
    }
    
    function getSimpleRecommendation(level) {
      switch(level) {
        case 'Ready Now':
          return 'Employee is ready for immediate succession. Consider for leadership roles.';
        case 'Ready Soon':
          return 'Employee shows strong potential. Provide targeted development for 3-6 months.';
        case 'Needs Development':
          return 'Employee requires development. Focus on skill building and training completion.';
        default:
          return 'Assessment needed to determine readiness level.';
      }
    }
    
    function applyToForm(employeeId, level) {
      if (employeeId) {
        // This is the old function - the new enhanced one is below
        document.querySelector('input[name="employee_id"]').value = employeeId;
      }
      document.querySelector('input[name="readiness_level"]').value = level;
      document.querySelector('input[name="assessment_date"]').value = new Date().toISOString().split('T')[0];
      
      // Scroll to form
      document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
      
      // Show success message
      const alert = document.createElement('div');
      alert.className = 'alert alert-success alert-dismissible fade show mt-2';
      alert.innerHTML = `
        <i class="bi bi-check-circle me-2"></i>AI analysis applied to form!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.querySelector('form').parentElement.insertBefore(alert, document.querySelector('form'));
      setTimeout(() => alert.remove(), 3000);
    }

    // Simple table reasoning functions
    document.addEventListener('DOMContentLoaded', function() {
      @foreach($ratings as $rating)
        @if($rating->employee)
          loadSimpleReasoning({{ $rating->id }}, '{{ $rating->employee->employee_id }}', '{{ $rating->readiness_level }}');
        @endif
      @endforeach
    });

    function loadSimpleReasoning(ratingId, employeeId, readinessLevel) {
      const reasoningElement = document.getElementById(`reasoning-${ratingId}`);
      
      fetch(`/succession_readiness_ratings/competency-data/${employeeId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
          }
          return response.json();
        })
        .then(data => {
          const reasoning = generateSimpleReasoning(data, readinessLevel);
          reasoningElement.innerHTML = reasoning;
        })
        .catch(error => {
          reasoningElement.innerHTML = `
            <div class="text-muted small">
              <i class="bi bi-exclamation-triangle me-1"></i>
              Unable to load analysis data.
            </div>
          `;
        });
    }

    function generateSimpleReasoning(data, readinessLevel) {
      const proficiency = data.avg_proficiency_level || 0;
      const leadership = data.leadership_competencies_count || 0;
      const totalCompetencies = data.total_competencies_assessed || 0;
      const trainingProgress = data.training_progress || 0;
      const certificates = data.certificates_earned || 0;
      const yearsOfService = data.years_of_service || 0;

      let icon, colorClass, reasoning;
      
      switch(readinessLevel) {
        case 'Ready Now':
          icon = 'bi-check-circle-fill';
          colorClass = 'text-success';
          reasoning = `Ready for succession: ${Math.round(proficiency)}/5 competency, ${leadership} leadership skills, ${yearsOfService} years experience, ${Math.round(trainingProgress)}% training progress, ${data.destination_trainings_completed || 0} destination trainings`;
          break;
        case 'Ready Soon':
          icon = 'bi-clock-fill';
          colorClass = 'text-warning';
          reasoning = `Good potential: ${Math.round(proficiency)}/5 competency, ${leadership} leadership skills, ${yearsOfService} years experience, needs 3-6 months development`;
          break;
        case 'Needs Development':
          icon = 'bi-exclamation-triangle-fill';
          colorClass = 'text-danger';
          reasoning = `Requires development: ${Math.round(proficiency)}/5 competency, ${leadership} leadership skills, ${yearsOfService} years experience, 6-12 months recommended`;
          break;
        default:
          icon = 'bi-question-circle-fill';
          colorClass = 'text-secondary';
          reasoning = 'Assessment data incomplete';
      }

      return `
        <div class="${colorClass} small">
          <i class="${icon} me-1"></i>
          ${reasoning}
        </div>
      `;
    }

    function toggleReadinessDetails(ratingId, employeeId) {
      const detailsElement = document.getElementById(`details-${ratingId}`);
      const button = event.target.closest('button');
      const icon = button.querySelector('i');
      const text = button.querySelector('span');
      
      if (detailsElement.classList.contains('show')) {
        detailsElement.classList.remove('show');
        icon.className = 'bi bi-chevron-down';
        text.textContent = 'Show Details';
      } else {
        detailsElement.classList.add('show');
        icon.className = 'bi bi-chevron-up';
        text.textContent = 'Hide Details';
        
        // Load simple detailed analysis
        const analysisContainer = document.getElementById(`detailed-analysis-${ratingId}`);
        if (analysisContainer.innerHTML.includes('Analyzing competency data') || analysisContainer.innerHTML.includes('Loading detailed analysis')) {
          loadSimpleDetailedAnalysis(ratingId, employeeId);
        }
      }
    }

    function loadSimpleDetailedAnalysis(ratingId, employeeId) {
      const analysisContainer = document.getElementById(`detailed-analysis-${ratingId}`);
      
      // Show loading state
      analysisContainer.innerHTML = `
        <div class="text-center py-3">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <div class="text-muted">Loading employee data...</div>
        </div>
      `;
      
      console.log('Loading analysis for employee:', employeeId);
      
      fetch(`/succession_readiness_ratings/competency-data/${employeeId}`)
        .then(response => {
          console.log('Response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
          }
          return response.json();
        })
        .then(data => {
          console.log('Received data:', data);
          if (data && typeof data === 'object') {
            analysisContainer.innerHTML = generateSimpleDetailedHTML(data);
          } else {
            throw new Error('Invalid data received');
          }
        })
        .catch(error => {
          console.error('Error loading analysis:', error);
          analysisContainer.innerHTML = `
            <div class="alert alert-warning mb-0">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Unable to load detailed analysis.</strong><br>
              <small class="text-muted">Employee ID: ${employeeId}</small><br>
              <small class="text-muted">Error: ${error.message}</small>
              <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary" onclick="loadSimpleDetailedAnalysis(${ratingId}, '${employeeId}')">
                  <i class="bi bi-arrow-clockwise me-1"></i>Retry
                </button>
              </div>
            </div>
          `;
        });
    }

    function generateSimpleDetailedHTML(data) {
      // Handle empty or invalid data
      if (!data || typeof data !== 'object') {
        return `
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>No detailed data available</strong><br>
            <small class="text-muted">Employee competency data not found in the system.</small>
          </div>
        `;
      }
      
      const proficiency = data.avg_proficiency_level || 0;
      const leadership = data.leadership_competencies_count || 0;
      const totalCompetencies = data.total_competencies_assessed || 0;
      const certificates = data.certificates_earned || 0;
      const yearsOfService = data.years_of_service || 0;
      const totalCourses = data.total_courses_assigned || 0;
      
      // Calculate training progress - use API value or calculate from certificates/courses
      let trainingProgress = data.training_progress || 0;
      if (trainingProgress === 0 && totalCourses > 0) {
        trainingProgress = Math.min(100, Math.round((certificates / totalCourses) * 100));
      } else if (trainingProgress === 0 && certificates > 0) {
        // If no courses assigned but has certificates, assume good progress
        trainingProgress = Math.min(100, certificates * 25); // 25% per certificate, max 100%
      }
      
      return `
        <div class="row g-4">
          <!-- Competency Section -->
          <div class="col-md-6">
            <div class="card border-success border-opacity-25 h-100">
              <div class="card-header bg-success bg-opacity-10 border-0">
                <h6 class="mb-0 text-success">
                  <i class="bi bi-graph-up me-2"></i>Competency Profile
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="text-muted">Proficiency Level</span>
                      <span class="fw-bold">${Math.round(proficiency)}/5</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                      <div class="progress-bar bg-success" style="width: ${(proficiency/5)*100}%"></div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                      <div class="h5 mb-0 text-primary">${leadership}</div>
                      <small class="text-muted">Leadership Skills</small>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                      <div class="h5 mb-0 text-info">${totalCompetencies}</div>
                      <small class="text-muted">Assessments</small>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-calendar-check text-secondary me-2"></i>
                      <span class="text-muted">Experience:</span>
                      <span class="fw-bold ms-2">${yearsOfService} years</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Training Section -->
          <div class="col-md-6">
            <div class="card border-warning border-opacity-25 h-100">
              <div class="card-header bg-warning bg-opacity-10 border-0">
                <h6 class="mb-0 text-warning">
                  <i class="bi bi-mortarboard me-2"></i>Training & Development
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="text-muted">Training Progress</span>
                      <span class="fw-bold">${Math.round(trainingProgress || 0)}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                      <div class="progress-bar bg-warning" style="width: ${Math.max(0, Math.min(100, trainingProgress || 0))}%"></div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                      <div class="h5 mb-0 text-success">${certificates}</div>
                      <small class="text-muted">Certificates</small>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="text-center p-2 bg-light rounded">
                      <div class="h5 mb-0 text-primary">${totalCourses}</div>
                      <small class="text-muted">Total Courses</small>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-trophy text-warning me-2"></i>
                      <span class="text-muted">Completion Rate:</span>
                      <span class="fw-bold ms-2">${totalCourses > 0 ? Math.min(100, Math.round((certificates / totalCourses) * 100)) : (certificates > 0 ? 100 : 0)}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Performance Summary -->
        <div class="card border-info border-opacity-25 mt-3">
          <div class="card-header bg-info bg-opacity-10 border-0">
            <h6 class="mb-0 text-info">
              <i class="bi bi-speedometer2 me-2"></i>Performance Summary
            </h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-3">
                <div class="p-2">
                  <div class="h6 mb-1 ${proficiency >= 4 ? 'text-success' : proficiency >= 3 ? 'text-warning' : 'text-danger'}">
                    ${proficiency >= 4 ? 'Excellent' : proficiency >= 3 ? 'Good' : 'Needs Improvement'}
                  </div>
                  <small class="text-muted">Competency Level</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-2">
                  <div class="h6 mb-1 ${leadership >= 3 ? 'text-success' : leadership >= 1 ? 'text-warning' : 'text-danger'}">
                    ${leadership >= 3 ? 'Strong' : leadership >= 1 ? 'Moderate' : 'Limited'}
                  </div>
                  <small class="text-muted">Leadership</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-2">
                  <div class="h6 mb-1 ${trainingProgress >= 80 ? 'text-success' : trainingProgress >= 50 ? 'text-warning' : 'text-danger'}">
                    ${trainingProgress >= 80 ? 'Excellent' : trainingProgress >= 50 ? 'Progressing' : 'Behind'}
                  </div>
                  <small class="text-muted">Training</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-2">
                  <div class="h6 mb-1 ${yearsOfService >= 3 ? 'text-success' : yearsOfService >= 1 ? 'text-warning' : 'text-danger'}">
                    ${yearsOfService >= 3 ? 'Experienced' : yearsOfService >= 1 ? 'Developing' : 'New'}
                  </div>
                  <small class="text-muted">Experience</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }


    function viewEmployeeDetails(employeeId) {
      // Analyze specific employee in individual analysis
      document.getElementById('analyzeEmployee').value = employeeId;
      analyzeEmployeeReadiness();
    }

    // Add Rating with Confirmation
    async function addRatingWithConfirmation() {
      const isVerified = await verifyAdminPassword();
      if (!isVerified) return;

      const form = document.querySelector('form');
      
      // Force correct form action if it's wrong
      if (form.action.includes('/admin/logout')) {
        console.log('WARNING: Form action was pointing to logout, fixing it...');
        form.action = '/admin/succession-readiness-ratings';
      }
      
      console.log('Form action URL:', form.action);
      console.log('Form method:', form.method);
      
      // Get form values directly from form elements
      const employeeIdHidden = document.querySelector('input[name="employee_id"]');
      const readinessInput = document.querySelector('input[name="readiness_level"]');
      const assessmentInput = document.querySelector('input[name="assessment_date"]');
      
      const employeeId = employeeIdHidden ? employeeIdHidden.value : '';
      const readinessLevel = readinessInput ? readinessInput.value : '';
      const assessmentDate = assessmentInput ? assessmentInput.value : '';

      console.log('Form validation values:', { employeeId, readinessLevel, assessmentDate });
      console.log('Form elements found:', { 
        employeeIdHidden: !!employeeIdHidden, 
        readinessInput: !!readinessInput, 
        assessmentInput: !!assessmentInput 
      });
      
      // Debug actual form element values
      if (employeeIdHidden) console.log('Employee ID hidden value:', employeeIdHidden.value);
      if (readinessInput) console.log('Readiness input value:', readinessInput.value);
      if (assessmentInput) console.log('Assessment input value:', assessmentInput.value);

      if (!employeeId || !readinessLevel || !assessmentDate) {
        Swal.fire({
          title: 'Validation Error',
          text: 'Please fill in all required fields.',
          icon: 'warning',
          confirmButtonText: 'OK',
          confirmButtonColor: '#ffc107'
        });
        return;
      }

      // Get employee name for confirmation
      const employeeDisplayInput = document.querySelector('input[name="employee_display"]');
      const employeeName = employeeDisplayInput ? employeeDisplayInput.value : 'Selected Employee';

      const result = await Swal.fire({
        title: '<i class="bi bi-plus-circle text-success"></i> Confirm Add Rating',
        html: `
          <div class="text-start">
            <p class="mb-3">Please confirm the readiness rating details:</p>
            <div class="card">
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-12">
                    <strong>Employee:</strong> ${employeeName}
                  </div>
                  <div class="col-12">
                    <strong>Readiness Level:</strong> 
                    <span class="badge ${readinessLevel === 'Ready Now' ? 'bg-success' : readinessLevel === 'Ready Soon' ? 'bg-warning' : 'bg-danger'} ms-1">
                      ${readinessLevel}
                    </span>
                  </div>
                  <div class="col-12">
                    <strong>Assessment Date:</strong> ${assessmentDate}
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        width: 600,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Add Rating',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
      });

      if (result.isConfirmed) {
        // Show processing
        Swal.fire({
          title: 'Processing...',
          html: 'Adding succession readiness rating...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Submit form via AJAX
        try {
          // Create FormData manually to ensure correct field names
          const formData = new FormData();
          
          // Add CSRF token
          formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
          
          // Add form fields manually
          formData.append('employee_id', employeeId);
          formData.append('readiness_level', readinessLevel);
          formData.append('assessment_date', assessmentDate);
          
          // Debug FormData contents
          console.log('Manual FormData contents:');
          for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
          }
          
          const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });

          if (!response.ok) {
            // For 422 validation errors, try to get the detailed error response
            if (response.status === 422) {
              try {
                const responseText = await response.text();
                console.log('422 Raw response:', responseText);
                
                // Try to parse as JSON
                const errorData = JSON.parse(responseText);
                console.log('Validation errors:', errorData);
                const errorMessages = errorData.errors ? Object.values(errorData.errors).flat().join(', ') : errorData.message;
                throw new Error(`Validation Error: ${errorMessages}`);
              } catch (parseError) {
                console.log('Failed to parse 422 response as JSON:', parseError);
                throw new Error(`HTTP 422: Validation failed - Response could not be parsed`);
              }
            }
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
          }

          const result = await response.json();

          if (response.ok && result.success) {
            Swal.fire({
              title: 'Success!',
              text: result.message || 'Succession readiness rating added successfully.',
              icon: 'success',
              confirmButtonText: 'OK',
              confirmButtonColor: '#198754'
            }).then(() => {
              // Reload page to show new data
              window.location.reload();
            });
          } else {
            throw new Error(result.message || 'Failed to add rating');
          }
        } catch (error) {
          console.error('Form submission error:', error);
          Swal.fire({
            title: 'Form Submission Error',
            html: `
              <div class="text-start">
                <p><strong>Error:</strong> ${error.message}</p>
                <p><strong>Action:</strong> Add Rating</p>
                <p><strong>Endpoint:</strong> ${form.action}</p>
                <p class="small text-muted">Please check the console for more details.</p>
              </div>
            `,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // Update Rating with Confirmation
    async function updateRatingWithConfirmation() {
      const isVerified = await verifyAdminPassword();
      if (!isVerified) return;

      const form = document.querySelector('form');
      const formData = new FormData(form);
      
      const result = await Swal.fire({
        title: '<i class="bi bi-pencil text-primary"></i> Confirm Update Rating',
        html: `
          <div class="text-start">
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Update Confirmation</strong><br>
              Are you sure you want to update this succession readiness rating?
            </div>
            <p class="mb-0">This action will modify the existing rating record.</p>
          </div>
        `,
        width: 500,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Update Rating',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d'
      });

      if (result.isConfirmed) {
        Swal.fire({
          title: 'Processing...',
          html: 'Updating succession readiness rating...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        try {
          const formData = new FormData(form);
          
          const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
          }

          const result = await response.json();

          if (response.ok && result.success) {
            Swal.fire({
              title: 'Success!',
              text: result.message || 'Succession readiness rating updated successfully.',
              icon: 'success',
              confirmButtonText: 'OK',
              confirmButtonColor: '#198754'
            }).then(() => {
              // Reload page to show updated data
              window.location.reload();
            });
          } else {
            throw new Error(result.message || 'Failed to update rating');
          }
        } catch (error) {
          console.error('Update submission error:', error);
          Swal.fire({
            title: 'Update Submission Error',
            html: `
              <div class="text-start">
                <p><strong>Error:</strong> ${error.message}</p>
                <p><strong>Action:</strong> Update Rating</p>
                <p><strong>Endpoint:</strong> ${form.action}</p>
                <p class="small text-muted">Please check the console for more details.</p>
              </div>
            `,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }

    // View Rating Details
    function viewRatingDetails(ratingId) {
      Swal.fire({
        title: '<i class="bi bi-eye text-info"></i> Loading Rating Details...',
        html: 'Please wait while we fetch the rating information...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Redirect to show route
      setTimeout(() => {
        window.location.href = `/admin/succession-readiness-ratings/${ratingId}`;
      }, 1000);
    }

    // Edit Rating with Confirmation
    async function editRatingWithConfirmation(ratingId) {
      const isVerified = await verifyAdminPassword();
      if (!isVerified) return;

      const result = await Swal.fire({
        title: '<i class="bi bi-pencil text-primary"></i> Edit Rating',
        html: `
          <div class="text-start">
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Edit Confirmation</strong><br>
              You are about to edit this succession readiness rating.
            </div>
            <p class="mb-0">This will take you to the edit form where you can modify the rating details.</p>
          </div>
        `,
        width: 500,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-pencil me-1"></i>Edit Rating',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d'
      });

      if (result.isConfirmed) {
        Swal.fire({
          title: 'Redirecting...',
          html: 'Taking you to the edit form...',
          timer: 1500,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        setTimeout(() => {
          window.location.href = `/admin/succession-readiness-ratings/${ratingId}/edit`;
        }, 1500);
      }
    }

    // Delete Rating with Confirmation
    async function deleteRatingWithConfirmation(ratingId) {
      const isVerified = await verifyAdminPassword();
      if (!isVerified) return;

      const result = await Swal.fire({
        title: '<i class="bi bi-trash text-danger"></i> Delete Rating',
        html: `
          <div class="text-start">
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Warning: Irreversible Action</strong><br>
              This action cannot be undone!
            </div>
            <p class="mb-3">Are you sure you want to permanently delete this succession readiness rating?</p>
            <div class="bg-light p-3 rounded">
              <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                This will remove all associated data and cannot be recovered.
              </small>
            </div>
          </div>
        `,
        width: 600,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Yes, Delete',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
      });

      if (result.isConfirmed) {
        Swal.fire({
          title: 'Deleting...',
          html: 'Removing succession readiness rating...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        try {
          // Create and submit delete form
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/succession-readiness-ratings/${ratingId}`;
          
          const csrfToken = document.createElement('input');
          csrfToken.type = 'hidden';
          csrfToken.name = '_token';
          csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          
          const methodField = document.createElement('input');
          methodField.type = 'hidden';
          methodField.name = '_method';
          methodField.value = 'DELETE';
          
          form.appendChild(csrfToken);
          form.appendChild(methodField);
          document.body.appendChild(form);
          form.submit();
        } catch (error) {
          Swal.fire({
            title: 'Error',
            text: 'Failed to delete rating. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }


    // Enhanced apply to form function with SweetAlert
    function applyToForm(employeeId, level) {
      console.log('Applying to form:', { employeeId, level });
      
      // Set employee ID and display name
      if (employeeId) {
        // Get employee name from the analyze dropdown
        const analyzeSelect = document.getElementById('analyzeEmployee');
        const selectedOption = analyzeSelect.options[analyzeSelect.selectedIndex];
        const employeeName = selectedOption.getAttribute('data-name');
        
        // Set display name in the visible input
        const employeeDisplayInput = document.querySelector('input[name="employee_display"]');
        if (employeeDisplayInput && employeeName) {
          employeeDisplayInput.value = `${employeeName} (${employeeId})`;
          console.log('Employee display name set to:', `${employeeName} (${employeeId})`);
        }
        
        // Set actual employee ID in the hidden field
        const employeeIdHidden = document.querySelector('input[name="employee_id"]');
        if (employeeIdHidden) {
          employeeIdHidden.value = employeeId;
          console.log('Employee ID value set to:', employeeId);
        }
      }
      
      // Set readiness level
      const readinessInput = document.querySelector('input[name="readiness_level"]');
      if (readinessInput && level) {
        readinessInput.value = level;
        console.log('Readiness level set to:', level);
        
        // Trigger change event to ensure any listeners are notified
        readinessInput.dispatchEvent(new Event('change'));
      }
      
      // Set assessment date to today
      const dateInput = document.querySelector('input[name="assessment_date"]');
      if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
        console.log('Assessment date set to:', today);
      }
      
      // Scroll to form
      const form = document.querySelector('form');
      if (form) {
        form.scrollIntoView({ behavior: 'smooth' });
      }
      
      // Notification removed as requested
    }

    // ========== SWEETALERT INTEGRATION FUNCTIONS ==========

    // Password verification function
    async function verifyAdminPassword() {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification',
        html: `
          <div class="text-start">
            <p class="mb-3">Please enter your admin password to proceed with this action.</p>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" id="admin-password" class="form-control" placeholder="Enter admin password">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                <i class="bi bi-eye" id="password-toggle-icon"></i>
              </button>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Verify',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const password = document.getElementById('admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Please enter your password');
            return false;
          }
          return password;
        }
      });

      if (password) {
        // For now, we'll accept any non-empty password
        // In a real implementation, you would verify against the actual admin password
        return true;
      }
      
      return false;
    }

    // Toggle password visibility
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('admin-password');
      const toggleIcon = document.getElementById('password-toggle-icon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
      }
    }

    // Enhanced AI Analysis with SweetAlert
  </script>
</body>
</html>
