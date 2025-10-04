// Add this JavaScript to the destination_knowledge_training.blade.php file

// Sync Competency Profiles functionality
document.getElementById('syncCompetencyBtn').addEventListener('click', async function() {
  const button = this;
  const originalText = button.innerHTML;

  if (!confirm('This will sync all destination knowledge training records with competency profiles. Continue?')) {
    return;
  }

  try {
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch('/admin/destination-knowledge-training/sync-competency-profiles', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    const result = await response.json();

    if (response.ok && result.success) {
      // Show success message
      alert(result.message || 'Competency profiles synced successfully!');

      // Refresh the page to show updated data
      window.location.reload();
    } else {
      const errorMessage = result.message || `Server error: ${response.status} ${response.statusText}`;
      throw new Error(errorMessage);
    }
  } catch (error) {
    console.error('Sync competency profiles error:', error);
    alert('Error syncing competency profiles. Please try again: ' + error.message);

    // Restore button state
    button.disabled = false;
    button.innerHTML = originalText;
  }
});