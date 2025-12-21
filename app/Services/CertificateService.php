<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    /**
     * إنشاء شهادة
     */
    public function generateCertificate(
        User $user,
        string $type,
        ?Subject $subject = null,
        ?CertificateTemplate $template = null
    ): Certificate {
        // جلب القالب الافتراضي إذا لم يتم تحديد واحد
        if (!$template) {
            $template = CertificateTemplate::default()
                ->ofType($type)
                ->first();

            if (!$template) {
                $template = CertificateTemplate::active()
                    ->ofType($type)
                    ->first();
            }
        }

        // إنشاء رقم الشهادة
        $certificateNumber = $this->generateCertificateNumber($user, $type);

        // إنشاء الشهادة
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'subject_id' => $subject?->id,
            'type' => $type,
            'certificate_number' => $certificateNumber,
            'issued_at' => now(),
            'template_id' => $template?->id,
            'metadata' => [
                'user_name' => $user->name,
                'subject_name' => $subject?->name,
                'issued_date' => now()->format('Y-m-d'),
            ],
        ]);

        // توليد PDF
        $pdfPath = $this->generatePDF($certificate, $template);
        $certificate->pdf_path = $pdfPath;
        $certificate->save();

        return $certificate;
    }

    /**
     * توليد رقم الشهادة
     */
    private function generateCertificateNumber(User $user, string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $year = now()->format('Y');
        $userId = str_pad($user->id, 6, '0', STR_PAD_LEFT);
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$userId}-{$random}";
    }

    /**
     * توليد PDF
     */
    private function generatePDF(Certificate $certificate, ?CertificateTemplate $template): string
    {
        // تحديد القالب المناسب
        $templateView = $this->getTemplateView($certificate, $template);
        
        // إعداد البيانات
        $data = [
            'certificate' => $certificate->load(['user', 'subject']),
        ];

        // إذا كان هناك قالب مخصص، أضفه للبيانات
        if ($template && $template->template_html) {
            $data['template'] = $template;
        }
        
        // توليد HTML من Blade template
        $html = view($templateView, $data)->render();

        // توليد PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        // حفظ الملف
        $filename = "certificates/{$certificate->certificate_number}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * الحصول على قالب العرض المناسب
     */
    private function getTemplateView(Certificate $certificate, ?CertificateTemplate $template): string
    {
        if ($template && $template->template_html) {
            // استخدام قالب مخصص من قاعدة البيانات
            return 'certificates.templates.custom';
        }

        // استخدام قالب حسب النوع
        $typeMap = [
            'course_completion' => 'certificates.templates.course-completion',
            'excellence' => 'certificates.templates.excellence',
            'attendance' => 'certificates.templates.attendance',
        ];

        return $typeMap[$certificate->type] ?? 'certificates.templates.default';
    }

    /**
     * معاينة الشهادة مع قالب مخصص
     */
    public function previewCertificate(Certificate $certificate, ?CertificateTemplate $template = null)
    {
        $templateView = $this->getTemplateView($certificate, $template);
        
        $data = [
            'certificate' => $certificate->load(['user', 'subject']),
        ];

        // إذا كان هناك قالب مخصص، أضفه للبيانات
        if ($template && $template->template_html) {
            $data['template'] = $template;
        }
        
        return view($templateView, $data);
    }

    /**
     * إعادة توليد الشهادة
     */
    public function regenerateCertificate(Certificate $certificate, ?CertificateTemplate $template = null): Certificate
    {
        // حذف PDF القديم إن وجد
        if ($certificate->pdf_path) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }

        // تحديث القالب إذا تم تحديد واحد
        if ($template) {
            $certificate->template_id = $template->id;
            $certificate->save();
        }

        // استخدام القالب المحدد أو القالب الحالي
        $templateToUse = $template ?? ($certificate->template_id ? $certificate->template : null);

        // توليد PDF جديد
        $pdfPath = $this->generatePDF($certificate, $templateToUse);
        $certificate->pdf_path = $pdfPath;
        $certificate->save();

        return $certificate;
    }

    /**
     * تحميل الشهادة
     */
    public function downloadCertificate(Certificate $certificate)
    {
        if (!$certificate->pdf_path) {
            throw new \Exception('ملف الشهادة غير موجود');
        }

        $path = Storage::disk('public')->path($certificate->pdf_path);
        
        if (!file_exists($path)) {
            throw new \Exception('ملف الشهادة غير موجود');
        }

        return response()->download($path, "certificate-{$certificate->certificate_number}.pdf");
    }

    /**
     * التحقق من الشهادة
     */
    public function verifyCertificate(string $certificateNumber): ?Certificate
    {
        return Certificate::where('certificate_number', $certificateNumber)->first();
    }
}

