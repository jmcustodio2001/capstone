<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Dark Mode CSS -->
<style>
:root {
  /* Light Mode Colors */
  --bg-primary: #ffffff;
  --bg-secondary: #f8f9fa;
  --bg-tertiary: #e9ecef;
  --text-primary: #212529;
  --text-secondary: #6c757d;
  --text-muted: #868e96;
  --border-color: #dee2e6;
  --card-bg: #ffffff;
  --navbar-bg: var(--jetlouge-primary, #2c3e50);
  --dropdown-bg: #ffffff;
  --dropdown-border: rgba(0,0,0,.15);
  --notification-bg: #ffffff;
  --shadow: rgba(0,0,0,.15);
}

[data-theme="dark"] {
  /* Dark Mode Colors */
  --bg-primary: #1a1a1a;
  --bg-secondary: #2d2d2d;
  --bg-tertiary: #404040;
  --text-primary: #ffffff;
  --text-secondary: #b0b0b0;
  --text-muted: #888888;
  --border-color: #404040;
  --card-bg: #2d2d2d;
  --navbar-bg: #1a1a1a;
  --dropdown-bg: #2d2d2d;
  --dropdown-border: rgba(255,255,255,.15);
  --notification-bg: #2d2d2d;
  --shadow: rgba(0,0,0,.5);
}

/* Apply dark mode styles */
body {
  background-color: var(--bg-primary);
  color: var(--text-primary);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.navbar {
  background-color: var(--navbar-bg) !important;
}

.dropdown-menu {
  background-color: var(--dropdown-bg);
  border: 1px solid var(--dropdown-border);
  box-shadow: 0 0.5rem 1rem var(--shadow);
}

.dropdown-item {
  color: var(--text-primary);
}

.dropdown-item:hover,
.dropdown-item:focus {
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
}

.dropdown-header {
  color: var(--text-secondary);
}

.dropdown-divider {
  border-top: 1px solid var(--border-color);
}

.notification-item {
  background-color: var(--notification-bg);
  border-color: var(--border-color);
}

.notification-item:hover {
  background-color: var(--bg-tertiary);
}

.card {
  background-color: var(--card-bg);
  border-color: var(--border-color);
}

.table {
  --bs-table-bg: var(--card-bg);
  --bs-table-color: var(--text-primary);
}

.form-control,
.form-select {
  background-color: var(--bg-secondary);
  border-color: var(--border-color);
  color: var(--text-primary);
}

.form-control:focus,
.form-select:focus {
  background-color: var(--bg-secondary);
  border-color: var(--jetlouge-primary, #2c3e50);
  color: var(--text-primary);
  box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
}

.btn-outline-light {
  color: var(--text-primary);
  border-color: rgba(255,255,255,0.3);
}

.btn-outline-light:hover {
  background-color: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.5);
}

/* Dark mode toggle animation */
.dark-mode-toggle {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 30px;
}

.dark-mode-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.dark-mode-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 30px;
}

.dark-mode-slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .dark-mode-slider {
  background-color: #2196F3;
}

input:checked + .dark-mode-slider:before {
  transform: translateX(30px);
}

/* SweetAlert2 dark mode */
[data-theme="dark"] .swal2-popup {
  background-color: var(--card-bg);
  color: var(--text-primary);
}

[data-theme="dark"] .swal2-title {
  color: var(--text-primary);
}

[data-theme="dark"] .swal2-content {
  color: var(--text-secondary);
}

[data-theme="dark"] .swal2-input,
[data-theme="dark"] .swal2-select {
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  border-color: var(--border-color);
}
</style>

<!-- Responsive Employee Topbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--jetlouge-primary); z-index: 1055;" aria-label="Main Navigation">
  <div class="container-fluid">
    <button class="sidebar-toggle desktop-toggle me-3 d-none d-lg-block" id="desktop-toggle" title="Toggle Sidebar">
      <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand fw-bold" href="{{ route('employee.dashboard') }}">
      <i class="bi bi-person-badge me-2 d-none d-sm-inline"></i>
      <span class="d-none d-md-inline">Jetlouge Employee Portal</span>
      <span class="d-inline d-md-none">Employee</span>
    </a>
    <div class="d-flex align-items-center">
      <!-- Quick Navigation -->
      <button class="btn btn-outline-light btn-sm me-2 d-none d-lg-block" onclick="showQuickNavigation()" title="Quick Navigation">
        <i class="bi bi-grid-3x3-gap"></i>
      </button>
      
      <!-- Quick Dark Mode Toggle -->
      <button class="btn btn-outline-light btn-sm me-2 d-none d-md-block" onclick="quickToggleDarkMode()" title="Toggle Dark Mode" id="quickDarkModeBtn">
        <i class="bi bi-moon" id="darkModeIcon"></i>
      </button>
      
      <!-- Notifications Dropdown -->
      <div class="dropdown me-2 d-none d-sm-block">
        <button class="btn btn-outline-light btn-sm dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown" id="notificationBtn">
          <i class="bi bi-bell"></i>
          <span class="badge bg-danger ms-1 d-none d-md-inline" id="notificationCount">4</span>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-md-none" id="notificationBadge">
            4
            <span class="visually-hidden">unread notifications</span>
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 320px;">
          <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="markAllAsRead()" title="Mark all as read">
              <i class="bi bi-check2-all"></i>
            </button>
          </h6></li>
          <li>
            <a class="dropdown-item notification-item" href="#" onclick="showPayslipNotification(event)" data-notification-id="1">
              <div class="d-flex align-items-start">
                <div class="notification-icon bg-success text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                  <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">New Payslip Available</div>
                  <div class="text-muted small">Your payslip for December 2024 is ready</div>
                  <div class="text-muted small">2 hours ago</div>
                </div>
                <span class="badge bg-primary rounded-pill">New</span>
              </div>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item notification-item" href="#" onclick="showLeaveRequestNotification(event)" data-notification-id="2">
              <div class="d-flex align-items-start">
                <div class="notification-icon bg-warning text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                  <i class="bi bi-calendar-event"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">Upcoming Leave Request</div>
                  <div class="text-muted small">Your leave starts in 3 days</div>
                  <div class="text-muted small">1 day ago</div>
                </div>
                <span class="badge bg-primary rounded-pill">New</span>
              </div>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item notification-item" href="#" onclick="showAttendanceNotification(event)" data-notification-id="3">
              <div class="d-flex align-items-start">
                <div class="notification-icon bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                  <i class="bi bi-check-circle"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">Attendance Approved</div>
                  <div class="text-muted small">Your overtime request has been approved</div>
                  <div class="text-muted small">2 days ago</div>
                </div>
                <span class="badge bg-primary rounded-pill">New</span>
              </div>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item notification-item" href="#" onclick="showPolicyUpdateNotification(event)" data-notification-id="4">
              <div class="d-flex align-items-start">
                <div class="notification-icon bg-danger text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                  <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">Policy Update Notice</div>
                  <div class="text-muted small">New remote work policy effective Jan 1, 2025</div>
                  <div class="text-muted small">3 days ago</div>
                </div>
                <span class="badge bg-primary rounded-pill">New</span>
              </div>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-center" href="#" onclick="showAllNotifications(event)">
              <i class="bi bi-bell me-2"></i> View All Notifications
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="#" onclick="showSettings(event)">
              <i class="bi bi-gear me-2"></i> Settings
            </a>
          </li>
        </ul>
      </div>

      <!-- User Profile & Logout -->
      <div class="dropdown ms-2 d-none d-sm-block">
        <button class="btn btn-outline-light btn-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
          @php
            $user = auth('employee')->user();
            $profilePicture = $user && !empty($user->profile_picture) && Storage::disk('public')->exists($user->profile_picture)
              ? Storage::url($user->profile_picture)
              : asset('images/default-avatar.png');
          @endphp
          <img src="{{ $profilePicture }}"
               alt="{{ $user ? ($user->first_name . ' ' . $user->last_name) : 'Profile' }}"
               class="rounded-circle"
               style="width: 24px; height: 24px; object-fit: cover; border: 1px solid rgba(255,255,255,0.2);">
          <div class="d-flex flex-column align-items-start d-none d-lg-block">
            <span class="small" style="line-height: 1;">{{ $user ? ($user->first_name . ' ' . $user->last_name) : 'User' }}</span>
            <span class="small text-light-50" style="line-height: 1;">{{ $user ? $user->employee_id : 'Employee' }}</span>
          </div>
          <i class="bi bi-chevron-down d-none d-lg-inline"></i>
          <i class="bi bi-person d-inline d-lg-none"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg">
          <li><h6 class="dropdown-header">{{ $user ? ($user->first_name . ' ' . $user->last_name) : 'User Profile' }}</h6></li>
          <li>
            <a class="dropdown-item" href="#" onclick="showProfile(event)">
              <i class="bi bi-person-circle me-2"></i> View Profile
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#" onclick="showSettings(event)">
              <i class="bi bi-gear me-2"></i> Account Settings
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#" onclick="showHelp(event)">
              <i class="bi bi-question-circle me-2"></i> Help & Support
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="#" onclick="confirmLogout(event)">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
          </li>
        </ul>
      </div>

      <!-- Mobile Menu Button -->
      <button class="sidebar-toggle mobile-toggle d-lg-none" id="menu-btn" title="Open Menu">
        <i class="bi bi-list fs-5"></i>
      </button>
    </div>
  </div>
</nav>

<!-- Hidden Logout Form -->
<form method="POST" action="{{ route('employee.logout') }}" id="logoutForm" style="display: none;">
  @csrf
</form>
<script>
// SweetAlert Notification Functions
function showPayslipNotification(event) {
  event.preventDefault();
  markNotificationAsRead(1);
  Swal.fire({
    title: '💰 New Payslip Available',
    html: `
      <div class="text-start">
        <p><strong>Period:</strong> December 2024</p>
        <p><strong>Status:</strong> <span class="badge bg-success">Ready for Download</span></p>
        <p><strong>Generated:</strong> 2 hours ago</p>
        <hr>
        <p class="text-muted">Your payslip has been processed and is ready for download. Click below to view or download your payslip.</p>
      </div>
    `,
    icon: 'success',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-download"></i> View Payslips',
    cancelButtonText: 'Close',
    confirmButtonColor: '#28a745',
    width: '500px'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '/employee/payslips';
    }
  });
}

function showLeaveRequestNotification(event) {
  event.preventDefault();
  markNotificationAsRead(2);
  Swal.fire({
    title: '📅 Upcoming Leave Request',
    html: `
      <div class="text-start">
        <p><strong>Leave Type:</strong> Annual Leave</p>
        <p><strong>Start Date:</strong> January 15, 2025</p>
        <p><strong>Duration:</strong> 5 days</p>
        <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
        <hr>
        <p class="text-muted">Your leave request starts in 3 days. Please ensure all pending tasks are completed before your leave.</p>
      </div>
    `,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-calendar-check"></i> View Leave Details',
    cancelButtonText: 'Close',
    confirmButtonColor: '#17a2b8',
    width: '500px'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '/employee/leave-applications';
    }
  });
}

function showAttendanceNotification(event) {
  event.preventDefault();
  markNotificationAsRead(3);
  Swal.fire({
    title: '✅ Attendance Approved',
    html: `
      <div class="text-start">
        <p><strong>Request Type:</strong> Overtime Request</p>
        <p><strong>Date:</strong> December 27, 2024</p>
        <p><strong>Hours:</strong> 3 hours overtime</p>
        <p><strong>Approved By:</strong> HR Manager</p>
        <hr>
        <p class="text-muted">Your overtime request has been approved and will be reflected in your next payslip.</p>
      </div>
    `,
    icon: 'success',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-clock-history"></i> View Attendance',
    cancelButtonText: 'Close',
    confirmButtonColor: '#28a745',
    width: '500px'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '/employee/attendance-logs';
    }
  });
}

function showPolicyUpdateNotification(event) {
  event.preventDefault();
  markNotificationAsRead(4);
  Swal.fire({
    title: '📋 Policy Update Notice',
    html: `
      <div class="text-start">
        <p><strong>Policy:</strong> Remote Work Policy</p>
        <p><strong>Effective Date:</strong> January 1, 2025</p>
        <p><strong>Priority:</strong> <span class="badge bg-warning">Important</span></p>
        <hr>
        <div class="alert alert-info">
          <strong>Key Changes:</strong>
          <ul class="mb-0">
            <li>New hybrid work schedule options</li>
            <li>Updated equipment allowance</li>
            <li>Revised communication protocols</li>
          </ul>
        </div>
        <p class="text-muted">Please review the updated policy document in the employee handbook.</p>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-file-text"></i> Read Policy',
    cancelButtonText: 'Close',
    confirmButtonColor: '#ffc107',
    width: '600px'
  }).then((result) => {
    if (result.isConfirmed) {
      // For now, show a message that policies section is coming soon
      Swal.fire({
        title: 'Policy Section',
        text: 'The employee policy section is coming soon. Please contact HR for policy documents.',
        icon: 'info',
        confirmButtonText: 'OK'
      });
    }
  });
}

function showAllNotifications(event) {
  event.preventDefault();
  Swal.fire({
    title: '🔔 All Notifications',
    html: `
      <div class="text-start">
        <div class="list-group">
          <div class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">New Payslip Available</div>
              <small class="text-muted">2 hours ago</small>
            </div>
            <span class="badge bg-success rounded-pill">New</span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">Upcoming Leave Request</div>
              <small class="text-muted">1 day ago</small>
            </div>
            <span class="badge bg-warning rounded-pill">Reminder</span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">Attendance Approved</div>
              <small class="text-muted">2 days ago</small>
            </div>
            <span class="badge bg-info rounded-pill">Approved</span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">Policy Update Notice</div>
              <small class="text-muted">3 days ago</small>
            </div>
            <span class="badge bg-danger rounded-pill">Important</span>
          </div>
        </div>
      </div>
    `,
    icon: 'info',
    confirmButtonText: 'Close',
    confirmButtonColor: '#6c757d',
    width: '600px'
  });
}

function showSettings(event) {
  event.preventDefault();
  
  // Get current settings
  const currentTheme = localStorage.getItem('theme') || 'light';
  const emailNotifications = localStorage.getItem('emailNotifications') !== 'false';
  const pushNotifications = localStorage.getItem('pushNotifications') !== 'false';
  const currentLanguage = localStorage.getItem('language') || 'en';
  
  Swal.fire({
    title: '⚙️ Settings',
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-12">
            <h6><i class="bi bi-bell me-2"></i>Notification Preferences</h6>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="emailNotifications" ${emailNotifications ? 'checked' : ''}>
              <label class="form-check-label" for="emailNotifications">
                <i class="bi bi-envelope me-1"></i>Email Notifications
              </label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="pushNotifications" ${pushNotifications ? 'checked' : ''}>
              <label class="form-check-label" for="pushNotifications">
                <i class="bi bi-app-indicator me-1"></i>Push Notifications
              </label>
            </div>
          </div>
          <div class="col-12">
            <h6><i class="bi bi-palette me-2"></i>Display Settings</h6>
            <div class="d-flex align-items-center justify-content-between">
              <label class="form-label mb-0">
                <i class="bi bi-moon me-1"></i>Dark Mode
              </label>
              <div class="dark-mode-toggle">
                <input type="checkbox" id="darkModeToggle" ${currentTheme === 'dark' ? 'checked' : ''}>
                <span class="dark-mode-slider"></span>
              </div>
            </div>
            <small class="text-muted">Switch between light and dark themes</small>
          </div>
          <div class="col-12">
            <h6><i class="bi bi-translate me-2"></i>Language</h6>
            <select class="form-select" id="language">
              <option value="en" ${currentLanguage === 'en' ? 'selected' : ''}>🇺🇸 English</option>
              <option value="fil" ${currentLanguage === 'fil' ? 'selected' : ''}>🇵🇭 Filipino</option>
            </select>
          </div>
          <div class="col-12">
            <h6><i class="bi bi-speedometer2 me-2"></i>Performance</h6>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="animations" checked>
              <label class="form-check-label" for="animations">
                <i class="bi bi-magic me-1"></i>Enable Animations
              </label>
            </div>
          </div>
        </div>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-check-lg"></i> Save Settings',
    cancelButtonText: '<i class="bi bi-x-lg"></i> Cancel',
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    width: '550px',
    didOpen: () => {
      // Add real-time dark mode toggle
      const darkModeToggle = document.getElementById('darkModeToggle');
      if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
          toggleDarkMode(this.checked);
        });
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Save all settings
      const emailNotifs = document.getElementById('emailNotifications').checked;
      const pushNotifs = document.getElementById('pushNotifications').checked;
      const selectedLanguage = document.getElementById('language').value;
      const animationsEnabled = document.getElementById('animations').checked;
      
      // Save to localStorage
      localStorage.setItem('emailNotifications', emailNotifs);
      localStorage.setItem('pushNotifications', pushNotifs);
      localStorage.setItem('language', selectedLanguage);
      localStorage.setItem('animations', animationsEnabled);
      
      // Show success message
      Swal.fire({
        title: '✅ Settings Saved!',
        text: 'Your preferences have been updated successfully.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
      
      // Apply language change if needed
      if (selectedLanguage !== currentLanguage) {
        setTimeout(() => {
          Swal.fire({
            title: 'Language Changed',
            text: 'Please refresh the page to apply language changes.',
            icon: 'info',
            confirmButtonText: 'Refresh Now',
            showCancelButton: true,
            cancelButtonText: 'Later'
          }).then((refreshResult) => {
            if (refreshResult.isConfirmed) {
              window.location.reload();
            }
          });
        }, 2100);
      }
    }
  });
}

function showProfile(event) {
  event.preventDefault();
  
  @php
    // Helper function to get department name
    $departmentNames = [
      '1' => 'Human Resources',
      '2' => 'Information Technology', 
      '3' => 'Finance',
      '4' => 'Marketing',
      '5' => 'Operations',
      '6' => 'Customer Service'
    ];
    $departmentName = isset($user->department_id) && isset($departmentNames[$user->department_id]) 
      ? $departmentNames[$user->department_id] 
      : 'Not Assigned';
  @endphp
  
  Swal.fire({
    title: '👤 Employee Profile',
    html: `
      <div class="text-start">
        <div class="text-center mb-3">
          <img src="{{ $profilePicture }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
          <h5 class="mt-2">{{ $user ? ($user->first_name . ' ' . $user->last_name) : 'User' }}</h5>
          <p class="text-muted">{{ $user ? $user->employee_id : 'Employee ID' }}</p>
        </div>
        <hr>
        <div class="row g-2">
          <div class="col-6"><strong>Department:</strong></div>
          <div class="col-6">{{ $departmentName }}</div>
          <div class="col-6"><strong>Position:</strong></div>
          <div class="col-6">{{ $user->position ?? 'Not Assigned' }}</div>
          <div class="col-6"><strong>Email:</strong></div>
          <div class="col-6">{{ $user->email ?? 'N/A' }}</div>
          <div class="col-6"><strong>Phone:</strong></div>
          <div class="col-6">{{ $user->phone_number ?? 'Not Provided' }}</div>
        </div>
      </div>
    `,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-pencil"></i> Edit Profile',
    cancelButtonText: 'Close',
    confirmButtonColor: '#007bff',
    width: '500px'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '/employee/settings';
    }
  });
}

function showHelp(event) {
  event.preventDefault();
  Swal.fire({
    title: '❓ Help & Support',
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-md-6">
            <h6><i class="bi bi-question-circle me-2"></i>Quick Help</h6>
            <div class="list-group list-group-flush">
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/leave-applications')">
                <i class="bi bi-calendar-event me-2"></i>Apply for Leave
              </button>
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/payslips')">
                <i class="bi bi-file-earmark-text me-2"></i>View Payslips
              </button>
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/attendance-logs')">
                <i class="bi bi-clock-history me-2"></i>Attendance Logs
              </button>
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/my-trainings')">
                <i class="bi bi-book me-2"></i>My Trainings
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <h6><i class="bi bi-link-45deg me-2"></i>Quick Links</h6>
            <div class="list-group list-group-flush">
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/competency-profile')">
                <i class="bi bi-person-badge me-2"></i>Competency Profile
              </button>
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/claim-reimbursements')">
                <i class="bi bi-receipt me-2"></i>Claim Reimbursements
              </button>
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/requests')">
                <i class="bi bi-file-earmark-plus me-2"></i>Request Forms
              </button>
              <button class="list-group-item list-group-item-action" onclick="navigateToSection('/employee/settings')">
                <i class="bi bi-gear me-2"></i>Settings
              </button>
            </div>
          </div>
        </div>
        <hr>
        <div class="text-center">
          <p><strong>Need more help?</strong></p>
          <p>Contact HR: <a href="mailto:hr@company.com">hr@company.com</a></p>
          <p>Phone: +63 985 982 6398 </p>
        </div>
      </div>
    `,
    icon: 'question',
    confirmButtonText: 'Close',
    confirmButtonColor: '#6c757d',
    width: '600px'
  });
}

function confirmLogout(event) {
  event.preventDefault();
  Swal.fire({
    title: '🚪 Confirm Logout',
    text: 'Are you sure you want to logout from your account?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-box-arrow-right"></i> Yes, Logout',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Logging out...',
        text: 'Please wait while we log you out safely.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
          setTimeout(() => {
            document.getElementById('logoutForm').submit();
          }, 1000);
        }
      });
    }
  });
}

function markNotificationAsRead(notificationId) {
  const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
  if (notificationItem) {
    const badge = notificationItem.querySelector('.badge');
    if (badge && badge.textContent === 'New') {
      badge.textContent = 'Read';
      badge.className = 'badge bg-secondary rounded-pill';
      updateNotificationCount();
    }
  }
}

function markAllAsRead() {
  const newBadges = document.querySelectorAll('.notification-item .badge.bg-primary');
  newBadges.forEach(badge => {
    badge.textContent = 'Read';
    badge.className = 'badge bg-secondary rounded-pill';
  });
  updateNotificationCount();
  Swal.fire({
    title: 'All notifications marked as read!',
    icon: 'success',
    timer: 2000,
    showConfirmButton: false
  });
}

function updateNotificationCount() {
  const newBadges = document.querySelectorAll('.notification-item .badge.bg-primary');
  const count = newBadges.length;
  const countElement = document.getElementById('notificationCount');
  const badgeElement = document.getElementById('notificationBadge');
  
  if (countElement) countElement.textContent = count;
  if (badgeElement) badgeElement.textContent = count;
  
  if (count === 0) {
    if (countElement) countElement.style.display = 'none';
    if (badgeElement) badgeElement.style.display = 'none';
  }
}

// Dark Mode Functions
function toggleDarkMode(isDark) {
  const theme = isDark ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  
  // Update navbar background
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    if (isDark) {
      navbar.style.backgroundColor = '#1a1a1a';
    } else {
      navbar.style.backgroundColor = 'var(--jetlouge-primary, #2c3e50)';
    }
  }
  
  // Update quick toggle button icon
  const darkModeIcon = document.getElementById('darkModeIcon');
  const quickDarkModeBtn = document.getElementById('quickDarkModeBtn');
  if (darkModeIcon) {
    darkModeIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon';
  }
  if (quickDarkModeBtn) {
    quickDarkModeBtn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
  }
  
  // Show feedback
  const icon = isDark ? '🌙' : '☀️';
  const message = isDark ? 'Dark mode enabled' : 'Light mode enabled';
  
  // Only show toast if not in settings modal
  if (!document.querySelector('.swal2-container')) {
    Swal.fire({
      title: `${icon} ${message}`,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    });
  }
}

function initializeDarkMode() {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = savedTheme || (prefersDark ? 'dark' : 'light');
  const isDark = theme === 'dark';
  
  if (isDark) {
    document.documentElement.setAttribute('data-theme', 'dark');
    const navbar = document.querySelector('.navbar');
    if (navbar) {
      navbar.style.backgroundColor = '#1a1a1a';
    }
  }
  
  // Update quick toggle button icon on initialization
  const darkModeIcon = document.getElementById('darkModeIcon');
  const quickDarkModeBtn = document.getElementById('quickDarkModeBtn');
  if (darkModeIcon) {
    darkModeIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon';
  }
  if (quickDarkModeBtn) {
    quickDarkModeBtn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
  }
  
  // Listen for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
      toggleDarkMode(e.matches);
    }
  });
}

// Quick Dark Mode Toggle (can be called from anywhere)
function quickToggleDarkMode() {
  const currentTheme = localStorage.getItem('theme') || 'light';
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  toggleDarkMode(newTheme === 'dark');
}

// Navigation helper function
function navigateToSection(url) {
  Swal.close(); // Close any open SweetAlert
  window.location.href = url;
}

// Quick Navigation function
function showQuickNavigation() {
  Swal.fire({
    title: '🚀 Quick Navigation',
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-md-6">
            <h6><i class="bi bi-speedometer2 me-2"></i>Dashboard & Profile</h6>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-primary btn-sm" onclick="navigateToSection('/employee/dashboard')">
                <i class="bi bi-house me-2"></i>Dashboard
              </button>
              <button class="btn btn-outline-primary btn-sm" onclick="navigateToSection('/employee/competency-profile')">
                <i class="bi bi-person-badge me-2"></i>Competency Profile
              </button>
              <button class="btn btn-outline-primary btn-sm" onclick="navigateToSection('/employee/settings')">
                <i class="bi bi-gear me-2"></i>Settings
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <h6><i class="bi bi-calendar-event me-2"></i>Leave & Attendance</h6>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-success btn-sm" onclick="navigateToSection('/employee/leave-applications')">
                <i class="bi bi-calendar-plus me-2"></i>Apply Leave
              </button>
              <button class="btn btn-outline-success btn-sm" onclick="navigateToSection('/employee/attendance-logs')">
                <i class="bi bi-clock-history me-2"></i>Attendance
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <h6><i class="bi bi-book me-2"></i>Training & Learning</h6>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-info btn-sm" onclick="navigateToSection('/employee/my-trainings')">
                <i class="bi bi-book me-2"></i>My Trainings
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <h6><i class="bi bi-file-earmark me-2"></i>Forms & Payroll</h6>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-warning btn-sm" onclick="navigateToSection('/employee/payslips')">
                <i class="bi bi-file-earmark-text me-2"></i>Payslips
              </button>
              <button class="btn btn-outline-warning btn-sm" onclick="navigateToSection('/employee/requests')">
                <i class="bi bi-file-earmark-plus me-2"></i>Request Forms
              </button>
              <button class="btn btn-outline-warning btn-sm" onclick="navigateToSection('/employee/claim-reimbursements')">
                <i class="bi bi-receipt me-2"></i>Claims
              </button>
            </div>
          </div>
        </div>
      </div>
    `,
    icon: 'question',
    confirmButtonText: '<i class="bi bi-x-lg"></i> Close',
    confirmButtonColor: '#6c757d',
    width: '700px'
  });
}

// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
  // Initialize dark mode on page load
  initializeDarkMode();
  
  const menuBtn = document.getElementById('menu-btn');
  const desktopToggle = document.getElementById('desktop-toggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  const mainContent = document.getElementById('main-content');

  // Mobile sidebar toggle
  if (menuBtn && sidebar && overlay) {
    menuBtn.addEventListener('click', (e) => {
      e.preventDefault();
      sidebar.classList.toggle('active');
      overlay.classList.toggle('show');
      document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    });
  }

  // Desktop sidebar toggle
  if (desktopToggle && sidebar && mainContent) {
    desktopToggle.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const isCollapsed = sidebar.classList.contains('collapsed');
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
      localStorage.setItem('sidebarCollapsed', !isCollapsed);
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 300);
    });
  }

  // Restore sidebar state from localStorage
  const savedState = localStorage.getItem('sidebarCollapsed');
  if (savedState === 'true' && sidebar && mainContent) {
    sidebar.classList.add('collapsed');
    mainContent.classList.add('expanded');
  }

  // Close mobile sidebar when clicking overlay
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('active');
      overlay.classList.remove('show');
      document.body.style.overflow = '';
    });
  }
});
</script>
