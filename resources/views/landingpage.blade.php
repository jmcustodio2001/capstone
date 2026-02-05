<!DOCTYPE html>
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
     <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
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
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1 {{ request()->is('login-options') ? 'text-black' : 'text-white' }}" href="{{ route('login-options') }}">
                                    <i class="bi bi-box-arrow-in-right"></i> Portals
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
        }

        .hero-section h1 {
            font-size: 65px;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
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

        .testimonials {
            background-color: #2F4156;
        }

        .why-join-us h2 {
            font-size: 60px !important;
        }

        .why-join-us .d-flex {
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
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
                max-width: 60%;
                opacity: 0.8;
            }
        }

        @media (min-width: 992px) {
            .hero-section .col-lg-12 {
                max-width: 80%;
                min-width: 50%;
            }
        }
    </style>

    <section @class('hero-section d-flex align-items-center position-relative')>
        <div @class('container')>
            <div @class('row')>
                <div @class('col-lg-12 col-12')>
                    <h1 @class('text-white fw-bold')>BUILD YOUR CAREER IN <span @class('text-warning')>TRAVEL & TOURISM</span> - GROW, EXPLORE, AND MAKE AN IMPACT</h1>
                    <p @class('text-white w-75')>Join a workforce that travels the world, deliver unforgettable experiences and builds real career opportunities</p>
                    <a href="https://hr1.jetlougetravels-ph.com/careers" @class('btn btn-dark btn-lg fs-3')>View Careers</a>
                </div>
            </div>
        </div>
        <img src="{{ asset('images/home-1.png') }}" @class('hero-image position-absolute bottom-0 end-0') alt="Hero Image">
    </section>

    <section @class('what-to-expect py-5')>
        <div @class('container')>
            <div @class('row align-items-center')>
                <div @class('col-lg-6 col-md-12 text-center pe-lg-5 mb-4 mb-lg-0')>
                    <img src="{{ asset('images/home-2.png') }}" alt="What to Expect Image" @class('img-fluid')>
                </div>
                <div @class('col-lg-6 col-md-12 ps-lg-5')>
                    <h2 @class('fw-bold mb-4') style="font-size: 60px">WHAT TO EXPECT?</h2>
                    <ul @class('list-unstyled')>
                        <li @class('d-flex align-items-start mb-3')>
                            <span @class('badge bg-warning rounded-circle me-3 mt-1')>1</span>
                            <p @class('mb-0')>Clear career paths for tour guides, agents, drivers, and operations staff</p>
                        </li>
                        <li @class('d-flex align-items-start mb-3')>
                            <span @class('badge bg-warning rounded-circle me-3 mt-1')>2</span>
                            <p @class('mb-0')>Hands-on training, certifications, and professional development</p>
                        </li>
                        <li @class('d-flex align-items-start mb-3')>
                            <span @class('badge bg-warning rounded-circle me-3 mt-1')>3</span>
                            <p @class('mb-0')>A supportive hiring process with guidance every step</p>
                        </li>
                        <li @class('d-flex align-items-start mb-3')>
                            <span @class('badge bg-warning rounded-circle me-3 mt-1')>4</span>
                            <p @class('mb-0')>Opportunities to travel, meet people, and explore new cultures</p>
                        </li>
                        <li @class('d-flex align-items-start mb-3')>
                            <span @class('badge bg-warning rounded-circle me-3 mt-1')>5</span>
                            <p @class('mb-0')>Work environments built on teamwork, safety, and respect</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section @class('testimonials py-5 text-white')>
        <div @class('container')>
            <div @class('row text-center')>
                <div @class('col-lg-4 col-md-12 mb-4 mb-lg-0')>
                    <img src="{{ asset('images/home-3.png') }}" alt="Maria Del Valle" @class('rounded-circle mb-3') width="150" height="150">
                    <h4 @class('fw-bold')>Maria Del Valle</h4>
                    <p @class('text-sm px-4')>Started as a tour guide; now a senior operations supervisor managing 30+ tours.</p>
                    <div @class('stars')>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('far fa-star text-warning')></i>
                    </div>
                </div>
                <div @class('col-lg-4 col-md-12 mb-4 mb-lg-0')>
                    <img src="{{ asset('images/home-4.png') }}" alt="Clifford Hiller" @class('rounded-circle mb-3') width="150" height="150">
                    <h4 @class('fw-bold')>Clifford Hiller</h4>
                    <p @class('text-sm px-4')>From fresh graduate to award-winning cultural guide in just 18 months.</p>
                    <div @class('stars')>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('far fa-star text-warning')></i>
                    </div>
                </div>
                <div @class('col-lg-4 col-md-12')>
                    <img src="{{ asset('images/home-5.png') }}" alt="Lia Hidalgo" @class('rounded-circle mb-3') width="150" height="150">
                    <h4 @class('fw-bold')>Lia Hidalgo</h4>
                    <p @class('text-sm px-4')>Joined as customer support; now works internationally with partner agencies.</p>
                    <div @class('stars')>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                        <i @class('fas fa-star text-warning')></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section @class('why-join-us py-5')>
        <div @class('container')>
            <h2 @class('fw-bold') style="font-size: 60px;">WHY JOIN US?</h2>
            <div @class('row g-4 py-5')>
                <div @class('col-lg-6')>
                    <div @class('row g-3')>
                        <div @class('col-12 text-start')>
                            <div @class('d-flex align-items-center mb-3')>
                                <img src="{{ asset('images/home-6.png') }}" alt="Competitive salary" @class('me-3') style='width: 50px; height: 50px; object-fit: contain;'>
                                <p @class('mb-0') style="font-size: 32px;">Competitive salary + seasonal bonuses</p>
                            </div>
                        </div>
                        <div @class('col-12 text-start')>
                            <div @class('d-flex align-items-center mb-3')>
                                <img src="{{ asset('images/home-7.png') }}" alt="Training" @class('me-3') style='width: 50px; height: 50px; object-fit: contain;'>
                                <p @class('mb-0') style="font-size: 32px;">Free or subsidized training & certifications</p>
                            </div>
                        </div>
                        <div @class('col-12 text-start')>
                            <div @class('d-flex align-items-center mb-3')>
                                <img src="{{ asset('images/home-8.png') }}" alt="Flexible schedules" @class('me-3') style='width: 50px; height: 50px; object-fit: contain;'>
                                <p @class('mb-0') style="font-size: 32px;">Flexible schedules (seasonal, full-time, or part-time)</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div @class('col-lg-6')>
                    <div @class('row g-3')>
                        <div @class('col-12 text-start')>
                            <div @class('d-flex align-items-center mb-3')>
                                <img src="{{ asset('images/home-9.png') }}" alt="Travel perks" @class('me-3') style='width: 50px; height: 50px; object-fit: contain;'>
                                <p @class('mb-0') style="font-size: 32px;">Travel perks and destination-based assignments</p>
                            </div>
                        </div>
                        <div @class('col-12 text-start')>
                            <div @class('d-flex align-items-center mb-3')>
                                <img src="{{ asset('images/home-10.png') }}" alt="Career growth" @class('me-3') style='width: 50px; height: 50px; object-fit: contain;'>
                                <p @class('mb-0') style="font-size: 32px;">Career growth and internal promotions</p>
                            </div>
                        </div>
                        <div @class('col-12 text-start')>
                            <div @class('d-flex align-items-center mb-3')>
                                <img src="{{ asset('images/home-11.png') }}" alt="Working environment" @class('me-3') style='width: 50px; height: 50px; object-fit: contain;'>
                                <p @class('mb-0') style="font-size: 32px;">Inclusive, diverse, and safe working environment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

    gsap.from(".hero-section p", {
        opacity: 0,
        y: 30,
        duration: 1,
        delay: 0.3,
        ease: "power3.out"
    });

    gsap.from(".hero-section .btn", {
        opacity: 1,
        scale: 0.8,
        duration: 0.8,
        delay: 0.6,
        ease: "back.out(1.7)"
    });

    gsap.from(".hero-image", {
        opacity: 0,
        x: 100,
        duration: 1.2,
        delay: 0.5,
        ease: "power3.out"
    });

    // What to Expect Section Animation
    gsap.from(".what-to-expect h2", {
        scrollTrigger: {
            trigger: ".what-to-expect",
            start: "top 80%"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        ease: "power2.out"
    });

    gsap.from(".what-to-expect img", {
        scrollTrigger: {
            trigger: ".what-to-expect",
            start: "top 70%"
        },
        opacity: 0,
        x: -50,
        duration: 1,
        ease: "power2.out"
    });

    gsap.from(".what-to-expect ul li", {
        scrollTrigger: {
            trigger: ".what-to-expect",
            start: "top 70%"
        },
        opacity: 0,
        x: 50,
        duration: 0.8,
        stagger: 0.2,
        ease: "power2.out"
    });

    // Testimonials Section Animation
    gsap.from(".testimonials h2", {
        scrollTrigger: {
            trigger: ".testimonials",
            start: "top 80%"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        ease: "power2.out"
    });

    gsap.from(".testimonials .col-lg-4", {
        scrollTrigger: {
            trigger: ".testimonials",
            start: "top 70%"
        },
        opacity: 0,
        scale: 0.9,
        duration: 0.8,
        stagger: 0.2,
        ease: "back.out(1.7)"
    });

    // Why Join Us Section Animation
    gsap.from(".why-join-us h2", {
        scrollTrigger: {
            trigger: ".why-join-us",
            start: "top 80%"
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        ease: "power2.out"
    });

    gsap.from(".why-join-us .d-flex", {
        scrollTrigger: {
            trigger: ".why-join-us",
            start: "top 70%"
        },
        opacity: 0,
        y: 30,
        duration: 0.6,
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
