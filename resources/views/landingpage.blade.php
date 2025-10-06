<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jetlouge Travels - Discover Your Next Adventure</title>
    <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
    <link href="../assets/css/landing-style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo">
                <span class="logo-text">Jetlouge Travels</span>
            </div>
            <div class="nav-menu">
                <a href="#home" class="nav-link">Home</a>
                <a href="#features" class="nav-link">About</a>
                <a href="#footer" class="nav-link">Contact</a>
                <a href="{{ route('welcome.page') }}" class="nav-link btn-portal">Login</a>
            </div>
            <div class="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Discover Your Next Adventure</h1>
            <p class="hero-subtitle">Explore the Philippines' most beautiful destinations with our carefully curated travel packages</p>
        </div>
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number" id="happy-travelers">1000+</span>
                <span class="stat-label">Happy Travelers</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="destinations">50+</span>
                <span class="stat-label">Destinations</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="years-experience">10+</span>
                <span class="stat-label">Years Experience</span>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose Jetlouge Travels?</h2>
                <p class="section-subtitle">Your trusted partner for memorable adventures</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Safe & Secure</h3>
                    <p class="feature-description">Your safety is our priority. All our tours are fully insured and guided by certified professionals.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="feature-title">Best Prices</h3>
                    <p class="feature-description">Competitive pricing with no hidden fees. Get the best value for your travel investment.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">Round-the-clock customer support to assist you before, during, and after your trip.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3 class="feature-title">Expert Guides</h3>
                    <p class="feature-description">Local expert guides who know the best spots and hidden gems in every destination.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Travelers Say</h2>
                <p class="section-subtitle">Real experiences from real adventurers</p>
            </div>

            <div class="testimonials-slider">
                <div class="testimonial-card active">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Amazing experience in Boracay! The beach resort was perfect and the island hopping tour was unforgettable. Jetlouge Travels made everything seamless."</p>
                        <div class="testimonial-author">
                            <div class="author-info">
                                <h4 class="author-name">Marian Rivera</h4>
                                <span class="author-location">Boracay Beach Paradise</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The Palawan underground river tour exceeded all expectations. Professional guides and well-organized itinerary. Highly recommended!"</p>
                        <div class="testimonial-author">
                            <div class="author-info">
                                <h4 class="author-name">JM Custodio</h4>
                                <span class="author-location">Palawan Underground Adventure</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3 class="newsletter-title">Stay Updated with Our Latest Offers</h3>
                    <p class="newsletter-subtitle">Subscribe to get exclusive deals and travel tips</p>
                </div>
                <div class="newsletter-form">
                    <form id="newsletter-form">
                        <input type="email" placeholder="Enter your email address" class="newsletter-input" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo">
                        <span class="logo-text">Jetlouge Travels</span>
                    </div>
                    <p class="footer-description">Your trusted partner for unforgettable travel experiences across the Philippines and beyond.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-title">Popular Destinations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Boracay</a></li>
                        <li><a href="#">Palawan</a></li>
                        <li><a href="#">Baguio</a></li>
                        <li><a href="#">Cebu</a></li>
                        <li><a href="#">Siargao</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-title">Contact Info</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+63 917 123 4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@jetlougetravels.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Manila, Philippines</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Jetlouge Travels. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/landing-script.js"></script>
</body>
</html>
