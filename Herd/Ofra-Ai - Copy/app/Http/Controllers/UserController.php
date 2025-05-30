<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HandlesApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use HandlesApiResponse;

    public function updateProfile(Request $request)
    {
        return $this->safeCall(function () use ($request) {

            // Validate the request
            $validatedData = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
                'old_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = Auth::user(); // Get the authenticated user

            // Update password if `old_password` and `new_password` are provided
            if (!empty($validatedData['old_password'])) {
                // Verify the old password
                if (!Hash::check($validatedData['old_password'], $user->password)) {
                    return $this->errorResponse(
                        'The old password is incorrect.',
                        400
                    );
                    //    return response()->json(['error' => 'The old password is incorrect.'], 400);
                }

                // Update the password
                $user->password = Hash::make($validatedData['new_password']);
            }

            // Update other profile details
            $user->first_name = $validatedData['first_name'];
            $user->last_name = $validatedData['last_name'];
            $user->email = $validatedData['email'];
            $user->save();

            return $this->successResponse(
                'Profile updated successfully.',
                ['user' => $user]
            );
        });
    }
}
