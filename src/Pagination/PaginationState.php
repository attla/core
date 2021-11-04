<?php

namespace Attla\Pagination;

use Illuminate\Container\Container;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;

class PaginationState
{
    /**
     * Bind the pagination state resolvers using the given application container as a base
     *
     * @param \Illuminate\Container\Container $app
     * @return void
     */
    public static function resolveUsing(Container $app)
    {
        $view = $app['view'];

        $view->addNamespace('pagination', collect(config('view.paths'))->map(function ($path) {
            if (is_dir($paginationPath = "$path/pagination")) {
                return $paginationPath;
            }
        })->filter()
        ->push(__DIR__ . '/views')
        ->all());

        $request = $app['request'];

        Paginator::viewFactoryResolver(function () use ($view) {
            return $view;
        });

        Paginator::currentPathResolver(function () use ($request) {
            return $request->url();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') use ($request) {
            $page = $request->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });

        Paginator::queryStringResolver(function () use ($request) {
            return $request->query();
        });

        $paginationView = config('pagination', 'bootstrap');
        Paginator::defaultView('pagination::' . $paginationView);
        Paginator::defaultSimpleView('pagination::simple-' . $paginationView);

        CursorPaginator::currentCursorResolver(function ($cursorName = 'cursor') use ($request) {
            return Cursor::fromEncoded($request->input($cursorName));
        });
    }
}
