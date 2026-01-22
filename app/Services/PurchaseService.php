<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Payment;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseService
{
    /**
     * إنشاء شراء جديد
     */
    public function createPurchase(User $user, $purchasable, string $type, $currencyId = null): Purchase
    {
        // الحصول على السعر بالعملة المختارة
        if ($currencyId) {
            $price = $purchasable->getPrice($currencyId);
            $currency = \App\Models\Currency::find($currencyId);
        } else {
            $defaultCurrency = $purchasable->defaultCurrency ?? \App\Models\Currency::getDefault();
            $price = $purchasable->getPrice($defaultCurrency->id);
            $currency = $defaultCurrency;
        }

        $isFree = ($price == 0);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'purchase_type' => $type,
            'price' => $price,
            'status' => $isFree ? 'completed' : 'pending',
            'purchased_at' => $isFree ? now() : null,
            'notes' => "شراء {$type}: " . ($purchasable->name ?? ''),
        ]);

        // إذا كان مجانياً، إنشاء دفع مكتمل تلقائياً
        if ($isFree) {
            Payment::create([
                'purchase_id' => $purchase->id,
                'payment_method' => 'wallet', // أو 'free'
                'amount' => 0,
                'currency' => $currency->code ?? 'SAR',
                'status' => 'completed',
                'transaction_id' => 'FREE-' . $purchase->id,
            ]);
        }

        return $purchase;
    }

    /**
     * معالجة الشراء بعد الدفع
     */
    public function processPurchase(Purchase $purchase, string $paymentMethod, array $data = []): bool
    {
        try {
            DB::beginTransaction();

            // تحديد حالة الدفع
            $paymentStatus = 'completed';
            if ($paymentMethod === 'iban' || $paymentMethod === 'custom') {
                $paymentStatus = 'pending';
            }

            // الحصول على العملة
            $currencyCode = $data['currency'] ?? 'SAR';
            if (isset($data['currency_id'])) {
                $currency = \App\Models\Currency::find($data['currency_id']);
                if ($currency) {
                    $currencyCode = $currency->code;
                }
            }

            // إنشاء سجل الدفع
            $payment = Payment::create([
                'purchase_id' => $purchase->id,
                'payment_method' => $paymentMethod,
                'custom_payment_method_id' => $data['custom_payment_method_id'] ?? null,
                'amount' => $purchase->price,
                'currency' => $currencyCode,
                'status' => $paymentStatus,
                'transaction_id' => $data['transaction_id'] ?? null,
                'gateway_response' => $data['gateway_response'] ?? null,
                'receipt_file' => $data['receipt_file'] ?? null,
                'payment_data' => $data['payment_data'] ?? null,
            ]);

            // إذا كان الدفع مكتملاً، تحديث حالة الشراء وإكمال التسجيل
            if ($payment->status === 'completed') {
                $purchase->update([
                    'status' => 'completed',
                    'purchased_at' => now(),
                ]);
                $this->completePurchase($purchase);
            }
            // إذا كان الدفع pending، تبقى حالة الشراء pending

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing purchase: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إكمال الشراء وإنشاء التسجيل
     */
    public function completePurchase(Purchase $purchase): void
    {
        $user = $purchase->user;
        $purchasable = $purchase->purchasable;

        if ($purchase->purchase_type === 'class') {
            // إنشاء تسجيل للصف
            $classEnrollment = \App\Models\ClassEnrollment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'class_id' => $purchasable->id,
                ],
                [
                    'status' => 'approved',
                    'enrolled_at' => now(),
                    'notes' => 'تسجيل تلقائي بعد الشراء',
                ]
            );

            // إنشاء تسجيلات لجميع المواد في الصف
            $subjects = $purchasable->subjects()->where('is_active', true)->get();
            foreach ($subjects as $subject) {
                \App\Models\Enrollment::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'status' => 'active',
                        'enrolled_at' => now(),
                        'notes' => 'تسجيل تلقائي بعد شراء الصف',
                    ]
                );
            }
        } elseif ($purchase->purchase_type === 'subject') {
            // إنشاء تسجيل للمادة
            \App\Models\Enrollment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'subject_id' => $purchasable->id,
                ],
                [
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'notes' => 'تسجيل تلقائي بعد الشراء',
                ]
            );
        }
    }

    /**
     * التحقق من وصول المستخدم للصف/المادة
     */
    public function checkAccess(User $user, $purchasable): bool
    {
        // إذا كان مجانياً، السماح بالوصول
        if (($purchasable->is_free ?? false) || ($purchasable->price ?? 0) == 0) {
            return true;
        }

        // التحقق من وجود شراء مكتمل
        $purchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', get_class($purchasable))
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 'completed')
            ->first();

        if ($purchase) {
            // التحقق من انتهاء الصلاحية
            if ($purchase->expires_at && $purchase->expires_at->isPast()) {
                return false;
            }
            return true;
        }

        // إذا كان الصف، التحقق من شراء الصف كاملاً
        if ($purchasable instanceof Subject) {
            $class = $purchasable->schoolClass;
            if ($class) {
                $classPurchase = Purchase::where('user_id', $user->id)
                    ->where('purchasable_type', SchoolClass::class)
                    ->where('purchasable_id', $class->id)
                    ->where('status', 'completed')
                    ->first();

                if ($classPurchase) {
                    if (!$classPurchase->expires_at || $classPurchase->expires_at->isFuture()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
