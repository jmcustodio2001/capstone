<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Employee Portal - Jetlouge Travels HR System')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Employee Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('employee.dashboard') }}">
                <i class="fas fa-user-tie"></i> Employee Portal
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employee.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employee.my_trainings.index') }}">
                            <i class="fas fa-graduation-cap"></i> My Trainings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employee.competency_profile.index') }}">
                            <i class="fas fa-chart-line"></i> Competency Profile
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> {{ Auth::user()->first_name ?? 'Employee' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('employee.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Global Session Management - NEVER EXPIRE -->
    <script>
    // Global session management for ALL employee pages
    let globalTokenRefreshInterval;
    let globalSessionKeepAliveInterval;
    let globalHeartbeatInterval;

    function globalRefreshCSRFToken() {
        return fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.csrf_token) {
                // Update all CSRF tokens on page
                const tokenInputs = document.querySelectorAll('input[name="_token"]');
                tokenInputs.forEach(input => input.value = data.csrf_token);

                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken) {
                    metaToken.setAttribute('content', data.csrf_token);
                }

                console.log('Global CSRF token refreshed at', new Date().toLocaleTimeString());
                return data.csrf_token;
            } else {
                throw new Error('No CSRF token in response');
            }
        })
        .catch(error => {
            console.error('Global CSRF token refresh failed:', error);
            // Retry after 10 seconds if failed
            setTimeout(globalRefreshCSRFToken, 10000);
            throw error;
        });
    }

    function globalKeepSessionAlive() {
        fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.ok) {
                console.log('Global session kept alive at', new Date().toLocaleTimeString());
            }
        })
        .catch(error => {
            console.log('Global session keep-alive failed:', error);
            setTimeout(globalKeepSessionAlive, 30000);
        });
    }

    function globalSendHeartbeat() {
        fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).catch(error => console.log('Global heartbeat failed:', error));
    }

    // Start global session management on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initial token refresh
        globalRefreshCSRFToken().catch(error => console.error('Initial global token refresh failed:', error));

        // Refresh CSRF token every 1 minute (very aggressive)
        globalTokenRefreshInterval = setInterval(globalRefreshCSRFToken, 60000);

        // Keep session alive every 2 minutes
        globalSessionKeepAliveInterval = setInterval(globalKeepSessionAlive, 120000);

        // Send heartbeat every 30 seconds to prevent any timeout
        globalHeartbeatInterval = setInterval(globalSendHeartbeat, 30000);
    });

    // Clean up intervals when page unloads
    window.addEventListener('beforeunload', function() {
        if (globalTokenRefreshInterval) clearInterval(globalTokenRefreshInterval);
        if (globalSessionKeepAliveInterval) clearInterval(globalSessionKeepAliveInterval);
        if (globalHeartbeatInterval) clearInterval(globalHeartbeatInterval);
    });
    </script>

    @stack('scripts')
</body>
</html>
