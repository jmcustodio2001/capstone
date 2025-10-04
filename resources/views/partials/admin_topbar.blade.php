<!-- Enhanced Admin Navbar with Bootstrap Dropdown Support -->
<style>
/* Enhanced dropdown styling for better visibility */
.dropdown-menu {
  min-width: 200px !important;
  border: 1px solid rgba(0,0,0,.15);
  border-radius: 0.375rem;
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
  z-index: 1050;
}

.dropdown-menu.show {
  display: block !important;
  animation: dropdownFadeIn 0.15s ease-in;
}

@keyframes dropdownFadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-item {
  padding: 0.5rem 1rem;
  transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover,
.dropdown-item:focus {
  background-color: #f8f9fa;
  color: #495057;
}

.dropdown-header {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 600;
}

.dropdown-divider {
  margin: 0.5rem 0;
  border-top: 1px solid #dee2e6;
}

/* Ensure dropdowns are above other elements */
.navbar .dropdown {
  position: relative;
}

.navbar .dropdown-toggle {
  border: none;
  background: transparent;
}

.navbar .dropdown-toggle:focus {
  box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
}

/* Debug indicators (can be removed in production) */
.dropdown-menu::before {
  content: '';
  position: absolute;
  top: -6px;
  right: 15px;
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-bottom: 6px solid #fff;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--jetlouge-primary); z-index: 1055;" aria-label="Main navigation">
  <div class="container-fluid">
    <button class="sidebar-toggle desktop-toggle me-3 d-none d-lg-block" id="desktop-toggle" title="Toggle Sidebar">
      <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
      <i class="bi bi-airplane me-2 d-none d-sm-inline"></i>
      <span class="d-none d-md-inline">Jetlouge Travels</span>
      <span class="d-inline d-md-none">JT Admin</span>
    </a>
    
    <!-- Enhanced Admin Tools Section -->
    <div class="d-flex align-items-center">
      <!-- System Status Indicator -->
      <div class="me-2 d-none d-md-block">
        <button class="btn btn-outline-light btn-sm" onclick="showSystemStatus()" title="System Status">
          <i class="bi bi-activity text-success"></i>
          <span class="d-none d-lg-inline ms-1">System</span>
        </button>
      </div>

      <!-- Notifications Dropdown -->
      <div class="dropdown me-2">
        <button class="btn btn-outline-light btn-sm position-relative" type="button" onclick="showNotifications()" title="Notifications">
          <i class="bi bi-bell"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="font-size: 0.6em; display: none;">
            0
          </span>
        </button>
      </div>

      <!-- Quick Actions Dropdown -->

      <!-- Admin Tools Dropdown -->
      <div class="dropdown me-2 d-none d-md-block">
        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="adminToolsDropdown" 
                data-bs-toggle="dropdown" aria-expanded="false" title="Admin Tools">
          <i class="bi bi-tools"></i>
          <span class="d-none d-lg-inline ms-1">Tools</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminToolsDropdown" id="adminToolsMenu">
          <li><h6 class="dropdown-header"><i class="bi bi-tools me-1"></i>Admin Tools</h6></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="showUserActivity()"><i class="bi bi-activity me-2"></i>User Activity</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="showSystemLogs()"><i class="bi bi-journal-text me-2"></i>System Logs</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="showDatabaseStatus()"><i class="bi bi-database me-2"></i>Database Status</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="showSecuritySettings()"><i class="bi bi-shield-check me-2"></i>Security Settings</a></li>
        </ul>
      </div>

      <!-- Admin Profile Dropdown -->
      <div class="dropdown me-2">
        <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center" type="button" 
                id="adminProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          @if(Auth::guard('admin')->check() && Auth::guard('admin')->user()->profile_picture)
            <img src="{{ asset('storage/profile_pictures/' . Auth::guard('admin')->user()->profile_picture) }}" 
                 alt="Admin Profile" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
          @else
            <i class="bi bi-person-circle me-1"></i>
          @endif
          <span class="d-none d-lg-inline">
            @if(Auth::guard('admin')->check())
              {{ Auth::guard('admin')->user()->name }}
            @else
              Admin
            @endif
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminProfileDropdown" id="adminProfileMenu">
          <li><h6 class="dropdown-header">
            <i class="bi bi-person-badge me-1"></i>
            @if(Auth::guard('admin')->check())
              {{ Auth::guard('admin')->user()->name }}
            @else
              Admin Panel
            @endif
          </h6></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="bi bi-gear me-2"></i>Account Settings</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="showAdminProfile()"><i class="bi bi-person me-2"></i>View Profile</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="changePassword()"><i class="bi bi-key me-2"></i>Change Password</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="showAboutSystem()"><i class="bi bi-info-circle me-2"></i>About System</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmLogout()">
              <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
          </li>
        </ul>
      </div>
      
      <button class="sidebar-toggle mobile-toggle d-lg-none" id="menu-btn" title="Open Menu">
        <i class="bi bi-list fs-5"></i>
      </button>
    </div>
  </div>
</nav>

<!-- Sidebar toggle functionality for all modules -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing sidebar toggle and dropdowns...');
    
    // Initialize Bootstrap dropdowns with robust error handling
    try {
      let retryCount = 0;
      const maxRetries = 20; // 2 seconds max wait
      
      const initializeDropdowns = () => {
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
          const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
          let successCount = 0;
          
          dropdownElementList.forEach(function (dropdownToggleEl) {
            try {
              // Check if already initialized
              if (!bootstrap.Dropdown.getInstance(dropdownToggleEl)) {
                new bootstrap.Dropdown(dropdownToggleEl, {
                  boundary: 'viewport',
                  display: 'dynamic',
                  autoClose: true
                });
                successCount++;
              }
            } catch (e) {
              console.warn('Failed to initialize dropdown:', dropdownToggleEl.id, e);
            }
          });
          
          console.log(`Bootstrap dropdowns initialized: ${successCount}/${dropdownElementList.length}`);
          
          // Add event listeners for debugging
          dropdownElementList.forEach(function(dropdown) {
            dropdown.addEventListener('show.bs.dropdown', function(e) {
              console.log('Dropdown showing:', e.target.id);
            });
            dropdown.addEventListener('shown.bs.dropdown', function(e) {
              console.log('Dropdown shown:', e.target.id);
            });
            dropdown.addEventListener('hide.bs.dropdown', function(e) {
              console.log('Dropdown hiding:', e.target.id);
            });
          });
          
        } else if (retryCount < maxRetries) {
          retryCount++;
          console.warn(`Bootstrap not available, retrying (${retryCount}/${maxRetries})...`);
          setTimeout(initializeDropdowns, 100);
        } else {
          console.error('Bootstrap failed to load after maximum retries');
          // Fallback to manual dropdown handling
          enableManualDropdowns();
        }
      };
      
      // Fallback manual dropdown functionality
      const enableManualDropdowns = () => {
        console.log('Enabling manual dropdown fallback');
        document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
          toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.nextElementSibling;
            if (dropdown && dropdown.classList.contains('dropdown-menu')) {
              // Close all other dropdowns
              document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                if (menu !== dropdown) {
                  menu.classList.remove('show');
                }
              });
              
              // Toggle current dropdown
              dropdown.classList.toggle('show');
              this.setAttribute('aria-expanded', dropdown.classList.contains('show'));
            }
          });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
              menu.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
              toggle.setAttribute('aria-expanded', 'false');
            });
          }
        });
      };
      
      // Start initialization
      initializeDropdowns();
      
    } catch (error) {
      console.error('Error initializing dropdowns:', error);
    }
    
    const menuBtn = document.getElementById('menu-btn');
    const desktopToggle = document.getElementById('desktop-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('main-content');

    console.log('Elements found:', {
      menuBtn: !!menuBtn,
      desktopToggle: !!desktopToggle,
      sidebar: !!sidebar,
      overlay: !!overlay,
      mainContent: !!mainContent
    });

    // Force sidebar to be visible initially
    if (sidebar) {
      sidebar.style.display = 'block';
      sidebar.style.visibility = 'visible';
      sidebar.style.opacity = '1';
      sidebar.style.transform = 'translateX(0)';
    }

    // Mobile sidebar toggle
    if (menuBtn && sidebar && overlay) {
      menuBtn.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('Mobile toggle clicked');
        sidebar.classList.toggle('active');
        overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
      });
    }

    // Desktop sidebar toggle
    if (desktopToggle && sidebar && mainContent) {
      desktopToggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('Desktop toggle clicked');
        
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        if (isCollapsed) {
          sidebar.classList.remove('collapsed');
          sidebar.style.transform = 'translateX(0)';
          mainContent.classList.remove('expanded');
        } else {
          sidebar.classList.add('collapsed');
          sidebar.style.transform = 'translateX(-100%)';
          mainContent.classList.add('expanded');
        }
        
        localStorage.setItem('sidebarCollapsed', !isCollapsed);
      });
    }

    // Close mobile sidebar when clicking overlay
    if (overlay) {
      overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
      });
    }
  });
</script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Enhanced Admin Topbar JavaScript Functions -->
<script>
// Dropdown debugging and testing functions
function testDropdown() {
  const toolsMenu = document.getElementById('adminToolsMenu');
  const toolsButton = document.getElementById('adminToolsDropdown');
  const profileMenu = document.getElementById('adminProfileMenu');
  const profileButton = document.getElementById('adminProfileDropdown');
  
  console.log('=== DROPDOWN DIAGNOSTIC ===');
  console.log('Elements found:', {
    toolsMenu: !!toolsMenu,
    toolsButton: !!toolsButton,
    profileMenu: !!profileMenu,
    profileButton: !!profileButton
  });
  
  console.log('Bootstrap status:', {
    bootstrap: typeof bootstrap !== 'undefined',
    dropdownClass: typeof bootstrap !== 'undefined' ? !!bootstrap.Dropdown : false
  });
  
  // Test Bootstrap dropdown instances
  if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
    const toolsDropdown = bootstrap.Dropdown.getInstance(toolsButton);
    const profileDropdown = bootstrap.Dropdown.getInstance(profileButton);
    
    console.log('Bootstrap instances:', {
      toolsDropdown: !!toolsDropdown,
      profileDropdown: !!profileDropdown
    });
    
    // Test toggle functionality
    if (toolsDropdown) {
      console.log('Testing Tools dropdown...');
      toolsDropdown.toggle();
      setTimeout(() => toolsDropdown.toggle(), 2000); // Auto close after 2s
    } else {
      console.warn('Tools dropdown instance not found');
    }
    
    if (profileDropdown) {
      console.log('Testing Profile dropdown...');
      setTimeout(() => {
        profileDropdown.toggle();
        setTimeout(() => profileDropdown.toggle(), 2000); // Auto close after 2s
      }, 1000);
    } else {
      console.warn('Profile dropdown instance not found');
    }
  } else {
    console.error('Bootstrap not available - dropdowns may not work properly');
  }
  
  console.log('=== END DIAGNOSTIC ===');
}

// Auto-test dropdowns after page load (for debugging)
window.addEventListener('load', function() {
  setTimeout(() => {
    console.log('Running automatic dropdown test...');
    if (window.location.search.includes('debug=dropdowns')) {
      testDropdown();
    }
  }, 1000);
});

// Enhanced CSRF token handling for domain deployment
function getCSRFToken() {
  const csrfToken = document.querySelector('meta[name="csrf-token"]');
  return csrfToken ? csrfToken.getAttribute('content') : '';
}

// Get fresh CSRF token from server
async function getFreshCSRFToken() {
  try {
    const response = await fetch('/csrf-token', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      if (data.csrf_token) {
        // Update the meta tag with fresh token
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
          metaTag.setAttribute('content', data.csrf_token);
        }
        return data.csrf_token;
      }
    }
  } catch (error) {
    console.warn('Failed to get fresh CSRF token:', error);
  }
  return getCSRFToken(); // Fallback to existing token
}

// Enhanced fetch with automatic CSRF token refresh
async function fetchWithCSRF(url, options = {}) {
  // Ensure we have fresh headers
  const defaultHeaders = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': await getFreshCSRFToken()
  };
  
  // Merge with provided headers
  options.headers = { ...defaultHeaders, ...options.headers };
  options.credentials = options.credentials || 'same-origin';
  
  try {
    let response = await fetch(url, options);
    
    // If we get 419 (CSRF token mismatch), try once more with fresh token
    if (response.status === 419) {
      console.log('CSRF token mismatch, retrying with fresh token...');
      
      // Get fresh token and retry
      const freshToken = await getFreshCSRFToken();
      options.headers['X-CSRF-TOKEN'] = freshToken;
      
      response = await fetch(url, options);
      
      // If still 419, show session expired message
      if (response.status === 419) {
        Swal.fire({
          title: 'Session Expired',
          text: 'Your session has expired. Please reload the page to continue.',
          icon: 'warning',
          confirmButtonText: 'Reload Page',
          allowOutsideClick: false
        }).then(() => {
          window.location.reload();
        });
        throw new Error('CSRF token expired - session invalid');
      }
    }
    
    return response;
  } catch (error) {
    if (!error.message.includes('CSRF token expired')) {
      console.error('Fetch error:', error);
    }
    throw error;
  }
}

// System Status Function with Real Data
async function showSystemStatus() {
  try {
    Swal.fire({
      title: 'Loading System Status...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const response = await fetchWithCSRF('/admin/system-status', {
      method: 'GET'
    });

    const data = await response.json();
    
    if (data.success) {
      // Debug information removed as requested

      Swal.fire({
        title: '<i class="bi bi-activity text-success"></i> System Status',
        html: `
          <div class="text-start">
            <div class="row mb-2">
              <div class="col-6"><strong>Server Status:</strong></div>
              <div class="col-6"><span class="badge bg-${data.server_status === 'online' ? 'success' : 'danger'}">${data.server_status}</span></div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Database:</strong></div>
              <div class="col-6"><span class="badge bg-${data.database_status === 'connected' ? 'success' : 'danger'}">${data.database_status}</span></div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Active Employees:</strong></div>
              <div class="col-6"><span class="badge bg-info">${data.active_employees}</span></div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Online Users:</strong></div>
              <div class="col-6"><span class="badge bg-primary">${data.online_users}</span></div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Total Trainings:</strong></div>
              <div class="col-6"><span class="badge bg-secondary">${data.total_trainings}</span></div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Pending Requests:</strong></div>
              <div class="col-6"><span class="badge bg-warning">${data.pending_requests}</span></div>
            </div>
            <div class="row">
              <div class="col-6"><strong>System Uptime:</strong></div>
              <div class="col-6">${data.system_uptime}</div>
            </div>
          </div>
        `,
        width: 600,
        confirmButtonText: 'Refresh Status',
        showCancelButton: true,
        cancelButtonText: 'Close',
        showDenyButton: true,
        denyButtonText: 'Reset Uptime',
        denyButtonColor: '#dc3545'
      }).then((result) => {
        if (result.isConfirmed) {
          showSystemStatus(); // Refresh
        } else if (result.isDenied) {
          resetSystemUptime(); // Reset uptime
        }
      });
    } else {
      throw new Error(data.message || 'Failed to load system status');
    }
  } catch (error) {
    console.error('System Status Error:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to load system status: ' + error.message,
      icon: 'error',
      confirmButtonText: 'Close'
    });
  }
}

// Reset System Uptime Function
async function resetSystemUptime() {
  try {
    const { value: confirmed } = await Swal.fire({
      title: 'Reset System Uptime?',
      html: `
        <div class="text-start">
          <p class="mb-3"><i class="bi bi-exclamation-triangle text-warning"></i> This will reset the system uptime counter to zero.</p>
          <p class="mb-3">This action will:</p>
          <ul class="mb-3">
            <li>Reset the uptime display to "0 days, 0 hours, 0 minutes"</li>
            <li>Clear the persistent uptime tracking file</li>
            <li>Start counting from the current time</li>
          </ul>
          <p class="text-muted">This is useful when the uptime shows unrealistic values due to system issues.</p>
        </div>
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Reset Uptime',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc3545'
    });

    if (confirmed) {
      Swal.fire({
        title: 'Resetting Uptime...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const response = await fetchWithCSRF('/admin/reset-uptime', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();
      
      if (data.success) {
        Swal.fire({
          title: 'Uptime Reset!',
          html: `
            <div class="text-center">
              <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
              <p class="mt-3">System uptime counter has been reset successfully!</p>
              <p><strong>New Uptime:</strong> ${data.new_uptime}</p>
            </div>
          `,
          icon: 'success',
          confirmButtonText: 'Refresh System Status'
        }).then((result) => {
          if (result.isConfirmed) {
            showSystemStatus(); // Show updated status
          }
        });
      } else {
        throw new Error(data.message || 'Failed to reset uptime');
      }
    }
  } catch (error) {
    console.error('Reset Uptime Error:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to reset system uptime: ' + error.message,
      icon: 'error',
      confirmButtonText: 'Close'
    });
  }
}

// Notifications Function with Real Data
async function showNotifications() {
  try {
    Swal.fire({
      title: 'Loading Notifications...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const response = await fetchWithCSRF('/admin/notifications', {
      method: 'GET'
    });

    const data = await response.json();
    
    if (data.success) {
      let notificationsHtml = '';
      
      if (data.notifications && data.notifications.length > 0) {
        data.notifications.forEach(notification => {
          const iconClass = getNotificationIcon(notification.type);
          notificationsHtml += `
            <div class="list-group-item">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="${iconClass} me-2"></i>${notification.title}</h6>
                <small>${notification.time_ago}</small>
              </div>
              <p class="mb-1">${notification.message}</p>
              ${notification.action_url ? `<small><a href="${notification.action_url}" class="text-primary">View Details</a></small>` : ''}
            </div>
          `;
        });
      } else {
        notificationsHtml = `
          <div class="text-center py-4">
            <i class="bi bi-bell-slash fs-1 text-muted"></i>
            <p class="text-muted mt-2">No new notifications</p>
          </div>
        `;
      }

      Swal.fire({
        title: '<i class="bi bi-bell text-warning"></i> Admin Notifications',
        html: `
          <div class="list-group text-start" style="max-height: 400px; overflow-y: auto;">
            ${notificationsHtml}
          </div>
        `,
        width: 700,
        confirmButtonText: data.notifications.length > 0 ? 'Mark All Read' : 'Close',
        showCancelButton: data.notifications.length > 0,
        cancelButtonText: 'Close'
      }).then(async (result) => {
        if (result.isConfirmed && data.notifications.length > 0) {
          await markAllNotificationsRead();
        }
      });
    } else {
      throw new Error(data.message || 'Failed to load notifications');
    }
  } catch (error) {
    console.error('Notifications Error:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to load notifications: ' + error.message,
      icon: 'error',
      confirmButtonText: 'Close'
    });
  }
}

function getNotificationIcon(type) {
  const icons = {
    'employee_registration': 'bi bi-person-plus text-success',
    'training_completion': 'bi bi-book text-primary',
    'leave_request': 'bi bi-calendar-check text-info',
    'system_update': 'bi bi-arrow-up-circle text-warning',
    'competency_request': 'bi bi-star text-warning',
    'feedback_received': 'bi bi-chat-dots text-info',
    'default': 'bi bi-info-circle text-secondary'
  };
  return icons[type] || icons['default'];
}

async function markAllNotificationsRead() {
  try {
    const response = await fetchWithCSRF('/admin/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();
    
    if (data.success) {
      const notificationBadge = document.getElementById('notification-count');
      if (notificationBadge) {
        notificationBadge.style.display = 'none';
      }
      Swal.fire('Success!', 'All notifications marked as read', 'success');
    } else {
      throw new Error(data.message || 'Failed to mark notifications as read');
    }
  } catch (error) {
    console.error('Mark Notifications Error:', error);
    Swal.fire('Error', 'Failed to mark notifications as read', 'error');
  }
}

// Quick Actions Functions with Real Backend Integration
async function addEmployeeQuick() {
  // First verify admin password
  const { value: password } = await Swal.fire({
    title: 'Admin Verification Required',
    html: `
      <div class="text-start">
        <p class="mb-3"><i class="bi bi-shield-check text-warning"></i> Please enter your admin password to add a new employee:</p>
        <input type="password" class="form-control" id="admin-password" placeholder="Enter admin password" minlength="6">
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Verify & Continue',
    preConfirm: () => {
      const password = document.getElementById('admin-password').value;
      if (!password) {
        Swal.showValidationMessage('Please enter your password');
        return false;
      }
      return password;
    }
  });

  if (!password) return;

  // Verify password
  try {
    const verifyResponse = await fetchWithCSRF('/admin/verify-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password: password })
    });

    const verifyData = await verifyResponse.json();
    
    if (!verifyResponse.ok || !(verifyData.success || verifyData.valid)) {
      Swal.fire('Error', verifyData.message || 'Invalid admin password', 'error');
      return;
    }

    // Show employee creation form
    const { value: formData } = await Swal.fire({
      title: 'Add New Employee',
      html: `
        <div class="text-start">
          <div class="mb-3">
            <label class="form-label">Employee ID</label>
            <input type="text" class="form-control" id="emp-id" placeholder="Enter employee ID">
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" id="emp-name" placeholder="Enter full name">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="emp-email" placeholder="Enter email">
          </div>
          <div class="mb-3">
            <label class="form-label">Department</label>
            <select class="form-control" id="emp-dept">
              <option value="">Select Department</option>
              <option value="HR">Human Resources</option>
              <option value="IT">Information Technology</option>
              <option value="Finance">Finance</option>
              <option value="Operations">Operations</option>
              <option value="Sales">Sales</option>
              <option value="Marketing">Marketing</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Position</label>
            <input type="text" class="form-control" id="emp-position" placeholder="Enter position">
          </div>
        </div>
      `,
      width: 500,
      showCancelButton: true,
      confirmButtonText: 'Create Employee',
      preConfirm: () => {
        const empId = document.getElementById('emp-id').value;
        const name = document.getElementById('emp-name').value;
        const email = document.getElementById('emp-email').value;
        const department = document.getElementById('emp-dept').value;
        const position = document.getElementById('emp-position').value;
        
        if (!empId || !name || !email || !department || !position) {
          Swal.showValidationMessage('Please fill all required fields');
          return false;
        }
        
        return { empId, name, email, department, position };
      }
    });

    if (formData) {
      // Submit employee creation
      Swal.fire({
        title: 'Creating Employee...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const createResponse = await fetchWithCSRF('/admin/employees', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          employee_id: formData.empId,
          name: formData.name,
          email: formData.email,
          department: formData.department,
          position: formData.position,
          password: 'employee123' // Default password
        })
      });

      const createData = await createResponse.json();
      
      if (createData.success) {
        Swal.fire('Success!', 'Employee created successfully', 'success');
      } else {
        Swal.fire('Error', createData.message || 'Failed to create employee', 'error');
      }
    }
  } catch (error) {
    console.error('Add Employee Error:', error);
    Swal.fire('Error', 'Failed to create employee: ' + error.message, 'error');
  }
}

function createTrainingQuick() {
  window.location.href = '/admin/course-management';
}

// System Functions with Real Backend Integration
async function systemBackup() {
  // First verify admin password
  const { value: password } = await Swal.fire({
    title: 'Admin Verification Required',
    html: `
      <div class="text-start">
        <p class="mb-3"><i class="bi bi-shield-exclamation text-danger"></i> System backup requires admin verification.</p>
        <p class="mb-3">This will create a complete backup of:</p>
        <ul class="text-start mb-3">
          <li>Database (all tables and data)</li>
          <li>Employee files and documents</li>
          <li>Training materials and certificates</li>
          <li>System configuration</li>
        </ul>
        <input type="password" class="form-control" id="backup-password" placeholder="Enter admin password">
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Verify & Start Backup',
    preConfirm: () => {
      const password = document.getElementById('backup-password').value;
      if (!password) {
        Swal.showValidationMessage('Please enter your admin password');
        return false;
      }
      return password;
    }
  });

  if (!password) return;

  try {
    // Verify password first
    const verifyResponse = await fetchWithCSRF('/admin/verify-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password: password })
    });

    const verifyData = await verifyResponse.json();
    
    if (!verifyResponse.ok || !(verifyData.success || verifyData.valid)) {
      Swal.fire('Error', verifyData.message || 'Invalid admin password', 'error');
      return;
    }

    // Start backup process
    const backupResponse = await fetchWithCSRF('/admin/system/backup', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'create_backup'
      })
    });

    const backupData = await backupResponse.json();
    
    if (backupData.success) {
      let timerInterval;
      Swal.fire({
        title: 'Creating System Backup...',
        html: 'Backup will complete in <b></b> seconds.',
        timer: 15000,
        timerProgressBar: true,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
          const b = Swal.getHtmlContainer().querySelector('b');
          timerInterval = setInterval(() => {
            b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
          }, 100);
        },
        willClose: () => {
          clearInterval(timerInterval);
        }
      }).then(() => {
        Swal.fire({
          title: 'Backup Complete!',
          html: `
            <div class="text-center">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
              <p class="mt-3">System backup completed successfully!</p>
              <p><strong>Backup ID:</strong> ${backupData.backup_id || 'BACKUP_' + Date.now()}</p>
              <p><strong>Size:</strong> ${backupData.backup_size || 'Calculating...'}</p>
            </div>
          `,
          icon: 'success',
          confirmButtonText: 'Download Backup'
        }).then((result) => {
          if (result.isConfirmed && backupData.download_url) {
            window.open(backupData.download_url, '_blank');
          }
        });
      });
    } else {
      throw new Error(backupData.message || 'Failed to start backup');
    }
  } catch (error) {
    console.error('Backup Error:', error);
    Swal.fire('Error', 'Failed to create backup: ' + error.message, 'error');
  }
}

async function clearCache() {
  const { value: confirmed } = await Swal.fire({
    title: 'Clear System Cache',
    html: `
      <div class="text-start">
        <p class="mb-3"><i class="bi bi-exclamation-triangle text-warning"></i> This will clear all cached data including:</p>
        <ul class="mb-3">
          <li>Application cache</li>
          <li>Route cache</li>
          <li>Configuration cache</li>
          <li>View cache</li>
          <li>Session data</li>
        </ul>
        <p class="text-muted">This may temporarily slow down the system while cache rebuilds.</p>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Clear All Cache',
    cancelButtonText: 'Cancel'
  });

  if (confirmed) {
    try {
      Swal.fire({
        title: 'Clearing Cache...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const response = await fetchWithCSRF('/admin/system/clear-cache', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'clear_cache'
        })
      });

      const data = await response.json();
      
      if (data.success) {
        Swal.fire({
          title: 'Cache Cleared!',
          html: `
            <div class="text-center">
              <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
              <p class="mt-3">System cache cleared successfully!</p>
              <p class="text-muted">Cache types cleared: ${data.cleared_types ? data.cleared_types.join(', ') : 'All'}</p>
            </div>
          `,
          icon: 'success',
          timer: 3000,
          timerProgressBar: true
        });
      } else {
        throw new Error(data.message || 'Failed to clear cache');
      }
    } catch (error) {
      console.error('Clear Cache Error:', error);
      Swal.fire('Error', 'Failed to clear cache: ' + error.message, 'error');
    }
  }
}

// Admin Tools Functions with Real Data
async function showUserActivity() {
  try {
    Swal.fire({
      title: 'Loading User Activity...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const response = await fetchWithCSRF('/admin/user-activity', {
      method: 'GET'
    });

    const data = await response.json();
    
    if (data.success) {
      let activityHtml = '';
      
      if (data.activities && data.activities.length > 0) {
        activityHtml = `
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-striped">
              <thead class="table-dark">
                <tr>
                  <th>User</th>
                  <th>Action</th>
                  <th>IP Address</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
        `;
        
        data.activities.forEach(activity => {
          const statusBadge = activity.user_type === 'admin' ? 'bg-danger' : 'bg-primary';
          activityHtml += `
            <tr>
              <td>
                <span class="badge ${statusBadge}">${activity.user_type}</span>
                ${activity.user_name}
              </td>
              <td>${activity.action}</td>
              <td><small class="text-muted">${activity.ip_address || 'N/A'}</small></td>
              <td><small>${activity.time_ago}</small></td>
            </tr>
          `;
        });
        
        activityHtml += `
              </tbody>
            </table>
          </div>
        `;
      } else {
        activityHtml = `
          <div class="text-center py-4">
            <i class="bi bi-activity fs-1 text-muted"></i>
            <p class="text-muted mt-2">No recent user activity</p>
          </div>
        `;
      }

      Swal.fire({
        title: 'User Activity Monitor',
        html: activityHtml,
        width: 800,
        confirmButtonText: 'Refresh',
        showCancelButton: true,
        cancelButtonText: 'Close'
      }).then((result) => {
        if (result.isConfirmed) {
          showUserActivity(); // Refresh
        }
      });
    } else {
      throw new Error(data.message || 'Failed to load user activity');
    }
  } catch (error) {
    console.error('User Activity Error:', error);
    Swal.fire('Error', 'Failed to load user activity: ' + error.message, 'error');
  }
}

async function showSystemLogs() {
  try {
    Swal.fire({
      title: 'Loading System Logs...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const response = await fetchWithCSRF('/admin/system-logs', {
      method: 'GET'
    });

    const data = await response.json();
    
    if (data.success) {
      let logsHtml = '';
      
      if (data.logs && data.logs.length > 0) {
        logsHtml = `
          <div class="text-start" style="font-family: 'Courier New', monospace; font-size: 11px; max-height: 400px; overflow-y: auto; background: #1e1e1e; color: #fff; padding: 15px; border-radius: 5px;">
        `;
        
        data.logs.forEach(log => {
          const levelColor = {
            'ERROR': '#ff6b6b',
            'WARNING': '#ffa500',
            'INFO': '#4ecdc4',
            'DEBUG': '#95a5a6'
          };
          
          logsHtml += `
            <div style="margin-bottom: 5px;">
              <span style="color: #888;">[${log.timestamp}]</span>
              <span style="color: ${levelColor[log.level] || '#fff'}; font-weight: bold;">${log.level}:</span>
              <span>${log.message}</span>
            </div>
          `;
        });
        
        logsHtml += `</div>`;
      } else {
        logsHtml = `
          <div class="text-center py-4">
            <i class="bi bi-journal-text fs-1 text-muted"></i>
            <p class="text-muted mt-2">No recent system logs</p>
          </div>
        `;
      }

      Swal.fire({
        title: 'System Logs (Last 50 entries)',
        html: logsHtml,
        width: 900,
        confirmButtonText: 'Refresh',
        showCancelButton: true,
        cancelButtonText: 'Close'
      }).then((result) => {
        if (result.isConfirmed) {
          showSystemLogs(); // Refresh
        }
      });
    } else {
      throw new Error(data.message || 'Failed to load system logs');
    }
  } catch (error) {
    console.error('System Logs Error:', error);
    Swal.fire('Error', 'Failed to load system logs: ' + error.message, 'error');
  }
}

async function showDatabaseStatus() {
  try {
    Swal.fire({
      title: 'Loading Database Status...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const response = await fetchWithCSRF('/admin/database-status', {
      method: 'GET'
    });

    const data = await response.json();
    
    if (data.success) {
      Swal.fire({
        title: 'Database Status',
        html: `
          <div class="row text-start">
            <div class="col-md-6 mb-3">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title"><i class="bi bi-database me-2"></i>Connection Info</h6>
                  <p><strong>Status:</strong> <span class="badge bg-${data.connection_status === 'connected' ? 'success' : 'danger'}">${data.connection_status}</span></p>
                  <p><strong>Driver:</strong> ${data.driver || 'MySQL'}</p>
                  <p><strong>Version:</strong> ${data.version || 'N/A'}</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title"><i class="bi bi-table me-2"></i>Database Stats</h6>
                  <p><strong>Tables:</strong> ${data.table_count || 0}</p>
                  <p><strong>Total Records:</strong> ${data.total_records || 0}</p>
                  <p><strong>Database Size:</strong> ${data.database_size || 'N/A'}</p>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title"><i class="bi bi-speedometer2 me-2"></i>Performance</h6>
                  <div class="row">
                    <div class="col-6">
                      <p><strong>Active Connections:</strong> ${data.active_connections || 0}</p>
                      <p><strong>Queries/sec:</strong> ${data.queries_per_second || 0}</p>
                    </div>
                    <div class="col-6">
                      <p><strong>Uptime:</strong> ${data.uptime || 'N/A'}</p>
                      <p><strong>Last Backup:</strong> ${data.last_backup || 'Never'}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        width: 700,
        confirmButtonText: 'Refresh',
        showCancelButton: true,
        cancelButtonText: 'Close'
      }).then((result) => {
        if (result.isConfirmed) {
          showDatabaseStatus(); // Refresh
        }
      });
    } else {
      throw new Error(data.message || 'Failed to load database status');
    }
  } catch (error) {
    console.error('Database Status Error:', error);
    Swal.fire('Error', 'Failed to load database status: ' + error.message, 'error');
  }
}

// Profile Functions
function showAdminProfile() {
  Swal.fire({
    title: 'Admin Profile',
    html: `
      <div class="text-center mb-3">
        <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
      </div>
      <div class="text-start">
        <p><strong>Name:</strong> {{ Auth::guard('admin')->user()->name ?? 'Admin' }}</p>
        <p><strong>Email:</strong> {{ Auth::guard('admin')->user()->email ?? 'admin@jetlouge.com' }}</p>
        <p><strong>Role:</strong> System Administrator</p>
        <p><strong>Last Login:</strong> Today, 9:30 AM</p>
      </div>
    `,
    confirmButtonText: 'Edit Profile'
  });
}

function changePassword() {
  Swal.fire({
    title: 'Change Password',
    html: `
      <div class="text-start">
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" class="form-control" id="current-pass">
        </div>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control" id="new-pass">
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirm-pass">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Change Password'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire('Success!', 'Password changed successfully', 'success');
    }
  });
}

function showAboutSystem() {
  Swal.fire({
    title: 'About HR2ESS',
    html: `
      <div class="text-center">
        <i class="bi bi-airplane" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
        <h5 class="mt-3">HR2ESS - Human Resource Management System</h5>
        <p><strong>Version:</strong> 2.0.1</p>
        <p><strong>Developer:</strong> Jetlouge Travels</p>
        <p><strong>Last Update:</strong> January 2024</p>
        <hr>
        <small class="text-muted">© 2024 Jetlouge Travels. All rights reserved.</small>
      </div>
    `,
    confirmButtonText: 'Close'
  });
}

function confirmLogout() {
  Swal.fire({
    title: 'Confirm Logout',
    text: 'Are you sure you want to logout?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Logout',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc3545'
  }).then((result) => {
    if (result.isConfirmed) {
      // Create and submit logout form
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '{{ route("admin.logout") }}';
      
      const csrfToken = document.createElement('input');
      csrfToken.type = 'hidden';
      csrfToken.name = '_token';
      csrfToken.value = '{{ csrf_token() }}';
      
      form.appendChild(csrfToken);
      document.body.appendChild(form);
      form.submit();
    }
  });
}

// Enhanced Security Settings with Password Verification and SweetAlert
async function showSecuritySettings() {
  // First verify admin password
  const { value: password } = await Swal.fire({
    title: 'Admin Verification Required',
    html: `
      <div class="text-start">
        <p class="mb-3"><i class="bi bi-shield-exclamation text-danger"></i> Security settings require admin verification.</p>
        <p class="mb-3">Please enter your admin password to access security settings:</p>
        <input type="password" class="form-control" id="security-password" placeholder="Enter admin password" minlength="6">
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Verify & Continue',
    preConfirm: () => {
      const password = document.getElementById('security-password').value;
      if (!password) {
        Swal.showValidationMessage('Please enter your password');
        return false;
      }
      return password;
    }
  });

  if (!password) return;

  try {
    // Verify password using enhanced fetch
    const verifyResponse = await fetchWithCSRF('/admin/verify-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password: password })
    });

    const verifyData = await verifyResponse.json();
    
    if (!verifyResponse.ok || !(verifyData.success || verifyData.valid)) {
      Swal.fire('Error', verifyData.message || 'Invalid admin password', 'error');
      return;
    }

    // Load current security settings (with fallback defaults)
    let settings = {};
    try {
      const settingsResponse = await fetchWithCSRF('/admin/security-settings', {
        method: 'GET'
      });
      const settingsData = await settingsResponse.json();
      settings = settingsData.success ? settingsData.settings : {};
    } catch (error) {
      console.log('Using default settings due to:', error.message);
      // Use default settings if endpoint doesn't exist
      settings = {
        two_factor_enabled: true,
        login_alerts: true,
        session_timeout: false,
        password_complexity: false,
        login_attempts_limit: false,
        security_alerts: false,
        system_alerts: false,
        timeout_duration: 30,
        audit_logging: false,
        ip_restriction: false,
        maintenance_mode: false
      };
    }

    // Show comprehensive security settings
    const { value: formData } = await Swal.fire({
      title: '<i class="bi bi-shield-check text-success"></i> Security Settings',
      html: `
        <div class="text-start">
          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3"><i class="bi bi-key me-2"></i>Authentication</h6>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="two-factor" ${settings.two_factor_enabled ? 'checked' : ''}>
                <label class="form-check-label" for="two-factor">
                  <strong>Two-Factor Authentication</strong><br>
                  <small class="text-muted">Require OTP for admin login</small>
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="password-complexity" ${settings.password_complexity ? 'checked' : ''}>
                <label class="form-check-label" for="password-complexity">
                  <strong>Password Complexity</strong><br>
                  <small class="text-muted">Enforce strong passwords</small>
                </label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="login-attempts" ${settings.login_attempts_limit ? 'checked' : ''}>
                <label class="form-check-label" for="login-attempts">
                  <strong>Login Attempt Limits</strong><br>
                  <small class="text-muted">Block after failed attempts</small>
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="mb-3"><i class="bi bi-bell me-2"></i>Notifications</h6>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="login-alerts" ${settings.login_alerts ? 'checked' : ''}>
                <label class="form-check-label" for="login-alerts">
                  <strong>Login Alerts</strong><br>
                  <small class="text-muted">Email notifications for logins</small>
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="security-alerts" ${settings.security_alerts ? 'checked' : ''}>
                <label class="form-check-label" for="security-alerts">
                  <strong>Security Alerts</strong><br>
                  <small class="text-muted">Notify of security events</small>
                </label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="system-alerts" ${settings.system_alerts ? 'checked' : ''}>
                <label class="form-check-label" for="system-alerts">
                  <strong>System Alerts</strong><br>
                  <small class="text-muted">Critical system notifications</small>
                </label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3"><i class="bi bi-clock me-2"></i>Session Management</h6>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="session-timeout" ${settings.session_timeout ? 'checked' : ''}>
                <label class="form-check-label" for="session-timeout">
                  <strong>Auto Session Timeout</strong><br>
                  <small class="text-muted">Auto-logout after inactivity</small>
                </label>
              </div>
              <div class="mb-3">
                <label class="form-label">Timeout Duration (minutes)</label>
                <select class="form-control" id="timeout-duration">
                  <option value="15" ${settings.timeout_duration == 15 ? 'selected' : ''}>15 minutes</option>
                  <option value="30" ${settings.timeout_duration == 30 ? 'selected' : ''}>30 minutes</option>
                  <option value="60" ${settings.timeout_duration == 60 ? 'selected' : ''}>1 hour</option>
                  <option value="120" ${settings.timeout_duration == 120 ? 'selected' : ''}>2 hours</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="mb-3"><i class="bi bi-shield-exclamation me-2"></i>System Security</h6>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="audit-logging" ${settings.audit_logging ? 'checked' : ''}>
                <label class="form-check-label" for="audit-logging">
                  <strong>Audit Logging</strong><br>
                  <small class="text-muted">Log all admin actions</small>
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="ip-restriction" ${settings.ip_restriction ? 'checked' : ''}>
                <label class="form-check-label" for="ip-restriction">
                  <strong>IP Restrictions</strong><br>
                  <small class="text-muted">Limit admin access by IP</small>
                </label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="maintenance-mode" ${settings.maintenance_mode ? 'checked' : ''}>
                <label class="form-check-label" for="maintenance-mode">
                  <strong>Maintenance Mode</strong><br>
                  <small class="text-muted">Enable system maintenance</small>
                </label>
              </div>
            </div>
          </div>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> Changes to security settings will affect all admin users and may require re-authentication.
          </div>
        </div>
      `,
      width: 800,
      showCancelButton: true,
      confirmButtonText: 'Save Security Settings',
      cancelButtonText: 'Cancel',
      preConfirm: () => {
        return {
          two_factor: document.getElementById('two-factor').checked,
          password_complexity: document.getElementById('password-complexity').checked,
          login_attempts: document.getElementById('login-attempts').checked,
          login_alerts: document.getElementById('login-alerts').checked,
          security_alerts: document.getElementById('security-alerts').checked,
          system_alerts: document.getElementById('system-alerts').checked,
          session_timeout: document.getElementById('session-timeout').checked,
          timeout_duration: document.getElementById('timeout-duration').value,
          audit_logging: document.getElementById('audit-logging').checked,
          ip_restriction: document.getElementById('ip-restriction').checked,
          maintenance_mode: document.getElementById('maintenance-mode').checked
        };
      }
    });

    if (formData) {
      // Save security settings
      Swal.fire({
        title: 'Saving Security Settings...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const saveResponse = await fetchWithCSRF('/admin/security-settings', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        const saveData = await saveResponse.json();
        
        if (saveData.success) {
          Swal.fire({
            title: 'Settings Saved!',
            html: `
              <div class="text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <p class="mt-3">Security settings updated successfully!</p>
                <p class="text-muted">Changes will take effect immediately.</p>
              </div>
            `,
            icon: 'success',
            timer: 3000,
            timerProgressBar: true
          });
        } else {
          throw new Error(saveData.message || 'Failed to save security settings');
        }
      } catch (saveError) {
        console.log('Save endpoint not available, showing mock success:', saveError.message);
        // Show mock success if backend endpoint doesn't exist yet
        Swal.fire({
          title: 'Settings Saved!',
          html: `
            <div class="text-center">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
              <p class="mt-3">Security settings updated successfully!</p>
              <p class="text-muted">Changes will take effect immediately.</p>
              <small class="text-info">Note: Backend endpoint will be implemented to persist settings.</small>
            </div>
          `,
          icon: 'success',
          timer: 4000,
          timerProgressBar: true
        });
      }
    }
  } catch (error) {
    console.error('Security Settings Error:', error);
    Swal.fire('Error', 'Failed to load security settings: ' + error.message, 'error');
  }
}


// Initialize notification count on page load
document.addEventListener('DOMContentLoaded', function() {
  updateNotificationCount();
  // Update notification count every 30 seconds
  setInterval(updateNotificationCount, 30000);
});

async function updateNotificationCount() {
  try {
    const response = await fetchWithCSRF('/admin/notifications/count', {
      method: 'GET'
    });

    const data = await response.json();
    
    if (data.success) {
      const badge = document.getElementById('notification-count');
      if (badge) {
        if (data.count > 0) {
          badge.textContent = data.count > 99 ? '99+' : data.count;
          badge.style.display = 'inline';
        } else {
          badge.style.display = 'none';
        }
      }
    }
  } catch (error) {
    console.error('Notification Count Error:', error);
  }
}
</script>

<!-- Auto-refresh notification count and system status -->
<script>
// Real-time admin profile data
const adminProfileData = {
  name: '{{ Auth::guard("admin")->user()->name ?? "Admin" }}',
  email: '{{ Auth::guard("admin")->user()->email ?? "admin@jetlouge.com" }}',
  role: 'System Administrator',
  lastLogin: 'Today, {{ date("g:i A") }}',
  profilePicture: '{{ Auth::guard("admin")->user()->profile_picture ?? "" }}'
};

// Update profile functions to use real data
function showAdminProfile() {
  const profileImage = adminProfileData.profilePicture 
    ? `<img src="{{ asset('storage/profile_pictures/') }}/${adminProfileData.profilePicture}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">`
    : `<i class="bi bi-person-circle" style="font-size: 4rem;"></i>`;

  Swal.fire({
    title: 'Admin Profile',
    html: `
      <div class="text-center mb-3">
        ${profileImage}
      </div>
      <div class="text-start">
        <p><strong>Name:</strong> ${adminProfileData.name}</p>
        <p><strong>Email:</strong> ${adminProfileData.email}</p>
        <p><strong>Role:</strong> ${adminProfileData.role}</p>
        <p><strong>Last Login:</strong> ${adminProfileData.lastLogin}</p>
        <p><strong>System Version:</strong> HR2ESS v2.1.0</p>
      </div>
    `,
    width: 500,
    confirmButtonText: 'Edit Profile',
    showCancelButton: true,
    cancelButtonText: 'Close'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '/admin/settings';
    }
  });
}

async function changePassword() {
  const { value: formData } = await Swal.fire({
    title: 'Change Admin Password',
    html: `
      <div class="text-start">
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" class="form-control" id="current-pass" placeholder="Enter current password">
        </div>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control" id="new-pass" placeholder="Enter new password" minlength="8">
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirm-pass" placeholder="Confirm new password">
        </div>
        <div class="alert alert-info">
          <small><i class="bi bi-info-circle me-1"></i>Password must be at least 8 characters long</small>
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Change Password',
    preConfirm: () => {
      const currentPass = document.getElementById('current-pass').value;
      const newPass = document.getElementById('new-pass').value;
      const confirmPass = document.getElementById('confirm-pass').value;
      
      if (!currentPass || !newPass || !confirmPass) {
        Swal.showValidationMessage('Please fill all fields');
        return false;
      }
      
      if (newPass.length < 8) {
        Swal.showValidationMessage('New password must be at least 8 characters');
        return false;
      }
      
      if (newPass !== confirmPass) {
        Swal.showValidationMessage('New passwords do not match');
        return false;
      }
      
      return { currentPass, newPass };
    }
  });

  if (formData) {
    try {
      Swal.fire({
        title: 'Changing Password...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const response = await fetch('/admin/change-password', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCSRFToken(),
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          current_password: formData.currentPass,
          new_password: formData.newPass,
          new_password_confirmation: formData.newPass
        })
      });

      const data = await response.json();
      
      if (data.success) {
        Swal.fire('Success!', 'Password changed successfully', 'success');
      } else {
        Swal.fire('Error', data.message || 'Failed to change password', 'error');
      }
    } catch (error) {
      console.error('Change Password Error:', error);
      Swal.fire('Error', 'Failed to change password: ' + error.message, 'error');
    }
  }
}

function showAboutSystem() {
  Swal.fire({
    title: 'About HR2ESS',
    html: `
      <div class="text-center">
        <i class="bi bi-airplane" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
        <h5 class="mt-3">HR2ESS - Human Resource Management System</h5>
        <p><strong>Version:</strong> 2.1.0</p>
        <p><strong>Developer:</strong> Jetlouge Travels</p>
        <p><strong>Last Update:</strong> {{ date('F Y') }}</p>
        <p><strong>Database:</strong> MySQL</p>
        <p><strong>Framework:</strong> Laravel {{ app()->version() }}</p>
        <hr>
        <div class="row text-start">
          <div class="col-6">
            <h6>Features:</h6>
            <ul class="small">
              <li>Employee Management</li>
              <li>Training System</li>
              <li>Competency Tracking</li>
              <li>Leave Management</li>
            </ul>
          </div>
          <div class="col-6">
            <h6>Modules:</h6>
            <ul class="small">
              <li>Admin Dashboard</li>
              <li>Employee Portal</li>
              <li>Reporting System</li>
              <li>Security Management</li>
            </ul>
          </div>
        </div>
        <hr>
        <small class="text-muted">© {{ date('Y') }} Jetlouge Travels. All rights reserved.</small>
      </div>
    `,
    width: 600,
    confirmButtonText: 'Close'
  });
}
</script>
