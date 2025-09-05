<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels Admin</title>
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

    <!-- Competency List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Competency List</h4>
        <div class="d-flex gap-2">
          @if(Auth::guard('admin')->check() && strtoupper(Auth::guard('admin')->user()->role) === 'ADMIN')
            <button class="btn btn-primary" onclick="addCompetency()">
              <i class="bi bi-plus-lg me-1"></i> Add Competency
            </button>
          @else
            <span class="text-muted">Admin access required for management actions</span>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">ID</th>
                <th class="fw-bold">Competency Name</th>
                <th class="fw-bold">Description</th>
                <th class="fw-bold">Category</th>
                <th class="fw-bold">Proficiency</th>
                <th class="fw-bold">Created At</th>
                <th class="fw-bold">Updated At</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($competencies as $index => $comp)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $comp->competency_name }}</td>
                  <td>{{ Str::limit($comp->description, 50) }}</td>
                  <td>
                    @php
                      $categoryColors = [
                        'Technical' => 'bg-primary',
                        'Leadership' => 'bg-success',
                        'Communication' => 'bg-info',
                        'Behavioral' => 'bg-warning',
                        'Management' => 'bg-danger',
                        'Analytical' => 'bg-purple',
                        'Creative' => 'bg-pink',
                        'Strategic' => 'bg-dark'
                      ];
                      $effectiveCategory = $comp->category ?? '';
                      $colorClass = $categoryColors[$effectiveCategory] ?? 'bg-secondary';
                      $badgeClass = "badge {$colorClass} bg-opacity-10 text-" . str_replace('bg-', '', $colorClass);
                      $iconClass = '';
                    @endphp
                    <span class="{{ $badgeClass }}">
                      {{ $effectiveCategory }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      @php
                        $progressPercent = round((($comp->rate ?? 0)/5)*100);
                        // Ensure progress is within valid range
                        $progressPercent = max(0, min(100, $progressPercent));
                      @endphp
                      <div class="progress me-2" style="width: 80px; height: 20px;">
                        <div class="progress-bar {{ $progressPercent >= 80 ? 'bg-success' : ($progressPercent >= 50 ? 'bg-warning' : 'bg-danger') }}" data-progress-width="{{ $progressPercent }}"></div>
                      </div>
                      @php
                        $percentClass = $progressPercent >= 80 ? 'text-success' : ($progressPercent >= 50 ? 'text-warning' : 'text-danger');
                      @endphp
                      <span class="fw-semibold {{ $percentClass }}">{{ $progressPercent }}%</span>
                    </div>
                  </td>
                  <td>{{ $comp->created_at->format('M d, Y') }}</td>
                  <td>{{ $comp->updated_at->format('M d, Y') }}</td>
                  <td class="text-center">
                    @if(Auth::guard('admin')->check() && strtoupper(Auth::guard('admin')->user()->role) === 'ADMIN')
                      <button class="btn btn-outline-primary btn-sm me-1 edit-competency-btn"
                              data-id="{{ $comp->id }}"
                              data-name="{{ $comp->competency_name }}"
                              data-description="{{ $comp->description }}"
                              data-category="{{ $comp->category }}"
                              data-rate="{{ $comp->rate }}">
                        <i class="bi bi-pencil"></i> Edit
                      </button>
                      <button class="btn btn-outline-info btn-sm me-1 notify-course-btn"
                              data-id="{{ $comp->id }}"
                              data-name="{{ $comp->competency_name }}"
                              data-description="{{ $comp->description }}">
                        <i class="bi bi-bell"></i> Notify Course Mgmt
                      </button>
                      <form id="deleteForm{{ $comp->id }}" action="{{ route('admin.competency_library.destroy', $comp->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger btn-sm delete-competency-btn"
                                data-id="{{ $comp->id }}"
                                data-name="{{ $comp->competency_name }}">
                          <i class="bi bi-trash"></i> Delete
                        </button>
                      </form>
                    @else
                      <span class="text-muted small">Admin access required</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-info-circle-fill me-2"></i>No competencies found. Click "Add Competency" to create one.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Competency Modal -->
  <div class="modal fade" id="addCompetencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" action="{{ route('admin.competency_library.store') }}">
        @csrf
        <div class="modal-content">
          <div class="card-header modal-header">
            <h5 class="modal-title">Add New Competency</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="add-competency-name" class="form-label">Competency Name*</label>
              <input id="add-competency-name" type="text" name="competency_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="add-description" class="form-label">Description</label>
              <textarea id="add-description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label for="add-category" class="form-label">Category*</label>
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
            <div class="mb-3">
              <label for="add-rate" class="form-label">Proficiency Level*</label>
              <input id="add-rate" type="number" name="rate" class="form-control" min="1" max="5" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
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
          <div class="card-header modal-header">
            <h5 class="modal-title">Edit Competency</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="edit-competency-name" class="form-label">Competency Name*</label>
              <input id="edit-competency-name" type="text" name="competency_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="edit-description" class="form-label">Description</label>
              <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label for="edit-category" class="form-label">Category*</label>
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
            <div class="mb-3">
              <label for="edit-rate" class="form-label">Proficiency Level*</label>
              <input id="edit-rate" type="number" name="rate" class="form-control" min="1" max="5" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="updateCompetencyBtn">
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
      // Initialize Bootstrap modals
      const editCompetencyModal = new bootstrap.Modal(document.getElementById('editCompetencyModal'));
      window.addCompetencyModal = new bootstrap.Modal(document.getElementById('addCompetencyModal'));

      // ========== COMPETENCY EDIT FUNCTIONALITY ==========
      document.querySelectorAll('.edit-competency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const name = this.getAttribute('data-name');
          const description = this.getAttribute('data-description');
          const category = this.getAttribute('data-category');
          const rate = this.getAttribute('data-rate');

          console.log('Edit button clicked, Swal available:', typeof Swal);

          // Show SweetAlert confirmation
          try {
            Swal.fire({
              title: 'Edit Competency',
              text: 'Are you sure you want to edit this competency?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#007bff',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Yes, edit it!',
              cancelButtonText: 'Cancel'
            }).then((result) => {
              if (result.isConfirmed) {
                console.log('User confirmed edit, proceeding to password verification');
                // Show password verification before proceeding to edit
                showPasswordVerification(id, name, description, category, rate);
              }
            });
          } catch (e) {
            console.error('Error showing initial confirmation dialog:', e);
          }
        });
      });

      // Function to show password verification dialog
      function showPasswordVerification(id, name, description, category, rate) {
        try {
          Swal.fire({
            title: 'Password Verification',
            text: 'Please enter your password to proceed',
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
                    // Proceed to edit after successful verification
                    proceedToEdit(id, name, description, category, rate);
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

      // Function to proceed with editing after verification
      function proceedToEdit(id, name, description, category, rate) {
        // Debug logging
        console.log('Edit button clicked for ID:', id);
        console.log('Data attributes:', { id, name, description, category, rate });

        // Set form action URL
        const actionUrl = `/admin/competency-library/${id}`;
        const form = document.getElementById('editCompetencyForm');
        form.action = actionUrl;
        console.log('Form action set to:', actionUrl);
        console.log('Form element action attribute:', form.getAttribute('action'));

        // Populate form fields
        document.getElementById('edit-competency-name').value = name || '';
        document.getElementById('edit-description').value = description || '';
        document.getElementById('edit-category').value = category || '';
        document.getElementById('edit-rate').value = rate || '';

        // Verify form fields were populated
        console.log('Form fields populated:', {
          name: document.getElementById('edit-competency-name').value,
          description: document.getElementById('edit-description').value,
          category: document.getElementById('edit-category').value,
          rate: document.getElementById('edit-rate').value
        });

        // Show modal
        editCompetencyModal.show();
      }

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
      window.addCompetency = function() {
        console.log('Add competency button clicked, Swal available:', typeof Swal);

        // Show SweetAlert confirmation
        try {
          Swal.fire({
            title: 'Add New Competency',
            text: 'Are you sure you want to add a new competency?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, add it!',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              console.log('User confirmed add, proceeding to password verification');
              // Show password verification
              showPasswordVerificationForAdd();
            }
          });
        } catch (e) {
          console.error('Error showing initial confirmation dialog:', e);
        }
      }

      // Function to show password verification for add
      window.showPasswordVerificationForAdd = function() {
        try {
          Swal.fire({
            title: 'Password Verification',
            text: 'Please enter your password to proceed',
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
                    // Proceed to open add modal after successful verification
                    window.addCompetencyModal.show();
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

      // Star rating hover effect for add modal
      const addRateInput = document.getElementById('add-rate');
      if (addRateInput) {
        addRateInput.addEventListener('input', function() {
          const value = parseInt(this.value);
          if (value >= 1 && value <= 5) {
            // You could add visual feedback here if needed
          }
        });
      }

      // Star rating hover effect for edit modal
      const editRateInput = document.getElementById('edit-rate');
      if (editRateInput) {
        editRateInput.addEventListener('input', function() {
          const value = parseInt(this.value);
          if (value >= 1 && value <= 5) {
            // You could add visual feedback here if needed
          }
        });
      }

      // Apply progress widths without inline Blade in style attributes (avoids linter errors)
      document.querySelectorAll('.progress-bar[data-progress-width]').forEach(function(el){
        var w = el.getAttribute('data-progress-width');
        if (w !== null) {
          el.style.width = String(w) + '%';
        }
      });

      // ========== COURSE MANAGEMENT NOTIFICATION FUNCTIONALITY ==========
      document.querySelectorAll('.notify-course-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const name = this.getAttribute('data-name');
          const description = this.getAttribute('data-description');

          console.log('Notify course management button clicked for competency:', name);

          // Show SweetAlert confirmation
          Swal.fire({
            title: 'Notify Course Management',
            text: `Send notification to course management about active courses using "${name}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Send Notification',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              console.log('User confirmed notification for competency ID:', id);

              // Send AJAX request to notify course management
              $.ajax({
                url: `/admin/competency-library/${id}/notify-course-management`,
                type: 'POST',
                data: { competency_name: name, description: description },
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                  console.log('Notification sent successfully:', response);
                  Swal.fire({
                    title: 'Notification Sent',
                    text: 'Course management has been notified about active courses using this competency.',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                  });
                },
                error: function(xhr) {
                  console.error('Error sending notification:', xhr);
                  let errorMessage = 'Failed to send notification. Please try again.';
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                  }
                  Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                  });
                }
              });
            }
          });
        });
      });

      // ========== DELETE COMPETENCY FUNCTIONALITY ==========
      document.querySelectorAll('.delete-competency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const name = this.getAttribute('data-name');

          console.log('Delete button clicked for competency:', name);

          // Show SweetAlert confirmation
          Swal.fire({
            title: 'Delete Competency',
            text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              console.log('User confirmed deletion for competency ID:', id);

              // Show password verification
              showPasswordVerificationForDelete(id, name);
            }
          });
        });
      });

      // Function to show password verification for delete
      function showPasswordVerificationForDelete(id, name) {
        try {
          Swal.fire({
            title: 'Password Verification',
            text: 'Please enter your password to confirm deletion',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            inputAttributes: {
              autocapitalize: 'off',
              autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Verify & Delete',
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
                    // Proceed with deletion after successful verification
                    submitDeleteForm(id);
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

      // Function to submit the delete form
      function submitDeleteForm(id) {
        const form = document.getElementById('deleteForm' + id);
        if (form) {
          form.submit();
        } else {
          console.error('Delete form not found for ID:', id);
          Swal.fire({
            title: 'Error',
            text: 'Could not find the delete form. Please try again.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    });
  </script>
</body>
</html>
