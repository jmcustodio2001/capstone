<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  
  <!-- Enhanced Dashboard Styles -->
  <style>
    .dashboard-card {
      transition: all 0.3s ease;
      border-radius: 15px;
      overflow: hidden;
    }
    
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    
    .modern-card {
      transition: all 0.3s ease;
      border-radius: 12px;
      border-left: 4px solid transparent;
    }
    
    .modern-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
      border-left-color: var(--bs-primary);
    }
    
    .performance-card {
      transition: all 0.3s ease;
      border-radius: 12px;
      background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    }
    
    .performance-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
    }
    
    .stat-icon {
      transition: all 0.3s ease;
    }
    
    .dashboard-card:hover .stat-icon {
      transform: scale(1.1);
    }
    
    .progress {
      border-radius: 10px;
      overflow: hidden;
    }
    
    .progress-bar {
      transition: width 0.6s ease;
    }
    
    .card-title {
      font-weight: 600;
      color: #2c3e50;
    }
    
    @media (max-width: 768px) {
      .dashboard-card .card-body {
        padding: 1.5rem !important;
      }
      
      .dashboard-card h2 {
        font-size: 2rem !important;
      }
      
      .modern-card h3 {
        font-size: 1.5rem !important;
      }
    }
    
    /* Card Grid Animations */
    .row > [class*="col-"] {
      animation: fadeInUp 0.6s ease forwards;
      opacity: 0;
      transform: translateY(20px);
    }
    
    .row > [class*="col-"]:nth-child(1) { animation-delay: 0.1s; }
    .row > [class*="col-"]:nth-child(2) { animation-delay: 0.2s; }
    .row > [class*="col-"]:nth-child(3) { animation-delay: 0.3s; }
    .row > [class*="col-"]:nth-child(4) { animation-delay: 0.4s; }
    
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Enhanced Quick Actions */
    .btn {
      transition: all 0.3s ease;
      border-radius: 8px;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
  </style>

</head>
<body>

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

  <!-- Overlay for mobile -->
  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <!-- Main Content -->
  <main id="main-content">
    <div class="container-fluid">
      <!-- Page Header -->
      <div class="page-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center page-header">
          <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
              <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
              <h2 class="fw-bold mb-1">Admin Dashboard</h2>
              <p class="text-muted mb-0">
                Welcome back,
                @if(Auth::guard('admin')->check())
                  {{ Auth::guard('admin')->user()->name }}
                @else
                  Admin
                @endif
                ! Here's what's happening with your travel business today.
              </p>
            </div>
          </div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </nav>
        </div>
      </div>

        <!-- Enhanced Statistics Cards Grid -->
        <div class="row g-4 mb-4">
          <!-- Primary Statistics -->
          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card dashboard-card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ isset($totalEmployees) ? number_format($totalEmployees) : 'N/A' }}</h2>
                    <p class="mb-0 opacity-75">Total Employees</p>
                    <small class="opacity-50">Active workforce</small>
                  </div>
                  <div class="stat-icon bg-white text-primary" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <i class="bi bi-people" style="font-size: 32px; color: #667eea;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card dashboard-card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
              <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ isset($activeCourses) ? $activeCourses : 'N/A' }}</h2>
                    <p class="mb-0 opacity-75">Active Courses</p>
                    <small class="opacity-50">Available training</small>
                  </div>
                  <div class="stat-icon bg-white text-primary" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <i class="bi bi-book" style="font-size: 32px; color: #f093fb;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card dashboard-card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
              <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ isset($trainingSessions) ? $trainingSessions : 'N/A' }}</h2>
                    <p class="mb-0 opacity-75">Training Sessions</p>
                    <small class="opacity-50">Ongoing sessions</small>
                  </div>
                  <div class="stat-icon bg-white text-primary" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <i class="bi bi-easel" style="font-size: 32px; color: #4facfe;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card dashboard-card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
              <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ isset($completedTrainings) ? $completedTrainings : '0' }}</h2>
                    <p class="mb-0 opacity-75">Completed Trainings</p>
                    <small class="opacity-50">Total completions</small>
                  </div>
                  <div class="stat-icon bg-white text-primary" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <i class="bi bi-check-circle" style="font-size: 32px; color: #fa709a;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Secondary Statistics Grid -->
        <div class="row g-4 mb-4">
          <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card modern-card shadow-sm border-0 h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                    <i class="bi bi-diagram-3" style="font-size: 28px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h3 class="fw-bold mb-1" style="font-size: 2rem;">{{ isset($successionPlans) ? $successionPlans : '0' }}</h3>
                    <p class="text-muted mb-0">Succession Plans</p>
                    <div class="progress mt-2" style="height: 4px; border-radius: 6px; background-color: #f8f9fa;">
                      <div class="progress-bar" style="width: 65%; background: linear-gradient(90deg, #dc3545, #e74c3c); border-radius: 6px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card modern-card shadow-sm border-0 h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                    <i class="bi bi-trophy" style="font-size: 28px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h3 class="fw-bold mb-1" style="font-size: 2rem;">{{ isset($competencies) ? $competencies : '0' }}</h3>
                    <p class="text-muted mb-0">Competencies</p>
                    <div class="progress mt-2" style="height: 4px; border-radius: 6px; background-color: #f8f9fa;">
                      <div class="progress-bar" style="width: 80%; background: linear-gradient(90deg, #ffc107, #ffb300); border-radius: 6px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card modern-card shadow-sm border-0 h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-info bg-opacity-10 text-info me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                    <i class="bi bi-clock-history" style="font-size: 28px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h3 class="fw-bold mb-1" style="font-size: 2rem;">{{ isset($attendanceLogs) ? $attendanceLogs : '0' }}</h3>
                    <p class="text-muted mb-0">Attendance Logs</p>
                    <div class="progress mt-2" style="height: 4px; border-radius: 6px; background-color: #f8f9fa;">
                      <div class="progress-bar" style="width: 45%; background: linear-gradient(90deg, #17a2b8, #20c997); border-radius: 6px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card modern-card shadow-sm border-0 h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                    <i class="bi bi-clipboard-check" style="font-size: 28px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h3 class="fw-bold mb-1" style="font-size: 2rem;">{{ isset($trainingRequestsCount) ? $trainingRequestsCount : '0' }}</h3>
                    <p class="text-muted mb-0">Training Requests</p>
                    <div class="progress mt-2" style="height: 4px; border-radius: 6px; background-color: #f8f9fa;">
                      <div class="progress-bar" style="width: 70%; background: linear-gradient(90deg, #6c757d, #495057); border-radius: 6px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Metrics Grid -->
        <div class="row g-4 mb-4">
          <div class="col-lg-6">
            <div class="card performance-card shadow-sm border-0 h-100">
              <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h5 class="card-title mb-0 d-flex align-items-center">
                  <i class="bi bi-graph-up text-success me-2"></i>
                  Training Performance
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                      @php
                        $completionRate = 0;
                        if (isset($completedTrainings) && isset($totalEmployees) && isset($activeCourses)) {
                          $totalPossibleCompletions = ($totalEmployees ?? 1) * ($activeCourses ?? 1);
                          $completionRate = $totalPossibleCompletions > 0 ? min(round(($completedTrainings / $totalPossibleCompletions) * 100), 100) : 0;
                        }
                      @endphp
                      <h4 class="fw-bold text-success mb-1">{{ $completionRate }}%</h4>
                      <small class="text-muted">Completion Rate</small>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                      <h4 class="fw-bold text-primary mb-1">{{ isset($activeCourses) ? $activeCourses : '0' }}</h4>
                      <small class="text-muted">Active Courses</small>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="small">Training Progress</span>
                    <span class="small text-muted">{{ $completionRate }}%</span>
                  </div>
                  <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                    <div class="progress-bar" style="width: {{ $completionRate }}%; background: linear-gradient(90deg, #28a745, #20c997); border-radius: 10px; box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card performance-card shadow-sm border-0 h-100">
              <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h5 class="card-title mb-0 d-flex align-items-center">
                  <i class="bi bi-people-fill text-info me-2"></i>
                  Employee Engagement
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                      <h4 class="fw-bold text-info mb-1">{{ isset($trainingRequestsCount) ? $trainingRequestsCount : '0' }}</h4>
                      <small class="text-muted">Requests</small>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                      <h4 class="fw-bold text-warning mb-1">{{ isset($competencies) ? $competencies : '0' }}</h4>
                      <small class="text-muted">Skills Tracked</small>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  @php
                    $engagementRate = 0;
                    if (isset($trainingRequestsCount) && isset($totalEmployees)) {
                      $engagementRate = ($totalEmployees ?? 1) > 0 ? min(round(($trainingRequestsCount / ($totalEmployees ?? 1)) * 100), 100) : 0;
                    }
                  @endphp
                  <div class="d-flex justify-content-between mb-2">
                    <span class="small">Engagement Level</span>
                    <span class="small text-muted">{{ $engagementRate }}%</span>
                  </div>
                  <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                    <div class="progress-bar" style="width: {{ $engagementRate }}%; background: linear-gradient(90deg, #17a2b8, #6f42c1); border-radius: 10px; box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      <!-- Recent Activity & Quick Actions -->
      <div class="row g-4 mt-4">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Recent Trainings</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Trainer</th>
                      <th>Course</th>
                      <th>Date</th>
                      <th>Status</th>
                      <th>Participants</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recentTrainings ?? [] as $training)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <img src="{{ $training->employee && $training->employee->profile_picture ? asset('storage/' . $training->employee->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode(($training->employee ? $training->employee->first_name : 'User') ?? 'User') }}" class="rounded-circle me-2" width="32" height="32" alt="Trainer">
                          <span>{{ $training->employee ? ($training->employee->first_name ?? 'N/A') : 'N/A' }} {{ $training->employee ? ($training->employee->last_name ?? '') : '' }}</span>
                        </div>
                      </td>
                      <td>{{ $training->display_title ?? ($training->course ? $training->course->course_title : null) ?? $training->training_title ?? 'Training Course' }}</td>
                      <td>{{ \Carbon\Carbon::parse($training->training_date)->format('M d, Y') }}</td>
                      <td>
                        @if($training->status == 'Completed')
                          <span class="badge bg-success">Completed</span>
                        @elseif($training->status == 'Ongoing')
                          <span class="badge bg-warning">Ongoing</span>
                        @else
                          <span class="badge bg-info">{{ $training->status ?? 'Scheduled' }}</span>
                        @endif
                      </td>
                      <td>
                        @if(isset($training->participant_count))
                          <span class="badge bg-primary">{{ $training->participant_count }} {{ $training->participant_count == 1 ? 'participant' : 'participants' }}</span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted">No recent trainings found.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <!-- Recent Completed Trainings -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Recent Completed Trainings</h5>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
              @forelse($recentCompletedTrainings ?? [] as $completion)
              <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                <img src="{{ isset($completion['employee']) && $completion['employee'] && $completion['employee']->profile_picture ? asset('storage/' . $completion['employee']->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode((isset($completion['employee']) && $completion['employee'] ? $completion['employee']->first_name : 'User') ?? 'User') }}" class="rounded-circle me-3" width="40" height="40" alt="Employee">
                <div class="flex-grow-1">
                  <h6 class="mb-1">{{ isset($completion['employee']) && $completion['employee'] ? $completion['employee']->first_name ?? 'N/A' : 'N/A' }} {{ isset($completion['employee']) && $completion['employee'] ? $completion['employee']->last_name ?? '' : '' }}</h6>
                  <p class="mb-1 small">{{ $completion['training_title'] ?? 'Training Course' }}</p>
                  <div class="d-flex align-items-center">
                    <small class="text-muted me-2">{{ $completion['completion_date'] ? \Carbon\Carbon::parse($completion['completion_date'])->format('M d, Y') : 'N/A' }}</small>
                    @if(($completion['source'] ?? '') == 'Employee Reported')
                      <span class="badge bg-info text-white small">Self-Reported</span>
                    @else
                      <span class="badge bg-success text-white small">Admin Assigned</span>
                    @endif
                    @if(($completion['status'] ?? '') == 'Pending')
                      <span class="badge bg-warning text-dark small ms-1">Pending</span>
                    @elseif(($completion['status'] ?? '') == 'Verified')
                      <span class="badge bg-success text-white small ms-1">Verified</span>
                    @endif
                  </div>
                </div>
              </div>
              @empty
              <div class="text-muted text-center">No recent completed trainings found.</div>
              @endforelse
            </div>
          </div>

          <!-- Live Activity Log -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Live Activity Log</h5>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
              <ul class="list-group list-group-flush" id="activity-log-list">
                <li class="list-group-item text-muted">Loading activity...</li>
              </ul>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="card shadow-sm border-0">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('admin.employee_trainings_dashboard.create') }}" class="btn btn-primary">
                  <i class="bi bi-plus-circle me-2"></i>New Training
                </a>
                <a href="{{ route('employee.list') }}" class="btn btn-outline-primary">
                  <i class="bi bi-person-plus me-2"></i>View Employees
                </a>
                <a href="{{ route('admin.course_management.index') }}" class="btn btn-outline-primary">
                  <i class="bi bi-bookmark-plus me-2"></i>Manage Courses
                </a>
                <a href="{{ route('activity_logs.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-file-earmark-text me-2"></i>Activity Reports
                </a>
              </div>
            </div>
          </div>

          <!-- Top Skills in Demand -->
          <div class="card shadow-sm border-0 mt-4">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Top Skills in Demand</h5>
            </div>
            <div class="card-body">
              @forelse($topSkills ?? [] as $i => $skill)
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small">{{ $skill['name'] }}</span>
                  <span class="small text-muted">{{ $skill['percent'] }}%</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar @if($i==0) bg-primary @elseif($i==1) bg-success @elseif($i==2) bg-warning @elseif($i==3) bg-info @else bg-secondary @endif" style="width: {{ $skill['percent'] }}%"></div>
                </div>
              </div>
              @empty
              <div class="text-muted text-center">No skills in demand found.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Live Activity Log AJAX Polling -->
  <script>
    function fetchActivityLog() {
      const list = document.getElementById('activity-log-list');
      if (list) {
        list.innerHTML = '<li class="list-group-item text-muted">Loading activity...</li>';
      }

      // Check if route exists before making request
      try {
        fetch("{{ route('activity_logs.index') }}")
          .then(response => {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
            return response.json();
          })
          .then(data => {
            if (!list) return;
            list.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
              list.innerHTML = '<li class="list-group-item text-muted">No recent activity.</li>';
              return;
            }
            data.forEach(log => {
              const user = log.user ? log.user.name : 'System';
              const time = new Date(log.created_at).toLocaleString();
              const item = document.createElement('li');
              item.className = 'list-group-item';
              item.innerHTML = `<strong>[${log.module}]</strong> ${log.action}: ${log.description}<br><small class="text-muted">By: ${user} &bull; ${time}</small>`;
              list.appendChild(item);
            });
          })
          .catch(error => {
            if (list) {
              list.innerHTML = `<li class="list-group-item text-muted">Activity log temporarily unavailable.</li>`;
            }
          });
      } catch (error) {
        if (list) {
          list.innerHTML = '<li class="list-group-item text-muted">Activity log temporarily unavailable.</li>';
        }
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      fetchActivityLog();
      setInterval(fetchActivityLog, 10000); // Poll every 10 seconds
    });
  </script>

</body>
</html>
