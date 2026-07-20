<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QuestionMathBackfillService;
use Illuminate\Http\Request;

/**
 * أداة إصلاح شامل لعرض LaTeX في بنك الأسئلة الحالي بالكامل — تُشغَّل مرة واحدة
 * (أو كل ما استُدعيت) من صفحة استيراد الأسئلة عبر أزرار AJAX تعالج دفعات صغيرة
 * متتالية لتفادي انتهاء مهلة الخادم على بنك أسئلة كبير.
 */
class QuestionMathBackfillController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:question-import']);
    }

    public function status(QuestionMathBackfillService $service)
    {
        return response()->json($service->totals());
    }

    public function processBatch(Request $request, QuestionMathBackfillService $service)
    {
        $data = $request->validate([
            'entity' => ['required', 'in:questions,options'],
            'after_id' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:10', 'max:1000'],
        ]);

        $afterId = (int) ($data['after_id'] ?? 0);
        $limit = (int) ($data['limit'] ?? 200);

        $result = $data['entity'] === 'options'
            ? $service->processOptionsBatch($afterId, $limit)
            : $service->processQuestionsBatch($afterId, $limit);

        return response()->json($result);
    }
}
