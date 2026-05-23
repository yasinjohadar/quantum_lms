<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\CustomPaymentMethod;
use App\Models\SystemSetting;
use App\Services\Pricing\PricingResolver;
use App\Services\PurchaseService;
use App\Services\PaymentService;
use App\Services\WalletService;
use App\Services\Storage\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class PurchaseController extends Controller
{
    protected $purchaseService;
    protected $paymentService;
    protected $walletService;

    public function __construct(
        PurchaseService $purchaseService,
        PaymentService $paymentService,
        WalletService $walletService,
        protected PricingResolver $pricingResolver,
    ) {
        $this->purchaseService = $purchaseService;
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
        $this->middleware('auth');
        $this->middleware('check.user.active');
    }

    /**
     * عرض صفحة شراء الصف
     */
    public function showClass($classId)
    {
        $user = Auth::user();
        $class = SchoolClass::where('is_active', true)->findOrFail($classId);

        // التحقق من وجود شراء مسبق
        $existingPurchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', SchoolClass::class)
            ->where('purchasable_id', $class->id)
            ->where('status', 'completed')
            ->first();

        if ($existingPurchase) {
            return redirect()->route('student.enrollments.class.show', $class->id)
                ->with('info', 'لقد قمت بشراء هذا الصف مسبقاً');
        }

        // التحقق من الوصول (إذا كان مجانياً)
        if ($class->is_free || $class->price == 0) {
            // إنشاء شراء مجاني تلقائياً
            $purchase = $this->purchaseService->createPurchase($user, $class, 'class');
            return redirect()->route('student.enrollments.class.show', $class->id)
                ->with('success', 'تم التسجيل في الصف بنجاح');
        }

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        return view('student.pages.purchases.class-show', compact('class', 'wallet', 'customPaymentMethods'));
    }

    /**
     * عرض صفحة شراء المادة
     */
    public function showSubject($subjectId)
    {
        $user = Auth::user();
        $subject = Subject::where('is_active', true)->findOrFail($subjectId);

        $access = $this->pricingResolver->resolveSubjectAccessData($subject, $user, null);

        if ($access->canAccess) {
            return redirect()->route('student.subjects.show', $subject->id)
                ->with('info', 'لديك وصول لهذه المادة');
        }

        if (! $access->canPurchaseSeparately) {
            $class = $subject->schoolClass;
            if ($class && ! $class->is_free) {
                return redirect()->route('student.purchases.class.show', $class->id)
                    ->with('info', 'هذه المادة متاحة فقط من خلال شراء الصف');
            }
        }

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        return view('student.pages.purchases.subject-show', compact('subject', 'wallet', 'customPaymentMethods'));
    }

    /**
     * بدء عملية الشراء
     */
    public function initiatePurchase(Request $request)
    {
        $request->validate([
            'purchasable_type' => 'required|in:class,subject',
            'purchasable_id' => 'required|integer',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        $user = Auth::user();
        $type = $request->purchasable_type;
        $id = $request->purchasable_id;
        $currencyId = $request->currency_id;

        if ($type === 'class') {
            $purchasable = SchoolClass::where('is_active', true)->findOrFail($id);
        } else {
            $purchasable = Subject::where('is_active', true)->findOrFail($id);
        }

        // التحقق من وجود شراء مسبق
        $existingPurchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', get_class($purchasable))
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 'completed')
            ->first();

        if ($existingPurchase) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بشراء هذا العنصر مسبقاً',
            ], 400);
        }

        // إنشاء شراء
        $purchase = $this->purchaseService->createPurchase($user, $purchasable, $type, $currencyId);

        // إذا كان مجانياً، تم إكماله تلقائياً
        if ($purchase->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'تم الشراء بنجاح',
                'purchase_id' => $purchase->id,
                'redirect' => $type === 'class' 
                    ? route('student.enrollments.class.show', $id)
                    : route('student.subjects.show', $id),
            ]);
        }

        return response()->json([
            'success' => true,
            'purchase_id' => $purchase->id,
            'redirect' => route('student.purchases.payment', $purchase->id),
        ]);
    }

    /**
     * عرض صفحة الدفع
     */
    public function showPayment($purchaseId)
    {
        $user = Auth::user();
        
        // إذا كان 'new'، إنشاء purchase جديد
        if ($purchaseId === 'new') {
            $request = request();
            $type = $request->get('type');
            $id = $request->get('id');
            
            if (!$type || !$id) {
                return redirect()->route('student.enrollments.index')
                    ->with('error', 'بيانات غير صحيحة');
            }
            
            if ($type === 'class') {
                $purchasable = SchoolClass::where('is_active', true)->findOrFail($id);
            } else {
                $purchasable = Subject::where('is_active', true)->findOrFail($id);
            }
            
            // التحقق من وجود شراء مسبق
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', get_class($purchasable))
                ->where('purchasable_id', $purchasable->id)
                ->where('status', 'completed')
                ->first();
            
            if ($existingPurchase) {
                return redirect()->route('student.classes')
                    ->with('info', 'لقد قمت بشراء هذا العنصر مسبقاً');
            }
            
            // إنشاء purchase جديد
            $currencyId = $request->get('currency_id');
            $purchase = $this->purchaseService->createPurchase($user, $purchasable, $type, $currencyId);
            
            // إذا كان مجانياً، تم إكماله تلقائياً
            if ($purchase->status === 'completed') {
                return redirect($type === 'class' 
                    ? route('student.enrollments.class.show', $id)
                    : route('student.subjects.show', $id))
                    ->with('success', 'تم التسجيل بنجاح');
            }
        } else {
            $purchase = Purchase::where('user_id', $user->id)->findOrFail($purchaseId);
        }

        if ($purchase->status === 'completed') {
            return redirect()->route('student.classes')
                ->with('info', 'تم إكمال هذا الشراء مسبقاً');
        }

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        return view('student.pages.purchases.payment', compact('purchase', 'wallet', 'customPaymentMethods'));
    }

    /**
     * تحضير شراء الصف وعرض جزء HTML لنموذج الدفع (عند فتح نافذة الدفع من الانضمام).
     */
    public function prepareClassPaymentFragment(Request $request, SchoolClass $class)
    {
        $user = Auth::user();
        $class = SchoolClass::with(['subjects' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->findOrFail($class->id);

        if ($class->subjects->isEmpty()) {
            abort(404);
        }

        if (! $class->classJoinRequiresPayment($request->filled('currency_id') ? (int) $request->currency_id : null)) {
            abort(403);
        }

        $completed = Purchase::query()
            ->where('user_id', $user->id)
            ->where('purchasable_type', SchoolClass::class)
            ->where('purchasable_id', $class->id)
            ->where('status', 'completed')
            ->exists();

        if ($completed) {
            abort(403);
        }

        $currencyId = $request->filled('currency_id') ? (int) $request->currency_id : null;
        $purchase = $this->purchaseService->resolveOrCreatePendingPurchase($user, $class, 'class', $currencyId);

        return $this->renderPaymentFragment($request, $purchase);
    }

    /**
     * تحضير شراء المادة وعرض جزء HTML لنموذج الدفع.
     */
    public function prepareSubjectPaymentFragment(Request $request, Subject $subject)
    {
        $user = Auth::user();
        $subject = Subject::with('schoolClass')
            ->where('is_active', true)
            ->findOrFail($subject->id);

        $currencyId = $request->filled('currency_id') ? (int) $request->currency_id : null;
        $access = $this->pricingResolver->resolveSubjectAccessData($subject, $user, $currencyId);

        if ($access->isEffectivelyFree || $access->effectivePrice <= 0) {
            abort(403);
        }

        $completed = Purchase::query()
            ->where('user_id', $user->id)
            ->where('purchasable_type', Subject::class)
            ->where('purchasable_id', $subject->id)
            ->where('status', 'completed')
            ->exists();

        if ($completed) {
            abort(403);
        }

        $purchase = $this->purchaseService->resolveOrCreatePendingPurchase($user, $subject, 'subject', $currencyId);

        return $this->renderPaymentFragment($request, $purchase);
    }

    /**
     * جزء HTML لنموذج الدفع (مثلاً داخل نافذة طلب الانضمام).
     */
    public function paymentFragment(Request $request, Purchase $purchase)
    {
        $user = Auth::user();
        $purchase = Purchase::query()
            ->where('user_id', $user->id)
            ->findOrFail($purchase->id);

        if ($purchase->status !== 'pending') {
            abort(403);
        }

        return $this->renderPaymentFragment($request, $purchase);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    private function renderPaymentFragment(Request $request, Purchase $purchase)
    {
        $user = Auth::user();
        $purchase->load(['purchasable']);

        $return = $request->query('return', 'classes');
        $allowed = ['classes', 'enrollments', 'class'];
        if (! in_array($return, $allowed, true)) {
            $return = 'classes';
        }

        $classId = (int) $request->query('class_id', 0);
        if ($classId === 0 && $purchase->purchase_type === 'class' && $purchase->purchasable) {
            $classId = (int) $purchase->purchasable_id;
        }

        $afterSuccessUrl = match ($return) {
            'enrollments' => route('student.enrollments.index'),
            'class' => $classId > 0 ? route('student.enrollments.class.show', $classId) : route('student.enrollments.index'),
            default => route('student.classes'),
        };

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        return response()
            ->view('student.pages.purchases.payment-fragment', compact(
                'purchase',
                'wallet',
                'customPaymentMethods',
                'afterSuccessUrl'
            ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
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

        $ibanReceiptRules = SystemSetting::ibanReceiptRequired()
            ? 'required_if:payment_method,iban|file|mimes:jpg,jpeg,png,pdf|max:5120'
            : 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';

        $request->validate([
            'payment_method' => 'required|in:iban',
            'receipt_file' => $ibanReceiptRules,
            'payment_data' => 'nullable|array',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        $paymentMethod = $request->payment_method;
        $result = null;

        try {
            $result = $this->paymentService->processIBANPayment(
                $purchase,
                $request->hasFile('receipt_file') ? $request->file('receipt_file') : null
            );

            if (!$result || !$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'فشلت عملية الدفع',
                ], 400);
            }

            // الحصول على العملة
            $currencyCode = 'SAR';
            if ($request->currency_id) {
                $currency = \App\Models\Currency::find($request->currency_id);
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

            $redirect = $request->input('redirect_url', route('student.classes'));
            if (! is_string($redirect) || $redirect === '' || ! str_starts_with($redirect, url('/'))) {
                $redirect = route('student.classes');
            }

            return response()->json([
                'success' => true,
                'pending_review' => true,
                'message' => SystemSetting::ibanPendingMessage(),
                'redirect' => $redirect,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الدفع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * رفع الوصل
     */
    public function uploadReceipt(Request $request, $paymentId)
    {
        $user = Auth::user();
        $payment = Payment::whereHas('purchase', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($paymentId);

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن رفع الوصل لهذا الدفع',
            ], 400);
        }

        $request->validate([
            'receipt_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $uploadResult = MediaStorageService::uploadDocument($request->file('receipt_file'), 'receipts');
            $path = $uploadResult['path'];
            $payment->update(['receipt_file' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'تم رفع الوصل بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفع الوصل',
            ], 500);
        }
    }

    /**
     * إلغاء شراء قيد المراجعة (من قبل الطالب)
     */
    public function cancelPending(Purchase $purchase)
    {
        $user = Auth::user();

        try {
            $this->purchaseService->cancelPendingByStudent($purchase, $user);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الطلب نهائياً',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'غير مصرح',
            ], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * مشترياتي - إعادة توجيه إلى صفوفي (المشتريات قيد المراجعة تظهر ضمن صفوفي)
     */
    public function myPurchases()
    {
        return redirect()->route('student.classes');
    }
}
