<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Master\ActionController;
use App\Http\Controllers\Master\JabatanController;
use App\Http\Controllers\Master\ModuleController;
use App\Http\Controllers\Master\PermissionController;
use App\Http\Controllers\Master\RoleController;
use App\Http\Controllers\Master\RolePermissionController;
use App\Http\Controllers\Master\SysUserController;
use App\Http\Controllers\Master\TicketCategoryController;
use App\Http\Controllers\Master\TicketSlaController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('tickets.index') : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::prefix('master')->name('master.')->group(function () {
        $masterResource = function (string $uri, string $controller): void {
            Route::get($uri.'/datatable', [$controller, 'datatable'])->name($uri.'.datatable');
            Route::resource($uri, $controller);
        };

        $masterResource('units', UnitController::class);
        $masterResource('jabatan', JabatanController::class);
        Route::post('users/{user}/reset-password', [SysUserController::class, 'resetPassword'])
            ->middleware('permission:users.update')
            ->name('users.reset-password');

        Route::middleware('permission:users.view|users.create|users.update|users.delete')->group(fn () => $masterResource('users', SysUserController::class));
        Route::middleware('permission:permissions.manage|permissions.view')->group(fn () => $masterResource('modules', ModuleController::class));
        Route::middleware('permission:permissions.manage|permissions.view')->group(fn () => $masterResource('actions', ActionController::class));
        Route::middleware('permission:roles.view|roles.create|roles.update|roles.delete')->group(fn () => $masterResource('roles', RoleController::class));
        Route::middleware('permission:permissions.view|permissions.create|permissions.update|permissions.delete')->group(fn () => $masterResource('permissions', PermissionController::class));
        Route::middleware('permission:role-permissions.manage|roles.update')->group(fn () => $masterResource('role-permissions', RolePermissionController::class));
    });

    Route::prefix('master-ticketing')->name('master-ticketing.')->group(function () {
        Route::get('categories/datatable', [TicketCategoryController::class, 'datatable'])->name('categories.datatable');
        Route::resource('categories', TicketCategoryController::class);

        Route::get('slas/datatable', [TicketSlaController::class, 'datatable'])->name('slas.datatable');
        Route::resource('slas', TicketSlaController::class);
    });

    Route::middleware('permission:ticket.tab.my_request|ticket.tab.need_assignment|ticket.tab.assign_to_me|ticket.tab.overdue|ticket.tab.closed|ticket.tab.all')->group(function () {
        Route::get('tickets/datatable', [TicketController::class, 'datatable'])->name('tickets.datatable');
        Route::post('tickets/{ticket}/comment', [TicketController::class, 'comment'])->name('tickets.comment');
        Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::post('tickets/{ticket}/status', [TicketController::class, 'changeStatus'])->name('tickets.status');
        Route::resource('tickets', TicketController::class);
    });

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::resource('settings', SettingController::class);
});
