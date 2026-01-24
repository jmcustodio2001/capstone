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
    {{-- Header --}}
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
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') ? 'text-black' : 'text-white' }}" href="{{ route('welcome') }}">
                                    <i class="bi bi-house-door-fill"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') ? 'text-black' : 'text-white' }}" href="{{ route('about') }}">
                                    <i class="bi bi-info-circle-fill"></i> About
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') ? 'text-black' : 'text-white' }}" href="{{ route('contact') }}">
                                    <i class="bi bi-envelope-fill"></i> Contact
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('careers') ? 'text-black' : 'text-white' }}" href="https://hr1.jetlougetravels-ph.com/careers">
                                    <i class="bi bi-briefcase-fill"></i> Careers
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 {{ request()->is('login') ? 'text-black' : 'text-white' }}" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="loginDropdown">
                                    <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                                    <li><a class="dropdown-item" href="#">HR1</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.login') }}">HR2</a></li>
                                    <li><a class="dropdown-item" href="#">HR3</a></li>
                                    <li><a class="dropdown-item" href="#">HR4</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

    </div>
    {{-- Main --}}
    <main @class('flex-grow-1')>
<div>
    <style>
        .hero-section {
            background-color: #567C8D;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .hero-content {
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #FF8922;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 0;
            opacity: 0.9;
            color: white;
        }

        .card-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }

        .card {
            background: #2F4156;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 0;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }

        .card-body {
            padding: 1.5rem;
            text-align: center;
        }

        .card-body h4 {
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .card-body p {
            color: white;
            font-size: 24px;
            margin-bottom: 0;
        }

        .contact-section {
            background-color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 80px 0;
        }

        .contact-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .contact-info {
            background-color: #f8f9fa;
            padding: 3rem;
            height: 100%;
        }

        .contact-info h2 {
            color: #2F4156;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .contact-info p {
            color: #567C8D;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .contact-info i {
            margin-right: 1rem;
            color: #FF8922;
            font-size: 1.2rem;
            width: 20px;
        }


        .form-section {
            background-color: #567C8D;
            padding: 80px 0;
        }

        .form-section .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .form-section .card:hover {
            transform: translateY(-5px);
        }

        .form-section h3 {
            color: #2F4156;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .form-section h4 {
            color: #2F4156;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .form-section h5 {
            color: #FF8922;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-section .card-body p {
            color: #567C8D;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .form-section .form-label {
            color: #567C8D;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-section .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-section .form-control:focus {
            border-color: #FF8922;
            box-shadow: 0 0 0 0.2rem rgba(255, 137, 34, 0.25);
        }

        .form-section .btn-primary {
            background-color: #FF8922;
            border-color: #FF8922;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-section .btn-primary:hover {
            background-color: #e67a1f;
            border-color: #e67a1f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 137, 34, 0.3);
        }

        .form-section img {
            max-height: 300px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .form-section {
                padding: 40px 0;
            }

            .form-section h3 {
                font-size: 1.5rem;
            }

            .form-section h4 {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 768px) {
            .contact-section {
                padding: 40px 0;
            }

            .contact-info {
                padding: 2rem;
            }

            .contact-info h2 {
                font-size: 2rem;
            }
        }
    </style>

    <section @class('hero-section')>
        <div @class('container')>
            <div @class('hero-content')>
                <h1>We're Here to Help You Start Your Career Journey</h1>
                <p>Whether you're applying, training, or looking for information — reach out and our team will assist you.</p>
            </div>
        </div>
    </section>
    <section @class('card-section py-5')>
        <div @class('container')>
            <h2>Contact Options</h2>
            <div @class('row g-4 justify-content-center')>
                <div @class('col-lg-4 col-md-6 mb-4')>
                    <div @class('card h-100')>
                        <div @class('card-body text-center p-4')>
                            <h4>General Inquiries</h4>
                            <p>Get help anytime, anywhere in the world</p>
                        </div>
                    </div>
                </div>
                <div @class('col-lg-4 col-md-6 mb-4')>
                    <div @class('card h-100')>
                        <div @class('card-body text-center p-4')>
                            <h4>Quick Response</h4>
                            <p>Fast replies to all your inquiries</p>
                        </div>
                    </div>
                </div>
                <div @class('col-lg-4 col-md-6 mb-4')>
                    <div @class('card h-100')>
                        <div @class('card-body text-center p-4')>
                            <h4>Expert Team</h4>
                            <p>Professional guidance from experienced travel specialists</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section @class('form-section py-5')>
        <div @class('container')>
            <div @class('row justify-content-center')>
                <div @class('col-lg-8 col-md-12')>
                    <div @class('row')>
                        <div @class('col-lg-8 col-md-12')>
                            <div @class('card rounded-3 shadow-sm bg-white')>
                                <div @class('card-body')>
                                    <h3>Send us a Message</h3>
                                    <form>
                                        <div @class('row g-3')>
                                            <div @class('col-md-6 mb-3')>
                                                <label for="name" @class('form-label')>Your Name</label>
                                                <input type="text" id="name" name="name" @class('form-control') placeholder="Enter your name" required>
                                            </div>
                                            <div @class('col-md-6 mb-3')>
                                                <label for="email" @class('form-label')>Email Address</label>
                                                <input type="email" id="email" name="email" @class('form-control') placeholder="Enter your email" required>
                                            </div>
                                        </div>
                                        <div @class('mb-3')>
                                            <label for="subject" @class('form-label')>Subject</label>
                                            <input type="text" id="subject" name="subject" @class('form-control') placeholder="Enter subject" required>
                                        </div>
                                        <div @class('mb-3')>
                                            <label for="message" @class('form-label')>Message</label>
                                            <textarea id="message" name="message" @class('form-control') rows="5" placeholder="Enter your message" required></textarea>
                                        </div>
                                        <div @class('text-center mt-4')>
                                            <button type="submit" @class('btn btn-primary btn-lg px-5')>Send Message</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div @class('col-lg-4 col-md-12')>
                            <div @class('card rounded-3 shadow-sm bg-white')>
                                <div @class('card-body')>
                                    <h4>Additional Contact Channels</h4>
                                    <h5>Email</h5>
                                    <p>info@travel-career.com</p>
                                    <h5>Phone Number</h5>
                                    <p>+62 812 3456 7890</p>
                                </div>
                                <div @class('card-body p-0 text-center')>
                                    <img src="{{ asset('images/contact-form.png') }}" alt="Contact Form" class="img-fluid rounded-3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    @include('layouts.includes.animation')

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Register ScrollTrigger plugin
        gsap.registerPlugin(ScrollTrigger);

        // Hero Section Animation
        gsap.from(".hero-section", {
            scrollTrigger: {
                trigger: ".hero-section",
                start: "top 80%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 50,
            duration: 1,
            ease: "power2.out"
        });

        gsap.from(".hero-content h1", {
            scrollTrigger: {
                trigger: ".hero-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            delay: 0.2,
            ease: "power2.out"
        });

        gsap.from(".hero-content p", {
            scrollTrigger: {
                trigger: ".hero-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 20,
            duration: 0.8,
            delay: 0.4,
            ease: "power2.out"
        });

        // Card Section Animation
        gsap.from(".card-section", {
            scrollTrigger: {
                trigger: ".card-section",
                start: "top 80%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 50,
            duration: 1,
            ease: "power2.out"
        });

        gsap.from(".card-section h2", {
            scrollTrigger: {
                trigger: ".card-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            delay: 0.2,
            ease: "power2.out"
        });

        gsap.from(".card", {
            scrollTrigger: {
                trigger: ".card-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            stagger: 0.2,
            delay: 0.4,
            ease: "power2.out"
        });

        // Form Section Animation
        gsap.from(".form-section", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 80%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 50,
            duration: 1,
            ease: "power2.out"
        });

        gsap.from(".form-section h3", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            delay: 0.2,
            ease: "power2.out"
        });

        gsap.from(".form-section .form-control", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 20,
            duration: 0.6,
            stagger: 0.1,
            delay: 0.4,
            ease: "power2.out"
        });

        gsap.from(".form-section .btn-primary", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 20,
            duration: 0.8,
            delay: 0.8,
            ease: "power2.out"
        });

        gsap.from(".form-section h4", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            x: -30,
            duration: 0.8,
            delay: 0.6,
            ease: "power2.out"
        });

        gsap.from(".form-section .card-body p", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            x: -20,
            duration: 0.6,
            stagger: 0.1,
            delay: 0.8,
            ease: "power2.out"
        });

        gsap.from(".form-section img", {
            scrollTrigger: {
                trigger: ".form-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            scale: 0.9,
            duration: 1,
            delay: 1,
            ease: "power2.out"
        });

        // Contact Section Animation
        gsap.from(".contact-section", {
            scrollTrigger: {
                trigger: ".contact-section",
                start: "top 80%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 50,
            duration: 1,
            ease: "power2.out"
        });

        gsap.from(".contact-info h2", {
            scrollTrigger: {
                trigger: ".contact-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            delay: 0.2,
            ease: "power2.out"
        });

        gsap.from(".contact-info p", {
            scrollTrigger: {
                trigger: ".contact-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            y: 20,
            duration: 0.8,
            stagger: 0.1,
            ease: "power2.out"
        });

        gsap.from("#map", {
            scrollTrigger: {
                trigger: ".contact-section",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            scale: 0.9,
            duration: 1,
            delay: 0.4,
            ease: "power2.out"
        });
    });
    </script>
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
