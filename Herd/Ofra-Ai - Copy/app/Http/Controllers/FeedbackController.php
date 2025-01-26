<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesApiResponse;

class FeedbackController extends Controller
{
    use HandlesApiResponse;

    // Authenticated user gives feedback
    public function store(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            $request->validate([
                'feedback' => 'required|string|max:1000',
            ]);

            $feedback = Feedback::create([
                'user_id' => Auth::id(),
                'feedback' => $request->feedback,
            ]);

            return $this->successResponse('Feedback submitted successfully.', $feedback->toArray(), 201);
        });
    }

    // Admin fetches all feedback
    public function index()
    {
        return $this->safeCall(function () {
            if (!Auth::user()->is_admin) {
                return $this->errorResponse('You are not authorized to perform this action.', 403);
            }

            $feedback = Feedback::with('user:id,first_name,last_name,email')->get();

            return $this->successResponse('All feedback retrieved successfully.', $feedback->toArray());
        });
    }

    // Admin fetches feedback by ID
    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            if (!Auth::user()->is_admin) {
                return $this->errorResponse('You are not authorized to perform this action.', 403);
            }

            $feedback = Feedback::with('user:id,first_name,last_name,email')->find($id);

            if (!$feedback) {
                return $this->errorResponse('Feedback not found.', 404);
            }

            return $this->successResponse('Feedback retrieved successfully.', $feedback->toArray());
        });
    }

    // Admin deletes feedback by ID
    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            if (!Auth::user()->is_admin) {
                return $this->errorResponse('You are not authorized to perform this action.', 403);
            }

            $feedback = Feedback::find($id);

            if (!$feedback) {
                return $this->errorResponse('Feedback not found.', 404);
            }

            $feedback->delete();

            return $this->successResponse('Feedback deleted successfully.');
        });
    }
}
