<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--jetlouge-primary);" aria-label="Main navigation">
  <div class="container-fluid">
    <button class="sidebar-toggle desktop-toggle me-3" id="desktop-toggle" title="Toggle Sidebar">
      <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
      <i class="bi bi-airplane me-2"></i>Jetlouge Travels
    </a>
    <div class="d-flex align-items-center">
      <button class="sidebar-toggle mobile-toggle" id="menu-btn" title="Open Menu">
        <i class="bi bi-list fs-5"></i>
      </button>
    </div>
  </div>
</nav>

<!-- Sidebar toggle functionality for all modules -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing sidebar toggle...');
    
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
