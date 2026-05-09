<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\Master\SysUser;
use App\Models\Ticket;
use App\Policies\HelpArticlePolicy;
use App\Policies\HelpCategoryPolicy;
use App\Policies\SysUserPolicy;
use App\Policies\TicketPolicy;
use App\Services\TicketService;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(function (SysUser $user, string $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Gate::policy(SysUser::class, SysUserPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(HelpArticle::class, HelpArticlePolicy::class);
        Gate::policy(HelpCategory::class, HelpCategoryPolicy::class);
        Gate::define('manage-users-access', fn (SysUser $user) => $user->can('users.view') || $user->can('roles.view'));

        View::composer('partials.erp.topbar', function ($view) {
            if (! Schema::hasTable('notifications') || ! Schema::hasTable('sys_users')) {
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
