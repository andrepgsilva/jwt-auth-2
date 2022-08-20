<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $allPostCategories = PostCategory::where('id', '>', 0)->orderBy('name')->get(['id', 'name']);

        return response()->json($allPostCategories);
    }
}
