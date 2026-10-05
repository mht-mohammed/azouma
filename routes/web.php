<?php

use App\Http\Controllers\Auth\OwnerRegistrationController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\OpeningHourController;
use App\Http\Controllers\Owner\RestaurantImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RestaurantController::class, 'index'])->name('home');
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

Route::get('/owner/register', [OwnerRegistrationController::class, 'create'])->name('owner.register');
Route::post('/owner/register', [OwnerRegistrationController::class, 'store'])->name('owner.register.store');

Route::get('/admin', function () {
    return view('admin.placeholder');
})->middleware(['auth', 'role:admin'])->name('admin.placeholder');

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/restaurants/create', [App\Http\Controllers\Owner\RestaurantController::class, 'create'])->name('restaurants.create');
    Route::post('/restaurants', [App\Http\Controllers\Owner\RestaurantController::class, 'store'])->name('restaurants.store');
    Route::get('/restaurants/{restaurant:id}/edit', [App\Http\Controllers\Owner\RestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::put('/restaurants/{restaurant:id}', [App\Http\Controllers\Owner\RestaurantController::class, 'update'])->name('restaurants.update');
    Route::patch('/restaurants/{restaurant:id}/status', [App\Http\Controllers\Owner\RestaurantController::class, 'updateStatus'])->name('restaurants.status');
    Route::put('/restaurants/{restaurant:id}/hours', [OpeningHourController::class, 'update'])->name('restaurants.hours');
    Route::post('/restaurants/{restaurant:id}/images', [RestaurantImageController::class, 'store'])->name('restaurants.images.store');
    Route::delete('/restaurants/{restaurant:id}/images/{image}', [RestaurantImageController::class, 'destroy'])->name('restaurants.images.destroy');
    Route::patch('/restaurants/{restaurant:id}/images/{image}/cover', [RestaurantImageController::class, 'cover'])->name('restaurants.images.cover');
    Route::patch('/restaurants/{restaurant:id}/images/{image}/move', [RestaurantImageController::class, 'move'])->name('restaurants.images.move');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
