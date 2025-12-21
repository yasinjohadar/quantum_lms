<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificateService
    ) {}

    /**
     * عرض قائمة الشهادات
     */
    public function index()
    {
        $certificates = Certificate::with(['user', 'subject'])
            ->orderBy('issued_at', 'desc')
            ->paginate(20);

        return view('admin.pages.certificates.index', compact('certificates'));
    }

    /**
     * توليد شهادة
     */
    public function generate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|in:course_completion,excellence,attendance,achievement',
            'subject_id' => 'nullable|exists:subjects,id',
            'template_id' => 'nullable|exists:certificate_templates,id',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $subject = $request->subject_id ? \App\Models\Subject::find($request->subject_id) : null;
        $template = $request->template_id ? CertificateTemplate::find($request->template_id) : null;

        $certificate = $this->certificateService->generateCertificate(
            $user,
            $request->type,
            $subject,
            $template
        );

        return redirect()->route('admin.certificates.index')
            ->with('success', 'تم توليد الشهادة بنجاح');
    }

    /**
     * تحميل شهادة
     */
    public function download(Certificate $certificate)
    {
        return $this->certificateService->downloadCertificate($certificate);
    }

    /**
     * معاينة الشهادة
     */
    public function preview(Certificate $certificate, Request $request)
    {
        $template = $request->template_id ? CertificateTemplate::find($request->template_id) : null;
        
        return $this->certificateService->previewCertificate($certificate, $template);
    }

    /**
     * إعادة توليد الشهادة
     */
    public function regenerate(Certificate $certificate, Request $request)
    {
        $template = $request->template_id ? CertificateTemplate::find($request->template_id) : null;
        
        $this->certificateService->regenerateCertificate($certificate, $template);

        return redirect()->back()->with('success', 'تم إعادة توليد الشهادة بنجاح');
    }

    /**
     * التحقق من شهادة
     */
    public function verify(Request $request)
    {
        $request->validate([
            'certificate_number' => 'required|string',
        ]);

        $certificate = $this->certificateService->verifyCertificate($request->certificate_number);

        if (!$certificate) {
            return redirect()->back()->with('error', 'الشهادة غير موجودة');
        }

        return view('admin.pages.certificates.verify', compact('certificate'));
    }
}

