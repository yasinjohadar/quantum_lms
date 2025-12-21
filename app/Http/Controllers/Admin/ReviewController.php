<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * عرض قائمة جميع التقييمات مع فلترة
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'reviewable', 'approver']);

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            if ($request->type === 'subject') {
                $query->where('reviewable_type', Subject::class);
            } elseif ($request->type === 'class') {
                $query->where('reviewable_type', SchoolClass::class);
            }
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->forSubject($request->subject_id);
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $query->forClass($request->class_id);
        }

        // فلترة حسب التقييم
        if ($request->filled('rating')) {
            $query->withRating($request->rating);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'rating') {
            $query->orderBy('rating', $sortOrder);
        } elseif ($sortBy === 'helpful') {
            $query->orderBy('is_helpful_count', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $reviews = $query->paginate(20)->withQueryString();

        // الإحصائيات
        $stats = $this->reviewService->getGeneralStats();

        // البيانات للفلترة
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();

        return view('admin.pages.reviews.index', compact('reviews', 'stats', 'subjects', 'classes'));
    }

    /**
     * عرض تفاصيل التقييم
     */
    public function show(Review $review)
    {
        $review->load(['user', 'reviewable', 'approver', 'votes.user']);

        return view('admin.pages.reviews.show', compact('review'));
    }

    /**
     * الموافقة على التقييم
     */
    public function approve(Review $review)
    {
        try {
            $this->reviewService->approveReview($review, auth()->user());

            return redirect()->back()
                ->with('success', 'تم الموافقة على التقييم بنجاح');
        } catch (\Exception $e) {
            Log::error('Error approving review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الموافقة على التقييم');
        }
    }

    /**
     * رفض التقييم مع السبب
     */
    public function reject(Request $request, Review $review)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->reviewService->rejectReview($review, $request->reason, auth()->user());

            return redirect()->back()
                ->with('success', 'تم رفض التقييم بنجاح');
        } catch (\Exception $e) {
            Log::error('Error rejecting review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض التقييم');
        }
    }

    /**
     * حذف التقييم
     */
    public function destroy(Review $review)
    {
        try {
            $review->delete();

            return redirect()->route('admin.reviews.index')
                ->with('success', 'تم حذف التقييم بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف التقييم');
        }
    }

    /**
     * الموافقة الجماعية
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
        ]);

        try {
            $count = 0;
            DB::transaction(function () use ($request, &$count) {
                $reviews = Review::whereIn('id', $request->review_ids)->get();
                foreach ($reviews as $review) {
                    $this->reviewService->approveReview($review, auth()->user());
                    $count++;
                }
            });

            return redirect()->back()
                ->with('success', "تم الموافقة على {$count} تقييم بنجاح");
        } catch (\Exception $e) {
            Log::error('Error bulk approving reviews: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الموافقة الجماعية');
        }
    }

    /**
     * الرفض الجماعي
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $count = 0;
            DB::transaction(function () use ($request, &$count) {
                $reviews = Review::whereIn('id', $request->review_ids)->get();
                foreach ($reviews as $review) {
                    $this->reviewService->rejectReview($review, $request->reason, auth()->user());
                    $count++;
                }
            });

            return redirect()->back()
                ->with('success', "تم رفض {$count} تقييم بنجاح");
        } catch (\Exception $e) {
            Log::error('Error bulk rejecting reviews: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الرفض الجماعي');
        }
    }

    /**
     * عرض إعدادات التقييمات
     */
    public function settings()
    {
        $subjects = Subject::with('schoolClass')->ordered()->get();
        $classes = SchoolClass::with('stage')->ordered()->get();

        return view('admin.pages.reviews.settings', compact('subjects', 'classes'));
    }

    /**
     * حفظ إعدادات التقييمات
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'subjects' => 'nullable|array',
            'subjects.*.reviews_enabled' => 'boolean',
            'subjects.*.reviews_require_approval' => 'boolean',
            'classes' => 'nullable|array',
            'classes.*.reviews_enabled' => 'boolean',
            'classes.*.reviews_require_approval' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // تحديث إعدادات المواد
                if ($request->has('subjects')) {
                    foreach ($request->subjects as $subjectId => $settings) {
                        Subject::where('id', $subjectId)->update([
                            'reviews_enabled' => $settings['reviews_enabled'] ?? true,
                            'reviews_require_approval' => $settings['reviews_require_approval'] ?? true,
                        ]);
                    }
                }

                // تحديث إعدادات الصفوف
                if ($request->has('classes')) {
                    foreach ($request->classes as $classId => $settings) {
                        SchoolClass::where('id', $classId)->update([
                            'reviews_enabled' => $settings['reviews_enabled'] ?? true,
                            'reviews_require_approval' => $settings['reviews_require_approval'] ?? true,
                        ]);
                    }
                }
            });

            return redirect()->back()
                ->with('success', 'تم حفظ الإعدادات بنجاح');
        } catch (\Exception $e) {
            Log::error('Error saving review settings: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الإعدادات');
        }
    }
}
