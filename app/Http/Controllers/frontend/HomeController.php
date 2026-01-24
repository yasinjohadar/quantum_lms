<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Currency;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * عرض الصفحة الرئيسية
     */
    public function index(): View
    {
        // جلب العملة الافتراضية
        $defaultCurrency = Currency::getDefault();
        
        // جلب الصفوف النشطة مع العلاقات
        $classes = SchoolClass::with(['stage', 'subjects', 'defaultCurrency'])
            ->active()
            ->ordered()
            ->get()
            ->map(function ($class) use ($defaultCurrency) {
                try {
                    // الحصول على السعر باستخدام getPrice() التي تستخدم fallback
                    $price = $class->getPrice($defaultCurrency->id ?? null);
                    $currency = $class->defaultCurrency ?? $defaultCurrency;
                    
                    // جلب عدد الطلاب المسجلين في الصف
                    try {
                        $enrolledStudentsCount = ClassEnrollment::where('class_id', $class->id)
                            ->where('status', 'approved')
                            ->count();
                    } catch (\Exception $e) {
                        $enrolledStudentsCount = 0;
                    }
                    
                    // جلب صور الطلاب (أول 5 طلاب)
                    try {
                        $enrolledStudents = ClassEnrollment::where('class_id', $class->id)
                            ->where('status', 'approved')
                            ->with('user')
                            ->limit(5)
                            ->get()
                            ->map(function ($enrollment) {
                                if (!$enrollment->user) {
                                    return null;
                                }
                                return [
                                    'id' => $enrollment->user->id ?? null,
                                    'name' => $enrollment->user->name ?? '',
                                    'avatar' => $enrollment->user->avatar ?? null,
                                ];
                            })
                            ->filter(function ($student) {
                                return $student !== null;
                            })
                            ->values();
                    } catch (\Exception $e) {
                        $enrolledStudents = collect([]);
                    }
                    
                    // حساب السعر القديم (يمكن أن يكون 20% أكثر من السعر الحالي)
                    $oldPrice = $price > 0 ? $price * 1.2 : 0;
                    
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'slug' => $class->slug,
                        'image' => $class->image,
                        'description' => $class->description,
                        'stage' => $class->stage,
                        'subjects_count' => $class->subjects()->count(),
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'currency' => $currency,
                        'is_free' => $class->is_free ?? ($price == 0),
                        'enrolled_students_count' => $enrolledStudentsCount,
                        'enrolled_students' => $enrolledStudents,
                        'created_at' => $class->created_at,
                        'updated_at' => $class->updated_at,
                    ];
                } catch (\Exception $e) {
                    // في حالة حدوث خطأ، إرجاع بيانات افتراضية
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'slug' => $class->slug,
                        'image' => $class->image,
                        'description' => $class->description,
                        'stage' => $class->stage,
                        'subjects_count' => $class->subjects()->count(),
                        'price' => $class->price ?? 0,
                        'old_price' => ($class->price ?? 0) * 1.2,
                        'currency' => $defaultCurrency,
                        'is_free' => $class->is_free ?? true,
                        'enrolled_students_count' => 0,
                        'enrolled_students' => collect([]),
                        'created_at' => $class->created_at,
                        'updated_at' => $class->updated_at,
                    ];
                }
            });

        return view('frontend.pages.index', compact('classes'));
    }

    /**
     * عرض صفحة الصف مع مواده
     */
    public function showClass($slug): View
    {
        // جلب الصف بالـ slug
        $class = SchoolClass::with(['stage', 'defaultCurrency'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // جلب العملة الافتراضية
        $defaultCurrency = Currency::getDefault();

        // جلب المواد النشطة للصف
        $subjects = Subject::with(['defaultCurrency', 'schoolClass'])
            ->where('class_id', $class->id)
            ->active()
            ->ordered()
            ->get()
            ->map(function ($subject) use ($defaultCurrency) {
                try {
                    // الحصول على السعر
                    $price = $subject->getPrice($defaultCurrency->id ?? null);
                    $currency = $subject->defaultCurrency ?? $defaultCurrency;

                    // جلب عدد الطلاب المسجلين في المادة
                    $enrolledStudentsCount = Enrollment::where('subject_id', $subject->id)
                        ->where('status', 'active')
                        ->count();

                    // جلب صور الطلاب (أول 5 طلاب)
                    $enrolledStudents = Enrollment::where('subject_id', $subject->id)
                        ->where('status', 'active')
                        ->with('user')
                        ->limit(5)
                        ->get()
                        ->map(function ($enrollment) {
                            return [
                                'id' => $enrollment->user->id ?? null,
                                'name' => $enrollment->user->name ?? '',
                                'avatar' => $enrollment->user->avatar ?? null,
                            ];
                        });

                    // حساب السعر القديم
                    $oldPrice = $price > 0 ? $price * 1.2 : 0;

                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'slug' => $subject->slug,
                        'image' => $subject->image,
                        'description' => $subject->description,
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'currency' => $currency,
                        'is_free' => $subject->is_free ?? ($price == 0),
                        'enrolled_students_count' => $enrolledStudentsCount,
                        'enrolled_students' => $enrolledStudents,
                    ];
                } catch (\Exception $e) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'slug' => $subject->slug,
                        'image' => $subject->image,
                        'description' => $subject->description,
                        'price' => $subject->price ?? 0,
                        'old_price' => ($subject->price ?? 0) * 1.2,
                        'currency' => $defaultCurrency,
                        'is_free' => $subject->is_free ?? true,
                        'enrolled_students_count' => 0,
                        'enrolled_students' => collect([]),
                    ];
                }
            });

        return view('frontend.pages.class-show', compact('class', 'subjects'));
    }
}
