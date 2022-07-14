<?php

namespace Attla\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\SessionManager;

class StartSession
{
    /**
     * The session manager
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $manager;

    /**
    * The session manager
    *
    * @var \Illuminate\Session\Store
    */
    protected $store;

    /**
     * The configuration of session manager
     *
     * @var array
     */
    protected $config;

    /**
     * The Attla tokens instance
     *
     * @var \Attla\Tokens
     */
    protected $tokens;

    /**
     * Create a new session middleware
     *
     * @param \Illuminate\Session\SessionManager $manager
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
        $this->store = $manager->driver();
        $this->config = $manager->getSessionConfig();
    }

    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $session = $this->getSession();
        $session->start();

        $response = $next($request);

        $this->storeCurrentUrl($request, $session);
        $this->addCookieToResponse($response, $request, $session);
        $this->saveSession();

        return $response;
    }

    /**
     * Check if session driver is a cookie
     *
     * @return bool
     */
    protected function isCookieDriver()
    {
        return $this->config['driver'] == 'cookie';
    }

    /**
     * Get the session implementation from the manager
     *
     * @return \Illuminate\Session\SessionManager
     */
    public function getSession()
    {
        $sessionCookie = \Cookier::get('session');
        if ($sessionCookie && $this->isCookieDriver()) {
            $sessionCookieObject = \Pincryp::decode($sessionCookie);
            if (is_array($sessionCookieObject) || is_object($sessionCookieObject)) {
                foreach ($sessionCookieObject as $key => $value) {
                    $this->store->put($key, $value);
                }
            }
            $this->store->ageFlashData();
        } elseif ($sessionCookie) {
            $this->manager->setId($sessionCookie);
        }

        return $this->manager;
    }

    /**
     * Store the current URL for the request if necessary
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Session\SessionManager $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        if (
            $request->method() === 'GET' &&
            $request->route() instanceof Route &&
            ! $request->ajax() &&
            ! $request->prefetch()
        ) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    /**
     * Add the session cookie to the application response
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Session\SessionManager $session
     * @return void
     */
    protected function addCookieToResponse($response, $request, $session)
    {
        if (!empty($response->headers)) {
            $stringMode = config('app.to_string');
            config(['app.to_string' => 'serialize']);

            if ($request->method() == 'GET') {
                $session->ageFlashData();
                if ($session->previousUrl() == $request->fullUrl()) {
                    $session->remove('errors');
                    $session->remove('_old_input');
                }
            }

            $value = $this->isCookieDriver() ? \Pincryp::encode(collect($session->all())->except('_token')) : $session->getId();
            config(['app.to_string' => $stringMode]);

            \Cookier::set('session', $value, $this->config['lifetime']);
        }
    }

    /**
     * Save the session data to storage
     *
     * @return void
     */
    protected function saveSession()
    {
        if (!$this->isCookieDriver()) {
            $this->manager->driver()->save();
        }
    }
}
