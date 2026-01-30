<!-- Responsive Admin Sidebar -->
<aside id="sidebar" class="bg-white border-end p-3 shadow-sm">
  <!-- Profile Section -->
  <div class="profile-section text-center">
    @if(Auth::guard('admin')->check() && Auth::guard('admin')->user()->profile_picture)
      <img src="{{ asset('storage/profile_pictures/' . Auth::guard('admin')->user()->profile_picture) }}"
           alt="Admin Profile" class="profile-img mb-2"
           onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('admin')->user()->name ?? 'Admin') }}&size=150&background=007bff&color=ffffff&bold=true&rounded=true'">
    @else
      @php
        $adminName = Auth::guard('admin')->check() ? (Auth::guard('admin')->user()->name ?? 'Admin') : 'Admin';
        $adminAvatar = "https://ui-avatars.com/api/?name=" . urlencode($adminName) . "&size=150&background=007bff&color=ffffff&bold=true&rounded=true";
      @endphp
      <img src="{{ $adminAvatar }}"
           alt="Admin Profile" class="profile-img mb-2">
    @endif
    <h6 class="fw-semibold mb-1">
      @if(Auth::guard('admin')->check())
        {{ Auth::guard('admin')->user()->name }}
      @else
        Admin
      @endif
    </h6>
    <small class="text-muted">Jetlouge Travels Admin</small>
  </div>

  <!-- Navigation Menu -->
  <ul class="nav flex-column">

    <!-- Dashboard/Home -->
    <li class="nav-item">
      <a href="{{ route('admin.dashboard') }}" class="nav-link text-primary d-flex align-items-center{{ request()->routeIs('admin.dashboard') ? ' active fw-bold bg-light border-start border-primary' : '' }}">
        <i class="bi bi-house-door me-2"></i>
        <span>Home</span>
      </a>
    </li>

    <!-- Competency Management -->
    <li class="nav-item">
      <a href="#competencySubmenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="competencySubmenu" class="nav-link text-dark d-flex align-items-center">
        <i class="bi bi-bar-chart-line me-2"></i>
        <span>Competency Management</span>
      </a>
      <ul class="collapse list-unstyled ps-4" id="competencySubmenu">
        <li class="nav-item">
          <a href="{{ route('admin.competency_library.index') }}" class="nav-link text-dark">
            <i class="bi bi-collection me-2"></i> Competency Library
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('employee_competency_profiles.index') }}" class="nav-link text-dark">
            <i class="bi bi-person-badge me-2"></i> Employee Competency Profile
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('competency_gap_analysis.index') }}" class="nav-link text-dark">
            <i class="bi bi-graph-up-arrow me-2"></i> Competency Gap/Analysis
          </a>
        </li>
      </ul>
    </li>

    <!-- Learning Management -->
    <li class="nav-item">
      <a href="#learningSubmenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="learningSubmenu" class="nav-link text-dark d-flex align-items-center">
        <i class="bi bi-book me-2"></i>
        <span>Learning Management</span>
      </a>
      <ul class="collapse list-unstyled ps-4" id="learningSubmenu">
        <li class="nav-item">
          <a href="{{ route('admin.course_management.index') }}" class="nav-link text-dark">
            <i class="bi bi-journal-text me-2"></i> Course Management
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.employee_trainings_dashboard.index') }}" class="nav-link text-dark">
            <i class="bi bi-speedometer2 me-2"></i> Employee Training Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('training_record_certificate_tracking.index') }}" class="nav-link text-dark">
            <i class="bi bi-award me-2"></i> Training Record & Certificate Tracking
          </a>
        </li>
      </ul>
    </li>

    <!-- Training Management -->
    <li class="nav-item">
  <a href="#trainingSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('customer_service_sales_skills_training.*') ? 'true' : 'false' }}" aria-controls="trainingSubmenu" class="nav-link text-dark d-flex align-items-center">
        <i class="bi bi-easel me-2"></i>
        <span>Training Management</span>
      </a>
  <ul class="collapse list-unstyled ps-4{{ request()->routeIs('customer_service_sales_skills_training.*') ? ' show' : '' }}" id="trainingSubmenu">
        <li class="nav-item">
          <a href="{{ route('admin.destination-knowledge-training.index') }}" class="nav-link text-dark">
            <i class="bi bi-geo-alt me-2"></i> Destination Knowledge Training
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('customer_service_sales_skills_training.index') }}"
             class="nav-link{{ request()->routeIs('customer_service_sales_skills_training.*') ? ' active fw-bold bg-light border-start border-primary' : ' text-dark' }}" style="color: black !important;">
            <i class="bi bi-people me-2"></i> Customer Service & Sales Skills Training
          </a>
        </li>
      </ul>
    </li>

    <!-- Employee Self-Service -->
    <li class="nav-item">
      <a href="#essSubmenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="essSubmenu" class="nav-link text-dark d-flex align-items-center">
        <i class="bi bi-person-badge me-2"></i>
        <span>Employee Self-Service</span>
      </a>
      <ul class="collapse list-unstyled ps-4" id="essSubmenu">
        <li class="nav-item">
          <a href="{{ route('profile_update_of_employees.index') }}" class="nav-link text-dark">
            <i class="bi bi-pencil-square me-2"></i> Profile Update of Employee
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('employee_request_forms.index') }}" class="nav-link text-dark">
            <i class="bi bi-file-earmark-text me-2"></i> Request Forms of Employee
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('employee.list') }}" class="nav-link text-dark">
            <i class="bi bi-people me-2"></i> Employee List
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.training_feedback.index') }}" class="nav-link{{ request()->routeIs('admin.training_feedback.*') ? ' active fw-bold bg-light border-start border-primary' : ' text-dark' }}">
            <i class="bi bi-chat-dots me-2"></i> Employee Feedback
          </a>
        </li>
      </ul>
    </li>

    <!-- Succession Planning -->
    <li class="nav-item">
      <a href="#successionSubmenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="successionSubmenu" class="nav-link text-dark d-flex align-items-center">
        <i class="bi bi-diagram-3 me-2"></i>
        <span>Succession Planning</span>
      </a>
      <ul class="collapse list-unstyled ps-4" id="successionSubmenu">
        <li class="nav-item">
          <a href="{{ route('potential_successors.index') }}" class="nav-link text-dark">
            <i class="bi bi-person-plus me-2"></i> Potential Successor Identification
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('succession_readiness_ratings.index') }}" class="nav-link text-dark">
            <i class="bi bi-bar-chart me-2"></i> Succession Readiness Rating
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route ('succession_simulations.index') }}" class="nav-link text-dark">
            <i class="bi bi-grid-3x3-gap me-2"></i> Succession Planning Dashboard / Simulation Tools
          </a>
        </li>
      </ul>
    </li>
    <!-- Settings -->

    <!-- Reports -->
    <li class="nav-item">
      <a href="{{ route('admin.reports') }}" class="nav-link text-dark d-flex align-items-center{{ request()->routeIs('admin.reports*') ? ' active fw-bold bg-light border-start border-primary' : '' }}">
        <i class="bi bi-file-earmark-bar-graph me-2"></i>
        <span>Reports Data</span>
      </a>
    </li>

    <li class="nav-item mt-3">
  <a href="{{ route('admin.settings') }}" class="nav-link text-primary w-100 text-start">
    <i class="bi bi-gear me-2"></i> Settings
  </a>
</li>


    <!-- Logout -->
    <li class="nav-item mt-3">
  <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start">
          <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
      </form>
    </li>

  </ul>
</aside>
