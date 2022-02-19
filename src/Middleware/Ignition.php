<?php

namespace Attla\Middleware;

use Illuminate\Http\Request;
use Spatie\Ignition\Ignition as SpatieIgnition;

class Ignition
{
    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        SpatieIgnition::make()
            ->theme('auto')
            ->applicationPath(app()->basePath())
            ->shouldDisplayException(config('debug'))
            ->registerMiddleware([
                AddUserInformation::class,
                AddEnvironmentInformation::class,
            ])->register();

        return $next($request);
    }
}


use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddUserInformation implements FlareMiddleware
{
    public function handle(Report $report, \Closure $next)
    {
        $report->group('user', optional(auth()->user())->toArray());
        return $next($report);
    }
}

class AddEnvironmentInformation implements FlareMiddleware
{
    public function handle(Report $report, \Closure $next)
    {
        $attlaVersion = app()->version();
        $report->frameworkVersion($attlaVersion);

        $report->group('env', [
            'Attla version' => $attlaVersion,
            'Attla locale' => app()->getLocale(),
            'App debug' => config('debug'),
            'environment' => config('app.env'),
            'php_version' => phpversion(),
        ]);

        return $next($report);
    }
}
