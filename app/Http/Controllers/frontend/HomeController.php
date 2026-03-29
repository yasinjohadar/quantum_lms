<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Currency;
use App\Models\ClassEnrollment;
use App\Models\User;
use App\Models\Purchase;
use App\Models\HeroSlide;
use App\Models\DistinguishedStudent;
use App\Models\SystemSetting;
use App\Models\CustomPaymentMethod;
use App\Services\PurchaseService;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $purchaseService;
    protected $paymentService;
    protected $walletService;

    public function __construct(
        PurchaseService $purchaseService,
        PaymentService $paymentService,
        WalletService $walletService
    ) {
        $this->purchaseService = $purchaseService;
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
    }
    /**
     * عرض الصفحة الرئيسية
     */
    public function index(): View
    {
        // جلب العملة الافتراضية
        $defaultCurrency = Currency::getDefault();
        
        // جلب جميع الصفوف النشطة مع العلاقات للواجهة الرئيسية (السلايدر)
        $classes = SchoolClass::with(['stage', 'subjects', 'defaultCurrency', 'features'])
            ->active()
            ->ordered()
            ->get()
            ->map(function ($class) use ($defaultCurrency) {
                try {
                    // الحصول على السعر باستخدام getPrice() التي تستخدم fallback
                    $price = $class->getPrice($defaultCurrency->id ?? null);
                    $currency = $class->defaultCurrency ?? $defaultCurrency;

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
                        'features' => $class->features->pluck('label')->values(),
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
                        'features' => $class->features->pluck('label')->values(),
                        'created_at' => $class->created_at,
                        'updated_at' => $class->updated_at,
                    ];
                }
            });

        // شرائح Hero للصفحة الرئيسية (سلايدر Hero)
        $heroSlides = HeroSlide::active()->ordered()->get();

        // الطلاب المتميزون (سلايدر الصفحة الرئيسية)
        $limit = (int) SystemSetting::get('distinguished_students_display_limit', 12);
        $distinguishedStudents = DistinguishedStudent::active()
            ->ordered()
            ->with(['user', 'schoolClass'])
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->get();

        return view('frontend.pages.index', compact('classes', 'heroSlides', 'distinguishedStudents'));
    }

    /**
     * بحث الصفوف والمواد (صفحة النتائج)
     */
    public function search(Request $request): View
    {
        $query = trim((string) $request->get('q', ''));

        $classes = collect();
        $subjects = collect();

        if ($query !== '') {
            $like = '%' . $query . '%';
            $classes = SchoolClass::query()
                ->active()
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                })
                ->ordered()
                ->limit(20)
                ->get(['id', 'name', 'slug', 'description', 'image']);

            $subjects = Subject::query()
                ->with('schoolClass:id,name,slug')
                ->whereHas('schoolClass', fn ($c) => $c->active())
                ->active()
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                })
                ->ordered()
                ->limit(30)
                ->get(['id', 'name', 'slug', 'class_id']);
        }

        return view('frontend.pages.search', compact('query', 'classes', 'subjects'));
    }

    /**
     * عرض صفحة الصف مع مواده
     */
    public function showClass($slug): View
    {
        // جلب الصف بالـ slug
        $class = SchoolClass::with(['stage', 'defaultCurrency', 'features'])
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
                    ];
                }
            });

        // التحقق من حالة المستخدم مع الصف (إذا كان مسجل دخول)
        $isEnrolled = false;
        $purchaseStatus = null;
        $enrollmentStatus = null;
        $pendingPurchase = null;
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // التحقق من وجود ClassEnrollment approved
            $classEnrollment = ClassEnrollment::where('user_id', $user->id)
                ->where('class_id', $class->id)
                ->where('status', 'approved')
                ->first();
            
            if ($classEnrollment) {
                $isEnrolled = true;
                $enrollmentStatus = 'approved';
            } else {
                // التحقق من وجود Purchase للصف
                $completedPurchase = Purchase::where('user_id', $user->id)
                    ->where('purchasable_type', SchoolClass::class)
                    ->where('purchasable_id', $class->id)
                    ->where('status', 'completed')
                    ->first();
                
                if ($completedPurchase) {
                    $isEnrolled = true;
                    $purchaseStatus = 'completed';
                } else {
                    // التحقق من وجود Purchase pending
                    $pendingPurchase = Purchase::with(['purchasable.defaultCurrency'])
                        ->where('user_id', $user->id)
                        ->where('purchasable_type', SchoolClass::class)
                        ->where('purchasable_id', $class->id)
                        ->where('status', 'pending')
                        ->first();
                    
                    if ($pendingPurchase) {
                        $purchaseStatus = 'pending';
                    }
                }
            }
        }

        return view('frontend.pages.class-show', compact('class', 'subjects', 'isEnrolled', 'purchaseStatus', 'enrollmentStatus', 'pendingPurchase'));
    }

    /**
     * عرض صفحة checkout
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'purchase_type' => 'required|in:class,subjects',
            'class_id' => 'required|exists:classes,id',
            'subject_ids' => 'required_if:purchase_type,subjects|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $class = SchoolClass::with(['stage', 'defaultCurrency', 'subjects'])
            ->where('id', $request->class_id)
            ->active()
            ->firstOrFail();

        $defaultCurrency = Currency::getDefault();
        $items = [];
        $totalPrice = 0;
        $allFree = true;

        if ($request->purchase_type === 'class') {
            // شراء الصف بالكامل
            $price = $class->getPrice($defaultCurrency->id ?? null);
            $currency = $class->defaultCurrency ?? $defaultCurrency;
            $isFree = $class->is_free || $price == 0;
            
            // التحقق من عدم الشراء مسبقاً (completed)
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', SchoolClass::class)
                ->where('purchasable_id', $class->id)
                ->where('status', 'completed')
                ->first();
            
            if ($existingPurchase) {
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('info', 'لقد قمت بشراء هذا الصف مسبقاً');
            }
            
            // التحقق من وجود Purchase pending
            $pendingPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', SchoolClass::class)
                ->where('purchasable_id', $class->id)
                ->where('status', 'pending')
                ->first();
            
            if ($pendingPurchase) {
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('warning', 'لديك طلب شراء قيد المراجعة من قبل الإدارة');
            }
            
            $items[] = [
                'type' => 'class',
                'purchasable' => $class,
                'name' => $class->name,
                'price' => $price,
                'currency' => $currency,
                'is_free' => $isFree,
            ];
            
            $totalPrice = $price;
            $allFree = $isFree;
        } else {
            // شراء مواد متفرقة
            $subjectIds = $request->subject_ids ?? [];
            
            if (empty($subjectIds)) {
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('error', 'يرجى اختيار مادة واحدة على الأقل');
            }
            
            $subjects = Subject::with(['defaultCurrency', 'schoolClass'])
                ->whereIn('id', $subjectIds)
                ->where('class_id', $class->id)
                ->active()
                ->get();
            
            foreach ($subjects as $subject) {
                // التحقق من عدم الشراء مسبقاً (completed)
                $existingPurchase = Purchase::where('user_id', $user->id)
                    ->where('purchasable_type', Subject::class)
                    ->where('purchasable_id', $subject->id)
                    ->where('status', 'completed')
                    ->first();
                
                if ($existingPurchase) {
                    continue; // تخطي المواد المشتراة مسبقاً
                }
                
                // التحقق من شراء الصف كاملاً (completed)
                $classPurchase = Purchase::where('user_id', $user->id)
                    ->where('purchasable_type', SchoolClass::class)
                    ->where('purchasable_id', $class->id)
                    ->where('status', 'completed')
                    ->first();
                
                if ($classPurchase) {
                    continue; // تخطي إذا كان الصف مشترى
                }
                
                // التحقق من وجود Purchase pending للمادة
                $subjectPendingPurchase = Purchase::where('user_id', $user->id)
                    ->where('purchasable_type', Subject::class)
                    ->where('purchasable_id', $subject->id)
                    ->where('status', 'pending')
                    ->first();
                
                if ($subjectPendingPurchase) {
                    continue; // تخطي المواد التي لديها طلب pending
                }
                
                $price = $subject->getPrice($defaultCurrency->id ?? null);
                $currency = $subject->defaultCurrency ?? $defaultCurrency;
                $isFree = $subject->is_free || $price == 0;
                
                $items[] = [
                    'type' => 'subject',
                    'purchasable' => $subject,
                    'name' => $subject->name,
                    'price' => $price,
                    'currency' => $currency,
                    'is_free' => $isFree,
                ];
                
                $totalPrice += $price;
                if (!$isFree) {
                    $allFree = false;
                }
            }
            
            if (empty($items)) {
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('info', 'جميع المواد المختارة مشتراة مسبقاً أو متاحة من خلال شراء الصف');
            }
        }

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        return view('frontend.pages.checkout', compact('class', 'items', 'totalPrice', 'allFree', 'defaultCurrency', 'wallet', 'customPaymentMethods'));
    }

    /**
     * معالجة الشراء
     */
    public function processCheckout(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'purchase_type' => 'required|in:class,subjects',
            'class_id' => 'required|exists:classes,id',
            'subject_ids' => 'required_if:purchase_type,subjects|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $class = SchoolClass::where('id', $request->class_id)
            ->active()
            ->firstOrFail();

        $defaultCurrency = Currency::getDefault();
        $purchasables = [];

        try {
            DB::beginTransaction();

            if ($request->purchase_type === 'class') {
                // شراء الصف
                $purchasables[] = [
                    'purchasable' => $class,
                    'type' => 'class',
                ];
            } else {
                // شراء مواد متفرقة
                $subjectIds = $request->subject_ids ?? [];
                $subjects = Subject::whereIn('id', $subjectIds)
                    ->where('class_id', $class->id)
                    ->active()
                    ->get();
                
                foreach ($subjects as $subject) {
                    // التحقق من عدم الشراء مسبقاً
                    $existingPurchase = Purchase::where('user_id', $user->id)
                        ->where('purchasable_type', Subject::class)
                        ->where('purchasable_id', $subject->id)
                        ->where('status', 'completed')
                        ->first();
                    
                    if ($existingPurchase) {
                        continue;
                    }
                    
                    // التحقق من شراء الصف
                    $classPurchase = Purchase::where('user_id', $user->id)
                        ->where('purchasable_type', SchoolClass::class)
                        ->where('purchasable_id', $class->id)
                        ->where('status', 'completed')
                        ->first();
                    
                    if ($classPurchase) {
                        continue;
                    }
                    
                    $purchasables[] = [
                        'purchasable' => $subject,
                        'type' => 'subject',
                    ];
                }
            }

            if (empty($purchasables)) {
                DB::rollBack();
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('error', 'لا توجد عناصر متاحة للشراء');
            }

            // إنشاء المشتريات
            $purchases = $this->purchaseService->createMultiplePurchases($user, $purchasables, $defaultCurrency->id);

            if ($purchases->isEmpty()) {
                DB::rollBack();
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('error', 'فشل إنشاء طلب الشراء');
            }

            // التحقق من أن كل المشتريات مجانية
            $allFree = $purchases->every(function ($purchase) {
                return $purchase->status === 'completed';
            });

            DB::commit();

            if ($allFree) {
                // كل شيء مجاني، التفعيل التلقائي تم
                $purchaseType = $request->purchase_type === 'class' ? 'الصف' : 'المواد';
                $message = $request->purchase_type === 'class' 
                    ? 'تم التسجيل في الصف بنجاح' 
                    : 'تم التسجيل في المواد المختارة بنجاح';
                
                return redirect()->route('frontend.class.show', $class->slug)
                    ->with('success', $message);
            } else {
                // هناك مشتريات مدفوعة، توجيه إلى صفحة الدفع في Frontend
                // نستخدم أول purchase غير مكتمل
                $pendingPurchase = $purchases->firstWhere('status', 'pending');
                
                if ($pendingPurchase) {
                    return redirect()->route('frontend.payment', $pendingPurchase->id);
                } else {
                    return redirect()->route('frontend.class.show', $class->slug)
                        ->with('success', 'تم التسجيل بنجاح');
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('frontend.class.show', $class->slug)
                ->with('error', 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الدفع
     */
    public function showPayment($purchaseId): View
    {
        $user = Auth::user();
        
        $purchase = Purchase::where('user_id', $user->id)
            ->with(['purchasable'])
            ->findOrFail($purchaseId);

        if ($purchase->status === 'completed') {
            return redirect()->route('frontend.class.show', $purchase->purchasable->slug ?? '')
                ->with('info', 'تم إكمال هذا الشراء مسبقاً');
        }

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        // الحصول على الصف للعودة إليه
        $class = null;
        if ($purchase->purchasable_type === SchoolClass::class) {
            $class = $purchase->purchasable;
        } elseif ($purchase->purchasable_type === Subject::class) {
            $class = $purchase->purchasable->schoolClass ?? null;
        }

        return view('frontend.pages.payment', compact('purchase', 'wallet', 'customPaymentMethods', 'class'));
    }

    /**
     * معالجة الدفع
     */
    public function processPayment(Request $request, $purchaseId)
    {
        $user = Auth::user();
        $purchase = Purchase::where('user_id', $user->id)->findOrFail($purchaseId);

        if ($purchase->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'تم إكمال هذا الشراء مسبقاً',
            ], 400);
        }

        // Validation rules
        $rules = [
            'payment_method' => 'required|in:stripe,paypal,wallet,iban,custom',
            'payment_data' => 'nullable|array',
            'currency_id' => 'nullable|exists:currencies,id',
        ];
        
        // إضافة validation للـ custom payment method
        if ($request->payment_method === 'custom') {
            $rules['custom_payment_method_id'] = 'required|integer|exists:custom_payment_methods,id';
        }
        
        // إضافة validation للـ receipt_file للـ IBAN
        if ($request->payment_method === 'iban') {
            $rules['receipt_file'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }
        
        // إضافة validation للـ receipt_file للـ custom methods (إذا كان مطلوباً)
        if ($request->payment_method === 'custom' && $request->custom_payment_method_id) {
            $customMethod = CustomPaymentMethod::find($request->custom_payment_method_id);
            if ($customMethod && $customMethod->requires_receipt) {
                $rules['receipt_file'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            }
        }
        
        $validated = $request->validate($rules, [
            'payment_method.required' => 'يرجى اختيار طريقة الدفع',
            'payment_method.in' => 'طريقة الدفع المختارة غير صحيحة',
            'custom_payment_method_id.required' => 'يرجى اختيار طريقة الدفع المخصصة',
            'custom_payment_method_id.exists' => 'طريقة الدفع المخصصة غير موجودة',
            'receipt_file.required' => 'يرجى رفع وصل الدفع',
            'receipt_file.file' => 'الملف المرفوع غير صحيح',
            'receipt_file.mimes' => 'نوع الملف غير مدعوم. يرجى رفع ملف JPG, PNG, أو PDF',
            'receipt_file.max' => 'حجم الملف كبير جداً. الحد الأقصى 5MB',
        ]);

        $paymentMethod = $request->payment_method;
        $result = null;

        try {
            switch ($paymentMethod) {
                case 'stripe':
                    $result = $this->paymentService->processStripePayment($purchase, $request->token ?? '');
                    break;

                case 'paypal':
                    $result = $this->paymentService->processPayPalPayment($purchase, $request->all());
                    break;

                case 'wallet':
                    $result = $this->paymentService->processWalletPayment($purchase);
                    break;

                case 'iban':
                    $result = $this->paymentService->processIBANPayment($purchase, $request->hasFile('receipt_file') ? $request->file('receipt_file') : null);
                    break;

                case 'custom':
                    $customData = $request->all();
                    if ($request->hasFile('receipt_file')) {
                        $customData['receipt_file'] = $request->file('receipt_file');
                    }
                    $result = $this->paymentService->processCustomPayment(
                        $purchase,
                        $request->custom_payment_method_id,
                        $customData
                    );
                    break;
            }

            if (!$result || !$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'فشلت عملية الدفع',
                ], 400);
            }

            // الحصول على العملة
            $currencyCode = 'SAR';
            if ($request->currency_id) {
                $currency = Currency::find($request->currency_id);
                if ($currency) {
                    $currencyCode = $currency->code;
                }
            }

            // معالجة الشراء
            $data = [
                'transaction_id' => $result['transaction_id'] ?? null,
                'gateway_response' => $result['gateway_response'] ?? null,
                'receipt_file' => $result['receipt_file'] ?? null,
                'custom_payment_method_id' => $request->custom_payment_method_id ?? null,
                'payment_data' => $request->payment_data ?? null,
                'currency' => $currencyCode,
                'currency_id' => $request->currency_id ?? null,
            ];

            $this->purchaseService->processPurchase($purchase, $paymentMethod, $data);

            $message = 'تم إرسال طلب الدفع بنجاح';
            if ($paymentMethod === 'iban' || $paymentMethod === 'custom') {
                $message = 'تم إرسال طلب الدفع بنجاح. سيتم مراجعته من قبل الإدارة';
            }

            // تحديد صفحة التوجيه
            $redirectUrl = route('frontend.class.show', $purchase->purchasable->slug ?? '');
            if ($purchase->purchasable_type === Subject::class && $purchase->purchasable->schoolClass) {
                $redirectUrl = route('frontend.class.show', $purchase->purchasable->schoolClass->slug ?? '');
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectUrl,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // معالجة أخطاء Validation
            $errors = $e->errors();
            $firstError = collect($errors)->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $firstError ?? 'بيانات غير صحيحة',
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            // Logging للأخطاء
            \Illuminate\Support\Facades\Log::error('Payment processing error', [
                'purchase_id' => $purchaseId,
                'user_id' => $user->id,
                'payment_method' => $request->payment_method ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // رسالة خطأ واضحة للمستخدم
            $errorMessage = 'حدث خطأ أثناء معالجة الدفع';
            if (config('app.debug')) {
                $errorMessage .= ': ' . $e->getMessage();
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
