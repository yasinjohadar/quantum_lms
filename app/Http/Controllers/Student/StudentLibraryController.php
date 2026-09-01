<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Services\LibraryService;
use Illuminate\Http\Request;

class StudentLibraryController extends Controller
{
    public function __construct(
        private LibraryService $libraryService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['category_id', 'type', 'subject_id', 'search']);
        $items = $this->libraryService->getStudentItems($user, $filters);

        $categories = LibraryCategory::active()->ordered()->get();
        $subjects = $user->subjects()->active()->get();

        return view('student.pages.library.index', compact('items', 'categories', 'subjects', 'filters'));
    }

    public function show(Request $request, LibraryItem $item)
    {
        if (! $this->libraryService->canUserAccess($item, $request->user())) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا العنصر');
        }

        $item->load(['category', 'subject', 'schoolClass', 'uploader']);

        return view('student.pages.library.show', compact('item'));
    }

    public function download(Request $request, LibraryItem $item)
    {
        if (! $this->libraryService->canUserDownload($item, $request->user())) {
            abort(403, 'غير مصرح لك بتحميل هذا العنصر');
        }

        if ($item->external_url) {
            return redirect()->away($item->external_url);
        }

        if (! $item->file_path) {
            abort(404, 'لا يوجد ملف لهذا العنصر');
        }

        return $this->libraryService->downloadResponse($item);
    }
}
