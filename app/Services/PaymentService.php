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
                $path = $receiptFile->store('receipts', 'public');
            }

            return [
                'success' => true,
                'receipt_file' => $path,
                'status' => 'pending', // يحتاج مراجعة
            ];
        } catch (\Exception $e) {
            Log::error('IBAN payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
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

            $payment->update([
                'status' => $approved ? 'completed' : 'failed',
                'reviewed_by' => $reviewedBy ?? auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            if ($approved) {
                // إكمال الشراء
                $purchaseService = app(PurchaseService::class);
                $purchaseService->completePurchase($payment->purchase);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review payment error: ' . $e->getMessage());
            return false;
        }
    }
}
