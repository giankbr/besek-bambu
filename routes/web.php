<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('contact.submit');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/products/{product:slug}', [ShopController::class, 'show'])->name('shop.product');
Route::get('/categories/{category:slug}', [ShopController::class, 'category'])->name('shop.category');

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/{order}/confirmation', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::prefix('shipping')->name('shipping.')->group(function () {
    Route::get('destinations', [ShippingController::class, 'searchDestinations'])->name('destinations');
    Route::post('cost', [ShippingController::class, 'cost'])->name('cost');
});

Route::get('/payment/{order}', [PaymentController::class, 'pay'])->name('payment.pay');
Route::post('/payment/notification', [PaymentController::class, 'notification'])->name('payment.notification');

Route::middleware(['auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'show'])->name('account.orders.show');
    Route::post('/account/orders/{order}/cancel', [AccountController::class, 'cancel'])->name('account.orders.cancel');

    Route::post('/products/{product:slug}/reviews', [ProductReviewController::class, 'store'])->name('reviews.store');

    Route::get('/account/wishlist', [WishlistController::class, 'index'])->name('account.wishlist');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard')->middleware('admin');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/admin/products');

        Route::prefix('products')->name('products.')->group(function () {
            Route::livewire('/', 'pages::admin.products.index')->name('index');
            Route::livewire('create', 'pages::admin.products.create')->name('create');
            Route::livewire('{product}/edit', 'pages::admin.products.edit')->name('edit');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::livewire('/', 'pages::admin.categories.index')->name('index');
            Route::livewire('create', 'pages::admin.categories.create')->name('create');
            Route::livewire('{category}/edit', 'pages::admin.categories.edit')->name('edit');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::livewire('/', 'pages::admin.orders.index')->name('index');
            Route::livewire('{order}', 'pages::admin.orders.show')->name('show');
        });

        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::livewire('/', 'pages::admin.reviews.index')->name('index');
        });

        Route::prefix('gallery')->name('gallery.')->group(function () {
            Route::livewire('/', 'pages::admin.gallery.index')->name('index');
            Route::livewire('create', 'pages::admin.gallery.create')->name('create');
            Route::livewire('{item}/edit', 'pages::admin.gallery.edit')->name('edit');
        });

        Route::prefix('messages')->name('messages.')->group(function () {
            Route::livewire('/', 'pages::admin.messages.index')->name('index');
        });

        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::livewire('/', 'pages::admin.coupons.index')->name('index');
            Route::livewire('create', 'pages::admin.coupons.create')->name('create');
            Route::livewire('{coupon}/edit', 'pages::admin.coupons.edit')->name('edit');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::livewire('/', 'pages::admin.users.index')->name('index');
            Route::livewire('create', 'pages::admin.users.create')->name('create');
            Route::livewire('{user}/edit', 'pages::admin.users.edit')->name('edit');
        });

        Route::prefix('customers')->name('customers.')->group(function () {
            Route::livewire('/', 'pages::admin.customers.index')->name('index');
            Route::livewire('show', 'pages::admin.customers.show')->name('show');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::livewire('/', 'pages::admin.reports.index')->name('index');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::livewire('/', 'pages::admin.settings.index')->name('index');
        });

        Route::prefix('activity-log')->name('activity-log.')->group(function () {
            Route::livewire('/', 'pages::admin.activity-log.index')->name('index');
        });

        Route::prefix('media')->name('media.')->group(function () {
            Route::livewire('/', 'pages::admin.media.index')->name('index');
        });
    });
});

require __DIR__.'/settings.php';
