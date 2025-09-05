<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')->latest()->limit(50)->get();
        // Map logs to ensure user is always serializable and no PHP warnings break JSON
        $safeLogs = $logs->map(function($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name
                ] : null,
                'module' => $log->module,
                'action' => $log->action,
                'description' => $log->description,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ];
        });
        return response()->json($safeLogs);
    }
}
