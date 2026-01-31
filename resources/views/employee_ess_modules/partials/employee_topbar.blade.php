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
  color: white !important;
  border-color: rgba(255,255,255,0.3);
}

.btn-outline-light:hover {
  color: white !important;
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
            
            // Fallback for session-based external users
            if (!$user && session()->has('external_employee_data')) {
                $data = session('external_employee_data');
                $user = new \App\Models\Employee();
                $user->forceFill($data);
            }

            $profilePicture = asset('images/default-avatar.png');
            $firstName = $user->first_name ?? 'User';
            $lastName = $user->last_name ?? '';
            $fullName = trim($firstName . ' ' . $lastName);
            
            if ($user && !empty($user->profile_picture)) {
                $pic = $user->profile_picture;
                if (strpos($pic, 'http') === 0) {
                    $profilePicture = $pic;
                } elseif (Storage::disk('public')->exists($pic)) {
                    $profilePicture = Storage::url($pic);
                }
            }
            
            // Generate consistent fallback color
            $colors = ['FF9A56', 'FF6B9D', '4ECDC4', '45B7D1', 'FFA726', 'AB47BC', 'EF5350', '66BB6A', 'FFCA28', '26A69A'];
            $colorIndex = abs(crc32($user->employee_id ?? 'default')) % count($colors);
            $bgColor = $colors[$colorIndex];
            $fallbackUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . "&size=200&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
          @endphp
          <img src="{{ $profilePicture }}"
               alt="{{ $fullName }}"
               class="rounded-circle"
               style="width: 24px; height: 24px; object-fit: cover; border: 1px solid rgba(255,255,255,0.2);"
               onerror="this.onerror=null; this.src='{{ $fallbackUrl }}'">
          <div class="d-flex flex-column align-items-start d-none d-lg-block">
            <span class="small" style="line-height: 1;">{{ $user ? ($user->first_name . ' ' . $user->last_name) : 'User' }}</span>
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

async function showSettings(event) {
  event.preventDefault();
  
  // Load settings from backend first, fallback to localStorage
  let settings = {
    theme: localStorage.getItem('theme') || 'light',
    emailNotifications: localStorage.getItem('emailNotifications') !== 'false',
    pushNotifications: localStorage.getItem('pushNotifications') !== 'false',
    language: localStorage.getItem('language') || 'en',
    animations: localStorage.getItem('animations') !== 'false'
  };

  // Try to load from backend
  try {
    const response = await fetch('/employee/settings/get', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      if (data.success && data.settings) {
        settings = {
          theme: data.settings.dark_mode ? 'dark' : 'light',
          emailNotifications: data.settings.email_notifications,
          pushNotifications: data.settings.push_notifications,
          language: data.settings.language,
          animations: data.settings.animations_enabled
        };
        
        // Update localStorage with backend data
        localStorage.setItem('theme', settings.theme);
        localStorage.setItem('emailNotifications', settings.emailNotifications);
        localStorage.setItem('pushNotifications', settings.pushNotifications);
        localStorage.setItem('language', settings.language);
        localStorage.setItem('animations', settings.animations);
      }
    }
  } catch (error) {
    console.warn('Failed to load settings from backend, using localStorage:', error);
  }

  const currentTheme = settings.theme;
  const emailNotifications = settings.emailNotifications;
  const pushNotifications = settings.pushNotifications;
  const currentLanguage = settings.language;
  const animationsEnabled = settings.animations;
  
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
              <option value="fil" ${currentLanguage === 'fil' ? 'selected' : ''}>🇵🇭 Filipino (Tagalog)</option>
            </select>
          </div>
          <div class="col-12">
            <h6><i class="bi bi-speedometer2 me-2"></i>Performance</h6>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="animations" ${animationsEnabled ? 'checked' : ''}>
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
      const darkModeEnabled = document.getElementById('darkModeToggle').checked;
      
      // Save to localStorage
      localStorage.setItem('emailNotifications', emailNotifs);
      localStorage.setItem('pushNotifications', pushNotifs);
      localStorage.setItem('language', selectedLanguage);
      localStorage.setItem('animations', animationsEnabled);
      localStorage.setItem('theme', darkModeEnabled ? 'dark' : 'light');
      
      // Save to backend (optional - for persistent storage across devices)
      saveSettingsToBackend({
        email_notifications: emailNotifs,
        push_notifications: pushNotifs,
        language: selectedLanguage,
        animations_enabled: animationsEnabled,
        dark_mode: darkModeEnabled
      });
      
      // Apply animations setting immediately
      applyAnimationsSetting(animationsEnabled);
      
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
        // Apply translations immediately with retry
        translateWithRetry();
        
        // Restart translation observer for new language
        setTimeout(() => {
          startTranslationObserver();
        }, 1000);
        
        setTimeout(() => {
          Swal.fire({
            title: 'Language Changed',
            text: `Language changed to ${getLanguageName(selectedLanguage)}. The interface has been translated automatically!`,
            icon: 'success',
            confirmButtonText: 'Great!',
            showCancelButton: true,
            cancelButtonText: 'Refresh Page'
          }).then((refreshResult) => {
            if (refreshResult.isDismissed || refreshResult.dismiss === 'cancel') {
              window.location.reload();
            }
          });
        }, 1500);
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

// Helper function to get language name
function getLanguageName(langCode) {
  const languages = {
    'en': 'English',
    'fil': 'Filipino (Tagalog)'
  };
  return languages[langCode] || langCode;
}

// Save settings to backend
async function saveSettingsToBackend(settings) {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      console.warn('CSRF token not found, skipping backend save');
      return;
    }

    const response = await fetch('/employee/settings/save', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(settings)
    });

    if (response.ok) {
      const data = await response.json();
      console.log('Settings saved to backend:', data);
    } else {
      console.warn('Failed to save settings to backend:', response.status);
    }
  } catch (error) {
    console.warn('Error saving settings to backend:', error);
  }
}

// Apply animations setting
function applyAnimationsSetting(enabled) {
  const style = document.getElementById('animations-style') || document.createElement('style');
  style.id = 'animations-style';
  
  if (!enabled) {
    style.textContent = `
      *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }
    `;
  } else {
    style.textContent = '';
  }
  
  if (!document.getElementById('animations-style')) {
    document.head.appendChild(style);
  }
  
  // Save to localStorage
  localStorage.setItem('animations', enabled);
}

// Comprehensive Language translations
const translations = {
  'en': {
    // Greetings & Basic
    'dashboard': 'Dashboard',
    'good_afternoon': 'Good afternoon',
    'good_morning': 'Good morning',
    'good_evening': 'Good evening',
    'employee_portal': 'Employee Portal',
    'jetlouge_employee_portal': 'Jetlouge Employee Portal',
    'home': 'Home',
    'employee_dashboard': 'Employee Dashboard',
    
    // Navigation & Menu
    'leave_application_balance': 'Leave Application & Balance',
    'attendance_time_logs': 'Attendance & Time Logs',
    'payslip_access': 'Payslip Access',
    'claim_reimbursement': 'Claim & Reimbursement',
    'my_trainings': 'My Trainings',
    'competency_profile': 'Competency Profile',
    'request_forms': 'Request Forms',
    'profile_updates': 'Profile Updates',
    'settings': 'Settings',
    'logout': 'Logout',
    
    // Training & Notifications
    'training_notifications': 'Training Notifications',
    'you_have_been_assigned_training': 'You have been assigned training',
    'please_check_your_upcoming_trainings': 'Please check your upcoming trainings',
    'upcoming_trainings': 'Upcoming Trainings',
    'starts_next_week': 'Starts next week',
    'new': 'New',
    
    // Dashboard Cards & Stats
    'pending_leave_requests': 'Pending Leave Requests',
    'attendance_this_month': 'Attendance This Month',
    'latest_payslip': 'Latest Payslip',
    'company_announcements': 'Company Announcements',
    
    // Time & Dates
    'week_ago': 'week ago',
    'weeks_ago': 'weeks ago',
    'day_ago': 'day ago',
    'days_ago': 'days ago',
    'hour_ago': 'hour ago',
    'hours_ago': 'hours ago',
    'minute_ago': 'minute ago',
    'minutes_ago': 'minutes ago',
    'october_2024': 'October 2024',
    
    // Actions & Buttons
    'view': 'View',
    'edit': 'Edit',
    'delete': 'Delete',
    'save': 'Save',
    'cancel': 'Cancel',
    'submit': 'Submit',
    'close': 'Close',
    'actions': 'Actions',
    'date': 'Date',
    'title': 'Title',
    'message': 'Message',
    'priority': 'Priority',
    
    // Training Specific
    'problem_solving': 'Problem-Solving',
    'time_management': 'Time Management',
    'customer_service_excellence': 'Customer Service Excellence',
    'communication_skills': 'Communication Skills',
    
    // Common Interface Elements
    'employee_id': 'Employee ID',
    'employee': 'Employee',
    'status': 'Status',
    'active': 'Active',
    'inactive': 'Inactive',
    'pending': 'Pending',
    'approved': 'Approved',
    'rejected': 'Rejected'
  },
  'fil': {
    // Greetings & Basic
    'dashboard': 'Dashboard',
    'good_afternoon': 'Magandang hapon',
    'good_morning': 'Magandang umaga',
    'good_evening': 'Magandang gabi',
    'employee_portal': 'Portal ng Empleyado',
    'jetlouge_employee_portal': 'Jetlouge Portal ng Empleyado',
    'home': 'Tahanan',
    'employee_dashboard': 'Dashboard ng Empleyado',
    
    // Navigation & Menu
    'leave_application_balance': 'Aplikasyon at Balanse ng Leave',
    'attendance_time_logs': 'Pagdalo at Mga Tala ng Oras',
    'payslip_access': 'Access sa Payslip',
    'claim_reimbursement': 'Claim at Reimbursement',
    'my_trainings': 'Aking mga Pagsasanay',
    'competency_profile': 'Profile ng Kakayahan',
    'request_forms': 'Mga Form ng Kahilingan',
    'profile_updates': 'Mga Update sa Profile',
    'settings': 'Mga Setting',
    'logout': 'Mag-logout',
    
    // Training & Notifications
    'training_notifications': 'Mga Abiso sa Pagsasanay',
    'you_have_been_assigned_training': 'Nakatakda ka sa pagsasanay',
    'please_check_your_upcoming_trainings': 'Pakitingnan ang inyong mga paparating na pagsasanay',
    'upcoming_trainings': 'Mga Paparating na Pagsasanay',
    'starts_next_week': 'Magsisimula sa susunod na linggo',
    'new': 'Bago',
    
    // Dashboard Cards & Stats
    'pending_leave_requests': 'Mga Nakabinbing Kahilingan sa Leave',
    'attendance_this_month': 'Pagdalo ngayong Buwan',
    'latest_payslip': 'Pinakabagong Payslip',
    'company_announcements': 'Mga Pabatid ng Kumpanya',
    
    // Time & Dates
    'week_ago': 'isang linggo na ang nakalipas',
    'weeks_ago': 'mga linggo na ang nakalipas',
    'day_ago': 'isang araw na ang nakalipas',
    'days_ago': 'mga araw na ang nakalipas',
    'hour_ago': 'isang oras na ang nakalipas',
    'hours_ago': 'mga oras na ang nakalipas',
    'minute_ago': 'isang minuto na ang nakalipas',
    'minutes_ago': 'mga minuto na ang nakalipas',
    'october_2024': 'Oktubre 2024',
    
    // Actions & Buttons
    'view': 'Tingnan',
    'edit': 'I-edit',
    'delete': 'Tanggalin',
    'save': 'I-save',
    'cancel': 'Kanselahin',
    'submit': 'Ipasa',
    'close': 'Isara',
    'actions': 'Mga Aksyon',
    'date': 'Petsa',
    'title': 'Pamagat',
    'message': 'Mensahe',
    'priority': 'Priyoridad',
    
    // Training Specific
    'problem_solving': 'Paglutas ng Problema',
    'time_management': 'Pamamahala ng Oras',
    'customer_service_excellence': 'Kahusayan sa Serbisyo sa Customer',
    'communication_skills': 'Mga Kasanayan sa Komunikasyon',
    
    // Common Interface Elements
    'employee_id': 'ID ng Empleyado',
    'employee': 'Empleyado',
    'status': 'Katayuan',
    'active': 'Aktibo',
    'inactive': 'Hindi Aktibo',
    'pending': 'Naghihintay',
    'approved': 'Aprubado',
    'rejected': 'Tinanggihan'
  }
};

// Translation function
function translate(key, lang = null) {
  const currentLang = lang || localStorage.getItem('language') || 'en';
  return translations[currentLang] && translations[currentLang][key] ? translations[currentLang][key] : key;
}

// Comprehensive translation function that scans and translates all text
function applyLanguageTranslations() {
  const currentLang = localStorage.getItem('language') || 'en';
  
  if (currentLang === 'en') {
    console.log('Language is English, no translation needed');
    return;
  }
  
  try {
    // Translation mapping for common phrases and patterns
    const textMappings = {
      // Exact matches
      'Jetlouge Employee Portal': translate('jetlouge_employee_portal', currentLang),
      'Employee Portal': translate('employee_portal', currentLang),
      'Training Notifications': translate('training_notifications', currentLang),
      'Company Announcements': translate('company_announcements', currentLang),
      'Pending Leave Requests': translate('pending_leave_requests', currentLang),
      'Attendance This Month': translate('attendance_this_month', currentLang),
      'Latest Payslip': translate('latest_payslip', currentLang),
      'Upcoming Trainings': translate('upcoming_trainings', currentLang),
      'Dashboard': translate('dashboard', currentLang),
      'Home': translate('home', currentLang),
      'Employee Dashboard': translate('employee_dashboard', currentLang),
      
      // Training names
      'Problem-Solving': translate('problem_solving', currentLang),
      'Time Management': translate('time_management', currentLang),
      'Customer Service Excellence': translate('customer_service_excellence', currentLang),
      'Communication Skills': translate('communication_skills', currentLang),
      
      // Actions and buttons
      'View': translate('view', currentLang),
      'Edit': translate('edit', currentLang),
      'Delete': translate('delete', currentLang),
      'Save': translate('save', currentLang),
      'Cancel': translate('cancel', currentLang),
      'Submit': translate('submit', currentLang),
      'Close': translate('close', currentLang),
      'Actions': translate('actions', currentLang),
      'New': translate('new', currentLang),
      
      // Table headers
      'Date': translate('date', currentLang),
      'Title': translate('title', currentLang),
      'Message': translate('message', currentLang),
      'Priority': translate('priority', currentLang),
      'Status': translate('status', currentLang),
      'Employee ID': translate('employee_id', currentLang),
      'Employee': translate('employee', currentLang),
      
      // Status values
      'Active': translate('active', currentLang),
      'Inactive': translate('inactive', currentLang),
      'Pending': translate('pending', currentLang),
      'Approved': translate('approved', currentLang),
      'Rejected': translate('rejected', currentLang),
      
      // Time expressions
      'week ago': translate('week_ago', currentLang),
      'weeks ago': translate('weeks_ago', currentLang),
      'day ago': translate('day_ago', currentLang),
      'days ago': translate('days_ago', currentLang),
      'hour ago': translate('hour_ago', currentLang),
      'hours ago': translate('hours_ago', currentLang),
      'minute ago': translate('minute_ago', currentLang),
      'minutes ago': translate('minutes_ago', currentLang),
      'October 2024': translate('october_2024', currentLang),
      
      // Common phrases
      'You have been assigned training': translate('you_have_been_assigned_training', currentLang),
      'Please check your upcoming trainings': translate('please_check_your_upcoming_trainings', currentLang),
      'Starts next week': translate('starts_next_week', currentLang)
    };
    
    // Update navbar brand
    const navbarBrand = document.querySelector('.navbar-brand span');
    if (navbarBrand && navbarBrand.textContent.includes('Employee Portal')) {
      navbarBrand.textContent = translate('jetlouge_employee_portal', currentLang);
    }
    
    // Handle greetings with time-based logic
    const greetingElements = document.querySelectorAll('h1, h2, h3');
    greetingElements.forEach(element => {
      const text = element.textContent;
      if (text.includes('Good afternoon') || text.includes('Good morning') || 
          text.includes('Good evening') || text.includes('Magandang')) {
        
        const hour = new Date().getHours();
        let greetingKey = 'good_afternoon';
        if (hour < 12) greetingKey = 'good_morning';
        else if (hour >= 18) greetingKey = 'good_evening';
        
        // Extract user name
        const parts = text.split(', ');
        const userName = parts.length > 1 ? parts[1].replace('!', '') : '';
        
        if (userName) {
          element.textContent = `${translate(greetingKey, currentLang)}, ${userName}!`;
        }
      }
    });
    
    // Comprehensive text replacement function
    function replaceTextInElement(element) {
      if (element.nodeType === Node.TEXT_NODE) {
        let text = element.textContent.trim();
        if (text && textMappings[text]) {
          element.textContent = textMappings[text];
        }
      } else if (element.nodeType === Node.ELEMENT_NODE) {
        // Skip script and style elements
        if (element.tagName === 'SCRIPT' || element.tagName === 'STYLE') {
          return;
        }
        
        // For elements with only text content (no child elements)
        if (element.children.length === 0) {
          const text = element.textContent.trim();
          if (text && textMappings[text]) {
            element.textContent = textMappings[text];
          }
        } else {
          // Recursively process child nodes
          Array.from(element.childNodes).forEach(child => {
            replaceTextInElement(child);
          });
        }
      }
    }
    
    // Apply translations to the entire document body
    const elementsToTranslate = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, div, td, th, button, a, label, .card-title, .card-header, .badge, .btn');
    
    elementsToTranslate.forEach(element => {
      // Skip if element contains other elements (to avoid double translation)
      if (element.children.length === 0) {
        const text = element.textContent.trim();
        if (text && textMappings[text]) {
          element.textContent = textMappings[text];
        }
      }
    });
    
    // Handle breadcrumbs and navigation
    const breadcrumbs = document.querySelectorAll('.breadcrumb-item, .nav-link');
    breadcrumbs.forEach(item => {
      const text = item.textContent.trim();
      if (textMappings[text]) {
        item.textContent = textMappings[text];
      }
    });
    
    // Handle specific training notification content
    const trainingTexts = document.querySelectorAll('p, div');
    trainingTexts.forEach(element => {
      let text = element.textContent;
      
      // Replace training assignment text
      if (text.includes('You have been assigned training')) {
        text = text.replace('You have been assigned training', translate('you_have_been_assigned_training', currentLang));
        element.textContent = text;
      }
      
      // Replace check trainings text
      if (text.includes('Please check your upcoming trainings')) {
        text = text.replace('Please check your upcoming trainings', translate('please_check_your_upcoming_trainings', currentLang));
        element.textContent = text;
      }
    });
    
    console.log('Comprehensive language translation applied:', currentLang);
  } catch (error) {
    console.warn('Error applying comprehensive translations:', error);
  }
}

// Initialize all settings on page load
function initializeSettings() {
  // Initialize dark mode
  initializeDarkMode();
  
  // Initialize animations setting
  const animationsEnabled = localStorage.getItem('animations') !== 'false';
  applyAnimationsSetting(animationsEnabled);
  
  // Initialize notification preferences
  const emailNotifications = localStorage.getItem('emailNotifications') !== 'false';
  const pushNotifications = localStorage.getItem('pushNotifications') !== 'false';
  
  // Apply language translations with retry
  translateWithRetry();
  
  // Start translation observer for dynamic content
  setTimeout(() => {
    startTranslationObserver();
  }, 1000);
  
  // Apply any other settings as needed
  console.log('Settings initialized:', {
    theme: localStorage.getItem('theme') || 'light',
    language: localStorage.getItem('language') || 'en',
    animations: animationsEnabled,
    emailNotifications,
    pushNotifications
  });
}

// Manual translation trigger (for testing)
window.changeLanguage = function(langCode) {
  localStorage.setItem('language', langCode);
  applyLanguageTranslations();
  console.log('Language changed to:', langCode);
};

// Continuous translation observer for dynamic content
let translationObserver = null;

function startTranslationObserver() {
  const currentLang = localStorage.getItem('language') || 'en';
  
  if (currentLang === 'en') {
    return; // No need to observe if language is English
  }
  
  // Stop existing observer
  if (translationObserver) {
    translationObserver.disconnect();
  }
  
  // Create new observer
  translationObserver = new MutationObserver(function(mutations) {
    let shouldTranslate = false;
    
    mutations.forEach(function(mutation) {
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        // Check if new text nodes were added
        mutation.addedNodes.forEach(function(node) {
          if (node.nodeType === Node.TEXT_NODE || 
              (node.nodeType === Node.ELEMENT_NODE && node.textContent.trim())) {
            shouldTranslate = true;
          }
        });
      }
    });
    
    if (shouldTranslate) {
      // Debounce translation calls
      clearTimeout(window.translationTimeout);
      window.translationTimeout = setTimeout(() => {
        applyLanguageTranslations();
      }, 500);
    }
  });
  
  // Start observing
  translationObserver.observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true
  });
  
  console.log('Translation observer started for language:', currentLang);
}

// Enhanced translation function with retry mechanism
function translateWithRetry(maxRetries = 3) {
  let retryCount = 0;
  
  function attemptTranslation() {
    try {
      applyLanguageTranslations();
      console.log('Translation successful on attempt:', retryCount + 1);
    } catch (error) {
      retryCount++;
      if (retryCount < maxRetries) {
        console.warn(`Translation failed, retrying (${retryCount}/${maxRetries}):`, error);
        setTimeout(attemptTranslation, 1000 * retryCount);
      } else {
        console.error('Translation failed after', maxRetries, 'attempts:', error);
      }
    }
  }
  
  attemptTranslation();
}

// Initialize settings on page load
document.addEventListener('DOMContentLoaded', function() {
  initializeSettings();
  
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
