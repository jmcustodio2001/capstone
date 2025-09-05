<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels - Edit Profile Update</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --light-bg: #f8f9fa;
    }
    
    body {
      background-color: #f8f9fa !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .main-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      background: white;
    }
    
    .card-header-custom {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border-radius: 12px 12px 0 0 !important;
      padding: 1.5rem 2rem;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      border: 1px solid #ced4da;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
      border-color: var(--primary-color);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
    }
    
    .btn-secondary {
      background: #6c757d;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 600;
    }
    
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
    }
    
    .field-info {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      border-left: 4px solid var(--primary-color);
    }
    
    .current-value-box {
      background-color: #e3f2fd;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      border-left: 4px solid #2196f3;
    }
    
    .file-upload-area {
      border: 2px dashed #ced4da;
      border-radius: 8px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .file-upload-area:hover {
      border-color: var(--primary-color);
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    .file-upload-area.dragover {
      border-color: var(--primary-color);
      background-color: rgba(67, 97, 238, 0.1);
    }
  </style>
</head>
<body>
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-8">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="mb-1 fw-bold">Edit Profile Update</h2>
            <p class="text-muted mb-0">Modify your pending profile update request</p>
          </div>
          <a href="{{ route('employee.profile_updates.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Updates
          </a>
        </div>

        <!-- Main Form Card -->
        <div class="card main-card">
          <div class="card-header card-header-custom">
            <h4 class="mb-0">
              <i class="bi bi-pencil-square me-2"></i>Edit Profile Update Request
            </h4>
          </div>
          <div class="card-body p-4">
            <!-- Current Request Info -->
            <div class="current-value-box">
              <h6 class="mb-2">
                <i class="bi bi-info-circle me-2"></i>Current Request Details
              </h6>
              <div class="row">
                <div class="col-md-4">
                  <strong>Field:</strong> {{ ucwords(str_replace('_', ' ', $profileUpdate->field_name)) }}
                </div>
                <div class="col-md-4">
                  <strong>Current Value:</strong> {{ $profileUpdate->old_value }}
                </div>
                <div class="col-md-4">
                  <strong>Status:</strong> 
                  <span class="badge bg-warning">{{ ucfirst($profileUpdate->status) }}</span>
                </div>
              </div>
            </div>

            <form action="{{ route('employee.profile_updates.update', $profileUpdate) }}" method="POST" enctype="multipart/form-data" id="profileUpdateForm">
              @csrf
              @method('PUT')
              
              <!-- Field Selection -->
              <div class="mb-4">
                <label for="field_name" class="form-label">
                  <i class="bi bi-list-ul me-2"></i>Field to Update
                </label>
                <select name="field_name" id="field_name" class="form-select @error('field_name') is-invalid @enderror" required>
                  <option value="">Select field to update...</option>
                  <option value="phone_number" {{ (old('field_name', $profileUpdate->field_name) == 'phone_number') ? 'selected' : '' }}>Phone Number</option>
                  <option value="emergency_contact_name" {{ (old('field_name', $profileUpdate->field_name) == 'emergency_contact_name') ? 'selected' : '' }}>Emergency Contact Name</option>
                  <option value="emergency_contact_phone" {{ (old('field_name', $profileUpdate->field_name) == 'emergency_contact_phone') ? 'selected' : '' }}>Emergency Contact Phone</option>
                  <option value="emergency_contact_relationship" {{ (old('field_name', $profileUpdate->field_name) == 'emergency_contact_relationship') ? 'selected' : '' }}>Emergency Contact Relationship</option>
                  <option value="profile_picture" {{ (old('field_name', $profileUpdate->field_name) == 'profile_picture') ? 'selected' : '' }}>Profile Picture</option>
                </select>
                @error('field_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Text Input for New Value -->
              <div class="mb-4" id="text-input-section">
                <label for="new_value" class="form-label">
                  <i class="bi bi-pencil me-2"></i>New Value
                </label>
                <input type="text" name="new_value" id="new_value" class="form-control @error('new_value') is-invalid @enderror" 
                       value="{{ old('new_value', $profileUpdate->field_name !== 'profile_picture' ? $profileUpdate->new_value : '') }}" 
                       placeholder="Enter the new value">
                @error('new_value')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- File Input for Profile Picture -->
              <div class="mb-4 d-none" id="file-input-section">
                <label for="new_value_file" class="form-label">
                  <i class="bi bi-image me-2"></i>Profile Picture
                </label>
                @if($profileUpdate->field_name === 'profile_picture' && $profileUpdate->new_value)
                  <div class="current-image mb-3">
                    <p class="small text-muted mb-2">Current uploaded image:</p>
                    <img src="{{ asset('storage/' . $profileUpdate->new_value) }}" alt="Current Profile Picture" 
                         class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                  </div>
                @endif
                <div class="file-upload-area" id="fileUploadArea">
                  <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
                  <h5 class="text-muted">Drop your image here or click to browse</h5>
                  <p class="text-muted small mb-0">Supported formats: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
                  <input type="file" name="new_value_file" id="new_value_file" class="form-control mt-3 @error('new_value_file') is-invalid @enderror" 
                         accept="image/jpeg,image/png,image/jpg,image/gif" style="display: none;">
                </div>
                @error('new_value_file')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <!-- Reason -->
              <div class="mb-4">
                <label for="reason" class="form-label">
                  <i class="bi bi-chat-text me-2"></i>Reason for Update (Optional)
                </label>
                <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                          rows="3" placeholder="Please provide a reason for this update request...">{{ old('reason', $profileUpdate->reason) }}</textarea>
                @error('reason')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Info Box -->
              <div class="field-info">
                <h6 class="mb-2">
                  <i class="bi bi-info-circle me-2"></i>Important Information
                </h6>
                <ul class="mb-0 small">
                  <li>Only pending profile update requests can be edited</li>
                  <li>Changes will require re-approval from HR</li>
                  <li>You will be notified via email once your request is processed</li>
                  <li>Profile picture must be less than 2MB in size</li>
                </ul>
              </div>

              <!-- Submit Buttons -->
              <div class="d-flex gap-3 justify-content-end">
                <a href="{{ route('employee.profile_updates.index') }}" class="btn btn-secondary">
                  <i class="bi bi-x-lg me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-lg me-2"></i>Update Request
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const fieldSelect = document.getElementById('field_name');
      const textInputSection = document.getElementById('text-input-section');
      const fileInputSection = document.getElementById('file-input-section');
      const textInput = document.getElementById('new_value');
      const fileInput = document.getElementById('new_value_file');
      const fileUploadArea = document.getElementById('fileUploadArea');

      // Initialize form based on current selection
      toggleInputSections();

      // Toggle input sections based on field selection
      fieldSelect.addEventListener('change', function() {
        toggleInputSections();
      });

      function toggleInputSections() {
        if (fieldSelect.value === 'profile_picture') {
          textInputSection.classList.add('d-none');
          fileInputSection.classList.remove('d-none');
          textInput.removeAttribute('required');
          fileInput.setAttribute('required', 'required');
        } else {
          textInputSection.classList.remove('d-none');
          fileInputSection.classList.add('d-none');
          textInput.setAttribute('required', 'required');
          fileInput.removeAttribute('required');
        }
      }

      // File upload area click handler
      fileUploadArea.addEventListener('click', function() {
        fileInput.click();
      });

      // Drag and drop handlers
      fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
      });

      fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
      });

      fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
          fileInput.files = files;
          updateFileDisplay(files[0]);
        }
      });

      // File input change handler
      fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
          updateFileDisplay(this.files[0]);
        }
      });

      function updateFileDisplay(file) {
        const uploadArea = fileUploadArea;
        uploadArea.innerHTML = `
          <i class="bi bi-check-circle fs-1 text-success mb-3"></i>
          <h5 class="text-success">File Selected: ${file.name}</h5>
          <p class="text-muted small mb-0">Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
          <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="clearFile()">
            <i class="bi bi-x me-1"></i>Remove
          </button>
        `;
      }

      // Make clearFile function global
      window.clearFile = function() {
        fileInput.value = '';
        fileUploadArea.innerHTML = `
          <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
          <h5 class="text-muted">Drop your image here or click to browse</h5>
          <p class="text-muted small mb-0">Supported formats: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
        `;
      };

      // Form validation
      document.getElementById('profileUpdateForm').addEventListener('submit', function(e) {
        const fieldName = fieldSelect.value;
        const textValue = textInput.value.trim();
        const fileValue = fileInput.files.length;

        if (!fieldName) {
          e.preventDefault();
          alert('Please select a field to update.');
          return;
        }

        if (fieldName === 'profile_picture' && fileValue === 0) {
          e.preventDefault();
          alert('Please select a profile picture to upload.');
          return;
        }

        if (fieldName !== 'profile_picture' && !textValue) {
          e.preventDefault();
          alert('Please enter a new value for the selected field.');
          return;
        }
      });
    });
  </script>
</body>
</html>
