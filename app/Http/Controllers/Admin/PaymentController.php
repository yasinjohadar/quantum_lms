<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('auth');
        $this->middleware('admin');
        $this->middleware(['permission:payment-list'])->only('index');
        $this->middleware(['permission:payment-show'])->only('show');
        $this->middleware(['permission:payment-review'])->only('reviewPayment');
        $this->middleware(['permission:payment-approve'])->only('approvePayment');
        $this->middleware(['permission:payment-reject'])->only('rejectPayment');
        $this->middleware(['permission:payment-download-receipt'])->only('downloadReceipt');
    }

    /**
     * قائمة المدفوعات
     */
    public function index(Request $request)
    {
        $query = Payment::with(['purchase.user', 'purchase.purchasable', 'customPaymentMethod', 'reviewedBy']);

        // فلترة حسب الحالة
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // فلترة حسب طريقة الدفع
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // فلترة حسب المدفوعات التي تحتاج مراجعة
        if ($request->has('needs_review') && $request->needs_review) {
            $query->needsReview();
        }

        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('purchase.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.pages.payments.index', compact('payments'));
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
            'notes' => 'required|string|max:1000',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'لا يمكن رفض هذا الدفع'], 400);
            }
            return back()->with('error', 'لا يمكن رفض هذا الدفع');
        }

        $success = $this->paymentService->reviewIBANPayment(
            $payment,
            false,
            $request->notes,
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
}
