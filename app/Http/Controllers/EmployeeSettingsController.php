<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmployeeSettingsController extends Controller
{
    /**
     * Save employee settings
     */
    public function saveSettings(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            // Validate the settings data
            $validatedData = $request->validate([
                'email_notifications' => 'boolean',
                'push_notifications' => 'boolean',
                'language' => 'string|max:10',
                'animations_enabled' => 'boolean',
                'dark_mode' => 'boolean'
            ]);

            // Check if employee_settings table exists, create if not
            if (!DB::getSchemaBuilder()->hasTable('employee_settings')) {
                DB::statement('
                    CREATE TABLE employee_settings (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id VARCHAR(255) NOT NULL,
                        email_notifications BOOLEAN DEFAULT TRUE,
                        push_notifications BOOLEAN DEFAULT TRUE,
                        language VARCHAR(10) DEFAULT "en",
                        animations_enabled BOOLEAN DEFAULT TRUE,
                        dark_mode BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_employee (employee_id)
                    )
                ');
            }

            // Save or update settings
            DB::table('employee_settings')->updateOrInsert(
                ['employee_id' => $employee->employee_id],
                [
                    'email_notifications' => $validatedData['email_notifications'] ?? true,
                    'push_notifications' => $validatedData['push_notifications'] ?? true,
                    'language' => $validatedData['language'] ?? 'en',
                    'animations_enabled' => $validatedData['animations_enabled'] ?? true,
                    'dark_mode' => $validatedData['dark_mode'] ?? false,
                    'updated_at' => now()
                ]
            );

            Log::info('Employee settings saved', [
                'employee_id' => $employee->employee_id,
                'settings' => $validatedData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully',
                'settings' => $validatedData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save employee settings: ' . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? 'unknown',
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee settings
     */
    public function getSettings(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            // Get settings from database
            $settings = DB::table('employee_settings')
                ->where('employee_id', $employee->employee_id)
                ->first();

            // Default settings if none found
            $defaultSettings = [
                'email_notifications' => true,
                'push_notifications' => true,
                'language' => 'en',
                'animations_enabled' => true,
                'dark_mode' => false
            ];

            $responseSettings = $settings ? [
                'email_notifications' => (bool) $settings->email_notifications,
                'push_notifications' => (bool) $settings->push_notifications,
                'language' => $settings->language,
                'animations_enabled' => (bool) $settings->animations_enabled,
                'dark_mode' => (bool) $settings->dark_mode
            ] : $defaultSettings;

            return response()->json([
                'success' => true,
                'settings' => $responseSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get employee settings: ' . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get settings',
                'settings' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'language' => 'en',
                    'animations_enabled' => true,
                    'dark_mode' => false
                ]
            ]);
        }
    }
}
