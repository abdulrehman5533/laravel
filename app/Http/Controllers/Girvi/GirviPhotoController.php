<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\GirviItem;
use App\Models\GirviItemPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GirviPhotoController extends Controller
{
    public function upload(Request $request, GirviItem $item)
    {
        $validated = $request->validate([
            'photos' => 'required|array|min:1|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'photo_type' => 'required|in:before,after,general',
            'notes' => 'nullable|string',
        ]);

        $uploaded = [];

        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('girvi/items', 'public');
            
            $photoRecord = GirviItemPhoto::create([
                'girvi_item_id' => $item->id,
                'photo_path' => $path,
                'photo_type' => $validated['photo_type'],
                'notes' => $validated['notes'] ?? null,
            ]);
            
            $uploaded[] = $photoRecord;
        }

        return back()->with('success', count($uploaded) . ' photo(s) uploaded successfully');
    }

    public function delete(GirviItemPhoto $photo)
    {
        if (Storage::disk('public')->exists($photo->photo_path)) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $photo->delete();

        return back()->with('success', 'Photo deleted successfully');
    }

    public function gallery(GirviItem $item)
    {
        $item->load(['photos', 'girvi.customer']);
        
        return view('girvi.photos.gallery', compact('item'));
    }
}
