<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

// Appointment requests: spam-protected (honeypot + timing check) and rate-limited per visitor.
Route::get('/book', [BookingController::class, 'create'])->name('book');
Route::post('/book', [BookingController::class, 'store'])
    ->middleware([ProtectAgainstSpam::class, 'throttle:booking'])
    ->name('book.store');
Route::get('/book/thank-you', [BookingController::class, 'thanks'])->name('book.thanks');

Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

// Keep this last: any other address is looked up as an editable page (e.g. /about).
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '[a-z0-9-]+')
    ->name('page');
