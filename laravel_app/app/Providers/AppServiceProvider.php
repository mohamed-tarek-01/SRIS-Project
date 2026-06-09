<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemAlert;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (
            request()->header('X-Forwarded-Proto') === 'https' ||
            str_contains(request()->getHost(), 'ngrok') ||
            str_contains(request()->getHost(), 'nip.io') ||
            app()->environment('production')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Share unread alert count with the navbar on every page (admin only).
        // Using a View Composer is the correct pattern — keeps DB logic out of Blade.
        View::composer('components.navbar', function ($view) {
            $unreadAlerts = 0;
            if (Auth::check() && Auth::user()->role === 'admin') {
                $unreadAlerts = SystemAlert::where('is_read', false)->count();
            }
            $view->with('unreadAlerts', $unreadAlerts);
        });
    }
}
