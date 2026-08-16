<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureRateLimiting();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        // The app sits behind an Nginx reverse proxy in production, so
        // asset/route URLs must be generated with https even though the
        // request itself reaches PHP-FPM over plain HTTP internally.
        URL::forceScheme(app()->isProduction() ? 'https' : 'http');
    }

    /**
     * Grant the Admin role every ability, implicitly, via Laravel's Gate.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('Admin') ? true : null;
        });

        // Explicit, so the Pulse dashboard stays admin-only in every
        // environment instead of relying on Pulse's "local only" default.
        Gate::define('viewPulse', fn (User $user): bool => $user->hasRole('Admin'));
    }

    /**
     * Define named rate limiters for endpoints that are expensive or
     * sensitive enough to need their own throttle, separate from the
     * general per-route limits applied in routes files.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('exports', fn (Request $request): Limit => Limit::perMinute(10)
            ->by($request->user()?->id ?: $request->ip()));
    }
}
