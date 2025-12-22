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
                <span class="badge {{ ($r->status ?? 'Assigned') == 'Assigned' ? 'bg-info' : 'bg-secondary' }}">
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
                <button class="btn btn-outline-info btn-sm" onclick="viewTrainingRequest('{{ $r->training_title ?? 'N/A' }}', '{{ $r->source ?? 'N/A' }}')">
                  <i class="bi bi-eye"></i>
                </button>
                @if(($r->source ?? '') == 'competency_gap')
                  <a href="/employee/exam/start/{{ $r->destination_training_id ?? 1 }}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-book"></i> Start Training
                  </a>
                @else
                  <a href="/employee/exam/start/{{ $r->course_id ?? 1 }}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-book"></i> Start Training
                  </a>
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
function viewTrainingRequest(title, source) {
  alert('Training: ' + title + '\nSource: ' + source);
}
</script>
