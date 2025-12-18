<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\SessionComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        
        // تسجيل PermissionServiceProvider
        $this->app->register(PermissionServiceProvider::class);
        
        // تسجيل View Composer للجلسات
        View::composer('admin.layouts.master', SessionComposer::class);
    }
}