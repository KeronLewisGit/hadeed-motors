<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LeadFormController;
use App\Http\Controllers\LineupController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/new', [LineupController::class, 'index'])->name('lineup.index');
Route::get('/new/{brand}', [LineupController::class, 'brand'])->name('lineup.brand');
Route::get('/new/{brand}/{model}', [LineupController::class, 'show'])->name('lineup.show');

Route::get('/pre-owned', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/pre-owned/{vehicle}', [InventoryController::class, 'show'])->name('inventory.show');

Route::get('/finance', [PageController::class, 'finance'])->name('finance');
Route::get('/finance/apply', [LeadFormController::class, 'finance'])->name('finance.apply');
Route::get('/service', [PageController::class, 'service'])->name('service');
Route::get('/parts', [LeadFormController::class, 'parts'])->name('parts');
Route::get('/trade-in', [LeadFormController::class, 'tradeIn'])->name('trade-in');
Route::get('/test-drive', [LeadFormController::class, 'testDrive'])->name('test-drive');

Route::middleware('throttle:forms')->group(function () {
    Route::post('/test-drive', [LeadFormController::class, 'storeTestDrive'])->name('test-drive.store');
    Route::post('/service', [LeadFormController::class, 'storeService'])->name('service.store');
    Route::post('/parts', [LeadFormController::class, 'storeParts'])->name('parts.store');
    Route::post('/trade-in', [LeadFormController::class, 'storeTradeIn'])->name('trade-in.store');
    Route::post('/finance/apply', [LeadFormController::class, 'storeFinance'])->name('finance.store');
    Route::post('/contact', [LeadFormController::class, 'storeEnquiry'])->name('contact.store');
});
Route::get('/thanks', [LeadFormController::class, 'thanks'])->name('thanks');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/group', [PageController::class, 'group'])->name('group');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/photo-credits', [PageController::class, 'credits'])->name('credits');
Route::get('/sitemap', [PageController::class, 'sitemapPage'])->name('sitemap.page');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

// The old single-page site: /index.html?page=vehicle&model=jimny and friends.
Route::get('/index.html', [PageController::class, 'legacy']);
Route::permanentRedirect('/about/who-we-are', '/about');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/leads', [Admin\LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/export', [Admin\LeadController::class, 'export'])->name('leads.export');
    Route::get('/leads/{lead}', [Admin\LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [Admin\LeadController::class, 'update'])->name('leads.update');
    Route::post('/leads/{lead}/notes', [Admin\LeadController::class, 'note'])->name('leads.note');
    Route::get('/leads/{lead}/files/{file}', [Admin\LeadController::class, 'file'])->name('leads.file');

    Route::middleware('role:admin,service')->get('/service', Admin\ServiceCalendarController::class)->name('service.index');

    Route::middleware('role:admin,finance')->group(function () {
        Route::get('/finance', [Admin\FinanceController::class, 'index'])->name('finance.index');
        Route::patch('/finance/{lead}', [Admin\FinanceController::class, 'update'])->name('finance.update');
    });

    Route::middleware('role:admin,sales')->group(function () {
        Route::resource('vehicles', Admin\VehicleController::class)->except('show');
        Route::patch('/vehicles/{vehicle}/status', [Admin\VehicleController::class, 'status'])->name('vehicles.status');
        Route::delete('/vehicles/{vehicle}/photos/{photo}', [Admin\VehicleController::class, 'destroyPhoto'])->name('vehicles.photos.destroy');
        Route::patch('/vehicles/{vehicle}/photos/{photo}/cover', [Admin\VehicleController::class, 'coverPhoto'])->name('vehicles.photos.cover');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    });
});
