<?php

require_once 'vendor/autoload.php';

// Simple test to check if certificate generation works
try {
    // Create a simple HTML certificate
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .certificate { border: 5px solid #0066cc; padding: 30px; margin: 20px; }
            h1 { color: #0066cc; font-size: 36px; }
            .name { font-size: 24px; font-weight: bold; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="certificate">
            <h1>Certificate of Completion</h1>
            <p>This is to certify that</p>
            <div class="name">Test Employee</div>
            <p>has successfully completed the training course</p>
            <div class="name">Test Course</div>
            <p>Date: ' . date('Y-m-d') . '</p>
        </div>
    </body>
    </html>';

    // Try to generate PDF using DomPDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
    
    // Save to storage
    $filename = 'test_certificate_' . time() . '.pdf';
    $path = 'storage/app/public/certificates/' . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists('storage/app/public/certificates')) {
        mkdir('storage/app/public/certificates', 0755, true);
    }
    
    $pdf->save($path);
    
    echo "SUCCESS: Certificate generated at: " . $path . "\n";
    echo "File exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
    echo "File size: " . (file_exists($path) ? filesize($path) . ' bytes' : 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}