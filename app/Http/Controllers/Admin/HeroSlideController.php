<?php

namespace App\Http\Controllers\Admin;

use App\Models\HeroSlide;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HeroSlideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $slidesQuery = HeroSlide::query();

        if ($request->filled('query')) {
            $search = $request->input('query');
            $slidesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('subtitle', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('is_active')) {
            $slidesQuery->where('is_active', $request->input('is_active'));
        }

        $slides = $slidesQuery->ordered()->paginate(10);

        return view('admin.pages.hero-slides.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.hero-slides.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:500',
            'button2_text' => 'nullable|string|max:100',
            'button2_url' => 'nullable|string|max:500',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'content_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'text_position' => 'nullable|in:left,right,center',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ];

        if ($request->filled('button_text') || $request->filled('button_url')) {
            $rules['button_text'] = 'required_with:button_url|nullable|string|max:100';
            $rules['button_url'] = 'required_with:button_text|nullable|string|max:500';
        }
        if ($request->filled('button2_text') || $request->filled('button2_url')) {
            $rules['button2_text'] = 'required_with:button2_url|nullable|string|max:100';
            $rules['button2_url'] = 'required_with:button2_text|nullable|string|max:500';
        }

        $validated = $request->validate($rules);

        try {
            $data = $validated;
            $data['is_active'] = $request->has('is_active');
            $data['text_position'] = $request->input('text_position', 'right');
            $data['order'] = (int) ($request->input('order', 0));

            if ($request->hasFile('background_image')) {
                $data['background_image'] = $request->file('background_image')
                    ->store('hero-slides', 'public');
            }

            if ($request->hasFile('content_image')) {
                $data['content_image'] = $request->file('content_image')
                    ->store('hero-slides', 'public');
            }

            HeroSlide::create($data);

            return redirect()->route('admin.hero-slides.index')
                ->with('success', 'تم إضافة الشريحة بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة الشريحة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.pages.hero-slides.edit', compact('heroSlide'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeroSlide $heroSlide)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:500',
            'button2_text' => 'nullable|string|max:100',
            'button2_url' => 'nullable|string|max:500',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'content_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'text_position' => 'nullable|in:left,right,center',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ];

        if ($request->filled('button_text') || $request->filled('button_url')) {
            $rules['button_text'] = 'required_with:button_url|nullable|string|max:100';
            $rules['button_url'] = 'required_with:button_text|nullable|string|max:500';
        }
        if ($request->filled('button2_text') || $request->filled('button2_url')) {
            $rules['button2_text'] = 'required_with:button2_url|nullable|string|max:100';
            $rules['button2_url'] = 'required_with:button2_text|nullable|string|max:500';
        }

        $validated = $request->validate($rules);

        try {
            $data = $validated;
            $data['is_active'] = $request->has('is_active');
            $data['text_position'] = $request->input('text_position', 'right');
            $data['order'] = (int) ($request->input('order', $heroSlide->order));

            if ($request->hasFile('background_image')) {
                if ($heroSlide->background_image && Storage::disk('public')->exists($heroSlide->background_image)) {
                    Storage::disk('public')->delete($heroSlide->background_image);
                }
                $data['background_image'] = $request->file('background_image')
                    ->store('hero-slides', 'public');
            } else {
                unset($data['background_image']);
            }

            if ($request->hasFile('content_image')) {
                if ($heroSlide->content_image && Storage::disk('public')->exists($heroSlide->content_image)) {
                    Storage::disk('public')->delete($heroSlide->content_image);
                }
                $data['content_image'] = $request->file('content_image')
                    ->store('hero-slides', 'public');
            } else {
                unset($data['content_image']);
            }

            $heroSlide->update($data);

            return redirect()->route('admin.hero-slides.index')
                ->with('success', 'تم تحديث الشريحة بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الشريحة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeroSlide $heroSlide)
    {
        try {
            if ($heroSlide->background_image && Storage::disk('public')->exists($heroSlide->background_image)) {
                Storage::disk('public')->delete($heroSlide->background_image);
            }
            if ($heroSlide->content_image && Storage::disk('public')->exists($heroSlide->content_image)) {
                Storage::disk('public')->delete($heroSlide->content_image);
            }
            $heroSlide->delete();

            return redirect()->route('admin.hero-slides.index')
                ->with('success', 'تم حذف الشريحة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('admin.hero-slides.index')
                ->with('error', 'حدث خطأ أثناء حذف الشريحة: ' . $e->getMessage());
        }
    }
}
