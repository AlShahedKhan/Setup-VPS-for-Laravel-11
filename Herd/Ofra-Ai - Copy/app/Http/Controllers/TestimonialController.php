<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Traits\HandlesApiResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use HandlesApiResponse;
    public function index()
    {
        return $this->safeCall(function () {
            $testimonials = Testimonial::all();

            return $this->successResponse(
                'Testimonials retrieved successfully.',
                ['testimonials' => $testimonials]
            );
        });
    }

    public function store(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'name' => 'required|string|max:255',
                'designation' => 'required|string|max:255',
            ]);

            $testimonial = Testimonial::create($validated);

            return $this->successResponse(
                'Testimonial created successfully.',
                ['testimonial' => $testimonial]
            );
        });
    }

    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            $testimonial = Testimonial::findOrFail($id);

            return $this->successResponse(
                'Testimonial retrieved successfully.',
                ['testimonial' => $testimonial]
            );
        });
    }

    public function update(Request $request, $id)
    {
        return $this->safeCall(function () use ($request, $id) {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'name' => 'sometimes|string|max:255',
                'designation' => 'sometimes|string|max:255',
            ]);

            $testimonial = Testimonial::findOrFail($id);
            $testimonial->update($validated);

            return $this->successResponse(
                'Testimonial updated successfully.',
                ['testimonial' => $testimonial]
            );
        });
    }

    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            $testimonial = Testimonial::findOrFail($id);
            $testimonial->delete();

            return $this->successResponse(
                'Testimonial deleted successfully.',
                ['testimonial' => $testimonial]
            );
        });
    }
}
