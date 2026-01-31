<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationalPosition;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;

class PositionApiController extends Controller
{
    /**
     * Get all positions with competency details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return OrganizationalPosition::all();
    }

    /**
     * Get specific position details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $position = OrganizationalPosition::find($id);

            if (!$position) {
                return response()->json([
                    'success' => false,
                    'message' => 'Position not found'
                ], 404);
            }

            // Resolve competencies for this specific position
            $compNames = [];
            if ($position->required_competencies && is_array($position->required_competencies)) {
                $compIds = [];
                foreach ($position->required_competencies as $rc) {
                    $compId = is_array($rc) ? ($rc['competency_id'] ?? null) : ($rc->competency_id ?? null);
                    if ($compId) {
                        $compIds[] = $compId;
                    }
                }

                if (!empty($compIds)) {
                    $compNames = CompetencyLibrary::whereIn('id', $compIds)
                        ->pluck('competency_name')
                        ->toArray();
                }
            }

            $position->competency_names = $compNames;

            return response()->json([
                'success' => true,
                'data' => $position
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching position: ' . $e->getMessage()
            ], 500);
        }
    }
}
