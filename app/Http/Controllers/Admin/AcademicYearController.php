<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:user-edit']);
    }

    public function index(Request $request)
    {
        $years = AcademicYear::query()
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('admin.pages.academic-years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.pages.academic-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only('name', 'start_date', 'end_date');
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        AcademicYear::create($data);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تم إنشاء السنة الدراسية بنجاح');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.pages.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only('name', 'start_date', 'end_date');
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            AcademicYear::query()->where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update($data);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تم تحديث السنة الدراسية بنجاح');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->academicWeeks()->exists()) {
            return redirect()->back()->with('error', 'لا يمكن الحذف: توجد أسابيع مرتبطة بهذه السنة. احذف الأسابيع أولاً.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تم حذف السنة الدراسية');
    }

    /**
     * تفعيل سنة دراسية (واحدة فقط نشطة).
     */
    public function activate(AcademicYear $academicYear)
    {
        AcademicYear::query()->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        return redirect()->back()->with('success', 'تم تفعيل السنة الدراسية: ' . $academicYear->name);
    }
}
