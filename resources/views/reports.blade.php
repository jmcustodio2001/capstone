<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels - Reports</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">

  <!-- Reports Dashboard Styles -->
  <style>
    :root {
      --report-primary: #1a237e;
      --report-secondary: #2962ff;
      --report-success: #00c853;
      --report-warning: #ff9100;
      --report-danger: #ff5252;
      --report-light: #f5f7ff;
      --report-dark: #0d1b2a;
      --report-surface: #ffffff;
      --report-border: #e0e7ff;
    }

    body {
      background-color: #f8fafc;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
      border-radius: 16px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(31, 38, 135, 0.15);
      border-color: var(--report-secondary);
    }

    .metric-card {
      background: linear-gradient(135deg, var(--report-surface) 0%, #f8fafc 100%);
      border-radius: 14px;
      padding: 1.75rem;
      border: 1px solid var(--report-border);
      height: 100%;
      position: relative;
      overflow: hidden;
    }

    .metric-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--report-secondary), var(--report-primary));
    }

    .stat-highlight {
      font-size: 2.5rem;
      font-weight: 700;
      background: linear-gradient(135deg, var(--report-primary), var(--report-secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin: 0.5rem 0;
    }

    .chart-container {
      background: var(--report-surface);
      border-radius: 16px;
      padding: 2rem;
      border: 1px solid var(--report-border);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .filter-panel {
      background: var(--report-surface);
      border-radius: 16px;
      padding: 1.75rem;
      border: 1px solid var(--report-border);
      margin-bottom: 2rem;
    }

    .stat-badge {
      font-size: 0.75rem;
      padding: 0.35rem 1rem;
      border-radius: 20px;
      font-weight: 600;
      letter-spacing: 0.3px;
    }

    .report-table {
      font-size: 0.9rem;
      --bs-table-bg: transparent;
    }

    .report-table thead {
      background: linear-gradient(135deg, var(--report-primary), #2d3b8c);
      color: white;
    }

    .report-table th {
      font-weight: 600;
      padding: 1rem 1.25rem;
      border: none;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .report-table tbody tr {
      border-bottom: 1px solid var(--report-border);
      transition: all 0.2s ease;
    }

    .report-table tbody tr:hover {
      background-color: rgba(41, 98, 255, 0.04);
      transform: translateX(4px);
    }

    .report-table td {
      padding: 1.25rem;
      vertical-align: middle;
      color: var(--report-dark);
    }

    .action-btn {
      background: linear-gradient(135deg, var(--report-secondary), #1a56db);
      border: none;
      border-radius: 10px;
      padding: 0.75rem 1.75rem;
      color: white;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(41, 98, 255, 0.3);
      color: white;
    }

    .date-range-picker {
      border: 2px solid var(--report-border);
      border-radius: 10px;
      padding: 0.75rem 1.25rem;
      background: white;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .date-range-picker:focus {
      border-color: var(--report-secondary);
      box-shadow: 0 0 0 3px rgba(41, 98, 255, 0.1);
      outline: none;
    }

    .trend-indicator {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      font-weight: 600;
      font-size: 0.85rem;
    }

    .trend-up {
      color: var(--report-success);
    }

    .trend-down {
      color: var(--report-danger);
    }

    .progress-thin {
      height: 8px;
      border-radius: 4px;
      background-color: #e0e7ff;
      overflow: hidden;
    }

    .progress-thin .progress-bar {
      border-radius: 4px;
      background: linear-gradient(90deg, var(--report-secondary), var(--report-primary));
    }

    .report-tabs {
      background: white;
      border-radius: 12px;
      padding: 0.5rem;
      border: 1px solid var(--report-border);
      display: inline-flex;
    }

    .report-tabs .nav-link {
      padding: 0.875rem 2rem;
      border-radius: 10px;
      font-weight: 600;
      color: var(--report-dark);
      transition: all 0.3s ease;
      border: none;
      margin: 0 0.25rem;
    }

    .report-tabs .nav-link:hover {
      background-color: rgba(41, 98, 255, 0.08);
      color: var(--report-secondary);
    }

    .report-tabs .nav-link.active {
      background: linear-gradient(135deg, var(--report-secondary), var(--report-primary));
      color: white;
      box-shadow: 0 4px 12px rgba(41, 98, 255, 0.25);
    }

    .export-toolbar {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .section-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--report-primary);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .section-title::before {
      content: '';
      width: 4px;
      height: 24px;
      background: linear-gradient(180deg, var(--report-secondary), var(--report-primary));
      border-radius: 2px;
    }

    .metric-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, rgba(41, 98, 255, 0.1), rgba(26, 35, 126, 0.1));
      color: var(--report-secondary);
      font-size: 1.5rem;
    }

    /* Animations */
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-slide {
      animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .row > [class*="col-"] {
      opacity: 0;
    }

    .row > [class*="col-"]:nth-child(1) { animation-delay: 0.1s; }
    .row > [class*="col-"]:nth-child(2) { animation-delay: 0.2s; }
    .row > [class*="col-"]:nth-child(3) { animation-delay: 0.3s; }
    .row > [class*="col-"]:nth-child(4) { animation-delay: 0.4s; }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--report-secondary);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--report-primary);
    }

    @media (max-width: 768px) {
      .chart-container {
        padding: 1.25rem;
      }
      
      .metric-card {
        padding: 1.25rem;
      }
      
      .report-tabs {
        flex-direction: column;
        width: 100%;
      }
      
      .report-tabs .nav-link {
        margin: 0.25rem 0;
        text-align: center;
      }
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
    <div class="container-fluid py-4">
      <!-- Reports Header -->
      <div class="page-header-container mb-5">
        <div class="d-flex justify-content-between align-items-center page-header">
          <div class="d-flex align-items-center gap-4">
            <div class="dashboard-logo">
              <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="width: 60px;">
            </div>
            <div>
              <h1 class="fw-bold mb-2" style="color: var(--report-primary);">Analytics Dashboard</h1>
              <p class="text-muted mb-0 fs-6">
                <i class="bi bi-graph-up me-2"></i>Real-time insights and performance metrics
              </p>
            </div>
          </div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
              <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
              <li class="breadcrumb-item active" aria-current="page" style="color: var(--report-secondary); font-weight: 600;">Analytics</li>
            </ol>
          </nav>
        </div>
      </div>

      <!-- Quick Stats Row -->
      <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
          <div class="metric-card animate-slide">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span class="text-muted small fw-semibold">TOTAL COURSES</span>
                <h2 class="stat-highlight">{{ count($courses) ?? 12 }}</h2>
              </div>
              <div class="metric-icon">
                <i class="bi bi-journal-bookmark"></i>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="trend-indicator trend-up">
                <i class="bi bi-arrow-up-right"></i>
                12% growth
              </span>
              <small class="text-muted">Last 30 days</small>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="metric-card animate-slide">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span class="text-muted small fw-semibold">ACTIVE LEARNERS</span>
                <h2 class="stat-highlight">{{ isset($employees) ? count($employees) : 84 }}</h2>
              </div>
              <div class="metric-icon">
                <i class="bi bi-people"></i>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="trend-indicator trend-up">
                <i class="bi bi-arrow-up-right"></i>
                8% increase
              </span>
              <small class="text-muted">Currently enrolled</small>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="metric-card animate-slide">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span class="text-muted small fw-semibold">CERTIFICATIONS</span>
                <h2 class="stat-highlight">{{ $certifications ?? 156 }}</h2>
              </div>
              <div class="metric-icon">
                <i class="bi bi-award"></i>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="trend-indicator trend-up">
                <i class="bi bi-arrow-up-right"></i>
                18% increase
              </span>
              <small class="text-muted">This quarter</small>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="metric-card animate-slide">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span class="text-muted small fw-semibold">AVG. TRAINING TIME</span>
                <h2 class="stat-highlight">{{ $avgTrainingTime ?? '4.2 hrs' }}</h2>
              </div>
              <div class="metric-icon">
                <i class="bi bi-clock"></i>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="trend-indicator trend-down">
                <i class="bi bi-arrow-down-right"></i>
                12% decrease
              </span>
              <small class="text-muted">Per employee</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Control Panel -->
      <div class="filter-panel mb-5">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="d-flex align-items-center gap-3">
              <h5 class="section-title mb-0">Report Controls</h5>
              <div class="report-tabs">
                <a class="nav-link active" href="#training" data-bs-toggle="tab">Training</a>
                <a class="nav-link" href="#employee" data-bs-toggle="tab">Employees</a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-md-end align-items-center gap-3">
              <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0">
                  <i class="bi bi-calendar"></i>
                </span>
                <input type="text" class="form-control date-range-picker border-start-0" placeholder="Select date range">
              </div>
              <div class="export-toolbar">
                <button class="action-btn" id="export-excel" style="background: linear-gradient(135deg, #00c853, #00b248);">
                  <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button class="action-btn" id="export-csv" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                  <i class="bi bi-file-earmark-text"></i> CSV
                </button>
                <button class="action-btn" id="export-print" style="background: linear-gradient(135deg, #ff9100, #e67e22);">
                  <i class="bi bi-printer"></i> Print
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Training Reports Tab -->
        <div class="tab-pane fade show active" id="training">
          <div class="row g-4">
            <div class="col-12">
              <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <h5 class="section-title mb-0">Training Performance Overview</h5>
                  <small class="text-muted">Sorted by completion rate</small>
                </div>
                <div class="table-responsive">
                  <table class="table report-table table-hover">
                    <thead>
                      <tr>
                        <th>Course Name</th>
                        <th>Department</th>
                        <th>Participants</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
                        <th>Avg. Score</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($courses as $course)
                      <tr class="animate-slide">
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text text-primary"></i>
                            <strong>{{ $course['name'] }}</strong>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 stat-badge">
                            {{ $course['department'] }}
                          </span>
                        </td>
                        <td class="fw-semibold">{{ $course['participants'] }}</td>
                        <td class="fw-semibold">{{ $course['completed'] }}</td>
                        <td>
                          <div class="d-flex align-items-center gap-3">
                            <div class="progress progress-thin flex-grow-1" style="max-width: 150px;">
                              <div class="progress-bar" role="progressbar" style="width: {{ $course['completion_percent'] }}%"></div>
                            </div>
                            <span class="fw-bold">{{ $course['completion_percent'] }}%</span>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 stat-badge">
                            {{ $course['avg_score'] }}
                          </span>
                        </td>
                        <td>
                          <span class="badge {{ $course['status_class'] ?? 'bg-secondary' }} stat-badge">
                            {{ $course['status_text'] }}
                          </span>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Employee Reports Tab -->
        <div class="tab-pane fade" id="employee">
          <div class="row g-4">
            <div class="col-12">
              <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <h5 class="section-title mb-0">Employee Learning Progress</h5>
                  <small class="text-muted">Real-time training status</small>
                </div>
                <div class="table-responsive">
                  <table class="table report-table table-hover">
                    <thead>
                      <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Avg. Score</th>
                        <th>Progress</th>
                        <th>Last Activity</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($employees as $emp)
                      <tr class="animate-slide">
                        <td>
                          <span class="badge bg-dark bg-opacity-10 text-dark stat-badge">
                            {{ $emp['id'] }}
                          </span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-circle text-primary"></i>
                            {{ $emp['name'] }}
                          </div>
                        </td>
                        <td>
                          @if($emp['department'])
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 stat-badge">
                              {{ $emp['department'] }}
                            </span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td class="fw-semibold">{{ $emp['assigned'] }}</td>
                        <td class="fw-semibold">{{ $emp['completed'] }}</td>
                        <td>
                          @if($emp['avg_score'])
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 stat-badge">
                              {{ $emp['avg_score'] }}
                            </span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-3">
                            <div class="progress progress-thin flex-grow-1" style="max-width: 120px;">
                              <div class="progress-bar" role="progressbar" style="width: {{ $emp['progress_percent'] }}%"></div>
                            </div>
                            <small class="fw-bold">{{ $emp['progress_percent'] }}%</small>
                          </div>
                        </td>
                        <td>
                          <small class="text-muted">
                            <i class="bi bi-clock-history me-1"></i>
                            {{ $emp['last_activity'] ?? '-' }}
                          </small>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Metrics -->
      <div class="row g-4 mt-5">
        <div class="col-lg-4">
          <div class="chart-container">
            <h5 class="section-title mb-4">Training Efficiency</h5>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="text-muted">Course Completion Rate</span>
              <span class="fw-bold text-primary">78%</span>
            </div>
            <div class="progress progress-thin mb-4">
              <div class="progress-bar" style="width: 78%"></div>
            </div>
            <div class="d-flex justify-content-between text-center">
              <div>
                <div class="fw-bold fs-4">92%</div>
                <small class="text-muted">Satisfaction</small>
              </div>
              <div>
                <div class="fw-bold fs-4">4.2</div>
                <small class="text-muted">Avg. Rating</small>
              </div>
              <div>
                <div class="fw-bold fs-4">24h</div>
                <small class="text-muted">Avg. Time</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="chart-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="section-title mb-0">Quick Actions</h5>
              <button class="action-btn">
                <i class="bi bi-plus-circle me-2"></i> Generate Report
              </button>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="border rounded p-3 text-center hover-shadow">
                  <div class="metric-icon mx-auto mb-2">
                    <i class="bi bi-download"></i>
                  </div>
                  <h6 class="fw-semibold mb-1">Export All Data</h6>
                  <small class="text-muted">CSV, Excel, PDF</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="border rounded p-3 text-center hover-shadow">
                  <div class="metric-icon mx-auto mb-2">
                    <i class="bi bi-graph-up"></i>
                  </div>
                  <h6 class="fw-semibold mb-1">View Analytics</h6>
                  <small class="text-muted">Detailed insights</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="border rounded p-3 text-center hover-shadow">
                  <div class="metric-icon mx-auto mb-2">
                    <i class="bi bi-bell"></i>
                  </div>
                  <h6 class="fw-semibold mb-1">Set Alerts</h6>
                  <small class="text-muted">Notifications</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Initialize animations
    document.addEventListener('DOMContentLoaded', function() {
      // Animate cards on load
      const cards = document.querySelectorAll('.row > [class*="col-"]');
      cards.forEach((card, index) => {
        card.style.animation = `slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s forwards`;
      });

      // Add hover effects to quick action cards
      const quickActions = document.querySelectorAll('.hover-shadow');
      quickActions.forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-4px)';
          this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.1)';
          this.style.borderColor = 'var(--report-secondary)';
        });
        
        card.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
          this.style.boxShadow = 'none';
          this.style.borderColor = '';
        });
      });

      // Tab switching with enhanced animation
      const tabLinks = document.querySelectorAll('.report-tabs .nav-link');
      tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          // Update active tab styles
          tabLinks.forEach(l => l.classList.remove('active'));
          this.classList.add('active');
          
          // Animate content
          const target = this.getAttribute('href');
          const tabContent = document.querySelector(target + ' .animate-slide');
          if (tabContent) {
            tabContent.style.animation = 'none';
            setTimeout(() => {
              tabContent.style.animation = 'slideUp 0.4s ease forwards';
            }, 10);
          }
        });
      });

      // Export button functionality
      const exportExcelBtn = document.getElementById('export-excel');
      const exportCsvBtn = document.getElementById('export-csv');
      const exportPrintBtn = document.getElementById('export-print');

      const exportUrl = function(type) {
        return `{{ route('admin.reports.export') }}?type=${type}`;
      }

      if (exportExcelBtn) exportExcelBtn.addEventListener('click', function() {
        window.location.href = exportUrl('excel');
      });
      
      if (exportCsvBtn) exportCsvBtn.addEventListener('click', function() {
        window.location.href = exportUrl('csv');
      });
      
      if (exportPrintBtn) exportPrintBtn.addEventListener('click', function() {
        window.open(exportUrl('print'), '_blank');
      });

      // Add loading animation to export buttons
      const exportButtons = [exportExcelBtn, exportCsvBtn, exportPrintBtn];
      exportButtons.forEach(btn => {
        if (btn) {
          btn.addEventListener('click', function() {
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Processing...';
            this.disabled = true;
            
            setTimeout(() => {
              this.innerHTML = originalHTML;
              this.disabled = false;
            }, 2000);
          });
        }
      });

      // Date range picker placeholder functionality
      const datePicker = document.querySelector('.date-range-picker');
      if (datePicker) {
        datePicker.addEventListener('focus', function() {
          this.style.borderColor = 'var(--report-secondary)';
          this.style.boxShadow = '0 0 0 3px rgba(41, 98, 255, 0.1)';
        });
        
        datePicker.addEventListener('blur', function() {
          this.style.borderColor = '';
          this.style.boxShadow = '';
        });
      }
    });
  </script>
</body>
</html>