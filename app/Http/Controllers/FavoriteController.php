<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request, Reply $reply)
    {
        if (!$reply->favoritedBy()->where('user_id', auth()->id())->exists()) {
            $reply->favoritedBy()->attach(auth()->id());
            return response()->json(['message' => 'Reply favorited'], 201);
        } else {
            return response()->json(['message' => 'Reply already favorited'], 200);
        }
    }

    public function delete(Reply $reply)
    {
        if ($reply->favoritedBy()->where('user_id', auth()->id())->exists()) {
            $reply->favoritedBy()->detach(auth()->id());
            return response()->json(['message' => 'Reply Favorite deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Reply was not favorited'], 200);
        }
    }
}
