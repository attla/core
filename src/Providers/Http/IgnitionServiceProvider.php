<?php

namespace Attla\Providers\Http;

use Spatie\Ignition\Ignition;

class IgnitionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        Ignition::make()
            ->theme('auto')
            ->applicationPath(app()->basePath())
            ->shouldDisplayException(config('debug'))
            ->registerMiddleware([
                AddUserInformation::class,
                AddEnvironmentInformation::class,
            ])->register();
    }
}

use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddUserInformation implements FlareMiddleware
{
    public function handle(Report $report, \Closure $next)
    {
        $report->group('user', optional(\Auth::user())->toArray());
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
            'App debug' => config('debug'),
            'environment' => config('app.env'),
            'php_version' => phpversion(),
        ]);

        return $next($report);
    }
}