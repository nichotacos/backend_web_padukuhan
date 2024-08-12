<?php

namespace App\Http\Controllers;

use App\Models\Post;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
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

            $updloadedFileUrl = Cloudinary::upload($image->getRealPath())->getSecurePath();
        } else {
            $updloadedFileUrl = null;
        }

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $updloadedFileUrl
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
            'title' => 'string|max:255',
            'content' => 'string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // echo "echo request all";
        // echo $request;

        $title = $request->input('title');
        $content = $request->input('content');
        // $image = $request->file('image');
        // echo "echo title content";
        // echo $title;
        // echo $content;
        // echo "end echo title content";

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // echo $post;

        if ($request->hasFile('image')) {
            try {
                // echo "masuk file image";
                $image = $request->file('image');

                if ($post->image) {;
                    $parsedUrl = parse_url($post->image, PHP_URL_PATH);
                    $pathInfo = pathinfo($parsedUrl);
                    $publicId = $pathInfo['filename'];
                    // echo $publicId;
                    Cloudinary::destroy($publicId);
                } else {
                    // echo "image not found";
                }

                $uploadedFileUrl = Cloudinary::upload($image->getRealPath())->getSecurePath();
                $post->image = $uploadedFileUrl;
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Image upload failed: ' . $e->getMessage()
                ], 404);
            }
        } else {
            return response()->json($validator->errors(), 200);
        }

        // echo $post;
        // echo "before update";

        $post->title = $title;
        $post->content = $content;
        // echo "after update";
        // echo $post;
        $post->save();

        // echo "final";
        // echo $post;

        return response()->json([
            'status' => true,
            'message' => 'Konten berhasil diupdate',
            'data' => $post,
        ], 200);
    }

    // Delete
    public function destroy($id)
    {
        $post = Post::find($id);

        Cloudinary::destroy($post->image);

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Konten berhasil dihapus',
        ], 200);
    }
}
