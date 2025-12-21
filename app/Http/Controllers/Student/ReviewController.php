<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\ReviewVote;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {
        $this->middleware('auth');
    }

    /**
     * عرض نموذج إنشاء تقييم
     */
    public function create(Request $request)
    {
        $type = $request->get('type'); // subject or class
        $id = $request->get('id');

        if (!$type || !$id) {
            return redirect()->back()
                ->with('error', 'البيانات المطلوبة غير موجودة');
        }

        $reviewable = null;
        if ($type === 'subject') {
            $reviewable = Subject::findOrFail($id);
        } elseif ($type === 'class') {
            $reviewable = SchoolClass::findOrFail($id);
        }

        // التحقق من إمكانية التقييم
        if (!$this->reviewService->canUserReview(Auth::user(), $reviewable)) {
            return redirect()->back()
                ->with('error', 'لا يمكنك تقييم هذا العنصر');
        }

        // التحقق من وجود تقييم سابق (غير محذوف)
        $existingReview = Review::where('user_id', Auth::id())
            ->where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->whereNull('deleted_at')
            ->first();

        return view('student.pages.reviews.create', [
            'reviewable' => $reviewable,
            'type' => $type,
            'existingReview' => $existingReview,
        ]);
    }

    /**
     * حفظ التقييم
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:subject,class',
            'id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        try {
            $reviewable = null;
            if ($request->type === 'subject') {
                $reviewable = Subject::findOrFail($request->id);
            } elseif ($request->type === 'class') {
                $reviewable = SchoolClass::findOrFail($request->id);
            }

            $review = $this->reviewService->createReview(
                Auth::user(),
                $reviewable,
                $request->rating,
                $request->title,
                $request->comment,
                $request->boolean('is_anonymous', false)
            );

            $message = $review->isApproved() 
                ? 'تم نشر التقييم بنجاح' 
                : 'تم إرسال التقييم بنجاح. سيتم مراجعته قبل النشر';

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error creating review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage() ?: 'حدث خطأ أثناء إنشاء التقييم')
                ->withInput();
        }
    }

    /**
     * عرض نموذج تعديل التقييم
     */
    public function edit(Review $review)
    {
        // التحقق من أن التقييم يخص المستخدم
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $review->load('reviewable');

        return view('student.pages.reviews.edit', compact('review'));
    }

    /**
     * تحديث التقييم
     */
    public function update(Request $request, Review $review)
    {
        // التحقق من أن التقييم يخص المستخدم
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        try {
            $this->reviewService->updateReview(
                $review,
                $request->rating,
                $request->title,
                $request->comment,
                $request->boolean('is_anonymous', false)
            );

            $message = $review->fresh()->isApproved() 
                ? 'تم تحديث التقييم بنجاح' 
                : 'تم تحديث التقييم بنجاح. سيتم مراجعته قبل النشر';

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error updating review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث التقييم')
                ->withInput();
        }
    }

    /**
     * حذف التقييم الخاص بالمستخدم
     */
    public function destroy(Review $review)
    {
        // التحقق من أن التقييم يخص المستخدم
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $review->delete();

            return redirect()->back()
                ->with('success', 'تم حذف التقييم بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف التقييم');
        }
    }

    /**
     * إضافة/إزالة صوت "مفيد"
     */
    public function toggleHelpful(Review $review)
    {
        // لا يمكن للمستخدم التصويت على تقييمه الخاص
        if ($review->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك التصويت على تقييمك الخاص',
            ], 403);
        }

        // التحقق من أن التقييم معتمد
        if (!$review->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن التصويت على تقييم غير معتمد',
            ], 403);
        }

        try {
            $vote = ReviewVote::where('review_id', $review->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($vote) {
                // إزالة التصويت
                $vote->delete();
                $review->decrementHelpfulCount();
                $isHelpful = false;
            } else {
                // إضافة تصويت
                ReviewVote::create([
                    'review_id' => $review->id,
                    'user_id' => Auth::id(),
                    'is_helpful' => true,
                ]);
                $review->incrementHelpfulCount();
                $isHelpful = true;
            }

            return response()->json([
                'success' => true,
                'is_helpful' => $isHelpful,
                'helpful_count' => $review->fresh()->is_helpful_count,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling helpful vote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التصويت',
            ], 500);
        }
    }
}
