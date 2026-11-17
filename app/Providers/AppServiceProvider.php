<?php

namespace App\Providers;

use App\Models\BookingRequest;
use App\Models\Dentist;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Policies\BookingRequestPolicy;
use App\Policies\ContentPolicy;
use App\Settings\ClinicSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $isProduction = $this->app->isProduction();

        // Catch common coding mistakes early on your PC (never on the live site).
        Model::shouldBeStrict($this->app->environment('local', 'testing'));

        // On the live site: always use HTTPS, and block commands that wipe the database.
        if ($isProduction) {
            URL::forceScheme('https');
        }
        DB::prohibitDestructiveCommands($isProduction);

        // Strong passwords everywhere (staff accounts, password resets).
        Password::defaults(fn () => Password::min(12)->mixedCase()->numbers()->uncompromised());

        // Website content shares one set of permission rules.
        foreach ([Page::class, Service::class, Dentist::class, Faq::class] as $model) {
            Gate::policy($model, ContentPolicy::class);
        }

        Gate::policy(BookingRequest::class, BookingRequestPolicy::class);

        // Spam protection for the booking form: a few requests per visitor per hour/day.
        RateLimiter::for('booking', fn (Request $request) => [
            Limit::perHour(config('booking.max_per_hour'))->by($request->ip()),
            Limit::perDay(config('booking.max_per_day'))->by($request->ip()),
        ]);

        // Clinic details (phone, hours, ...) are available in every public page template as $clinic.
        View::composer(['blocks.*', 'pages.*', 'services.*', 'components.*'], function ($view): void {
            $view->with('clinic', app(ClinicSettings::class));
        });

        // The /up health check (used by the uptime monitor and the deploy pipeline)
        // also fails if the database or cache can't be reached.
        Event::listen(DiagnosingHealth::class, function (): void {
            DB::select('select 1');
            Cache::put('health-check', now()->timestamp, 60);
        });

        // Top menu and footer links come from the Pages screen ("Show in menu / footer").
        View::composer('components.layouts.site', function ($view): void {
            $pages = Page::published()
                ->where(fn ($query) => $query->where('show_in_menu', true)->orWhere('show_in_footer', true))
                ->orderBy('menu_order')
                ->get(['id', 'title', 'slug', 'show_in_menu', 'show_in_footer']);

            $view->with([
                'menuPages' => $pages->where('show_in_menu', true)->values(),
                'footerPages' => $pages->where('show_in_footer', true)->values(),
            ]);
        });
    }
}
