<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;

class AssessmentController extends Controller
{
    public function index() {
        $assessmentQuestion = AssessmentQuestion::all();
    }
}
