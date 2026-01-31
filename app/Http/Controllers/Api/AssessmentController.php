<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;

class AssessmentController extends Controller
{
    public function index() {
        try {
            $assessmentQuestions = AssessmentQuestion::all();
            return response()->json([
                'success' => true,
                'data' => $assessmentQuestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving assessment questions: ' . $e->getMessage()
            ], 500);
        }
    }
}
