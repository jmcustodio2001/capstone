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
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Custom Certificate Card Styles -->
  <style>
    .certificate-card {
      transition: all 0.3s ease;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .certificate-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      border-color: #007bff;
    }

    .info-section {
      transition: all 0.2s ease;
    }

    .info-section:hover {
      transform: translateY(-2px);
    }

    .info-section .bg-light {
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }

    .info-section:hover .bg-light {
      border-color: #dee2e6;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .certificate-card .card-body {
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .certificate-card .row.g-4 {
      flex: 1;
    }

    .certificate-card .row.mt-4 {
      margin-top: auto !important;
    }

    @media (max-width: 1200px) {
      .col-xl-4 {
        flex: 0 0 50%;
        max-width: 50%;
      }
    }

    @media (max-width: 768px) {
      .col-lg-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }

      .certificate-card .card-header .d-flex {
        flex-direction: column;
        text-align: center;
      }

      .certificate-card .card-header .text-end {
        text-align: center !important;
        margin-top: 1rem;
      }

      .certificate-card .row .col-md-6 {
        margin-bottom: 1rem;
      }

      .certificate-card .d-flex.justify-content-end {
        justify-content: center !important;
      }
    }

    .badge {
      font-size: 0.75rem;
      padding: 0.5rem 0.75rem;
    }

    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<script>
  // Make admin email available to JS (null if not logged in)
  window.adminEmail = @json(Auth::guard('admin')->check() ? Auth::guard('admin')->user()->email : null);
</script>
</head>
<body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">
    <!-- Success/Error Messages -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(isset($errors) && is_object($errors) && $errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Certificate Tracking</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Manage employee training certificates here.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Certificate Tracking </li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Certificate Tracking Content -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Training Records</h4>
        <div class="d-flex gap-2">
          <button class="btn btn-success" onclick="autoGenerateWithConfirmation()">
            <i class="bi bi-magic me-1"></i> Generate Certificates
          </button>
          <button class="btn btn-info" onclick="previewCertificateTemplate()">
            <i class="bi bi-eye me-1"></i> Preview Template
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-4">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th scope="col">Employee</th>
                <th scope="col">Course / Training</th>
                <th scope="col">Date Completed</th>
                <th scope="col">Certificate No.</th>
                <th scope="col">Expiry</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($certificates as $certificate)
                @php
                  // Employee Data
                  $employee = $certificate->employee;
                  $firstName = $employee->first_name ?? 'Unknown';
                  $lastName = $employee->last_name ?? 'Employee';
                  $fullName = $firstName . ' ' . $lastName;
                  $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));

                  // Profile Pic
                  $profilePicUrl = null;
                  if ($employee && $employee->profile_picture) {
                      if (filter_var($employee->profile_picture, FILTER_VALIDATE_URL) || str_starts_with($employee->profile_picture, 'http')) {
                          $profilePicUrl = $employee->profile_picture;
                      } else {
                          $profilePicUrl = asset('storage/' . $employee->profile_picture);
                      }
                  }

                  $colors = ['007bff', '28a745', 'dc3545', 'ffc107', '6f42c1', 'fd7e14'];
                  $colorIndex = abs(crc32($certificate->employee_id ?? '0')) % count($colors);
                  $bgColor = $colors[$colorIndex];

                  if (!$profilePicUrl) {
                      $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) .
                                     "&size=100&background=" . $bgColor . "&color=ffffff&bold=true&rounded=true";
                  }

                  // Expiry Logic
                  $expiryDate = null;
                  if ($certificate->training_date) {
                      try {
                          $completionDate = \Carbon\Carbon::parse($certificate->training_date);
                          $course = $certificate->course ?? null;
                          $courseTitle = $course && $course->course_title ? strtolower($course->course_title) : '';

                          if (strpos($courseTitle, 'safety') !== false || strpos($courseTitle, 'security') !== false) {
                              $expiryDate = $completionDate->copy()->addYear();
                          } elseif (strpos($courseTitle, 'leadership') !== false || strpos($courseTitle, 'management') !== false) {
                              $expiryDate = $completionDate->copy()->addYears(3);
                          } elseif (strpos($courseTitle, 'technical') !== false || strpos($courseTitle, 'software') !== false) {
                              $expiryDate = $completionDate->copy()->addYears(2);
                          } elseif (strpos($courseTitle, 'destination') !== false || strpos($courseTitle, 'location') !== false) {
                              $expiryDate = $completionDate->copy()->addMonths(18);
                          } else {
                              $expiryDate = $completionDate->copy()->addYears(2);
                          }
                      } catch (\Exception $e) { $expiryDate = null; }
                  }

                  if (!$expiryDate && $certificate->certificate_expiry) {
                      try { $expiryDate = \Carbon\Carbon::parse($certificate->certificate_expiry); } catch (\Exception $e) {}
                  }

                  // Expiry Status
                  $expiryStatus = 'valid';
                  $expiryText = 'No expiry';
                  $expiryClass = 'text-muted';
                  if ($expiryDate) {
                      $now = \Carbon\Carbon::now();
                      $daysUntilExpiry = $now->diffInDays($expiryDate, false);

                      if ($daysUntilExpiry < 0) {
                          $expiryStatus = 'expired';
                          $expiryText = $expiryDate->format('M d, Y');
                          $expiryClass = 'text-danger fw-bold';
                      } elseif ($daysUntilExpiry <= 30) {
                          $expiryStatus = 'expiring-soon';
                          $expiryText = $expiryDate->format('M d, Y');
                          $expiryClass = 'text-warning fw-bold';
                      } else {
                          $expiryStatus = 'valid';
                          $expiryText = $expiryDate->format('M d, Y');
                          $expiryClass = 'text-success';
                      }
                  }
                @endphp
                <tr>
                  <!-- Employee Column -->
                  <td>
                    <div class="d-flex align-items-center">
                      <div>
                        <div class="fw-bold text-dark">{{ $fullName }}</div>
                        <small class="text-muted">ID: {{ $certificate->employee_id ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </td>

                  <!-- Course Column -->
                  <td>
                    <div class="fw-semibold text-primary">
                      @if($certificate->course && isset($certificate->course->course_title))
                        {{Str::limit($certificate->course->course_title, 30)}}
                      @elseif($certificate->course_id)
                        Course ID: {{ $certificate->course_id }}
                      @else
                        <span class="text-muted">No course</span>
                      @endif
                    </div>
                  </td>

                  <!-- Date Completed -->
                  <td>
                    @if($certificate->training_date)
                      {{ \Carbon\Carbon::parse($certificate->training_date)->format('M d, Y') }}
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>

                  <!-- Certificate Number -->
                  <td>
                    <span class="badge bg-light text-dark border">
                      {{ $certificate->certificate_number ?? 'N/A' }}
                    </span>
                  </td>

                  <!-- Expiry -->
                  <td>
                    <span class="{{ $expiryClass }} small">
                      {{ $expiryText }}
                    </span>
                  </td>

                  <!-- Status -->
                  <td>
                    @if(strtolower($certificate->status) == 'completed')
                      <span class="badge bg-success">Completed</span>
                    @elseif(strtolower($certificate->status) == 'pending')
                      <span class="badge bg-warning text-dark">Pending</span>
                    @else
                      <span class="badge bg-secondary">{{ ucfirst($certificate->status) }}</span>
                    @endif
                  </td>

                  <!-- Actions -->
                  <td>
                    <div class="btn-group btn-group-sm">
                      @if($certificate->certificate_url)
                        <a href="{{ route('certificates.view', $certificate->id) }}" target="_blank" class="btn btn-outline-primary" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <button class="btn btn-outline-success" onclick="downloadCertificatePDF({{ $certificate->id }})" title="Download">
                          <i class="bi bi-download"></i>
                        </button>
                      @else
                        @if($certificate->employee_id && $certificate->course_id)
                          <button class="btn btn-outline-warning" onclick="generateCertificateWithConfirmation('{{ $certificate->employee_id }}', '{{ $certificate->course_id }}', '{{ $certificate->id }}')" title="Generate">
                            <i class="bi bi-magic"></i>
                          </button>
                        @endif
                      @endif
                      <button class="btn btn-outline-primary" onclick="editCertificateWithConfirmation({{ $certificate->id }})" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-outline-danger" onclick="deleteCertificateWithConfirmation({{ $certificate->id }})" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="mb-3">
                      <i class="bi bi-file-earmark-text display-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Certificate Records Found</h5>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $certificates->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </div>
  </main>

  <!-- Old Bootstrap modals removed - replaced with SweetAlert2 -->

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- html2pdf.js for PDF export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <!-- Hidden certificate preview for PDF generation - matches _completed.blade.php design -->
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
    // Create certificate element dynamically for PDF generation - Professional Design
    function createCertificateElement(certData) {
      const div = document.createElement('div');
      div.style.cssText = `
        background: white;
        width: 1000px;
        height: 700px;
        padding: 0;
        border: 8px solid #2d3a5a;
        font-family: 'Times New Roman', serif;
        position: relative;
        box-sizing: border-box;
      `;

      div.innerHTML = `
        <!-- Inner light blue border -->
        <div style="position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; border: 2px solid #87ceeb; background: white;"></div>

        <!-- Logo Section -->
        <div style="position: absolute; top: 40px; left: 0; right: 0; text-align: center; z-index: 2;">
          <div style="width: 80px; height: 80px; background: #4285f4; margin: 0 auto; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: inline-flex; align-items: center; justify-content: center;">
            <div style="width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
              <div style="width: 30px; height: 30px; background: #4285f4; border-radius: 50%;"></div>
            </div>
          </div>
        </div>

        <!-- Title Section -->
        <div style="position: absolute; top: 140px; left: 0; right: 0; text-align: center; z-index: 2;">
          <h1 style="font-size: 48px; font-weight: bold; color: #2d3a5a; margin: 0 0 8px 0; letter-spacing: 3px; font-family: 'Times New Roman', serif;">CERTIFICATE</h1>
          <p style="font-size: 16px; color: #2d3a5a; margin: 0 0 5px 0; letter-spacing: 2px; font-weight: 400;">OF ACHIEVEMENT</p>
          <p style="font-size: 12px; color: #2d3a5a; font-style: italic; margin: 0;">Excellence in Travel & Tourism Training</p>
        </div>

        <!-- Content Section -->
        <div style="position: absolute; top: 250px; left: 40px; right: 40px; text-align: center; z-index: 2;">
          <p style="font-size: 16px; color: #2d3a5a; margin: 0 0 20px 0;">This is to proudly certify that</p>

          <h2 style="font-size: 42px; color: #2d3a5a; margin: 20px 0; font-family: 'Brush Script MT', cursive, serif; font-weight: bold; letter-spacing: 1px;">${certData.name}</h2>

          <p style="font-size: 16px; color: #2d3a5a; margin: 0 0 20px 0; line-height: 1.4;">has successfully completed the comprehensive training program and demonstrated exceptional proficiency in</p>

          <div style="background: #2196f3; color: white; padding: 12px 30px; border-radius: 8px; font-size: 24px; font-weight: bold; margin: 20px auto; display: inline-block; max-width: 500px;">${certData.course}</div>

          <p style="font-size: 14px; color: #2d3a5a; margin: 20px 0 0 0;">Completed with distinction on <strong>${certData.date}</strong></p>
        </div>

        <!-- Signature Section -->
        <div style="position: absolute; bottom: 80px; left: 60px; right: 60px; z-index: 2;">
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="text-align: center; width: 50%; vertical-align: top; padding: 0 20px;">
                <div style="border-top: 1px solid #2d3a5a; width: 150px; margin: 0 auto 8px;"></div>
                <div style="font-size: 14px; font-weight: bold; color: #2d3a5a; margin: 0 0 3px 0;">John Mark Custodio</div>
                <div style="font-size: 11px; color: #2d3a5a; font-style: italic; margin: 0;">Training Director</div>
              </td>
              <td style="text-align: center; width: 50%; vertical-align: top; padding: 0 20px;">
                <div style="border-top: 1px solid #2d3a5a; width: 150px; margin: 0 auto 8px;"></div>
                <div style="font-size: 14px; font-weight: bold; color: #2d3a5a; margin: 0 0 3px 0;">Jetlouge Admin</div>
                <div style="font-size: 11px; color: #2d3a5a; font-style: italic; margin: 0;">HR Manager</div>
              </td>
            </tr>
          </table>
        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 25px; left: 40px; right: 40px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 8px; z-index: 2;">
          Certificate ID: ${certData.id} &nbsp; | &nbsp; Issued: ${certData.issued}
        </div>
      `;

      return div;
    }

    // PDF Export for Certificate with actual employee data - Clean approach like _completed.blade.php
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

    // Preview Certificate Template (shows the preview modal with consistent design)
    function previewCertificateTemplate() {
      // Use static demo data for preview
      var certData = {
        name: 'Sample Employee Name',
        course: 'Communication Skills Training',
        date: 'September 26, 2025',
        id: 'CERT-202509-001-8261',
        issued: 'Sep 26, 2025'
      };

      // Build the HTML for the preview using the properly contained layout
      var previewHtml = `
      <div style=\"background:#fff; width:10.5in; height:7.5in; margin:0 auto; border:8px solid #2d3a5a; border-radius:6px; position:relative; padding:25px; page-break-inside:avoid; box-sizing:border-box; display:flex; flex-direction:column; justify-content:space-between;\">
        <div style=\"position:absolute; top:15px; left:15px; right:15px; bottom:15px; border:2px solid #87ceeb; border-radius:3px; pointer-events:none;\"></div>

        <div style=\"text-align:center; margin-bottom:15px;\">
          <div style=\"width:50px; height:50px; margin:0 auto 10px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center;\">
            <img src=\"/assets/images/jetlouge_logo.png\" alt=\"Jetlouge Logo\" style=\"width:100%; height:100%; object-fit:cover;\" onerror=\"this.parentElement.innerHTML='<div style=&quot;background:#2d3a5a;width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;font-weight:bold;&quot;>JT</div>'\">
          </div>
          <div style=\"font-size:48px; font-weight:bold; color:#2d3a5a; margin-bottom:5px; letter-spacing:2px;\">CERTIFICATE</div>
          <div style=\"font-size:12px; color:#888; letter-spacing:1px; margin-bottom:15px;\">OF ACHIEVEMENT</div>
        </div>

        <div style=\"text-align:center; flex:1; display:flex; flex-direction:column; justify-content:center; margin:15px 0;\">
          <div style=\"font-size:14px; color:#2d3a5a; margin-bottom:10px; line-height:1.2;\">This is to proudly certify that</div>

          <div style=\"font-size:48px; font-family:cursive; font-weight:bold; color:#2d3a5a; margin:10px 0;\">${certData.name}</div>

          <div style=\"font-size:14px; color:#2d3a5a; margin-bottom:10px; line-height:1.2;\">has successfully completed the comprehensive training program and demonstrated exceptional proficiency in</div>

          <div style=\"background:#2196f3; color:white; padding:8px 25px; border-radius:5px; font-size:28px; font-weight:bold; margin:12px auto; display:inline-block;\">${certData.course}</div>

          <div style=\"font-size:12px; color:#2d3a5a; margin:12px 0;\">Completed with distinction on <strong>${certData.date}</strong></div>
        </div>

        <div style=\"display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:10px;\">
          <div style=\"text-align:center; flex:1;\">
            <div style=\"font-weight:bold; font-size:12px; color:#2d3a5a; margin-bottom:2px;\">John Mark Custodio</div>
            <div style=\"font-size:10px; color:#888;\">Training Director</div>
          </div>

          <div style=\"text-align:center; flex:1;\">
            <div style=\"font-weight:bold; font-size:12px; color:#2d3a5a; margin-bottom:2px;\">Jetlouge Admin</div>
            <div style=\"font-size:10px; color:#888;\">HR Manager</div>
          </div>
        </div>

        <div style=\"text-align:center; margin-top:15px; font-size:10px; color:#555;\">
          Certificate ID: ${certData.id} &nbsp; | &nbsp; Issued: ${certData.issued}
        </div>
      </div>
      `;

      Swal.fire({
        title: 'Certificate Template Preview',
        html: previewHtml,
        width: 1200,
        showCloseButton: true,
        showCancelButton: false,
        showConfirmButton: false
      });
    }
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Auto-dismiss alerts after 5 seconds
      setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        });
      }, 5000);
    });

    // ===== SWEETALERT FUNCTIONS =====

    // Add Certificate without Password Confirmation
    async function addCertificateWithConfirmation() {
      // Directly show the add certificate form without password verification
      showAddCertificateForm('');
    }

    // Show Add Certificate Form
    async function showAddCertificateForm(password) {
      const { value: formData } = await Swal.fire({
        title: '📋 Add New Certificate Record',
        html: `
          <form id="addCertificateForm" class="text-start">
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Employee*</label>
                <select class="form-select" name="employee_id" required>
                  <option value="">Select Employee</option>
                  @foreach($employees as $employee)
                    <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }} (ID: {{ $employee->employee_id }})</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Course*</label>
                <select class="form-select" name="course_id" required>
                  <option value="">Select Course</option>
                  @foreach($courses as $course)
                    <option value="{{ $course->course_id }}">{{ $course->course_title }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Certificate File*</label>
              <input type="file" class="form-control" name="certificate_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" required>
              <div class="form-text">Accepted formats: PDF, PNG, JPG, JPEG, DOC, DOCX (Max: 10MB)</div>
            </div>
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Completion Date*</label>
                <input type="date" class="form-control" name="training_date" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Status*</label>
                <select class="form-select" name="status" required>
                  <option value="Pending Examination">Pending Examination</option>
                  <option value="Completed">Completed</option>
                  <option value="Pending">Pending</option>
                  <option value="Expired">Expired</option>
                </select>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Certificate Number*</label>
                <input type="text" class="form-control" name="certificate_number" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Expiry Date*</label>
                <input type="date" class="form-control" name="certificate_expiry" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Remarks</label>
              <textarea class="form-control" name="remarks" rows="3" placeholder="Optional remarks or notes"></textarea>
            </div>
          </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-save me-1"></i> Save Record',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: '800px',
        preConfirm: () => {
          const form = document.getElementById('addCertificateForm');
          const formData = new FormData(form);

          // Validate required fields
          const requiredFields = ['employee_id', 'course_id', 'certificate_file', 'training_date', 'status', 'certificate_number', 'certificate_expiry'];
          for (let field of requiredFields) {
            if (!formData.get(field)) {
              Swal.showValidationMessage(`Please fill in the ${field.replace('_', ' ')} field`);
              return false;
            }
          }

          // Validate file
          const file = formData.get('certificate_file');
          if (file && file.size > 10 * 1024 * 1024) {
            Swal.showValidationMessage('File size must be less than 10MB');
            return false;
          }

          return formData;
        }
      });

      if (formData) {
        submitCertificateForm(formData, 'store');
      }
    }

    // Submit Certificate Form
    async function submitCertificateForm(formData, action) {
      const url = action === 'store' ?
        '{{ route("training_record_certificate_tracking.store") }}' :
        '{{ route("training_record_certificate_tracking.update", ":id") }}'.replace(':id', formData.get('certificate_id'));

      if (action === 'update') {
        formData.append('_method', 'PUT');
      }

      try {
        Swal.fire({
          title: 'Processing...',
          html: 'Saving certificate record, please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: formData
        });

        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: `Certificate record ${action === 'store' ? 'added' : 'updated'} successfully!`,
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          const errorText = await response.text();
          throw new Error(`Server error: ${response.status}`);
        }
      } catch (error) {
        console.error('Form submission error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Submission Failed',
          text: 'Unable to save certificate record. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // View Certificate Details function removed - no longer needed

    // Edit Certificate without Password Confirmation
    async function editCertificateWithConfirmation(certificateId) {
      // Directly show the edit certificate form without password verification
      showEditCertificateForm(certificateId, '');
    }

    // Show Edit Certificate Form
    async function showEditCertificateForm(certificateId, password) {
      try {
        // Fetch current certificate data
        const response = await fetch(`/admin/training-record-certificate-tracking/${certificateId}`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const certificate = await response.json();

        // Validate that we received valid certificate data
        if (!certificate || !certificate.id) {
          throw new Error('Invalid certificate data received from server');
        }

        const { value: formData } = await Swal.fire({
          title: '✏️ Edit Certificate Record',
          html: `
            <form id="editCertificateForm" class="text-start">
              <div class="row g-3">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Employee*</label>
                  <select class="form-select" name="employee_id" id="edit-employee-select" required>
                    @foreach($employees as $employee)
                      <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }} (ID: {{ $employee->employee_id }})</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Course*</label>
                  <select class="form-select" name="course_id" id="edit-course-select" required>
                    @foreach($courses as $course)
                      <option value="{{ $course->course_id }}">{{ $course->course_title }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Certificate File</label>
                <input type="file" class="form-control" name="certificate_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                <div class="form-text">Leave empty to keep current file. Accepted formats: PDF, PNG, JPG, JPEG, DOC, DOCX (Max: 10MB)</div>
                ${certificate.certificate_url ? `<small class="text-muted">Current: <a href="${certificate.certificate_url}" target="_blank">View Certificate</a></small>` : ''}
              </div>
              <div class="row g-3">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Completion Date*</label>
                  <input type="date" class="form-control" name="training_date" value="${certificate.training_date || ''}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Status*</label>
                  <select class="form-select" name="status" required>
                    <option value="Pending Examination" ${certificate.status === 'Pending Examination' ? 'selected' : ''}>Pending Examination</option>
                    <option value="Completed" ${certificate.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    <option value="Pending" ${certificate.status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="Expired" ${certificate.status === 'Expired' ? 'selected' : ''}>Expired</option>
                  </select>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Certificate Number*</label>
                  <input type="text" class="form-control" name="certificate_number" value="${certificate.certificate_number || ''}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Expiry Date*</label>
                  <input type="date" class="form-control" name="certificate_expiry" value="${certificate.certificate_expiry || ''}" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Remarks</label>
                <textarea class="form-control" name="remarks" rows="3" placeholder="Optional remarks or notes">${certificate.remarks || ''}</textarea>
              </div>
              <input type="hidden" name="certificate_id" value="${certificateId}">
            </form>
          `,
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> Update Record',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#0d6efd',
          cancelButtonColor: '#6c757d',
          width: '800px',
          didOpen: () => {
            // Set the selected values after the modal opens
            const employeeSelect = document.getElementById('edit-employee-select');
            const courseSelect = document.getElementById('edit-course-select');

            if (employeeSelect && certificate.employee_id) {
              employeeSelect.value = certificate.employee_id;
            }

            if (courseSelect && certificate.course_id) {
              courseSelect.value = certificate.course_id;
            }
          },
          preConfirm: () => {
            const form = document.getElementById('editCertificateForm');
            const formData = new FormData(form);

            // Validate required fields
            const requiredFields = ['employee_id', 'course_id', 'training_date', 'status', 'certificate_number', 'certificate_expiry'];
            for (let field of requiredFields) {
              if (!formData.get(field)) {
                Swal.showValidationMessage(`Please fill in the ${field.replace('_', ' ')} field`);
                return false;
              }
            }

            // Validate file if provided
            const file = formData.get('certificate_file');
            if (file && file.size > 0 && file.size > 10 * 1024 * 1024) {
              Swal.showValidationMessage('File size must be less than 10MB');
              return false;
            }

            return formData;
          }
        });

        if (formData) {
          submitCertificateForm(formData, 'update');
        }
      } catch (error) {
        console.error('Error loading certificate for edit:', error);
        let errorMessage = 'Unable to load certificate data for editing.';

        if (error.message.includes('404')) {
          errorMessage = 'Certificate record not found. It may have been deleted.';
        } else if (error.message.includes('403')) {
          errorMessage = 'Access denied. You do not have permission to edit this certificate.';
        } else if (error.message.includes('500')) {
          errorMessage = 'Server error occurred. Please try again or contact administrator.';
        } else if (error.message.includes('Failed to fetch')) {
          errorMessage = 'Network error. Please check your connection and try again.';
        }

        Swal.fire({
          icon: 'error',
          title: 'Error Loading Certificate',
          text: errorMessage,
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Delete Certificate without Password Confirmation
    async function deleteCertificateWithConfirmation(certificateId) {
      const result = await Swal.fire({
        title: '⚠️ Delete Certificate Record',
        html: `
          <div class="text-start">
            <p class="mb-3"><i class="bi bi-exclamation-triangle text-warning"></i> <strong>Warning:</strong></p>
            <p class="text-muted mb-3">You are about to permanently delete this certificate record. This action cannot be undone and will remove all associated data.</p>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Delete Record',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        width: '500px'
      });

      if (result.isConfirmed) {
        submitDeleteCertificate(certificateId);
      }
    }

    // Submit Delete Certificate
    async function submitDeleteCertificate(certificateId) {
      try {
        Swal.fire({
          title: 'Deleting...',
          html: 'Removing certificate record, please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch(`{{ route('training_record_certificate_tracking.destroy', ':id') }}`.replace(':id', certificateId), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            _method: 'DELETE'
          })
        });

        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Certificate record has been deleted successfully.',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(`Server error: ${response.status}`);
        }
      } catch (error) {
        console.error('Delete error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Delete Failed',
          text: 'Unable to delete certificate record. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Auto-Generate without Password Confirmation
    async function autoGenerateWithConfirmation() {
      const result = await Swal.fire({
        title: 'Auto-Generate Certificates',
        text: 'Generate certificates for all completed trainings without certificates?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
      });

      if (result.isConfirmed) {
        submitAutoGenerate();
      }
    }

    // Submit Auto-Generate
    async function submitAutoGenerate() {
      try {
        Swal.fire({
          title: 'Generating...',
          html: 'Auto-generating certificate records, please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const response = await fetch('{{ route("training_record_certificate_tracking.auto_generate") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Generation Complete!',
            text: 'Certificate records have been auto-generated successfully.',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(`Server error: ${response.status}`);
        }
      } catch (error) {
        console.error('Auto-generate error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Generation Failed',
          text: 'Unable to auto-generate certificate records. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      }
    }

    // Generate Certificate with Password Confirmation
    async function generateCertificateWithConfirmation(employeeId, courseId, certificateId) {
      if (!employeeId || !courseId) {
        Swal.fire({
          icon: 'error',
          title: 'Missing Information',
          text: 'Employee ID or Course ID is missing.',
          confirmButtonColor: '#dc3545'
        });
        return;
      }

      // Directly call generateCertificate without password verification
      generateCertificate(employeeId, courseId, certificateId);
    }

    // Original Certificate Generation Function (now password-protected)
    function generateCertificate(employeeId, courseId, certificateId) {
      if (!employeeId || !courseId) {
        Swal.fire({
          icon: 'error',
          title: 'Missing Information',
          text: 'Employee ID or Course ID is missing.',
          confirmButtonColor: '#dc3545'
        });
        return;
      }

      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';

      const requestData = {
        employee_id: employeeId,
        course_id: courseId,
        completion_date: new Date().toISOString().split('T')[0],
        certificate_expiry: new Date(Date.now() + 2 * 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] // 2 years from now
      };

      console.log('Certificate generation request:', {
        url: '/certificates/generate',
        method: 'POST',
        data: requestData,
        csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      });

      // Add timeout to the fetch request
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

      fetch('/certificates/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify(requestData),
        signal: controller.signal
      })
      .then(response => {
        clearTimeout(timeoutId);
        console.log('Certificate generation response:', {
          status: response.status,
          statusText: response.statusText,
          headers: Object.fromEntries(response.headers.entries()),
          ok: response.ok
        });

        // Handle different response types
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return response.json().then(data => ({ data, response }));
        } else {
          return response.text().then(text => {
            console.error('Non-JSON response received:', {
              status: response.status,
              statusText: response.statusText,
              contentType: contentType,
              body: text.substring(0, 500)
            });
            throw new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
          });
        }
      })
      .then(({ data, response }) => {
        console.log('Certificate generation result:', data);

        if (data && data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Certificate Generated!',
            text: 'AI certificate has been generated successfully.',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          const errorMsg = data && data.message ? data.message : 'Certificate generation failed';
          console.error('Certificate generation failed:', {
            success: data?.success,
            message: data?.message,
            fullResponse: data
          });

          // Check if it's a database error related to certificate_expiry
          let displayMessage = errorMsg;
          if (errorMsg.includes('certificate_expiry') && errorMsg.includes('default value')) {
            displayMessage = 'Database configuration issue detected. The certificate_expiry field needs a default value. Please contact your administrator to fix the database table structure.';
          }

          Swal.fire({
            icon: 'error',
            title: 'Generation Failed',
            text: 'Failed: ' + displayMessage,
            confirmButtonColor: '#dc3545'
          });
        }
      })
      .catch(error => {
        clearTimeout(timeoutId);
        console.error('Certificate generation error:', {
          message: error.message,
          stack: error.stack,
          name: error.name
        });

        let errorMessage = 'Certificate generation failed';

        if (error.name === 'AbortError') {
          errorMessage = 'Certificate generation timed out - please try again';
        } else if (error.message.includes('500')) {
          if (error.message.includes('certificate_expiry') && error.message.includes('default value')) {
            errorMessage = 'Database configuration issue: The certificate_expiry field needs a default value. Please contact your administrator to fix the database table structure.';
          } else {
            errorMessage = 'Server error - please check server logs for details';
          }
        } else if (error.message.includes('404')) {
          errorMessage = 'Certificate generation endpoint not found';
        } else if (error.message.includes('422')) {
          errorMessage = 'Invalid request data - please check employee and course information';
        } else if (error.message.includes('403')) {
          errorMessage = 'Access denied - insufficient permissions';
        } else if (error.message.includes('Failed to fetch')) {
          errorMessage = 'Network error - please check your connection and try again';
        } else {
          errorMessage = 'Generation failed: ' + error.message;
        }

        Swal.fire({
          icon: 'error',
          title: 'Generation Error',
          text: errorMessage,
          confirmButtonColor: '#dc3545'
        });
      })
      .finally(() => {
        clearTimeout(timeoutId);
        btn.disabled = false;
        btn.innerHTML = originalText;
      });
    }

    function bulkGenerateCertificates() {
      if (!confirm('Generate certificates for all completed trainings without certificates?')) return;

      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';

      fetch('/certificates/bulk-generate', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return response.json();
        } else {
          return response.text().then(text => {
            throw new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
          });
        }
      })
      .then(data => {
        if (data && data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Bulk Generation Complete!',
            text: `Generated: ${data.generated}, Failed: ${data.failed}`,
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            location.reload();
          });
        } else {
          const errorMsg = data && data.message ? data.message : 'Bulk generation failed';
          Swal.fire({
            icon: 'error',
            title: 'Bulk Generation Failed',
            text: errorMsg,
            confirmButtonColor: '#dc3545'
          });
        }
      })
      .catch(error => {
        console.error('Bulk generation error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Bulk Generation Error',
          text: 'Error in bulk generation: ' + error.message,
          confirmButtonColor: '#dc3545'
        });
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
      });
    }

    async function previewCertificateTemplate() {
      const { value: courseName } = await Swal.fire({
        title: '📋 Certificate Preview',
        html: `
          <div class="text-start">
            <p class="mb-3">Enter a course name to preview the certificate template:</p>
            <div class="mb-3">
              <label class="form-label fw-semibold">Course Name:</label>
              <input type="text" id="course-name" class="form-control" placeholder="e.g., Communication Skills Training" value="Communication Skills Training">
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Preview Template',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        width: '500px',
        preConfirm: () => {
          const courseName = document.getElementById('course-name').value;
          if (!courseName.trim()) {
            Swal.showValidationMessage('Please enter a course name');
            return false;
          }
          return courseName.trim();
        }
      });

      if (courseName) {
        try {
          const previewUrl = '/certificates/preview?course_name=' + encodeURIComponent(courseName);

          // Test if the preview endpoint is available
          const response = await fetch(previewUrl, { method: 'HEAD' });

          if (response.ok) {
            window.open(previewUrl, '_blank');
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Preview Unavailable',
              text: 'Certificate preview is not available at this time.',
              confirmButtonColor: '#dc3545'
            });
          }
        } catch (error) {
          console.error('Preview error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Preview Error',
            text: 'Certificate preview service is not available.',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }



    // Collapsible certificate button functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Add event listeners to all collapse buttons
      const collapseButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');

      collapseButtons.forEach(button => {
        const targetId = button.getAttribute('data-bs-target');
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
          targetElement.addEventListener('show.bs.collapse', function() {
            button.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Hide Certificates';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-outline-secondary');
          });

          targetElement.addEventListener('hide.bs.collapse', function() {
            button.innerHTML = '<i class="bi bi-eye me-1"></i>View Certificates';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-outline-primary');
          });
        }
      });
    });


    // Toast functions removed - using SweetAlert2 throughout the application
  </script>
</body>
</html>
