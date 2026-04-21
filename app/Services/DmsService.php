<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class DMSService
{
    /**
     * Upload a new document or a new version.
     */
    public function uploadDocument(UploadedFile $file, array $data, ?Document $existingDocument = null)
    {
        $tenantId = $data['tenant_id'] ?? (app()->has('current_tenant_id') ? app('current_tenant_id') : 'global');
        $path = $file->store('documents/'.$tenantId);

        if ($existingDocument) {
            // Create new version
            DocumentVersion::create([
                'document_id' => $existingDocument->id,
                'file_path' => $existingDocument->file_path,
                'version_number' => $existingDocument->version,
                'updated_by' => Auth::id(),
            ]);

            $newVersion = (float) $existingDocument->version + 0.1;
            $existingDocument->update([
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'version' => (string) $newVersion,
                'uploaded_by' => Auth::id(),
            ]);

            return $existingDocument;
        }

        return Document::create(array_merge($data, [
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'version' => '1.0',
        ]));
    }

    /**
     * Create a new version of a document.
     */
    public function createVersion(Document $document, UploadedFile $file, ?string $changeLog = null)
    {
        return $this->uploadDocument($file, ['metadata' => ['change_log' => $changeLog]], $document);
    }

    /**
     * Create a folder structure.
     */
    public function createFolder(string $name, ?int $parentId = null, ?int $tenantId = null, ?int $branchId = null)
    {
        return DocumentFolder::create([
            'name' => $name,
            'parent_id' => $parentId,
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
        ]);
    }

    /**
     * Verify a document.
     */
    public function verifyDocument(Document $document)
    {
        $document->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return $document;
    }

    /**
     * Link a document to a specific model (Customer, Vendor, Sale, etc.)
     */
    public function linkToModel(Document $document, string $modelType, int $modelId)
    {
        return DB::table('document_links')->insert([
            'document_id' => $document->id,
            'linkable_type' => $modelType,
            'linkable_id' => $modelId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
