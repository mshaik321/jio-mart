<?php

use App\Http\Controllers\Storefront\AddressController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\LocaleController;
use App\Http\Controllers\Storefront\OrderController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\SectionController;
use App\Livewire\Actions\Logout;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\CheckoutPage;
use App\Livewire\Storefront\WishlistPage;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/language/{locale}', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/housekeeping', [SectionController::class, 'housekeeping'])->name('sections.housekeeping');
Route::get('/food-delivery', [SectionController::class, 'food'])->name('sections.food');
Route::get('/medicines', [SectionController::class, 'medicines'])->name('sections.medicines');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])
    ->middleware(['auth', 'customer'])
    ->name('products.reviews.store');

Route::get('/cart', CartPage::class)->name('cart.index');
Route::get('/wishlist', WishlistPage::class)->name('wishlist.index');

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/checkout', CheckoutPage::class)->name('checkout.index');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/return', [OrderController::class, 'requestReturn'])->name('orders.return.store');

    Route::resource('addresses', AddressController::class)->except(['show']);
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('/logout', function (Logout $logout) {
    $logout();

    return redirect('/');
})->middleware('auth')->name('logout');

require __DIR__.'/auth.php';
