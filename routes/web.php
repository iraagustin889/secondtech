<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\ProductController;

use Illuminate\Support\Facades\Route;


Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');

// Katalog publik (Issue #4), tanpa login
Route::get('/products', [CatalogController::class, 'index'])->name('products.index');

// Detail produk (Issue #5), publik
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Profil pengguna (Issue #5), semua user yang login
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Halaman admin, hanya untuk user login dengan role admin.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard (Issue #11)
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Manajemen pengguna (Issue #11)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        

        // Kategori
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

        Route::post('/orders/{order}/payment/confirm', [PaymentController::class, 'confirm'])->name('orders.payment.confirm');
    });

// Halaman penjual: semua user yang login boleh jual (sesuai SRS)
Route::middleware(['auth'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        // Daftar produk milik penjual
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        // Form tambah produk
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        // Simpan produk baru
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/process', [SellerOrderController::class, 'process'])->name('orders.process');
        Route::post('/orders/{order}/shipment', [SellerOrderController::class, 'ship'])->name('orders.ship');
        Route::post('/orders/{order}/cod-agreement', [SellerOrderController::class, 'agreeCod'])->name('orders.cod.agree');
        Route::post('/orders/{order}/cod-confirmation', [SellerOrderController::class, 'confirmCod'])->name('orders.cod.confirm');
    });
