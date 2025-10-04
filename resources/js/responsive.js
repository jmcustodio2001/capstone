// HR2ESS Responsive JavaScript Framework
document.addEventListener('DOMContentLoaded', function() {
    initializeResponsiveFeatures();
});

function initializeResponsiveFeatures() {
    createSidebarToggle();
    initializeMobileNavigation();
    initializeResponsiveTables();
    initializeTouchInteractions();
    handleOrientationChange();
    initializeSwipeGestures();
}

// Create mobile sidebar toggle button
function createSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    
    if (!sidebar) return;
    
    // Create toggle button
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
    toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
    
    // Insert toggle button
    document.body.insertBefore(toggleBtn, document.body.firstChild);
    
    // Create overlay
    let overlay = document.getElementById('overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'overlay';
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50';
        overlay.style.cssText = 'z-index: 1040; display: none;';
        document.body.appendChild(overlay);
    }
    
    // Toggle functionality
    toggleBtn.addEventListener('click', function() {
        const isOpen = sidebar.classList.contains('show');
        
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', closeSidebar);
    
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
    
    function openSidebar() {
        sidebar.classList.add('show');
        overlay.style.display = 'block';
        toggleBtn.innerHTML = '<i class="bi bi-x"></i>';
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.style.display = 'none';
        toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
        document.body.style.overflow = '';
    }
    
    // Auto-close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 767 && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
}

// Initialize mobile navigation enhancements
function initializeMobileNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        // Add touch feedback
        link.addEventListener('touchstart', function() {
            this.style.opacity = '0.7';
        });
        
        link.addEventListener('touchend', function() {
            this.style.opacity = '';
        });
        
        // Close sidebar when navigating on mobile
        link.addEventListener('click', function() {
            if (window.innerWidth <= 767) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');
                if (sidebar && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    overlay.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }
        });
    });
}

// Initialize responsive tables
function initializeResponsiveTables() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        if (!table.closest('.table-responsive-mobile')) {
            // Wrap table in responsive container
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive-mobile';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
        
        // Create mobile card view
        createMobileTableCards(table);
    });
}

// Create mobile card view for tables
function createMobileTableCards(table) {
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    const rows = table.querySelectorAll('tbody tr');
    
    const cardContainer = document.createElement('div');
    cardContainer.className = 'table-card-mobile';
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const card = document.createElement('div');
        card.className = 'card';
        
        const cardHeader = document.createElement('div');
        cardHeader.className = 'card-header';
        cardHeader.textContent = cells[0]?.textContent.trim() || 'Record';
        
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        
        cells.forEach((cell, index) => {
            if (index > 0 && headers[index]) {
                const row = document.createElement('div');
                row.className = 'row mb-2';
                row.innerHTML = `
                    <div class="col-5 fw-bold">${headers[index]}:</div>
                    <div class="col-7">${cell.innerHTML}</div>
                `;
                cardBody.appendChild(row);
            }
        });
        
        card.appendChild(cardHeader);
        card.appendChild(cardBody);
        cardContainer.appendChild(card);
    });
    
    table.parentNode.insertBefore(cardContainer, table.nextSibling);
}

// Initialize touch interactions
function initializeTouchInteractions() {
    // Add touch feedback to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        btn.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });
    
    // Add touch feedback to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        if (card.getAttribute('data-clickable') === 'true') {
            card.style.cursor = 'pointer';
            card.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            card.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        }
    });
}

// Handle orientation change
function handleOrientationChange() {
    window.addEventListener('orientationchange', function() {
        // Force recalculation of viewport height
        setTimeout(function() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }, 100);
    });
    
    // Initial calculation
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

// Initialize swipe gestures
function initializeSwipeGestures() {
    let startX = 0;
    let startY = 0;
    let endX = 0;
    let endY = 0;
    
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', function(e) {
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        
        handleSwipe();
    });
    
    function handleSwipe() {
        const deltaX = endX - startX;
        const deltaY = endY - startY;
        const minSwipeDistance = 50;
        
        // Only handle horizontal swipes
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            const sidebar = document.getElementById('sidebar');
            
            if (deltaX > 0 && startX < 50) {
                // Swipe right from left edge - open sidebar
                if (sidebar && !sidebar.classList.contains('show')) {
                    sidebar.classList.add('show');
                    document.getElementById('overlay').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            } else if (deltaX < 0 && sidebar && sidebar.classList.contains('show')) {
                // Swipe left - close sidebar
                sidebar.classList.remove('show');
                document.getElementById('overlay').style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    }
}

// Utility functions
function isMobile() {
    return window.innerWidth <= 767;
}

function isTablet() {
    return window.innerWidth >= 768 && window.innerWidth <= 991;
}

function isDesktop() {
    return window.innerWidth >= 992;
}

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Export functions for use in other scripts
window.ResponsiveUtils = {
    isMobile,
    isTablet,
    isDesktop,
    debounce,
    throttle
};
