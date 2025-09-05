<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom">
    <h4 class="fw-bold mb-0">Training Notifications</h4>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Notification ID</th>
            <th>Message</th>
            <th>Sent At</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $uniqueNotifications = collect($notifications)->unique('notification_id');
          @endphp
          @forelse($uniqueNotifications as $n)
            <tr>
              <td>{{ $n->notification_id }}</td>
              <td>{{ $n->message }}</td>
              <td>{{ $n->sent_at }}</td>
              <td class="text-center">
                <button
                  class="btn btn-info btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#viewNotificationModal"
                  data-id="{{ $n->notification_id }}"
                  data-message="{{ $n->message }}"
                  data-sent="{{ $n->sent_at }}"
                ><i class="bi bi-eye"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted">No notifications</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>


{{-- View --}}
<div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-bold">Notification ID</label>
          <p class="form-control-plaintext" id="viewNotificationId"></p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Message</label>
          <p class="form-control-plaintext" id="viewNotificationMessage"></p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Sent At</label>
          <p class="form-control-plaintext" id="viewNotificationSent"></p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('viewNotificationModal')?.addEventListener('show.bs.modal', function (e) {
  const b = e.relatedTarget;
  const id = b.getAttribute('data-id');
  const message = b.getAttribute('data-message');
  const sent = b.getAttribute('data-sent');

  document.getElementById('viewNotificationId').textContent = id;
  document.getElementById('viewNotificationMessage').textContent = message;
  document.getElementById('viewNotificationSent').textContent = sent;
});
</script>

<script>
  // Remove all .modal-backdrop elements on page load and after any modal event
  function removeAllModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
      backdrop.remove();
    });
  }
  window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
  document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
  document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);
</script>
