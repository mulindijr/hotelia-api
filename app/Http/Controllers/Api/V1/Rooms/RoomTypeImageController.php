<?php

namespace App\Http\Controllers\Api\V1\Rooms;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\RoomTypeImage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomTypeImageController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Hotel $hotel, RoomType $roomType): JsonResponse
    {
        $this->authorize('view', [$roomType, $hotel]);

        return response()->json([
            'success' => true,
            'data' => $roomType->images,
        ]);
    }

    public function store(Request $request, Hotel $hotel, RoomType $roomType): JsonResponse
    {
        $this->authorize('update', [$roomType, $hotel]);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $file = $request->file('image');
        // Save to public storage
        $path = $file->store('room_types', 'public');

        // Generate full URL
        $fullPath = asset('storage/' . $path);

        $image = $roomType->images()->create([
            'image_path' => $fullPath,
            'is_primary' => $roomType->images()->count() === 0, // Make first image primary
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'data' => $image,
        ], 201);
    }

    public function setPrimary(Request $request, Hotel $hotel, RoomType $roomType, RoomTypeImage $image): JsonResponse
    {
        $this->authorize('update', [$roomType, $hotel]);

        if ($image->room_type_id !== $roomType->id) {
            abort(404, 'Image does not belong to this room type.');
        }

        // Set all others to false
        $roomType->images()->update(['is_primary' => false]);
        
        // Set this one to true
        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary image updated.',
            'data' => $image,
        ]);
    }

    public function destroy(Request $request, Hotel $hotel, RoomType $roomType, RoomTypeImage $image): JsonResponse
    {
        $this->authorize('update', [$roomType, $hotel]);

        if ($image->room_type_id !== $roomType->id) {
            abort(404, 'Image does not belong to this room type.');
        }

        // Extract relative path from URL to delete from storage
        $relativePath = str_replace(asset('storage/'), '', $image->image_path);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $image->delete();

        // If it was primary and other images exist, make the newest one primary
        if ($image->is_primary && $roomType->images()->count() > 0) {
            $roomType->images()->latest()->first()->update(['is_primary' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }
}
