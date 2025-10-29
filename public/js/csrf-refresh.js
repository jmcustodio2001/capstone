// CSRF Token Refresh System
// Prevents 419 Page Expired errors by automatically refreshing CSRF tokens

(function() {
    'use strict';

    let refreshInterval;
    let isRefreshing = false;

    // Initialize CSRF refresh system
    function initializeCSRFRefresh() {
        try {
            // Start periodic refresh (every 30 minutes)
            refreshInterval = setInterval(refreshCSRFToken, 30 * 60 * 1000);
            
            // Refresh on page visibility change (when user returns to tab)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden && !isRefreshing) {
                    refreshCSRFToken();
                }
            });

            console.log('CSRF refresh system initialized');
        } catch (error) {
            console.error('Error initializing CSRF refresh:', error);
        }
    }

    // Refresh CSRF token
    function refreshCSRFToken() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        
        const endpoints = [
            '/employee/csrf-token',
            '/csrf-token',
            '/sanctum/csrf-cookie'
        ];

        tryRefreshEndpoint(endpoints, 0);
    }

    // Try refresh endpoint
    function tryRefreshEndpoint(endpoints, index) {
        if (index >= endpoints.length) {
            console.warn('All CSRF refresh endpoints failed');
            isRefreshing = false;
            return;
        }

        fetch(endpoints[index], {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.csrf_token) {
                updateCSRFToken(data.csrf_token);
                console.log('CSRF token refreshed from:', endpoints[index]);
            } else {
                throw new Error('No CSRF token in response');
            }
        })
        .catch(error => {
            console.log('CSRF refresh failed for ' + endpoints[index] + ':', error.message);
            tryRefreshEndpoint(endpoints, index + 1);
        })
        .finally(() => {
            isRefreshing = false;
        });
    }

    // Update CSRF token in DOM and global objects
    function updateCSRFToken(newToken) {
        try {
            // Update meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }

            // Update Laravel global object
            if (window.Laravel) {
                window.Laravel.csrfToken = newToken;
            }

            // Update jQuery AJAX setup if available
            if (typeof $ !== 'undefined' && $.ajaxSetup) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': newToken
                    }
                });
            }

            // Update Axios defaults if available
            if (typeof axios !== 'undefined') {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
            }

            // Dispatch custom event for other scripts
            document.dispatchEvent(new CustomEvent('csrf-token-updated', {
                detail: { token: newToken }
            }));

        } catch (error) {
            console.error('Error updating CSRF token:', error);
        }
    }

    // Get current CSRF token
    function getCurrentCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    // Cleanup function
    function cleanup() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }

    // Expose public API
    window.CSRFRefresh = {
        refresh: refreshCSRFToken,
        getCurrentToken: getCurrentCSRFToken,
        cleanup: cleanup
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCSRFRefresh);
    } else {
        initializeCSRFRefresh();
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', cleanup);

})();