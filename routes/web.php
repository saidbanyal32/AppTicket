<?php

use App\Http\Controllers\Master\CostCodeController;
use App\Http\Controllers\Master\ItemCategoryController;
use App\Http\Controllers\Master\ItemController;
use App\Http\Controllers\Master\ItemUnitController;
use App\Http\Controllers\Master\JabatanController;
use App\Http\Controllers\Master\PermissionController;
use App\Http\Controllers\Master\ProjectController;
use App\Http\Controllers\Master\ProjectSiteController;
use App\Http\Controllers\Master\RoleController;
use App\Http\Controllers\Master\RolePermissionController;
use App\Http\Controllers\Master\SysUserController;
use App\Http\Controllers\Master\TicketCategoryController;
use App\Http\Controllers\Master\TicketSlaController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\VendorController;
use App\Http\Controllers\Master\VendorTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('master')->name('master.')->group(function () {
    $masterResource = function (string $uri, string $controller): void {
        Route::get($uri.'/datatable', [$controller, 'datatable'])->name($uri.'.datatable');
        Route::resource($uri, $controller);
    };

    $masterResource('units', UnitController::class);
    $masterResource('jabatan', JabatanController::class);
    $masterResource('users', SysUserController::class);
    $masterResource('roles', RoleController::class);
    $masterResource('permissions', PermissionController::class);
    $masterResource('role-permissions', RolePermissionController::class);
    $masterResource('item-categories', ItemCategoryController::class);
    $masterResource('item-units', ItemUnitController::class);
    $masterResource('items', ItemController::class);
    $masterResource('projects', ProjectController::class);
    $masterResource('project-sites', ProjectSiteController::class);
    $masterResource('cost-codes', CostCodeController::class);
    $masterResource('vendor-types', VendorTypeController::class);
    $masterResource('vendors', VendorController::class);
});

Route::prefix('master-ticketing')->name('master-ticketing.')->group(function () {
    Route::get('categories/datatable', [TicketCategoryController::class, 'datatable'])->name('categories.datatable');
    Route::resource('categories', TicketCategoryController::class);

    Route::get('slas/datatable', [TicketSlaController::class, 'datatable'])->name('slas.datatable');
    Route::resource('slas', TicketSlaController::class);
});

Route::get('tickets/datatable', [TicketController::class, 'datatable'])->name('tickets.datatable');
Route::post('tickets/{ticket}/comment', [TicketController::class, 'comment'])->name('tickets.comment');
Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
Route::post('tickets/{ticket}/status', [TicketController::class, 'changeStatus'])->name('tickets.status');
Route::resource('tickets', TicketController::class);

Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

Route::resource('settings', SettingController::class);
