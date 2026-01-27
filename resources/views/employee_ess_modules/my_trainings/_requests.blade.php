<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-bold mb-2">Training Requests</h4>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>Training ID</th>
            <th>Training Title</th>
            <th>Source</th>
            <th>Status</th>
            <th>Assigned Date</th>
            <th>Expiration Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($trainingRequests ?? [] as $index => $r)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $r->training_title ?? 'N/A' }}</td>
              <td>
                <span class="badge {{ ($r->source ?? 'N/A') == 'competency_gap' ? 'bg-info' : 'bg-secondary' }}">
                  {{ ucfirst(str_replace('_', ' ', $r->source ?? 'N/A')) }}
                </span>
              </td>
              <td>
                <span class="badge {{ 
                  ($r->status ?? 'Assigned') == 'Completed' ? 'bg-success' : 
                  (($r->status ?? 'Assigned') == 'In Progress' ? 'bg-warning text-dark' : 
                  (($r->status ?? 'Assigned') == 'Assigned' ? 'bg-info' : 'bg-secondary')) 
                }}">
                  {{ $r->status ?? 'Assigned' }}
                </span>
              </td>
              <td>{{ isset($r->assigned_date) ? \Carbon\Carbon::parse($r->assigned_date)->format('M d, Y') : 'N/A' }}</td>
              <td>
                @php
                  $endDate = isset($r->end_date) ? \Carbon\Carbon::parse($r->end_date) : null;
                  $daysLeft = $endDate ? $endDate->diffInDays(now(), false) : 0;
                  $isExpired = $endDate && $endDate->isPast();
                @endphp
                <span class="badge {{ $isExpired ? 'bg-danger' : ($daysLeft <= 7 ? 'bg-warning text-dark' : 'bg-success') }}">
                  {{ $endDate ? $endDate->format('M d, Y') : 'N/A' }}
                  @if($daysLeft > 0)
                    <small>({{ $daysLeft }} days)</small>
                  @elseif($isExpired)
                    <small>(Expired)</small>
                  @endif
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-outline-info btn-sm" onclick="viewTrainingRequest('{{ addslashes($r->training_title ?? 'N/A') }}', '{{ $r->source ?? 'N/A' }}', '{{ $r->status ?? 'Assigned' }}', '{{ isset($r->assigned_date) ? \Carbon\Carbon::parse($r->assigned_date)->format('M d, Y') : 'N/A' }}', '{{ isset($r->end_date) ? \Carbon\Carbon::parse($r->end_date)->format('M d, Y') : 'N/A' }}', '{{ addslashes($r->reason ?? 'N/A') }}')">
                  <i class="bi bi-eye"></i>
                </button>
                @if(isset($r->course_id) || isset($r->destination_training_id))
                  <a href="/employee/exam/start/{{ $r->course_id ?? $r->destination_training_id }}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-book"></i> Start Training
                  </a>
                @else
                  <button class="btn btn-secondary btn-sm" disabled title="No course linked yet">
                    <i class="fas fa-lock"></i> No Course
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No training requests</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function viewTrainingRequest(title, source, status, assignedDate, endDate, reason) {
  const sourceName = source.replace(/_/g, ' ').toUpperCase();
  let statusBadge = '';
  
  if (status === 'Completed') statusBadge = '<span class="badge bg-success">Completed</span>';
  else if (status === 'In Progress') statusBadge = '<span class="badge bg-warning text-dark">In Progress</span>';
  else if (status === 'Assigned') statusBadge = '<span class="badge bg-info">Assigned</span>';
  else statusBadge = `<span class="badge bg-secondary">${status}</span>`;

  Swal.fire({
    title: '<h4 class="fw-bold mb-0">Training Request Details</h4>',
    html: `
      <div class="text-start mt-3">
        <div class="mb-3">
          <label class="text-muted small fw-bold text-uppercase">Training Title</label>
          <div class="fw-bold text-primary" style="font-size: 1.1rem;">${title}</div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <label class="text-muted small fw-bold text-uppercase">Source</label>
            <div><span class="badge bg-light text-dark border">${sourceName}</span></div>
          </div>
          <div class="col-6">
            <label class="text-muted small fw-bold text-uppercase">Status</label>
            <div>${statusBadge}</div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <label class="text-muted small fw-bold text-uppercase">Assigned Date</label>
            <div class="fw-semibold"><i class="bi bi-calendar-check me-1 text-info"></i>${assignedDate}</div>
          </div>
          <div class="col-6">
            <label class="text-muted small fw-bold text-uppercase">Expiration Date</label>
            <div class="fw-semibold text-danger"><i class="bi bi-calendar-x me-1"></i>${endDate}</div>
          </div>
        </div>
        <div class="mb-0">
          <label class="text-muted small fw-bold text-uppercase">Reason / Notes</label>
          <div class="p-3 bg-light rounded text-muted italic" style="border-left: 4px solid #0d6efd; font-style: italic;">
            ${reason || 'No additional notes provided.'}
          </div>
        </div>
      </div>
    `,
    showCloseButton: true,
    showConfirmButton: true,
    confirmButtonText: 'Close',
    confirmButtonColor: '#0d6efd',
    customClass: {
      popup: 'rounded-4 shadow-lg border-0',
      confirmButton: 'btn btn-primary px-4 py-2'
    }
  });
}
</script>
