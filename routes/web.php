<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
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
    });
});

require __DIR__.'/settings.php';
