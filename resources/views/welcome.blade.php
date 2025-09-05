<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JETLOUGE TRAVEL AND TOURS : HUMAN RESOURCES 2</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Responsive design */
        @media (max-width: 768px) {
            .welcome-container {
                padding: 30px;
            }

            .welcome-container h1 {
                font-size: 2.2rem;
            }

            .btn-container {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
            <button class="btn" onclick="window.location.href='{{ route('admin.login') }}'">
                <i class="fas fa-user-shield"></i> ADMIN LOGIN
            </button>
            <button class="btn admin" onclick="window.location.href='{{ route('employee.login') }}'">
                <i class="fas fa-user-tie"></i> EMPLOYEE LOGIN
            </button>
        </div>
    </div>

    <footer>
        &copy; 2025 JETLOUGE TRAVEL AND TOURS. All Rights Reserved.
    </footer>
</body>
</html>
