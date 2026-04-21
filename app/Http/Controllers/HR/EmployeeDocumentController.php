<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string',
            'expiry_date' => 'nullable|date',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
        ]);

        $path = $request->file('document')->store('hr/documents', 'public');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'title' => $request->title,
            'document_type' => $request->document_type,
            'file_path' => 'storage/'.$path,
            'expiry_date' => $request->expiry_date,
            'is_verified' => false,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function verify(EmployeeDocument $document)
    {
        $document->update(['is_verified' => true]);

        return back()->with('success', 'Document verified successfully.');
    }

    public function destroy(EmployeeDocument $document)
    {
        Storage::disk('public')->delete(str_replace('storage/', '', $document->file_path));
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
