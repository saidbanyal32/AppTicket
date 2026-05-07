<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Services\TicketService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('partials.erp.topbar', function ($view) {
            if (! Schema::hasTable('notifications') || ! Schema::hasTable('users')) {
                $view->with(['topbarUnreadCount' => 0, 'topbarNotifications' => collect()]);

                return;
            }

            $userId = app(TicketService::class)->currentUserId();

            $view->with([
                'topbarUnreadCount' => AppNotification::where('user_id', $userId)->where('is_read', false)->count(),
                'topbarNotifications' => AppNotification::where('user_id', $userId)->latest('created_at')->limit(5)->get(),
            ]);
        });
    }
}
