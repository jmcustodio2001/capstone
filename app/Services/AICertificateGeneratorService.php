<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AICertificateGeneratorService
{
    /**
     * Generate a certificate using AI-designed template
     */
    public function generateCertificate($employeeName, $courseName, $completionDate, $employeeId = null)
    {
        try {
            // Generate unique certificate number
            $certificateNumber = $this->generateCertificateNumber($employeeId);
            
            // Create AI-designed certificate template
            $certificateHtml = $this->createAICertificateTemplate($employeeName, $courseName, $completionDate, $certificateNumber);
            
            // Convert HTML to PDF and save
            $fileName = $this->saveCertificateAsPDF($certificateHtml, $certificateNumber);
            
            return [
                'success' => true,
                'certificate_number' => $certificateNumber,
                'file_path' => $fileName,
                'file_url' => '/storage/certificates/' . $fileName
            ];
            
        } catch (\Exception $e) {
            Log::error('Certificate generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($employeeId = null)
    {
        $prefix = 'CERT';
        $year = date('Y');
        $month = date('m');
        $empId = $employeeId ? substr($employeeId, -3) : rand(100, 999);
        $random = rand(1000, 9999);
        
        return $prefix . '-' . $year . $month . '-' . $empId . '-' . $random;
    }
    
    /**
     * Create AI-designed certificate template with dynamic styling
     */
    private function createAICertificateTemplate($employeeName, $courseName, $completionDate, $certificateNumber)
    {
        // AI-powered template selection based on course type
        $templateStyle = $this->selectTemplateStyleBasedOnCourse($courseName);
        
        $formattedDate = Carbon::parse($completionDate)->format('F j, Y');
        $currentYear = date('Y');
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap");
        
        body {
            margin: 0;
            padding: 20px;
            font-family: "Open Sans", sans-serif;
            background: ' . $templateStyle['background'] . ';
            color: #333;
        }
        
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: ' . $templateStyle['border'] . ';
            border-radius: 15px;
            padding: 60px 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .certificate-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: ' . $templateStyle['decorative_pattern'] . ';
            opacity: 0.05;
            z-index: 1;
        }
        
        .certificate-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .header {
            margin-bottom: 40px;
        }
        
        .company-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 3px solid ' . $templateStyle['accent_color'] . ';
        }
        
        .company-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .certificate-title {
            font-family: "Playfair Display", serif;
            font-size: 48px;
            font-weight: 700;
            color: ' . $templateStyle['primary_color'] . ';
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .certificate-subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            font-weight: 300;
        }
        
        .recipient-section {
            margin: 40px 0;
            padding: 30px;
            background: ' . $templateStyle['highlight_bg'] . ';
            border-radius: 10px;
            border-left: 5px solid ' . $templateStyle['accent_color'] . ';
        }
        
        .presented-to {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .recipient-name {
            font-family: "Playfair Display", serif;
            font-size: 36px;
            font-weight: 700;
            color: ' . $templateStyle['primary_color'] . ';
            margin: 10px 0 20px;
            text-decoration: underline;
            text-decoration-color: ' . $templateStyle['accent_color'] . ';
            text-underline-offset: 8px;
        }
        
        .completion-text {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin: 20px 0;
        }
        
        .course-name {
            font-family: "Playfair Display", serif;
            font-size: 24px;
            font-weight: 600;
            color: ' . $templateStyle['course_color'] . ';
            margin: 15px 0;
            padding: 15px;
            background: ' . $templateStyle['course_bg'] . ';
            border-radius: 8px;
            border: 2px solid ' . $templateStyle['course_border'] . ';
        }
        
        .completion-date {
            font-size: 16px;
            color: #666;
            margin: 30px 0;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid ' . $templateStyle['accent_color'] . ';
        }
        
        .signature-block {
            text-align: center;
            flex: 1;
            margin: 0 20px;
        }
        
        .signature-line {
            width: 200px;
            height: 2px;
            background: #ccc;
            margin: 0 auto 10px;
        }
        
        .signature-title {
            font-size: 14px;
            color: #666;
            font-weight: 600;
        }
        
        .certificate-number {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 12px;
            color: #999;
            font-family: monospace;
        }
        
        .achievement-badge {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 80px;
            height: 80px;
            background: ' . $templateStyle['badge_color'] . ';
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .decorative-elements {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 60px;
            height: 60px;
            background: ' . $templateStyle['decorative_color'] . ';
            border-radius: 50%;
            opacity: 0.1;
        }
        
        .decorative-elements::after {
            content: "";
            position: absolute;
            bottom: -40px;
            right: -40px;
            width: 40px;
            height: 40px;
            background: ' . $templateStyle['decorative_color'] . ';
            border-radius: 50%;
        }
        
        @media print {
            body { background: white; padding: 0; }
            .certificate-container { box-shadow: none; border: 2px solid #333; }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="decorative-elements"></div>
        <div class="achievement-badge">
            CERTIFIED<br>COMPLETE
        </div>
        
        <div class="certificate-content">
            <div class="header">
                <div class="company-logo">
                    <img src="data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/images/jetlouge_logo.png'))) . '" alt="Jetlouge Travels Logo">
                </div>
                <h1 class="certificate-title">Certificate of Completion</h1>
                <p class="certificate-subtitle">Jetlouge Travels - Excellence in Professional Development</p>
            </div>
            
            <div class="recipient-section">
                <p class="presented-to">This is to certify that</p>
                <h2 class="recipient-name">' . htmlspecialchars($employeeName) . '</h2>
                <p class="completion-text">
                    has successfully completed the comprehensive training program and demonstrated 
                    proficiency in all required competencies for:
                </p>
                <div class="course-name">' . htmlspecialchars($courseName) . '</div>
                <p class="completion-date">
                    <strong>Date of Completion:</strong> ' . $formattedDate . '
                </p>
            </div>
            
            <div class="signature-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="signature-title">Training Director</p>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="signature-title">HR Manager</p>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="signature-title">Date: ' . $formattedDate . '</p>
                </div>
            </div>
        </div>
        
        <div class="certificate-number">Certificate No: ' . $certificateNumber . '</div>
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
        
        // AI logic to determine appropriate colors and styling based on course content
        if (strpos($courseNameLower, 'leadership') !== false || strpos($courseNameLower, 'management') !== false) {
            return [
                'primary_color' => '#1a365d',
                'accent_color' => '#3182ce',
                'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'border' => '3px solid #3182ce',
                'highlight_bg' => 'rgba(49, 130, 206, 0.1)',
                'course_color' => '#1a365d',
                'course_bg' => 'rgba(26, 54, 93, 0.05)',
                'course_border' => '#3182ce',
                'badge_color' => 'linear-gradient(135deg, #3182ce, #1a365d)',
                'decorative_color' => '#3182ce',
                'decorative_pattern' => 'radial-gradient(circle, #3182ce 1px, transparent 1px)'
            ];
        } elseif (strpos($courseNameLower, 'communication') !== false || strpos($courseNameLower, 'customer') !== false) {
            return [
                'primary_color' => '#2d3748',
                'accent_color' => '#38a169',
                'background' => 'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)',
                'border' => '3px solid #38a169',
                'highlight_bg' => 'rgba(56, 161, 105, 0.1)',
                'course_color' => '#2d3748',
                'course_bg' => 'rgba(56, 161, 105, 0.05)',
                'course_border' => '#38a169',
                'badge_color' => 'linear-gradient(135deg, #38a169, #2d3748)',
                'decorative_color' => '#38a169',
                'decorative_pattern' => 'radial-gradient(circle, #38a169 1px, transparent 1px)'
            ];
        } elseif (strpos($courseNameLower, 'technical') !== false || strpos($courseNameLower, 'software') !== false || strpos($courseNameLower, 'it') !== false) {
            return [
                'primary_color' => '#2a2a2a',
                'accent_color' => '#e53e3e',
                'background' => 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
                'border' => '3px solid #e53e3e',
                'highlight_bg' => 'rgba(229, 62, 62, 0.1)',
                'course_color' => '#2a2a2a',
                'course_bg' => 'rgba(229, 62, 62, 0.05)',
                'course_border' => '#e53e3e',
                'badge_color' => 'linear-gradient(135deg, #e53e3e, #2a2a2a)',
                'decorative_color' => '#e53e3e',
                'decorative_pattern' => 'radial-gradient(circle, #e53e3e 1px, transparent 1px)'
            ];
        } elseif (strpos($courseNameLower, 'safety') !== false || strpos($courseNameLower, 'security') !== false) {
            return [
                'primary_color' => '#744210',
                'accent_color' => '#d69e2e',
                'background' => 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                'border' => '3px solid #d69e2e',
                'highlight_bg' => 'rgba(214, 158, 46, 0.1)',
                'course_color' => '#744210',
                'course_bg' => 'rgba(214, 158, 46, 0.05)',
                'course_border' => '#d69e2e',
                'badge_color' => 'linear-gradient(135deg, #d69e2e, #744210)',
                'decorative_color' => '#d69e2e',
                'decorative_pattern' => 'radial-gradient(circle, #d69e2e 1px, transparent 1px)'
            ];
        } else {
            // Default professional template
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
    }
    
    /**
     * Save certificate as PDF file
     */
    private function saveCertificateAsPDF($html, $certificateNumber)
    {
        // Create certificates directory if it doesn't exist
        $certificatesPath = storage_path('app/public/certificates');
        if (!file_exists($certificatesPath)) {
            mkdir($certificatesPath, 0755, true);
        }
        
        // Generate filename
        $fileName = 'certificate_' . $certificateNumber . '_' . time() . '.html';
        $filePath = $certificatesPath . '/' . $fileName;
        
        // Save HTML file (can be converted to PDF later with libraries like dompdf or wkhtmltopdf)
        file_put_contents($filePath, $html);
        
        // For now, we'll save as HTML. In production, you might want to use:
        // - dompdf: composer require dompdf/dompdf
        // - wkhtmltopdf: for better PDF rendering
        // - puppeteer: for high-quality PDF generation
        
        return $fileName;
    }
    
    /**
     * Get certificate template preview for testing
     */
    public function getTemplatePreview($courseName = "Sample Course")
    {
        return $this->createAICertificateTemplate(
            "John Doe", 
            $courseName, 
            now()->format('Y-m-d'), 
            "PREVIEW-001"
        );
    }
}
