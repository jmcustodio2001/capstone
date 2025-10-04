/**
 * Employee Settings Form Fix
 * 
 * This script fixes the form submission issue by using AJAX instead of regular form submission
 * to prevent redirect to login page.
 * 
 * Usage: Include this script in the employee settings page
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Employee settings form fix loaded');
    
    const form = document.querySelector('#settings-form');
    const saveBtn = document.getElementById('save-changes-btn');
    
    if (!form || !saveBtn) {
        console.error('Form or save button not found');
        return;
    }

    // Override the save button click to use AJAX
    saveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        console.log('Save button clicked - using fix method');
        
        // Validate form first
        const passwordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (passwordInput && confirmPasswordInput) {
            const newPassword = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (newPassword && newPassword !== confirmPassword) {
                Swal.fire({
                    title: 'Password Mismatch',
                    text: 'Please ensure your confirm password matches the new password.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
        }

        // Show confirmation dialog
        Swal.fire({
            title: 'Save Changes?',
            text: 'Do you want to save these changes to your profile?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Save Changes!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                submitFormViaAjax();
            }
        });
    });

    function submitFormViaAjax() {
        // Show loading state
        Swal.fire({
            title: 'Saving Changes...',
            text: 'Please wait while we update your settings.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare form data
        const formData = new FormData(form);
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Submit via fetch API to fix endpoint
        fetch('/employee/settings/fix-save', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reload page to show updated data
                    window.location.reload();
                });
            } else {
                let errorMessage = data.message || 'An error occurred while saving your settings.';
                
                if (data.errors) {
                    errorMessage += '\n\nDetails:\n';
                    Object.keys(data.errors).forEach(field => {
                        errorMessage += `• ${data.errors[field].join(', ')}\n`;
                    });
                }
                
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            
            Swal.fire({
                title: 'Network Error',
                text: 'There was a problem connecting to the server. Please check your internet connection and try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    }

    // Debug function to check authentication status
    function checkAuthStatus() {
        fetch('/employee/debug-auth', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Auth status:', data);
        })
        .catch(error => {
            console.error('Auth check failed:', error);
        });
    }

    // Check auth status on page load
    checkAuthStatus();
});
