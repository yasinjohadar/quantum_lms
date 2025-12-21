<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    /**
     * عرض قائمة التحديات
     */
    public function index()
    {
        $challenges = Challenge::orderBy('start_date', 'desc')->get();
        return view('admin.pages.challenges.index', compact('challenges'));
    }

    /**
     * عرض نموذج إنشاء تحدٍ
     */
    public function create()
    {
        return view('admin.pages.challenges.create');
    }

    /**
     * حفظ تحدٍ جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:weekly,monthly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'criteria' => 'nullable|array',
            'rewards' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        Challenge::create($request->all());

        return redirect()->route('admin.challenges.index')
            ->with('success', 'تم إنشاء التحدي بنجاح');
    }

    /**
     * عرض نموذج تعديل تحدٍ
     */
    public function edit(Challenge $challenge)
    {
        return view('admin.pages.challenges.edit', compact('challenge'));
    }

    /**
     * تحديث تحدٍ
     */
    public function update(Request $request, Challenge $challenge)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:weekly,monthly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'criteria' => 'nullable|array',
            'rewards' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $challenge->update($request->all());

        return redirect()->route('admin.challenges.index')
            ->with('success', 'تم تحديث التحدي بنجاح');
    }

    /**
     * حذف تحدٍ
     */
    public function destroy(Challenge $challenge)
    {
        $challenge->delete();

        return redirect()->route('admin.challenges.index')
            ->with('success', 'تم حذف التحدي بنجاح');
    }
}

