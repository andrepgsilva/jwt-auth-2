<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'sometimes|required|email'
        ]);

        if (strlen($request->email) > 0) {
            $user = User::where('email', $request->email)->first();
            $allPostCategories = Post::where('user_id', $user->id);
        }

        $allPostCategories = Post::latest();
        
        $allPostCategories = $allPostCategories
                                ->with(['category:id,name','firstMedia'])
                                ->orderBy('created_at', 'DESC')
                                ->get(['id', 'name', 'category_id']);

        return response()->json($allPostCategories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_name' => 'required|string|max:255|min:3',
            'user_email' => 'required|email',
            'post_category' => 'required|numeric',
        ]);

        $postCategory = PostCategory::where('id', $request->post_category)->first();

        $jsonResponseError = response()->json([
            'message' => 'An error has occurred!',
        ], 500);

        if ($postCategory === null) return $jsonResponseError;

        $user = User::where('email', $request->user_email)->first();
        $post = Post::create([
            'name' => $request->post_name,
            'category_id' => $postCategory->id,
            'user_id' => $user->id
        ]);

        $user->posts()->save($post);

        $post->addMedia($request->file('file'))->toMediaCollection();

        return response()->json($post);
    }
}
