<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\ApiService;
use Illuminate\Http\Request;

class ApiManagerController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $apiKeys = ApiKey::latest()->get();

        return view('admin.api.index', compact('apiKeys'));
    }

    public function storeKey(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->apiService->generateKey($request->name);

        return redirect()->back()->with('success', 'API Key generated successfully.');
    }

    public function rotate(ApiKey $apiKey)
    {
        $this->apiService->rotateKey($apiKey);

        return redirect()->back()->with('success', 'API Secret rotated successfully.');
    }

    public function update(Request $request, ApiKey $apiKey)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $apiKey->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'API Key status updated.');
    }

    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();

        return redirect()->back()->with('success', 'API Key revoked successfully.');
    }
}
