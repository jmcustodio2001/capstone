<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageLinkController extends Controller
{
    /**
     * Return a public URL for a file stored in the public disk for allowed folders.
     *
     * Example: GET /storage-file/certificates/abc.pdf -> { url: '/storage/certificates/abc.pdf' }
     * If ?redirect=1 is provided the controller will redirect to the URL.
     */
    public function show(Request $request, $folder, $path)
    {
        $responseStatus = 200;
        $responseData = ['success' => false];

        // Normalize folder name (accept both profile_picture and profile_pictures)
        $folder = Str::of($folder)->lower()->replaceFirst('profile_picture', 'profile_pictures')->__toString();

        // Allowed folders whitelist
        $allowed = [
            'certificates',
            'claim_receipts',
            'employee_photos',
            'profile_pictures',
        ];

        // Validate folder
        if (!in_array($folder, $allowed)) {
            $responseStatus = 400;
            $responseData['message'] = 'Folder not allowed';
        } else {
            // Prevent path traversal and normalize path
            $path = trim($path, '/');
            if (Str::contains($path, '..') || $path === '') {
                $responseStatus = 400;
                $responseData['message'] = 'Invalid file path';
            } else {
                $storagePath = $folder . '/' . $path;

                // Check existence on public disk
                if (!Storage::disk('public')->exists($storagePath)) {
                    $responseStatus = 404;
                    $responseData['message'] = 'File not found';
                    $responseData['path'] = $storagePath;
                } else {
                    // Construct public URL using the conventional public storage symlink
                    // (storage/app/public -> public/storage)
                    $url = asset('storage/' . $storagePath);
                    $responseData = ['success' => true, 'url' => $url, 'path' => $storagePath];
                }
            }
        }

        // If requested, redirect to the file URL when available
        if ($request->query('redirect') == 1 && isset($url) && $responseData['success'] === true) {
            return redirect($url);
        }

        return response()->json($responseData, $responseStatus);
    }
}
