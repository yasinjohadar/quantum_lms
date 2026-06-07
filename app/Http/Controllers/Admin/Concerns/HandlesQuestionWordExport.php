<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Http\Requests\ExportQuestionsWordRequest;
use App\Models\Subject;
use App\Services\Exports\QuestionWordExportException;
use App\Services\Exports\QuestionWordExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait HandlesQuestionWordExport
{
    protected function downloadQuestionsWord(ExportQuestionsWordRequest $request, ?Subject $subject = null): BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        if ($subject !== null) {
            $this->authorizeManagedSubjectAccess($request->user(), $subject);
        }

        $query = $this->buildQuestionIndexQuery($request, $subject);
        $service = app(QuestionWordExportService::class);

        try {
            $path = $service->exportFromQuery(
                $query,
                $request->validated('scope'),
                $request->input('ids', []),
                $request->validated('order'),
                $service->buildDocumentMeta($subject, 0)
            );
        } catch (QuestionWordExportException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return response()
            ->download($path, $service->downloadFilename($subject))
            ->deleteFileAfterSend(true);
    }
}
