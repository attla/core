<?php

namespace Attla\Providers\Http;

use Spatie\Ignition\Ignition;
use Illuminate\Support\ServiceProvider;

class IgnitionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application service
     *
     * @return void
     */
    public function boot()
    {
        if (!\App::runningInConsole()) {
            Ignition::make()
                ->setTheme('auto')
                ->applicationPath(\App::basePath())
                ->shouldDisplayException(config('app.debug'))
                ->registerMiddleware([
                    AddUserInformation::class,
                    AddEnvironmentInformation::class,
                ])->register();
        }
    }
}

use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddUserInformation implements FlareMiddleware
{
    public function handle(Report $report, \Closure $next)
    {
        if ($user = \Auth::user()) {
            $report->group('user', $user->toArray());
        }
        return $next($report);
    }
}

class AddEnvironmentInformation implements FlareMiddleware
{
    public function handle(Report $report, \Closure $next)
    {
        $attlaVersion = \App::version();
        $report->frameworkVersion($attlaVersion);

        $report->group('env', [
            'Attla version' => $attlaVersion,
            'Attla locale' => \App::getLocale(),
            'App debug' => config('app.debug'),
            'environment' => config('app.env'),
            'php_version' => phpversion(),
        ]);

        return $next($report);
    }
}
