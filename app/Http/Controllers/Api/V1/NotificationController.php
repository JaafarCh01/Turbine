<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->notifications()->with('entity'); // Eager load the related entity

        // Allow filtering by 'seen' status
        if ($request->has('seen')) {
            $query->where('seen', $request->boolean('seen'));
        }

        return $query->latest()->paginate($request->get('per_page', 15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        // Authorization: Ensure the notification belongs to the authenticated user
        if ($notification->recipientId !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();
        return response()->json(null, 204);
    }

    // PATCH /notifications/{notification}/read
    public function markAsRead(Notification $notification)
    {
        // Authorization: Ensure the notification belongs to the authenticated user
        if ($notification->recipientId !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$notification->seen) {
            $notification->seen = true;
            $notification->save();
        }
        return response()->json($notification->load('entity'));
    }

    // PATCH /notifications/mark-all-read
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->notifications()->where('seen', false)->update(['seen' => true]);

        return response()->json(['message' => 'All unread notifications marked as read.']);
    }
}
