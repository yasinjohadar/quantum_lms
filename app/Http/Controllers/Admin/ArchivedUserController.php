<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArchiveUserRequest;
use App\Http\Requests\Admin\BulkArchiveRequest;
use App\Http\Requests\Admin\BulkRestoreRequest;
use App\Models\ArchivedUser;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Services\UserArchiveService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ArchivedUserController extends Controller
{
    public function __construct(
        private UserArchiveService $archiveService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of archived users
     */
    public function index(Request $request): View
    {
        $query = ArchivedUser::with(['archivedByUser', 'restoredByUser', 'originalUser'])
            ->notRestored();

        // Search filter
        if ($request->filled('query')) {
            $search = $request->input('query');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
                  ->orWhere('student_id', 'like', "%$search%");
            });
        }

        // Active status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        // Date from filter
        if ($request->filled('archived_from')) {
            $query->whereDate('archived_at', '>=', $request->input('archived_from'));
        }

        // Date to filter
        if ($request->filled('archived_to')) {
            $query->whereDate('archived_at', '<=', $request->input('archived_to'));
        }

        // Subject filter (through enrollments)
        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $query->whereHas('originalUser.enrollments', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        // Class filter (through enrollments -> subject -> class)
        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $query->whereHas('originalUser.enrollments.subject', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $archivedUsers = $query->orderBy('archived_at', 'desc')->paginate(15)->withQueryString();

        $subjects = Subject::active()->with('schoolClass')->get();
        $classes = SchoolClass::active()->with('stage')->get();

        return view('admin.pages.archived-users.index', compact('archivedUsers', 'subjects', 'classes'));
    }

    /**
     * Store (archive) a user
     */
    public function store(ArchiveUserRequest $request, User $user): RedirectResponse
    {
        try {
            if ($user->is_archived) {
                return redirect()->back()
                    ->with('error', 'المستخدم مُؤرشف بالفعل');
            }

            $this->archiveService->archive($user, $request->input('reason'), auth()->user());

            return redirect()->route('admin.archived-users.index')
                ->with('success', 'تم أرشفة المستخدم بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'فشل أرشفة المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * Bulk archive users
     */
    public function bulkArchive(BulkArchiveRequest $request): RedirectResponse
    {
        try {
            $userIds = $request->input('user_ids');
            
            // Log للتصحيح
            Log::info('Bulk archive request', [
                'user_ids' => $userIds,
                'count' => count($userIds),
                'reason' => $request->input('reason'),
            ]);
            
            $result = $this->archiveService->bulkArchive(
                $userIds,
                $request->input('reason'),
                auth()->user()
            );

            $archivedCount = count($result['archived']);
            $errorCount = count($result['errors']);

            $message = "تم أرشفة {$archivedCount} مستخدم بنجاح";
            if ($errorCount > 0) {
                $errorDetails = collect($result['errors'])->pluck('error')->unique()->implode(', ');
                $message .= " (فشل {$errorCount} مستخدم: {$errorDetails})";
            }

            if ($archivedCount > 0) {
                return redirect()->route('admin.archived-users.index')
                    ->with('success', $message);
            } else {
                return redirect()->back()
                    ->with('error', $message);
            }
        } catch (\Exception $e) {
            Log::error('Bulk archive exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                ->with('error', 'فشل أرشفة المستخدمين: ' . $e->getMessage());
        }
    }

    /**
     * Restore an archived user
     */
    public function restore(ArchivedUser $archivedUser): RedirectResponse
    {
        try {
            if ($archivedUser->restored_at) {
                return redirect()->back()
                    ->with('error', 'المستخدم مُستعاد بالفعل');
            }

            $this->archiveService->restore($archivedUser, auth()->user());

            return redirect()->route('admin.archived-users.index')
                ->with('success', 'تم استعادة المستخدم بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'فشل استعادة المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * Bulk restore archived users
     */
    public function bulkRestore(BulkRestoreRequest $request): RedirectResponse
    {
        try {
            $archivedUserIds = $request->input('archived_user_ids');
            
            // Log للتصحيح
            Log::info('Bulk restore request', [
                'archived_user_ids' => $archivedUserIds,
                'count' => count($archivedUserIds),
            ]);
            
            $result = $this->archiveService->bulkRestore(
                $archivedUserIds,
                auth()->user()
            );

            $restoredCount = count($result['restored']);
            $errorCount = count($result['errors']);

            $message = "تم استعادة {$restoredCount} مستخدم بنجاح";
            if ($errorCount > 0) {
                $errorDetails = collect($result['errors'])->pluck('error')->unique()->implode(', ');
                $message .= " (فشل {$errorCount} مستخدم: {$errorDetails})";
            }

            if ($restoredCount > 0) {
                return redirect()->route('admin.archived-users.index')
                    ->with('success', $message);
            } else {
                return redirect()->back()
                    ->with('error', $message);
            }
        } catch (\Exception $e) {
            Log::error('Bulk restore exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                ->with('error', 'فشل استعادة المستخدمين: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified archived user
     */
    public function show(ArchivedUser $archivedUser): View
    {
        $archivedUser->load(['archivedByUser', 'restoredByUser', 'originalUser']);

        return view('admin.pages.archived-users.show', compact('archivedUser'));
    }

    /**
     * Remove the specified archived user (permanent delete)
     */
    public function destroy(ArchivedUser $archivedUser): RedirectResponse
    {
        try {
            $archivedUser->delete();

            return redirect()->route('admin.archived-users.index')
                ->with('success', 'تم حذف السجل المؤرشف بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'فشل حذف السجل: ' . $e->getMessage());
        }
    }
}