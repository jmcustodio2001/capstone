function attachAutoAssignHandlers() {
  document.querySelectorAll('.auto-assign-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const employeeId = this.getAttribute('data-employee-id');
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Assigning...';

      fetch(`/course_management/auto-assign/${employeeId}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        // Show toast notification
        showToast(data.message || 'Auto-assign complete');

        // Reset button state
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Auto-Assign Courses';

        // Handle redirect on success
        if (data.success && data.redirect) {
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 1500);
        } else {
          // Just refresh the table if no redirect
          refreshGapTable();
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Auto-assign failed. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Auto-Assign Courses';
      });
    });
  });
}
