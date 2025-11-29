<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-bold mb-2">Training Requests</h4>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Training ID</th>
            <th>Training Title</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Requested Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($trainingRequests ?? [] as $index => $r)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $r->training_title ?? 'N/A' }}</td>
              <td>{{ $r->reason ?? 'N/A' }}</td>
              <td>
                <span class="badge {{ ($r->status ?? 'Pending') == 'Approved' ? 'bg-success' : (($r->status ?? 'Pending') == 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                  {{ $r->status ?? 'Pending' }}
                </span>
              </td>
              <td>{{ $r->requested_date ?? now()->format('Y-m-d') }}</td>
              <td class="text-center">
                <button class="btn btn-outline-info btn-sm" onclick="viewDetails('{{ $r->training_title ?? 'N/A' }}')">
                  <i class="bi bi-eye"></i>
                </button>
                @if(($r->status ?? 'Pending') == 'Approved' || isset($r->course_id))
                  <a href="/employee/exam/start/{{ $r->course_id ?? 1 }}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-edit"></i> Take Exam
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">No training requests</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function viewDetails(title) {
  alert('Training: ' + title);
}
</script>