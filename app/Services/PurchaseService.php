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
     * إنشاء عدة مشتريات (صف أو مواد متعددة)
     */
    public function createMultiplePurchases(User $user, array $purchasables, $currencyId = null): \Illuminate\Support\Collection
    {
        $purchases = collect();
        
        foreach ($purchasables as $item) {
            $purchasable = $item['purchasable'];
            $type = $item['type'];
            
            // التحقق من عدم وجود شراء مسبق
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('purchasable_type', get_class($purchasable))
                ->where('purchasable_id', $purchasable->id)
                ->where('status', 'completed')
                ->first();
            
            if ($existingPurchase) {
                continue; // تخطي إذا كان موجوداً
            }
            
            $purchase = $this->createPurchase($user, $purchasable, $type, $currencyId);
            $purchases->push($purchase);
        }
        
        return $purchases;
    }

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

        // إذا كان مجانياً، إنشاء دفع مكتمل تلقائياً وإكمال التسجيل
        if ($isFree) {
            try {
                DB::beginTransaction();
                
                Payment::create([
                    'purchase_id' => $purchase->id,
                    'payment_method' => 'wallet', // أو 'free'
                    'amount' => 0,
                    'currency' => $currency->code ?? 'SAR',
                    'status' => 'completed',
                    'transaction_id' => 'FREE-' . $purchase->id,
                ]);
                
                // إكمال الشراء وإنشاء التسجيلات
                $this->completePurchase($purchase);
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error completing free purchase: ' . $e->getMessage());
                // لا نرمي exception هنا لأن Purchase تم إنشاؤه بالفعل
            }
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
        Log::info('completePurchase started', [
            'purchase_id' => $purchase->id,
            'purchase_type' => $purchase->purchase_type,
        ]);

        // تحميل العلاقات اللازمة
        $purchase->refresh();
        $purchase->load(['user', 'purchasable']);
        
        $user = $purchase->user;
        $purchasable = $purchase->purchasable;
        
        Log::info('Purchase data loaded', [
            'purchase_id' => $purchase->id,
            'user_exists' => $user ? 'yes' : 'no',
            'purchasable_exists' => $purchasable ? 'yes' : 'no',
            'purchasable_type' => $purchase->purchasable_type,
            'purchasable_id' => $purchase->purchasable_id,
        ]);
        
        if (!$user || !$purchasable) {
            Log::error('Incomplete purchase data', [
                'purchase_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'purchasable_type' => $purchase->purchasable_type,
                'purchasable_id' => $purchase->purchasable_id,
            ]);
            throw new \Exception('لا يمكن إكمال الشراء: بيانات غير مكتملة');
        }

        $enrolledBy = auth()->id() ?? $user->id; // استخدام ID الأدمن أو المستخدم

        Log::info('Enrolled by determined', [
            'purchase_id' => $purchase->id,
            'enrolled_by' => $enrolledBy,
        ]);

        if ($purchase->purchase_type === 'class') {
            Log::info('Processing class purchase', [
                'purchase_id' => $purchase->id,
                'class_id' => $purchasable->id,
            ]);

            // تحميل المواد للصف
            if (method_exists($purchasable, 'subjects')) {
                $purchasable->load('subjects');
            }
            
            // إنشاء تسجيل للصف
            try {
                $classEnrollment = \App\Models\ClassEnrollment::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'class_id' => $purchasable->id,
                    ],
                    [
                        'status' => 'approved',
                        'enrolled_by' => $enrolledBy,
                        'enrolled_at' => now(),
                        'notes' => 'تسجيل تلقائي بعد الشراء',
                    ]
                );

                Log::info('ClassEnrollment created/updated', [
                    'purchase_id' => $purchase->id,
                    'class_enrollment_id' => $classEnrollment->id,
                    'user_id' => $user->id,
                    'class_id' => $purchasable->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating ClassEnrollment', [
                    'purchase_id' => $purchase->id,
                    'user_id' => $user->id,
                    'class_id' => $purchasable->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            // إنشاء تسجيلات لجميع المواد في الصف
            try {
                $subjects = $purchasable->subjects()->where('is_active', true)->get();
                
                Log::info('Subjects loaded for class', [
                    'purchase_id' => $purchase->id,
                    'class_id' => $purchasable->id,
                    'subjects_count' => $subjects->count(),
                ]);

                foreach ($subjects as $subject) {
                    try {
                        $enrollment = \App\Models\Enrollment::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'subject_id' => $subject->id,
                            ],
                            [
                                'status' => 'active',
                                'enrolled_by' => $enrolledBy,
                                'enrolled_at' => now(),
                                'notes' => 'تسجيل تلقائي بعد شراء الصف',
                            ]
                        );

                        Log::info('Enrollment created/updated for subject', [
                            'purchase_id' => $purchase->id,
                            'enrollment_id' => $enrollment->id,
                            'user_id' => $user->id,
                            'subject_id' => $subject->id,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error creating Enrollment for subject', [
                            'purchase_id' => $purchase->id,
                            'user_id' => $user->id,
                            'subject_id' => $subject->id,
                            'error' => $e->getMessage(),
                        ]);
                        // نستمر في باقي المواد بدلاً من إيقاف العملية
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error loading/processing subjects', [
                    'purchase_id' => $purchase->id,
                    'class_id' => $purchasable->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        } elseif ($purchase->purchase_type === 'subject') {
            Log::info('Processing subject purchase', [
                'purchase_id' => $purchase->id,
                'subject_id' => $purchasable->id,
            ]);

            // إنشاء تسجيل للمادة
            try {
                $enrollment = \App\Models\Enrollment::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'subject_id' => $purchasable->id,
                    ],
                    [
                        'status' => 'active',
                        'enrolled_by' => $enrolledBy,
                        'enrolled_at' => now(),
                        'notes' => 'تسجيل تلقائي بعد الشراء',
                    ]
                );

                Log::info('Enrollment created/updated for subject', [
                    'purchase_id' => $purchase->id,
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $user->id,
                    'subject_id' => $purchasable->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating Enrollment for subject', [
                    'purchase_id' => $purchase->id,
                    'user_id' => $user->id,
                    'subject_id' => $purchasable->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        Log::info('completePurchase finished successfully', [
            'purchase_id' => $purchase->id,
        ]);

        // Invalidate cache after successful purchase
        try {
            app(\App\Services\Pricing\PricingCacheManager::class)->invalidateOnPurchase($user, $purchasable);
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate pricing cache: ' . $e->getMessage());
        }
    }

    /**
     * إلغاء شراء معلّق بواسطة الطالب (والدفع المرتبط إن وجد حالته انتظار)
     */
    public function cancelPendingByStudent(Purchase $purchase, User $user): void
    {
        if ((int) $purchase->user_id !== (int) $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('غير مصرح بإلغاء هذا الطلب');
        }

        if ($purchase->status !== 'pending') {
            throw new \InvalidArgumentException('لا يمكن إلغاء هذا الطلب لأنه ليس قيد المراجعة');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->refresh();

            if ($purchase->status !== 'pending') {
                throw new \InvalidArgumentException('لا يمكن إلغاء هذا الطلب لأنه ليس قيد المراجعة');
            }

            $purchase->update([
                'status' => 'cancelled',
                'cancelled_by' => 'student',
                'cancelled_at' => now(),
            ]);

            $pendingPayment = Payment::where('purchase_id', $purchase->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingPayment) {
                $pendingPayment->update([
                    'status' => 'failed',
                    'reviewed_at' => now(),
                    'review_notes' => 'ألغى الطالب الطلب نهائياً',
                ]);
            }
        });

        try {
            $purchase->load('purchasable');
            if ($purchase->purchasable) {
                app(\App\Services\Pricing\PricingCacheManager::class)->invalidateOnPurchase($user, $purchase->purchasable);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to invalidate pricing cache after purchase cancel: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من وصول المستخدم للصف/المادة
     */
    public function checkAccess(User $user, $purchasable): bool
    {
        $accessResolver = app(\App\Services\Pricing\AccessResolver::class);

        if ($purchasable instanceof Subject) {
            return $accessResolver->hasSubjectAccess($user, $purchasable);
        }

        if ($purchasable instanceof SchoolClass) {
            return $accessResolver->hasClassAccess($user, $purchasable);
        }

        // Fallback للمنطق القديم
        if (($purchasable->is_free ?? false) || ($purchasable->price ?? 0) == 0) {
            return true;
        }

        $purchase = Purchase::where('user_id', $user->id)
            ->where('purchasable_type', get_class($purchasable))
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 'completed')
            ->first();

        if ($purchase) {
            if ($purchase->expires_at && $purchase->expires_at->isPast()) {
                return false;
            }
            return true;
        }

        return false;
    }
}
