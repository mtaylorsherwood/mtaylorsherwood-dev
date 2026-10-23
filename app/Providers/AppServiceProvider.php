<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use Inertia\Ssr\SsrRenderFailed;

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
        $this->configureErrorPages();
        $this->configureSsrFailureLogging();
    }

    /**
     * Log Inertia SSR failures.
     *
     * When the SSR server is unreachable or throws, Inertia catches it and
     * falls back to client-side rendering without surfacing anything - no
     * exception, no error page, nothing in the log. The site looks fine in a
     * browser while serving an empty body and no meta tags to every crawler and
     * link unfurler, since all of this site's SEO markup comes from SeoHead.vue
     * on the client. This turns that silent degradation into a logged error.
     */
    protected function configureSsrFailureLogging(): void
    {
        Event::listen(function (SsrRenderFailed $event): void {
            Log::error('Inertia SSR render failed; falling back to client-side rendering.', $event->toArray());
        });
    }

    /**
     * Render missing pages using the public site's 404 page.
     *
     * Other status codes fall through to Laravel's default handling, which
     * keeps Inertia's error modal available while developing.
     */
    protected function configureErrorPages(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            if ($response->statusCode() !== 404) {
                return null;
            }

            return $response->render('site/NotFound')->withSharedData();
        });
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

        Password::defaults(
            fn(): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
