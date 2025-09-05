<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
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
            action="{{ route('succession_readiness_ratings.update', $rating->id) }}"
          @else
            action="{{ route('succession_readiness_ratings.store') }}"
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
                <button class="btn btn-primary w-100">Update</button>
              @else
                <button class="btn btn-primary w-100">Add</button>
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
                <button class="btn btn-outline-primary btn-sm" onclick="runBatchAnalysis()">
                  <i class="bi bi-collection me-1"></i>Batch Analysis
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="generateReadinessReport()">
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
                <div class="mb-2">
                  <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} fs-6">
                    {{ $level }}
                  </span>
                </div>
                
                <!-- Reasoning Section -->
                <div class="readiness-reasoning" id="reasoning-{{ $rating->id }}">
                  <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    <span class="loading-text">Loading analysis...</span>
                  </div>
                </div>
                
                <!-- Toggle Details Button -->
                <button class="btn btn-sm btn-outline-secondary mt-2" type="button" 
                        onclick="toggleReadinessDetails({{ $rating->id }}, '{{ $rating->employee ? $rating->employee->employee_id : '' }}')">
                  <i class="bi bi-chevron-down"></i> <span>Show Details</span>
                </button>
                
                <!-- Expandable Details -->
                <div class="collapse mt-3" id="details-{{ $rating->id }}">
                  <div class="card card-body bg-light">
                    <div id="detailed-analysis-{{ $rating->id }}">
                      <div class="text-center py-2">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <small class="ms-2">Loading detailed analysis...</small>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
              <td>{{ $rating->assessment_date }}</td>
              <td class="text-center">
                <a href="{{ route('succession_readiness_ratings.show', $rating->id) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                  <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('succession_readiness_ratings.edit', $rating->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('succession_readiness_ratings.destroy', $rating->id) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this rating?')">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </form>
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
      
      fetch(`/employee_training_dashboard/readiness-score/${employeeId}`)
        .then(response => {
          console.log('API Response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          return response.json();
        })
        .then(trainingData => {
          console.log('Training data received:', trainingData);
          
          // Also get competency data for detailed analysis
          return fetch(`/succession_readiness_ratings/competency-data/${employeeId}`)
            .then(response => {
              if (!response.ok) {
                throw new Error(`Competency API HTTP ${response.status}: ${response.statusText}`);
              }
              return response.json();
            })
            .then(competencyData => {
              console.log('Competency data received:', competencyData);
              
              // Combine both data sources
              const combinedData = {
                ...competencyData,
                readiness_score: trainingData.readiness_score || 0,
                training_data: trainingData
              };
              console.log('Combined data:', combinedData);
              displaySimpleAnalysis(combinedData, employeeName);
            });
        })
        .catch(error => {
          console.error('API Error:', error);
          resultsContainer.innerHTML = `
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>API Error: ${error.message}</strong><br>
              Please check the console for more details.
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
      const container = document.getElementById('analysisResults');
      
      // Calculate readiness score using EXACT same algorithm as Employee Training Dashboard
      const proficiency = data.avg_proficiency_level || 0;
      const leadership = data.leadership_competencies_count || 0;
      const totalCompetencies = data.total_competencies_assessed || 0;
      const trainingProgress = data.training_progress || 0;
      const certificates = data.certificates_earned || 0;
      const totalCourses = data.total_courses || 0;
      const completedCourses = data.completed_courses || 0;
      
      // Calculate competency profile component (70% weight) - Additive approach that rewards growth
      let competencyProfileScore = 0;
      if (totalCompetencies > 0) {
        // NEW: Additive proficiency scoring - each competency contributes based on its individual level
        // This ensures adding new competencies ALWAYS increases the score
        // Since we only have average proficiency from API, calculate total points
        const totalProficiencyPoints = proficiency * totalCompetencies * 1.4; // Each level worth 1.4 points
        const proficiencyScore = Math.min(totalProficiencyPoints, 35); // Cap at 35%
        
        // Leadership score - based on actual leadership competencies, not ratio
        const leadershipScore = Math.min(leadership * 3, 20); // 3% per leadership competency, max 20%
        
        // Competency breadth - progressive scoring that rewards more competencies
        let competencyBreadthScore;
        if (totalCompetencies >= 20) {
          competencyBreadthScore = 15; // Full 15% for 20+ competencies
        } else if (totalCompetencies >= 10) {
          competencyBreadthScore = 10; // 10% for 10-19 competencies
        } else if (totalCompetencies >= 5) {
          competencyBreadthScore = 7; // 7% for 5-9 competencies
        } else {
          competencyBreadthScore = totalCompetencies * 1.5; // 1.5% per competency for 1-4
        }
        
        // Weighted average that rewards competency development
        competencyProfileScore = (proficiencyScore * 0.6) + (leadershipScore * 0.25) + (competencyBreadthScore * 0.15);
      }
      
      // Calculate training records component (30% weight)
      let trainingRecordsScore = 0;
      if (totalCourses > 0) {
        // Cap training progress at 15% to prevent inflation
        const trainingProgressScore = Math.min(trainingProgress * 0.15, 15);
        
        // Cap completion rate at 12%
        const completionRate = totalCourses > 0 ? (completedCourses / totalCourses) * 100 : 0;
        const completionRateScore = Math.min(completionRate * 0.12, 12);
        
        // Ultra-conservative assignment scoring - requires 50+ courses
        const assignmentScore = Math.min((totalCourses / 50) * 8, 8); // Max 8%
        
        // Ultra-conservative certificate scoring - requires 15+ certificates
        const certificateScore = certificates > 0 ? Math.min((certificates / 15) * 5, 5) : 0; // Max 5%
        
        // Ultra-conservative weighted average
        trainingRecordsScore = (trainingProgressScore * 0.6) + (completionRateScore * 0.25) + (assignmentScore * 0.1) + (certificateScore * 0.05);
      }
      
      // Final weighted calculation: 70% competency + 30% training (capped at 100%)
      let score = 0;
      if (totalCompetencies > 0 && totalCourses > 0) {
        // Both competency and training data available
        score = (competencyProfileScore * 0.70) + (trainingRecordsScore * 0.30);
      } else if (totalCompetencies > 0) {
        // Only competency data available
        score = competencyProfileScore;
      } else if (totalCourses > 0) {
        // Only training data available
        score = trainingRecordsScore;
      } else {
        // No data available
        score = 0; // Baseline score for employees with no data
      }
      
      // Ensure maximum is 100%
      score = Math.min(Math.round(score), 100);
      console.log(`AI Analysis Score: ${score}% for ${employeeName} (Competency: ${Math.round(competencyProfileScore)}%, Training: ${Math.round(trainingRecordsScore)}%)`);
      
      // Use backend readiness score for comparison
      if (data.readiness_score !== undefined) {
        console.log(`Employee Training Dashboard Score: ${Math.round(data.readiness_score)}% for comparison`);
      }
      
      // Determine readiness level
      let level, levelClass, timeline;
      if (score >= 70) {
        level = 'Ready Now';
        levelClass = 'success';
        timeline = 'Immediate';
      } else if (score >= 50) {
        level = 'Ready Soon';
        levelClass = 'warning';
        timeline = '3-6 months';
      } else {
        level = 'Needs Development';
        levelClass = 'danger';
        timeline = '6-12 months';
      }
      
      // Generate strengths and development areas
      const strengths = [];
      const developmentAreas = [];
      
      if (proficiency >= 4) strengths.push('High Competency Level');
      if (leadership >= 3) strengths.push('Leadership Skills');
      if (trainingProgress >= 80) strengths.push('Training Excellence');
      if (certificates >= 2) strengths.push('Professional Certifications');
      
      if (proficiency < 3) developmentAreas.push('Skill Development');
      if (leadership < 2) developmentAreas.push('Leadership Training');
      if (trainingProgress < 60) developmentAreas.push('Complete Training');
      if (certificates === 0) developmentAreas.push('Earn Certifications');
      
      if (strengths.length === 0) strengths.push('Ready for Assessment');
      if (developmentAreas.length === 0) developmentAreas.push('Continue Development');
      
      container.innerHTML = `
        <div class="card border-${levelClass}">
          <div class="card-header bg-${levelClass} bg-opacity-10">
            <h5 class="mb-0 text-${levelClass}">
              <i class="bi bi-person-check me-2"></i>${employeeName} - AI Analysis
              <span class="badge bg-success ms-2">Real Data</span>
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-3 text-center">
                <div class="display-6 text-${levelClass} fw-bold">${score}%</div>
                <small class="text-muted">Overall Readiness</small>
              </div>
              <div class="col-md-3 text-center">
                <div class="h4 text-${levelClass}">${level}</div>
                <small class="text-muted">Readiness Level</small>
              </div>
              <div class="col-md-3 text-center">
                <div class="h4 text-primary">${timeline}</div>
                <small class="text-muted">Timeline</small>
              </div>
              <div class="col-md-3 text-center">
                <div class="h4 text-info">${totalCompetencies}</div>
                <small class="text-muted">Competencies</small>
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
              <p class="mb-2">${getSimpleRecommendation(level)}</p>
              <button class="btn btn-primary btn-sm" onclick="applyToForm('${data.employee_id || ''}', '${level}')">
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
        .then(response => response.json())
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

      let icon, colorClass, reasoning;
      
      switch(readinessLevel) {
        case 'Ready Now':
          icon = 'bi-check-circle-fill';
          colorClass = 'text-success';
          reasoning = `Ready for succession: ${proficiency.toFixed(1)}/5.0 competency, ${leadership} leadership skills, ${Math.round(trainingProgress)}% training progress`;
          break;
        case 'Ready Soon':
          icon = 'bi-clock-fill';
          colorClass = 'text-warning';
          reasoning = `Good potential: ${proficiency.toFixed(1)}/5.0 competency, ${leadership} leadership skills, needs 3-6 months development`;
          break;
        case 'Needs Development':
          icon = 'bi-exclamation-triangle-fill';
          colorClass = 'text-danger';
          reasoning = `Requires development: ${proficiency.toFixed(1)}/5.0 competency, ${leadership} leadership skills, 6-12 months recommended`;
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
        if (analysisContainer.innerHTML.includes('Loading detailed analysis')) {
          loadSimpleDetailedAnalysis(ratingId, employeeId);
        }
      }
    }

    function loadSimpleDetailedAnalysis(ratingId, employeeId) {
      const analysisContainer = document.getElementById(`detailed-analysis-${ratingId}`);
      
      fetch(`/succession_readiness_ratings/competency-data/${employeeId}`)
        .then(response => response.json())
        .then(data => {
          analysisContainer.innerHTML = generateSimpleDetailedHTML(data);
        })
        .catch(error => {
          analysisContainer.innerHTML = `
            <div class="alert alert-danger mb-0">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Error loading analysis data.
            </div>
          `;
        });
    }

    function generateSimpleDetailedHTML(data) {
      const proficiency = data.avg_proficiency_level || 0;
      const leadership = data.leadership_competencies_count || 0;
      const totalCompetencies = data.total_competencies_assessed || 0;
      const trainingProgress = data.training_progress || 0;
      const certificates = data.certificates_earned || 0;
      
      return `
        <div class="row g-3">
          <div class="col-md-6">
            <h6 class="fw-bold mb-2"><i class="bi bi-graph-up me-1"></i>Competency Data</h6>
            <ul class="list-unstyled mb-0">
              <li><strong>Proficiency Level:</strong> ${proficiency.toFixed(1)}/5.0</li>
              <li><strong>Leadership Skills:</strong> ${leadership} competencies</li>
              <li><strong>Total Assessments:</strong> ${totalCompetencies} areas</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold mb-2"><i class="bi bi-book me-1"></i>Training & Certificates</h6>
            <ul class="list-unstyled mb-0">
              <li><strong>Training Progress:</strong> ${Math.round(trainingProgress)}%</li>
              <li><strong>Certificates Earned:</strong> ${certificates}</li>
              <li><strong>Total Courses:</strong> ${data.total_courses_assigned || 0}</li>
            </ul>
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
      
      // Simulate batch analysis data
      const batchResults = [
        { name: 'John Smith', id: 'EMP001', readiness: 85, level: 'Ready Now', risk: 'Low', potential: 'High' },
        { name: 'Sarah Johnson', id: 'EMP002', readiness: 72, level: 'Ready Soon', risk: 'Medium', potential: 'High' },
        { name: 'Mike Davis', id: 'EMP003', readiness: 68, level: 'Ready Soon', risk: 'Medium', potential: 'Medium' },
        { name: 'Lisa Wilson', id: 'EMP004', readiness: 45, level: 'Needs Development', risk: 'High', potential: 'Medium' },
        { name: 'Tom Brown', id: 'EMP005', readiness: 78, level: 'Ready Soon', risk: 'Low', potential: 'High' },
        { name: 'Anna Garcia', id: 'EMP006', readiness: 92, level: 'Ready Now', risk: 'Low', potential: 'High' }
      ];

      const summary = {
        total: batchResults.length,
        readyNow: batchResults.filter(r => r.level === 'Ready Now').length,
        readySoon: batchResults.filter(r => r.level === 'Ready Soon').length,
        needsDev: batchResults.filter(r => r.level === 'Needs Development').length,
        avgReadiness: Math.round(batchResults.reduce((sum, r) => sum + r.readiness, 0) / batchResults.length)
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
          <div class="col-md-3">
            <div class="card border-success border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-check-circle text-success display-6 mb-2"></i>
                <h6>Ready Now</h6>
                <div class="h4 text-success">${summary.readyNow}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-warning border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-clock text-warning display-6 mb-2"></i>
                <h6>Ready Soon</h6>
                <div class="h4 text-warning">${summary.readySoon}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-danger border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle text-danger display-6 mb-2"></i>
                <h6>Needs Development</h6>
                <div class="h4 text-danger">${summary.needsDev}</div>
                <small class="text-muted">Employees</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-primary border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-graph-up text-primary display-6 mb-2"></i>
                <h6>Average Readiness</h6>
                <div class="h4 text-primary">${summary.avgReadiness}%</div>
                <small class="text-muted">Overall Score</small>
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
                            <div class="progress-bar ${result.readiness >= 80 ? 'bg-success' : result.readiness >= 60 ? 'bg-warning' : 'bg-danger'}" 
                                 style="width: ${result.readiness}%"></div>
                          </div>
                          <span class="fw-bold">${result.readiness}%</span>
                        </div>
                      </td>
                      <td>
                        <span class="badge ${result.level === 'Ready Now' ? 'bg-success' : result.level === 'Ready Soon' ? 'bg-warning' : 'bg-danger'} bg-opacity-10 
                                      ${result.level === 'Ready Now' ? 'text-success' : result.level === 'Ready Soon' ? 'text-warning' : 'text-danger'}">
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
        
        <div class="alert alert-info mt-4">
          <h6><i class="bi bi-lightbulb me-2"></i>AI Recommendations</h6>
          <ul class="mb-0">
            <li>Focus development efforts on ${summary.needsDev} employees who need improvement</li>
            <li>Consider ${summary.readyNow} employees for immediate succession opportunities</li>
            <li>Create development plans for ${summary.readySoon} employees to accelerate readiness</li>
            <li>Overall succession pipeline health: ${summary.avgReadiness >= 70 ? 'Strong' : summary.avgReadiness >= 50 ? 'Moderate' : 'Needs Attention'}</li>
          </ul>
        </div>
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
      
      // Simulate AI insights data
      const insights = {
        trends: {
          readinessTrend: '+12%',
          competencyGrowth: '+8%',
          trainingCompletion: '+15%',
          leadershipDevelopment: '+6%'
        },
        predictions: [
          { metric: 'Succession Coverage', current: 75, predicted: 85, timeline: '6 months' },
          { metric: 'Leadership Pipeline', current: 60, predicted: 78, timeline: '12 months' },
          { metric: 'Skill Readiness', current: 68, predicted: 82, timeline: '9 months' }
        ],
        recommendations: [
          {
            priority: 'High',
            category: 'Leadership Development',
            action: 'Implement accelerated leadership program for top 5 candidates',
            impact: 'High',
            timeline: '3-6 months'
          },
          {
            priority: 'Medium',
            category: 'Skill Enhancement',
            action: 'Focus on technical skill development for mid-level employees',
            impact: 'Medium',
            timeline: '6-9 months'
          },
          {
            priority: 'Low',
            category: 'Training Optimization',
            action: 'Optimize training completion rates through gamification',
            impact: 'Medium',
            timeline: '9-12 months'
          }
        ]
      };

      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-info mb-3">
              <i class="bi bi-lightbulb me-2"></i>AI-Powered Succession Insights
            </h5>
          </div>
        </div>
        
        <!-- Trend Analysis -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-info border-opacity-25">
              <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 text-info">
                  <i class="bi bi-graph-up me-2"></i>Trend Analysis (Last 6 Months)
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 text-center">
                    <div class="h4 text-success">${insights.trends.readinessTrend}</div>
                    <small class="text-muted">Overall Readiness</small>
                  </div>
                  <div class="col-md-3 text-center">
                    <div class="h4 text-primary">${insights.trends.competencyGrowth}</div>
                    <small class="text-muted">Competency Growth</small>
                  </div>
                  <div class="col-md-3 text-center">
                    <div class="h4 text-warning">${insights.trends.trainingCompletion}</div>
                    <small class="text-muted">Training Completion</small>
                  </div>
                  <div class="col-md-3 text-center">
                    <div class="h4 text-info">${insights.trends.leadershipDevelopment}</div>
                    <small class="text-muted">Leadership Development</small>
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
                  <i class="bi bi-lightbulb me-2"></i>Strategic Recommendations
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
          <h6><i class="bi bi-check-circle me-2"></i>Summary</h6>
          <p class="mb-0">Your succession planning program shows positive trends with ${insights.trends.readinessTrend} improvement in overall readiness. Focus on the high-priority recommendations to maximize succession coverage and leadership pipeline strength.</p>
        </div>
      `;
    }

    function viewEmployeeDetails(employeeId) {
      // This would typically navigate to employee detail page or show modal
      alert(`Viewing details for employee: ${employeeId}`);
    }
  </script>
</body>
</html>
