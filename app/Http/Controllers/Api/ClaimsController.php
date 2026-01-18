<?php

namespace App\Http\Controllers\Api;

use App\Models\ClaimReimbursement;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class ClaimsController extends Controller
{
    public function c(){
        return ClaimReimbursement::all();
    }
}
