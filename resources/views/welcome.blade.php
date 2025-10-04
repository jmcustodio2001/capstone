<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JETLOUGE TRAVEL AND TOURS : HUMAN RESOURCES 2</title>
    <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0a3d62, #1e5799, #3a7bd5);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        .airplane {
            position: absolute;
            font-size: 2rem;
            top: 15%;
            left: -50px;
            animation: fly 15s linear infinite;
            opacity: 0.7;
        }

        @keyframes fly {
            0% { left: -50px; transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { left: 100%; transform: translateY(0) rotate(0deg); }
        }

        .cloud {
            position: absolute;
            font-size: 3rem;
            opacity: 0.3;
            z-index: -1;
        }

        .cloud:nth-child(1) {
            top: 20%;
            left: 10%;
            animation: drift 25s linear infinite;
        }

        .cloud:nth-child(2) {
            top: 60%;
            right: 15%;
            animation: drift 30s linear infinite reverse;
        }

        .cloud:nth-child(3) {
            bottom: 25%;
            left: 20%;
            animation: drift 35s linear infinite;
        }

        @keyframes drift {
            0% { transform: translateX(0); }
            50% { transform: translateX(50px); }
            100% { transform: translateX(0); }
        }

        .welcome-container {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            padding: 50px 70px;
            border-radius: 25px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1.5s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 90%;
            position: relative;
            z-index: 2;
        }

        .logo {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            font-size: 2.8rem;
            color: #ffb347;
        }

        .welcome-container h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-container p {
            font-size: 1.3rem;
            margin-bottom: 35px;
            max-width: 600px;
            line-height: 1.6;
        }

        .tagline {
            font-style: italic;
            margin-top: 10px;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .btn-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            color: #fff;
            text-decoration: none;
            padding: 16px 40px;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(to right, #feb47b, #ff7e5f);
        }

        .btn.admin {
            background: linear-gradient(to right, #1a2980, #26d0ce);
        }

        .btn.admin:hover {
            background: linear-gradient(to right, #26d0ce, #1a2980);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            padding: 0 20px;
        }

        /* Comprehensive Responsive Design */
        
        /* Mobile First - Base styles for mobile devices */
        @media (max-width: 480px) {
            body {
                padding: 10px;
                min-height: 100vh;
            }

            .welcome-container {
                padding: 20px 15px;
                margin: 10px;
                max-width: calc(100% - 20px);
                border-radius: 15px;
            }

            .logo-icon {
                font-size: 2rem;
            }

            .welcome-container h1 {
                font-size: 1.8rem;
                line-height: 1.2;
                margin-bottom: 10px;
            }

            .welcome-container p {
                font-size: 1rem;
                margin-bottom: 20px;
                line-height: 1.4;
            }

            .tagline {
                font-size: 0.9rem;
                margin-top: 5px;
            }

            .btn-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .btn {
                width: 100%;
                max-width: 280px;
                padding: 14px 20px;
                font-size: 1rem;
                justify-content: center;
                border-radius: 25px;
                /* Enhanced touch targets */
                min-height: 48px;
                touch-action: manipulation;
            }

            /* Hide decorative elements on very small screens */
            .passport-stamp,
            .luggage-tag,
            .boarding-pass {
                display: none;
            }

            .airplane {
                font-size: 1.5rem;
                animation-duration: 20s;
            }

            .cloud {
                font-size: 2rem;
                opacity: 0.2;
            }

            footer {
                font-size: 0.8rem;
                bottom: 10px;
                padding: 0 15px;
            }
        }

        /* Large Mobile / Small Tablet (481px - 768px) */
        @media (min-width: 481px) and (max-width: 768px) {
            body {
                padding: 15px;
            }

            .welcome-container {
                padding: 35px 25px;
                margin: 15px;
                max-width: calc(100% - 30px);
                border-radius: 20px;
            }

            .logo-icon {
                font-size: 2.4rem;
            }

            .welcome-container h1 {
                font-size: 2.2rem;
                margin-bottom: 12px;
            }

            .welcome-container p {
                font-size: 1.1rem;
                margin-bottom: 25px;
            }

            .tagline {
                font-size: 1rem;
            }

            .btn-container {
                flex-direction: column;
                align-items: center;
                gap: 18px;
            }

            .btn {
                width: 100%;
                max-width: 320px;
                padding: 15px 30px;
                font-size: 1.05rem;
                justify-content: center;
                min-height: 50px;
                touch-action: manipulation;
            }

            /* Show some decorative elements but smaller */
            .passport-stamp {
                width: 80px;
                height: 80px;
                top: -40px;
                right: -40px;
            }

            .passport-stamp i {
                font-size: 1.5rem;
            }

            .luggage-tag {
                bottom: -20px;
                left: 20px;
                font-size: 0.7rem;
                padding: 6px 12px;
            }

            .boarding-pass {
                display: none; /* Too cluttered on tablets */
            }

            footer {
                font-size: 0.85rem;
                bottom: 15px;
            }
        }

        /* Tablet Portrait (769px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .welcome-container {
                padding: 45px 40px;
                max-width: 85%;
            }

            .welcome-container h1 {
                font-size: 2.5rem;
            }

            .welcome-container p {
                font-size: 1.2rem;
                margin-bottom: 30px;
            }

            .btn-container {
                flex-direction: row;
                gap: 25px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                padding: 16px 35px;
                font-size: 1.1rem;
                min-width: 200px;
                min-height: 52px;
                touch-action: manipulation;
            }

            /* Show all decorative elements */
            .passport-stamp {
                width: 100px;
                height: 100px;
                top: -50px;
                right: -50px;
            }

            .passport-stamp i {
                font-size: 1.8rem;
            }

            .luggage-tag {
                bottom: -25px;
                left: 25px;
            }

            .boarding-pass {
                width: 180px;
                height: 90px;
                left: -90px;
            }
        }

        /* Tablet Landscape (1025px - 1200px) */
        @media (min-width: 1025px) and (max-width: 1200px) {
            .welcome-container {
                padding: 50px 60px;
                max-width: 80%;
            }

            .btn-container {
                flex-direction: row;
                gap: 30px;
            }

            .btn {
                min-width: 220px;
                padding: 16px 40px;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .btn {
                /* Larger touch targets for touch devices */
                min-height: 48px;
                padding: 16px 24px;
            }

            .btn:hover {
                /* Disable hover effects on touch devices */
                transform: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }

            .btn:active {
                /* Add active state for touch feedback */
                transform: scale(0.98);
                transition: transform 0.1s ease;
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .welcome-container {
                border: 0.5px solid rgba(255, 255, 255, 0.1);
            }
        }

        /* Landscape orientation adjustments */
        @media (orientation: landscape) and (max-height: 500px) {
            body {
                justify-content: flex-start;
                padding-top: 20px;
            }

            .welcome-container {
                margin-top: 20px;
                padding: 25px 35px;
            }

            .welcome-container h1 {
                font-size: 2rem;
                margin-bottom: 10px;
            }

            .welcome-container p {
                font-size: 1rem;
                margin-bottom: 20px;
            }

            .btn-container {
                flex-direction: row;
                gap: 20px;
            }

            .btn {
                padding: 12px 24px;
                font-size: 0.95rem;
            }

            footer {
                position: relative;
                margin-top: 20px;
            }

            /* Hide decorative elements in landscape */
            .airplane,
            .cloud,
            .passport-stamp,
            .luggage-tag,
            .boarding-pass {
                display: none;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            .airplane,
            .cloud {
                animation: none;
            }

            .welcome-container {
                animation: none;
            }

            .btn:hover {
                transform: none;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .boarding-pass {
                background: rgba(40, 40, 40, 0.9);
                color: #fff;
            }

            .luggage-tag {
                background: linear-gradient(45deg, #4a4a4a, #6a6a6a);
                color: #fff;
            }
        }

        /* NEW DESIGN ELEMENTS */
        .passport-stamp {
            position: absolute;
            width: 120px;
            height: 120px;
            border: 3px dashed rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            top: -60px;
            right: -60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(15deg);
            opacity: 0.8;
        }

        .passport-stamp i {
            font-size: 2rem;
            color: #ff6b6b;
        }

        .luggage-tag {
            position: absolute;
            bottom: -30px;
            left: 30px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: #333;
            transform: rotate(-5deg);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .boarding-pass {
            position: absolute;
            top: 50%;
            left: -100px;
            width: 200px;
            height: 100px;
            background: rgba(255, 255, 255, 0.9);
            transform: rotate(-15deg) translateY(-50%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0.7;
            z-index: 1;
        }

        .boarding-pass::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 30%;
            width: 40%;
            height: 3px;
            background: repeating-linear-gradient(90deg, #333, #333 5px, transparent 5px, transparent 10px);
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <!-- Decorative elements -->
    <i class="fas fa-plane airplane"></i>
    <i class="fas fa-cloud cloud"></i>
    <i class="fas fa-cloud cloud"></i>
    <i class="fas fa-cloud cloud"></i>

    <div class="welcome-container">
        <!-- New decorative elements -->
        <div class="passport-stamp">
            <i class="fas fa-stamp"></i>
        </div>
        <div class="luggage-tag">
            <i class="fas fa-tag"></i> HR 2 PORTAL
        </div>
        <div class="boarding-pass">
            JETLOUGE HR 2
        </div>

        <div class="logo">
            <i class="fas fa-globe-americas logo-icon"></i>
        </div>
        <h1>JETLOUGE TRAVEL AND TOURS</h1>
        <p>Welcome to <strong>Human Resources 2</strong> Portal</p>
        <p class="tagline">Managing our greatest asset - our people</p>

        <div class="btn-container">
            <button class="btn" onclick="confirmAdminLogin()">
                <i class="fas fa-user-shield"></i> ADMIN LOGIN
            </button>
            <button class="btn admin" onclick="confirmEmployeeLogin()">
                <i class="fas fa-user-tie"></i> EMPLOYEE LOGIN
            </button>
        </div>
    </div>

    <footer>
        &copy; 2025 JETLOUGE TRAVEL AND TOURS. All Rights Reserved.
    </footer>

    <script>
        // Show welcome message on page load
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Welcome to HR2ESS!',
                text: 'JETLOUGE TRAVEL AND TOURS Human Resources Portal',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                color: '#fff'
            });
        });

        // Admin login confirmation
        function confirmAdminLogin() {
            Swal.fire({
                title: 'Admin Login',
                text: 'You are about to access the Admin Portal',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a2980',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-user-shield"></i> Continue to Admin',
                cancelButtonText: 'Cancel',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(0,0,0,0.4)',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            resolve();
                        }, 1000);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Redirecting...',
                        text: 'Taking you to Admin Login',
                        icon: 'info',
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didClose: () => {
                            window.location.href = '{{ route('admin.login') }}';
                        }
                    });
                }
            });
        }

        // Employee login confirmation
        function confirmEmployeeLogin() {
            Swal.fire({
                title: 'Employee Login',
                text: 'You are about to access the Employee Portal',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#26d0ce',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-user-tie"></i> Continue to Employee',
                cancelButtonText: 'Cancel',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(0,0,0,0.4)',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            resolve();
                        }, 1000);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Redirecting...',
                        text: 'Taking you to Employee Login',
                        icon: 'info',
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didClose: () => {
                            window.location.href = '{{ route('employee.login') }}';
                        }
                    });
                }
            });
        }

        // Add system status check functionality
        function checkSystemStatus() {
            Swal.fire({
                title: 'System Status',
                html: `
                    <div style="text-align: left;">
                        <p><i class="fas fa-server" style="color: #28a745;"></i> <strong>Server:</strong> Online</p>
                        <p><i class="fas fa-database" style="color: #28a745;"></i> <strong>Database:</strong> Connected</p>
                        <p><i class="fas fa-shield-alt" style="color: #28a745;"></i> <strong>Security:</strong> Active</p>
                        <p><i class="fas fa-users" style="color: #17a2b8;"></i> <strong>Active Users:</strong> 127</p>
                        <p><i class="fas fa-clock" style="color: #ffc107;"></i> <strong>Last Update:</strong> ${new Date().toLocaleString()}</p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Close',
                confirmButtonColor: '#007bff',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(0,0,0,0.4)'
            });
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + A for Admin Login
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                confirmAdminLogin();
            }
            // Alt + E for Employee Login
            if (e.altKey && e.key === 'e') {
                e.preventDefault();
                confirmEmployeeLogin();
            }
            // Alt + S for System Status
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                checkSystemStatus();
            }
        });

        // Add click effect to decorative elements
        document.querySelector('.passport-stamp').addEventListener('click', function() {
            Swal.fire({
                title: 'HR2ESS Portal',
                text: 'Authorized Personnel Only',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });

        document.querySelector('.luggage-tag').addEventListener('click', function() {
            Swal.fire({
                title: 'Welcome Aboard!',
                text: 'JETLOUGE HR2 Portal - Your journey to efficient HR management',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
    </script>
</body>
</html>
