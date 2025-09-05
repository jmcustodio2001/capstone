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
    
    public function __construct(AICertificateGeneratorService $certificateGenerator)
    {
        $this->certificateGenerator = $certificateGenerator;
    }
    
    /**
     * Automatically generate certificate when training is completed
     */
    public function generateCertificateOnCompletion($employeeId, $courseId, $completionDate = null)
    {
        try {
            // Get employee and course information
            $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
            $course = \App\Models\CourseManagement::where('course_id', $courseId)->first();
            
            if (!$employee || !$course) {
                Log::error("Certificate generation failed: Employee or course not found", [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId
                ]);
                return false;
            }
            
            $employeeName = $employee->first_name . ' ' . $employee->last_name;
            $courseName = $course->course_title;
            $completionDate = $completionDate ?? now()->format('Y-m-d');
            
            // Generate certificate using AI service
            $result = $this->certificateGenerator->generateCertificate(
                $employeeName, 
                $courseName, 
                $completionDate, 
                $employeeId
            );
            
            if ($result['success']) {
                // Check if certificate record already exists
                $existingCertificate = TrainingRecordCertificateTracking::where('employee_id', $employeeId)
                    ->where('course_id', $courseId)
                    ->first();
                
                if ($existingCertificate) {
                    // Update existing certificate
                    $existingCertificate->update([
                        'certificate_number' => $result['certificate_number'],
                        'certificate_url' => $result['file_url'],
                        'training_date' => $completionDate,
                        'certificate_expiry' => now()->addYear()->format('Y-m-d'), // 1 year validity
                        'status' => 'Completed',
                        'remarks' => 'Auto-generated certificate upon training completion'
                    ]);
                    
                    $certificateRecord = $existingCertificate;
                    
                    Log::info("Updated existing certificate tracking record", [
                        'certificate_id' => $existingCertificate->id,
                        'employee_id' => $employeeId,
                        'course_id' => $courseId,
                        'certificate_number' => $result['certificate_number']
                    ]);
                } else {
                    // Create new certificate record
                    $certificateRecord = TrainingRecordCertificateTracking::create([
                        'employee_id' => $employeeId,
                        'course_id' => $courseId,
                        'certificate_number' => $result['certificate_number'],
                        'certificate_url' => $result['file_url'],
                        'training_date' => $completionDate,
                        'certificate_expiry' => now()->addYear()->format('Y-m-d'), // 1 year validity
                        'status' => 'Completed',
                        'remarks' => 'Auto-generated certificate upon training completion'
                    ]);
                    
                    Log::info("Created new certificate tracking record", [
                        'certificate_id' => $certificateRecord->id,
                        'employee_id' => $employeeId,
                        'course_id' => $courseId,
                        'certificate_number' => $result['certificate_number']
                    ]);
                }
                
                // Log the certificate generation
                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'auto_generate',
                    'module' => 'Certificate Generation',
                    'description' => "Auto-generated certificate for {$employeeName} - {$courseName} (Certificate: {$result['certificate_number']})"
                ]);
                
                Log::info("Certificate auto-generated successfully", [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'certificate_number' => $result['certificate_number']
                ]);
                
                return $certificateRecord;
            } else {
                Log::error("Certificate generation failed", [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'error' => $result['error']
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("Certificate generation exception: " . $e->getMessage(), [
                'employee_id' => $employeeId,
                'course_id' => $courseId
            ]);
            return false;
        }
    }
    
    /**
     * Manual certificate generation endpoint
     */
    public function generateManualCertificate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'course_id' => 'required|exists:course_management,course_id',
            'completion_date' => 'nullable|date'
        ]);
        
        $certificate = $this->generateCertificateOnCompletion(
            $request->employee_id,
            $request->course_id,
            $request->completion_date
        );
        
        if ($certificate) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate generated successfully!',
                'certificate' => [
                    'number' => $certificate->certificate_number,
                    'url' => $certificate->certificate_url
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate certificate. Please try again.'
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
                return response()->download($fullPath, 'certificate_' . $certificate->certificate_number . '.html');
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
     * View certificate in browser
     */
    public function viewCertificate($certificateId)
    {
        try {
            $certificate = TrainingRecordCertificateTracking::findOrFail($certificateId);
            
            // Check if user has permission to view this certificate
            $user = Auth::user();
            if ($user && $user->role !== 'admin') {
                // For non-admin users, we'll allow viewing for now
                // You can implement additional permission logic here if needed
            }
            
            // Handle case where certificate_url might be null or empty
            if (!$certificate->certificate_url) {
                abort(404, 'Certificate file not available');
            }
            
            $filePath = str_replace('/storage/', '', $certificate->certificate_url);
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                return response($content)->header('Content-Type', 'text/html');
            } else {
                abort(404, 'Certificate file not found');
            }
            
        } catch (\Exception $e) {
            Log::error('Certificate view error: ' . $e->getMessage(), [
                'certificate_id' => $certificateId,
                'user_id' => Auth::id()
            ]);
            abort(500, 'Failed to load certificate');
        }
    }
    
    /**
     * Get certificate preview for testing
     */
    public function previewTemplate(Request $request)
    {
        $courseName = $request->get('course_name', 'Sample Training Course');
        $html = $this->certificateGenerator->getTemplatePreview($courseName);
        
        return response($html)->header('Content-Type', 'text/html');
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
}
