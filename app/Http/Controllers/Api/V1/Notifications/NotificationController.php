<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Notifications\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Notifications Retrieved Successfully.',
            'data' => NotificationResource::collection($notifications->items()),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function read(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->unreadNotifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification Marked As Read Successfully.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All Notifications Marked As Read Successfully.',
        ]);
    }
}
