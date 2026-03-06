<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AcademicWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcademicWeekController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:user-edit']);
    }

    public function index(Request $request)
    {
        $query = AcademicWeek::query()->with('academicYear')->orderBy('start_date');

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $weeks = $query->paginate(20);
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.pages.academic-weeks.index', compact('weeks', 'academicYears'));
    }

    public function create(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $selectedYearId = $request->get('academic_year_id');

        return view('admin.pages.academic-weeks.create', compact('academicYears', 'selectedYearId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'week_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'required_lessons_target' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $year = AcademicYear::findOrFail($request->academic_year_id);
        $exists = AcademicWeek::where('academic_year_id', $year->id)
            ->where('week_number', $request->week_number)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'رقم الأسبوع موجود مسبقاً في هذه السنة');
        }

        AcademicWeek::create([
            'academic_year_id' => $year->id,
            'week_number' => (int) $request->week_number,
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'required_lessons_target' => (int) ($request->required_lessons_target ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.academic-weeks.index', ['academic_year_id' => $year->id])
            ->with('success', 'تم إنشاء الأسبوع بنجاح');
    }

    public function edit(AcademicWeek $academicWeek)
    {
        $academicWeek->load('academicYear');
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.pages.academic-weeks.edit', compact('academicWeek', 'academicYears'));
    }

    public function update(Request $request, AcademicWeek $academicWeek)
    {
        $request->validate([
            'week_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'required_lessons_target' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $exists = AcademicWeek::where('academic_year_id', $academicWeek->academic_year_id)
            ->where('week_number', $request->week_number)
            ->where('id', '!=', $academicWeek->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'رقم الأسبوع موجود مسبقاً في هذه السنة');
        }

        $academicWeek->update([
            'week_number' => (int) $request->week_number,
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'required_lessons_target' => (int) ($request->required_lessons_target ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.academic-weeks.index', ['academic_year_id' => $academicWeek->academic_year_id])
            ->with('success', 'تم تحديث الأسبوع بنجاح');
    }

    public function destroy(AcademicWeek $academicWeek)
    {
        $yearId = $academicWeek->academic_year_id;
        $academicWeek->delete();

        return redirect()->route('admin.academic-weeks.index', ['academic_year_id' => $yearId])
            ->with('success', 'تم حذف الأسبوع');
    }

    /**
     * توليد أسابيع تلقائياً من تاريخ بداية السنة (7 أيام لكل أسبوع).
     */
    public function generate(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'weeks_count' => 'nullable|integer|min:1|max:52',
        ]);

        $count = (int) ($request->input('weeks_count') ?? 36);
        $start = $academicYear->start_date->copy();
        $endYear = $academicYear->end_date;

        $created = 0;
        for ($n = 1; $n <= $count; $n++) {
            $weekEnd = $start->copy()->addDays(6);
            if ($weekEnd->gt($endYear)) {
                break;
            }
            $exists = AcademicWeek::where('academic_year_id', $academicYear->id)
                ->where('week_number', $n)
                ->exists();
            if (!$exists) {
                AcademicWeek::create([
                    'academic_year_id' => $academicYear->id,
                    'week_number' => $n,
                    'title' => 'الأسبوع ' . $n,
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $weekEnd->format('Y-m-d'),
                    'required_lessons_target' => 0,
                    'is_active' => true,
                ]);
                $created++;
            }
            $start->addDays(7);
        }

        return redirect()->route('admin.academic-weeks.index', ['academic_year_id' => $academicYear->id])
            ->with('success', 'تم إنشاء ' . $created . ' أسبوعاً');
    }
}
