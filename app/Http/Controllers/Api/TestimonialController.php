<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{

    public function index(Request $request)
    {
        $query = Testimonial::query();

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('company', 'like', '%'.$request->search.'%')
                ->orWhere('testimony', 'like', '%'.$request->search.'%');
        }

        $perPage = $request->get('per_page', 10);

        $testimonials = $query
            ->latest()
            ->paginate($perPage);

        return response()->json($testimonials);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'company' => 'nullable',
            'testimony' => 'required',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        $path = null;

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('testimonials', 'public');
        }

        $testimonial = Testimonial::create([
            'name' => $request->name,
            'company' => $request->company,
            'testimony' => $request->testimony,
            'thumbnail' => $path
        ]);

        return response()->json($testimonial);
    }

    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        if ($testimonial->thumbnail) {
            $testimonial->thumbnail = asset('storage/' . $testimonial->thumbnail);
        }

        return response()->json($testimonial);
    }
    
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'company' => 'nullable',
            'testimony' => 'required',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('thumbnail')) {

            if ($testimonial->thumbnail) {
                Storage::disk('public')->delete($testimonial->thumbnail);
            }

            $testimonial->thumbnail = $request
                ->file('thumbnail')
                ->store('testimonials','public');
        }

        $testimonial->update([
            'name'=>$request->name,
            'company'=>$request->company,
            'testimony'=>$request->testimony
        ]);

        return response()->json($testimonial);
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json([
            'message' => 'Testimonial deleted'
        ]);
    }
}