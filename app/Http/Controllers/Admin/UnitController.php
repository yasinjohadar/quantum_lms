<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUnitRequest;
use App\Http\Requests\Admin\UpdateUnitRequest;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    /**
     * تخزين وحدة جديدة تابعة لقسم معيّن.
     */
    public function store(StoreUnitRequest $request, SubjectSection $section)
    {
        Log::info('محاولة إنشاء وحدة جديدة للقسم: ' . $section->id, $request->all());

        try {
            $data = $request->validated();
            $data['section_id'] = $section->id;
            $data['is_active'] = $request->has('is_active');

            // لو لم يُرسل ترتيب نضعه في آخر القائمة
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = $section->units()->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }

            $unit = Unit::create($data);

            Log::info('تم إنشاء الوحدة بنجاح، ID: ' . $unit->id);

            return redirect()
                ->route('admin.subjects.show', $section->subject_id)
                ->with('success', 'تم إنشاء الوحدة "' . $unit->title . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء وحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $section->subject_id)
                ->with('error', 'حدث خطأ أثناء إنشاء الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * تحديث وحدة موجودة.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $unit->update($data);

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('success', 'تم تحديث بيانات الوحدة بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث وحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('error', 'حدث خطأ أثناء تحديث الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * حذف وحدة.
     */
    public function destroy(Unit $unit)
    {
        $subjectId = $unit->section->subject_id;
        $unitTitle = $unit->title;

        try {
            $unit->delete();

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم حذف الوحدة "' . $unitTitle . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في حذف وحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('error', 'حدث خطأ أثناء حذف الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * عرض أسئلة الوحدة
     */
    public function questions(Unit $unit)
    {
        $questions = $unit->questions()
            ->with('options')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pages.units.questions', compact('unit', 'questions'));
    }

    /**
     * ربط أسئلة بالوحدة
     */
    public function attachQuestions(Request $request, Unit $unit)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        try {
            // ربط الأسئلة بالوحدة (مع تجنب التكرار)
            $unit->questions()->syncWithoutDetaching($request->question_ids);

            $count = count($request->question_ids);

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('success', "تم ربط {$count} سؤال بالوحدة بنجاح.");
        } catch (\Exception $e) {
            Log::error('خطأ في ربط الأسئلة بالوحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('error', 'حدث خطأ أثناء ربط الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * فك ربط سؤال من الوحدة
     */
    public function detachQuestion(Unit $unit, Question $question)
    {
        try {
            $unit->questions()->detach($question->id);

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('success', 'تم فك ربط السؤال من الوحدة بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في فك ربط السؤال: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('error', 'حدث خطأ أثناء فك ربط السؤال: ' . $e->getMessage());
        }
    }

    /**
     * الأسئلة المتاحة للربط بالوحدة (للاستخدام في AJAX)
     */
    public function availableQuestions(Request $request, Unit $unit)
    {
        $query = Question::active()
            ->whereDoesntHave('units', function ($q) use ($unit) {
                $q->where('units.id', $unit->id);
            });

        // البحث
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // فلترة حسب النوع
        if ($request->has('type') && $request->type) {
            $query->ofType($request->type);
        }

        // فلترة حسب الصعوبة
        if ($request->has('difficulty') && $request->difficulty) {
            $query->ofDifficulty($request->difficulty);
        }

        $questions = $query->with('options')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'questions' => $questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'type' => $q->type,
                    'type_name' => $q->type_name,
                    'type_icon' => $q->type_icon,
                    'type_color' => $q->type_color,
                    'difficulty' => $q->difficulty,
                    'difficulty_name' => $q->difficulty_name,
                    'difficulty_color' => $q->difficulty_color,
                    'default_points' => $q->default_points,
                    'options_count' => $q->options->count(),
                ];
            }),
        ]);
    }
}

