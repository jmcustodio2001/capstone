<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AICertificateGeneratorService;
use App\Models\TrainingRecordCertificateTracking;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CertificateGenerationController extends Controller
{
    protected $certificateGenerator;

    public function __construct()
    {
        try {
            $this->certificateGenerator = new \App\Services\AICertificateGeneratorService();
        } catch (\Exception $e) {
            Log::error('Failed to initialize certificate generator service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->certificateGenerator = null;
        }
    }

    /**
     * Automatically generate certificate when training is completed
     */
    public function generateCertificateOnCompletion($employeeId, $courseId, $completionDate = null)
    {
        try {
            Log::info('Starting certificate generation', [
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'completion_date' => $completionDate
            ]);

            // Get employee and course information
            $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
            $course = \App\Models\CourseManagement::where('course_id', $courseId)->first();

            if (!$employee || !$course) {
                Log::error("Certificate generation failed: Employee or course not found", [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'employee_found' => !!$employee,
                    'course_found' => !!$course
                ]);
                return false;
            }

            $employeeName = $employee->first_name . ' ' . $employee->last_name;
            $courseName = $course->course_title;
            $completionDate = $completionDate ? \Carbon\Carbon::parse($completionDate) : now();

            Log::info('Employee and course found', [
                'employee_name' => $employeeName,
                'course_name' => $courseName
            ]);

            // Check if service is available, try to initialize if needed
            if (!$this->certificateGenerator) {
                Log::warning('Certificate generator service not available, attempting to initialize');
                try {
                    $this->certificateGenerator = new AICertificateGeneratorService();
                } catch (\Exception $e) {
                    Log::error('Failed to initialize certificate generator service', [
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            }

            // Generate certificate using AI service
            $result = $this->certificateGenerator->generateCertificate(
                $employeeName,
                $courseName,
                $completionDate,
                $employeeId
            );

            Log::info('Certificate generation result', [
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'result' => $result
            ]);

            if ($result && isset($result['success']) && $result['success']) {
                // Check if certificate record already exists
                $existingCertificate = TrainingRecordCertificateTracking::where('employee_id', $employeeId)
                    ->where('course_id', $courseId)
                    ->first();

                // Build certificate data with only basic required fields
                $certificateData = [
                    'certificate_number' => $result['certificate_number'],
                    'certificate_url' => $result['file_url']
                ];

                // Only add fields that exist in the database and are in the fillable array
                $fillableFields = (new TrainingRecordCertificateTracking())->getFillable();

                if (in_array('training_date', $fillableFields) && \Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', 'training_date')) {
                    $certificateData['training_date'] = $completionDate;
                }
                if (in_array('certificate_expiry', $fillableFields) && \Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', 'certificate_expiry')) {
                    $certificateData['certificate_expiry'] = $completionDate->copy()->addYear();
                }
                if (in_array('status', $fillableFields) && \Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', 'status')) {
                    $certificateData['status'] = 'Completed';
                }
                if (in_array('remarks', $fillableFields) && \Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', 'remarks')) {
                    $certificateData['remarks'] = 'Auto-generated certificate upon training completion';
                }

                if ($existingCertificate) {
                    // Update existing certificate
                    $existingCertificate->update($certificateData);
                    $certificateRecord = $existingCertificate;

                    Log::info("Updated existing certificate tracking record", [
                        'certificate_id' => $existingCertificate->id,
                        'employee_id' => $employeeId,
                        'course_id' => $courseId,
                        'certificate_number' => $result['certificate_number']
                    ]);
                } else {
                    // Create new certificate record
                    $certificateData['employee_id'] = $employeeId;
                    $certificateData['course_id'] = $courseId;

                    $certificateRecord = TrainingRecordCertificateTracking::create($certificateData);

                    Log::info("Created new certificate tracking record", [
                        'certificate_id' => $certificateRecord->id,
                        'employee_id' => $employeeId,
                        'course_id' => $courseId,
                        'certificate_number' => $result['certificate_number']
                    ]);
                }

                // Log the certificate generation
                try {
                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'auto_generate',
                        'module' => 'Certificate Generation',
                        'description' => "Auto-generated certificate for {$employeeName} - {$courseName} (Certificate: {$result['certificate_number']})"
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create activity log', ['error' => $e->getMessage()]);
                }

                Log::info("Certificate auto-generated successfully", [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'certificate_number' => $result['certificate_number']
                ]);

                return $certificateRecord;
            } else {
                $errorMessage = $result['error'] ?? 'Unknown error';
                Log::error("Certificate generation failed", [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'error' => $errorMessage,
                    'full_result' => $result
                ]);
                throw new \Exception('Certificate generation failed: ' . $errorMessage);
            }

        } catch (\Exception $e) {
            Log::error("Certificate generation exception", [
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Run comprehensive diagnostics for certificate generation
     */
    private function runDiagnostics()
    {
        $diagnostics = [
            'timestamp' => now(),
            'storage_ready' => false,
            'service_ready' => false,
            'database_ready' => false,
            'details' => []
        ];

        try {
            // Check storage
            $certificatesPath = storage_path('app/public/certificates');
            $publicPath = storage_path('app/public');

            // Create directories if needed
            if (!file_exists($publicPath)) {
                @mkdir($publicPath, 0755, true);
            }
            if (!file_exists($certificatesPath)) {
                @mkdir($certificatesPath, 0755, true);
            }

            $diagnostics['details']['storage'] = [
                'certificates_path' => $certificatesPath,
                'path_exists' => file_exists($certificatesPath),
                'path_writable' => is_writable($certificatesPath),
                'parent_writable' => is_writable($publicPath),
                'permissions' => file_exists($certificatesPath) ? substr(sprintf('%o', fileperms($certificatesPath)), -4) : 'N/A'
            ];

            $diagnostics['storage_ready'] = file_exists($certificatesPath) && is_writable($certificatesPath);

            // Check service
            try {
                if (!$this->certificateGenerator) {
                    $testService = new \App\Services\AICertificateGeneratorService();
                } else {
                    $testService = $this->certificateGenerator;
                }
                $diagnostics['service_ready'] = true;
                $diagnostics['details']['service'] = ['status' => 'initialized'];
            } catch (\Exception $e) {
                $diagnostics['service_ready'] = false;
                $diagnostics['details']['service'] = ['error' => $e->getMessage()];
            }

            // Check database
            try {
                $employeeCount = \App\Models\Employee::count();
                $courseCount = \App\Models\CourseManagement::count();
                $diagnostics['database_ready'] = true;
                $diagnostics['details']['database'] = [
                    'employees' => $employeeCount,
                    'courses' => $courseCount,
                    'connection' => 'active'
                ];
            } catch (\Exception $e) {
                $diagnostics['database_ready'] = false;
                $diagnostics['details']['database'] = ['error' => $e->getMessage()];
            }

        } catch (\Exception $e) {
            $diagnostics['details']['general_error'] = $e->getMessage();
        }

        return $diagnostics;
    }

    /**
     * Manual certificate generation endpoint
     */
    public function generateManualCertificate(Request $request)
    {
        try {
            // Run diagnostics first
            $diagnostics = $this->runDiagnostics();
            Log::info('Certificate generation diagnostics', $diagnostics);

            // If diagnostics show critical issues, return error
            if (!$diagnostics['storage_ready'] || !$diagnostics['service_ready']) {
                return response()->json([
                    'success' => false,
                    'message' => 'System not ready for certificate generation. Check: ' .
                               (!$diagnostics['storage_ready'] ? 'Storage permissions. ' : '') .
                               (!$diagnostics['service_ready'] ? 'Service initialization.' : ''),
                    'diagnostics' => $diagnostics
                ], 500);
            }

            Log::info('Certificate generation request received', [
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);

            $validated = $request->validate([
                'employee_id' => 'required|string',
                'course_id' => 'required|string',
                'completion_date' => 'nullable|date'
            ]);

            // Initialize service with better error handling
            if (!$this->certificateGenerator) {
                try {
                    $this->certificateGenerator = new \App\Services\AICertificateGeneratorService();
                    Log::info('Certificate generator service initialized');
                } catch (\Exception $e) {
                    Log::error('Service initialization failed', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Service initialization failed: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Check if employee exists
            $employee = \App\Models\Employee::where('employee_id', $request->employee_id)->first();
            if (!$employee) {
                Log::warning('Employee not found', ['employee_id' => $request->employee_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found with ID: ' . $request->employee_id
                ], 404);
            }

            // Check if course exists
            $course = \App\Models\CourseManagement::where('course_id', $request->course_id)->first();
            if (!$course) {
                Log::warning('Course not found', ['course_id' => $request->course_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found with ID: ' . $request->course_id
                ], 404);
            }

            // Check storage permissions
            $certificatesPath = storage_path('app/public/certificates');
            if (!file_exists($certificatesPath)) {
                try {
                    mkdir($certificatesPath, 0755, true);
                } catch (\Exception $e) {
                    Log::error('Failed to create certificates directory', [
                        'path' => $certificatesPath,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to create certificates directory. Please check storage permissions.'
                    ], 500);
                }
            }

            if (!is_writable($certificatesPath)) {
                Log::error('Certificates directory is not writable', ['path' => $certificatesPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'Certificates directory is not writable. Please check storage permissions.'
                ], 500);
            }

            Log::info('Generating certificate', [
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'course' => $course->course_title,
                'certificates_path' => $certificatesPath,
                'path_writable' => is_writable($certificatesPath)
            ]);

            // Generate certificate with detailed error handling
            try {
                $certificate = $this->generateCertificateOnCompletion(
                    $request->employee_id,
                    $request->course_id,
                    $request->completion_date ?? now()
                );

                if ($certificate && is_object($certificate)) {
                    Log::info('Certificate generated successfully', [
                        'certificate_id' => $certificate->id,
                        'certificate_number' => $certificate->certificate_number,
                        'certificate_url' => $certificate->certificate_url
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Certificate generated successfully!',
                        'certificate' => [
                            'id' => $certificate->id,
                            'number' => $certificate->certificate_number,
                            'url' => $certificate->certificate_url
                        ]
                    ]);
                } else {
                    Log::error('Certificate generation failed - returned false or invalid object', [
                        'result_type' => gettype($certificate),
                        'result_value' => $certificate
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Certificate generation failed. The service returned an invalid result.'
                    ], 500);
                }
            } catch (\Exception $certException) {
                Log::error('Certificate generation threw exception', [
                    'error' => $certException->getMessage(),
                    'trace' => $certException->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate generation failed: ' . $certException->getMessage()
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Certificate generation validation failed', [
                'errors' => $e->validator->errors()->all(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Certificate generation endpoint error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Certificate generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download certificate
     */
    public function downloadCertificate($certificateId)
    {
        try {
            $certificate = TrainingRecordCertificateTracking::findOrFail($certificateId);

            // Check if user has permission to download this certificate
            $user = Auth::user();
            if ($user && $user->role !== 'admin') {
                // For non-admin users, we'll allow downloading for now
                // You can implement additional permission logic here if needed
            }

            // Handle case where certificate_url might be null or empty
            if (!$certificate->certificate_url) {
                return response()->json(['error' => 'Certificate file not available'], 404);
            }

            $filePath = str_replace('/storage/', '', $certificate->certificate_url);
            $fullPath = storage_path('app/public/' . $filePath);

            if (file_exists($fullPath)) {
                $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
                $downloadName = 'certificate_' . $certificate->certificate_number . '.' . $extension;
                return response()->download($fullPath, $downloadName);
            } else {
                return response()->json(['error' => 'Certificate file not found'], 404);
            }

        } catch (\Exception $e) {
            Log::error('Certificate download error: ' . $e->getMessage(), [
                'certificate_id' => $certificateId,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Failed to download certificate'], 500);
        }
    }

    /**
     * View certificate in browser using consistent template design
     */
    public function viewCertificate($certificateId)
    {
        try {
            Log::info('Certificate view requested', [
                'certificate_id' => $certificateId,
                'user_id' => Auth::id()
            ]);

            // Find certificate with relationships for better error handling
            $certificate = TrainingRecordCertificateTracking::with(['employee', 'course'])->find($certificateId);

            if (!$certificate) {
                Log::warning('Certificate not found', ['certificate_id' => $certificateId]);
                abort(404, 'Certificate record not found');
            }

            // Get employee name with fallbacks
            $employeeName = 'Unknown Employee';
            if ($certificate->employee) {
                $firstName = trim($certificate->employee->first_name ?? '');
                $lastName = trim($certificate->employee->last_name ?? '');
                if (!empty($firstName) || !empty($lastName)) {
                    $employeeName = trim($firstName . ' ' . $lastName);
                }
            } else {
                // Try to get employee by ID if relationship is missing
                try {
                    $employee = \App\Models\Employee::where('employee_id', $certificate->employee_id)->first();
                    if ($employee) {
                        $firstName = trim($employee->first_name ?? '');
                        $lastName = trim($employee->last_name ?? '');
                        if (!empty($firstName) || !empty($lastName)) {
                            $employeeName = trim($firstName . ' ' . $lastName);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not fetch employee by ID', ['employee_id' => $certificate->employee_id]);
                }
            }

            // Get course name with fallbacks
            $courseName = 'Unknown Course';
            if ($certificate->course && $certificate->course->course_title) {
                $courseName = $certificate->course->course_title;
            } else {
                // Try to get course by ID if relationship is missing
                try {
                    $course = \App\Models\CourseManagement::where('course_id', $certificate->course_id)->first();
                    if ($course && $course->course_title) {
                        $courseName = $course->course_title;
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not fetch course by ID', ['course_id' => $certificate->course_id]);
                }
            }

            // Generate certificate HTML using the consistent template
            if (!$this->certificateGenerator) {
                $this->certificateGenerator = new \App\Services\AICertificateGeneratorService();
            }

            // Use the same template as the AICertificateGeneratorService but return HTML directly
            $certificateNumber = $certificate->certificate_number ?? 'CERT-' . date('Ymd') . '-' . $certificateId;
            $completionDate = $certificate->training_date ?? now();

            Log::info('Generating certificate view with data', [
                'certificate_id' => $certificateId,
                'employee_name' => $employeeName,
                'course_name' => $courseName,
                'certificate_number' => $certificateNumber
            ]);

            // Create certificate HTML using the consistent template
            $html = $this->createCertificateViewTemplate($employeeName, $courseName, $completionDate, $certificateNumber);

            return response($html)->header('Content-Type', 'text/html');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Certificate record not found', ['certificate_id' => $certificateId]);
            abort(404, 'Certificate record not found');
        } catch (\Exception $e) {
            Log::error('Certificate view error: ' . $e->getMessage(), [
                'certificate_id' => $certificateId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->showCertificateError(null, 'Failed to load certificate: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create certificate view template that matches the PDF download template
     * Unified design consistent across view, download, and preview
     */
    private function createCertificateViewTemplate($employeeName, $courseName, $completionDate, $certificateNumber)
    {
        $formattedDate = \Carbon\Carbon::parse($completionDate)->format('F j, Y');
        $issuedDate = \Carbon\Carbon::parse($completionDate)->format('M j, Y');

        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Achievement - Jetlouge Travels Training</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.2in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Georgia", "Times New Roman", serif;
            background: #f8f9fa;
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }

        .certificate-container {
            background: #fff;
            width: 100%;
            max-width: 10.5in;
            height: 7.5in;
            border: 8px solid #2d3a5a;
            border-radius: 6px;
            position: relative;
            padding: 25px;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            overflow: hidden;
        }

        .inner-border {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 2px solid #87ceeb;
            border-radius: 3px;
            pointer-events: none;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d3a5a, #4a5568);
            border: 3px solid #ffffff;
            box-shadow: 0 4px 8px rgba(45, 58, 90, 0.3);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #2d3a5a;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .certificate-subtitle {
            font-size: 16px;
            color: #2d3a5a;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 300;
        }

        .travel-tagline {
            font-size: 12px;
            color: #2d3a5a;
            font-style: italic;
            margin-bottom: 15px;
        }

        .certificate-body {
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 15px 0;
            position: relative;
            z-index: 2;
        }

        .certification-text {
            font-size: 14px;
            color: #2d3a5a;
            margin-bottom: 10px;
            line-height: 1.2;
            font-weight: 400;
        }

        .recipient-name {
            font-size: 48px;
            font-family: cursive;
            font-weight: bold;
            color: #2d3a5a;
            margin: 10px 0;
            letter-spacing: 1px;
        }

        .course-name {
            background: #2196f3;
            color: white;
            padding: 8px 25px;
            border-radius: 5px;
            font-size: 28px;
            font-weight: bold;
            margin: 12px auto;
            display: inline-block;
        }

        .completion-date {
            font-size: 12px;
            color: #2d3a5a;
            margin: 12px 0;
            font-weight: 500;
        }

        .certificate-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 10px;
            position: relative;
            z-index: 2;
        }

        .signature-section {
            text-align: center;
            flex: 1;
            position: relative;
        }

        .signature-line {
            width: 100px;
            height: 1px;
            background: #2d3a5a;
            margin: 0 auto 5px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 12px;
            color: #2d3a5a;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 10px;
            color: #2d3a5a;
            font-style: italic;
        }

        .certificate-info {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            color: #555;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0.2in;
            }

            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
            }

            .certificate-container {
                max-width: 10.5in !important;
                width: 10.5in !important;
                height: 7.5in !important;
                max-height: 7.5in !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin: 0 auto !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="inner-border"></div>

        <div class="certificate-header">
            <div class="logo-container">
                <div class="logo">
                    <img src="/assets/images/jetlouge_logo.png" alt="Jetlouge Logo" onerror="this.parentElement.innerHTML=\'&lt;div style=&quot;background:linear-gradient(135deg, #2d3a5a, #4a5568);width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:20px;font-weight:bold;&quot;&gt;JT&lt;/div&gt;\'">
                </div>
            </div>
            <div class="certificate-title">CERTIFICATE</div>
            <div class="certificate-subtitle">OF ACHIEVEMENT</div>
            <div class="travel-tagline">Excellence in Travel & Tourism Training</div>
        </div>

        <div class="certificate-body">
            <div class="certification-text">This is to proudly certify that</div>

            <div class="recipient-name">' . htmlspecialchars($employeeName) . '</div>

            <div class="certification-text">has successfully completed the comprehensive training program and demonstrated exceptional proficiency in</div>

            <div class="course-name">' . htmlspecialchars($courseName) . '</div>

            <div class="completion-date">Completed with distinction on <strong>' . $formattedDate . '</strong></div>
        </div>

        <div class="certificate-footer">
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-name">HR Department</div>
                <div class="signature-title">Training Director</div>
            </div>

            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-name">Jetlouge Admin</div>
                <div class="signature-title">HR Manager</div>
            </div>
        </div>

        <div class="certificate-info">
            Certificate ID: ' . htmlspecialchars($certificateNumber) . ' &nbsp; | &nbsp; Issued: ' . $issuedDate . '
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Get certificate preview for testing
     */
    public function previewTemplate(Request $request)
    {
        try {
            $courseName = $request->get('course_name', 'Sample Training Course');

            if (!$this->certificateGenerator) {
                return response('Certificate generator service not available', 500);
            }

            $html = $this->certificateGenerator->getTemplatePreview($courseName);

            return response($html)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            Log::error('Certificate preview error: ' . $e->getMessage());
            return response('Certificate preview failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk generate certificates for completed trainings
     */
    public function bulkGenerateCertificates()
    {
        try {
            // Find completed trainings without certificates
            $completedTrainings = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])
                ->where('status', 'Completed')
                ->orWhere('progress', '>=', 100)
                ->get();

            $generated = 0;
            $failed = 0;

            foreach ($completedTrainings as $training) {
                // Check if certificate already exists
                $existingCert = TrainingRecordCertificateTracking::where('employee_id', $training->employee_id)
                    ->where('course_id', $training->course_id)
                    ->exists();

                if (!$existingCert) {
                    $result = $this->generateCertificateOnCompletion(
                        $training->employee_id,
                        $training->course_id,
                        $training->updated_at->format('Y-m-d')
                    );

                    if ($result) {
                        $generated++;
                    } else {
                        $failed++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk generation completed. Generated: {$generated}, Failed: {$failed}",
                'generated' => $generated,
                'failed' => $failed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public diagnostic endpoint for certificate generation
     */
    public function diagnostics()
    {
        try {
            $diagnostics = $this->runDiagnostics();

            // Add additional test information
            $diagnostics['test_data'] = [
                'sample_employee' => \App\Models\Employee::first(),
                'sample_course' => \App\Models\CourseManagement::first(),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION
            ];

            return response()->json([
                'success' => true,
                'diagnostics' => $diagnostics,
                'recommendations' => $this->getDiagnosticRecommendations($diagnostics)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get recommendations based on diagnostic results
     */
    private function getDiagnosticRecommendations($diagnostics)
    {
        $recommendations = [];

        if (!$diagnostics['storage_ready']) {
            $recommendations[] = 'Fix storage permissions: Run "chmod 755 ' . storage_path('app/public') . '" and "chmod 755 ' . storage_path('app/public/certificates') . '"';
        }

        if (!$diagnostics['service_ready']) {
            $recommendations[] = 'Check AICertificateGeneratorService class exists and dependencies are installed';
        }

        if (!$diagnostics['database_ready']) {
            $recommendations[] = 'Check database connection and ensure Employee and CourseManagement models are accessible';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'All systems appear ready. Try generating a certificate again.';
        }

        return $recommendations;
    }

    /**
     * Test certificate generation with sample data
     */
    public function testGeneration()
    {
        try {
            // Get first employee and course for testing
            $employee = \App\Models\Employee::first();
            $course = \App\Models\CourseManagement::first();

            if (!$employee || !$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sample employee or course found for testing'
                ], 404);
            }

            Log::info('Starting test certificate generation', [
                'employee_id' => $employee->employee_id,
                'course_id' => $course->course_id
            ]);

            $result = $this->generateCertificateOnCompletion(
                $employee->employee_id,
                $course->course_id,
                now()
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test certificate generated successfully',
                    'certificate' => [
                        'id' => $result->id,
                        'number' => $result->certificate_number,
                        'url' => $result->certificate_url
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Test certificate generation failed'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test generation error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Show certificate error page with inline HTML
     */
    private function showCertificateError($certificate, $message, $statusCode = 404)
    {
        $employeeName = 'Unknown Employee';
        $courseName = 'Unknown Course';
        $certificateId = 'N/A';

        if ($certificate) {
            $certificateId = $certificate->id ?? 'N/A';
            if ($certificate->employee) {
                $employeeName = trim(($certificate->employee->first_name ?? '') . ' ' . ($certificate->employee->last_name ?? ''));
            }
            if ($certificate->course) {
                $courseName = $certificate->course->course_title ?? 'Unknown Course';
            }
        }

        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Not Available</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        .error-icon {
            font-size: 64px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .error-message {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .certificate-info {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .certificate-info strong {
            color: #2c3e50;
        }
        .back-button {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .back-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <div class="error-title">Certificate Not Available</div>
        <div class="error-message">' . htmlspecialchars($message) . '</div>

        <div class="certificate-info">
            <div><strong>Certificate ID:</strong> ' . htmlspecialchars($certificateId) . '</div>
            <div><strong>Employee:</strong> ' . htmlspecialchars($employeeName) . '</div>
            <div><strong>Course:</strong> ' . htmlspecialchars($courseName) . '</div>
        </div>

        <a href="javascript:history.back()" class="back-button">Go Back</a>
        <a href="/admin/training-record-certificate-tracking" class="back-button" style="margin-left: 10px;">Certificate Management</a>
    </div>
</body>
</html>';

        return response($html, $statusCode)->header('Content-Type', 'text/html');
    }

    /**
     * Force generate certificate for a specific certificate ID
     */
    public function forceGenerateCertificate($certificateId)
    {
        try {
            Log::info('Force certificate generation requested', ['certificate_id' => $certificateId]);

            // Find certificate record
            $certificate = TrainingRecordCertificateTracking::with(['employee', 'course'])->find($certificateId);

            if (!$certificate) {
                return response()->json(['error' => 'Certificate record not found'], 404);
            }

            // Get employee and course data
            $employee = $certificate->employee;
            $course = $certificate->course;

            // Try direct database queries if relationships are missing
            if (!$employee && $certificate->employee_id) {
                $employee = \App\Models\Employee::where('employee_id', $certificate->employee_id)->first();
            }

            if (!$course && $certificate->course_id) {
                $course = \App\Models\CourseManagement::where('course_id', $certificate->course_id)->first();
            }

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            // Generate employee name
            $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
            if (empty($employeeName)) {
                $employeeName = 'Employee ' . $certificate->employee_id;
            }

            // Get course name
            $courseName = $course->course_title ?? 'Unknown Course';

            Log::info('Force generating certificate with data', [
                'certificate_id' => $certificateId,
                'employee_name' => $employeeName,
                'course_name' => $courseName,
                'employee_id' => $certificate->employee_id,
                'course_id' => $certificate->course_id
            ]);

            // Initialize service
            if (!$this->certificateGenerator) {
                $this->certificateGenerator = new \App\Services\AICertificateGeneratorService();
            }

            // Generate certificate
            $result = $this->certificateGenerator->generateCertificate(
                $employeeName,
                $courseName,
                $certificate->training_date ?? now(),
                $certificate->employee_id
            );

            if ($result && isset($result['success']) && $result['success']) {
                // Update certificate record
                $certificate->update([
                    'certificate_url' => $result['file_url'],
                    'certificate_number' => $result['certificate_number'] ?? $certificate->certificate_number,
                    'status' => 'Completed'
                ]);

                Log::info('Force certificate generation successful', [
                    'certificate_id' => $certificateId,
                    'file_url' => $result['file_url'],
                    'certificate_number' => $result['certificate_number']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Certificate generated successfully!',
                    'certificate_url' => route('certificates.view', $certificateId),
                    'download_url' => route('certificates.download', $certificateId)
                ]);
            } else {
                Log::error('Force certificate generation failed', [
                    'certificate_id' => $certificateId,
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Certificate generation failed',
                    'error' => $result['error'] ?? 'Unknown error'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Force certificate generation exception', [
                'certificate_id' => $certificateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Certificate generation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
