<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">

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

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-people"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($totalEmployees) ? number_format($totalEmployees) : 'N/A' }}</h3>
                    <p class="text-muted mb-0 small">Total Employees</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-book"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($activeCourses) ? $activeCourses : 'N/A' }}</h3>
                    <p class="text-muted mb-0 small">Active Courses</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-easel"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($trainingSessions) ? $trainingSessions : 'N/A' }}</h3>
                    <p class="text-muted mb-0 small">Training Sessions</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-person-badge"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($employeeUsers) ? number_format($employeeUsers) : 'N/A' }}</h3>
                    <p class="text-muted mb-0 small">Employee Self-Service Users</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Statistics Row -->
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="bi bi-diagram-3"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($successionPlans) ? $successionPlans : '0' }}</h3>
                    <p class="text-muted mb-0 small">Succession Plans</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                    <i class="bi bi-trophy"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($competencies) ? $competencies : '0' }}</h3>
                    <p class="text-muted mb-0 small">Competencies</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-check-circle"></i>
                  </div>
                  <div>
                    <h3 class="fw-bold mb-0">{{ isset($completedTrainings) ? $completedTrainings : '0' }}</h3>
                    <p class="text-muted mb-0 small">Completed Trainings</p>
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
                          @php
                            $employee = null;
                            $firstName = 'Unknown';
                            $lastName = 'Employee';

                            if (isset($training) && is_object($training) && isset($training->employee) && is_object($training->employee)) {
                              $employee = $training->employee;
                              $firstName = $employee->first_name ?? 'Unknown';
                              $lastName = $employee->last_name ?? 'Employee';
                            }
                          @endphp

                          @if($employee)
                            @php
                              // Profile picture logic - consistent with other HR modules
                              $profilePicUrl = '';
                              if (optional($employee)->profile_picture) {
                                $profilePicUrl = asset('storage/' . $employee->profile_picture);
                              } else {
                                // Fallback to UI Avatars with consistent color scheme
                                $employeeId = optional($employee)->employee_id ?? 'EMP';
                                $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                                $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                                $colorIndex = crc32($employeeId) % count($colors);
                                $bgColor = $colors[$colorIndex];
                                $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background={$bgColor}&color=ffffff&size=128&bold=true";
                              }
                            @endphp

                            <img src="{{ $profilePicUrl }}"
                                 class="rounded-circle me-2"
                                 width="32"
                                 height="32"
                                 style="object-fit: cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(substr($firstName, 0, 1) . substr($lastName, 0, 1)) }}&background=6c757d&color=ffffff&size=128&bold=true'"
                                 alt="Trainer">
                            <span>{{ $firstName }} {{ $lastName }}</span>
                          @else
                            <img src="https://ui-avatars.com/api/?name=NA&background=6c757d&color=ffffff&size=128&bold=true"
                                 class="rounded-circle me-2"
                                 width="32"
                                 height="32"
                                 alt="No Trainer">
                            <span class="text-muted">N/A</span>
                          @endif
                        </div>
                      </td>
                      <td>{{ optional($training->course)->course_title ?? 'N/A' }}</td>
                      <td>{{ $training->training_date ? \Carbon\Carbon::parse($training->training_date)->format('M d, Y') : 'N/A' }}</td>
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
