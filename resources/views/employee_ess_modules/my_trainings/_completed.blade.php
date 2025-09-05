<div class="simulation-card card mb-4">
  <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">Completed Trainings</h4>
    <div class="d-flex gap-2">
      <!-- Search -->
      <input type="text" id="trainingSearch" class="form-control form-control-sm" placeholder="Search training...">
      <!-- Filter -->
      <select id="trainingFilter" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="Verified">Verified</option>
        <option value="Pending">Pending</option>
      </select>
      <!-- Export -->
      <a href="{{ route('employee.trainings.export.pdf') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-file-earmark-pdf"></i> PDF
      </a>
      <a href="{{ route('employee.trainings.export.excel') }}" class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Excel
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="trainingTable" class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Completed ID</th>
            <th>Training Title</th>
            <th>Completion Date</th>
            <th>Remarks</th>
            <th>Status</th>
            <th>Certificate</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $uniqueCompleted = collect($completed)->unique('completed_id');
          @endphp
          @forelse($uniqueCompleted as $c)
            <tr>
              <td>
                {{ $c->completed_id }}
                @if(isset($c->source) && $c->source != 'manual')
                  <small class="badge bg-info ms-1">System</small>
                @endif
              </td>
              <td>
                {{ $c->training_title }}
                @if(isset($c->progress) && $c->progress >= 100)
                  <small class="text-success ms-1">({{ $c->progress }}%)</small>
                @endif
              </td>
              <td>{{ $c->completion_date }}</td>
              <td>{{ $c->remarks }}</td>
              <td>
                @if($c->status == 'Verified')
                  <span class="badge bg-success">Verified</span>
                @else
                  <span class="badge bg-warning text-dark">Pending</span>
                @endif
              </td>
              <td>
                @php
                  // Check for certificate in training_record_certificate_tracking table
                  $employeeId = Auth::user()->employee_id;
                  $certificateRecord = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)
                    ->where(function($q) use ($c) {
                      // Match by course_id if available
                      if (isset($c->course_id)) {
                        $q->where('course_id', $c->course_id);
                      } else {
                        // Match by training title
                        $q->whereHas('course', function($subQ) use ($c) {
                          $subQ->where('course_title', 'LIKE', '%' . trim(str_replace('Training', '', $c->training_title)) . '%');
                        });
                      }
                    })
                    ->first();
                @endphp

                @if($certificateRecord && $certificateRecord->certificate_url)
                  {{-- Show certificate from tracking system --}}
                  <div class="d-flex gap-1 flex-wrap">
                    <a href="{{ route('certificates.view', $certificateRecord->id) }}" target="_blank" class="btn btn-sm btn-success">
                      <i class="bi bi-eye"></i> View
                    </a>
                    <a href="{{ route('certificates.download', $certificateRecord->id) }}" class="btn btn-sm btn-primary">
                      <i class="bi bi-download"></i> Download
                    </a>
                  </div>
                  <small class="text-success d-block mt-1">
                    <i class="bi bi-check-circle"></i> Certificate Available
                  </small>
                @elseif(!empty($c->certificate_path))
                  {{-- Show legacy certificate file --}}
                  <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#previewCertModal"
                            data-cert="{{ asset('storage/' . $c->certificate_path) }}">
                      <i class="bi bi-eye"></i> Preview
                    </button>
                    <a class="btn btn-success btn-sm" href="{{ route('employee.certificate.download', $c->completed_id) }}">
                      <i class="bi bi-download"></i> Download
                    </a>
                  </div>
                @else
                  {{-- No certificate available --}}
                  <div class="text-center">
                    <span class="text-muted small">
                      <i class="bi bi-file-x"></i> No Certificate
                    </span>
                    @if(isset($c->source) && $c->source != 'manual')
                      <br><small class="text-info">Auto-generation pending</small>
                    @endif
                  </div>
                @endif
              </td>
              <td class="text-center">
                @if(!isset($c->source) || $c->source == 'manual')
                  <!-- Edit - Only for manual entries -->
                  <button class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editCompletedModal"
                    data-id="{{ $c->completed_id }}"
                    data-title="{{ $c->training_title }}"
                    data-date="{{ $c->completion_date }}"
                    data-remarks="{{ $c->remarks }}"
                  ><i class="bi bi-pencil-square"></i></button>

                  <!-- Delete - Only for manual entries -->
                  <form action="{{ route('employee.my_trainings.destroy', $c->completed_id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this training?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                  </form>
                @else
                  <!-- System entries - View only -->
                  <span class="text-muted small">System Record</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No completed trainings</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addCompletedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('employee.my_trainings.store') }}" method="POST">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Add Completed Training</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
          <div class="mb-3"><label class="form-label">Training Title</label>
            <input type="text" name="training_title" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Completion Date</label>
            <input type="date" name="completion_date" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCompletedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="editCompletedForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Edit Completed Training</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="employee_id" value="{{ Auth::user()->employee_id }}">
          <div class="mb-3"><label class="form-label">Training Title</label>
            <input type="text" name="training_title" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Completion Date</label>
            <input type="date" name="completion_date" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Certificate Preview Modal --}}
<div class="modal fade" id="previewCertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Certificate Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <iframe id="certFrame" src="" width="100%" height="600px" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('editCompletedModal')?.addEventListener('show.bs.modal', function (e) {
  const b = e.relatedTarget;
  const id = b.getAttribute('data-id');
  const f = document.getElementById('editCompletedForm');
  f.action = "{{ url('employee/my_trainings') }}/" + id;

  f.querySelector('[name="training_title"]').value = b.getAttribute('data-title');
  f.querySelector('[name="completion_date"]').value = b.getAttribute('data-date');
  f.querySelector('[name="remarks"]').value = b.getAttribute('data-remarks') || '';
});

document.getElementById('previewCertModal')?.addEventListener('show.bs.modal', function (e) {
  const cert = e.relatedTarget.getAttribute('data-cert');
  document.getElementById('certFrame').src = cert;
});

// Simple search + filter
document.getElementById('trainingSearch')?.addEventListener('keyup', function () {
  let val = this.value.toLowerCase();
  document.querySelectorAll('#trainingTable tbody tr').forEach(tr => {
    tr.style.display = tr.innerText.toLowerCase().includes(val) ? '' : 'none';
  });
});

document.getElementById('trainingFilter')?.addEventListener('change', function () {
  let val = this.value.toLowerCase();
  document.querySelectorAll('#trainingTable tbody tr').forEach(tr => {
    if (!val || tr.innerText.toLowerCase().includes(val)) tr.style.display = '';
    else tr.style.display = 'none';
  });
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
