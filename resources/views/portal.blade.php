<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- Title --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Jetlouge Travels</title>

    {{-- styles|scripts --}}
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="stylesheet" href="{{ asset('css/website.css') }}">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
   <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body @class('m-0 p-0 d-flex flex-column min-vh-100')>
    {{-- Header - Hidden on apply-now page for cleaner application experience --}}
    @unless(request()->routeIs('apply-now'))
    <div @class('container')>
        <header @class('sticky-top position-absolute container')>
            <nav @class('navbar navbar-expand-lg p-1')>
                <div @class('container d-flex justify-content-between align-items-center')>
                    <img src="{{ asset('images/logo.png') }}" width="50" height="50" alt="Logo">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">

                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') || request()->is('login-options') ? 'text-black' : 'text-white' }}" href="{{ route('welcome') }}">
                                    <i class="bi bi-house-door-fill"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') || request()->is('login-options') ? 'text-black' : 'text-white' }}" href="{{ route('about') }}">
                                    <i class="bi bi-info-circle-fill"></i> About
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') || request()->is('login-options') ? 'text-black' : 'text-white' }}" href="{{ route('contact') }}">
                                    <i class="bi bi-envelope-fill"></i> Contact
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') || request()->is('login-options') ? 'text-black' : 'text-white' }}" href="https://hr1.jetlougetravels-ph.com/careers">
                                    <i class="bi bi-briefcase-fill"></i> Careers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') || request()->is('login-options') ? 'text-black' : 'text-white' }}" href="{{ route('login-options') }}">
                                    <i class="bi bi-box-arrow-in-right"></i> Portals
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

    </div>
    @endunless
    {{-- Main --}}
    <main @class('flex-grow-1')>
  <div class="container py-5">
    <div class="row min-vh-50 justify-content-center align-items-center">
        <div class="col-12 text-center mb-5">
            <h2 class="fw-bold display-5 text-primary">Employee & HR Portals</h2>
            <p class="text-muted">Select your portal to login</p>
        </div>

        <div class="col-12">
            <div class="row g-4 justify-content-center">
                <!-- HR 1 Card -->
                <div class="col-md-6 col-lg-2">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-people-fill text-primary display-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">HR 1</h4>
                            <p class="card-text text-muted small mb-4">Human Resources Department System 1</p>
                            <a href="https://hr1.jetlougetravels-ph.com/login" class="btn btn-outline-primary w-100 rounded-pill">
                                Login to HR 1
                            </a>
                        </div>
                    </div>
                </div>

                <!-- HR 2 Card -->
                <div class="col-md-6 col-lg-2">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-building-gear text-success display-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">HR 2</h4>
                            <p class="card-text text-muted small mb-4">Human Resources Department System 2</p>
                            <a href="https://hr2.jetlougetravels-ph.com/admin/login" class="btn btn-outline-success w-100 rounded-pill">
                                Login to HR 2
                            </a>
                        </div>
                    </div>
                </div>

                <!-- HR 3 Card -->
                <div class="col-md-6 col-lg-2">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-person-workspace text-warning display-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">HR 3</h4>
                            <p class="card-text text-muted small mb-4">Human Resources Department System 3</p>
                            <a href="https://hr3.jetlougetravels-ph.com/admin/login" class="btn btn-outline-warning w-100 rounded-pill">
                                Login to HR 3
                            </a>
                        </div>
                    </div>
                </div>

                <!-- HR 4 Card -->
                <div class="col-md-6 col-lg-2">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-graph-up-arrow text-danger display-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">HR 4</h4>
                            <p class="card-text text-muted small mb-4">Human Resources Department System 4</p>
                            <a href="https://hr4.jetlougetravels-ph.com/login" class="btn btn-outline-danger w-100 rounded-pill">
                                Login to HR 4
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ESS Card -->
                <div class="col-md-6 col-lg-2">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-person-badge text-info display-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">ESS</h4>
                            <p class="card-text text-muted small mb-4">Employee Self-Service Portal</p>
                            <a href="https://hr2.jetlougetravels-ph.com/employee/login" class="btn btn-outline-info w-100 rounded-pill">
                                Login to ESS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
        }
    </style>
</div>

    </main>

    <footer @class([
    'bg-dark',
    'text-white',
    'pt-5',
    'pb-4',
    'mt-auto'])>
        <div class="container">
            <div class="row">
            {{-- Product Column --}}
            <div @class('col-md-3')>
                <h5 @class('text-uppercase mb-4')>Product</h5>
                <ul @class('list-unstyled')>
                    <li><a href="#" @class('text-white text-decoration-none')>Book a Trip</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Tour Packages</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Flight and Hotel Deals</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Special Discounts</a></li>
                </ul>
            </div>

            {{-- Resources Column --}}
            <div @class('col-md-3')>
                <h5 @class('text-uppercase mb-4')>Resources</h5>
                <ul @class('list-unstyled')>
                    <li><a href="#" @class('text-white text-decoration-none')>Travel Blog</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Destination Guides</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Customer Support</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Travel Tips</a></li>
                </ul>
            </div>

            {{-- Company Column --}}
            <div @class('col-md-3')>
                <h5 @class('text-uppercase mb-4')>Company</h5>
                <ul @class('list-unstyled')>
                <li><a href="#" @class('text-white text-decoration-none')>About Us</a></li>
                <li><a href="#" @class('text-white text-decoration-none')>Why Choose Us</a></li>
                <li><a href="#" @class('text-white text-decoration-none')>Contact Us</a></li>
                <li><a href="#" @class('text-white text-decoration-none')>Careers</a></li>
                </ul>
            </div>

            {{-- CTA Box --}}
            <div @class('col-md-3 d-flex align-items-center')>
                <div @class('bg-primary text-white p-4 rounded w-100 text-center')>
                <h5 @class('mb-0')>Travel with us Today!</h5>
                </div>
            </div>
            </div>
        </div>
    </footer>

    {{-- Animation --}}
    @include('layouts.animation')
</body>
</html>
