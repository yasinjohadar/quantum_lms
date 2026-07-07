<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Purchase;
use App\Services\PaymentService;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $purchaseService;

    public function __construct(PaymentService $paymentService, PurchaseService $purchaseService)
    {
        $this->paymentService = $paymentService;
        $this->purchaseService = $purchaseService;
        $this->middleware('auth');
        $this->middleware('admin');
        $this->middleware(['permission:payment-list'])->only('index');
        $this->middleware(['permission:payment-show'])->only('show');
        $this->middleware(['permission:payment-review'])->only('reviewPayment');
        $this->middleware(['permission:payment-approve'])->only(['approvePayment', 'approvePendingPurchase']);
        $this->middleware(['permission:payment-reject'])->only(['rejectPayment', 'rejectPendingPurchase']);
        $this->middleware(['permission:payment-download-receipt'])->only('downloadReceipt');
    }

    /**
     * قائمة المدفوعات
     */
    public function index(Request $request)
    {
        $query = Payment::with(['purchase.user', 'purchase.purchasable', 'customPaymentMethod', 'reviewedBy']);
        $pendingPurchaseRequests = Purchase::query()
            ->with(['user', 'purchasable'])
            ->pendingDirectApproval();

        // فلترة حسب الحالة
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // فلترة حسب طريقة الدفع
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // فلترة حسب المدفوعات التي تحتاج مراجعة
        if ($request->boolean('needs_review')) {
            $query->needsReview();
        }

        // نوع الشراء (صف / مادة)
        if ($request->filled('purchase_type')) {
            $purchaseType = $request->input('purchase_type');
            if (in_array($purchaseType, ['class', 'subject'], true)) {
                $query->whereHas('purchase', function ($q) use ($purchaseType) {
                    $q->where('purchase_type', $purchaseType);
                });
                $pendingPurchaseRequests->where('purchase_type', $purchaseType);
            }
        }

        if ($request->filled('purchase_status')) {
            $purchaseStatus = $request->input('purchase_status');
            if (in_array($purchaseStatus, ['pending', 'completed', 'cancelled', 'refunded'], true)) {
                $query->whereHas('purchase', function ($q) use ($purchaseStatus) {
                    $q->where('status', $purchaseStatus);
                });
                $pendingPurchaseRequests->where('status', $purchaseStatus);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
            $pendingPurchaseRequests->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
            $pendingPurchaseRequests->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('purchase.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
            $pendingPurchaseRequests->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $pendingPurchaseRequests = $pendingPurchaseRequests
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.pages.payments.index', compact('payments', 'pendingPurchaseRequests'));
    }

    /**
     * تفاصيل الدفع
     */
    public function show($id)
    {
        $payment = Payment::with([
            'purchase.user',
            'purchase.purchasable',
            'customPaymentMethod',
            'reviewedBy'
        ])->findOrFail($id);

        return view('admin.pages.payments.show', compact('payment'));
    }

    /**
     * مراجعة دفع IBAN/مخصص
     */
    public function reviewPayment(Request $request, $id)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::findOrFail($id);

        if (!in_array($payment->status, ['pending'])) {
            return back()->with('error', 'لا يمكن مراجعة هذا الدفع');
        }

        $payment->loadMissing('purchase');

        if ($request->boolean('approved') && $payment->purchase && $payment->purchase->status === 'cancelled') {
            return back()->with('error', 'لا يمكن الموافقة؛ الشراء ملغى نهائياً');
        }

        $success = $this->paymentService->reviewIBANPayment(
            $payment,
            $request->approved,
            $request->notes,
            auth()->id()
        );

        if ($success) {
            $message = $request->approved ? 'تم الموافقة على الدفع بنجاح' : 'تم رفض الدفع';
            return back()->with('success', $message);
        }

        return back()->with('error', 'حدث خطأ أثناء مراجعة الدفع');
    }

    /**
     * الموافقة على الدفع
     */
    public function approvePayment($id)
    {
        $payment = Payment::findOrFail($id);

        $payment->loadMissing('purchase');

        if ($payment->purchase && $payment->purchase->status === 'cancelled') {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'لا يمكن الموافقة؛ الشراء ملغى نهائياً'], 400);
            }

            return back()->with('error', 'لا يمكن الموافقة؛ الشراء ملغى نهائياً');
        }

        if ($payment->status !== 'pending') {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'لا يمكن الموافقة على هذا الدفع'], 400);
            }
            return back()->with('error', 'لا يمكن الموافقة على هذا الدفع');
        }

        try {
            $success = $this->paymentService->reviewIBANPayment(
                $payment,
                true,
                null,
                auth()->id()
            );

            if ($success) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => true, 'message' => 'تم الموافقة على الدفع بنجاح']);
                }
                return back()->with('success', 'تم الموافقة على الدفع بنجاح');
            }

            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الموافقة على الدفع. يرجى التحقق من السجلات'], 500);
            }
            return back()->with('error', 'حدث خطأ أثناء الموافقة على الدفع. يرجى التحقق من السجلات');
        } catch (\Exception $e) {
            Log::error('Payment approval error in controller', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الموافقة على الدفع: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'حدث خطأ أثناء الموافقة على الدفع: ' . $e->getMessage());
        }
    }

    /**
     * رفض الدفع
     */
    public function rejectPayment(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::findOrFail($id);

        $payment->loadMissing('purchase');

        if ($payment->purchase && $payment->purchase->status === 'cancelled') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'لا يمكن رفض دفع مرتبط بشراء ملغى'], 400);
            }

            return back()->with('error', 'لا يمكن رفض دفع مرتبط بشراء ملغى');
        }

        if ($payment->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'لا يمكن رفض هذا الدفع'], 400);
            }
            return back()->with('error', 'لا يمكن رفض هذا الدفع');
        }

        $notes = $request->filled('notes') ? trim((string) $request->input('notes')) : null;
        if ($notes === '') {
            $notes = null;
        }

        $success = $this->paymentService->reviewIBANPayment(
            $payment,
            false,
            $notes,
            auth()->id()
        );

        if ($success) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'تم رفض الدفع']);
            }
            return back()->with('success', 'تم رفض الدفع');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء رفض الدفع'], 500);
        }
        return back()->with('error', 'حدث خطأ أثناء رفض الدفع');
    }

    /**
     * تحميل الوصل
     */
    public function downloadReceipt($id)
    {
        $payment = Payment::findOrFail($id);

        if (!$payment->receipt_file) {
            abort(404, 'الوصل غير موجود');
        }

        if (!Storage::disk('public')->exists($payment->receipt_file)) {
            abort(404, 'ملف الوصل غير موجود');
        }

        return Storage::disk('public')->download($payment->receipt_file);
    }

    /**
     * اعتماد طلب شراء/انضمام معلّق مباشرة بدون دفعة.
     */
    public function approvePendingPurchase(Request $request, Purchase $purchase)
    {
        $defaults = \App\Support\PurchaseApprovalExpiryDefaults::resolve($purchase);

        $expiresRules = ['required', 'date', 'after_or_equal:today'];
        if ($defaults['max_expires_at']) {
            $expiresRules[] = 'before_or_equal:'.$defaults['max_expires_at']->format('Y-m-d');
        }

        $validated = $request->validate([
            'expires_at' => $expiresRules,
            'notes' => 'nullable|string|max:1000',
        ], [
            'expires_at.required' => 'يجب تحديد تاريخ انتهاء الاشتراك',
            'expires_at.date' => 'تاريخ انتهاء الاشتراك غير صالح',
            'expires_at.after_or_equal' => 'يجب أن يكون تاريخ الانتهاء اليوم أو بعده',
            'expires_at.before_or_equal' => 'لا يمكن أن يتجاوز تاريخ الانتهاء نهاية اشتراك الصف ('.$defaults['class_subscription_ends_at']?->format('Y-m-d').')',
        ]);

        try {
            $expiresAt = \Carbon\Carbon::parse($validated['expires_at'])->endOfDay();

            $this->purchaseService->approvePendingDirectPurchase(
                $purchase,
                (int) auth()->id(),
                $request->filled('notes') ? trim((string) $request->input('notes')) : null,
                $expiresAt
            );

            $purchase->refresh();

            return back()->with('success', 'تم اعتماد طلب الانضمام المدفوع بنجاح حتى '.$purchase->expires_at->format('Y-m-d'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Pending purchase direct approval failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'حدث خطأ أثناء اعتماد الطلب: ' . $e->getMessage());
        }
    }

    /**
     * رفض طلب شراء/انضمام معلّق مباشرة بدون دفعة.
     */
    public function rejectPendingPurchase(Request $request, Purchase $purchase)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->purchaseService->rejectPendingDirectPurchase(
                $purchase,
                (int) auth()->id(),
                $request->filled('notes') ? trim((string) $request->input('notes')) : null
            );

            return back()->with('success', 'تم رفض طلب الانضمام المدفوع');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Pending purchase direct rejection failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'حدث خطأ أثناء رفض الطلب');
        }
    }
}
