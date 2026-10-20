<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Seller\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Halaman admin, hanya untuk user login dengan role admin.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard (Issue #11)
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Manajemen pengguna (Issue #11)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Kategori
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
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
    });

