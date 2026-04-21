<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    /**
     * Create a new branch via API
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('current_tenant');
        if ($tenant && $tenant->reachedLimit('branches')) {
            return response()->json([
                'success' => false,
                'message' => 'Branch limit reached for your current plan. Please upgrade to add more branches.',
            ], 403);
        }

        try {
            // Validate the incoming data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_active' => 'boolean',
            ]);

            // Generate unique code if not provided
            $code = $validated['code'] ?? 'BR'.strtoupper(substr(md5(time().$validated['name']), 0, 6));

            // Prepare data for creation
            $data = [
                'name' => $validated['name'],
                'code' => $code,
                'is_active' => $validated['is_active'] ?? true,
            ];

            // Add optional fields only if provided
            if (! empty($validated['address'])) {
                $data['address'] = $validated['address'];
            }
            if (! empty($validated['phone'])) {
                $data['phone'] = $validated['phone'];
            }
            if (! empty($validated['email'])) {
                $data['email'] = $validated['email'];
            }

            // Set manager_id to current user if available
            if (Auth::check()) {
                $data['manager_id'] = Auth::id();
            }

            // Create the branch
            $branch = Branch::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully',
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.implode(', ', array_map(function ($item) {
                    return $item[0] ?? 'Unknown error';
                }, $e->errors())),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Branch creation error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create branch: '.$e->getMessage(),
            ], 500);
        }
    }
}
