<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Employee Attendance Logs</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    .simulation-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      transition: transform 0.3s ease;
    }

    .simulation-card:hover {
      transform: translateY(-5px);
    }

    .card-header-custom {
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
      border-radius: 12px 12px 0 0 !important;
    }

    .badge-simulation {
      padding: 0.5em 0.8em;
      font-weight: 500;
      letter-spacing: 0.5px;
      border-radius: 6px;
    }

    .table th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #495057;
    }

    .clock-container {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border-radius: 16px;
      color: white;
      padding: 2rem;
      text-align: center;
      margin-bottom: 2rem;
      box-shadow: 0 10px 30px rgba(67, 97, 238, 0.15);
    }

    #current-time {
      font-size: 4rem;
      font-weight: 700;
      letter-spacing: 2px;
      font-family: 'Courier New', monospace;
    }

    #current-date {
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
      opacity: 0.9;
    }

    .attendance-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-attendance {
      padding: 0.8rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .btn-time-in {
      background: linear-gradient(135deg, #4cc9f0, #4895ef);
      border: none;
    }

    .btn-time-out {
      background: linear-gradient(135deg, #f72585, #b5179e);
      border: none;
    }

    .btn-attendance:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .btn-attendance:disabled {
      opacity: 0.7;
      transform: none;
    }

    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      text-align: center;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0.5rem 0;
      color: var(--primary-color);
    }

    .stat-label {
      color: #6c757d;
      font-weight: 500;
    }

    .filter-container {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 2rem;
    }

    .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .page-link {
      color: var(--primary-color);
    }

    .table-hover tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }

    .status-present {
      background-color: rgba(76, 201, 240, 0.1);
      color: #4895ef;
    }

    .status-absent {
      background-color: rgba(247, 37, 133, 0.1);
      color: #b5179e;
    }

    .status-late {
      background-color: rgba(255, 159, 67, 0.1);
      color: #ff9f43;
    }

    .status-early-departure,
    .badge.status-early-departure,
    .badge-simulation.status-early-departure,
    span.badge.badge-simulation.status-early-departure {
      background-color: #dc3545 !important;
      color: #ffffff !important;
      border: 2px solid #dc3545 !important;
      font-weight: 700 !important;
      font-size: 0.85rem !important;
      text-shadow: none !important;
    }

    /* Additional fallback for Early Departure */
    .badge:contains("Early Departure") {
      background-color: #dc3545 !important;
      color: #ffffff !important;
    }

    .status-overtime {
      background-color: rgba(155, 89, 182, 0.15);
      color: #8e44ad;
      border: 1px solid rgba(142, 68, 173, 0.3);
    }

    @media (max-width: 768px) {
      #current-time {
        font-size: 2.5rem;
      }

      .attendance-actions {
        flex-direction: column;
        align-items: center;
      }

      .btn-attendance {
        width: 100%;
        max-width: 300px;
      }

      .stats-container {
        grid-template-columns: 1fr;
      }
    }

    /* Print Styles */
    @media print {
      * {
        visibility: hidden !important;
      }

      .print-content, .print-content * {
        visibility: visible !important;
      }

      .print-content {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
      }

      .card-header {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        border: none !important;
        padding: 10px 0 !important;
      }

      .badge {
        border: 1px solid #000 !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
      }

      .table th {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        border: 1px solid #000 !important;
        font-weight: bold !important;
      }

      .table td {
        border: 1px solid #000 !important;
      }

      .table {
        border-collapse: collapse !important;
        width: 100% !important;
      }

      .btn, .pagination, .filter-container, .clock-container, .stats-container,
      .page-header-container, #main-content > *:not(.print-content) {
        display: none !important;
      }
    }
  </style>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

<main id="main-content" class="expanded" style="margin-left: 280px; padding: 2rem; margin-top: 3.5rem; transition: margin-left 0.3s cubic-bezier(.4,2,.6,1);">
<style>
#main-content.expanded {
  margin-left: 0 !important;
  transition: margin-left 0.3s cubic-bezier(.4,2,.6,1);
}
#main-content.collapsed {
  margin-left: 280px !important;
  transition: margin-left 0.3s cubic-bezier(.4,2,.6,1);
}
</style>
<script>
// Sidebar toggle logic to expand/collapse main content
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.querySelector('.sidebar, #sidebar');
  const mainContent = document.getElementById('main-content');
  const toggleBtn = document.querySelector('.sidebar-toggle, #sidebarToggle, .toggle-sidebar');
  function updateMainContent() {
    if (sidebar && sidebar.classList.contains('collapsed')) {
      mainContent.classList.add('expanded');
      mainContent.classList.remove('collapsed');
      mainContent.style.marginLeft = '0';
    } else {
      mainContent.classList.remove('expanded');
      mainContent.classList.add('collapsed');
      mainContent.style.marginLeft = '280px';
    }
  }
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      setTimeout(updateMainContent, 10);
    });
  }
  // Initial state
  updateMainContent();
});
</script>

    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Attendance Logs</h2>
            <p class="text-muted mb-0">
              Welcome back! Here are your attendance records.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance Logs</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Real-time Clock & Attendance Actions -->
    <div class="clock-container">
      <div id="current-date"></div>
      <div id="current-time"></div>
      <div class="attendance-actions mt-4">
        <button id="time-in-btn" class="btn btn-time-in btn-attendance">
          <i class="bi bi-alarm-fill me-2"></i>Time In
        </button>
        <button id="time-out-btn" class="btn btn-time-out btn-attendance" disabled>
          <i class="bi bi-alarm me-2"></i>Time Out
        </button>
      </div>
    </div>

    <!-- Attendance Stats -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-label">Today's Hours</div>
        <div class="stat-value" id="today-hours">{{ $stats['today_hours'] ?? '0h 0m' }}</div>
        <div class="stat-desc">Time worked today</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">This Week</div>
        <div class="stat-value" id="week-hours">{{ $stats['week_hours'] ?? '0h 0m' }}</div>
        <div class="stat-desc">Total hours this week</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Attendance Rate</div>
        <div class="stat-value" id="attendance-rate">{{ $stats['attendance_rate'] ?? '0%' }}</div>
        <div class="stat-desc">This month</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Late Arrivals</div>
        <div class="stat-value" id="late-count">{{ $stats['late_count'] ?? '0' }}</div>
        <div class="stat-desc">This month</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-container">
      <div class="row">
        <div class="col-md-3 mb-2">
          <label for="month-filter" class="form-label">Month</label>
          <select class="form-select" id="month-filter">
            <option value="">All Months</option>
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <label for="year-filter" class="form-label">Year</label>
          <select class="form-select" id="year-filter">
            <option value="">All Years</option>
            <option value="2021">2021</option>
            <option value="2022">2022</option>
            <option value="2023">2023</option>
            <option value="2024">2024</option>
            <option value="2025" selected>2025</option>
            <option value="2026">2026</option>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <label for="status-filter" class="form-label">Status</label>
          <select class="form-select" id="status-filter">
            <option value="">All Status</option>
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
            <option value="Late">Late</option>
            <option value="Early Departure">Early Departure</option>
            <option value="Overtime">Overtime</option>
          </select>
        </div>
        <div class="col-md-3 mb-2 d-flex align-items-end">
          <button id="reset-filters" class="btn btn-outline-secondary w-100">Reset Filters</button>
        </div>
      </div>
    </div>

    <!-- ✅ Attendance Logs Table -->
    <div class="simulation-card card mb-4">
      <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Daily Attendance Logs</h4>
        <div>
          <button id="export-btn" class="btn btn-sm btn-outline-primary me-2">
            <i class="bi bi-download me-1"></i> Export
          </button>
          <button id="print-btn" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Print
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="attendance-table">
            <thead class="table-light">
              <tr>
                <th class="fw-bold">ID</th>
                <th class="fw-bold">Date</th>
                <th class="fw-bold">Time In</th>
                <th class="fw-bold">Time Out</th>
                <th class="fw-bold">Hours Worked</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($attendance_logs as $log)
                <tr>
                  <td>{{ $log->id }}</td>
                  <td>{{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}</td>
                  <td>
                    @if($log->time_in)
                      @php
                        try {
                          $timeIn = \Carbon\Carbon::parse($log->time_in);
                          echo $timeIn->format('g:i A');
                        } catch (Exception $e) {
                          // Try to extract time from datetime string
                          if (strpos($log->time_in, ' ') !== false) {
                            $timePart = explode(' ', $log->time_in)[1];
                            $timeIn = \Carbon\Carbon::createFromFormat('H:i:s', $timePart);
                            echo $timeIn->format('g:i A');
                          } else {
                            echo $log->time_in;
                          }
                        }
                      @endphp
                    @else
                      --:--
                    @endif
                  </td>
                  <td>
                    @if($log->time_out)
                      @php
                        try {
                          $timeOut = \Carbon\Carbon::parse($log->time_out);
                          echo $timeOut->format('g:i A');
                        } catch (Exception $e) {
                          // Try to extract time from datetime string
                          if (strpos($log->time_out, ' ') !== false) {
                            $timePart = explode(' ', $log->time_out)[1];
                            $timeOut = \Carbon\Carbon::createFromFormat('H:i:s', $timePart);
                            echo $timeOut->format('g:i A');
                          } else {
                            echo $log->time_out;
                          }
                        }
                      @endphp
                    @else
                      --:--
                    @endif
                  </td>
                  <td>
                    @if($log->hours_worked)
                      @php
                        $hours = floor($log->hours_worked);
                        $minutes = round(($log->hours_worked - $hours) * 60);
                        echo "{$hours}h {$minutes}m";
                      @endphp
                    @else
                      0h 0m
                    @endif
                  </td>
                  <td>
                    <span class="badge badge-simulation status-{{ strtolower(str_replace(' ', '-', $log->status)) }}">
                      {{ $log->status }}
                    </span>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                              data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item" href="#" onclick="viewAttendanceDetails({{ $log->id }})">
                            <i class="bi bi-eye me-2"></i>View Details
                          </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <a class="dropdown-item" href="#" onclick="requestCorrection({{ $log->id }})">
                            <i class="bi bi-exclamation-triangle me-2"></i>Request Correction
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-info-circle me-2"></i>No attendance logs found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
          <nav aria-label="Attendance pagination">
            <ul class="pagination">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
              </li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Real-time clock functionality
    function updateClock() {
      const now = new Date();
      const timeEl = document.getElementById('current-time');
      const dateEl = document.getElementById('current-date');

      // Format time (12-hour format)
      let hours = now.getHours();
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const seconds = now.getSeconds().toString().padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12; // 0 should be 12
      const displayHours = hours.toString().padStart(2, '0');
      timeEl.textContent = `${displayHours}:${minutes}:${seconds} ${ampm}`;

      // Format date
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      dateEl.textContent = now.toLocaleDateString('en-US', options);
    }

    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);

    // Time In/Out functionality
    document.getElementById('time-in-btn').addEventListener('click', function() {
      // Use current time automatically
      const now = new Date();
      const timeString = now.toTimeString().slice(0,5); // Get HH:MM format

      // Show SweetAlert confirmation
      Swal.fire({
        title: 'Time In Confirmation',
        text: `Clock in at ${timeString}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4cc9f0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-alarm-fill me-2"></i>Time In',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Disable button and show loading
          const btn = this;
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';

          // Send AJAX request
          fetch('{{ route("employee.attendance.time_in") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ time_in: timeString })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update UI
              document.getElementById('time-out-btn').disabled = false;
              btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked In';

              // Show SweetAlert success
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
              });

              // Update today's hours display
              updateTodayHours();
            } else {
              // Re-enable button on error
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
              Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#dc3545'
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
            Swal.fire({
              title: 'Error!',
              text: 'Error recording time in. Please try again.',
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
          });
        }
      });
    });

    document.getElementById('time-out-btn').addEventListener('click', function() {
      // Use current time automatically
      const now = new Date();
      const timeString = now.toTimeString().slice(0,5); // Get HH:MM format

      // Show SweetAlert confirmation
      Swal.fire({
        title: 'Time Out Confirmation',
        text: `Clock out at ${timeString}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f72585',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-alarm me-2"></i>Time Out',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Disable button and show loading
          const btn = this;
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';

          // Send AJAX request
          fetch('{{ route("employee.attendance.time_out") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ time_out: timeString })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update UI
              btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked Out';

              // Show SweetAlert success
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
              });

              // Reload the page after 2 seconds to show updated attendance
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            } else {
              // Re-enable button on error
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-alarm me-2"></i>Time Out';
              Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#dc3545'
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-alarm me-2"></i>Time Out';
            Swal.fire({
              title: 'Error!',
              text: 'Error recording time out. Please try again.',
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
          });
        }
      });
    });

    // Filter functionality
    document.getElementById('month-filter').addEventListener('change', filterTable);
    document.getElementById('year-filter').addEventListener('change', filterTable);
    document.getElementById('status-filter').addEventListener('change', filterTable);

    document.getElementById('reset-filters').addEventListener('click', function() {
      document.getElementById('month-filter').value = '';
      document.getElementById('year-filter').value = '2025';
      document.getElementById('status-filter').value = '';
      filterTable();
    });

    function filterTable() {
      const monthFilter = document.getElementById('month-filter').value;
      const yearFilter = document.getElementById('year-filter').value;
      const statusFilter = document.getElementById('status-filter').value;

      const rows = document.querySelectorAll('#attendance-table tbody tr');

      rows.forEach(row => {
        // Skip the "No attendance logs found" row
        if (row.cells.length < 6) {
          return;
        }

        let showRow = true;
        const dateCell = row.cells[1] ? row.cells[1].textContent.trim() : '';
        const statusBadge = row.cells[5] ? row.cells[5].querySelector('.badge') : null;
        const statusCell = statusBadge ? statusBadge.textContent.trim() : '';

        // Apply month filter
        if (monthFilter && dateCell) {
          try {
            const date = new Date(dateCell);
            if (!isNaN(date.getTime()) && date.getMonth() + 1 != monthFilter) {
              showRow = false;
            }
          } catch (e) {
            // Skip invalid dates
          }
        }

        // Apply year filter
        if (yearFilter && dateCell) {
          try {
            const date = new Date(dateCell);
            if (!isNaN(date.getTime()) && date.getFullYear() != yearFilter) {
              showRow = false;
            }
          } catch (e) {
            // Skip invalid dates
          }
        }

        // Apply status filter
        if (statusFilter && statusCell && statusCell !== statusFilter) {
          showRow = false;
        }

        row.style.display = showRow ? '' : 'none';
      });

      // Show/hide "No records found" message based on visible rows
      updateNoRecordsMessage();
    }

    // Function to show/hide "No records found" message
    function updateNoRecordsMessage() {
      const rows = document.querySelectorAll('#attendance-table tbody tr');
      let visibleRows = 0;

      rows.forEach(row => {
        if (row.style.display !== 'none' && row.cells.length >= 6) {
          visibleRows++;
        }
      });

      // Check if we need to show a "No matching records" message
      const tbody = document.querySelector('#attendance-table tbody');
      let noMatchMessage = tbody.querySelector('.no-match-message');

      if (visibleRows === 0) {
        // Remove existing "No attendance logs found" row if it exists
        const existingNoDataRow = tbody.querySelector('tr td[colspan="7"]');
        if (existingNoDataRow) {
          existingNoDataRow.parentElement.style.display = 'none';
        }

        if (!noMatchMessage) {
          const noMatchRow = document.createElement('tr');
          noMatchRow.className = 'no-match-message';
          noMatchRow.innerHTML = `
            <td colspan="7" class="text-center text-muted py-4">
              <i class="bi bi-search me-2"></i>No matching attendance records found. Try adjusting your filters.
            </td>
          `;
          tbody.appendChild(noMatchRow);
        } else {
          noMatchMessage.style.display = '';
        }
      } else {
        if (noMatchMessage) {
          noMatchMessage.style.display = 'none';
        }
        // Show original "No attendance logs found" row if no data exists
        const existingNoDataRow = tbody.querySelector('tr td[colspan="7"]');
        if (existingNoDataRow && visibleRows === 0) {
          existingNoDataRow.parentElement.style.display = '';
        }
      }
    }

    // SweetAlert notification function
    function showNotification(message, type) {
      let icon = 'info';
      let color = '#4361ee';

      switch(type) {
        case 'success':
          icon = 'success';
          color = '#4cc9f0';
          break;
        case 'danger':
        case 'error':
          icon = 'error';
          color = '#dc3545';
          break;
        case 'warning':
          icon = 'warning';
          color = '#ffc107';
          break;
        case 'info':
        default:
          icon = 'info';
          color = '#4361ee';
          break;
      }

      Swal.fire({
        title: message,
        icon: icon,
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        background: '#fff',
        color: '#333',
        iconColor: color
      });
    }

    // Apply Early Departure styling
    function applyEarlyDepartureStyles() {
      const badges = document.querySelectorAll('.badge');
      badges.forEach(badge => {
        if (badge.textContent.trim() === 'Early Departure') {
          badge.style.backgroundColor = '#dc3545';
          badge.style.color = '#ffffff';
          badge.style.border = '2px solid #dc3545';
          badge.style.fontWeight = '700';
          badge.classList.add('status-early-departure');
        }
      });
    }

    // Check current attendance status from server
    function checkAttendanceStatus() {
      fetch('{{ route("employee.attendance.status") }}', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const timeInBtn = document.getElementById('time-in-btn');
          const timeOutBtn = document.getElementById('time-out-btn');

          if (data.has_timed_in && !data.has_timed_out) {
            // Already timed in, waiting for time out
            timeInBtn.disabled = true;
            timeInBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked In';
            timeOutBtn.disabled = false;
          } else if (data.has_timed_out) {
            // Already completed for the day
            timeInBtn.disabled = false;
            timeInBtn.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
            timeOutBtn.disabled = true;
            timeOutBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked Out';
          } else {
            // Ready to time in
            timeInBtn.disabled = false;
            timeInBtn.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
            timeOutBtn.disabled = true;
            timeOutBtn.innerHTML = '<i class="bi bi-alarm me-2"></i>Time Out';
          }
        }
      })
      .catch(error => {
        console.error('Error checking attendance status:', error);
      });
    }

    // Update today's hours in real-time
    function updateTodayHours() {
      // This function updates the today's hours display
      // It will be called after time in to show live hours
      setInterval(() => {
        fetch('{{ route("employee.attendance.status") }}', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.has_timed_in && !data.has_timed_out && data.time_in) {
            // Calculate current working hours
            const timeIn = new Date();
            const [hours, minutes, seconds] = data.time_in.split(':');
            timeIn.setHours(hours, minutes, seconds);

            const now = new Date();
            const diffMs = now - timeIn;
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

            document.getElementById('today-hours').textContent = `${diffHours}h ${diffMinutes}m`;
          }
        })
        .catch(error => {
          console.error('Error updating hours:', error);
        });
      }, 60000); // Update every minute
    }

    // Export functionality
    document.getElementById('export-btn').addEventListener('click', function() {
      exportToCSV();
    });

    // Print functionality
    document.getElementById('print-btn').addEventListener('click', function() {
      printAttendanceTable();
    });

    // Export to CSV function
    function exportToCSV() {
      const table = document.getElementById('attendance-table');
      const rows = table.querySelectorAll('tr');
      let csvContent = '';

      // Get current date for filename
      const now = new Date();
      const dateStr = now.toISOString().split('T')[0];

      // Add header with employee info and date
      csvContent += 'Attendance Time Logs Report\n';
      csvContent += 'Generated on: ' + now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }) + '\n\n';

      // Process each row
      rows.forEach((row, index) => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];

        cells.forEach((cell, cellIndex) => {
          // Skip the Actions column (last column)
          if (cellIndex < cells.length - 1) {
            let cellText = '';

            // Special handling for Status column (check if it contains a badge)
            if (cell.querySelector('.badge')) {
              cellText = cell.querySelector('.badge').textContent.trim();
            } else {
              cellText = cell.textContent.trim();
            }

            // Clean up any extra whitespace or special characters
            cellText = cellText.replace(/\s+/g, ' ').trim();

            // Escape commas and quotes in CSV
            if (cellText.includes(',') || cellText.includes('"') || cellText.includes('\n')) {
              cellText = '"' + cellText.replace(/"/g, '""') + '"';
            }

            rowData.push(cellText);
          }
        });

        // Only add non-empty rows
        if (rowData.length > 0 && rowData.some(cell => cell.trim() !== '')) {
          csvContent += rowData.join(',') + '\n';
        }
      });

      // Create and download the file
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');

      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `attendance_logs_${dateStr}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Show success notification
        showNotification('Attendance logs exported successfully!', 'success');
      } else {
        showNotification('Export not supported in this browser.', 'danger');
      }
    }

    // Print function
    function printAttendanceTable() {
      // Store original title
      const originalTitle = document.title;

      // Set print title
      document.title = 'Attendance Time Logs Report - ' + new Date().toLocaleDateString();

      // Create print content wrapper
      const printContent = document.createElement('div');
      printContent.className = 'print-content';
      printContent.innerHTML = `
        <div style="text-align: center; margin-bottom: 30px; page-break-inside: avoid;">
          <h1 style="margin: 0; font-size: 28px; font-weight: bold; color: #000;">Attendance Time Logs Report</h1>
          <p style="margin: 10px 0; font-size: 16px; color: #333;">
            Generated on: ${new Date().toLocaleDateString('en-US', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
            })}
          </p>
        </div>
      `;

      // Clone the table and add it to print content
      const table = document.getElementById('attendance-table').cloneNode(true);

      // Remove the Actions column from the cloned table
      const headerRow = table.querySelector('thead tr');
      const bodyRows = table.querySelectorAll('tbody tr');

      // Remove last header cell (Actions)
      if (headerRow && headerRow.cells.length > 0) {
        headerRow.deleteCell(-1);
      }

      // Remove last cell from each body row (Actions) and fix Status column
      bodyRows.forEach(row => {
        if (row.cells.length > 0) {
          // Fix Status column (second to last cell) - extract text from badge
          const statusCell = row.cells[row.cells.length - 2];
          if (statusCell && statusCell.querySelector('.badge')) {
            const statusText = statusCell.querySelector('.badge').textContent.trim();
            statusCell.innerHTML = statusText;
            statusCell.style.fontWeight = 'bold';
            statusCell.style.textAlign = 'center';
          }

          // Remove Actions column (last cell)
          row.deleteCell(-1);
        }
      });

      printContent.appendChild(table);

      // Insert print content into body
      document.body.appendChild(printContent);

      // Trigger print
      window.print();

      // Clean up after print
      setTimeout(() => {
        document.title = originalTitle;
        if (printContent.parentNode) {
          printContent.parentNode.removeChild(printContent);
        }
      }, 1000);

      // Show notification
      showNotification('Print dialog opened!', 'info');
    }

    // Attendance record actions
    function viewAttendanceDetails(logId) {
      // Create modal for viewing attendance details
      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'attendanceDetailsModal';
      modal.innerHTML = `
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-calendar-check me-2"></i>Attendance Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading attendance details...</p>
              </div>
            </div>
          </div>
        </div>
      `;

      document.body.appendChild(modal);
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();

      // Fetch attendance details
      fetch(`/employee/attendance/${logId}/details`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const modalBody = modal.querySelector('.modal-body');
          modalBody.innerHTML = `
            <div class="row">
              <div class="col-md-6">
                <div class="card border-0 bg-light">
                  <div class="card-body">
                    <h6 class="card-title text-primary">
                      <i class="bi bi-calendar3 me-2"></i>Date Information
                    </h6>
                    <p class="mb-1"><strong>Date:</strong> ${data.log.formatted_date}</p>
                    <p class="mb-1"><strong>Day:</strong> ${data.log.day_of_week}</p>
                    <p class="mb-0"><strong>Status:</strong>
                      <span class="badge bg-${data.log.status_color}">${data.log.status}</span>
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card border-0 bg-light">
                  <div class="card-body">
                    <h6 class="card-title text-success">
                      <i class="bi bi-clock me-2"></i>Time Information
                    </h6>
                    <p class="mb-1"><strong>Time In:</strong> ${data.log.time_in || 'Not recorded'}</p>
                    <p class="mb-1"><strong>Time Out:</strong> ${data.log.time_out || 'Not recorded'}</p>
                    <p class="mb-0"><strong>Hours Worked:</strong> ${data.log.hours_worked || '0h 0m'}</p>
                  </div>
                </div>
              </div>
            </div>
            ${data.log.remarks ? `
              <div class="mt-3">
                <div class="card border-0 bg-warning bg-opacity-10">
                  <div class="card-body">
                    <h6 class="card-title text-warning">
                      <i class="bi bi-chat-square-text me-2"></i>Remarks
                    </h6>
                    <p class="mb-0">${data.log.remarks}</p>
                  </div>
                </div>
              </div>
            ` : ''}
          `;
        } else {
          modal.querySelector('.modal-body').innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Error loading attendance details: ${data.message}
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        modal.querySelector('.modal-body').innerHTML = `
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Error loading attendance details. Please try again.
          </div>
        `;
      });

      // Clean up modal when closed
      modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
      });
    }


    function requestCorrection(logId) {
      // Create modal for requesting correction
      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'correctionRequestModal';
      modal.innerHTML = `
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-exclamation-triangle me-2"></i>Request Attendance Correction
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="correctionForm">
                <div class="mb-3">
                  <label for="correctionType" class="form-label">Correction Type</label>
                  <select class="form-select" id="correctionType" required>
                    <option value="">Select correction type</option>
                    <option value="time_in">Incorrect Time In</option>
                    <option value="time_out">Incorrect Time Out</option>
                    <option value="missing_time_in">Missing Time In</option>
                    <option value="missing_time_out">Missing Time Out</option>
                    <option value="wrong_status">Wrong Status</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="correctionReason" class="form-label">Reason for Correction</label>
                  <textarea class="form-control" id="correctionReason" rows="3"
                           placeholder="Please explain why this correction is needed..." required></textarea>
                </div>
                <div class="mb-3">
                  <label for="correctTime" class="form-label">Correct Time (if applicable)</label>
                  <input type="time" class="form-control" id="correctTime">
                  <div class="form-text">Leave blank if not applicable</div>
                </div>
                <div class="mb-3">
                  <label for="verifyPassword" class="form-label">Verify Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="verifyPassword"
                         placeholder="Enter your password to confirm" required>
                  <div class="form-text">Password verification is required to submit correction requests</div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-warning" onclick="submitCorrectionRequest(${logId})">
                <i class="bi bi-send me-2"></i>Submit Request
              </button>
            </div>
          </div>
        </div>
      `;

      document.body.appendChild(modal);
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();

      // Clean up modal when closed
      modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
      });
    }

    function submitCorrectionRequest(logId) {
      const form = document.getElementById('correctionForm');
      const correctionType = document.getElementById('correctionType').value;
      const correctionReason = document.getElementById('correctionReason').value;
      const correctTime = document.getElementById('correctTime').value;
      const verifyPassword = document.getElementById('verifyPassword').value;

      if (!correctionType || !correctionReason || !verifyPassword) {
        Swal.fire({
          title: 'Missing Information',
          text: 'Please fill in all required fields including password verification.',
          icon: 'warning',
          confirmButtonColor: '#ffc107'
        });
        return;
      }

      // Send correction request
      fetch('/employee/attendance/correction-request', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          log_id: logId,
          correction_type: correctionType,
          reason: correctionReason,
          correct_time: correctTime,
          password: verifyPassword
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: 'Success!',
            text: 'Correction request submitted successfully. HR will review your request.',
            icon: 'success',
            confirmButtonColor: '#4cc9f0'
          });
          bootstrap.Modal.getInstance(document.getElementById('correctionRequestModal')).hide();
        } else {
          // Handle password verification errors specifically
          if (data.message && data.message.includes('password')) {
            Swal.fire({
              title: 'Password Verification Failed',
              text: data.message || 'Invalid password. Please try again.',
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: data.message || 'Error submitting correction request.',
              icon: 'error',
              confirmButtonColor: '#dc3545'
            });
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          title: 'Error!',
          text: 'Error submitting correction request. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545'
        });
      });
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      checkAttendanceStatus();
      applyEarlyDepartureStyles();

      // Start real-time updates if user has timed in
      fetch('{{ route("employee.attendance.status") }}', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.has_timed_in && !data.has_timed_out) {
          updateTodayHours();
        }
      })
      .catch(error => {
        console.error('Error initializing:', error);
      });
    });
  </script>
</body>
</html>
