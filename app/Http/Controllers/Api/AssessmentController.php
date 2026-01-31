<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssessmentController extends Controller
{
    /**
     * Get all assessment questions
     */
    public function index()
    {
        try {
            Log::info('Assessment API called');
            
            $assessmentQuestions = AssessmentQuestion::all();
            
            Log::info('Assessment questions retrieved: ' . $assessmentQuestions->count());
            
            return response()->json([
                'success' => true,
                'data' => $assessmentQuestions
            ]);
        } catch (\Exception $e) {
            Log::error('Assessment API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving assessment questions: ' . $e->getMessage()
            ], 500);
        }
    }
}
