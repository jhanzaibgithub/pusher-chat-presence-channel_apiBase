<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Events\ChatMessageSent;
use App\Events\GroupMessageSent;
use Illuminate\Http\Response;

class ChatController extends Controller
{
    public function users()
    {
        return User::where('id', '!=', Auth::id())->get();
    }

    public function getMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)->where('receiver_id', Auth::id());
        })->with('sender')->get();

        $user = User::findOrFail($userId);

        return response()->json([
         'status_code' => Response::HTTP_OK,
            'messages' => $messages,
            'user' => $user
        ]);
    }


public function send(Request $request)
{
    $request->validate([
        'message' => 'required|string',
        'receiver_id' => 'nullable|exists:users,id',
        'group_id' => 'nullable|exists:groups,id',
    ]);

    $message = Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $request->receiver_id, 
        'group_id' => $request->group_id,       
        'message' => $request->message,
    ]);

    $message->load('sender');

    if ($request->group_id) {
        broadcast(new GroupMessageSent($message))->toOthers();
    } else {
        broadcast(new ChatMessageSent($message))->toOthers();
    }

    return response()->json([ 'status_code' => Response::HTTP_CREATED,'response' => 'sent']);
}

}
