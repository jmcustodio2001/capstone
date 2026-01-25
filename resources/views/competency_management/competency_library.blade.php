<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels Admin</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard-style.css') }}">
  <style>
    .bg-orange {
      background-color: #ff8c00 !important;
    }
    .text-orange {
      color: #ff8c00 !important;
    }
    
    /* Professional list styling */
    .competency-row {
      border-left: 3px solid transparent;
    }
    
    /* Custom category colors */
    .bg-purple {
      background-color: #6f42c1 !important;
    }
    
    .bg-pink {
      background-color: #e83e8c !important;
    }
    
    /* Progress bar animations */
    .progress-bar {
      transition: width 0.6s ease;
    }
    
    /* Button group styling */
    .btn-group .btn {
      border-radius: 0.375rem !important;
      margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
      margin-right: 0;
    }
    
    /* Star rating styling */
    .bi-star-fill, .bi-star {
      font-size: 0.9rem;
    }
    
    /* Empty state styling */
    .bi-inbox {
      opacity: 0.3;
    }
    
    /* Professional table styling */
    .table {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .table th {
      background-color: #f8f9fa;
      color: #495057;
      border: none;
      font-weight: 600;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 1rem 0.75rem;
    }
    
    .table td {
      vertical-align: middle;
      padding: 1rem 0.75rem;
      border-color: #e9ecef;
    }
    
    .table tbody tr {
      border-bottom: 1px solid #f1f3f4;
    }
    
    .category-badge {
      font-size: 0.8rem;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-weight: 500;
      text-transform: none;
      letter-spacing: normal;
    }
    
    .progress-sm {
      height: 8px;
      border-radius: 10px;
      background-color: #e9ecef;
    }
    
    .progress-sm .progress-bar {
      border-radius: 10px;
    }
    
    .star-rating {
      font-size: 0.9rem;
    }
    
    .competency-name {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }
    
    .competency-date {
      font-size: 0.8rem;
      color: #6c757d;
    }
    
    .row-number {
      background: none;
      color: #495057;
      font-weight: 700;
      font-size: 0.8rem;
      padding: 0.3rem 0.6rem;
      border-radius: 15px;
    }
    
    .description-text {
      color: #495057;
      line-height: 1.4;
    }
    
    .progress-percentage {
      font-weight: 700;
      font-size: 0.9rem;
    }

    /* Modern Modal Styling */
    .modal-content {
      border: none;
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.15);
      overflow: hidden;
    }

    .modal-header {
      background: linear-gradient(135deg, #a8d0f0 0%, #d6e8f5 100%);
      color: #2c5282;
      border: none;
      padding: 2rem 2rem 1rem;
      position: relative;
    }

    .modal-header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: rgba(255,255,255,0.2);
    }

    .modal-title {
      font-weight: 700;
      font-size: 1.5rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .modal-icon {
      width: 40px;
      height: 40px;
      background: rgba(44, 82, 130, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }

    .btn-close {
      background: rgba(44,82,130,0.2) !important;
      border-radius: 50% !important;
      width: 40px !important;
      height: 40px !important;
      opacity: 1 !important;
      border: none !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: all 0.3s ease !important;
      position: relative !important;
    }


    .btn-close:focus {
      box-shadow: 0 0 0 3px rgba(44,82,130,0.3) !important;
    }

    .btn-close::before {
      content: '×' !important;
      font-size: 24px !important;
      font-weight: bold !important;
      color: #2c5282 !important;
      line-height: 1 !important;
    }

    .modal-body {
      padding: 2rem;
      background: #fafafa;
    }

    .form-floating {
      margin-bottom: 1.5rem;
    }

    .form-floating > .form-control {
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 1rem 0.75rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
    }

    .form-floating > .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-floating > label {
      color: #a0aec0;
      font-weight: 500;
    }

    .form-select {
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 1rem 0.75rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
    }

    .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-footer {
      background: white;
      border: none;
      padding: 1.5rem 2rem 2rem;
      gap: 1rem;
    }

    .btn-modern {
      padding: 0.75rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      border: none;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn-modern::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }


    .btn-primary.btn-modern {
      background: linear-gradient(135deg, #a8d0f0 0%, #d6e8f5 100%);
      color: #2c5282;
    }

    .btn-secondary.btn-modern {
      background: #e2e8f0;
      color: #4a5568;
    }

  </style>
</head>
<body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Competency Library</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Here's your Competency Library.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Competency Library</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Position List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
        <h4 class="fw-bold mb-0 text-primary"></i>Position List</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="fw-bold" style="width: 5%;">ID</th>
                <th class="fw-bold" style="width: 10%;">DEPARTMENT</th>
                <th class="fw-bold" style="width: 25%;">POSITION DESCRIPTION</th>
                <th class="fw-bold" style="width: 35%;">POSITION QUALIFICATION</th>
                <th class="fw-bold" style="width: 10%;">TYPE</th>
                <th class="fw-bold" style="width: 15%;">ARRANGEMENT</th>
              </tr>
            </thead>
            <tbody>
              @forelse($positions as $pos)
                <tr>
                  <td class="fw-bold text-secondary ps-3">{{ $loop->iteration }}</td>
                  <td class="fw-bold text-dark">
                    {{ $pos->department }}
                  </td>
                  <td>
                    <div class="fw-bold text-dark">{{ $pos->position_name }}</div>
                    <small class="text-muted d-block">{{ $pos->description }}</small>
                  </td>
                  <td>
                    @if($pos->qualification)
                      <span class="text-muted small">{{ $pos->qualification }}</span>
                    @else
                      @php
                        $compNames = [];
                        if ($pos->required_competencies && is_array($pos->required_competencies)) {
                            foreach($pos->required_competencies as $rc) {
                                $c = \App\Models\CompetencyLibrary::find($rc['competency_id']);
                                if ($c) $compNames[] = $c->competency_name;
                            }
                        }
                      @endphp
                      
                      @if(count($compNames) > 0)
                        <div class="d-flex flex-wrap gap-1 mb-1">
                          @foreach($compNames as $name)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.75rem;">{{ $name }}</span>
                          @endforeach
                        </div>
                      @endif
                      
                      @if($pos->min_experience_years > 0)
                        <div class="small">
                          <i class="bi bi-clock-history me-1 text-warning"></i>
                          <span class="text-muted">Min Experience:</span> 
                          <span class="fw-semibold text-dark">{{ $pos->min_experience_years }} years</span>
                        </div>
                      @else
                        <span class="text-muted small">No specific qualifications listed</span>
                      @endif
                    @endif
                  </td>
                  <td class="fw-medium">
                    {{ $pos->employment_type ?? 'Full-Time' }}
                  </td>
                  <td class="text-muted small">
                    {{ $pos->work_arrangement ?? 'On-site' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4">
                    <div class="text-muted">
                      <i class="bi bi-folder2-open display-4 d-block mb-2"></i>
                      No positions found in the organization.
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Competency List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header">
        <h4 class="fw-bold mb-0">Competency List</h4>
      </div>
      <div class="card-body p-0">
        @forelse($competencies as $index => $comp)
          @if($loop->first)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th width="5%" class="text-center">ID</th>
                    <th width="22%">Competency Name</th>
                    <th width="12%" class="text-center">Category</th>
                    <th width="28%">Description</th>
                    <th width="15%">Proficiency Level</th>
                    <th width="8%" class="text-center">Rating</th>
                  </tr>
                </thead>
                <tbody>
          @endif
                  @php
                    // Use single color for all categories (same as card header)
                    $effectiveCategory = $comp->category ?? '';
                    $colorClass = 'bg-primary'; // Single blue color for all badges
                    $progressPercent = ($comp->rate ?? 0) * 20;
                    $progressPercent = max(0, min(100, $progressPercent));
                    $percentClass = $progressPercent >= 80 ? 'text-success' : ($progressPercent >= 50 ? 'text-warning' : 'text-danger');
                  @endphp
                  <tr class="competency-row">
                    <td class="text-center">
                      <span class="row-number">{{ $competencies->firstItem() + $loop->index }}</span>
                    </td>
                    <td>
                      <div class="competency-name">{{ $comp->competency_name }}</div>
                    </td>
                    <td class="text-center">
                      <span class="badge category-badge {{ $colorClass }} text-white">
                        {{ $effectiveCategory }}
                      </span>
                    </td>
                    <td>
                      <div class="description-text" title="{{ $comp->description }}">
                        {{ Str::limit($comp->description, 85) }}
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center mb-2">
                        <span class="progress-percentage {{ $percentClass }} me-2">{{ $progressPercent }}%</span>
                      </div>
                      <div class="progress progress-sm">
                        <div class="progress-bar {{ $progressPercent >= 80 ? 'bg-success' : ($progressPercent >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                             data-progress-width="{{ $progressPercent }}" 
                             style="width: {{ $progressPercent }}%;">
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="star-rating mb-1">
                        @for($i = 1; $i <= 5; $i++)
                          @if($i <= ($comp->rate ?? 0))
                            <i class="bi bi-star-fill text-warning"></i>
                          @else
                            <i class="bi bi-star text-muted"></i>
                          @endif
                        @endfor
                      </div>
                      <div class="small text-muted fw-semibold">({{ $comp->rate ?? 0 }}/5)</div>
                    </td>
                  </tr>
          @if($loop->last)
                </tbody>
              </table>
            </div>
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
              {{ $competencies->links('pagination::bootstrap-5') }}
            </div>
          @endif
        @empty
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-inbox display-1 text-muted"></i>
            </div>
            <h5 class="text-muted mb-2">No Competencies Found</h5>
            <p class="text-muted mb-3">The competency library is currently empty.</p>
          </div>
        @endforelse
      </div>
    </div>
  </main>

  <!-- Add Competency Modal -->
  <div class="modal fade" id="addCompetencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" action="{{ route('admin.competency_library.store') }}">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <div class="modal-icon">
                <i class="bi bi-plus-circle-fill"></i>
              </div>
              Add New Competency
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" title="Close"></button>
          </div>
          <div class="modal-body">
            <div class="form-floating">
              <input id="add-competency-name" type="text" name="competency_name" class="form-control" placeholder="Competency Name" required>
              <label for="add-competency-name">Competency Name*</label>
            </div>
            <div class="form-floating">
              <textarea id="add-description" name="description" class="form-control" placeholder="Description" style="height: 100px"></textarea>
              <label for="add-description">Description</label>
            </div>
            <div class="mb-3">
              <label for="add-category" class="form-label fw-semibold text-muted mb-2">Category*</label>
              <select id="add-category" name="category" class="form-select" required>
                <option value="">Select Category</option>
                <option value="Technical">Technical</option>
                <option value="Behavioral">Behavioral</option>
                <option value="Leadership">Leadership</option>
                <option value="Communication">Communication</option>
                <option value="Management">Management</option>
                <option value="Functional">Functional</option>
              </select>
            </div>
            <div class="form-floating">
              <input id="add-rate" type="number" name="rate" class="form-control" placeholder="Proficiency Level" min="1" max="5" required>
              <label for="add-rate">Proficiency Level*</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-modern" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-modern">
              <i class="bi bi-save me-1"></i> Save Competency
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Competency Modal (Single Modal) -->
  <div class="modal fade" id="editCompetencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="editCompetencyForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <div class="modal-icon">
                <i class="bi bi-pencil-square"></i>
              </div>
              Edit Competency
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" title="Close"></button>
          </div>
          <div class="modal-body">
            <div class="form-floating">
              <input id="edit-competency-name" type="text" name="competency_name" class="form-control" placeholder="Competency Name" required>
              <label for="edit-competency-name">Competency Name*</label>
            </div>
            <div class="form-floating">
              <textarea id="edit-description" name="description" class="form-control" placeholder="Description" style="height: 100px"></textarea>
              <label for="edit-description">Description</label>
            </div>
            <div class="mb-3">
              <label for="edit-category" class="form-label fw-semibold text-muted mb-2">Category*</label>
              <select id="edit-category" name="category" class="form-select" required>
                <option value="">Select Category</option>
                <option value="Technical">Technical</option>
                <option value="Behavioral">Behavioral</option>
                <option value="Leadership">Leadership</option>
                <option value="Communication">Communication</option>
                <option value="Management">Management</option>
                <option value="Functional">Functional</option>
              </select>
            </div>
            <div class="form-floating">
              <input id="edit-rate" type="number" name="rate" class="form-control" placeholder="Proficiency Level" min="1" max="5" required>
              <label for="edit-rate">Proficiency Level*</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-modern" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-modern" id="updateCompetencyBtn">
              <i class="bi bi-arrow-repeat me-1"></i> Update Competency
            </button>
            <button type="button" class="btn btn-success" id="testDirectSubmit" style="display:none;">
              <i class="bi bi-bug me-1"></i> Test Direct Submit
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Initialize Bootstrap modals for add competency functionality only
      window.addCompetencyModal = new bootstrap.Modal(document.getElementById('addCompetencyModal'));

      // ========== COMPETENCY MANAGEMENT - AUTO-SYNC TO COURSES ==========
      // All competencies now automatically sync to course management
      // No manual edit/delete/notify actions needed

      // Function to submit the edit form
      function submitEditForm() {
        var form = $('#editCompetencyForm');
        var actionUrl = form.attr('action');

        // Validate that action URL is set
        if (!actionUrl || actionUrl === '') {
          alert('Error: Form action URL is not set properly.');
          return false;
        }

        // Submit form using AJAX
        $.ajax({
          url: actionUrl,
          type: 'POST',
          data: form.serialize(),
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            editCompetencyModal.hide();
            location.reload(); // Reload page to show updated data
          },
          error: function(xhr) {
            if (xhr.status === 401) {
              alert('Your session has expired. Please login again.');
              window.location.href = '/admin/login';
            } else {
              var errorMessage = 'Error updating competency.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              alert(errorMessage);
            }
          }
        });
      }

      // Function to show password verification for form submission
      function showPasswordVerificationForSubmit() {
        try {
          Swal.fire({
            title: 'Password Verification',
            text: 'Please enter your password to proceed with the update',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            inputAttributes: {
              autocapitalize: 'off',
              autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Verify',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
              if (!value) {
                return 'Password is required!';
              }
            }
          }).then((passwordResult) => {
            if (passwordResult.isConfirmed) {
              const password = passwordResult.value;

              // Verify password via AJAX
              $.ajax({
                url: '/admin/verify-password',
                type: 'POST',
                data: { password: password },
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                  if (response.success) {
                    // Proceed with form submission after successful verification
                    submitEditForm();
                  } else {
                    Swal.fire({
                      title: 'Verification Failed',
                      text: 'Incorrect password. Please try again.',
                      icon: 'error',
                      confirmButtonColor: '#dc3545'
                    });
                  }
                },
                error: function(xhr) {
                  Swal.fire({
                    title: 'Error',
                    text: 'An error occurred during verification. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                  });
                }
              });
            }
          });
        } catch (e) {
          console.error('Error showing password verification dialog:', e);
        }
      }

      // Form submission handler
      $('#editCompetencyForm').on('submit', function(e) {
        e.preventDefault();

        // Check if password is already verified in this session
        $.ajax({
          url: '/admin/check-password-verification',
          type: 'GET',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            if (response.verified) {
              // Password already verified in session, proceed with submission
              submitEditForm();
            } else {
              // Show password verification
              showPasswordVerificationForSubmit();
            }
          },
          error: function(xhr) {
            // If check fails, show password verification as fallback
            showPasswordVerificationForSubmit();
          }
        });
      });

      // ========== ADD COMPETENCY FUNCTIONALITY ==========
      // Apply progress bar widths for visual display
      document.querySelectorAll('.progress-bar[data-progress-width]').forEach(function(el){
        var w = el.getAttribute('data-progress-width');
        if (w !== null) {
          el.style.width = String(w) + '%';
        }
      });

      // Star rating input for add competency modal
      const addRateInput = document.getElementById('add-rate');
      if (addRateInput) {
        addRateInput.addEventListener('input', function() {
          const value = parseInt(this.value);
          if (value >= 1 && value <= 5) {
            // Visual feedback for rating selection
          }
        });
      }
    });
  </script>
</body>
</html>
