<?php

namespace App\Http\Controllers;

use App\Models\ChatTitle;
use Illuminate\Http\Request;
use App\Traits\HandlesApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatTitleController extends Controller
{
    use HandlesApiResponse;

    public function index()
    {
        return $this->safeCall(function () {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->errorResponse('User is not authenticated.', 401);
            }

            // Retrieve chat titles for the authenticated user with pagination
            $titles = Auth::user()->chatTitles()->get();

            // Convert paginated data to an array
            $titlesArray = $titles->toArray();

            return $this->successResponse('Chat titles retrieved successfully.', $titlesArray);
        });
    }


    public function store(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->errorResponse('User is not authenticated.', 401);
            }

            // Validate the input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
            ]);

            // Create a new chat title associated with the authenticated user
            $title = Auth::user()->chatTitles()->create([
                'title' => $validated['title'],
            ]);

            return $this->successResponse('Chat title created successfully.', $title->toArray(), 201);
        });
    }


    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->errorResponse('User is not authenticated.', 401);
            }

            // Find the chat title for the authenticated user
            $title = ChatTitle::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$title) {
                return $this->errorResponse('Chat title not found or you do not have access to it.', 404);
            }

            return $this->successResponse('Chat title retrieved successfully.', $title->toArray());
        });
    }


    public function update(Request $request, $id)
    {
        return $this->safeCall(function () use ($request, $id) {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->errorResponse('User is not authenticated.', 401);
            }

            // Validate the input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
            ]);

            // Find the chat title for the authenticated user
            $title = ChatTitle::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$title) {
                return $this->errorResponse('Chat title not found or you do not have access to it.', 404);
            }

            // Update the chat title
            $title->update([
                'title' => $validated['title'],
            ]);

            return $this->successResponse('Chat title updated successfully.', $title->toArray());
        });
    }

    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->errorResponse('User is not authenticated.', 401);
            }

            // Find the chat title for the authenticated user
            $title = ChatTitle::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$title) {
                return $this->errorResponse('Chat title not found or you do not have access to it.', 404);
            }

            $title->delete();

            return $this->successResponse('Chat title deleted successfully.', ['title' => $title]);
        });
    }

    public function search(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->errorResponse('User is not authenticated.', 401);
            }

            // Validate the input
            $validated = $request->validate([
                'query' => 'required|string',
            ]);

            // Search for chat titles for the authenticated user
            $titles = ChatTitle::where('title', 'like', '%' . $validated['query'] . '%')
                ->where('user_id', Auth::id())
                ->get();

            return $this->successResponse('Chat titles retrieved successfully.', $titles->toArray());
        });
    }

}
