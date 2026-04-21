<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DmsService;
use Illuminate\Http\Request;

class DmsController extends Controller
{
    protected $dmsService;

    public function __construct(DmsService $dmsService)
    {
        $this->dmsService = $dmsService;
    }

    public function index()
    {
        $documents = Document::with(['versions', 'uploader'])->latest()->get();

        return view('admin.dms.index', compact('documents'));
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'file' => 'required|mimes:pdf,doc,docx,jpg,png|max:10240',
            'folder_id' => 'nullable|exists:document_folders,id',
        ]);

        $this->dmsService->uploadDocument($request->file('file'), $request->only(['title', 'folder_id', 'branch_id', 'metadata']));

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:document_folders,id',
        ]);

        $this->dmsService->createFolder(
            $request->name,
            $request->parent_id,
            null, // Let BelongsToTenant handle this
            $request->branch_id
        );

        return redirect()->back()->with('success', 'Folder created successfully.');
    }

    public function updateVersion(Request $request, Document $document)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx,jpg,png|max:10240',
            'change_log' => 'required|string',
        ]);

        $this->dmsService->createVersion($document, $request->file('file'), $request->change_log);

        return redirect()->back()->with('success', 'New version uploaded successfully.');
    }
}
