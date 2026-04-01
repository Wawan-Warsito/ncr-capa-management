<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\NCRAttachment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class NCRAttachmentController extends Controller
{
    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request, $ncrId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:255',
            'attachment_type' => 'nullable|string|in:evidence,report,other',
        ]);

        $ncr = NCR::findOrFail($ncrId);
        $user = $request->user();

        // Check permission - anyone who can view the NCR can likely upload attachments, 
        // or restrict to those who can edit it.
        // Simplified permission check: User must be related to the NCR or Admin/QC
        $canUpload = $user->isAdmin() || $user->isQCManager() ||
                     $ncr->finder_dept_id === $user->department_id ||
                     $ncr->receiver_dept_id === $user->department_id ||
                     $ncr->assigned_pic_id === $user->id;

        if (!$canUpload) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();

        // Store file
        $path = $file->store("ncr-attachments/{$ncr->id}", 'public');

        DB::beginTransaction();
        try {
            $attachment = NCRAttachment::create([
                'ncr_id' => $ncr->id,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_size' => $fileSize,
                'file_type' => $extension,
                'mime_type' => $mimeType,
                'attachment_type' => $request->attachment_type ?? 'evidence',
                'description' => $request->description,
                'uploaded_by_user_id' => $user->id,
                'uploaded_at' => now(),
            ]);

            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Attachment_Uploaded',
                "Attachment uploaded: {$fileName}",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attachment uploaded successfully',
                'data' => $attachment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Cleanup file if DB failed
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload attachment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Request $request, $id)
    {
        $attachment = NCRAttachment::findOrFail($id);
        $user = $request->user();

        // Check permission: Owner or Admin/QC
        if ($attachment->uploaded_by_user_id !== $user->id && !$user->isAdmin() && !$user->isQCManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $attachment->deleteFile(); // Uses model method to soft delete and remove file if needed
            
            // Log activity
            ActivityLog::logActivity(
                'NCR',
                $attachment->ncr_id,
                'Attachment_Deleted',
                "Attachment deleted: {$attachment->file_name}",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attachment',
            ], 500);
        }
    }

    /**
     * Download the specified attachment.
     */
    public function download(Request $request, $id)
    {
        $attachment = NCRAttachment::findOrFail($id);
        
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
