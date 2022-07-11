<?php

namespace Attla\Bootstrap;

use Attla\Application;
use Illuminate\Http\Request;

class SetRequest
{
    /**
     * Bootstrap the given application
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->instance('request', $app->runningInConsole() ? $this->createConsoleRequest($app) : Request::capture());
    }

    /**
     * Create a illuminate request for console
     *
     * @param \Attla\Application $app
     * @return \Illuminate\Http\Request
     */
    protected function createConsoleRequest(Application $app)
    {
        $uri = $app['config']->get('app.server.host', 'http://localhost');
        $components = parse_url($uri);
        $server = $_SERVER;

        if (isset($components['path'])) {
            $server = array_merge($server, [
                'SCRIPT_FILENAME' => $components['path'],
                'SCRIPT_NAME' => $components['path'],
            ]);
        }

        return Request::create($uri, 'GET', [], [], [], $server);
    }
}
