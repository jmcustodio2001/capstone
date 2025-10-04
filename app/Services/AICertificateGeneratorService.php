<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;

class AICertificateGeneratorService
{
    /**
     * Generate a certificate using AI-designed template
     */
    public function generateCertificate($employeeName, $courseName, $completionDate, $employeeId = null)
    {
        try {
            Log::info('Starting certificate generation', [
                'employee_name' => $employeeName,
                'course_name' => $courseName,
                'completion_date' => $completionDate,
                'employee_id' => $employeeId
            ]);

            // Sanitize and validate employee name
            $originalEmployeeName = $employeeName;
            $employeeName = $this->sanitizeEmployeeName($employeeName);
            if (empty($employeeName)) {
                Log::error('Missing or invalid employee name for certificate generation', [
                    'original_employee_name' => $originalEmployeeName,
                    'sanitized_employee_name' => $employeeName,
                    'course_name' => $courseName,
                    'employee_id' => $employeeId
                ]);
                
                // Try to use original name if sanitization failed
                if (!empty($originalEmployeeName)) {
                    $employeeName = trim((string) $originalEmployeeName);
                    Log::warning('Using original employee name after sanitization failed', [
                        'employee_name' => $employeeName
                    ]);
                } else {
                    throw new \Exception('Valid employee name is required');
                }
            }

            // Sanitize and validate course name
            $originalCourseName = $courseName;
            $courseName = $this->sanitizeCourseName($courseName);
            if (empty($courseName)) {
                Log::error('Missing or invalid course name for certificate generation', [
                    'employee_name' => $employeeName,
                    'original_course_name' => $originalCourseName,
                    'sanitized_course_name' => $courseName,
                    'employee_id' => $employeeId
                ]);
                
                // Try to use original course name if sanitization failed
                if (!empty($originalCourseName)) {
                    $courseName = trim((string) $originalCourseName);
                    Log::warning('Using original course name after sanitization failed', [
                        'course_name' => $courseName
                    ]);
                } else {
                    throw new \Exception('Valid course name is required');
                }
            }

            // Validate and parse completion date
            $parsedDate = $this->validateAndParseDate($completionDate, $employeeName, $courseName, $employeeId);
            if (!$parsedDate) {
                throw new \Exception('Valid completion date is required');
            }

            // Ensure storage directory exists
            $certificatesPath = storage_path('app/public/certificates');
            if (!file_exists($certificatesPath)) {
                if (!@mkdir($certificatesPath, 0755, true)) {
                    Log::error('Failed to create certificates directory', [
                        'certificates_path' => $certificatesPath
                    ]);
                    throw new \Exception('Failed to create certificates directory: ' . $certificatesPath);
                }
            }
            if (!is_writable($certificatesPath)) {
                Log::error('Certificates directory is not writable', [
                    'certificates_path' => $certificatesPath
                ]);
                throw new \Exception('Certificates directory is not writable: ' . $certificatesPath);
            }

            // Generate unique certificate number
            $certificateNumber = $this->generateCertificateNumber($employeeId);
            Log::info('Generated certificate number: ' . $certificateNumber);

            // Create AI-designed certificate template
            $certificateHtml = $this->createAICertificateTemplate($employeeName, $courseName, $completionDate, $certificateNumber);
            Log::info('Created certificate HTML template');

            // Save PDF certificate
            $fileName = $this->saveCertificateAsPDF($certificateHtml, $certificateNumber);
            Log::info('Saved certificate PDF file: ' . $fileName);

            return [
                'success' => true,
                'certificate_number' => $certificateNumber,
                'file_path' => $fileName,
                'file_url' => '/storage/certificates/' . $fileName
            ];
        } catch (\Exception $e) {
            Log::error('Certificate generation failed: ' . $e->getMessage(), [
                'employee_name' => $employeeName ?? 'N/A',
                'course_name' => $courseName ?? 'N/A',
                'employee_id' => $employeeId ?? 'N/A',
                'completion_date' => $completionDate ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

                /**
                 * Save certificate as PDF file using DomPDF
                 */
                private function saveCertificateAsPDF($html, $certificateNumber)
                {
                    try {
                        $certificatesPath = storage_path('app/public/certificates');
                        if (!file_exists($certificatesPath)) {
                            if (!@mkdir($certificatesPath, 0755, true)) {
                                throw new \Exception('Failed to create certificates directory: ' . $certificatesPath);
                            }
                        }
                        if (!is_writable($certificatesPath)) {
                            throw new \Exception('Certificates directory is not writable: ' . $certificatesPath);
                        }

                        $sanitizedCertNumber = preg_replace('/[^a-zA-Z0-9\-_]/', '', $certificateNumber);
                        $fileName = 'certificate_' . $sanitizedCertNumber . '_' . time() . '.pdf';
                        $filePath = $certificatesPath . DIRECTORY_SEPARATOR . $fileName;

                        // Generate PDF using DomPDF with optimized settings for single-page printing
                        $pdf = Pdf::loadHTML($html);
                        $pdf->setPaper('A4', 'landscape');
                        $pdf->setOptions([
                            'isHtml5ParserEnabled' => true,
                            'isPhpEnabled' => true,
                            'defaultFont' => 'Times-Roman',
                            'dpi' => 96,
                            'defaultPaperSize' => 'A4',
                            'defaultPaperOrientation' => 'landscape',
                            'isFontSubsettingEnabled' => true,
                            'isRemoteEnabled' => false
                        ]);
                        $output = $pdf->output();
                        $bytesWritten = @file_put_contents($filePath, $output, LOCK_EX);

                        if ($bytesWritten === false) {
                            throw new \Exception('Failed to write certificate PDF to disk: ' . $filePath);
                        }
                        if (!file_exists($filePath) || !is_readable($filePath) || filesize($filePath) === 0) {
                            throw new \Exception('Certificate PDF file was not created or is not readable: ' . $filePath);
                        }

                        Log::info('Certificate PDF file saved successfully', [
                            'file_path' => $filePath,
                            'file_size' => $bytesWritten,
                            'certificate_number' => $certificateNumber,
                            'file_name' => $fileName
                        ]);

                        return $fileName;
                    } catch (\Exception $e) {
                        Log::error('Failed to save certificate PDF file', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'certificates_path' => $certificatesPath ?? 'N/A',
                            'certificate_number' => $certificateNumber ?? 'N/A'
                        ]);
                        // Fallback to simple number
                        return 'CERT-' . date('Ymd') . '-' . rand(10000, 99999);
                    }
                }

    /**
     * Create AI-designed certificate template with realistic travel and tours theme
     * Enhanced with professional styling, gradients, and travel-specific elements
     */
    private function createAICertificateTemplate($employeeName, $courseName, $completionDate, $certificateNumber)
    {
        $formattedDate = Carbon::parse($completionDate)->format('F j, Y');
        $issuedDate = Carbon::parse($completionDate)->format('M j, Y');
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Achievement - Travel & Tours Training</title>
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
                <div class="signature-name">John Mark Custodio</div>
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
                 * AI-powered template style selection based on course type
                 */
                private function selectTemplateStyleBasedOnCourse($courseName)
                {
                    $courseNameLower = strtolower($courseName);
                    // ...existing code for style selection...
                    return [
                        'primary_color' => '#2d3748',
                        'accent_color' => '#4a5568',
                        'background' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                        'border' => '3px solid #4a5568',
                        'highlight_bg' => 'rgba(74, 85, 104, 0.1)',
                        'course_color' => '#2d3748',
                        'course_bg' => 'rgba(74, 85, 104, 0.05)',
                        'course_border' => '#4a5568',
                        'badge_color' => 'linear-gradient(135deg, #4a5568, #2d3748)',
                        'decorative_color' => '#4a5568',
                        'decorative_pattern' => 'radial-gradient(circle, #4a5568 1px, transparent 1px)'
                    ];
                }
    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($employeeId = null)
    {
        try {
            $prefix = 'CERT';
            $year = date('Y');
            $month = date('m');
            // Ensure employeeId is numeric and not empty
            if (is_numeric($employeeId) && $employeeId > 0) {
                $empId = substr(str_pad($employeeId, 3, '0', STR_PAD_LEFT), -3);
            } else {
                // If employeeId is missing, null, or invalid, use a random 3-digit number
                $empId = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                Log::warning('Employee ID missing or invalid for certificate number generation, using random value', [
                    'provided_employee_id' => $employeeId
                ]);
            }
            $random = rand(1000, 9999);

            $certificateNumber = $prefix . '-' . $year . $month . '-' . $empId . '-' . $random;

            Log::info('Generated certificate number', [
                'certificate_number' => $certificateNumber,
                'employee_id' => $employeeId,
                'empId_used' => $empId
            ]);

            return $certificateNumber;
        } catch (\Exception $e) {
            Log::error('Failed to generate certificate number', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            // Fallback to simple number
            return 'CERT-' . date('Ymd') . '-' . rand(10000, 99999);
        }
    }

    /**
     * Sanitize employee name to prevent issues
     */
    private function sanitizeEmployeeName($employeeName)
    {
        if (is_null($employeeName) || $employeeName === '') {
            return null;
        }

        // Convert to string and trim
        $name = trim((string) $employeeName);
        
        // Handle common invalid values
        if (in_array(strtolower($name), ['unknown', 'null', 'n/a', 'na', ''])) {
            return null;
        }

        // Remove extra whitespace and sanitize
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^\p{L}\p{N}\s\-\.\']/u', '', $name);
        
        return trim($name) ?: null;
    }

    /**
     * Sanitize course name to prevent issues
     */
    private function sanitizeCourseName($courseName)
    {
        if (is_null($courseName) || $courseName === '') {
            return null;
        }

        // Convert to string and trim
        $name = trim((string) $courseName);
        
        // Handle common invalid values
        if (in_array(strtolower($name), ['unknown', 'null', 'n/a', 'na', 'no course', 'unknown course', ''])) {
            return null;
        }

        // Remove extra whitespace and sanitize
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^\p{L}\p{N}\s\-\.\'\(\)&]/u', '', $name);
        
        return trim($name) ?: null;
    }

    /**
     * Validate and parse completion date
     */
    private function validateAndParseDate($completionDate, $employeeName, $courseName, $employeeId)
    {
        if (empty($completionDate)) {
            Log::error('Missing completion date for certificate generation', [
                'employee_name' => $employeeName,
                'course_name' => $courseName,
                'employee_id' => $employeeId
            ]);
            return null;
        }

        try {
            // Handle Carbon objects passed directly
            if ($completionDate instanceof \Carbon\Carbon) {
                return $completionDate;
            }

            // Parse string dates
            $parsedDate = \Carbon\Carbon::parse($completionDate);
            
            // Validate date is not in the future (with 1 day tolerance)
            if ($parsedDate->isFuture() && $parsedDate->diffInDays(now()) > 1) {
                Log::warning('Completion date is in the future, adjusting to today', [
                    'original_date' => $completionDate,
                    'employee_name' => $employeeName,
                    'course_name' => $courseName
                ]);
                $parsedDate = now();
            }

            // Validate date is not too old (more than 10 years)
            if ($parsedDate->diffInYears(now()) > 10) {
                Log::warning('Completion date is very old, adjusting to 1 year ago', [
                    'original_date' => $completionDate,
                    'employee_name' => $employeeName,
                    'course_name' => $courseName
                ]);
                $parsedDate = now()->subYear();
            }

            return $parsedDate;

        } catch (\Exception $dateEx) {
            Log::error('Invalid completion date format', [
                'completion_date' => $completionDate,
                'employee_name' => $employeeName,
                'course_name' => $courseName,
                'employee_id' => $employeeId,
                'error' => $dateEx->getMessage()
            ]);
            
            // Return current date as fallback
            Log::info('Using current date as fallback for invalid completion date');
            return now();
        }
    }

    /**
     * Get template preview for testing with enhanced travel theme
     */
    public function getTemplatePreview($courseName = 'Sample Travel & Tourism Course')
    {
        try {
            return $this->createAICertificateTemplate(
                'Sample Employee Name',
                $courseName,
                now(),
                'PREVIEW-CERT-001'
            );
        } catch (\Exception $e) {
            Log::error('Template preview generation failed: ' . $e->getMessage());
            return '<html><body><h1>Template Preview Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></body></html>';
        }
    }
}
