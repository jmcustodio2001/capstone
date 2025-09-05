<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Potential Successor Identification</title>
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
            <h2 class="fw-bold mb-1">Potential Successor Identification</h2>
            <p class="text-muted mb-0">
              Welcome back, Admin! Identify successors by analyzing current competency profiles to match potential with future leadership roles.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employee_competency_profiles.index') }}" class="text-decoration-none">Competency Management</a></li>
            <li class="breadcrumb-item active" aria-current="page">Potential Successors</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Form Section -->
    @if(isset($editMode) && $editMode && isset($successor))
      <!-- Edit Form -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="fw-bold mb-0">Edit Successor</h4>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('potential_successors.update', $successor->id) }}">
            @csrf
            @method('PUT')
            <div class="row">
              <div class="col-md-4">
                <select name="employee_id" class="form-control" required>
                  <option value="">Select Employee</option>
                  @foreach($employees as $emp)
                    <option value="{{ $emp->employee_id }}" {{ $successor->employee_id == $emp->employee_id ? 'selected' : '' }}>
                      {{ $emp->first_name ?? 'Unknown' }} {{ $emp->last_name ?? 'Employee' }} ({{ $emp->employee_id }})
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <input type="text" name="potential_role" class="form-control" value="{{ $successor->potential_role }}" required>
              </div>
              <div class="col-md-3">
                <input type="date" name="identified_date" class="form-control" value="{{ $successor->identified_date }}" required>
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-save"></i> Update
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    @elseif(isset($showMode) && $showMode && isset($successor))
      <!-- View Section -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">Potential Successor Details</h4>
        </div>
        <div class="card-body">
          <dl class="row">
            <dt class="col-sm-3">Employee</dt>
            <dd class="col-sm-9">
              @php
                // Initialize default values
                $employeeName = 'No Employee Assigned';
                $employeeId = 'N/A';
                
                // Check if successor exists and is not null
                if (isset($successor) && $successor !== null && is_object($successor)) {
                    // Get employee ID safely
                    if (property_exists($successor, 'employee_id') && $successor->employee_id) {
                        $employeeId = $successor->employee_id;
                    }
                    
                    // Get employee name safely
                    if (property_exists($successor, 'employee') && $successor->employee !== null && is_object($successor->employee)) {
                        $emp = $successor->employee;
                        $fname = property_exists($emp, 'first_name') && $emp->first_name ? $emp->first_name : 'Unknown';
                        $lname = property_exists($emp, 'last_name') && $emp->last_name ? $emp->last_name : 'Employee';
                        $employeeName = trim($fname . ' ' . $lname);
                    }
                }
              @endphp
              {{ $employeeName }} ({{ $employeeId }})
            </dd>
            <dt class="col-sm-3">Potential Role</dt>
            <dd class="col-sm-9">{{ (isset($successor) && $successor !== null && is_object($successor) && property_exists($successor, 'potential_role')) ? $successor->potential_role : 'N/A' }}</dd>
            <dt class="col-sm-3">Identified Date</dt>
            <dd class="col-sm-9">{{ (isset($successor) && $successor !== null && is_object($successor) && property_exists($successor, 'identified_date')) ? $successor->identified_date : 'N/A' }}</dd>
          </dl>
          <a href="{{ route('potential_successors.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
      </div>
    @else
      <!-- Add Form (default) -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="fw-bold mb-0">Add Successor Identification</h4>
          <button type="button" class="btn btn-success" onclick="autoGenerateSuggestions()">
            <i class="bi bi-robot me-1"></i>LeadGen AI
          </button>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('potential_successors.store') }}">
            @csrf
            <div class="row">
              <div class="col-md-4">
                <select name="employee_id" class="form-control" required>
                  <option value="">Select Employee</option>
                  @foreach($employees as $emp)
                    <option value="{{ $emp->employee_id }}">{{ $emp->first_name ?? 'Unknown' }} {{ $emp->last_name ?? 'Employee' }} ({{ $emp->employee_id }})</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <select name="potential_role" class="form-select" required>
                  <option value="">Select Potential Role</option>
                  <option value="Travel Consultant">Travel Consultant</option>
                  <option value="Tour Guide">Tour Guide</option>
                  <option value="Travel Operations Manager">Travel Operations Manager</option>
                  <option value="Tour Package Designer">Tour Package Designer</option>
                  <option value="Customer Service Representative">Customer Service Representative</option>
                  <option value="Travel Sales Executive">Travel Sales Executive</option>
                  <option value="Destination Specialist">Destination Specialist</option>
                  <option value="Travel Coordinator">Travel Coordinator</option>
                  <option value="Tourism Marketing Manager">Tourism Marketing Manager</option>
                  <option value="Travel Agency Branch Manager">Travel Agency Branch Manager</option>
                  <option value="Corporate Travel Manager">Corporate Travel Manager</option>
                  <option value="Travel Product Manager">Travel Product Manager</option>
                  <option value="Tourism Business Development">Tourism Business Development</option>
                  <option value="Travel Quality Assurance">Travel Quality Assurance</option>
                  <option value="Senior Travel Advisor">Senior Travel Advisor</option>
                </select>
              </div>
              <div class="col-md-3">
                <input type="date" name="identified_date" class="form-control" required>
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-plus-lg"></i> Add
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Enhanced AI Suggestions Panel -->
      <div class="card shadow-sm border-0 mb-4" id="aiSuggestionsPanel" style="display: none;">
        <div class="card-header bg-primary bg-opacity-10">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-robot me-2"></i>LeadGen AI - Advanced Successor Intelligence</h4>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-success btn-sm" onclick="runPredictiveAnalysis()">
                <i class="bi bi-graph-up-arrow me-1"></i>Predictive Analysis
              </button>
              <button class="btn btn-outline-info btn-sm" onclick="generateDevelopmentPaths()">
                <i class="bi bi-map me-1"></i>Development Paths
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Target Role</label>
              <select id="targetRole" class="form-select" onchange="generateSuggestions()">
                <option value="">Select Role to Analyze</option>
                <option value="Travel Consultant">Travel Consultant</option>
                <option value="Tour Guide">Tour Guide</option>
                <option value="Travel Operations Manager">Travel Operations Manager</option>
                <option value="Tour Package Designer">Tour Package Designer</option>
                <option value="Customer Service Representative">Customer Service Representative</option>
                <option value="Travel Sales Executive">Travel Sales Executive</option>
                <option value="Destination Specialist">Destination Specialist</option>
                <option value="Travel Coordinator">Travel Coordinator</option>
                <option value="Tourism Marketing Manager">Tourism Marketing Manager</option>
                <option value="Travel Agency Branch Manager">Travel Agency Branch Manager</option>
                <option value="Corporate Travel Manager">Corporate Travel Manager</option>
                <option value="Travel Product Manager">Travel Product Manager</option>
                <option value="Tourism Business Development">Tourism Business Development</option>
                <option value="Travel Quality Assurance">Travel Quality Assurance</option>
                <option value="Senior Travel Advisor">Senior Travel Advisor</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Filter by Readiness</label>
              <select id="readinessFilter" class="form-select" onchange="filterSuggestions()">
                <option value="">All Readiness Levels</option>
                <option value="high">High Readiness (90%+)</option>
                <option value="medium">Medium Readiness (70-89%)</option>
                <option value="low">Low Readiness (Below 70%)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Filter by Department</label>
              <select id="departmentFilter" class="form-select" onchange="filterSuggestions()">
                <option value="">All Departments</option>
                <option value="Operations">Operations</option>
                <option value="Sales">Sales</option>
                <option value="Customer Service">Customer Service</option>
                <option value="Marketing">Marketing</option>
                <option value="Management">Management</option>
              </select>
            </div>
          </div>
          
          <!-- AI Analysis Tabs -->
          <ul class="nav nav-tabs mb-3" id="aiAnalysisTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="suggestions-tab" data-bs-toggle="tab" data-bs-target="#suggestions" type="button" role="tab">
                <i class="bi bi-people me-1"></i>Smart Suggestions
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="predictive-tab" data-bs-toggle="tab" data-bs-target="#predictive" type="button" role="tab">
                <i class="bi bi-graph-up me-1"></i>Predictive Analytics
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="development-tab" data-bs-toggle="tab" data-bs-target="#development" type="button" role="tab">
                <i class="bi bi-map me-1"></i>Development Paths
              </button>
            </li>
          </ul>
          
          <div class="tab-content" id="aiAnalysisContent">
            <!-- Smart Suggestions Tab -->
            <div class="tab-pane fade show active" id="suggestions" role="tabpanel">
              <div id="suggestionsContainer">
                <div class="text-center py-4">
                  <i class="bi bi-robot display-4 text-primary mb-3"></i>
                  <h5 class="text-primary">AI-Powered Successor Intelligence</h5>
                  <p class="text-muted">Select a target role to see advanced AI analysis with competency matching, performance prediction, and risk assessment.</p>
                  <div class="row mt-4">
                    <div class="col-md-4">
                      <div class="card border-primary border-opacity-25">
                        <div class="card-body text-center">
                          <i class="bi bi-brain text-primary display-6 mb-2"></i>
                          <h6>Machine Learning</h6>
                          <small class="text-muted">Advanced algorithms analyze patterns</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="card border-success border-opacity-25">
                        <div class="card-body text-center">
                          <i class="bi bi-graph-up-arrow text-success display-6 mb-2"></i>
                          <h6>Predictive Scoring</h6>
                          <small class="text-muted">Future performance predictions</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="card border-warning border-opacity-25">
                        <div class="card-body text-center">
                          <i class="bi bi-shield-check text-warning display-6 mb-2"></i>
                          <h6>Risk Assessment</h6>
                          <small class="text-muted">Succession risk mitigation</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Predictive Analytics Tab -->
            <div class="tab-pane fade" id="predictive" role="tabpanel">
              <div id="predictiveContainer">
                <div class="text-center py-4">
                  <i class="bi bi-graph-up-arrow display-4 text-success mb-3"></i>
                  <h5 class="text-success">Predictive Succession Analytics</h5>
                  <p class="text-muted">LeadGen-Ai forecasting for succession planning and talent pipeline optimization.</p>
                </div>
              </div>
            </div>
            
            <!-- Development Paths Tab -->
            <div class="tab-pane fade" id="development" role="tabpanel">
              <div id="developmentContainer">
                <div class="text-center py-4">
                  <i class="bi bi-map display-4 text-info mb-3"></i>
                  <h5 class="text-info">LeadGen-AI Development Paths</h5>
                  <p class="text-muted">Personalized career development recommendations based on competency gaps and role requirements.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif

    <!-- Table Section -->
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Successor List</h4>
        <div class="d-flex">
          <input type="text" class="form-control form-control-sm me-2" placeholder="Search..." style="width: 200px;">
          <button class="btn btn-sm btn-outline-primary">
            <i class="bi bi-funnel"></i> Filter
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Potential Role</th>
                <th class="fw-bold">Identified Date</th>
                <th class="fw-bold text-center">Actions</th>
                <th class="fw-bold">Profile Lookup</th>
              </tr>
            </thead>
            <tbody>
              @forelse($successors as $successor)
              <tr>
                <td>
                  @if($successor->employee)
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2">
                        @php
                          $firstName = $successor->employee->first_name ?? 'Unknown';
                          $lastName = $successor->employee->last_name ?? 'Employee';
                          $fullName = $firstName . ' ' . $lastName;
                          
                          // Check if profile picture exists - simplified approach
                          $profilePicUrl = null;
                          if ($successor->employee->profile_picture) {
                              // Direct asset URL generation - Laravel handles the storage symlink
                              $profilePicUrl = asset('storage/' . $successor->employee->profile_picture);
                          }
                          
                          // Generate consistent color based on employee name for fallback
                          $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                          $employeeId = $successor->employee->employee_id ?? 'default';
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
                        <span class="fw-semibold">
                          {{ $firstName }} {{ $lastName }}
                        </span>
                        <div class="small text-muted">{{ $successor->employee_id }}</div>
                      </div>
                    </div>
                  @else
                    N/A
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    {{ $successor->potential_role }}
                  </span>
                </td>
                <td>{{ $successor->identified_date }}</td>
                <td class="text-end pe-4">
                  <a href="{{ route('potential_successors.show', $successor->id) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                    <i class="bi bi-eye"></i> View
                  </a>
                  <a href="{{ route('potential_successors.edit', $successor->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                    <i class="bi bi-pencil"></i> Edit
                  </a>
                  <form action="{{ route('potential_successors.destroy', $successor->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this successor?')">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </td>
                <td>
                  <!-- Profile Lookup Button -->
                  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#profileLookupModal{{ $successor->employee_id }}">
                    <i class="bi bi-search"></i> Profile Lookup
                  </button>
                </td>
				</tr>
              <!-- Profile Lookup Modal -->
			  @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  <i class="bi bi-people display-5 text-muted mb-2"></i>
                  <div>No potential successors found.</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">
            Showing <span class="fw-semibold">1</span> to <span class="fw-semibold">2</span> of <span class="fw-semibold">2</span> entries
          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
              </li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item disabled">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </main>
   <!-- Profile Lookup Modals (outside table for proper backdrop handling) -->
   @foreach($successors as $successor)
    <div class="modal fade" id="profileLookupModal{{ $successor->employee_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Employee Competency Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @php
              $profile = $successor->employee && $successor->employee->competencyProfiles ? $successor->employee->competencyProfiles : [];
              $avgProficiency = $profile->count() > 0 ? round($profile->avg('proficiency_level'), 1) : 0;
              $leadershipCompetencies = $profile->filter(function($p) {
                $category = strtolower($p->competency->category ?? '');
                $name = strtolower($p->competency->competency_name ?? '');
                
                // Enhanced leadership detection
                $leadershipCategories = ['leadership', 'management', 'strategic thinking', 'team leadership', 'executive skills'];
                $leadershipKeywords = ['leadership', 'management', 'strategic', 'decision making', 'team building', 
                                     'communication', 'delegation', 'coaching', 'mentoring', 'vision', 'planning'];
                
                // Check category match
                foreach ($leadershipCategories as $leadershipCat) {
                  if (stripos($category, $leadershipCat) !== false) return true;
                }
                
                // Check keyword match
                foreach ($leadershipKeywords as $keyword) {
                  if (stripos($name, $keyword) !== false) return true;
                }
                
                return false;
              });
            @endphp
            
            <!-- Succession Readiness Summary -->
            <div class="row mb-4">
              <div class="col-md-4">
                <div class="card bg-primary bg-opacity-10 border-primary">
                  <div class="card-body text-center">
                    <h5 class="text-primary mb-1">{{ $avgProficiency }}/5</h5>
                    <small class="text-muted">Average Proficiency</small>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-success bg-opacity-10 border-success">
                  <div class="card-body text-center">
                    <h5 class="text-success mb-1">{{ $profile->count() }}</h5>
                    <small class="text-muted">Total Competencies</small>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-warning bg-opacity-10 border-warning">
                  <div class="card-body text-center">
                    <h5 class="text-warning mb-1">{{ $leadershipCompetencies->count() }}</h5>
                    <small class="text-muted">Leadership Skills</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Succession Readiness Indicator -->
            @php
              // Get training data for this employee
              $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $successor->employee_id)->get();
              $totalCourses = $trainingRecords->count();
              $completedCourses = $trainingRecords->where('progress', '>=', 100)->count();
              $avgTrainingProgress = $trainingRecords->count() > 0 ? $trainingRecords->avg('progress') : 0;
              $completionRate = $totalCourses > 0 ? ($completedCourses / $totalCourses) * 100 : 0;
              
              $hasTrainingData = $totalCourses > 0;
              $hasRealCompetencyData = $profile->count() > 0 && $avgProficiency > 0;
              
              // ULTRA-CONSERVATIVE algorithm to significantly lower suitability scores
              if ($hasTrainingData) {
                // Ultra-conservative training calculation with severe caps
                $progressScore = min(25, $avgTrainingProgress * 0.25); // Max 25% (was 100%)
                $completionScore = $totalCourses > 0 ? min(20, ($completedCourses / $totalCourses) * 20) : 0; // Max 20% (was 100%)
                $assignmentScore = min(12, ($totalCourses / 50) * 12); // Max 12%, requires 50+ courses (was 8)
                
                // Ultra-conservative training score with severe restrictions
                $trainingBasedScore = ($progressScore * 0.6) + 
                                    ($completionScore * 0.25) + 
                                    ($assignmentScore * 0.15);
                
                // If real competency data exists, blend with ultra-conservative caps
                if ($hasRealCompetencyData) {
                  // Ultra-conservative competency scoring with massive reductions
                  $proficiencyScore = min(20, ($avgProficiency / 5) * 20); // Max 20% (was 100%)
                  
                  // Ultra-conservative leadership scoring
                  if ($leadershipCompetencies->count() > 0) {
                    $leadershipProficiencySum = 0;
                    foreach ($leadershipCompetencies as $leadership) {
                      $profLevel = match(strtolower($leadership->proficiency_level)) {
                        'beginner', '1' => 1,
                        'developing', '2' => 2,
                        'proficient', '3' => 3,
                        'advanced', '4' => 4,
                        'expert', '5' => 5,
                        default => 1 // Default to lowest instead of 3
                      };
                      $leadershipProficiencySum += $profLevel;
                    }
                    $avgLeadershipProficiency = $leadershipProficiencySum / $leadershipCompetencies->count();
                    $leadershipScore = min(12, ($avgLeadershipProficiency / 5) * 12); // Max 12% (was 100%)
                  } else {
                    $leadershipScore = 0;
                  }
                  
                  // Ultra-conservative competency breadth - requires 100+ competencies for max score
                  $competencyBreadthScore = min(8, ($profile->count() / 100) * 8); // Max 8% (was 100%)
                  
                  $competencyScore = ($proficiencyScore * 0.7) + 
                                   ($leadershipScore * 0.2) + 
                                   ($competencyBreadthScore * 0.1);
                  
                  // Ultra-conservative final blend: 70% competency + 30% training
                  $readinessScore = round(($competencyScore * 0.7) + ($trainingBasedScore * 0.3));
                } else {
                  $readinessScore = round($trainingBasedScore);
                }
              } 
              // Ultra-conservative competency-only calculation
              elseif ($hasRealCompetencyData) {
                // Massive reductions for competency-only scoring
                $proficiencyScore = min(35, ($avgProficiency / 5) * 35); // Max 35% (was 100%)
                
                if ($leadershipCompetencies->count() > 0) {
                  $leadershipProficiencySum = 0;
                  foreach ($leadershipCompetencies as $leadership) {
                    $profLevel = match(strtolower($leadership->proficiency_level)) {
                      'beginner', '1' => 1,
                      'developing', '2' => 2,
                      'proficient', '3' => 3,
                      'advanced', '4' => 4,
                      'expert', '5' => 5,
                      default => 1 // Default to lowest
                    };
                    $leadershipProficiencySum += $profLevel;
                  }
                  $avgLeadershipProficiency = $leadershipProficiencySum / $leadershipCompetencies->count();
                  $leadershipScore = min(20, ($avgLeadershipProficiency / 5) * 20); // Max 20% (was 100%)
                } else {
                  $leadershipScore = 0;
                }
                
                // Ultra-conservative breadth scoring - requires 75+ competencies
                $competencyBreadthScore = min(15, ($profile->count() / 75) * 15); // Max 15% (was 100%)
                
                $readinessScore = round(
                  ($proficiencyScore * 0.7) + 
                  ($leadershipScore * 0.2) + 
                  ($competencyBreadthScore * 0.1)
                );
              }
              // Ultra-conservative baseline for no data
              else {
                $readinessScore = 0; // No readiness without data
              }
              
              // Adjusted readiness thresholds for more practical succession planning
              if ($readinessScore >= 80) {
                $readinessLevel = 'Ready Now';
                $readinessColor = 'success';
              } elseif ($readinessScore >= 60) {
                $readinessLevel = 'Ready Soon';
                $readinessColor = 'warning';
              } else {
                $readinessLevel = 'Needs Development';
                $readinessColor = 'danger';
              }
            @endphp
            
            <div class="alert alert-{{ $readinessColor }} alert-dismissible">
              <h6 class="alert-heading mb-2">
                <i class="bi bi-graph-up-arrow me-2"></i>Succession Readiness: {{ $readinessLevel }} ({{ $readinessScore }}%)
              </h6>
              <p class="mb-0">
                @if($readinessScore >= 80)
                  This employee is ready for immediate succession with strong competency levels and leadership skills.
                @elseif($readinessScore >= 60)
                  This employee shows good potential and will be ready soon with targeted development.
                @else
                  This employee needs significant development before being ready for succession roles.
                @endif
              </p>
            </div>

            @if($profile && count($profile))
              <!-- Leadership Skills Breakdown -->
              @if($leadershipCompetencies->count() > 0)
                <div class="card bg-light border-0 mb-4">
                  <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>Leadership Skills Analysis</h6>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      @php
                        $leadershipCategories = [
                          'Strategic Leadership' => ['strategic', 'vision', 'planning'],
                          'People Leadership' => ['team building', 'coaching', 'mentoring', 'communication'],
                          'Operational Leadership' => ['management', 'delegation', 'decision making'],
                          'Executive Skills' => ['leadership', 'executive']
                        ];
                        
                        $categoryScores = [];
                        foreach ($leadershipCategories as $catName => $keywords) {
                          $matchingComps = $leadershipCompetencies->filter(function($p) use ($keywords) {
                            $name = strtolower($p->competency->competency_name ?? '');
                            foreach ($keywords as $keyword) {
                              if (stripos($name, $keyword) !== false) return true;
                            }
                            return false;
                          });
                          if ($matchingComps->count() > 0) {
                            $categoryScores[$catName] = round($matchingComps->avg('proficiency_level'), 1);
                          }
                        }
                      @endphp
                      
                      @foreach($categoryScores as $category => $score)
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="fw-semibold">{{ $category }}</small>
                            <small class="text-muted">{{ $score }}/5</small>
                          </div>
                          <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ ($score/5)*100 }}%"></div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                    
                    <!-- Leadership Readiness Indicator -->
                    @php
                      $avgLeadershipScore = $leadershipCompetencies->avg('proficiency_level');
                      $leadershipReadiness = $avgLeadershipScore >= 4 ? 'Ready' : ($avgLeadershipScore >= 3 ? 'Developing' : 'Needs Development');
                      $leadershipColor = $avgLeadershipScore >= 4 ? 'success' : ($avgLeadershipScore >= 3 ? 'warning' : 'danger');
                    @endphp
                    
                    <div class="mt-3 p-2 bg-{{ $leadershipColor }} bg-opacity-10 rounded">
                      <small class="text-{{ $leadershipColor }} fw-semibold">
                        <i class="bi bi-graph-up me-1"></i>Leadership Readiness: {{ $leadershipReadiness }} ({{ round($avgLeadershipScore, 1) }}/5)
                      </small>
                    </div>
                  </div>
                </div>
              @endif

              <!-- Competency Details -->
              <h6 class="mb-3">Detailed Competency Profile</h6>
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead class="table-primary">
                    <tr>
                      <th>Competency</th>
                      <th>Category</th>
                      <th>Proficiency</th>
                      <th>Assessment Date</th>
                      <th>Gap Analysis</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($profile->sortByDesc('proficiency_level') as $p)
                      <tr>
                        <td>
                          <strong>{{ $p->competency->competency_name ?? 'N/A' }}</strong>
                          @if(stripos($p->competency->competency_name ?? '', 'leadership') !== false)
                            <span class="badge bg-warning bg-opacity-10 text-warning ms-1">Leadership</span>
                          @endif
                        </td>
                        <td>
                          @php
                            $categoryColors = [
                              'Technical' => 'bg-primary',
                              'Leadership' => 'bg-success', 
                              'Communication' => 'bg-info',
                              'Behavioral' => 'bg-warning',
                              'Management' => 'bg-danger',
                              'Analytical' => 'bg-purple',
                              'Creative' => 'bg-pink',
                              'Strategic' => 'bg-dark'
                            ];
                            $category = $p->competency->category ?? 'N/A';
                            $colorClass = $categoryColors[$category] ?? 'bg-secondary';
                          @endphp
                          <span class="badge {{ $colorClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $colorClass) }}">
                            {{ $category }}
                          </span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="progress me-2" style="width: 80px; height: 20px;">
                              <div class="progress-bar bg-warning" style="width: {{ ($p->proficiency_level/5)*100 }}%"></div>
                            </div>
                            <span class="fw-semibold">{{ round(($p->proficiency_level/5)*100) }}%</span>
                          </div>
                        </td>
                        <td>{{ date('M d, Y', strtotime($p->assessment_date)) }}</td>
                        <td>
                          @if($p->proficiency_level >= 4)
                            <span class="badge bg-success">Strong</span>
                          @elseif($p->proficiency_level >= 3)
                            <span class="badge bg-warning">Moderate</span>
                          @else
                            <span class="badge bg-danger">Needs Development</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              
              <!-- Quick Actions -->
              <div class="d-flex gap-2 mt-3">
                <a href="{{ route('employee_competency_profiles.index') }}?employee={{ $successor->employee_id }}" 
                   class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-pencil me-1"></i>Edit Competencies
                </a>
                <a href="{{ route('competency_gap_analysis.index') }}?employee={{ $successor->employee_id }}" 
                   class="btn btn-outline-info btn-sm">
                  <i class="bi bi-graph-down me-1"></i>View Gap Analysis
                </a>
              </div>
            @else
              <div class="text-center text-muted py-4">
                <i class="bi bi-person-x display-4 text-muted mb-3"></i>
                <h6>No Competency Profile Found</h6>
                <p>This employee doesn't have any competency assessments yet.</p>
                <a href="{{ route('employee_competency_profiles.index') }}" class="btn btn-primary btn-sm">
                  <i class="bi bi-plus-lg me-1"></i>Add Competency Profile
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Employee suitability tracking system
    function autoGenerateSuggestions() {
      document.getElementById('aiSuggestionsPanel').style.display = 'block';
      document.getElementById('aiSuggestionsPanel').scrollIntoView({ behavior: 'smooth' });
    }

    // Role-specific competency requirements for travel & tours
    const roleRequirements = {
      'Travel Consultant': {
        required: ['Communication', 'Customer Service', 'Travel Knowledge', 'Sales'],
        preferred: ['Technical', 'Problem Solving', 'Cultural Awareness'],
        weight: { communication: 30, customer_service: 25, sales: 20, travel_knowledge: 15, technical: 10 }
      },
      'Tour Guide': {
        required: ['Communication', 'Leadership', 'Cultural Knowledge', 'Public Speaking'],
        preferred: ['Language Skills', 'History Knowledge', 'Emergency Management'],
        weight: { communication: 25, leadership: 20, cultural_knowledge: 20, public_speaking: 15, language: 20 }
      },
      'Travel Operations Manager': {
        required: ['Management', 'Leadership', 'Operations', 'Strategic Planning'],
        preferred: ['Technical', 'Analytics', 'Budget Management'],
        weight: { management: 30, leadership: 25, operations: 20, strategic: 15, technical: 10 }
      },
      'Travel Sales Executive': {
        required: ['Sales', 'Communication', 'Customer Service', 'Negotiation'],
        preferred: ['Market Analysis', 'Relationship Building', 'Product Knowledge'],
        weight: { sales: 35, communication: 25, customer_service: 20, negotiation: 20 }
      },
      'Tourism Marketing Manager': {
        required: ['Marketing', 'Creative', 'Strategic', 'Communication'],
        preferred: ['Digital Marketing', 'Brand Management', 'Analytics'],
        weight: { marketing: 30, creative: 25, strategic: 20, communication: 15, digital: 10 }
      }
    };

    function generateSuggestions() {
      const targetRole = document.getElementById('targetRole').value;
      if (!targetRole) return;

      const container = document.getElementById('suggestionsContainer');
      container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Analyzing competency profiles...</p></div>';

      // Get filter values
      const readinessFilter = document.getElementById('readinessFilter').value;
      const departmentFilter = document.getElementById('departmentFilter').value;

      // Call real API endpoint for competency-based analysis
      fetch('{{ route("potential_successors.ai_suggestions") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          target_role: targetRole,
          readiness_filter: readinessFilter,
          department_filter: departmentFilter
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displayRealSuggestions(data.data, targetRole);
        } else {
          displayErrorMessage(data.error, data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        displayErrorMessage('Analysis failed', 'Unable to connect to the analysis service. Please try again.');
      });
    }

    function displayRealSuggestions(data, targetRole) {
      const container = document.getElementById('suggestionsContainer');
      const suggestions = data.suggestions;
      
      if (!suggestions || suggestions.length === 0) {
        container.innerHTML = `
          <div class="alert alert-info" role="alert">
            <div class="d-flex align-items-center">
              <i class="bi bi-info-circle-fill me-2"></i>
              <div>
                <strong>No candidates found</strong>
                <p class="mb-0 mt-1">No employees with competency profiles match the criteria for ${targetRole}.</p>
              </div>
            </div>
          </div>
        `;
        return;
      }

      let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Top Candidates for ${targetRole}</h5>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary">${suggestions.length} candidates</span>
            <small class="text-muted">Data: ${data.dataSource === 'competency_profiles' ? 'Competency Profiles' : 'Simulated'}</small>
          </div>
        </div>
      `;

      suggestions.forEach((candidate, index) => {
        const readinessClass = candidate.readinessLevel === 'Ready Now' ? 'success' : 
                              (candidate.readinessLevel === 'Ready Soon' ? 'warning' : 'danger');
        
        html += `
          <div class="card mb-3 border-${readinessClass}">
            <div class="card-header bg-${readinessClass} bg-opacity-10">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="display-6 text-${readinessClass} fw-bold">${candidate.suitabilityScore}%</div>
                    <small class="text-muted">Suitability</small>
                  </div>
                  <div>
                    <h6 class="mb-1">${candidate.employeeName}</h6>
                    <small class="text-muted">ID: ${candidate.employeeId}</small>
                  </div>
                </div>
                <div class="text-end">
                  <span class="badge bg-${readinessClass} mb-1">${candidate.readinessLevel}</span>
                  <br>
                  <small class="text-muted">Confidence: ${candidate.confidenceLevel}</small>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <strong class="text-success">Strengths:</strong>
                  <div class="mt-1">
                    ${candidate.strengths.map(strength => 
                      `<span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">${strength}</span>`
                    ).join('')}
                  </div>
                </div>
                <div class="col-md-6">
                  <strong class="text-warning">Development Areas:</strong>
                  <div class="mt-1">
                    ${candidate.developmentAreas.map(area => 
                      `<span class="badge bg-warning bg-opacity-10 text-warning me-1 mb-1">${area}</span>`
                    ).join('')}
                  </div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-8">
                  <p class="mb-2"><strong>Recommendation:</strong> ${candidate.recommendation}</p>
                  <small class="text-muted">
                    Avg Proficiency: ${candidate.avgProficiency}/5.0 | 
                    Total Competencies: ${candidate.totalCompetencies} | 
                    Leadership Skills: ${candidate.leadershipCompetencies}
                  </small>
                </div>
                <div class="col-md-4 text-end">
                  <button class="btn btn-primary btn-sm" onclick="selectCandidate('${candidate.employeeId}', '${candidate.employeeName}', '${targetRole}')">
                    <i class="bi bi-check-lg me-1"></i>Add as Successor
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
      });

      container.innerHTML = html;
    }

    function displayErrorMessage(error, message) {
      const container = document.getElementById('suggestionsContainer');
      container.innerHTML = `
        <div class="alert alert-warning" role="alert">
          <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
              <strong>${error}</strong>
              <p class="mb-0 mt-1">${message}</p>
            </div>
          </div>
        </div>
      `;
    }

    function calculateSuitabilityScore(employee, targetRole, requirements) {
      // Role-specific suitability calculation using competency matching
      if (!employee.competencyProfiles || employee.competencyProfiles.length === 0) {
        return 15; // Baseline score for employees without competency data
      }
      
      const roleWeights = requirements.weight || {};
      const requiredCompetencies = requirements.required || [];
      const preferredCompetencies = requirements.preferred || [];
      
      let totalScore = 0;
      let totalWeight = 0;
      
      // Calculate role-specific competency match scores
      Object.keys(roleWeights).forEach(competencyType => {
        const weight = roleWeights[competencyType];
        const matchingProfiles = employee.competencyProfiles.filter(profile => {
          const competencyName = (profile.competency?.competency_name || '').toLowerCase();
          const category = (profile.competency?.category || '').toLowerCase();
          const searchTerm = competencyType.replace('_', ' ').toLowerCase();
          
          return competencyName.includes(searchTerm) || category.includes(searchTerm);
        });
        
        if (matchingProfiles.length > 0) {
          const avgProficiency = matchingProfiles.reduce((sum, p) => sum + p.proficiency_level, 0) / matchingProfiles.length;
          const competencyScore = (avgProficiency / 5) * 100;
          totalScore += competencyScore * (weight / 100);
        } else {
          // Penalty for missing required competencies
          if (requiredCompetencies.some(req => req.toLowerCase().includes(competencyType.replace('_', ' ')))) {
            totalScore += 10 * (weight / 100); // Low score for missing required competency
          } else {
            totalScore += 30 * (weight / 100); // Moderate score for missing preferred competency
          }
        }
        totalWeight += weight;
      });
      
      // Normalize score based on total weights
      const roleSpecificScore = totalWeight > 0 ? (totalScore / totalWeight) * 100 : 0;
      
      // Combine with general readiness score (70% role-specific, 30% general readiness)
      const generalReadiness = calculateGeneralReadiness(employee);
      const finalScore = Math.round((roleSpecificScore * 0.7) + (generalReadiness * 0.3));
      
      return Math.min(100, Math.max(0, finalScore));
    }

    function calculateGeneralReadiness(employee) {
      // Same algorithm as succession readiness rating for consistency
      if (!employee.competencyProfiles || employee.competencyProfiles.length === 0) {
        return 15;
      }
      
      const profiles = employee.competencyProfiles;
      const avgProficiency = profiles.reduce((sum, p) => sum + p.proficiency_level, 0) / profiles.length;
      
      // Count leadership competencies
      const leadershipCount = profiles.filter(p => {
        const name = (p.competency?.competency_name || '').toLowerCase();
        const category = (p.competency?.category || '').toLowerCase();
        const leadershipKeywords = ['leadership', 'management', 'strategic', 'decision making', 'team building', 'communication'];
        return leadershipKeywords.some(keyword => name.includes(keyword) || category.includes(keyword));
      }).length;
      
      // Calculate scores using same weights as readiness rating
      const proficiencyScore = (avgProficiency / 5) * 100;
      const leadershipScore = Math.min(100, (leadershipCount / 3) * 100);
      const competencyBreadthScore = Math.min(100, (profiles.length / 10) * 100);
      
      // Weighted calculation: 50% proficiency + 30% leadership + 20% breadth
      const readinessScore = Math.round(
        (proficiencyScore * 0.5) + 
        (leadershipScore * 0.3) + 
        (competencyBreadthScore * 0.2)
      );
      
      return Math.min(100, Math.max(0, readinessScore));
    }
    
    function getReadinessLevel(score) {
      if (score >= 80) return 'Ready Now';
      if (score >= 60) return 'Ready Soon';
      return 'Needs Development';
    }

    function getEmployeeStrengths(employee, requirements) {
      const strengths = [];
      
      if (!employee.competencyProfiles || employee.competencyProfiles.length === 0) {
        return ['New employee potential', 'Ready for assessment'];
      }
      
      // Find actual strengths based on competency profiles
      const strongCompetencies = employee.competencyProfiles.filter(p => p.proficiency_level >= 4);
      
      strongCompetencies.forEach(comp => {
        const competencyName = comp.competency?.competency_name || '';
        const category = comp.competency?.category || '';
        
        if (competencyName.toLowerCase().includes('leadership') || category.toLowerCase().includes('leadership')) {
          if (!strengths.includes('Leadership Skills')) strengths.push('Leadership Skills');
        }
        if (competencyName.toLowerCase().includes('communication') || category.toLowerCase().includes('communication')) {
          if (!strengths.includes('Communication Excellence')) strengths.push('Communication Excellence');
        }
        if (competencyName.toLowerCase().includes('customer') || competencyName.toLowerCase().includes('service')) {
          if (!strengths.includes('Customer Service')) strengths.push('Customer Service');
        }
        if (category.toLowerCase().includes('technical')) {
          if (!strengths.includes('Technical Expertise')) strengths.push('Technical Expertise');
        }
      });
      
      // Add general strengths based on overall performance
      const avgProficiency = employee.competencyProfiles.reduce((sum, p) => sum + p.proficiency_level, 0) / employee.competencyProfiles.length;
      if (avgProficiency >= 4) {
        if (!strengths.includes('High Performance')) strengths.push('High Performance');
      }
      if (employee.competencyProfiles.length >= 5) {
        if (!strengths.includes('Well-Rounded Skills')) strengths.push('Well-Rounded Skills');
      }
      
      return strengths.length > 0 ? strengths.slice(0, 4) : ['Competency Assessment Complete'];
    }

    function getEmployeeGaps(employee, requirements) {
      const gaps = [];
      
      if (!employee.competencyProfiles || employee.competencyProfiles.length === 0) {
        return ['Add Competency Assessments', 'Complete Skills Evaluation'];
      }
      
      const requiredCompetencies = requirements.required || [];
      const preferredCompetencies = requirements.preferred || [];
      
      // Check for missing required competencies
      requiredCompetencies.forEach(required => {
        const hasCompetency = employee.competencyProfiles.some(p => {
          const name = (p.competency?.competency_name || '').toLowerCase();
          const category = (p.competency?.category || '').toLowerCase();
          return name.includes(required.toLowerCase()) || category.includes(required.toLowerCase());
        });
        
        if (!hasCompetency) {
          gaps.push(`${required} Skills`);
        }
      });
      
      // Check for weak areas (proficiency < 3)
      const weakAreas = employee.competencyProfiles.filter(p => p.proficiency_level < 3);
      weakAreas.forEach(weak => {
        const competencyName = weak.competency?.competency_name || '';
        if (competencyName && !gaps.some(gap => gap.includes(competencyName))) {
          gaps.push(`Improve ${competencyName}`);
        }
      });
      
      // Add general development areas
      const avgProficiency = employee.competencyProfiles.reduce((sum, p) => sum + p.proficiency_level, 0) / employee.competencyProfiles.length;
      if (avgProficiency < 3.5) {
        if (!gaps.includes('Overall Skill Enhancement')) gaps.push('Overall Skill Enhancement');
      }
      
      const leadershipCount = employee.competencyProfiles.filter(p => {
        const name = (p.competency?.competency_name || '').toLowerCase();
        return name.includes('leadership') || name.includes('management');
      }).length;
      
      if (leadershipCount === 0) {
        if (!gaps.includes('Leadership Development')) gaps.push('Leadership Development');
      }
      
      return gaps.length > 0 ? gaps.slice(0, 3) : ['Continue Professional Development'];
    }

    function getRecommendation(score) {
      if (score >= 90) return 'Ready for immediate promotion';
      if (score >= 80) return 'Ready with minimal training';
      if (score >= 70) return 'Needs 3-6 months development';
      if (score >= 60) return 'Needs 6-12 months development';
      return 'Requires extensive development';
    }

    // Enhanced AI Functions for Advanced Features
    function runPredictiveAnalysis() {
      const targetRole = document.getElementById('targetRole').value;
      if (!targetRole) {
        alert('Please select a target role first');
        return;
      }

      const predictiveTab = document.getElementById('predictive-tab');
      const predictiveContainer = document.getElementById('predictiveContainer');
      
      // Switch to predictive tab
      predictiveTab.click();
      
      predictiveContainer.innerHTML = `
        <div class="text-center py-3">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-2">Running predictive analysis for ${targetRole}...</p>
        </div>
      `;

      // Call real API endpoint for predictive analytics
      fetch('{{ route("potential_successors.predictive_analytics") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          target_role: targetRole
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displayRealPredictiveAnalysis(data.data, targetRole);
        } else {
          displayAnalysisError('Predictive Analysis Failed', 'Unable to generate predictive analysis. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        displayAnalysisError('Analysis Error', 'Unable to connect to the analysis service. Please try again.');
      });
    }

    function generatePredictiveAnalysis(targetRole) {
      // Simulate advanced predictive analytics
      const currentYear = new Date().getFullYear();
      const scenarios = [
        {
          scenario: 'Optimal Succession',
          probability: 75,
          timeline: '6-12 months',
          riskLevel: 'Low',
          successRate: 85,
          keyFactors: ['Strong internal candidates', 'Adequate development time', 'Leadership support']
        },
        {
          scenario: 'Accelerated Succession',
          probability: 45,
          timeline: '3-6 months',
          riskLevel: 'Medium',
          successRate: 70,
          keyFactors: ['Intensive training required', 'External mentoring', 'Close monitoring']
        },
        {
          scenario: 'External Hiring',
          probability: 30,
          timeline: '2-4 months',
          riskLevel: 'High',
          successRate: 60,
          keyFactors: ['Market competition', 'Cultural fit challenges', 'Onboarding complexity']
        }
      ];

      const trendData = {
        demandTrend: Math.floor(Math.random() * 20) + 10, // 10-30% increase
        supplyTrend: Math.floor(Math.random() * 15) + 5,  // 5-20% increase
        competitionIndex: Math.floor(Math.random() * 40) + 60, // 60-100
        retentionRate: Math.floor(Math.random() * 15) + 80 // 80-95%
      };

      return { scenarios, trendData, targetRole };
    }

    function displayRealPredictiveAnalysis(data, targetRole) {
      const container = document.getElementById('predictiveContainer');
      
      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-success mb-3">
              <i class="bi bi-graph-up-arrow me-2"></i>Real-Data Predictive Analysis: ${targetRole}
            </h5>
            <div class="alert alert-success alert-dismissible">
              <strong>Data Source:</strong> Live competency profiles and training records from your HR database
            </div>
          </div>
        </div>
        
        <!-- Readiness Overview -->
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="card border-success border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-check-circle text-success display-6 mb-2"></i>
                <h6>Ready Now</h6>
                <div class="h4 text-success">${data.readyNow}</div>
                <small class="text-muted">Candidates</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-warning border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-clock text-warning display-6 mb-2"></i>
                <h6>Ready Soon</h6>
                <div class="h4 text-warning">${data.readySoon}</div>
                <small class="text-muted">Candidates</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-info border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-arrow-up text-info display-6 mb-2"></i>
                <h6>Needs Development</h6>
                <div class="h4 text-info">${data.needsDevelopment}</div>
                <small class="text-muted">Candidates</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-primary border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-graph-up text-primary display-6 mb-2"></i>
                <h6>Avg Score</h6>
                <div class="h4 text-primary">${data.avgCompetencyScore}%</div>
                <small class="text-muted">Competency</small>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Timeline Projections -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Succession Timeline Projections</h6>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-md-3">
                    <div class="mb-2">
                      <div class="h5 text-success">${data.timeline.immediate}</div>
                      <small class="text-muted">Immediate (0-1 month)</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="mb-2">
                      <div class="h5 text-warning">${data.timeline['3_months']}</div>
                      <small class="text-muted">3 Months</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="mb-2">
                      <div class="h5 text-info">${data.timeline['6_months']}</div>
                      <small class="text-muted">6 Months</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="mb-2">
                      <div class="h5 text-primary">${data.timeline['12_months']}</div>
                      <small class="text-muted">12+ Months</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Top Competency Gaps -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Top Competency Gaps</h6>
              </div>
              <div class="card-body">
                ${Object.entries(data.topCompetencyGaps).map(([competency, count]) => `
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>${competency}</span>
                    <span class="badge bg-warning">${count} employees</span>
                  </div>
                `).join('')}
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-shield-exclamation me-2"></i>Risk Assessment</h6>
              </div>
              <div class="card-body">
                ${Object.entries(data.riskAssessment).filter(([level, risk]) => risk).map(([level, risk]) => `
                  <div class="alert alert-${level === 'high' ? 'danger' : level === 'medium' ? 'warning' : 'info'} py-2 mb-2">
                    <strong>${level.toUpperCase()} RISK:</strong> ${risk}
                  </div>
                `).join('')}
                ${Object.values(data.riskAssessment).every(risk => !risk) ? '<div class="text-success"><i class="bi bi-check-circle me-2"></i>No significant risks identified</div>' : ''}
              </div>
            </div>
          </div>
        </div>
        
        <!-- Training Recommendations -->
        ${data.trainingRecommendations.length > 0 ? `
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-book me-2"></i>Recommended Training Programs</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  ${data.trainingRecommendations.map(rec => `
                    <div class="col-md-4 mb-3">
                      <div class="card border-primary border-opacity-25">
                        <div class="card-body">
                          <h6 class="card-title">${rec.competency}</h6>
                          <p class="card-text">
                            <span class="badge bg-${rec.priority === 'High' ? 'danger' : 'warning'} mb-2">${rec.priority} Priority</span><br>
                            <small class="text-muted">Duration: ${rec.estimatedDuration}</small>
                          </p>
                          <div class="mt-2">
                            ${rec.suggestedCourses.map(course => `<small class="d-block">• ${course}</small>`).join('')}
                          </div>
                        </div>
                      </div>
                    </div>
                  `).join('')}
                </div>
              </div>
            </div>
          </div>
        </div>
        ` : ''}
      `;
    }

    function displayPredictiveAnalysis(data, targetRole) {
      const container = document.getElementById('predictiveContainer');
      
      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-success mb-3">
              <i class="bi bi-graph-up-arrow me-2"></i>Predictive Analysis: ${targetRole}
            </h5>
          </div>
        </div>
        
        <!-- Market Trends -->
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="card border-success border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-trending-up text-success display-6 mb-2"></i>
                <h6>Demand Trend</h6>
                <div class="h4 text-success">+${data.trendData.demandTrend}%</div>
                <small class="text-muted">Next 2 years</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-info border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-people text-info display-6 mb-2"></i>
                <h6>Talent Supply</h6>
                <div class="h4 text-info">+${data.trendData.supplyTrend}%</div>
                <small class="text-muted">Internal pipeline</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-warning border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-graph-down text-warning display-6 mb-2"></i>
                <h6>Competition</h6>
                <div class="h4 text-warning">${data.trendData.competitionIndex}</div>
                <small class="text-muted">Market index</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-primary border-opacity-25">
              <div class="card-body text-center">
                <i class="bi bi-shield-check text-primary display-6 mb-2"></i>
                <h6>Retention Rate</h6>
                <div class="h4 text-primary">${data.trendData.retentionRate}%</div>
                <small class="text-muted">Current year</small>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Succession Scenarios -->
        <div class="row">
          ${data.scenarios.map((scenario, index) => `
            <div class="col-md-4 mb-3">
              <div class="card h-100 ${scenario.riskLevel === 'Low' ? 'border-success' : scenario.riskLevel === 'Medium' ? 'border-warning' : 'border-danger'} border-opacity-25">
                <div class="card-header ${scenario.riskLevel === 'Low' ? 'bg-success' : scenario.riskLevel === 'Medium' ? 'bg-warning' : 'bg-danger'} bg-opacity-10">
                  <h6 class="mb-0">${scenario.scenario}</h6>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                      <small>Probability</small>
                      <small>${scenario.probability}%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                      <div class="progress-bar ${scenario.riskLevel === 'Low' ? 'bg-success' : scenario.riskLevel === 'Medium' ? 'bg-warning' : 'bg-danger'}" 
                           style="width: ${scenario.probability}%"></div>
                    </div>
                  </div>
                  
                  <div class="mb-2">
                    <strong>Timeline:</strong> ${scenario.timeline}
                  </div>
                  <div class="mb-2">
                    <strong>Success Rate:</strong> ${scenario.successRate}%
                  </div>
                  <div class="mb-3">
                    <strong>Risk Level:</strong> 
                    <span class="badge ${scenario.riskLevel === 'Low' ? 'bg-success' : scenario.riskLevel === 'Medium' ? 'bg-warning' : 'bg-danger'} bg-opacity-10 
                                      ${scenario.riskLevel === 'Low' ? 'text-success' : scenario.riskLevel === 'Medium' ? 'text-warning' : 'text-danger'}">
                      ${scenario.riskLevel}
                    </span>
                  </div>
                  
                  <div>
                    <strong>Key Factors:</strong>
                    <ul class="list-unstyled mt-1">
                      ${scenario.keyFactors.map(factor => `<li><small>• ${factor}</small></li>`).join('')}
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
        
        <div class="alert alert-info mt-4">
          <h6><i class="bi bi-lightbulb me-2"></i>AI Recommendation</h6>
          <p class="mb-0">Based on predictive analysis, the <strong>${data.scenarios[0].scenario}</strong> approach is recommended with ${data.scenarios[0].probability}% probability of success. Focus on developing internal candidates while maintaining external recruitment as backup.</p>
        </div>
      `;
    }

    function generateDevelopmentPaths() {
      const targetRole = document.getElementById('targetRole').value;
      if (!targetRole) {
        alert('Please select a target role first');
        return;
      }

      const developmentTab = document.getElementById('development-tab');
      const developmentContainer = document.getElementById('developmentContainer');
      
      // Switch to development tab
      developmentTab.click();
      
      developmentContainer.innerHTML = `
        <div class="text-center py-3">
          <div class="spinner-border text-info" role="status"></div>
          <p class="mt-2">Generating AI-powered development paths for ${targetRole}...</p>
        </div>
      `;

      // For development paths, we need to show paths for all potential candidates
      // First get the suggestions, then generate paths for top candidates
      fetch('{{ route("potential_successors.ai_suggestions") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          target_role: targetRole
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data.suggestions.length > 0) {
          // Get development paths for top 3 candidates
          const topCandidates = data.data.suggestions.slice(0, 3);
          displayDevelopmentPathsForCandidates(topCandidates, targetRole);
        } else {
          displayAnalysisError('Development Paths', 'No candidates found for development path generation.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        displayAnalysisError('Development Paths Error', 'Unable to generate development paths. Please try again.');
      });
    }

    function generateDevelopmentPathData(targetRole) {
      const roleRequirements = getRoleRequirements(targetRole);
      
      const developmentPaths = [
        {
          pathName: 'Fast Track Leadership',
          duration: '6-9 months',
          difficulty: 'High',
          successRate: 80,
          phases: [
            { phase: 'Foundation', duration: '2 months', activities: ['Leadership Assessment', 'Core Skills Training', 'Mentorship Assignment'] },
            { phase: 'Development', duration: '4 months', activities: ['Project Leadership', 'Cross-functional Exposure', 'Advanced Training'] },
            { phase: 'Validation', duration: '2-3 months', activities: ['Acting Role', 'Performance Review', 'Final Assessment'] }
          ]
        },
        {
          pathName: 'Gradual Progression',
          duration: '12-18 months',
          difficulty: 'Medium',
          successRate: 90,
          phases: [
            { phase: 'Skill Building', duration: '6 months', activities: ['Competency Development', 'Technical Training', 'Soft Skills Enhancement'] },
            { phase: 'Experience Gain', duration: '6 months', activities: ['Job Rotation', 'Special Projects', 'Team Leadership'] },
            { phase: 'Readiness Prep', duration: '6 months', activities: ['Shadow Leadership', 'Strategic Planning', 'Final Certification'] }
          ]
        },
        {
          pathName: 'Specialized Track',
          duration: '9-12 months',
          difficulty: 'Medium',
          successRate: 85,
          phases: [
            { phase: 'Specialization', duration: '4 months', activities: ['Domain Expertise', 'Industry Certification', 'Best Practices Study'] },
            { phase: 'Integration', duration: '4 months', activities: ['Cross-team Collaboration', 'Process Improvement', 'Knowledge Transfer'] },
            { phase: 'Leadership Prep', duration: '4 months', activities: ['Management Training', 'Strategic Thinking', 'Change Management'] }
          ]
        }
      ];

      return { developmentPaths, roleRequirements, targetRole };
    }

    function displayDevelopmentPathsForCandidates(candidates, targetRole) {
      const container = document.getElementById('developmentContainer');
      
      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-info mb-3">
              <i class="bi bi-map me-2"></i>AI-Generated Development Paths: ${targetRole}
            </h5>
            <div class="alert alert-info alert-dismissible">
              <strong>Personalized Paths:</strong> Based on real competency data for top ${candidates.length} candidates
            </div>
          </div>
        </div>
        
        <!-- Development Paths for Each Candidate -->
        <div class="row">
          ${candidates.map((candidate, index) => {
            const pathData = generatePersonalizedPath(candidate, targetRole);
            return `
            <div class="col-md-4 mb-4">
              <div class="card h-100 border-info border-opacity-25">
                <div class="card-header bg-info bg-opacity-10">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-0 text-info">${candidate.employeeName}</h6>
                      <small class="text-muted">ID: ${candidate.employeeId}</small>
                    </div>
                    <span class="badge bg-${candidate.readinessLevel === 'Ready Now' ? 'success' : candidate.readinessLevel === 'Ready Soon' ? 'warning' : 'danger'} bg-opacity-10 
                                  text-${candidate.readinessLevel === 'Ready Now' ? 'success' : candidate.readinessLevel === 'Ready Soon' ? 'warning' : 'danger'}">
                      ${candidate.readinessLevel}
                    </span>
                  </div>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <div class="row text-center">
                      <div class="col-6">
                        <div class="h6 text-primary">${pathData.duration}</div>
                        <small class="text-muted">Timeline</small>
                      </div>
                      <div class="col-6">
                        <div class="h6 text-success">${pathData.successRate}%</div>
                        <small class="text-muted">Success Rate</small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <h6 class="text-primary mb-2">Development Phases:</h6>
                    ${pathData.phases.map((phase, phaseIndex) => `
                      <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <strong class="small">${phase.phase}</strong>
                          <small class="text-muted">${phase.duration}</small>
                        </div>
                        <div class="ms-2">
                          ${phase.activities.slice(0, 2).map(activity => `<small class="d-block text-muted">• ${activity}</small>`).join('')}
                          ${phase.activities.length > 2 ? `<small class="text-info">+${phase.activities.length - 2} more</small>` : ''}
                        </div>
                      </div>
                    `).join('')}
                  </div>
                  
                  <div class="mb-3">
                    <h6 class="text-success mb-2">Key Strengths:</h6>
                    <div>
                      ${candidate.strengths.slice(0, 2).map(strength => 
                        `<span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">${strength}</span>`
                      ).join('')}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <h6 class="text-warning mb-2">Focus Areas:</h6>
                    <div>
                      ${candidate.developmentAreas.slice(0, 2).map(area => 
                        `<span class="badge bg-warning bg-opacity-10 text-warning me-1 mb-1">${area}</span>`
                      ).join('')}
                    </div>
                  </div>
                  
                  <div class="d-grid">
                    <button class="btn btn-outline-info btn-sm" onclick="getDetailedDevelopmentPath('${candidate.employeeId}', '${targetRole}')">
                      <i class="bi bi-eye me-1"></i>View Detailed Path
                    </button>
                  </div>
                </div>
              </div>
            </div>
            `;
          }).join('')}
        </div>
      `;
    }
    
    function generatePersonalizedPath(candidate, targetRole) {
      // Generate path based on candidate's readiness level
      switch(candidate.readinessLevel) {
        case 'Ready Now':
          return {
            duration: '3-6 months',
            successRate: 85,
            phases: [
              { phase: 'Transition Prep', duration: '1 month', activities: ['Role Shadowing', 'Stakeholder Meetings', 'Handover Planning'] },
              { phase: 'Gradual Takeover', duration: '2 months', activities: ['Supervised Leadership', 'Decision Making', 'Team Integration'] },
              { phase: 'Full Responsibility', duration: '3 months', activities: ['Independent Leadership', 'Performance Review', 'Continuous Improvement'] }
            ]
          };
        case 'Ready Soon':
          return {
            duration: '6-12 months',
            successRate: 78,
            phases: [
              { phase: 'Skill Enhancement', duration: '3 months', activities: ['Competency Training', 'Leadership Workshops', 'Mentoring'] },
              { phase: 'Experience Building', duration: '4 months', activities: ['Project Leadership', 'Cross-functional Work', 'Strategic Planning'] },
              { phase: 'Readiness Validation', duration: '5 months', activities: ['Acting Role', 'Performance Assessment', 'Final Preparation'] }
            ]
          };
        default:
          return {
            duration: '12-18 months',
            successRate: 65,
            phases: [
              { phase: 'Foundation Building', duration: '6 months', activities: ['Basic Training', 'Skill Assessment', 'Development Planning'] },
              { phase: 'Competency Development', duration: '6 months', activities: ['Advanced Training', 'Practical Application', 'Progress Review'] },
              { phase: 'Leadership Preparation', duration: '6 months', activities: ['Leadership Training', 'Succession Planning', 'Final Assessment'] }
            ]
          };
      }
    }
    
    function getDetailedDevelopmentPath(employeeId, targetRole) {
      // Call API for detailed development path
      fetch('{{ route("potential_successors.development_paths") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          employee_id: employeeId,
          target_role: targetRole
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showDetailedPathModal(data.data);
        } else {
          alert('Unable to load detailed development path.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error loading development path details.');
      });
    }
    
    function showDetailedPathModal(pathData) {
      // Create and show modal with detailed path information
      const modalHtml = `
        <div class="modal fade" id="developmentPathModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Detailed Development Path - ${pathData.employee.name}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Current Readiness:</strong> ${pathData.employee.currentReadiness}<br>
                    <strong>Suitability Score:</strong> ${pathData.employee.suitabilityScore}%<br>
                    <strong>Target Role:</strong> ${pathData.targetRole}
                  </div>
                  <div class="col-md-6">
                    <strong>Estimated Timeline:</strong> ${pathData.timeline}<br>
                    <strong>Development Phases:</strong> ${Object.keys(pathData.recommendedPath).length}
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6">
                    <h6>Current Strengths:</h6>
                    ${pathData.currentStrengths.map(strength => `<span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">${strength}</span>`).join('')}
                  </div>
                  <div class="col-md-6">
                    <h6>Development Areas:</h6>
                    ${pathData.developmentAreas.map(area => `<span class="badge bg-warning bg-opacity-10 text-warning me-1 mb-1">${area}</span>`).join('')}
                  </div>
                </div>
                
                <h6>Development Plan:</h6>
                ${Object.entries(pathData.recommendedPath).map(([phase, description]) => `
                  <div class="card mb-2">
                    <div class="card-body py-2">
                      <strong>${phase.replace('phase', 'Phase ')}:</strong> ${description}
                    </div>
                  </div>
                `).join('')}
                
                <h6 class="mt-3">Milestones:</h6>
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Milestone</th>
                        <th>Target Date</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${pathData.milestones.map(milestone => `
                        <tr>
                          <td>${milestone.milestone}</td>
                          <td>${milestone.target_date}</td>
                          <td><span class="badge bg-secondary">${milestone.status}</span></td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Export Plan</button>
              </div>
            </div>
          </div>
        </div>
      `;
      
      // Remove existing modal if any
      const existingModal = document.getElementById('developmentPathModal');
      if (existingModal) {
        existingModal.remove();
      }
      
      // Add modal to page and show
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      const modal = new bootstrap.Modal(document.getElementById('developmentPathModal'));
      modal.show();
    }
    
    function displayAnalysisError(title, message) {
      const containers = ['predictiveContainer', 'developmentContainer'];
      containers.forEach(containerId => {
        const container = document.getElementById(containerId);
        if (container && container.innerHTML.includes('spinner-border')) {
          container.innerHTML = `
            <div class="alert alert-warning" role="alert">
              <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                  <strong>${title}</strong>
                  <p class="mb-0 mt-1">${message}</p>
                </div>
              </div>
            </div>
          `;
        }
      });
    }

    function displayDevelopmentPaths(data, targetRole) {
      const container = document.getElementById('developmentContainer');
      
      container.innerHTML = `
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-info mb-3">
              <i class="bi bi-map me-2"></i>AI-Generated Development Paths: ${targetRole}
            </h5>
          </div>
        </div>
        
        <!-- Development Paths -->
        <div class="row">
          ${data.developmentPaths.map((path, index) => `
            <div class="col-md-4 mb-4">
              <div class="card h-100 border-info border-opacity-25">
                <div class="card-header bg-info bg-opacity-10">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-info">${path.pathName}</h6>
                    <span class="badge ${path.difficulty === 'High' ? 'bg-danger' : path.difficulty === 'Medium' ? 'bg-warning' : 'bg-success'} bg-opacity-10 
                                      ${path.difficulty === 'High' ? 'text-danger' : path.difficulty === 'Medium' ? 'text-warning' : 'text-success'}">
                      ${path.difficulty}
                    </span>
                  </div>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <div class="row text-center">
                      <div class="col-6">
                        <div class="h6 text-primary">${path.duration}</div>
                        <small class="text-muted">Duration</small>
                      </div>
                      <div class="col-6">
                        <div class="h6 text-success">${path.successRate}%</div>
                        <small class="text-muted">Success Rate</small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="development-phases">
                    ${path.phases.map((phase, phaseIndex) => `
                      <div class="phase-item mb-3">
                        <div class="d-flex align-items-center mb-2">
                          <div class="phase-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                               style="width: 24px; height: 24px; font-size: 12px;">
                            ${phaseIndex + 1}
                          </div>
                          <div>
                            <strong>${phase.phase}</strong>
                            <small class="text-muted ms-2">(${phase.duration})</small>
                          </div>
                        </div>
                        <div class="phase-activities ms-4">
                          ${phase.activities.map(activity => `
                            <div class="small text-muted mb-1">
                              <i class="bi bi-check-circle me-1"></i>${activity}
                            </div>
                          `).join('')}
                        </div>
                      </div>
                    `).join('')}
                  </div>
                  
                  <button class="btn btn-outline-info btn-sm w-100 mt-3" onclick="selectDevelopmentPath('${path.pathName}', '${targetRole}')">
                    <i class="bi bi-arrow-right me-1"></i>Select This Path
                  </button>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
        
        <div class="alert alert-success mt-4">
          <h6><i class="bi bi-lightbulb me-2"></i>AI Recommendation</h6>
          <p class="mb-2">For ${targetRole}, the <strong>${data.developmentPaths[1].pathName}</strong> is recommended due to its high success rate (${data.developmentPaths[1].successRate}%) and balanced approach.</p>
          <p class="mb-0">This path provides comprehensive skill development while maintaining manageable progression pace.</p>
        </div>
      `;
    }

    function selectDevelopmentPath(pathName, targetRole) {
      // Show confirmation and apply to succession planning
      if (confirm(`Apply ${pathName} development path for ${targetRole} role?`)) {
        // Here you would typically save this to the database
        // For now, show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show mt-3';
        alert.innerHTML = `
          <i class="bi bi-check-circle me-2"></i>
          <strong>Development Path Applied!</strong> ${pathName} has been selected for ${targetRole} succession planning.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.getElementById('developmentContainer').appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
      }
    }

    function displaySuggestions(suggestions, targetRole) {
      const container = document.getElementById('suggestionsContainer');
      
      if (suggestions.length === 0) {
        container.innerHTML = '<div class="text-center py-4"><h5 class="text-muted">No suitable candidates found</h5></div>';
        return;
      }

      let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Top Candidates for ${targetRole}</h5>
          <span class="badge bg-info">${suggestions.length} candidates analyzed</span>
        </div>
        <div class="row">
      `;

      suggestions.slice(0, 6).forEach((suggestion, index) => {
        const badgeClass = suggestion.readiness === 'high' ? 'bg-success' : 
                          suggestion.readiness === 'medium' ? 'bg-warning' : 'bg-danger';
        
        html += `
          <div class="col-md-6 mb-3">
            <div class="card h-100 border-${suggestion.readiness === 'high' ? 'success' : suggestion.readiness === 'medium' ? 'warning' : 'danger'}">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="card-title mb-0">${suggestion.employee.first_name || 'Unknown'} ${suggestion.employee.last_name || 'Employee'}</h6>
                  <span class="badge ${badgeClass}">${suggestion.score}%</span>
                </div>
                <p class="text-muted small mb-2">ID: ${suggestion.employee.employee_id}</p>
                
                <div class="mb-2">
                  <strong class="text-success">Strengths:</strong>
                  <div class="mt-1">
                    ${suggestion.strengths.map(s => `<span class="badge bg-success bg-opacity-10 text-success me-1">${s}</span>`).join('')}
                  </div>
                </div>
                
                <div class="mb-2">
                  <strong class="text-warning">Development Areas:</strong>
                  <div class="mt-1">
                    ${suggestion.gaps.map(g => `<span class="badge bg-warning bg-opacity-10 text-warning me-1">${g}</span>`).join('')}
                  </div>
                </div>
                
                <div class="mb-3">
                  <small class="text-muted">${suggestion.recommendation}</small>
                </div>
                
                <button class="btn btn-outline-primary btn-sm w-100" onclick="selectCandidate('${suggestion.employee.employee_id}', '${targetRole}')">
                  <i class="bi bi-plus-lg me-1"></i>Add as Successor
                </button>
              </div>
            </div>
          </div>
        `;
      });

      html += '</div>';
      container.innerHTML = html;
    }

    function selectCandidate(employeeId, employeeName, targetRole) {
      // Auto-fill the form with selected candidate
      document.querySelector('select[name="employee_id"]').value = employeeId;
      document.querySelector('select[name="potential_role"]').value = targetRole;
      document.querySelector('input[name="identified_date"]').value = new Date().toISOString().split('T')[0];
      
      // Scroll to form
      document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
      
      // Show success message
      const alert = document.createElement('div');
      alert.className = 'alert alert-success alert-dismissible fade show mt-3';
      alert.innerHTML = `
        <strong>Candidate Selected!</strong> ${employeeName} has been pre-filled for ${targetRole}.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.querySelector('form').parentNode.insertBefore(alert, document.querySelector('form'));
    }

    function filterSuggestions() {
      // Re-generate suggestions with filters applied
      generateSuggestions();
    }
  </script>
</body>
</html>
