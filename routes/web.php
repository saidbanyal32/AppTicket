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
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\VendorController;
use App\Http\Controllers\Master\VendorTypeController;
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
