<?php

namespace Attla\Middleware;

use Attla\Cookier;
use Attla\Encrypter;
use Illuminate\Translation\Translator;

class Csrf
{
    /**
     * The translator instance
     *
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Translation\Translator $translator
     * @return void
     */
    public function __construct(Translator $translator = null)
    {
        if (!is_null($translator)) {
            $this->translator = $translator;
        }
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
        if (
            $this->isReading($request) ||
            $this->tokensMatch($request)
        ) {
            if (!$this->inExceptArray($request)) {
                Cookier::set($this->timeToken(), config('csrf'), 60);
            }

            return $next($request);
        }

        return back()->withErrors([
            'csrf' => $this->translator->get('csrf.failed')
        ]);
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);
        $referer = $request->headers->get('referer');

        return is_string($token)
            && !is_null($referer)
            && strpos($referer, $request->root()) !== false
            && $token === Cookier::get($this->timeToken())
            && (Encrypter::hashEquals(url()->full() . $this->browser(), $token)
                || Encrypter::hashEquals(rtrim($referer, '/') . $this->browser(), $token));
    }

    /**
     * Get the CSRF token from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function getTokenFromRequest($request)
    {
        return $request->input($this->timeToken()) ?: $request->header('X-CSRF-TOKEN');
    }

    /**
     * Get the browser version
     *
     * @return string
     */
    private function browser()
    {
        return browser() . substr(browser_version(), 0, 2);
    }

    /**
     * Returns a random number of spaces
     *
     * @param string|array $value
     * @return string|array
     */
    private function randomRepeat($value, $length = false)
    {
        if (!$length) {
            $length = abs(mt_rand(-128, 127));
        }

        if (is_array($value)) {
            return array_fill(0, $length, str_shuffle(join('', array_map(function ($item) {
                return $this->randomRepeat($item);
            }, $value))));
        }

        if (strlen($value) > 1) {
            $value = str_shuffle($value);
        }

        return str_repeat($value, $length);
    }

    /**
     * Get CSRF input
     *
     * @return string
     */
    public function getCsrfInput($request)
    {
        $randomNl = $this->randomRepeat([" ", "\r", "\n", "\t"], 6);
        $formatInput = '%s<input%stype="hidden"%sname="'
            . $this->timeToken() . '"%svalue="'
            . config('csrf') . '"%s/>%s';

        return vsprintf($formatInput, $randomNl);
    }

    /**
     * Get a time-based token
     *
     * @param string $interval
     * @return string
     */
    protected function timeToken($interval = 'hour')
    {
        if (!in_array($interval, ['day', 'hour', 'min'])) {
            $interval = 'hour';
        }

        $timestamp = strtotime('+1' . ($interval == 'min' ? 0 : '') . ' ' . $interval);
        $length = $interval == 'min' ? 9 : ($interval == 'hour' ? 8 : 6);

        return substr(md5(dechex(substr(date('dmyHi', $timestamp), 0, $length))), 0, 10);
    }
}
