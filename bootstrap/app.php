<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/v1/extension/*',
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->routeIs(
                'frontend.checkout',
                'frontend.checkout.process',
                'frontend.payment',
                'frontend.payment.process'
            )) {
                return route('student.login');
            }

            return route('login');
        });

       $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'role-list' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'check.user.active' => \App\Http\Middleware\CheckUserActive::class,
            'share.student.pending.purchases' => \App\Http\Middleware\ShareStudentPendingPurchasesForLayout::class,
            'ensure.student.enrollment' => \App\Http\Middleware\EnsureStudentEnrollment::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'extension.api' => \App\Http\Middleware\EnsureExtensionApiAccess::class,
        ]);
    })
    ->withProviders([
        \App\Providers\PermissionServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        // منع أخطاء الـ Broadcasting من تعطيل التطبيق
        $exceptions->dontReport([
            \Illuminate\Broadcasting\BroadcastException::class,
            \Pusher\ApiErrorException::class,
            \Pusher\PusherException::class,
            \GuzzleHttp\Exception\ConnectException::class,
            \GuzzleHttp\Exception\RequestException::class,
        ]);

        $exceptions->render(function (\Throwable $e, $request) {
            // أخطاء الـ Broadcasting: سجل الخطأ وأعد الاستمرار
            if ($e instanceof \Illuminate\Broadcasting\BroadcastException
                || $e instanceof \Pusher\ApiErrorException
                || $e instanceof \Pusher\PusherException
                || $e instanceof \GuzzleHttp\Exception\ConnectException
                || $e instanceof \GuzzleHttp\Exception\RequestException
            ) {
                \Illuminate\Support\Facades\Log::warning('Broadcasting failed (non-fatal)', [
                    'error' => $e->getMessage(),
                    'url' => $request->url(),
                    'ip' => $request->ip(),
                ]);

                // إذا كان طلب AJAX/JSON، أعد استجابة JSON نظيفة
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'تمت العملية بنجاح',
                        'broadcast_unavailable' => true,
                    ], 200);
                }

                // للطلبات العادية، لا تفعل شيئاً (اترك الـ flow يكمل)
                return null;
            }
        });
    })->create();
