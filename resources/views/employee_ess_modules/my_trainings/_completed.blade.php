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
      <!-- Export buttons removed -->
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
              <td>
                @if($c->completion_date)
                  {{ \Carbon\Carbon::parse($c->completion_date)->format('M d, Y') }}
                  <small class="text-muted d-block">{{ \Carbon\Carbon::parse($c->completion_date)->format('h:i A') }}</small>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @if($c->remarks)
                  <div class="remarks-cell" style="max-width: 200px;">
                    <span class="remarks-text" title="{{ $c->remarks }}">
                      {{ Str::limit($c->remarks, 50) }}
                    </span>
                    @if(strlen($c->remarks) > 50)
                      <button class="btn btn-link btn-sm p-0 ms-1"
                              onclick="toggleRemarks(this)"
                              title="Show full remarks">
                        <i class="bi bi-three-dots"></i>
                      </button>
                    @endif
                  </div>
                @else
                  <span class="text-muted">No remarks</span>
                @endif
              </td>
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
                    <button class="btn btn-sm btn-primary" onclick="downloadCertificatePDF({{ $certificateRecord->id }})">
                      <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
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
                    <a class="btn btn-success btn-sm" href="{{ asset('storage/' . $c->certificate_path) }}" download>
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
                <!-- View Only - All completed trainings are read-only -->
                <span class="text-muted small">
                  <i class="bi bi-eye"></i> View Only
                </span>
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

<!-- html2pdf.js for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<!-- Hidden certificate preview for PDF generation - unified template -->
<div id="certificate-pdf-preview" style="display:none !important; background:#fff; width:10.5in; height:7.5in; margin:0 auto; border:8px solid #2d3a5a; border-radius:6px; position:relative; padding:25px; page-break-inside:avoid; box-sizing:border-box; flex-direction:column; justify-content:space-between; overflow:hidden;">
  <div style="position:absolute; top:15px; left:15px; right:15px; bottom:15px; border:2px solid #87ceeb; border-radius:3px; pointer-events:none;"></div>
  
  <div style="text-align:center; margin-bottom:15px; position:relative; z-index:2;">
    <div style="position:relative; display:inline-block; margin-bottom:10px;">
      <div style="width:60px; height:60px; margin:0 auto; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, #2d3a5a, #4a5568); border:3px solid #ffffff; box-shadow:0 4px 8px rgba(45, 58, 90, 0.3);">
        <img src="/assets/images/jetlouge_logo.png" alt="Jetlouge Logo" style="width:100%; height:100%; object-fit:cover;" onerror="this.parentElement.innerHTML='&lt;div style=&quot;color:white; font-size:20px; font-weight:bold;&quot;&gt;JT&lt;/div&gt;'">
      </div>
    </div>
    <div style="font-size:48px; font-weight:bold; color:#2d3a5a; margin-bottom:5px; letter-spacing:2px;">CERTIFICATE</div>
    <div style="font-size:16px; color:#2d3a5a; letter-spacing:1px; margin-bottom:8px; font-weight:300;">OF ACHIEVEMENT</div>
    <div style="font-size:12px; color:#2d3a5a; font-style:italic; margin-bottom:15px;">Excellence in Travel & Tourism Training</div>
  </div>
  
  <div style="text-align:center; flex:1; display:flex; flex-direction:column; justify-content:center; margin:15px 0; position:relative; z-index:2;">
    <div style="font-size:14px; color:#2d3a5a; margin-bottom:10px; line-height:1.2; font-weight:400;">This is to proudly certify that</div>
    
    <div id="pdf-certificate-name" style="font-size:48px; font-family:cursive; font-weight:bold; color:#2d3a5a; margin:10px 0; letter-spacing:1px;"></div>
    
    <div style="font-size:14px; color:#2d3a5a; margin-bottom:10px; line-height:1.2; font-weight:400;">has successfully completed the comprehensive training program and demonstrated exceptional proficiency in</div>
    
    <div id="pdf-certificate-course" style="background:#2196f3; color:white; padding:8px 25px; border-radius:5px; font-size:28px; font-weight:bold; margin:12px auto; display:inline-block;"></div>
    
    <div style="font-size:12px; color:#2d3a5a; margin:12px 0; font-weight:500;">Completed with distinction on <strong id="pdf-certificate-date"></strong></div>
  </div>
  
  <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:10px; position:relative; z-index:2;">
    <div style="text-align:center; flex:1; position:relative;">
      <div style="width:100px; height:1px; background:#2d3a5a; margin:0 auto 5px;"></div>
      <div style="font-weight:bold; font-size:12px; color:#2d3a5a; margin-bottom:2px;">John Mark Custodio</div>
      <div style="font-size:10px; color:#2d3a5a; font-style:italic;">Training Director</div>
    </div>
    <div style="text-align:center; flex:1; position:relative;">
      <div style="width:100px; height:1px; background:#2d3a5a; margin:0 auto 5px;"></div>
      <div style="font-weight:bold; font-size:12px; color:#2d3a5a; margin-bottom:2px;">Jetlouge Admin</div>
      <div style="font-size:10px; color:#2d3a5a; font-style:italic;">HR Manager</div>
    </div>
  </div>
  
  <div style="text-align:center; margin-top:15px; font-size:10px; color:#555;">
    Certificate ID: <span id="pdf-certificate-id"></span> &nbsp; | &nbsp; Issued: <span id="pdf-certificate-issued"></span>
  </div>
</div>

<script>
document.getElementById('previewCertModal')?.addEventListener('show.bs.modal', function (e) {
  const cert = e.relatedTarget.getAttribute('data-cert');
  document.getElementById('certFrame').src = cert;
});

// PDF Export for Certificate with actual employee data - matches training_record_certificate_tracking.blade.php
async function downloadCertificatePDF(certId) {
  try {
    // Fetch certificate data from server
    const response = await fetch(`/admin/training-record-certificate-tracking/${certId}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });

    if (!response.ok) {
      throw new Error('Failed to fetch certificate data');
    }

    const certificate = await response.json();
    
    // Use actual certificate data
    const certData = {
      name: certificate.employee_name || 'Unknown Employee',
      course: certificate.course_name || 'Unknown Course',
      date: certificate.formatted_date || 'Unknown Date',
      id: certificate.certificate_number || 'Unknown ID',
      issued: certificate.issued_date || 'Unknown Date'
    };
    
    // Update the hidden preview with actual data
    document.getElementById('pdf-certificate-name').innerText = certData.name;
    document.getElementById('pdf-certificate-course').innerText = certData.course;
    document.getElementById('pdf-certificate-date').innerText = certData.date;
    document.getElementById('pdf-certificate-id').innerText = certData.id;
    document.getElementById('pdf-certificate-issued').innerText = certData.issued;
    
    var certDiv = document.getElementById('certificate-pdf-preview');
    certDiv.style.display = 'flex';
    certDiv.style.setProperty('display', 'flex', 'important');
    
    var opt = {
      margin: 0.2,
      filename: `certificate_${certData.name.replace(/\s+/g, '_')}_${certData.id}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { 
        scale: 1.5, 
        useCORS: true,
        width: 1056, // 10.5 inches * 96 DPI
        height: 720,  // 7.5 inches * 96 DPI
        scrollX: 0,
        scrollY: 0
      },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };
    
    // Generate PDF and force direct download (bypass IDM)
    html2pdf().set(opt).from(certDiv).toPdf().get('pdf').then(function(pdf) {
      // Create blob and force download
      const blob = new Blob([pdf.output('blob')], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = opt.filename;
      link.style.display = 'none';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      certDiv.style.setProperty('display', 'none', 'important');
    });
    
  } catch (error) {
    console.error('PDF download error:', error);
    alert('Unable to download certificate PDF. Please try again.');
  }
}

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

// Toggle remarks expansion
function toggleRemarks(button) {
  const remarksCell = button.closest('.remarks-cell');
  const remarksText = remarksCell.querySelector('.remarks-text');
  const fullText = remarksText.getAttribute('title');
  const isExpanded = remarksText.classList.contains('expanded');

  if (isExpanded) {
    // Collapse
    remarksText.textContent = fullText.length > 50 ? fullText.substring(0, 50) + '...' : fullText;
    remarksText.classList.remove('expanded');
    button.innerHTML = '<i class="bi bi-three-dots"></i>';
    button.title = 'Show full remarks';
  } else {
    // Expand
    remarksText.textContent = fullText;
    remarksText.classList.add('expanded');
    button.innerHTML = '<i class="bi bi-dash"></i>';
    button.title = 'Show less';
  }
}
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
