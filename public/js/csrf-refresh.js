/**
 * CSRF Token Refresh System
 * Automatically refreshes CSRF tokens to prevent 419 Page Expired errors
 */

document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]');
    
    if (token) {
        // Set default CSRF token for all AJAX requests
        window.Laravel = {
            csrfToken: token.getAttribute('content')
        };
        
        // Update axios defaults if available
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }
        
        // Update jQuery AJAX defaults if available
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token.getAttribute('content')
                }
            });
        }
    }
    
    // Refresh CSRF token every 15 minutes to prevent expiration
    setInterval(function() {
        refreshCSRFToken();
    }, 15 * 60 * 1000); // 15 minutes
    
    // Refresh immediately on page load
    setTimeout(refreshCSRFToken, 2000);
    
    // Also refresh on user activity
    let lastActivity = Date.now();
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    
    activityEvents.forEach(function(event) {
        document.addEventListener(event, function() {
            lastActivity = Date.now();
        }, true);
    });
    
    // Check for inactivity and refresh token if user becomes active again
    setInterval(function() {
        const now = Date.now();
        const timeSinceLastActivity = now - lastActivity;
        
        // If user was inactive for more than 10 minutes and is now active
        if (timeSinceLastActivity < 1000 && timeSinceLastActivity > 0) {
            refreshCSRFToken();
        }
    }, 60 * 1000); // Check every minute
});

function refreshCSRFToken() {
    fetch('/csrf-refresh', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Handle both token formats: data.token and data.csrf_token
        const newToken = data.token || data.csrf_token;
        
        if (newToken) {
            // Update meta tag
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta) {
                tokenMeta.setAttribute('content', newToken);
            }
            
            // Update all CSRF input fields
            const csrfInputs = document.querySelectorAll('input[name="_token"]');
            csrfInputs.forEach(function(input) {
                input.value = newToken;
            });
            
            // Update global variables
            if (window.Laravel) {
                window.Laravel.csrfToken = newToken;
            }
            
            // Update axios defaults if available
            if (typeof axios !== 'undefined') {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
            }
            
            // Update jQuery AJAX defaults if available
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': newToken
                    }
                });
            }
            
            console.log('CSRF token refreshed successfully');
        }
    })
    .catch(error => {
        console.error('Failed to refresh CSRF token:', error);
        // Try to reload the page if CSRF refresh fails repeatedly
        if (error.message.includes('419') || error.message.includes('expired')) {
            console.warn('Session expired, reloading page...');
            window.location.reload();
        }
    });
}

// Export for manual use
window.refreshCSRFToken = refreshCSRFToken;
