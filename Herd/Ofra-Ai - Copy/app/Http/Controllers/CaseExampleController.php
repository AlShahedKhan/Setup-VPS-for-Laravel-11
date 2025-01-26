<?php

namespace App\Http\Controllers;

use App\Models\CaseExample;
use App\Traits\HandlesApiResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CaseExampleController extends Controller
{
    use HandlesApiResponse;

    // List all case examples
    public function index()
    {
        return $this->safeCall(function () {
            $caseExamples = CaseExample::all();
            return $this->successResponse(
                'Case examples fetched successfully.',
                ['caseExamples' => $caseExamples]
            );
        });
    }

    // Store a new case example
    public function store(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            // Validate the incoming request
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Handle the image upload if an image is provided
            if ($request->hasFile('image')) {
                $validatedData['image'] = $request->file('image')->store('uploads', 'public');
            }

            // Create the case example
            $caseExample = CaseExample::create($validatedData);

            return $this->successResponse(
                'Case example created successfully.',
                ['caseExample' => $caseExample]
            );
        });
    }

    // Show a specific case example
    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            $caseExample = CaseExample::findOrFail($id);
            return $this->successResponse(
                'Case example fetched successfully.',
                ['caseExample' => $caseExample]
            );
        });
    }

    // Update a specific case example
    public function update(Request $request, $id)
    {
        return $this->safeCall(function () use ($request, $id) {
            // Find the case example by ID
            $caseExample = CaseExample::findOrFail($id);

            // Validate the incoming request
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Handle the image upload
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($caseExample->image && file_exists(public_path('storage/' . $caseExample->image))) {
                    \Log::info('Deleting old image: ' . public_path('storage/' . $caseExample->image));
                    unlink(public_path('storage/' . $caseExample->image));
                }

                // Store the new image
                $validatedData['image'] = $request->file('image')->store('uploads', 'public');
            }

            // Update the case example
            $caseExample->update($validatedData);

            return $this->successResponse(
                'Case example updated successfully.',
                ['caseExample' => $caseExample]
            );
        });
    }


    // Delete a specific case example
    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            $caseExample = CaseExample::findOrFail($id);

            // Delete the associated image if it exists
            if ($caseExample->image && file_exists(public_path('storage/' . $caseExample->image))) {
                \Log::info('Deleting associated image: ' . public_path('storage/' . $caseExample->image));
                unlink(public_path('storage/' . $caseExample->image));
            }

            $caseExample->delete();

            return $this->successResponse(
                'Case example deleted successfully.',
                ['caseExample' => $caseExample]
            );
        });
    }
}
