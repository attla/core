<?php

namespace Attla\Middleware;

use Illuminate\Translation\Translator;

class Csrf
{
    /**
     * The translator instance
     *
     * @var \Illuminate\Translation\Translator
     */
    protected Translator $translator;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    /**
     * CSRF token
     *
     * @var string
     */
    protected string $token = '';

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

        $this->token = \Pincryp::hash(
            url()->full()
            . \Browser::browserFamily()
            . \Browser::browserVersionMajor()
        );
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
        $isExcept = $this->inExceptArray($request);

        if (
            $this->isReading($request)
            || $isExcept
            || $this->tokensMatch($request)
        ) {
            if (!$isExcept) {
                \Cookier::set($this->timeToken(), $this->token, 60);
            }

            return $next($request);
        }

        return back()->withErrors([
            'csrf' => $this->translator->get('Session token has expired, please try again.')
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
        foreach ((array) $this->except as $except) {
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
            || !\Browser::isBot()
            && ($token === \Cookier::get($this->timeToken())
                || (\Pincryp::hashEquals(url()->full() . $this->browser(), $token)
                    || \Pincryp::hashEquals(rtrim($referer, '/') . $this->browser(), $token)));
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
        return \Browser::browserFamily() . \Browser::browserVersionMajor();
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
            . $this->token . '"%s/>%s';

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
