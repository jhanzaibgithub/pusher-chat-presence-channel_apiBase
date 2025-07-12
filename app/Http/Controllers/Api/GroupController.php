<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    // ✅ Create a new group and add users (including creator)
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:groups,name',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group = Group::create([
            'name' => $request->name,
        ]);

        // Add creator + others
        $userIds = array_unique(array_merge($request->user_ids, [Auth::id()]));
        $group->users()->attach($userIds);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
            'members' => $group->users()->get()
        ]);
    }

    // ✅ List all groups of the current user
    public function myGroups()
    {
        $groups = Auth::user()->groups()->with('users')->get();

        return response()->json($groups);
    }

    // ✅ Add user(s) to existing group
    public function addUsers(Request $request, $groupId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group = Group::findOrFail($groupId);

        // (Optional) check if the current user belongs to this group before modifying
        if (! $group->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $group->users()->attach($request->user_ids);

        return response()->json([
            'message' => 'Users added to group',
            'group' => $group->load('users')
        ]);
    }
    public function getGroupMessages($groupId)
{
    // Optional: authorize only members to access messages
    $group = Group::with('users')->findOrFail($groupId);
    if (!$group->users->contains(Auth::id())) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Fetch messages where group_id matches
    $messages = Message::with('sender')
        ->where('group_id', $groupId)
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json([
        'group_name' => $group->name,
        'messages' => $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'message' => $msg->message,
                'sent_at' => $msg->created_at->toDateTimeString(),
            ];
        })
    ]);
}
}
