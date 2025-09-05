// =============================================
// JETLOUGE TRAVELS LANDING PAGE JAVASCRIPT
// =============================================

// Global variables
let currentOffset = 0;
let currentCategory = 'all';
let isLoading = false;
let hasMorePackages = true;

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    loadPackages();
    initializeFilters();
    initializeModal();
    initializeNewsletterForm();
    initializeAnimations();
    initializeTestimonialSlider();
});

// Navigation functionality
function initializeNavigation() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navbar = document.querySelector('.navbar');

    // Mobile menu toggle
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking on links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (navMenu) {
                navMenu.classList.remove('active');
            }
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        if (navbar) {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Load packages from API
async function loadPackages(reset = false) {
    if (isLoading) return;

    isLoading = true;
    const packagesGrid = document.getElementById('packages-grid');

    if (reset) {
        currentOffset = 0;
        packagesGrid.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p>Loading amazing packages...</p></div>';
    }

    try {
        const response = await fetch(`../api/get_packages.php?limit=6&offset=${currentOffset}&category=${currentCategory}`);
        const data = await response.json();

        if (data.success) {
            if (reset) {
                packagesGrid.innerHTML = '';
            } else {
                // Remove loading spinner if it exists
                const loadingSpinner = packagesGrid.querySelector('.loading-spinner');
                if (loadingSpinner) {
                    loadingSpinner.remove();
                }
            }

            // Add packages to grid
            data.data.forEach((package, index) => {
                const packageCard = createPackageCard(package);
                packagesGrid.appendChild(packageCard);

                // Animate card appearance
                setTimeout(() => {
                    packageCard.style.opacity = '1';
                    packageCard.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Update pagination
            currentOffset += data.data.length;
            hasMorePackages = data.pagination.has_more;

            // Update load more button
            updateLoadMoreButton();

        } else {
            throw new Error(data.message || 'Failed to load packages');
        }
    } catch (error) {
        console.error('Error loading packages:', error);
        if (reset) {
            packagesGrid.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load packages. Please try again later.</p>
                    <button class="btn btn-primary" onclick="loadPackages(true)">Retry</button>
                </div>
            `;
        }
    } finally {
        isLoading = false;
    }
}

// Create package card HTML
function createPackageCard(package) {
    const card = document.createElement('div');
    card.className = `package-card ${package.category}`;
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.6s ease';

    // Default image fallback
    const imageUrl = package.image || 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';

    // Create features HTML
    const featuresHTML = package.features.map(feature =>
        `<span class="feature-tag">${feature}</span>`
    ).join('');

    card.innerHTML = `
        <div class="package-image" style="background-image: url('${imageUrl}')">
            <div class="package-badge">${package.category}</div>
        </div>
        <div class="package-content">
            <h3 class="package-title">${package.title}</h3>
            <div class="package-destination">
                <i class="fas fa-map-marker-alt"></i>
                ${package.destination}
            </div>
            <p class="package-description">${truncateText(package.description, 120)}</p>
            <div class="package-features">
                ${featuresHTML}
            </div>
            <div class="package-footer">
                <div class="package-info">
                    <div class="package-price">₱${formatPrice(package.price)}</div>
                    <div class="package-duration">${package.duration} days</div>
                </div>
                <button class="book-btn" onclick="openBookingModal('${package.title}', ${package.id})">
                    <i class="fas fa-calendar-check"></i> Book Now
                </button>
            </div>
        </div>
    `;

    return card;
}

// Initialize package filters
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active filter
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update current category
            currentCategory = this.getAttribute('data-filter');

            // Reload packages
            loadPackages(true);
        });
    });
}

// Update load more button
function updateLoadMoreButton() {
    const loadMoreBtn = document.querySelector('.packages-footer .btn');
    if (loadMoreBtn) {
        if (hasMorePackages) {
            loadMoreBtn.style.display = 'inline-flex';
            loadMoreBtn.innerHTML = '<i class="fas fa-plus"></i> Load More Packages';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
}

// Load more packages
function loadMorePackages() {
    if (hasMorePackages && !isLoading) {
        loadPackages(false);
    }
}

// Modal functionality
function initializeModal() {
    const modal = document.getElementById('login-modal');
    const closeBtn = document.querySelector('.close');

    if (closeBtn) {
        closeBtn.addEventListener('click', closeLoginModal);
    }

    // Close modal when clicking outside
    if (modal) {
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeLoginModal();
            }
        });
    }
}

// Login Modal Functions
function openLoginModal() {
    console.log('openLoginModal called');
    const modal = document.getElementById('login-modal');
    console.log('Modal element:', modal);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        // Reset form states
        resetLoginForm();
        console.log('Modal opened successfully');
    } else {
        console.error('Login modal not found!');
    }
}

// Make function globally accessible
window.openLoginModal = openLoginModal;

function closeLoginModal() {
    const modal = document.getElementById('login-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetLoginForm();
    }
}

// Make all login functions globally accessible
window.closeLoginModal = closeLoginModal;

// Define other functions first before making them global
function loginWithGmail() {
    // Show loading state
    showNotification('Redirecting to Gmail...', 'info');

    // Simulate Gmail OAuth (replace with actual implementation)
    setTimeout(() => {
        showNotification('Gmail login successful! Redirecting to booking...', 'success');
        setTimeout(() => {
            closeLoginModal();
            redirectToBooking();
        }, 1500);
    }, 2000);
}

function showPhoneLogin() {
    const phoneForm = document.getElementById('phone-login-form');
    if (phoneForm) {
        phoneForm.style.display = 'block';
    }
}

function sendOTP() {
    const phoneInput = document.getElementById('phone-number');
    const countryCode = document.getElementById('country-code');

    if (!phoneInput || !phoneInput.value.trim()) {
        showNotification('Please enter a valid phone number', 'error');
        return;
    }

    const fullNumber = countryCode.value + phoneInput.value;

    // Show loading state
    showNotification('Sending OTP...', 'info');

    // Simulate OTP sending (replace with actual implementation)
    setTimeout(() => {
        showNotification(`OTP sent to ${fullNumber}`, 'success');
        const otpVerification = document.getElementById('otp-verification');
        if (otpVerification) {
            otpVerification.style.display = 'block';
        }
    }, 2000);
}

function resendOTP() {
    showNotification('Resending OTP...', 'info');

    setTimeout(() => {
        showNotification('OTP resent successfully', 'success');
    }, 1500);
}

function verifyOTP() {
    const otpInput = document.getElementById('otp-code');

    if (!otpInput || !otpInput.value.trim() || otpInput.value.length !== 6) {
        showNotification('Please enter a valid 6-digit OTP code', 'error');
        return;
    }

    // Show loading state
    showNotification('Verifying OTP...', 'info');

    // Simulate OTP verification (replace with actual implementation)
    setTimeout(() => {
        showNotification('Phone verification successful! Redirecting to booking...', 'success');
        setTimeout(() => {
            closeLoginModal();
            redirectToBooking();
        }, 1500);
    }, 2000);
}

function proceedAsGuest() {
    showNotification('Proceeding as guest...', 'info');
    setTimeout(() => {
        closeLoginModal();
        redirectToBooking();
    }, 1000);
}

// Make all functions globally accessible
window.loginWithGmail = loginWithGmail;
window.showPhoneLogin = showPhoneLogin;
window.sendOTP = sendOTP;
window.resendOTP = resendOTP;
window.verifyOTP = verifyOTP;
window.proceedAsGuest = proceedAsGuest;

function resetLoginForm() {
    // Hide phone form and OTP verification
    const phoneForm = document.getElementById('phone-login-form');
    const otpVerification = document.getElementById('otp-verification');

    if (phoneForm) phoneForm.style.display = 'none';
    if (otpVerification) otpVerification.style.display = 'none';

    // Clear form inputs
    const phoneInput = document.getElementById('phone-number');
    const otpInput = document.getElementById('otp-code');

    if (phoneInput) phoneInput.value = '';
    if (otpInput) otpInput.value = '';
}

// Gmail Login
function loginWithGmail() {
    // Show loading state
    showNotification('Redirecting to Gmail...', 'info');

    // Simulate Gmail OAuth (replace with actual implementation)
    setTimeout(() => {
        showNotification('Gmail login successful! Redirecting to booking...', 'success');
        setTimeout(() => {
            closeLoginModal();
            redirectToBooking();
        }, 1500);
    }, 2000);
}

// Phone Login
function showPhoneLogin() {
    const phoneForm = document.getElementById('phone-login-form');
    if (phoneForm) {
        phoneForm.style.display = 'block';
    }
}

function sendOTP() {
    const phoneInput = document.getElementById('phone-number');
    const countryCode = document.getElementById('country-code');

    if (!phoneInput || !phoneInput.value.trim()) {
        showNotification('Please enter a valid phone number', 'error');
        return;
    }

    const fullNumber = countryCode.value + phoneInput.value;

    // Show loading state
    showNotification('Sending OTP...', 'info');

    // Simulate OTP sending (replace with actual implementation)
    setTimeout(() => {
        showNotification(`OTP sent to ${fullNumber}`, 'success');
        const otpVerification = document.getElementById('otp-verification');
        if (otpVerification) {
            otpVerification.style.display = 'block';
        }
    }, 2000);
}

function resendOTP() {
    showNotification('Resending OTP...', 'info');

    setTimeout(() => {
        showNotification('OTP resent successfully', 'success');
    }, 1500);
}

function verifyOTP() {
    const otpInput = document.getElementById('otp-code');

    if (!otpInput || !otpInput.value.trim() || otpInput.value.length !== 6) {
        showNotification('Please enter a valid 6-digit OTP code', 'error');
        return;
    }

    // Show loading state
    showNotification('Verifying OTP...', 'info');

    // Simulate OTP verification (replace with actual implementation)
    setTimeout(() => {
        showNotification('Phone verification successful! Redirecting to booking...', 'success');
        setTimeout(() => {
            closeLoginModal();
            redirectToBooking();
        }, 1500);
    }, 2000);
}

// Guest Booking
function proceedAsGuest() {
    showNotification('Proceeding as guest...', 'info');
    setTimeout(() => {
        closeLoginModal();
        redirectToBooking();
    }, 1000);
}

// Redirect to welcome page after login
function redirectToBooking() {
    // Redirect to welcome page after successful login
    window.location.href = '/welcome';
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 3000;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;

    // Set background color based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b'
    };
    notification.style.backgroundColor = colors[type] || colors.info;

    // Add to DOM
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    return icons[type] || icons.info;
}

// Open booking modal
function openBookingModal(packageTitle = '', packageId = '') {
    const modal = document.getElementById('booking-modal');
    const destinationSelect = document.getElementById('destination');

    if (packageTitle && destinationSelect) {
        // Pre-fill destination if package is specified
        const destination = packageTitle.split(' ')[0]; // Get first word as destination
        destinationSelect.value = destination;
    }

    // Set minimum date to today
    const travelDateInput = document.getElementById('travel-date');
    if (travelDateInput) {
        const today = new Date().toISOString().split('T')[0];
        travelDateInput.min = today;
    }

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Close booking modal
function closeBookingModal() {
    const modal = document.getElementById('booking-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Handle booking form submission
async function handleBookingSubmission(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('../api/book.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Show success message
            showNotification('Booking submitted successfully! We will contact you soon.', 'success');
            closeBookingModal();
            event.target.reset();
        } else {
            throw new Error(result.message || 'Booking failed');
        }
    } catch (error) {
        console.error('Booking error:', error);
        showNotification('Failed to submit booking. Please try again.', 'error');
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Newsletter form
function initializeNewsletterForm() {
    const newsletterForm = document.getElementById('newsletter-form');

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const email = this.querySelector('input[type="email"]').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            submitBtn.disabled = true;

            try {
                // Simulate API call (replace with actual endpoint)
                await new Promise(resolve => setTimeout(resolve, 1000));

                showNotification('Successfully subscribed to our newsletter!', 'success');
                this.reset();
            } catch (error) {
                showNotification('Failed to subscribe. Please try again.', 'error');
            } finally {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
}

// Initialize animations
function initializeAnimations() {
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.feature-card, .testimonial-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });

    // Animate counters
    animateCounters();
}

// Animate counter numbers
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');

    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/\D/g, ''));
        const suffix = counter.textContent.replace(/\d/g, '');
        let current = 0;
        const increment = target / 100;

        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.textContent = Math.ceil(current) + suffix;
                setTimeout(updateCounter, 20);
            } else {
                counter.textContent = target + suffix;
            }
        };

        // Start animation when element is visible
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(counter);
    });
}

// Initialize testimonial slider
function initializeTestimonialSlider() {
    const testimonials = document.querySelectorAll('.testimonial-card');
    let currentTestimonial = 0;

    if (testimonials.length > 1) {
        setInterval(() => {
            testimonials[currentTestimonial].classList.remove('active');
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            testimonials[currentTestimonial].classList.add('active');
        }, 5000);
    }
}

// Utility functions
function scrollToPackages() {
    const packagesSection = document.getElementById('packages');
    if (packagesSection) {
        packagesSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    `;

    // Add to document
    document.body.appendChild(notification);

    // Close functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Image Slider Functions
function slideImage(packageId, direction) {
    const slider = document.querySelector(`[data-package-id="${packageId}"]`);
    const images = slider.querySelectorAll('.package-image');
    const dots = slider.parentElement.querySelectorAll('.dot');

    let currentIndex = 0;
    images.forEach((img, index) => {
        if (img.classList.contains('active')) {
            currentIndex = index;
        }
    });

    // Remove active class from current image and dot
    images[currentIndex].classList.remove('active');
    if (dots[currentIndex]) dots[currentIndex].classList.remove('active');

    // Calculate new index
    let newIndex = currentIndex + direction;
    if (newIndex >= images.length) newIndex = 0;
    if (newIndex < 0) newIndex = images.length - 1;

    // Add active class to new image and dot
    images[newIndex].classList.add('active');
    if (dots[newIndex]) dots[newIndex].classList.add('active');
}

function currentSlide(packageId, slideIndex) {
    const slider = document.querySelector(`[data-package-id="${packageId}"]`);
    const images = slider.querySelectorAll('.package-image');
    const dots = slider.parentElement.querySelectorAll('.dot');

    // Remove active class from all images and dots
    images.forEach(img => img.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    // Add active class to selected image and dot
    images[slideIndex].classList.add('active');
    if (dots[slideIndex]) dots[slideIndex].classList.add('active');
}

// Auto-slide functionality (optional)
function initializeAutoSlide() {
    const sliders = document.querySelectorAll('.package-image-slider');

    sliders.forEach(slider => {
        const packageId = slider.getAttribute('data-package-id');
        const images = slider.querySelectorAll('.package-image');

        if (images.length > 1) {
            setInterval(() => {
                slideImage(packageId, 1);
            }, 5000); // Auto-slide every 5 seconds
        }
    });
}

// Add notification styles to head
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
`;
document.head.appendChild(notificationStyles);

// Initialize auto-slide when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // ... existing initialization code ...
    initializeAutoSlide();
});
