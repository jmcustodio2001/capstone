// Agent Portal Script - Employee Dashboard Support
// This script provides additional functionality for the employee portal

(function() {
    'use strict';

    // Initialize agent portal functionality
    function initializeAgentPortal() {
        try {
            // Ensure global objects are available with comprehensive fallback
            if (typeof window.translationService === 'undefined') {
                console.warn('Translation service not found, initializing comprehensive fallback');
                window.translationService = {
                    translate: function(key, params) { return key; },
                    get: function(key, params) { return key; },
                    trans: function(key, params) { return key; },
                    choice: function(key, count, params) { return key; },
                    setLocale: function(locale) { return locale; },
                    getLocale: function() { return 'en'; },
                    has: function(key) { return true; },
                    translations: {},
                    setTranslations: function(translations) { this.translations = translations; }
                };
                
                // Also set up other global references
                window.trans = window.translationService.translate;
                window.__ = window.translationService.translate;
                window.app = window.app || {
                    locale: 'en',
                    fallback_locale: 'en',
                    translationService: window.translationService
                };
                window.Laravel = window.Laravel || {};
                window.Laravel.translationService = window.translationService;
            }

            // Initialize portal features
            initializeNotifications();
            initializeQuickActions();
            initializeProgressTracking();
            
            console.log('Agent portal script initialized successfully');
        } catch (error) {
            console.error('Error initializing agent portal:', error);
        }
    }

    // Initialize notification system
    function initializeNotifications() {
        // Auto-refresh notifications every 5 minutes
        setInterval(function() {
            refreshNotifications();
        }, 5 * 60 * 1000);
    }

    // Initialize quick actions
    function initializeQuickActions() {
        // Add keyboard shortcuts for quick actions
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'l':
                        e.preventDefault();
                        document.querySelector('[data-bs-target="#leaveApplicationModal"]')?.click();
                        break;
                    case 'p':
                        e.preventDefault();
                        if (typeof viewPayslip === 'function') {
                            viewPayslip();
                        }
                        break;
                }
            }
        });
    }

    // Initialize progress tracking
    function initializeProgressTracking() {
        // Animate progress bars on page load
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(function(bar) {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(function() {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = width;
            }, 100);
        });
    }

    // Refresh notifications
    function refreshNotifications() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) return;

        fetch('/employee/notifications/refresh', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications) {
                updateNotificationDisplay(data.notifications);
            }
        })
        .catch(error => {
            console.log('Notification refresh failed (non-critical):', error);
        });
    }

    // Update notification display
    function updateNotificationDisplay(notifications) {
        const notificationContainer = document.querySelector('.list-group-flush');
        if (!notificationContainer) return;

        if (notifications.length === 0) {
            notificationContainer.innerHTML = '<p class="text-muted mb-0">No new notifications</p>';
            return;
        }

        let html = '';
        notifications.forEach(function(notification) {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom py-3">
                    <div>
                        <p class="mb-0">${notification.message}</p>
                        <small class="text-muted">${notification.time_ago}</small>
                    </div>
                    <span class="badge bg-primary rounded-pill">New</span>
                </div>
            `;
        });
        notificationContainer.innerHTML = html;
    }

    // Initialize when DOM is ready with error handling
    function safeInitialize() {
        try {
            initializeAgentPortal();
        } catch (error) {
            console.error('Error initializing agent portal:', error);
            // Continue execution even if initialization fails
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInitialize);
    } else {
        safeInitialize();
    }

})();