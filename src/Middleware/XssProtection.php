<?php

namespace Attla\Middleware;

use voku\helper\AntiXSS;

class XssProtection
{
    /**
     * The names of the attributes that should not be cleared
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Store the instance of antiXSS class
     *
     * @var AntiXSS
     */
    protected $antiXSS;

    /**
     * The following method loops through all request input and strips out all tags from
     * the request. This to ensure that users are unable to set ANY HTML within the form
     * submissions, but also cleans up input.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $inputs = $request->all();
        $this->antiXSS = new AntiXSS();

        array_walk_recursive($inputs, function (&$value, $key) {
            $value = in_array($key, $this->except, true) ? $value : $this->cleanInput($value);
        });

        $request->merge($inputs);

        return $next($request);
    }

    /**
     * Filters an input and prevents sql injection, XSS attacks etc
     *
     * @param mixed $value
     * @return mixed
     */
    protected function cleanInput($value)
    {
        $value = stripslashes($value);
        $value = strip_tags($value);
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\xC2\xA0]/', '', $value);
        $value = $this->antiXSS->xss_clean($value);
        $value = preg_replace('/\\\\+0+/', '', $value);

        return $value;
    }
}
