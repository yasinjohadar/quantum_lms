<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssignmentQuestionController extends Controller
{
    /**
     * حفظ سؤال جديد
     */
    public function store(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:single_choice,multiple_choice,true_false,short_answer',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'correct_answer' => 'required',
            'points' => 'required|numeric|min:0',
            'order' => 'nullable|integer',
        ]);

        try {
            // تحويل correct_answer إلى JSON
            if (is_array($validated['correct_answer'])) {
                $validated['correct_answer'] = json_encode($validated['correct_answer']);
            }

            $validated['assignment_id'] = $assignment->id;
            $validated['order'] = $validated['order'] ?? ($assignment->questions()->max('order') + 1);

            $question = AssignmentQuestion::create($validated);

            return response()->json([
                'success' => true,
                'question' => $question,
                'message' => 'تم إضافة السؤال بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating assignment question: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'request' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تحديث سؤال
     */
    public function update(Request $request, Assignment $assignment, AssignmentQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:single_choice,multiple_choice,true_false,short_answer',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'correct_answer' => 'required',
            'points' => 'required|numeric|min:0',
            'order' => 'nullable|integer',
        ]);

        try {
            // تحويل correct_answer إلى JSON
            if (is_array($validated['correct_answer'])) {
                $validated['correct_answer'] = json_encode($validated['correct_answer']);
            }

            $question->update($validated);

            return response()->json([
                'success' => true,
                'question' => $question->fresh(),
                'message' => 'تم تحديث السؤال بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating assignment question: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'request' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف سؤال
     */
    public function destroy(Assignment $assignment, AssignmentQuestion $question)
    {
        try {
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف السؤال بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting assignment question: ' . $e->getMessage(), [
                'question_id' => $question->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إعادة ترتيب الأسئلة
     */
    public function reorder(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:assignment_questions,id',
            'questions.*.order' => 'required|integer',
        ]);

        try {
            foreach ($validated['questions'] as $item) {
                AssignmentQuestion::where('id', $item['id'])
                    ->where('assignment_id', $assignment->id)
                    ->update(['order' => $item['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة ترتيب الأسئلة بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering assignment questions: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة ترتيب الأسئلة: ' . $e->getMessage(),
            ], 500);
        }
    }
}
