<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformReview;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PlatformReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:platform-reviews-list'])->only('index');
        $this->middleware(['permission:platform-reviews-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:platform-reviews-approve'])->only(['approve', 'reject']);
    }

    /**
     * عرض قائمة آراء الطلاب
     */
    public function index(Request $request): View
    {
        $query = PlatformReview::with(['user', 'schoolClass'])->ordered();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reviews = $query->paginate(15)->withQueryString();

        return view('admin.pages.platform-reviews.index', compact('reviews'));
    }

    /**
     * عرض نموذج تعديل الرأي
     */
    public function edit(PlatformReview $platform_review): View
    {
        $platform_review->load(['user', 'schoolClass']);
        $classes = SchoolClass::active()->ordered()->get();

        return view('admin.pages.platform-reviews.edit', [
            'review' => $platform_review,
            'classes' => $classes,
        ]);
    }

    /**
     * تحديث الرأي (تعليق، حالة، صف، صورة)
     */
    public function update(Request $request, PlatformReview $platform_review): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
            'status' => 'required|in:pending,approved,rejected',
            'class_id' => 'nullable|exists:classes,id',
            'order' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = [
            'comment' => $validated['comment'],
            'status' => $validated['status'],
            'class_id' => $validated['class_id'] ?? null,
            'order' => $validated['order'] ?? $platform_review->order,
        ];

        if ($validated['status'] === 'approved' && !$platform_review->approved_at) {
            $data['approved_at'] = now();
            $data['approved_by'] = auth()->id();
        }
        if ($validated['status'] !== 'approved') {
            $data['approved_at'] = null;
            $data['approved_by'] = null;
        }

        if ($request->hasFile('photo')) {
            try {
                if ($platform_review->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($platform_review->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($platform_review->photo);
                }
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $data['photo'] = $photo->storeAs('platform_reviews/photos', $photoName, 'public');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'فشل رفع الصورة: ' . $e->getMessage());
            }
        }

        if ($request->boolean('remove_photo') && $platform_review->photo) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($platform_review->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($platform_review->photo);
                }
            } catch (\Exception $e) {
                // ignore
            }
            $data['photo'] = null;
        }

        $platform_review->update($data);

        return redirect()->route('admin.platform-reviews.index')
            ->with('success', 'تم تحديث الرأي بنجاح.');
    }

    /**
     * اعتماد الرأي
     */
    public function approve(PlatformReview $platform_review): RedirectResponse
    {
        $platform_review->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'تم اعتماد الرأي.');
    }

    /**
     * رفض الرأي
     */
    public function reject(PlatformReview $platform_review): RedirectResponse
    {
        $platform_review->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return redirect()->back()->with('success', 'تم رفض الرأي.');
    }
}
