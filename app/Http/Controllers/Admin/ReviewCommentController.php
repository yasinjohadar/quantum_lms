<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewComment;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:review-comment-create'])->only('store', 'reply');
        $this->middleware(['permission:review-comment-edit'])->only('update');
        $this->middleware(['permission:review-comment-delete'])->only('destroy');
        $this->middleware(['permission:review-comment-resolve'])->only('resolve', 'unresolve');
    }

    /**
     * إنشاء ملاحظة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'reviewable_type' => 'required|string|in:App\Models\Lesson,App\Models\Quiz',
            'reviewable_id' => 'required|integer',
            'comment' => 'required|string|min:3|max:5000',
        ]);

        $user = auth()->user();
        $reviewableType = $request->input('reviewable_type');
        $reviewableId = $request->input('reviewable_id');

        // جلب العنصر المرتبط
        $reviewable = $reviewableType::findOrFail($reviewableId);

        // التحقق من الصلاحيات
        if ($user->usesSupervisorAssignmentScope()) {
            // المشرف يمكنه إضافة ملاحظات فقط على العناصر المخصصة له
            if ($reviewable instanceof Lesson) {
                $canAccess = $reviewable->unit->section->subject->schoolClass
                    && $user->isAssignedToClassAsSupervisor($reviewable->unit->section->subject->schoolClass->id)
                    || $user->isAssignedToSubjectAsSupervisor($reviewable->unit->section->subject->id);
                if (!$canAccess) {
                    return response()->json(['error' => 'غير مصرح لك بإضافة ملاحظات على هذا العنصر'], 403);
                }
            } elseif ($reviewable instanceof Quiz) {
                $canAccess = $reviewable->subject->schoolClass
                    && $user->isAssignedToClassAsSupervisor($reviewable->subject->schoolClass->id)
                    || $user->isAssignedToSubjectAsSupervisor($reviewable->subject->id);
                if (!$canAccess) {
                    return response()->json(['error' => 'غير مصرح لك بإضافة ملاحظات على هذا العنصر'], 403);
                }
            }
        }

        // إنشاء الملاحظة
        $comment = ReviewComment::create([
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId,
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
        ]);

        // إرسال إشعار للمعلم إذا كانت الملاحظة من المشرف
        if ($user->usesSupervisorAssignmentScope() && $reviewable->created_by) {
            // TODO: إرسال إشعار للمعلم
        }

        Log::info('تم إنشاء ملاحظة مراجعة جديدة', [
            'comment_id' => $comment->id,
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الملاحظة بنجاح',
            'comment' => $comment->load('user'),
        ]);
    }

    /**
     * تحديث ملاحظة
     */
    public function update(Request $request, ReviewComment $comment)
    {
        $request->validate([
            'comment' => 'required|string|min:3|max:5000',
        ]);

        $user = auth()->user();

        // التحقق من أن المستخدم هو صاحب الملاحظة أو أدمن/مشرف
        if ($comment->user_id !== $user->id && !$user->canReviewContent()) {
            return response()->json(['error' => 'غير مصرح لك بتعديل هذه الملاحظة'], 403);
        }

        $comment->update([
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملاحظة بنجاح',
            'comment' => $comment->fresh()->load('user'),
        ]);
    }

    /**
     * حذف ملاحظة
     */
    public function destroy(ReviewComment $comment)
    {
        $user = auth()->user();

        // التحقق من أن المستخدم هو صاحب الملاحظة أو أدمن
        if ($comment->user_id !== $user->id && !$user->isPlatformAdmin()) {
            return response()->json(['error' => 'غير مصرح لك بحذف هذه الملاحظة'], 403);
        }

        // حذف الردود أيضاً
        $comment->replies()->delete();
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الملاحظة بنجاح',
        ]);
    }

    /**
     * الرد على ملاحظة
     */
    public function reply(Request $request, ReviewComment $parent)
    {
        $request->validate([
            'comment' => 'required|string|min:3|max:5000',
        ]);

        $user = auth()->user();
        $reviewable = $parent->reviewable;

        // التحقق من الصلاحيات
        if ($user->usesSupervisorAssignmentScope()) {
            // نفس التحقق من store
            if ($reviewable instanceof Lesson) {
                $canAccess = $reviewable->unit->section->subject->schoolClass
                    && $user->isAssignedToClassAsSupervisor($reviewable->unit->section->subject->schoolClass->id)
                    || $user->isAssignedToSubjectAsSupervisor($reviewable->unit->section->subject->id);
                if (!$canAccess) {
                    return response()->json(['error' => 'غير مصرح لك بالرد على هذه الملاحظة'], 403);
                }
            }
        } elseif ($user->usesTeacherAssignmentScope()) {
            // المعلم يمكنه الرد على أي ملاحظة متعلقة بعناصره
            if ($reviewable->created_by !== $user->id) {
                return response()->json(['error' => 'غير مصرح لك بالرد على هذه الملاحظة'], 403);
            }
        }

        // إنشاء الرد
        $reply = ReviewComment::create([
            'reviewable_type' => $parent->reviewable_type,
            'reviewable_id' => $parent->reviewable_id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'comment' => $request->input('comment'),
        ]);

        // إرسال إشعار للمستخدم الأصلي
        if ($parent->user_id !== $user->id) {
            // TODO: إرسال إشعار
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الرد بنجاح',
            'reply' => $reply->load('user'),
        ]);
    }

    /**
     * حل الملاحظة
     */
    public function resolve(ReviewComment $comment)
    {
        $user = auth()->user();

        // فقط صاحب الملاحظة أو المشرف/الأدمن يمكنهم حل الملاحظة
        if ($comment->user_id !== $user->id && !$user->canReviewContent()) {
            return response()->json(['error' => 'غير مصرح لك بحل هذه الملاحظة'], 403);
        }

        $comment->resolve();

        return response()->json([
            'success' => true,
            'message' => 'تم حل الملاحظة بنجاح',
            'comment' => $comment->fresh()->load('user'),
        ]);
    }

    /**
     * إلغاء حل الملاحظة
     */
    public function unresolve(ReviewComment $comment)
    {
        $user = auth()->user();

        // فقط صاحب الملاحظة أو المشرف/الأدمن يمكنهم إلغاء حل الملاحظة
        if ($comment->user_id !== $user->id && !$user->canReviewContent()) {
            return response()->json(['error' => 'غير مصرح لك بإلغاء حل هذه الملاحظة'], 403);
        }

        $comment->unresolve();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء حل الملاحظة بنجاح',
            'comment' => $comment->fresh()->load('user'),
        ]);
    }
}
