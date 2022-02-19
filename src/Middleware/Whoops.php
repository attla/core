<?php

namespace Attla\Middleware;

use Illuminate\Http\Request;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class Whoops
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
        $this->shouldDisplayException(config('debug'), $request);

        return $next($request);
    }

    /**
     * Decide whether to display exception
     *
     * @param bool $debug
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function shouldDisplayException($debug, $request)
    {
        if (!$debug) {
            return;
        }

        // Enable PrettyPageHandler with editor options
        $prettyPageHandler = new PrettyPageHandler();

        // Add more information to the PrettyPageHandler
        $contentCharset = '<none>';
        if (
            method_exists($request, 'getContentCharset') === true
            && $request->getContentCharset() !== null
        ) {
            $contentCharset = $request->getContentCharset();
        }

        $prettyPageHandler->addDataTable(config('name') . ' Framework - ' . app()->version(), [
            'Accept Charset'  => $request->header('ACCEPT_CHARSET') ?: '<none>',
            'Content Charset' => $contentCharset,
            'HTTP Method'     => $request->method(),
            'Path'            => $request->path(),
            'Query String'    => $request->query() ?: '<none>',
            'Base URL'        => (string) $request->root(),
        ]);

        // Set Whoops to default exception handler
        $whoops = new Run();
        $whoops->pushHandler($prettyPageHandler);

        // Enable JsonResponseHandler when request is AJAX
        if ($request->expectsJson()) {
            $whoops->pushHandler(new JsonResponseHandler());
        }

        // Add each custom handler to whoops handler stack
        if (empty($this->handlers) === false) {
            foreach ($this->handlers as $handler) {
                $whoops->pushHandler($handler);
            }
        }

        $whoops->register();
    }
}
