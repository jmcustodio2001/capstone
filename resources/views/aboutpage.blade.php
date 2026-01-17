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
            min-height: 100vh;
            position: relative;
        }

        .hero-image {
            max-width: 50%;
            height: auto;
            z-index: 1;
            position: absolute;
            left: 0;
            bottom: 0;
        }

        .hero-section h1 {
            font-size: 65px;
            line-height: 1.2;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
            text-align: center;
        }

        .hero-section .highlight {
            color: #FFD700;
            font-weight: bold;
        }

        .hero-section .empowering {
            color: #FF8922;
            font-weight: bold;
        }

        .hero-section .subtitle {
            font-size: 32px;
            font-weight: 600;
            color: white;
            margin-bottom: 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
            text-align: center;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
            max-width: 600px;
        }

        .hero-section .btn {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .hero-section .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .second-section {
            background-color: #f8f9fa;
        }


        .second-section h2, .third-section h2 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 2rem;
            color: #000205;
        }

        .second-section p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .second-section .subtitle {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .second-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            border: 1px solid #e9e9e9;
            background-color: #f8f9fa;
            padding: 1rem;
        }

        .third-section {
            background-color: #567C8D;
        }

        .third-section .empowering {
            color: #FF8922;
            font-weight: bold;
        }

        .third-section .subtitle {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 0;
        }


        .fourth-section h2 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 3rem;
        }

        .fourth-section .subtitle {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 0;
        }




        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
                text-align: center;
            }

            .hero-section .subtitle {
                font-size: 1.5rem;
                text-align: center;
            }

            .hero-section p {
                font-size: 1.1rem;
                text-align: center;
            }

            .hero-section .btn {
                display: block;
                margin: 0 auto;
            }

            .hero-image {
                position: relative;
                max-width: 70%;
                opacity: 0.8;
                right: auto;
                top: auto;
                transform: none;
                margin: 2rem auto;
                display: block;
            }
        }

        @media (min-width: 992px) {
            .hero-section .col-lg-8 {
                max-width: 65%;
            }
        }
    </style>

    <section @class('hero-section d-flex align-items-center position-relative')>
        <div @class('container position-relative')>
            <div @class('row justify-content-center')>
                <div @class('col-lg-12 col-md-12 text-center')>
                    <h1 @class('text-white fw-bold')><span @class('empowering')>EMPOWERING</span> THE FUTURE WORKFORCE OF TRAVEL AND TOURISM</h1>
                    <div @class('subtitle')>We develop, train, and connect passionate people with meaningful careers in one of the world's most exciting industries.</div>
                </div>
            </div>
        </div>
        <img src="{{ asset('images/about-1.png') }}" @class('hero-image position-absolute bottom-0 p-3') alt="About Hero Image">
    </section>

    <section @class('second-section py-5')>
        <div @class('container')>
            <div @class('row align-items-center')>
                <div @class('col-lg-6 col-md-12')>
                    <h2>WHO ARE WE</h2>
                    <h5 @class('mb-3')>A dedicated travel & tourism workforce organization</h5>
                    <h5 @class('mb-3')>Specialists in talent acquisition, training, and job placement</h5>
                    <h5 @class('mb-3')>Partnered with top tour operators, hospitality groups, and transportation provider</h5>
                    <h5 @class('mb-3')>Committed to building a skilled, customer-focused, and confident tourism workforce</h5>
                </div>
                <div @class('col-lg-6 col-md-12 mb-4 mb-lg-0')>
                    <img src="{{ asset('images/about-2.png') }}" alt="Who We Are" class="second-image">
                </div>
            </div>
        </div>
    </section>

    <section @class('third-section py-5')>
        <div @class('container')>
            <div @class('text-center')>
                <h2>OUR <span @class('empowering')>MISSION</span></h2>
                <p @class('text-center subtitle')>To unlock opportunities for individuals and strengthen the travel industry with qualified, empowered talent.</p>
            </div>
        </div>
    </section>

    <section @class('fourth-section py-5 mt-3')>
        <div @class('container')>
            <h2>WHAT WE DO</h2>
            <div @class('row g-4')>
                <div @class('col-lg-4 col-md-12 mb-4')>
                    <div @class('service-item-alt')>
                        <img src="{{ asset('images/about-3.png') }}" alt="Tour Packages" class="img-fluid">
                    </div>
                </div>
                <div @class('col-lg-8 col-md-12 mb-4')>
                    <div class="row">
                        <div @class('col-md-6')>
                            <div @class('service-item-alt')>
                                <span @class('d-flex align-items-center')>
                                    <img src="{{ asset('images/about-4.png') }}" class="img-fluid">
                                    <h5 @class('ms-3')>Recruitment and talent pipeline development</h5>
                                </span>
                                <p>Develops a strategic process to attract and nurture qualified candidates, ensuring a sustainable talent flow that supports organizational growth, aligns recruitment with long-term goals, and strengthens workforce readiness.</p>
                            </div>
                        </div>
                        <div @class('col-md-6')>
                            <div @class('service-item-alt')>
                                <span @class('d-flex align-items-center')>
                                    <img src="{{ asset('images/about-5.png') }}" class="img-fluid">
                                    <h5 @class('ms-3')>Workforce placement for local and international partners</h5>
                                </span>
                                <p>Facilitates the placement of qualified workers with local and international partners, meeting diverse staffing needs while strengthening organizational capacity and promoting global collaboration.</p>
                            </div>
                        </div>
                        <div @class('col-md-6')>
                            <div @class('service-item-alt')>
                                <span @class('d-flex align-items-center')>
                                    <img src="{{ asset('images/about-6.png') }}" class="img-fluid">
                                    <h5 @class('ms-3')>Certification programs and professional development</h5>
                                </span>
                                <p>Provides certification programs and professional development to strengthen workforce expertise, enhance credentials, and support career growth while aligning skills with organizational and global standards.</p>
                            </div>
                        </div>
                        <div @class('col-md-6')>
                            <div @class('service-item-alt')>
                                <span @class('d-flex align-items-center')>
                                    <img src="{{ asset('images/about-7.png') }}" class="img-fluid">
                                    <h5 @class('ms-3')>Ongoing support to help workers grow in their careers</h5>
                                </span>
                                <p>Provides structured career guidance, continuous training, and professional growth opportunities to empower workers in advancing their skills, achieving long-term career goals, and contributing to organizational success.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- The best athlete wants his opponent at his best. --}}
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger);

    // Hero Section Animation
    gsap.from(".hero-section h1", {
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power3.out"
    });

    gsap.from(".hero-section .subtitle", {
        opacity: 0,
        y: 30,
        duration: 1,
        delay: 0.3,
        ease: "power3.out"
    });

    gsap.from(".hero-image", {
        opacity: 0,
        x: 100,
        duration: 1.2,
        delay: 0.5,
        ease: "power3.out"
    });

    // Second Section Animation
    gsap.from(".second-section h2", {
        scrollTrigger: {
            trigger: ".second-section",
            start: "top 80%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power2.out"
    });

    gsap.from(".second-section .col-lg-6", {
        scrollTrigger: {
            trigger: ".second-section",
            start: "top 70%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        stagger: 0.2,
        ease: "power2.out"
    });

    // Third Section Animation
    gsap.from(".third-section h2", {
        scrollTrigger: {
            trigger: ".third-section",
            start: "top 80%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power2.out"
    });

    gsap.from(".third-section .subtitle", {
        scrollTrigger: {
            trigger: ".third-section",
            start: "top 70%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        ease: "power2.out"
    });

    // Fourth Section Animation
    gsap.from(".fourth-section h2", {
        scrollTrigger: {
            trigger: ".fourth-section",
            start: "top 80%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power2.out"
    });

    gsap.from(".fourth-section .col-lg-4", {
        scrollTrigger: {
            trigger: ".fourth-section",
            start: "top 70%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        ease: "power2.out"
    });

    gsap.from(".fourth-section .col-lg-8 .col-md-6", {
        scrollTrigger: {
            trigger: ".fourth-section",
            start: "top 70%",
            toggleActions: "play none none reverse"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        stagger: 0.1,
        ease: "power2.out"
    });
});
</script>
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
