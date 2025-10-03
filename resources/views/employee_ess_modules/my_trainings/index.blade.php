<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Employee Trainings</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    .simulation-card {
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      border: none;
    }
    .card-header-custom {
      background-color: #f8f9fa;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
    }
    .breadcrumb-container {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 12px 20px;
      margin-bottom: 15px;
    }
    .breadcrumb-link {
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .breadcrumb-link.active {
      font-weight: bold;
      color: #0d6efd !important;
    }
    .breadcrumb-link:hover {
      color: #0d6efd !important;
      text-decoration: underline !important;
    }
    .training-section {
      animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .alert {
      border-radius: 8px;
      border: none;
    }
    .btn {
      border-radius: 6px;
    }
    .table th {
      font-weight: 600;
      color: #495057;
    }
    .badge {
      font-size: 0.75em;
      padding: 0.35em 0.65em;
    }
    .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .modal-header {
      border-bottom: 1px solid #eee;
      padding: 1.5rem;
    }
    .modal-body {
      padding: 1.5rem;
    }
    .modal-footer {
      border-top: 1px solid #eee;
      padding: 1rem 1.5rem;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<main id="main-content">

  <!-- Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">My Trainings</h2>
          <p class="text-muted mb-0">Manage your training programs and progress.</p>
        </div>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">My Trainings</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Training Statistics Summary -->
  <div class="row mb-4">
    <div class="col-md-2">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body py-3">
          <h5 class="text-primary mb-1">{{ $upcoming->count() }}</h5>
          <small class="text-muted">Upcoming</small>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body py-3">
          <h5 class="text-success mb-1">{{ $completed->count() }}</h5>
          <small class="text-muted">Completed</small>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body py-3">
          <h5 class="text-warning mb-1">{{ $trainingRequests->count() }}</h5>
          <small class="text-muted">Requests</small>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body py-3">
          <h5 class="text-info mb-1">{{ $progress->count() }}</h5>
          <small class="text-muted">In Progress</small>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body py-3">
          <h5 class="text-secondary mb-1">{{ $feedback->count() }}</h5>
          <small class="text-muted">Feedback</small>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body py-3">
          <h5 class="text-dark mb-1">{{ $notifications->count() }}</h5>
          <small class="text-muted">Notifications</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation Breadcrumb -->
  <div class="breadcrumb-container mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link text-decoration-none active" data-target="upcoming">Upcoming Trainings</a></li>
        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link text-decoration-none" data-target="completed">Completed Trainings</a></li>
        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link text-decoration-none" data-target="requests">Request Training</a></li>
        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link text-decoration-none" data-target="progress">Training Progress</a></li>
        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link text-decoration-none" data-target="feedback">Feedback Form</a></li>
        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link text-decoration-none" data-target="notifications">Training Notifications</a></li>
      </ol>
    </nav>
  </div>

  <!-- Sections -->
  <div class="training-section" id="upcoming-section">
    @include('employee_ess_modules.my_trainings._upcoming')
  </div>

  <div class="training-section" id="completed-section" style="display:none;">
    @include('employee_ess_modules.my_trainings._completed')
  </div>

  <div class="training-section" id="requests-section" style="display:none;">
    @include('employee_ess_modules.my_trainings._requests')
  </div>

  <div class="training-section" id="progress-section" style="display:none;">
    @include('employee_ess_modules.my_trainings._progress')
  </div>

  <div class="training-section" id="feedback-section" style="display:none;">
    @include('employee_ess_modules.my_trainings._feedback')
  </div>

  <div class="training-section" id="notifications-section" style="display:none;">
    @include('employee_ess_modules.my_trainings._notifications')
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.breadcrumb-link');
    const sections = document.querySelectorAll('.training-section');

    // Function to show a specific section and hide others
    function showSection(targetId) {
        sections.forEach(section => {
            section.style.display = (section.id === targetId + '-section') ? 'block' : 'none';
        });

        // Update active state of breadcrumb links
        links.forEach(link => {
            if (link.getAttribute('data-target') === targetId) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Refresh progress data when progress section is shown
        if (targetId === 'progress') {
            refreshTrainingProgress();
        }
    }

    // Add click event listeners to breadcrumb links
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-target');
            showSection(target);
        });
    });

    // Check URL parameters for tab selection
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const refreshParam = urlParams.get('refresh');

    // If refresh parameter is present, refresh progress data
    if (refreshParam) {
        refreshTrainingProgress();
    }

    if (tabParam && ['upcoming', 'completed', 'requests', 'progress', 'feedback', 'notifications'].includes(tabParam)) {
        // Show the tab specified in URL parameter
        showSection(tabParam);
    } else {
        // Show the first section by default
        if (links.length > 0) {
            const firstTarget = links[0].getAttribute('data-target');
            showSection(firstTarget);
        }
    }

    // Auto-refresh progress data on page load
    setTimeout(function() {
        refreshTrainingProgress();
    }, 500);

    // Force refresh every 10 seconds for real-time updates
    setInterval(function() {
        if (document.getElementById('progress-section').style.display !== 'none') {
            refreshTrainingProgress();
        }
    }, 10000);
});

// Function to refresh training progress data
function refreshTrainingProgress() {
    fetch('/employee/training/refresh-progress', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Training progress refreshed:', data);
            // Reload the page to show updated progress if any records were updated
            if (data.updated_count > 0) {
                setTimeout(function() {
                    window.location.reload();
                }, 500);
            }
        } else {
            console.error('Failed to refresh training progress:', data.message);
        }
    })
    .catch(error => {
        console.error('Error refreshing training progress:', error);
    });
}

// Function to manually refresh progress (can be called by buttons)
function manualRefreshProgress() {
    const refreshBtn = document.getElementById('refresh-progress-btn');
    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';
        refreshBtn.disabled = true;
    }

    refreshTrainingProgress();

    setTimeout(function() {
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Progress';
            refreshBtn.disabled = false;
        }
    }, 2000);
}
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

</body>
</html>
