<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $notifications = Notification::with(['company', 'createdBy', 'user'])->get();
            return response()->json(['data' => $notifications], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve notifications'], 500);
        }
    }

    public function show($id)
    {
        try {
            $notification = Notification::with(['company', 'createdBy', 'user'])->find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            return response()->json(['data' => $notification], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve notification'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'message' => 'nullable|string',
            'crBy' => 'required|exists:users,id',
        ]);

        try {
            $notification = Notification::create($validatedData);
            return response()->json(['data' => $notification], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create notification'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'message' => 'nullable|string',
            'crBy' => 'required|exists:users,id',
        ]);

        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            $notification->update($validatedData);
            return response()->json(['data' => $notification], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update notification'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            $notification->delete();
            return response()->json(['message' => 'Notification deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            $notification->update(['is_read' => true]);
            return response()->json(['message' => 'Notification marked as read'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    public function getUnreadNotifications()
    {
        try {
            $notifications = Notification::with(['company', 'createdBy', 'user'])
                ->where('is_read', false)
                ->get();
            return response()->json(['data' => $notifications], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve unread notifications'], 500);
        }
    }
}
