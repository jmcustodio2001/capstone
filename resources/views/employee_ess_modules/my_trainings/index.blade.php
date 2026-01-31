  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- IMMEDIATE translation service initialization - MUST be first -->
  <script>
    (function(){window.translationService=window.translationService||{translate:function(k){return k},get:function(k){return k},trans:function(k){return k},choice:function(k){return k},setLocale:function(l){return l},getLocale:function(){return'en'},has:function(){return true},translations:{},setTranslations:function(t){this.translations=t||{}}};window.trans=window.translationService.translate;window.__=window.translationService.translate;window.app=window.app||{locale:'en',fallback_locale:'en',translationService:window.translationService};window.Laravel=window.Laravel||{};window.Laravel.translationService=window.translationService;if(typeof global!=='undefined'){global.translationService=window.translationService}})();
  </script>

  <title>Employee Trainings</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">

  <!-- Load translation service FIRST to prevent undefined errors -->
  <script src="{{ asset('js/translation-service-init.js') }}"></script>

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
      font-size: 0.75rem;
      padding: 0.5em 1em;
      font-weight: 600;
      border-radius: 50rem !important; /* Pill style */
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      letter-spacing: 0.02em;
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
                    <h5 class="text-primary mb-1">{{ collect($upcoming)->filter(function($item) { $upcomingId = is_array($item) ? ($item['upcoming_id'] ?? '') : ($item->upcoming_id ?? ''); return !str_starts_with((string)$upcomingId, 'TR'); })->count() }}</h5>
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
          @php
            // Count trainings that have progress (both approved requests AND competency gap trainings)
            $currentEmployeeId = $employeeId;

            // Filter to include ALL training progress sources with actual progress
            $trainingProgressItems = collect($progress)->filter(function ($item) use ($currentEmployeeId) {
                // Include items with progress > 0 from any source
                return ($item->employee_id ?? $currentEmployeeId) == $currentEmployeeId &&
                       isset($item->source) &&
                       $item->source !== null;
            });

            // Group by training title to eliminate any remaining duplicates
            $groupedProgress = $trainingProgressItems->groupBy(function ($item) {
                $trainingTitle = strtolower(trim($item->training_title ?? ''));

                // Normalize training title
                $normalizedTitle = preg_replace('/\s+/', ' ', $trainingTitle);
                $normalizedTitle = str_replace([' training', ' course', ' program', ' skills'], '', $normalizedTitle);
                $normalizedTitle = trim($normalizedTitle);

                return $normalizedTitle;
            });

            // Count unique trainings
            $actualProgressCount = $groupedProgress->count();
          @endphp
          <h5 class="text-info mb-1">{{ $actualProgressCount }}</h5>
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
// Enhanced Translation Service Provider with error handling
(function() {
  try {
    const translationService = {
        translations: {},
        translate(key, params = {}) {
            try {
                let text = this.translations[key] || key;
                Object.keys(params).forEach(param => {
                    text = text.replace(`:${param}`, params[param]);
                });
                return text;
            } catch (error) {
                console.warn('Translation error for key:', key, error);
                return key;
            }
        },
        setTranslations(translations) {
            this.translations = translations || {};
        },
        get: function(key, params) { return this.translate(key, params); },
        trans: function(key, params) { return this.translate(key, params); },
        choice: function(key, count, params) { return this.translate(key, params); },
        setLocale: function(locale) { return locale; },
        getLocale: function() { return 'en'; },
        has: function(key) { return true; }
    };

    window.translationService = translationService;

    // Global error handler for this page
    window.addEventListener('error', function(event) {
      if (event.error && event.error.message && event.error.message.includes('translationService')) {
        console.error('Translation service error caught and handled:', event.error);
        return true; // Prevent error from breaking the page
      }
    });

    console.log('My-trainings translation service initialized successfully');
  } catch (error) {
    console.error('Error initializing translation service:', error);
    // Minimal fallback
    window.translationService = {
      translate: function(key) { return key; }
    };
  }
})();
</script>
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

        // Store the current tab in sessionStorage to persist across refreshes
        sessionStorage.setItem('currentTrainingTab', targetId);

        // Disable automatic refresh when switching to progress tab
        // This prevents unwanted page reloads and tab switching
        // Users can manually refresh if needed
        if (targetId === 'progress') {
            console.log('Switched to progress tab - no automatic refresh');
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

    // Determine which tab to show
    let targetTab = null;

    if (tabParam && ['upcoming', 'completed', 'requests', 'progress', 'feedback', 'notifications'].includes(tabParam)) {
        // Show the tab specified in URL parameter
        targetTab = tabParam;
    } else {
        // Check if user was on a specific tab before (persist across refreshes)
        const savedTab = sessionStorage.getItem('currentTrainingTab');
        if (savedTab && ['upcoming', 'completed', 'requests', 'progress', 'feedback', 'notifications'].includes(savedTab)) {
            targetTab = savedTab;
        } else {
            // Show the first section by default only if no saved tab
            if (links.length > 0) {
                targetTab = links[0].getAttribute('data-target');
            }
        }
    }

    if (targetTab) {
        showSection(targetTab);
    }

    // Auto-refresh only on initial page load (reduced frequency)
    setTimeout(function() {
        // Only auto-create requests if explicitly needed
        if (urlParams.get('auto_create') === 'true') {
            autoCreateRequestsFromUpcoming();
        }
        // Only refresh if explicitly requested via URL parameter
        if (refreshParam) {
            refreshTrainingProgress();
        }
    }, 1000);

    // Remove aggressive auto-refresh - only refresh when user switches to progress tab
    // This prevents constant refreshing that causes UI lag
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
            // Update UI elements instead of full page reload to prevent refresh loop
            if (data.updated_count > 0) {
                console.log(`Updated ${data.updated_count} progress records`);
                // Refresh only the progress section content instead of full page
                updateProgressSection(data);
            }
        } else {
            console.error('Failed to refresh training progress:', data.message);
        }
    })
    .catch(error => {
        console.error('Error refreshing training progress:', error);
    });
}

// Function to refresh training data and update counts
function refreshTrainingData() {
    fetch('/employee/my-trainings/refresh-data', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Training data refreshed:', data);
            // Update the counts in the dashboard
            updateDashboardCounts(data.counts);
        } else {
            console.error('Failed to refresh training data:', data.message);
        }
    })
    .catch(error => {
        console.error('Error refreshing training data:', error);
    });
}

// Function to update dashboard counts
function updateDashboardCounts(counts) {
    // Update the count displays
    const upcomingCard = document.querySelector('.col-md-2:nth-child(1) h5');
    const completedCard = document.querySelector('.col-md-2:nth-child(2) h5');
    const requestsCard = document.querySelector('.col-md-2:nth-child(3) h5');
    const progressCard = document.querySelector('.col-md-2:nth-child(4) h5');
    const feedbackCard = document.querySelector('.col-md-2:nth-child(5) h5');
    const notificationsCard = document.querySelector('.col-md-2:nth-child(6) h5');

    if (upcomingCard) upcomingCard.textContent = counts.upcoming;
    if (completedCard) completedCard.textContent = counts.completed;
    if (requestsCard) requestsCard.textContent = counts.requests;
    if (progressCard) progressCard.textContent = counts.progress;
    if (feedbackCard) feedbackCard.textContent = counts.feedback;
    if (notificationsCard) notificationsCard.textContent = counts.notifications;
}

// Function to auto-create requests from upcoming trainings
function autoCreateRequestsFromUpcoming() {
    fetch('/employee/my-trainings/auto-create-requests', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Auto-created training requests:', data);
            if (data.created_count > 0) {
                console.log(`Successfully auto-created ${data.created_count} training requests`);
                // Refresh data after creation
                setTimeout(() => {
                    refreshTrainingData();
                }, 500);
            }
        } else {
            console.error('Failed to auto-create training requests:', data.message);
        }
    })
    .catch(error => {
        console.error('Error auto-creating training requests:', error);
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
