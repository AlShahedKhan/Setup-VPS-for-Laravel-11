<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogRequest;
use App\Http\Requests\BlogUpdateRequest;
use App\Models\Blog;
use App\Traits\HandlesApiResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BlogController extends Controller
{
    use HandlesApiResponse;
    public function index()
    {
        return $this->safeCall(function () {
            $blogs = Blog::paginate(10);
            return $this->successResponse(
                'Blogs fetched successfully',
                ['blogs' => $blogs]
            );
        });
    }

    public function store(BlogRequest $request)
    {
        return $this->safeCall(function () use ($request) {
            // Retrieve validated data from BlogRequest
            $validated = $request->validated();

            // Handle the image upload if an image is provided
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('images', 'public');
            }

            // Create the blog with the validated data
            $blog = Blog::create($validated);

            // Return a success response
            return $this->successResponse(
                'Blog created successfully',
                ['blog' => $blog]
            );
        });
    }


    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            $blog = Blog::findOrFail($id);

            return $this->successResponse(
                'Blog fetched successfully',
                ['blog' => $blog]
            );
        });
    }

    public function update(Request $request, Blog $blog)
    {
        \Log::info('Update Request Data:', $request->all());

        try {
            // Validate the incoming request
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'author' => 'required|string|max:255',
                'status' => 'in:draft,published',
                'tags' => 'nullable|string',
                'published_at' => 'nullable|date',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Update the blog fields
            $blog->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'author' => $request->input('author'),
                'status' => $request->input('status', 'draft'),
                'tags' => $request->input('tags'),
                'published_at' => $request->input('published_at'),
            ]);

            // Handle the image file
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($blog->image) {
                    $oldImagePath = storage_path("app/public/images/{$blog->image}");
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Save the new image
                $destinationPath = storage_path('app/public/images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move($destinationPath, $fileName);

                // Update the image path in the database
                $blog->update([
                    'image' => $fileName,
                ]);
            }

            return response()->json([
                'message' => 'Blog updated successfully',
                'blog' => $blog,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Blog not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the blog.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            $blog = Blog::findOrFail($id);
            $blog->delete();
            return $this->successResponse(
                'Blog deleted successfully',
                ['blog' => $blog]
            );
        });
    }
}
