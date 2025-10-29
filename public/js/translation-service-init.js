// Translation Service Global Initialization
// This script MUST be loaded before any other JavaScript to prevent undefined errors

(function() {
    'use strict';
    
    // Force override any existing translation service to ensure consistency
    window.translationService = {
        translate: function(key, params) { 
            return key; 
        },
        get: function(key, params) { 
            return key; 
        },
        trans: function(key, params) { 
            return key; 
        },
        choice: function(key, count, params) { 
            return key; 
        },
        setLocale: function(locale) { 
            return locale; 
        },
        getLocale: function() { 
            return 'en'; 
        },
        has: function(key) { 
            return true; 
        },
        translations: {},
        setTranslations: function(translations) { 
            this.translations = translations; 
        }
    };

    // Initialize global translation function
    if (typeof window.trans === 'undefined') {
        window.trans = function(key, params) { 
            return key; 
        };
    }

    // Initialize app object with config
    if (typeof window.app === 'undefined') {
        window.app = {
            locale: 'en',
            fallback_locale: 'en',
            translationService: window.translationService
        };
    }

    // Make translationService globally accessible in multiple ways
    window.Laravel = window.Laravel || {};
    window.Laravel.translationService = window.translationService;
    
    // Also make it available as a global variable for compatibility
    window.__ = window.translationService.translate;
    
    // Ensure it's available in the global scope for all scripts
    if (typeof global !== 'undefined') {
        global.translationService = window.translationService;
    }

    // Make it available for any external libraries that might need it
    window.i18n = window.translationService;
    window.t = window.translationService.translate;
    
    // Additional coverage for potential external library access patterns
    window.locale = window.translationService;
    window.lang = window.translationService;
    
    // Prevent any potential undefined errors by creating defensive getters
    Object.defineProperty(window, 'translationService', {
        get: function() {
            return window._translationService || {
                translate: function(key) { return key; },
                get: function(key) { return key; },
                trans: function(key) { return key; }
            };
        },
        set: function(value) {
            window._translationService = value;
        },
        configurable: true
    });
    
    // Set the actual value
    window.translationService = window.translationService;

    console.log('Translation service initialized globally - v2.0 - Enhanced coverage');
})();