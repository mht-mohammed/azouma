<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Auth\OwnerRegistrationController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\NotificationController;
use App\Http\Controllers\Owner\OpeningHourController;
use App\Http\Controllers\Owner\RestaurantImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RestaurantController::class, 'index'])->name('home');
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

Route::get('/owner/register', [OwnerRegistrationController::class, 'create'])->name('owner.register');
Route::post('/owner/register', [OwnerRegistrationController::class, 'store'])
    ->middleware('throttle:10,60')
    ->name('owner.register.store');

Route::post('/restaurants/{restaurant}/reports', [ReportController::class, 'store'])
    ->middleware('throttle:10,60')
    ->name('restaurants.reports.store');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/restaurants/pending', [App\Http\Controllers\Admin\RestaurantController::class, 'pending'])->name('restaurants.pending');
    Route::get('/restaurants', [App\Http\Controllers\Admin\RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('/restaurants/{restaurant:id}', [App\Http\Controllers\Admin\RestaurantController::class, 'show'])->name('restaurants.show');
    Route::post('/restaurants/{restaurant:id}/approve', [App\Http\Controllers\Admin\RestaurantController::class, 'approve'])->name('restaurants.approve');
    Route::post('/restaurants/{restaurant:id}/reject', [App\Http\Controllers\Admin\RestaurantController::class, 'reject'])->name('restaurants.reject');
    Route::post('/restaurants/{restaurant:id}/verify', [App\Http\Controllers\Admin\RestaurantController::class, 'verify'])->name('restaurants.verify');
    Route::post('/restaurants/{restaurant:id}/unverify', [App\Http\Controllers\Admin\RestaurantController::class, 'unverify'])->name('restaurants.unverify');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
    Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [App\Http\Controllers\Admin\ReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/resolve', [App\Http\Controllers\Admin\ReportController::class, 'resolve'])->name('reports.resolve');
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/restaurants/create', [App\Http\Controllers\Owner\RestaurantController::class, 'create'])->name('restaurants.create');
    Route::post('/restaurants', [App\Http\Controllers\Owner\RestaurantController::class, 'store'])->name('restaurants.store');
    Route::get('/restaurants/{restaurant:id}/edit', [App\Http\Controllers\Owner\RestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::put('/restaurants/{restaurant:id}', [App\Http\Controllers\Owner\RestaurantController::class, 'update'])->name('restaurants.update');
    Route::patch('/restaurants/{restaurant:id}/status', [App\Http\Controllers\Owner\RestaurantController::class, 'updateStatus'])->name('restaurants.status');
    Route::put('/restaurants/{restaurant:id}/hours', [OpeningHourController::class, 'update'])->name('restaurants.hours');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/restaurants/{restaurant:id}/images', [RestaurantImageController::class, 'store'])->name('restaurants.images.store');
    Route::delete('/restaurants/{restaurant:id}/images/{image}', [RestaurantImageController::class, 'destroy'])->name('restaurants.images.destroy');
    Route::patch('/restaurants/{restaurant:id}/images/{image}/cover', [RestaurantImageController::class, 'cover'])->name('restaurants.images.cover');
    Route::patch('/restaurants/{restaurant:id}/images/{image}/move', [RestaurantImageController::class, 'move'])->name('restaurants.images.move');
});

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
