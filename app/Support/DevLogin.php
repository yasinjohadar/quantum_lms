<?php

namespace App\Support;

use App\Models\User;

/**
 * مساعد بوابة الدخول السريع للمطورين.
 *
 * كل شيء هنا مُقيَّد ببيئة التطوير: في الإنتاج تُعيد enabled() القيمة false
 * دائماً، وبالتالي لا تُسجَّل المسارات ولا يظهر أي شيء في الواجهة.
 */
class DevLogin
{
    /**
     * هل بوابة الدخول السريع متاحة؟
     */
    public static function enabled(): bool
    {
        $app = app();

        // حماية مزدوجة: أبداً في الإنتاج، وفقط في بيئات التطوير المعروفة
        if ($app->isProduction()) {
            return false;
        }

        if (! $app->environment(['local', 'development', 'dev'])) {
            return false;
        }

        return (bool) config('dev.quick_login', false);
    }

    /**
     * كلمة المرور الموحدة لحسابات التطوير.
     */
    public static function password(): string
    {
        return (string) config('dev.password', '123456789');
    }

    /**
     * قائمة الحسابات التجريبية (مع كلمة المرور والحالة).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function accounts(): array
    {
        $accounts = [];

        foreach ((array) config('dev.accounts', []) as $account) {
            if (empty($account['key']) || empty($account['email'])) {
                continue;
            }

            $account['password'] = $account['password'] ?? self::password();
            $account['label'] = $account['label'] ?? $account['key'];
            $account['name'] = $account['name'] ?? $account['label'];
            $account['description'] = $account['description'] ?? '';
            $account['exists'] = self::userFor($account) !== null;

            $accounts[] = $account;
        }

        return $accounts;
    }

    /**
     * جلب حساب تجريبي واحد بالمعرف.
     *
     * @return array<string, mixed>|null
     */
    public static function account(string $key): ?array
    {
        foreach (self::accounts() as $account) {
            if ($account['key'] === $key) {
                return $account;
            }
        }

        return null;
    }

    /**
     * المستخدم المرتبط بحساب تجريبي (إن وُجد).
     *
     * @param  array<string, mixed>  $account
     */
    public static function userFor(array $account): ?User
    {
        try {
            return User::where('email', $account['email'])->first();
        } catch (\Throwable $e) {
            // قاعدة البيانات غير مهيأة بعد
            return null;
        }
    }
}
