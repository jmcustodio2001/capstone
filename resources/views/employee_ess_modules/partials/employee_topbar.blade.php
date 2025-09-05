<!-- Employee Topbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--jetlouge-primary);" aria-label="Main Navigation">
  <div class="container-fluid">
    <button class="sidebar-toggle desktop-toggle me-3" id="desktop-toggle" title="Toggle Sidebar">
      <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand fw-bold" href="#">
      <i class="bi bi-person-badge me-2"></i>Jetlouge Employee Portal
    </a>
    <div class="d-flex align-items-center">
      <div class="dropdown me-3">
        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-bell"></i>
          <span class="badge bg-danger ms-1">3</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header">Notifications</h6></li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="bi bi-file-earmark-text me-2"></i> New Payslip Available
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="bi bi-calendar-event me-2"></i> Upcoming Leave Request
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="bi bi-check-circle me-2"></i> Attendance Approved
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="bi bi-exclamation-circle me-2"></i> Policy Update Notice
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="bi bi-gear me-2"></i> Settings
            </a>
          </li>
        </ul>        
      </div>
      <form method="POST" action="{{ route('employee.logout') }}" class="ms-2">
        @csrf
        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
      </form>
      <button class="sidebar-toggle mobile-toggle" id="menu-btn" title="Open Menu">
        <i class="bi bi-list fs-5"></i>
      </button>
    </div>
  </div>
</nav>
<script>
  document.addEventListener('DOMContentLoaded', function() {
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
