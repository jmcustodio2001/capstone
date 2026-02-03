<?php

namespace App\Http\Controllers\Api;

use App\Models\PotentialSuccessor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PotentialController extends Controller
{
    public function index()
    {
        // Assuming the model class is in app/Models/PotentialEmployee.php
        // Make sure the file exists and is namespaced correctly as App\Models\PotentialEmployee
        return response()->json(PotentialSuccessor::all());
    }
}
