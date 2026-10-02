<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryAgentController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DeliverySlotController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'staff'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('brands', BrandController::class)->except(['show']);

    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::put('products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
    Route::post('products/{product}/images/{image}/primary', [ProductImageController::class, 'makePrimary'])->name('products.images.primary');

    Route::resource('suppliers', SupplierController::class)->except(['show']);

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('inventory/{variant}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/delivery/assign', [DeliveryController::class, 'assign'])->name('orders.delivery.assign');
    Route::post('orders/{order}/delivery/status', [DeliveryController::class, 'updateStatus'])->name('orders.delivery.update-status');

    Route::resource('coupons', CouponController::class)->except(['show']);
    Route::resource('delivery-zones', DeliveryZoneController::class)->except(['show']);
    Route::resource('delivery-slots', DeliverySlotController::class)->except(['show']);
    Route::resource('delivery-agents', DeliveryAgentController::class)->except(['show']);

    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('returns/{return}', [ReturnController::class, 'show'])->name('returns.show');
    Route::post('returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('returns/{return}/reject', [ReturnController::class, 'reject'])->name('returns.reject');
    Route::post('returns/{return}/complete', [ReturnController::class, 'complete'])->name('returns.complete');

    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{review}/moderate', [ReviewController::class, 'moderate'])->name('reviews.moderate');

    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->only(['index', 'edit', 'update']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
