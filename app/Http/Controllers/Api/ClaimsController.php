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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected,Processed'
        ]);

        $claim = ClaimReimbursement::find($id);

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Claim not found'
            ], 404);
        }

        $claim->status = $request->status;
        $claim->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $claim
        ]);
    }
}
