<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\CustomPaymentMethod;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * معالجة دفع Stripe
     */
    public function processStripePayment(Purchase $purchase, string $token): array
    {
        try {
            // TODO: تكامل Stripe API
            // $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            // $charge = $stripe->charges->create([...]);

            // مؤقتاً: محاكاة نجاح الدفع
            $transactionId = 'STRIPE-' . time() . '-' . $purchase->id;

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'gateway_response' => ['status' => 'succeeded'],
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة دفع PayPal
     */
    public function processPayPalPayment(Purchase $purchase, array $data): array
    {
        try {
            // TODO: تكامل PayPal API
            // $paypal = new \PayPal\Rest\ApiContext(...);
            // $payment = \PayPal\Api\Payment::get($data['payment_id'], $paypal);

            // مؤقتاً: محاكاة نجاح الدفع
            $transactionId = 'PAYPAL-' . time() . '-' . $purchase->id;

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'gateway_response' => ['status' => 'completed'],
            ];
        } catch (\Exception $e) {
            Log::error('PayPal payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة دفع من المحفظة
     */
    public function processWalletPayment(Purchase $purchase): array
    {
        try {
            DB::beginTransaction();

            $wallet = $this->walletService->getOrCreateWallet($purchase->user);

            if (!$wallet->hasBalance($purchase->price)) {
                return [
                    'success' => false,
                    'error' => 'الرصيد غير كافٍ',
                ];
            }

            // سحب من المحفظة
            $this->walletService->withdraw(
                $purchase->user,
                $purchase->price,
                "شراء {$purchase->purchase_type}: " . ($purchase->purchasable->name ?? '')
            );

            $transactionId = 'WALLET-' . time() . '-' . $purchase->id;

            DB::commit();

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'gateway_response' => ['status' => 'completed'],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة دفع IBAN
     */
    public function processIBANPayment(Purchase $purchase, $receiptFile): array
    {
        try {
            $path = null;
            if ($receiptFile) {
                // التحقق من أن الملف موجود وصحيح
                if (!$receiptFile->isValid()) {
                    return [
                        'success' => false,
                        'error' => 'الملف المرفوع غير صحيح أو تالف',
                    ];
                }
                
                // حفظ الملف
                $path = $receiptFile->store('receipts', 'public');
                
                if (!$path) {
                    return [
                        'success' => false,
                        'error' => 'فشل حفظ الملف. يرجى المحاولة مرة أخرى',
                    ];
                }
                
                Log::info('IBAN receipt uploaded', [
                    'purchase_id' => $purchase->id,
                    'file_path' => $path,
                    'file_size' => $receiptFile->getSize(),
                ]);
            } else {
                return [
                    'success' => false,
                    'error' => 'يرجى رفع وصل الدفع',
                ];
            }

            return [
                'success' => true,
                'receipt_file' => $path,
                'status' => 'pending', // يحتاج مراجعة
            ];
        } catch (\Exception $e) {
            Log::error('IBAN payment error', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => 'حدث خطأ أثناء معالجة الدفع: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة دفع مخصص
     */
    public function processCustomPayment(Purchase $purchase, int $methodId, array $data): array
    {
        try {
            $method = CustomPaymentMethod::findOrFail($methodId);

            $path = null;
            if (isset($data['receipt_file']) && $method->requires_receipt) {
                $path = $data['receipt_file']->store('receipts', 'public');
            }

            return [
                'success' => true,
                'custom_payment_method_id' => $methodId,
                'receipt_file' => $path,
                'payment_data' => $data['payment_data'] ?? null,
                'status' => 'pending', // يحتاج مراجعة
            ];
        } catch (\Exception $e) {
            Log::error('Custom payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * مراجعة دفع IBAN/مخصص
     */
    public function reviewIBANPayment(Payment $payment, bool $approved, ?string $notes = null, ?int $reviewedBy = null): bool
    {
        try {
            DB::beginTransaction();

            Log::info('Starting payment review', [
                'payment_id' => $payment->id,
                'approved' => $approved,
                'reviewed_by' => $reviewedBy ?? auth()->id(),
            ]);

            // تحديث الدفع أولاً
            $payment->refresh();
            $payment->load(['purchase.user', 'purchase.purchasable']);
            
            if (!$payment->purchase) {
                Log::error('Purchase not found for payment', ['payment_id' => $payment->id]);
                throw new \Exception('الشراء المرتبط بهذا الدفع غير موجود');
            }

            Log::info('Payment purchase loaded', [
                'payment_id' => $payment->id,
                'purchase_id' => $payment->purchase->id,
                'purchase_type' => $payment->purchase->purchase_type,
                'purchasable_type' => $payment->purchase->purchasable_type,
                'purchasable_id' => $payment->purchase->purchasable_id,
            ]);

            $payment->update([
                'status' => $approved ? 'completed' : 'failed',
                'reviewed_by' => $reviewedBy ?? auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            Log::info('Payment status updated', [
                'payment_id' => $payment->id,
                'new_status' => $approved ? 'completed' : 'failed',
            ]);

            if ($approved) {
                // تحديث حالة الشراء إلى completed
                $purchase = $payment->purchase;
                $purchase->refresh();
                $purchase->load(['user', 'purchasable']);
                
                Log::info('Checking purchasable', [
                    'purchase_id' => $purchase->id,
                    'purchasable_type' => $purchase->purchasable_type,
                    'purchasable_id' => $purchase->purchasable_id,
                    'purchasable_exists' => $purchase->purchasable ? 'yes' : 'no',
                ]);
                
                if (!$purchase->purchasable) {
                    Log::error('Purchasable not found', [
                        'purchase_id' => $purchase->id,
                        'purchasable_type' => $purchase->purchasable_type,
                        'purchasable_id' => $purchase->purchasable_id,
                    ]);
                    throw new \Exception('العنصر المرتبط بهذا الشراء غير موجود');
                }
                
                $purchase->update([
                    'status' => 'completed',
                    'purchased_at' => now(),
                ]);

                Log::info('Purchase status updated to completed', [
                    'purchase_id' => $purchase->id,
                ]);
                
                // إكمال الشراء وإنشاء التسجيلات
                Log::info('Starting completePurchase', [
                    'purchase_id' => $purchase->id,
                    'purchase_type' => $purchase->purchase_type,
                ]);
                
                $purchaseService = app(PurchaseService::class);
                $purchaseService->completePurchase($purchase);
                
                Log::info('completePurchase finished successfully', [
                    'purchase_id' => $purchase->id,
                ]);
            }

            DB::commit();
            Log::info('Payment review completed successfully', [
                'payment_id' => $payment->id,
                'approved' => $approved,
            ]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review payment error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }
}
