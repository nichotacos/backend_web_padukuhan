<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // Index
    public function index(Request $request)
    {
        $posts = Post::all();

        if ($posts->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data konten kosong',
            ], 404);
        }

        $posts = Post::query();
        $sorted = $posts->orderBy('created_at', 'desc')->get();

        if ($request->has('id')) {
            $posts->where('id', $request->id);
        }

        if ($request->has('limit')) {
            $posts->take($request->limit);
        }

        $sorted = $posts->get();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menampilkan data konten',
            'data' => $sorted
        ], 200);
    }

    // Post
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $hashName = $image->hashName();
            Storage::disk('public')->putFileAs('img', $image, $hashName);
        } else {
            $hashName = null;
        }

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $hashName
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Konten berhasil ditambahkan',
            'data' => $post
        ], 201);
    }

    // Update
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $image = $request->file('image');
        $image->storeAs('public/images', $image->hashName());

        $post = Post::find($id);

        Storage::disk('public')->delete('images/' . $post->image);

        $post->title = $request->title;
        $post->content = $request->content;
        $post->image = $image->hashName();
        $post->save();

        return response()->json([
            'status' => true,
            'message' => 'Konten berhasil diupdate',
            'data' => $post
        ], 200);
    }

    // Delete
    public function destroy($id)
    {
        $post = Post::find($id);

        Storage::disk('public')->delete('images/' . $post->image);

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Konten berhasil dihapus',
        ], 200);
    }
}
