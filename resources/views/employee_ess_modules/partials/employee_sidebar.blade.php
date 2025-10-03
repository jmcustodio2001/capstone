<!-- Responsive Employee Sidebar -->
<aside id="sidebar" class="bg-white border-end shadow-sm">
  <!-- Profile Section -->
  <div class="profile-section text-center p-3">
    @php
      // Use the 'employee' guard if available, fallback to default
      $employee = null;
      if (Auth::guard('employee')->check()) {
          $employee = Auth::guard('employee')->user();
      } elseif (Auth::check()) {
          $employee = Auth::user();
      }
      // Auto-generate profile picture for any employee
      $profilePicUrl = null;
      $initials = '??';
      $colors = [
        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe',
        '#43e97b', '#38f9d7', '#ffecd2', '#fcb69f', '#a8edea', '#fed6e3'
      ];

      if ($employee) {
        // Generate initials
        $firstName = $employee->first_name ?? '';
        $lastName = $employee->last_name ?? '';
        $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));

        // Check if profile picture exists
        if ($employee->profile_picture) {
          // The profile_picture field contains: profile_pictures/filename.jpg
          // This should be in storage/app/public/profile_pictures/filename.jpg
          $profilePicPath = 'app/public/' . $employee->profile_picture;

          if (file_exists(storage_path($profilePicPath))) {
            // File exists, create the public URL
            $profilePicUrl = asset('storage/' . $employee->profile_picture);
          } else {
            // File doesn't exist, but try the URL anyway (might be a symlink issue)
            $profilePicUrl = asset('storage/' . $employee->profile_picture);
          }
        }

        // Generate consistent color based on employee name
        $colorIndex = (ord($firstName[0] ?? 'A') + ord($lastName[0] ?? 'A')) % count($colors);
        $bgColor = $colors[$colorIndex];
      }
    @endphp


    @if($profilePicUrl)
      <img src="{{ $profilePicUrl }}"
           alt="Employee Profile" class="profile-img mb-2 mx-auto"
           style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    @else
      <div class="profile-img mb-2 mx-auto d-flex align-items-center justify-content-center"
           style="width: 60px; height: 60px; border-radius: 50%; background: {{ $bgColor ?? '#667eea' }}; color: white; font-weight: bold; font-size: 18px; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        {{ $initials }}
      </div>
    @endif

    <div class="fw-bold" style="font-size: 1.1rem;">{{ ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '') ?: 'Nica A. Casamingo' }}</div>
    <div class="text-secondary" style="font-size: 0.95rem;">{{ $employee->position ?? 'Software Developer' }}</div>
    <span class="badge bg-info text-white mt-2" style="font-size: 0.85rem;">Active</span>
    <div class="text-muted small mt-1">Employee ID: {{ $employee->employee_id ?? 'EMP001' }}</div>
  </div>

  <!-- Navigation Menu -->
  <div class="p-3 pt-0">
    <ul class="nav flex-column">

      <!-- Dashboard -->
    <li class="nav-item">
      <a href="{{ route('employee.dashboard') }}" class="nav-link{{ request()->routeIs('employee.dashboard') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
      </a>
    </li>

  <!-- Leave Application & Balance -->
  <li class="nav-item">
    <a href="{{ route('employee.leave_applications.index') }}" class="nav-link{{ request()->routeIs('employee.leave_applications.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
      <i class="bi bi-calendar-event me-2"></i>
      Leave Application & Balance
    </a>
  </li>

  <!-- Attendance & Time Logs -->
  <li class="nav-item">
    <a href="{{ route('employee.attendance_logs.index') }}" class="nav-link{{ request()->routeIs('employee.attendance_logs.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
      <i class="bi bi-clock-history me-2"></i>
      Attendance & Time Logs
    </a>
  </li>


    <!-- Payslip Access -->
    <li class="nav-item">
      <a href="{{ route('employee.payslips.index') }}" class="nav-link{{ request()->routeIs('employee.payslips.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-receipt me-2"></i>
        Payslip Access
      </a>
    </li>

    <li class="nav-item">
      <a href="{{ route('employee.claim_reimbursements.index') }}"
         class="nav-link{{ request()->routeIs('employee.claim_reimbursements.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
          <i class="bi bi-cash-stack me-2"></i>
          Claim & Reimbursement
      </a>
  </li>

    <!-- My Trainings -->
    <li class="nav-item">
      <a href="{{ route('employee.my_trainings.index') }}" class="nav-link{{ request()->routeIs('employee.my_trainings.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-journal-text me-2"></i>
        My Trainings
      </a>
    </li>

    <!-- Competency Profile -->
    <li class="nav-item">
      <a href="{{ route('employee.competency_profile.index') }}" class="nav-link{{ request()->routeIs('employee.competency_profile.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-graph-up-arrow me-2"></i>
        Competency Profile
      </a>
    </li>

        <!-- Request Forms -->
        <li class="nav-item">
          <a href="{{ route('employee.requests.index') }}" class="nav-link{{ request()->routeIs('employee.requests.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
            <i class="bi bi-file-earmark-text me-2"></i>
            Request Forms
          </a>
        </li>

    <!-- Profile Updates -->
    <li class="nav-item">
      <a href="{{ route('employee.profile_updates.index') }}" class="nav-link{{ request()->routeIs('employee.profile_updates.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-person-gear me-2"></i>
        Profile Updates
      </a>
    </li>

        <!-- Settings -->
<li class="nav-item">
  <a href="{{ route('employee.settings') }}" class="nav-link{{ request()->routeIs('employee.settings') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
    <i class="bi bi-gear me-2"></i>
    Settings
  </a>
</li>

    <!-- Logout -->
    <li class="nav-item mt-3">
      <form action="{{ route('employee.logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center">
          <i class="bi bi-box-arrow-right me-2"></i>
          <span>Logout</span>
        </button>
      </form>
    </li>

    </ul>
  </div>
</aside>
