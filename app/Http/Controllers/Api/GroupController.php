<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\GroupMessageResource;

class GroupController extends Controller
{
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

        $userIds = array_unique(
            array_map('intval', array_merge($request->user_ids, [Auth::id()]))
        );
        $group->users()->attach($userIds);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
            'members' => $group->users()->get()
        ]);
    }

    public function myGroups()
    {
        $groups = Auth::user()->groups()->with('users')->get();

        return response()->json($groups);
    }

    public function addUsers(Request $request, $groupId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group = Group::findOrFail($groupId);

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
    $group = Group::with('users')->findOrFail($groupId);
    if (!$group->users->contains(Auth::id())) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $messages = Message::with('sender')
        ->where('group_id', $groupId)
        ->orderBy('created_at', 'asc')
        ->get();
    $data = [
        'group_name' => $group->name,
        'messages' => GroupMessageResource::collection($messages),
    ];
       return response()->json($data);

}
}
