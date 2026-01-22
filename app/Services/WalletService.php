<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * الحصول على محفظة المستخدم أو إنشاء واحدة جديدة
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0,
                'currency' => 'SAR',
                'is_active' => true,
            ]
        );
    }

    /**
     * إيداع مبلغ في المحفظة
     */
    public function deposit(User $user, float $amount, string $description = ''): WalletTransaction
    {
        try {
            DB::beginTransaction();

            $wallet = $this->getOrCreateWallet($user);
            $balanceBefore = $wallet->balance;
            $wallet->balance += $amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'description' => $description ?: 'إيداع في المحفظة',
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet deposit error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * سحب مبلغ من المحفظة
     */
    public function withdraw(User $user, float $amount, string $description = ''): WalletTransaction
    {
        try {
            DB::beginTransaction();

            $wallet = $this->getOrCreateWallet($user);

            if (!$wallet->hasBalance($amount)) {
                throw new \Exception('الرصيد غير كافٍ');
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance -= $amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'description' => $description ?: 'سحب من المحفظة',
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet withdrawal error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تسجيل معاملة شراء
     */
    public function recordPurchase(Wallet $wallet, float $amount, $reference, string $description = ''): WalletTransaction
    {
        $balanceBefore = $wallet->balance;

        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'purchase',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance,
            'description' => $description,
            'reference_type' => get_class($reference),
            'reference_id' => $reference->id,
        ]);
    }

    /**
     * تسجيل معاملة استرداد
     */
    public function recordRefund(Wallet $wallet, float $amount, $reference, string $description = ''): WalletTransaction
    {
        try {
            DB::beginTransaction();

            $balanceBefore = $wallet->balance;
            $wallet->balance += $amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'refund',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'description' => $description,
                'reference_type' => get_class($reference),
                'reference_id' => $reference->id,
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet refund error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تحويل مبلغ بين محفظتين
     */
    public function transfer(User $fromUser, User $toUser, float $amount, string $description = ''): array
    {
        try {
            DB::beginTransaction();

            $withdrawal = $this->withdraw($fromUser, $amount, "تحويل إلى {$toUser->name}");
            $deposit = $this->deposit($toUser, $amount, "تحويل من {$fromUser->name}");

            DB::commit();
            return [
                'withdrawal' => $withdrawal,
                'deposit' => $deposit,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet transfer error: ' . $e->getMessage());
            throw $e;
        }
    }
}
