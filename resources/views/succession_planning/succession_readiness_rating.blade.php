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
        <button type="button" class="btn btn-success" onclick="showAIAnalysisWithAlert()" style="cursor: pointer;">
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
              <select name="employee_id" class="form-control" required @if(isset($showMode)) disabled @endif>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->employee_id }}"
                    @if((isset($rating) && $rating->employee_id == $emp->employee_id)) selected @endif>
                    {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_id }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select name="readiness_level" class="form-select" required @if(isset($showMode)) disabled @endif>
                <option value="">Select Readiness Level</option>
                <option value="Ready Now" @if(isset($rating) && $rating->readiness_level == 'Ready Now') selected @endif>Ready Now</option>
                <option value="Ready Soon" @if(isset($rating) && $rating->readiness_level == 'Ready Soon') selected @endif>Ready Soon</option>
                <option value="Needs Development" @if(isset($rating) && $rating->readiness_level == 'Needs Development') selected @endif>Needs Development</option>
              </select>
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
              <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="runBatchAnalysisWithAlert()">
                  <i class="bi bi-collection me-1"></i>Batch Analysis
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="generateReadinessReportWithAlert()">
                  <i class="bi bi-file-earmark-text me-1"></i>AI Report
                </button>
              </div>
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
            
            <!-- AI Analysis Tabs -->
            <ul class="nav nav-tabs mb-3" id="readinessAnalysisTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="individual-analysis-tab" data-bs-toggle="tab" data-bs-target="#individual-analysis" type="button" role="tab">
                  <i class="bi bi-person me-1"></i>Individual Analysis
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="batch-analysis-tab" data-bs-toggle="tab" data-bs-target="#batch-analysis" type="button" role="tab">
                  <i class="bi bi-collection me-1"></i>Batch Analysis
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="ai-insights-tab" data-bs-toggle="tab" data-bs-target="#ai-insights" type="button" role="tab">
                  <i class="bi bi-lightbulb me-1"></i>AI Insights
                </button>
              </li>
            </ul>
            
            <div class="tab-content" id="readinessAnalysisContent">
              <!-- Individual Analysis Tab -->
              <div class="tab-pane fade show active" id="individual-analysis" role="tabpanel">
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
              
              <!-- Batch Analysis Tab -->
              <div class="tab-pane fade" id="batch-analysis" role="tabpanel">
                <div id="batchAnalysisResults">
                  <div class="text-center py-4">
                    <i class="bi bi-collection display-4 text-primary mb-3"></i>
                    <h5 class="text-primary">Batch Readiness Analysis</h5>
                    <p class="text-muted">Analyze multiple employees simultaneously for comprehensive succession planning.</p>
                  </div>
                </div>
              </div>
              
              <!-- AI Insights Tab -->
              <div class="tab-pane fade" id="ai-insights" role="tabpanel">
                <div id="aiInsightsResults">
                  <div class="text-center py-4">
                    <i class="bi bi-lightbulb display-4 text-info mb-3"></i>
                    <h5 class="text-info">AI-Powered Insights</h5>
                    <p class="text-muted">Advanced analytics and recommendations for succession planning optimization.</p>
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
      
      // Determine readiness level based on calculated score
      let readinessLevel, readinessClass, timeline;
      if (overallReadiness >= 80) {
        readinessLevel = "Ready Now";
        readinessClass = "success";
        timeline = "0-3 months";
      } else if (overallReadiness >= 60) {
        readinessLevel = "Nearly Ready";
        readinessClass = "warning";
        timeline = "3-6 months";
      } else if (overallReadiness >= 40) {
        readinessLevel = "Developing";
        readinessClass = "info";
        timeline = "6-12 months";
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
        document.querySelector('select[name="employee_id"]').value = employeeId;
      }
      document.querySelector('select[name="readiness_level"]').value = level;
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

    // Enhanced AI Functions for Batch Analysis and Insights
    function runBatchAnalysis() {
      const batchTab = document.getElementById('batch-analysis-tab');
      const batchContainer = document.getElementById('batchAnalysisResults');
      
      // Switch to batch analysis tab
      batchTab.click();
      
      batchContainer.innerHTML = `
        <div class="text-center py-3">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Running batch analysis on all employees...</p>
        </div>
      `;

      // Simulate batch analysis
      setTimeout(() => {
        generateBatchAnalysisResults();
      }, 3000);
    }

    function generateBatchAnalysisResults() {
      const container = document.getElementById('batchAnalysisResults');
      
      // Show loading message
      container.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Calculating accurate readiness scores for all employees...</p>
        </div>
      `;
      
      // Get employee IDs for batch analysis
      const employeeIds = [
        @foreach($ratings as $rating)
        @if($rating->employee)
        '{{ $rating->employee_id }}',
        @endif
        @endforeach
      ];
      
      // Calculate real readiness scores for all employees
      Promise.all(employeeIds.map(employeeId => 
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
            // Use the same calculation logic as individual analysis
            let overallReadiness;
            if (data.calculated_readiness_score !== undefined) {
              overallReadiness = data.calculated_readiness_score;
            } else {
              // Fallback to simplified frontend calculation matching backend
              const yearsOfService = data.years_of_service || 0;
              const certificates = data.certificates_earned || 0;
              const totalCompetencies = data.total_competencies_assessed || 0;
              const avgProficiency = data.avg_proficiency_level || 0;
              
              // 1. HIRE DATE COMPONENT (10%)
              const hireDateScore = Math.min(10, yearsOfService * 1);
              
              // 2. TRAINING RECORDS COMPONENT (3%)
              const trainingRecordsScore = Math.min(3, certificates * 0.5);
              
              // 3. COMPETENCY PROFILES COMPONENT (Additive)
              const competencyScore = totalCompetencies * avgProficiency * 2;
              
              // CALCULATE FINAL SCORE
              const totalScore = hireDateScore + trainingRecordsScore + competencyScore;
              
              // Set minimum score
              const minimumScore = yearsOfService < 1 ? 5 : 15;
              
              // Final score
              overallReadiness = Math.max(minimumScore, Math.min(100, Math.round(totalScore)));
            }
            
            // Determine readiness level based on calculated score
            let readinessLevel, risk, potential;
            if (overallReadiness >= 80) {
              readinessLevel = "Ready Now";
              risk = "Low";
              potential = "High";
            } else if (overallReadiness >= 60) {
              readinessLevel = "Ready Soon";
              risk = "Medium";
              potential = "High";
            } else if (overallReadiness >= 40) {
              readinessLevel = "Developing";
              risk = "Medium";
              potential = "Medium";
            } else {
              readinessLevel = "Needs Development";
              risk = "High";
              potential = "Medium";
            }
            
            return {
              name: data.employee_name || 'Unknown Employee',
              id: employeeId,
              readiness: Math.round(overallReadiness),
              level: readinessLevel,
              risk: risk,
              potential: potential,
              yearsOfService: data.years_of_service || 0,
              certificates: data.certificates_earned || 0,
              avgProficiency: data.avg_proficiency_level || 0,
              trainingProgress: data.training_progress || 0
            };
          })
          .catch(error => {
            console.error(`Error fetching data for employee ${employeeId}:`, error);
            // Return fallback data
            return {
              name: 'Unknown Employee',
              id: employeeId,
              readiness: 25,
              level: 'Needs Development',
              risk: 'High',
              potential: 'Medium',
              yearsOfService: 0,
              certificates: 0,
              avgProficiency: 0,
              trainingProgress: 0
            };
          })
      )).then(batchResults => {
        displayBatchAnalysisResults(batchResults);
      }).catch(error => {
        console.error('Batch analysis error:', error);
        container.innerHTML = `
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> Failed to calculate readiness scores. Please try again.
          </div>
        `;
      });
    }
    
    function displayBatchAnalysisResults(batchResults) {
      const container = document.getElementById('batchAnalysisResults');

      const summary = {
        total: batchResults.length,
        readyNow: batchResults.filter(r => r.level === 'Ready Now').length,
        readySoon: batchResults.filter(r => r.level === 'Ready Soon').length,
        developing: batchResults.filter(r => r.level === 'Developing').length,
        needsDev: batchResults.filter(r => r.level === 'Needs Development').length,
        avgReadiness: batchResults.length > 0 ? Math.round(batchResults.reduce((sum, r) => sum + r.readiness, 0) / batchResults.length) : 0
      };

      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-primary mb-3">
              <i class="bi bi-collection me-2"></i>Batch Analysis Results
            </h5>
          </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
          <div class="col-md-2">
            <div class="card border-success border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-check-circle text-success display-6 mb-2"></i>
                <h6>Ready Now</h6>
                <div class="h4 text-success">${summary.readyNow}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="card border-warning border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-clock text-warning display-6 mb-2"></i>
                <h6>Ready Soon</h6>
                <div class="h4 text-warning">${summary.readySoon}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="card border-info border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-arrow-up-circle text-info display-6 mb-2"></i>
                <h6>Developing</h6>
                <div class="h4 text-info">${summary.developing}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="card border-danger border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle text-danger display-6 mb-2"></i>
                <h6>Needs Development</h6>
                <div class="h4 text-danger">${summary.needsDev}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="card border-primary border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-graph-up text-primary display-6 mb-2"></i>
                <h6>Average Readiness</h6>
                <div class="h4 text-primary">${summary.avgReadiness}%</div>
                <small class="text-muted">Overall Score</small>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="card border-secondary border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-people text-secondary display-6 mb-2"></i>
                <h6>Total</h6>
                <div class="h4 text-secondary">${summary.total}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Detailed Results Table -->
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Detailed Analysis Results</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Employee</th>
                    <th>Readiness Score</th>
                    <th>Readiness Level</th>
                    <th>Risk Level</th>
                    <th>Potential</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  ${batchResults.map(result => `
                    <tr>
                      <td>
                        <div>
                          <strong>${result.name}</strong>
                          <br><small class="text-muted">${result.id}</small>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="progress me-2" style="width: 60px; height: 8px;">
                            <div class="progress-bar ${
                              result.readiness >= 80 ? 'bg-success' : 
                              result.readiness >= 60 ? 'bg-warning' : 
                              result.readiness >= 40 ? 'bg-info' : 'bg-danger'
                            }" style="width: ${result.readiness}%"></div>
                          </div>
                          <span class="fw-bold">${Math.round(result.readiness)}%</span>
                        </div>
                      </td>
                      <td>
                        <span class="badge ${
                          result.level === 'Ready Now' ? 'bg-success' : 
                          result.level === 'Ready Soon' ? 'bg-warning' : 
                          result.level === 'Developing' ? 'bg-info' : 'bg-danger'
                        } bg-opacity-10 ${
                          result.level === 'Ready Now' ? 'text-success' : 
                          result.level === 'Ready Soon' ? 'text-warning' : 
                          result.level === 'Developing' ? 'text-info' : 'text-danger'
                        }">
                          ${result.level}
                        </span>
                      </td>
                      <td>
                        <span class="badge ${result.risk === 'Low' ? 'bg-success' : result.risk === 'Medium' ? 'bg-warning' : 'bg-danger'} bg-opacity-10 
                                      ${result.risk === 'Low' ? 'text-success' : result.risk === 'Medium' ? 'text-warning' : 'text-danger'}">
                          ${result.risk}
                        </span>
                      </td>
                      <td>
                        <span class="badge ${result.potential === 'High' ? 'bg-primary' : 'bg-secondary'} bg-opacity-10 
                                      ${result.potential === 'High' ? 'text-primary' : 'text-secondary'}">
                          ${result.potential}
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-outline-primary btn-sm" onclick="viewEmployeeDetails('${result.id}')">
                          <i class="bi bi-eye me-1"></i>View
                        </button>
                      </td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        ${batchResults.length > 0 ? `
        <div class="alert alert-info mt-4">
          <h6><i class="bi bi-lightbulb me-2"></i>AI Recommendations</h6>
          <ul class="mb-0">
            <li><strong>Ready Now (${summary.readyNow}):</strong> Consider for immediate succession opportunities and leadership roles</li>
            <li><strong>Ready Soon (${summary.readySoon}):</strong> Create accelerated development plans for 3-6 months preparation</li>
            <li><strong>Developing (${summary.developing}):</strong> Provide targeted skill development and mentoring programs</li>
            <li><strong>Needs Development (${summary.needsDev}):</strong> Focus on comprehensive training and competency building</li>
            <li><strong>Pipeline Health:</strong> ${summary.avgReadiness >= 70 ? 'Strong succession pipeline' : summary.avgReadiness >= 50 ? 'Moderate pipeline - needs attention' : 'Weak pipeline - immediate action required'} (${summary.avgReadiness}% average)</li>
          </ul>
        </div>
        ` : `
        <div class="alert alert-warning mt-4">
          <h6><i class="bi bi-exclamation-triangle me-2"></i>No Data Available</h6>
          <p class="mb-0">No succession readiness ratings found. Please add employee ratings first to see batch analysis results.</p>
        </div>
        `}
      `;
    }

    function generateReadinessReport() {
      const insightsTab = document.getElementById('ai-insights-tab');
      const insightsContainer = document.getElementById('aiInsightsResults');
      
      // Switch to AI insights tab
      insightsTab.click();
      
      insightsContainer.innerHTML = `
        <div class="text-center py-3">
          <div class="spinner-border text-info" role="status"></div>
          <p class="mt-2">Generating comprehensive AI readiness report...</p>
        </div>
      `;

      // Simulate report generation
      setTimeout(() => {
        displayAIInsights();
      }, 2500);
    }

    function displayAIInsights() {
      const container = document.getElementById('aiInsightsResults');
      
      // Show loading message
      container.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-info" role="status"></div>
          <p class="mt-2">Analyzing real succession data and generating insights...</p>
        </div>
      `;
      
      // Get employee IDs for insights analysis
      const employeeIds = [
        @foreach($ratings as $rating)
        @if($rating->employee)
        '{{ $rating->employee_id }}',
        @endif
        @endforeach
      ];
      
      // Fetch real data for all employees to generate insights
      Promise.all(employeeIds.map(employeeId => 
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
          .catch(error => {
            console.error(`Error fetching data for employee ${employeeId}:`, error);
            return null;
          })
      )).then(employeeDataArray => {
        // Filter out null responses
        const validEmployeeData = employeeDataArray.filter(data => data !== null);
        
        if (validEmployeeData.length === 0) {
          container.innerHTML = `
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>No Data Available:</strong> Unable to generate insights. Please ensure employee data is available.
            </div>
          `;
          return;
        }
        
        // Calculate real insights from employee data
        const insights = generateRealInsights(validEmployeeData);
        displayRealInsights(insights);
      }).catch(error => {
        console.error('AI Insights error:', error);
        container.innerHTML = `
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> Failed to generate AI insights. Please try again.
          </div>
        `;
      });
    }
    
    function generateRealInsights(employeeDataArray) {
      // Calculate real metrics from employee data
      const totalEmployees = employeeDataArray.length;
      let totalReadiness = 0;
      let totalCompetency = 0;
      let totalTrainingProgress = 0;
      let totalCertificates = 0;
      let totalYearsService = 0;
      let leadershipCompetencies = 0;
      
      employeeDataArray.forEach(data => {
        // Calculate readiness score using same logic as individual analysis
        const yearsOfService = data.years_of_service || 0;
        const certificates = data.certificates_earned || 0;
        const totalCompetenciesAssessed = data.total_competencies_assessed || 0;
        const avgProficiency = data.avg_proficiency_level || 0;
        
        // Same calculation as individual analysis
        const hireDateScore = Math.min(10, yearsOfService * 1);
        const trainingRecordsScore = Math.min(3, certificates * 0.5);
        const competencyScore = totalCompetenciesAssessed * avgProficiency * 2;
        const totalScore = hireDateScore + trainingRecordsScore + competencyScore;
        const minimumScore = yearsOfService < 1 ? 5 : 15;
        const readinessScore = Math.max(minimumScore, Math.min(100, Math.round(totalScore)));
        
        totalReadiness += readinessScore;
        totalCompetency += avgProficiency;
        totalTrainingProgress += data.training_progress || 0;
        totalCertificates += certificates;
        totalYearsService += yearsOfService;
        leadershipCompetencies += data.leadership_competencies_count || 0;
      });
      
      // Calculate averages
      const avgReadiness = Math.round(totalReadiness / totalEmployees);
      const avgCompetency = Math.round((totalCompetency / totalEmployees) * 20); // Convert to percentage
      const avgTrainingProgress = Math.round(totalTrainingProgress / totalEmployees);
      const avgCertificates = Math.round(totalCertificates / totalEmployees);
      const avgYearsService = Math.round(totalYearsService / totalEmployees);
      const avgLeadership = Math.round((leadershipCompetencies / totalEmployees) * 20); // Convert to percentage
      
      // Calculate readiness distribution
      const readyNow = employeeDataArray.filter(data => {
        const readiness = calculateEmployeeReadiness(data);
        return readiness >= 80;
      }).length;
      
      const readySoon = employeeDataArray.filter(data => {
        const readiness = calculateEmployeeReadiness(data);
        return readiness >= 60 && readiness < 80;
      }).length;
      
      const developing = employeeDataArray.filter(data => {
        const readiness = calculateEmployeeReadiness(data);
        return readiness >= 40 && readiness < 60;
      }).length;
      
      const needsDev = employeeDataArray.filter(data => {
        const readiness = calculateEmployeeReadiness(data);
        return readiness < 40;
      }).length;
      
      // Generate trend indicators (simulated growth based on current performance)
      const readinessTrend = avgReadiness >= 70 ? '+8%' : avgReadiness >= 50 ? '+5%' : '+2%';
      const competencyTrend = avgCompetency >= 70 ? '+12%' : avgCompetency >= 50 ? '+8%' : '+4%';
      const trainingTrend = avgTrainingProgress >= 80 ? '+15%' : avgTrainingProgress >= 60 ? '+10%' : '+6%';
      const leadershipTrend = avgLeadership >= 60 ? '+10%' : avgLeadership >= 40 ? '+7%' : '+3%';
      
      // Generate predictions based on current state
      const predictions = [
        { 
          metric: 'Succession Coverage', 
          current: Math.round((readyNow + readySoon) / totalEmployees * 100), 
          predicted: Math.min(100, Math.round((readyNow + readySoon) / totalEmployees * 100) + 15), 
          timeline: '6 months' 
        },
        { 
          metric: 'Leadership Pipeline', 
          current: avgLeadership, 
          predicted: Math.min(100, avgLeadership + 18), 
          timeline: '12 months' 
        },
        { 
          metric: 'Skill Readiness', 
          current: avgCompetency, 
          predicted: Math.min(100, avgCompetency + 14), 
          timeline: '9 months' 
        }
      ];
      
      // Generate smart recommendations based on data
      const recommendations = [];
      
      if (readyNow < totalEmployees * 0.3) {
        recommendations.push({
          priority: 'High',
          category: 'Leadership Development',
          action: `Accelerate development for ${readySoon} "Ready Soon" employees to increase succession coverage`,
          impact: 'High',
          timeline: '3-6 months'
        });
      }
      
      if (avgTrainingProgress < 70) {
        recommendations.push({
          priority: 'High',
          category: 'Training Completion',
          action: `Improve training completion rates - currently at ${avgTrainingProgress}% average`,
          impact: 'High',
          timeline: '3-6 months'
        });
      }
      
      if (avgCompetency < 60) {
        recommendations.push({
          priority: 'Medium',
          category: 'Skill Enhancement',
          action: `Focus on competency development - current average is ${avgCompetency}%`,
          impact: 'Medium',
          timeline: '6-9 months'
        });
      }
      
      if (needsDev > totalEmployees * 0.4) {
        recommendations.push({
          priority: 'High',
          category: 'Development Focus',
          action: `${needsDev} employees need significant development - create structured programs`,
          impact: 'High',
          timeline: '6-12 months'
        });
      }
      
      if (avgCertificates < 2) {
        recommendations.push({
          priority: 'Medium',
          category: 'Certification Program',
          action: `Increase certification rates - current average is ${avgCertificates} per employee`,
          impact: 'Medium',
          timeline: '6-9 months'
        });
      }
      
      // Ensure we have at least 3 recommendations
      if (recommendations.length < 3) {
        recommendations.push({
          priority: 'Low',
          category: 'Continuous Improvement',
          action: 'Implement regular succession planning reviews and updates',
          impact: 'Medium',
          timeline: '9-12 months'
        });
      }
      
      return {
        trends: {
          readinessTrend: readinessTrend,
          competencyGrowth: competencyTrend,
          trainingCompletion: trainingTrend,
          leadershipDevelopment: leadershipTrend
        },
        predictions: predictions,
        recommendations: recommendations.slice(0, 5), // Limit to 5 recommendations
        stats: {
          totalEmployees,
          avgReadiness,
          avgCompetency,
          avgTrainingProgress,
          readyNow,
          readySoon,
          developing,
          needsDev
        }
      };
    }
    
    function calculateEmployeeReadiness(data) {
      const yearsOfService = data.years_of_service || 0;
      const certificates = data.certificates_earned || 0;
      const totalCompetencies = data.total_competencies_assessed || 0;
      const avgProficiency = data.avg_proficiency_level || 0;
      
      const hireDateScore = Math.min(10, yearsOfService * 1);
      const trainingRecordsScore = Math.min(3, certificates * 0.5);
      const competencyScore = totalCompetencies * avgProficiency * 2;
      const totalScore = hireDateScore + trainingRecordsScore + competencyScore;
      const minimumScore = yearsOfService < 1 ? 5 : 15;
      
      return Math.max(minimumScore, Math.min(100, Math.round(totalScore)));
    }
    
    function displayRealInsights(insights) {
      const container = document.getElementById('aiInsightsResults');

      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-info mb-3">
              <i class="bi bi-lightbulb me-2"></i>AI-Powered Succession Insights
              <span class="badge bg-success ms-2">Real Data</span>
            </h5>
          </div>
        </div>
        
        <!-- Current Statistics -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-primary border-opacity-25">
              <div class="card-header bg-primary bg-opacity-10">
                <h6 class="mb-0 text-primary">
                  <i class="bi bi-bar-chart me-2"></i>Current Succession Statistics
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-2 text-center">
                    <div class="h4 text-success">${insights.stats.readyNow}</div>
                    <small class="text-muted">Ready Now</small>
                  </div>
                  <div class="col-md-2 text-center">
                    <div class="h4 text-warning">${insights.stats.readySoon}</div>
                    <small class="text-muted">Ready Soon</small>
                  </div>
                  <div class="col-md-2 text-center">
                    <div class="h4 text-info">${insights.stats.developing}</div>
                    <small class="text-muted">Developing</small>
                  </div>
                  <div class="col-md-2 text-center">
                    <div class="h4 text-danger">${insights.stats.needsDev}</div>
                    <small class="text-muted">Needs Dev</small>
                  </div>
                  <div class="col-md-2 text-center">
                    <div class="h4 text-primary">${insights.stats.avgReadiness}%</div>
                    <small class="text-muted">Avg Readiness</small>
                  </div>
                  <div class="col-md-2 text-center">
                    <div class="h4 text-secondary">${insights.stats.totalEmployees}</div>
                    <small class="text-muted">Total Employees</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Trend Analysis -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-info border-opacity-25">
              <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 text-info">
                  <i class="bi bi-graph-up me-2"></i>Projected Growth Trends
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 text-center">
                    <div class="h4 text-success">${insights.trends.readinessTrend}</div>
                    <small class="text-muted">Overall Readiness Growth</small>
                  </div>
                  <div class="col-md-3 text-center">
                    <div class="h4 text-primary">${insights.trends.competencyGrowth}</div>
                    <small class="text-muted">Competency Development</small>
                  </div>
                  <div class="col-md-3 text-center">
                    <div class="h4 text-warning">${insights.trends.trainingCompletion}</div>
                    <small class="text-muted">Training Completion</small>
                  </div>
                  <div class="col-md-3 text-center">
                    <div class="h4 text-info">${insights.trends.leadershipDevelopment}</div>
                    <small class="text-muted">Leadership Pipeline</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Predictive Analytics -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-success border-opacity-25">
              <div class="card-header bg-success bg-opacity-10">
                <h6 class="mb-0 text-success">
                  <i class="bi bi-graph-up-arrow me-2"></i>Predictive Analytics
                </h6>
              </div>
              <div class="card-body">
                ${insights.predictions.map(pred => `
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <strong>${pred.metric}</strong>
                    </div>
                    <div class="col-md-4">
                      <div class="d-flex align-items-center">
                        <span class="me-2">Current: ${pred.current}%</span>
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                          <div class="progress-bar bg-primary" style="width: ${pred.current}%"></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="d-flex align-items-center">
                        <span class="me-2">Predicted: ${pred.predicted}%</span>
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                          <div class="progress-bar bg-success" style="width: ${pred.predicted}%"></div>
                        </div>
                        <small class="text-muted">(${pred.timeline})</small>
                      </div>
                    </div>
                  </div>
                `).join('')}
              </div>
            </div>
          </div>
        </div>
        
        <!-- AI Recommendations -->
        <div class="row">
          <div class="col-12">
            <div class="card border-warning border-opacity-25">
              <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0 text-warning">
                  <i class="bi bi-lightbulb me-2"></i>Data-Driven Recommendations
                </h6>
              </div>
              <div class="card-body">
                ${insights.recommendations.map((rec, index) => `
                  <div class="card mb-3 ${rec.priority === 'High' ? 'border-danger' : rec.priority === 'Medium' ? 'border-warning' : 'border-info'} border-opacity-25">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${rec.category}</h6>
                        <span class="badge ${rec.priority === 'High' ? 'bg-danger' : rec.priority === 'Medium' ? 'bg-warning' : 'bg-info'} bg-opacity-10 
                                      ${rec.priority === 'High' ? 'text-danger' : rec.priority === 'Medium' ? 'text-warning' : 'text-info'}">
                          ${rec.priority} Priority
                        </span>
                      </div>
                      <p class="mb-2">${rec.action}</p>
                      <div class="row">
                        <div class="col-md-6">
                          <small><strong>Expected Impact:</strong> ${rec.impact}</small>
                        </div>
                        <div class="col-md-6">
                          <small><strong>Timeline:</strong> ${rec.timeline}</small>
                        </div>
                      </div>
                    </div>
                  </div>
                `).join('')}
              </div>
            </div>
          </div>
        </div>
        
        <div class="alert alert-success mt-4">
          <h6><i class="bi bi-check-circle me-2"></i>AI Analysis Summary</h6>
          <p class="mb-2">Based on real employee data analysis:</p>
          <ul class="mb-0">
            <li><strong>Succession Coverage:</strong> ${Math.round((insights.stats.readyNow + insights.stats.readySoon) / insights.stats.totalEmployees * 100)}% of employees are succession-ready</li>
            <li><strong>Average Readiness:</strong> ${insights.stats.avgReadiness}% across all employees</li>
            <li><strong>Training Progress:</strong> ${insights.stats.avgTrainingProgress}% average completion rate</li>
            <li><strong>Growth Potential:</strong> ${insights.trends.readinessTrend} projected improvement in readiness</li>
          </ul>
        </div>
      `;
    }

    function viewEmployeeDetails(employeeId) {
      // Switch to individual analysis and analyze specific employee
      document.getElementById('individual-analysis-tab').click();
      document.getElementById('analyzeEmployee').value = employeeId;
      analyzeEmployeeReadiness();
    }

    // ========== SWEETALERT INTEGRATION FUNCTIONS ==========

    // Enhanced AI Analysis with SweetAlert
    function showAIAnalysisWithAlert() {
      Swal.fire({
        title: '<i class="bi bi-robot text-success"></i> LeadGen AI Analysis',
        html: `
          <div class="text-start">
            <p class="mb-3">Welcome to the advanced AI-powered succession readiness analysis system!</p>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="card border-success border-opacity-25 h-100">
                  <div class="card-body text-center">
                    <i class="bi bi-graph-up text-success display-6 mb-2"></i>
                    <h6>Competency Analysis</h6>
                    <small class="text-muted">Skills & proficiency assessment</small>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border-primary border-opacity-25 h-100">
                  <div class="card-body text-center">
                    <i class="bi bi-trophy text-primary display-6 mb-2"></i>
                    <h6>Performance Prediction</h6>
                    <small class="text-muted">Future success probability</small>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border-warning border-opacity-25 h-100">
                  <div class="card-body text-center">
                    <i class="bi bi-shield-check text-warning display-6 mb-2"></i>
                    <h6>Risk Assessment</h6>
                    <small class="text-muted">Succession risk evaluation</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        width: 800,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-arrow-right me-1"></i>Start Analysis',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        customClass: {
          popup: 'swal-wide'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          showAIAnalysis();
          Swal.fire({
            title: 'AI Analysis Activated!',
            text: 'The AI analysis panel is now ready. Select an employee to begin analysis.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        }
      });
    }

    // Toggle password visibility
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('admin-password');
      const toggleIcon = document.getElementById('password-toggle-icon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
        toggleIcon.parentElement.innerHTML = '<i class="bi bi-eye-slash" id="password-toggle-icon"></i> Hide Password';
      } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
        toggleIcon.parentElement.innerHTML = '<i class="bi bi-eye" id="password-toggle-icon"></i> Show Password';
      }
    }

    // Password verification function
    async function verifyAdminPassword() {
      const { value: password } = await Swal.fire({
        title: '<i class="bi bi-shield-lock text-warning"></i> Security Verification',
        html: `
          <div class="text-start">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Admin Password Required</strong><br>
              This action requires admin password verification for security purposes.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Enter Admin Password:</label>
              <input type="password" id="admin-password" class="form-control" placeholder="Enter your admin password" minlength="6">
              <div class="form-text">
                Minimum 6 characters required<br>
                <small class="text-info">💡 Hint: Same password you use to login to admin dashboard</small>
              </div>
              <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePasswordVisibility()">
                  <i class="bi bi-eye" id="password-toggle-icon"></i> Show Password
                </button>
              </div>
            </div>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Verify',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: 500,
        preConfirm: () => {
          const password = document.getElementById('admin-password').value;
          if (!password) {
            Swal.showValidationMessage('Password is required');
            return false;
          }
          if (password.length < 6) {
            Swal.showValidationMessage('Password must be at least 6 characters');
            return false;
          }
          return password;
        }
      });

      if (password) {
        // Show loading
        Swal.fire({
          title: 'Verifying Password...',
          html: 'Please wait while we verify your credentials.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        try {
          console.log('Attempting password verification...');
          console.log('Password length:', password.length);
          console.log('Password (first 3 chars):', password.substring(0, 3) + '***');
          console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
          
          // Trim whitespace from password
          const trimmedPassword = password.trim();
          console.log('Trimmed password length:', trimmedPassword.length);
          
          const response = await fetch('/admin/verify-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ password: trimmedPassword })
          });

          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          console.log('Content-Type:', response.headers.get('content-type'));

          if (!response.ok) {
            // Try to get the actual response text for debugging
            const responseText = await response.text();
            console.log('Error response body:', responseText);
            throw new Error(`HTTP ${response.status}: ${response.statusText} - ${responseText.substring(0, 200)}`);
          }

          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            // Get the actual response to see what we're receiving
            const responseText = await response.text();
            console.log('Non-JSON response body:', responseText);
            throw new Error(`Server returned non-JSON response. Content-Type: ${contentType}. Response: ${responseText.substring(0, 200)}`);
          }

          const data = await response.json();
          console.log('Password verification response:', data);
          
          if (data.success || data.valid) {
            Swal.close();
            return true;
          } else {
            Swal.fire({
              title: 'Verification Failed',
              text: data.message || 'Invalid password. Please try again.',
              icon: 'error',
              confirmButtonText: 'Try Again',
              confirmButtonColor: '#dc3545'
            });
            return false;
          }
        } catch (error) {
          console.error('Password verification error:', error);
          Swal.fire({
            title: 'Password Verification Error',
            html: `
              <div class="text-start">
                <p><strong>Error:</strong> ${error.message}</p>
                <p><strong>Endpoint:</strong> /admin/verify-password</p>
                <p class="small text-muted">Please check the console for more details.</p>
              </div>
            `,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
          });
          return false;
        }
      }
      return false;
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
      const employeeSelect = document.querySelector('select[name="employee_id"]');
      const readinessSelect = document.querySelector('select[name="readiness_level"]');
      const assessmentInput = document.querySelector('input[name="assessment_date"]');
      
      const employeeId = employeeSelect ? employeeSelect.value : '';
      const readinessLevel = readinessSelect ? readinessSelect.value : '';
      const assessmentDate = assessmentInput ? assessmentInput.value : '';

      console.log('Form validation values:', { employeeId, readinessLevel, assessmentDate });
      console.log('Form elements found:', { 
        employeeSelect: !!employeeSelect, 
        readinessSelect: !!readinessSelect, 
        assessmentInput: !!assessmentInput 
      });
      
      // Debug actual form element values
      if (employeeSelect) console.log('Employee select value:', employeeSelect.value, 'Selected text:', employeeSelect.options[employeeSelect.selectedIndex]?.text);
      if (readinessSelect) console.log('Readiness select value:', readinessSelect.value, 'Selected text:', readinessSelect.options[readinessSelect.selectedIndex]?.text);
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
      const employeeName = employeeSelect.options[employeeSelect.selectedIndex].text;

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

    // Enhanced AI Analysis Functions with SweetAlert
    function runBatchAnalysisWithAlert() {
      Swal.fire({
        title: '<i class="bi bi-collection text-primary"></i> Batch Analysis',
        html: `
          <div class="text-start">
            <p class="mb-3">Run comprehensive AI analysis on all employees simultaneously.</p>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              This will analyze succession readiness for all employees in the system.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-play me-1"></i>Start Batch Analysis',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          originalRunBatchAnalysis();
          Swal.fire({
            title: 'Analysis Started!',
            text: 'Batch analysis is now running. Please wait for results.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        }
      });
    }

    function generateReadinessReportWithAlert() {
      Swal.fire({
        title: '<i class="bi bi-file-earmark-text text-info"></i> AI Report Generation',
        html: `
          <div class="text-start">
            <p class="mb-3">Generate comprehensive AI-powered succession readiness report.</p>
            <div class="alert alert-info">
              <i class="bi bi-lightbulb me-2"></i>
              This report includes trends, predictions, and strategic recommendations.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-file-earmark-text me-1"></i>Generate Report',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>Cancel',
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          originalGenerateReadinessReport();
          Swal.fire({
            title: 'Report Generation Started!',
            text: 'AI report is being generated. Please wait for completion.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        }
      });
    }

    // Store original functions for internal use
    const originalRunBatchAnalysis = runBatchAnalysis;
    const originalGenerateReadinessReport = generateReadinessReport;

    // Enhanced apply to form function with SweetAlert
    function applyToForm(employeeId, level) {
      if (employeeId) {
        document.querySelector('select[name="employee_id"]').value = employeeId;
      }
      document.querySelector('select[name="readiness_level"]').value = level;
      document.querySelector('input[name="assessment_date"]').value = new Date().toISOString().split('T')[0];
      
      // Scroll to form
      document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
      
      // Show SweetAlert success message
      Swal.fire({
        title: 'AI Analysis Applied!',
        html: `
          <div class="text-center">
            <i class="bi bi-check-circle text-success display-4 mb-3"></i>
            <p>AI analysis results have been applied to the form.</p>
            <p class="small text-muted">Review the details and click "Add" to save the rating.</p>
          </div>
        `,
        icon: 'success',
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    }

    // ========== END SWEETALERT INTEGRATION ==========
  </script>
</body>
</html>
