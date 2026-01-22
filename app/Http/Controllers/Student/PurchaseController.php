<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\CustomPaymentMethod;
use App\Services\PurchaseService;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
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

        // التحقق من وجود شراء مسبق
        $existingPurchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', Subject::class)
            ->where('purchasable_id', $subject->id)
            ->where('status', 'completed')
            ->first();

        if ($existingPurchase) {
            return redirect()->route('student.subjects.show', $subject->id)
                ->with('info', 'لقد قمت بشراء هذه المادة مسبقاً');
        }

        // التحقق من شراء الصف كاملاً
        $class = $subject->schoolClass;
        if ($class) {
            $classPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', SchoolClass::class)
                ->where('purchasable_id', $class->id)
                ->where('status', 'completed')
                ->first();

            if ($classPurchase) {
                return redirect()->route('student.subjects.show', $subject->id)
                    ->with('info', 'أنت مسجل في هذه المادة من خلال شراء الصف كاملاً');
            }
        }

        // التحقق من الوصول (إذا كان مجانياً)
        if ($subject->is_free || $subject->price == 0) {
            // إنشاء شراء مجاني تلقائياً
            $purchase = $this->purchaseService->createPurchase($user, $subject, 'subject');
            return redirect()->route('student.subjects.show', $subject->id)
                ->with('success', 'تم التسجيل في المادة بنجاح');
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
                return redirect()->route('student.purchases.my-purchases')
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
            return redirect()->route('student.purchases.my-purchases')
                ->with('info', 'تم إكمال هذا الشراء مسبقاً');
        }

        $wallet = $this->walletService->getOrCreateWallet($user);
        $customPaymentMethods = CustomPaymentMethod::active()->ordered()->get();

        return view('student.pages.purchases.payment', compact('purchase', 'wallet', 'customPaymentMethods'));
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

        $request->validate([
            'payment_method' => 'required|in:stripe,paypal,wallet,iban,custom',
            'custom_payment_method_id' => 'required_if:payment_method,custom|integer|exists:custom_payment_methods,id',
            'receipt_file' => 'required_if:payment_method,iban|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'payment_data' => 'nullable|array',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        $paymentMethod = $request->payment_method;
        $result = null;

        try {
            switch ($paymentMethod) {
                case 'stripe':
                    $result = $this->paymentService->processStripePayment($purchase, $request->token);
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

            $message = 'تم إرسال طلب الدفع بنجاح';
            if ($paymentMethod === 'iban' || $paymentMethod === 'custom') {
                $message = 'تم إرسال طلب الدفع بنجاح. سيتم مراجعته من قبل الإدارة';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('student.purchases.my-purchases'),
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
            $path = $request->file('receipt_file')->store('receipts', 'public');
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
     * مشترياتي
     */
    public function myPurchases()
    {
        $user = Auth::user();
        $purchases = Purchase::where('user_id', $user->id)
            ->with(['purchasable', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('student.pages.purchases.index', compact('purchases'));
    }
}
