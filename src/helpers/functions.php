<?php

use Attla\UserAgent;
use Attla\Encrypter;
use Attla\Application;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Date;

/**
 * Encode anything
 *
 * @param mixed $data
 * @return mixed
 */
function encode($data)
{
    return Encrypter::encode($data);
}

/**
 * Decode anything
 *
 * @param mixed $data
 * @param bool $assoc
 * @return mixed
 */
function decode($data, $assoc = false)
{
    return Encrypter::decode($data, '', $assoc);
}

/**
 * Encrypt a string following a salt, salt should not be passed if it is not a hash
 *
 * @param string $str
 * @param string $salt
 * @return string
 */
function encrypt($password, $salt = '')
{
    return Encrypter::hash($password, $salt);
}

/**
 * Generate a form field to spoof the HTTP verb used by forms
 *
 * @param string $method
 * @return HtmlString
 */
function method_field($method)
{
    return new HtmlString('<input type="hidden" name="_method" value="' . strtoupper($method) . '">');
}

/**
 * Generate a CSRF token form field
 *
 * @return HtmlString
 */
function csrf_field()
{
    return new HtmlString((new Attla\Middleware\Csrf())->getCsrfInput(app('request')));
}

/**
 * Dump the variable and die
 *
 * @param mixed ...$var
 * @return void
 */
function dumper(...$var)
{
    array_map(function ($x) {
        (new \Attla\Dumper())->dump($x);
    }, func_get_args());

    exit(1);
}

/**
 * Var dump the variable and die
 *
 * @param mixed ...$var
 * @return void
 */
function vd(...$var)
{
    if (!app()->runningInConsole()) {
        echo '<pre>';
    }

    array_map(function ($x) {
        var_dump($x);
    }, func_get_args());

    exit(1);
}

/**
 * Get a first and last name
 *
 * @param string $name
 * @return string
 */
function name($name)
{
    if (count($exploded = explode(' ', $name)) > 1) {
        return $exploded[0] . ' ' . (strlen($exploded[1]) > 2 ? $exploded[1] : end($exploded));
    }

    return $name;
}

/**
 * Get the year in 2018 - 2020 format
 *
 * @return string
 */
function year()
{
    $currentYear = date('Y');
    $year = (int) config('year');

    if (!$year) {
        return $currentYear;
    }

    return $currentYear > $year ? $year . ' - ' . $currentYear : $year;
}

/**
 * Get the available container instance
 *
 * @param string|null $abstract
 * @param array $parameters
 * @return \Attla\Application|mixed
 */
function app($abstract = null, array $parameters = [])
{
    $application = Application::getInstance();
    if (is_null($abstract)) {
        return $application;
    }

    return $application->make($abstract, $parameters);
}

/**
 * Resolve a service from the container
 *
 * @param string $name
 * @param array $parameters
 * @return mixed
 */
function resolve($name, array $parameters = [])
{
    return app($name, $parameters);
}

/**
 * Get the config of application
 *
 * @param array|string|null $key
 * @param mixed $default
 * @return \Attla\Config|mixed
 */
function config($key = null, $default = null)
{
    $config = app('config');
    if (is_null($key)) {
        return $config;
    }

    if (is_array($key)) {
        return $config->set($key);
    }

    return $config->get($key, $default);
}

/**
 * Get a route URL by name
 *
 * @param string|null $name
 * @param mixed $parameters
 * @param bool $absolute
 * @return \lluminate\Routing\Router|string|false
 */
function route($name = null, $parameters = [], $absolute = true)
{
    $router = app('router');

    if (is_null($name)) {
        return $router;
    }

    if (!is_null($route = $router->getRoutes()->getByName($name))) {
        return app('url')->toRoute($route, $parameters, $absolute);
    }

    return false;
}

/**
 * Create a new redirect response to a named route
 *
 * @param string $route
 * @param mixed $parameters
 * @param int $status
 * @param array $headers
 * @return \Illuminate\Http\RedirectResponse
 */
function to_route($route, $parameters = [], $status = 302, $headers = [])
{
    return redirect()->route($route, $parameters, $status, $headers);
}

/**
 * Get the available auth instance
 *
 * @param string|null $guard
 * @return \Attla\Auth\Authenticator
 */
function auth($guard = null)
{
    $auth = app('auth');
    if (is_null($guard)) {
        return $auth;
    }

    return $auth->guard($guard);
}

/**
 * Return a new response from the application
 *
 * @param \Illuminate\Contracts\View\View|string|array|null $content
 * @param int $status
 * @param array $headers
 * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
 */
function response($content = '', $status = 200, array $headers = [])
{
    $factory = app(Illuminate\Contracts\Routing\ResponseFactory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($content, $status, $headers);
}

/**
 * Creates a redirect
 *
 * @param string|null $to
 * @param int $status
 * @param array $headers
 * @param bool|null $secure
 * @return \Illuminate\Http\RedirectResponse
 */
function redirect($to = null, $status = 302, $headers = [], $secure = null)
{
    $redirector = app('redirect');

    if (is_null($to)) {
        return $redirector;
    }

    return $redirector->to($to, $status, $headers, $secure);
}

/**
 * Create a new redirect response to the previous location
 *
 * @param int $status
 * @param array $headers
 * @param mixed $fallback
 * @return \Illuminate\Http\RedirectResponse
 */
function back($status = 302, $headers = [], $fallback = false)
{
    return app('redirect')->back($status, $headers, $fallback);
}

/**
 * Generate a url for the application
 *
 * @param string|null $path
 * @param mixed $parameters
 * @param bool|null $secure
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function url($path = null, $parameters = [], $secure = null)
{
    $urlGenerator = app('url');
    if (is_null($path)) {
        return $urlGenerator;
    }

    return $urlGenerator->to($path, $parameters, $secure);
}

/**
 * Alias for url
 *
 * @param string|null $path
 * @param mixed $parameters
 * @param bool|null $secure
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function uri($path = '', $parameters = [], $secure = null)
{
    return url($path, $parameters, $secure);
}

/**
 * Get an instance of the current request or an input item from the request
 *
 * @param array|string|null $key
 * @param mixed $default
 * @return \Illuminate\Http\Request|string|array|null
 */
function request($key = null, $default = null)
{
    $request = app('request');

    if (is_null($key)) {
        return $request;
    }

    if (is_array($key)) {
        return $request->only($key);
    }

    return is_null($value = $request->__get($key)) ? value($default) : $value;
}

/**
 * Get/set the specified session value
 * If an array is passed as the key, we will assume you want to set an array of values
 *
 * @param array|string|null $key
 * @param mixed $default
 * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
 */
function session($key = null, $default = null)
{
    $session = app('session');
    if (is_null($key)) {
        return $session;
    }

    if (is_array($key)) {
        return $session->put($key);
    }

    return $session->get($key, $default);
}

/**
 * Retrieve an old input item
 *
 * @param string|null $key
 * @param mixed $default
 * @return mixed
 */
function old($key = null, $default = null)
{
    return app('request')->old($key, $default);
}

/**
 * Create a new Validator instance
 *
 * @param array $data
 * @param array $rules
 * @param array $messages
 * @param array $customAttributes
 * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Contracts\Validation\Factory
 */
function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
{
    $factory = app('validator');

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($data, $rules, $messages, $customAttributes);
}

/**
 * Get the evaluated view contents for the given view
 *
 * @param string|null $view
 * @param \Illuminate\Contracts\Support\Arrayable|array $data
 * @param array $mergeData
 * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
 */
function view($view = null, $data = [])
{
    if (is_null($view)) {
        return app('view');
    }

    return response()->view($view, $data);
}
/**
 * Throw an HttpException with the given data
 *
 * @param int $code
 * @param string $message
 * @param array $headers
 * @return void
 *
 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
 */
function abort($code, $message = null, array $headers = [])
{
    if ($code == 404) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException($message);
    }

    throw new \Symfony\Component\HttpKernel\Exception\HttpException($code, $message, null, $headers);
}

/**
 * Translate the given message
 *
 * @param string|null $key
 * @param array $replace
 * @param string|null $locale
 * @return \Illuminate\Contracts\Translation\Translator|string|array|null
 */
function trans($key = null, $replace = [], $locale = null)
{
    $translator = app('translator');
    if (is_null($key)) {
        return $translator;
    }

    return $translator->get($key, $replace, $locale);
}

/**
 * Translates the given message based on a count
 *
 * @param string $key
 * @param \Countable|int|array $number
 * @param array $replace
 * @param string|null $locale
 * @return string
 */
function trans_choice($key, $number, array $replace = [], $locale = null)
{
    return app('translator')->choice($key, $number, $replace, $locale);
}

/**
 * Translate the given message
 *
 * @param string|null $key
 * @param array $replace
 * @param string|null $locale
 * @return string|array|null
 */
function __($key = null, $replace = [], $locale = null)
{
    if (is_null($key)) {
        return $key;
    }

    return trans($key, $replace, $locale);
}

/**
 * Get the path to the core of Attla framework
 *
 * @param string $path
 * @return string
 */
function core_path($path = '')
{
    return app()->normalizePath(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . $path);
}

/**
 * Get the path to the base of the install
 *
 * @param string $path
 * @return string
 */
function base_path($path = '')
{
    return app()->basePath($path);
}

/**
 * Get the path to the application folder
 *
 * @param string $path
 * @return string
 */
function app_path($path = '')
{
    return app()->path($path);
}

/**
 * Get the path to the resources folder
 *
 * @param string $path
 * @return string
 */
function resource_path($path = '')
{
    return app()->resourcePath($path);
}

/**
 * Get the path to the storage folder
 *
 * @param string $path
 * @return string
 */
function storage_path($path = '')
{
    return app()->storagePath($path);
}

/**
 * Get the path to the database folder
 *
 * @param string $path
 * @return string
 */
function database_path($path = '')
{
    return app()->databasePath($path);
}

/**
 * Get the configuration path
 *
 * @param string $path
 * @return string
 */
function config_path($path = '')
{
    return app()->configPath($path);
}

/**
 * Get the absolute path by pointing to assets folder
 *
 * @param string $file
 * @return string
 */
function asset($file, $defaultFile = false)
{
    $filePath = assetPath($file);

    if ($defaultFile && !is_url($defaultFile)) {
        $defaultFilePath = assetPath($defaultFile);
    }

    $version = config('version') ?? app()->version();

    if (!$version) {
        $version = '0.0.0';
    }

    $version = '?v=' . (config('debug') ? time() : $version);

    if (!is_file(base_path($filePath)) && $defaultFile) {
        if (!is_url($defaultFile)) {
            $defaultFilePath = assetPath($defaultFile);

            if (is_file(base_path($defaultFilePath))) {
                return url($defaultFilePath . $version);
            }

            return false;
        }
        return $defaultFile;
    }

    return url($filePath . $version);
}

/**
 * Format path of asset
 *
 * @param string $file
 * @return string
 */
function assetPath($file)
{
    return 'resources/'
            . (is_dir(resource_path('assets')) && strpos($file, 'assets') === false ? 'assets/' : '')
            . trim($file, '/');
}

/**
 * Create a new cookie instance
 *
 * @param string|null $name
 * @param string|null $value
 * @param int $minutes
 * @param string|null $path
 * @param string|null $domain
 * @param bool|null $secure
 * @param bool $httpOnly
 * @param bool $raw
 * @param string|null $sameSite
 * @return \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
 */
function cookie(
    $name = null,
    $value = null,
    $minutes = 0,
    $path = null,
    $domain = null,
    $secure = null,
    $httpOnly = true,
    $raw = false,
    $sameSite = null
) {
    $cookie = app('cookier');

    if (is_null($name)) {
        return $cookie;
    }

    return $cookie->set(
        $name,
        $value,
        $minutes,
        $path,
        $domain,
        $secure,
        $httpOnly,
        $raw,
        $sameSite
    );
}

/**
 * Arrange for a flash message
 *
 * @param string $message
 * @param string|bool $type
 * @param bool $dismissible
 * @return void
 */
function flash($message, $type = 'primary', $dismissible = false)
{
    $flashName = '__flash';
    $session = session();

    if (is_bool($type)) {
        $dismissible = $type;
        $type = 'primary';
    }

    $flash = $session->get($flashName, collect());
    $flash->push((object) [
        'message' => $message,
        'type' => $type,
        'dismissible' => $dismissible,
    ]);

    $session->put($flashName, $flash);
}

/**
 * Create a new Carbon instance for the current time
 *
 * @param \DateTimeZone|string|null $tz
 * @return \Illuminate\Support\Carbon
 */
function now($tz = null)
{
    return Date::now($tz);
}

/**
 * Create a new Carbon instance for the current date
 *
 * @param \DateTimeZone|string|null $tz
 * @return \Illuminate\Support\Carbon
 */
function today($tz = null)
{
    return Date::today($tz);
}

/**
 * Create a new Carbon instance for tomorrow
 *
 * @param \DateTimeZone|string|null $tz
 * @return \Illuminate\Support\Carbon
 */
function tomorrow($tz = null)
{
    return Date::tomorrow($tz);
}

/**
 * Create a new Carbon instance for yesterday
 *
 * @param \DateTimeZone|string|null $tz
 * @return \Illuminate\Support\Carbon
 */
function yesterday($tz = null)
{
    return Date::yesterday($tz);
}




/**
 * Check if it is browser
 *
 * @param string $key
 * @return bool
 */
function is_browser($key = '')
{
    return app(UserAgent::class)->isBrowser($key);
}

/**
 * Get browser name
 *
 * @return string
 */
function browser()
{
    return app(UserAgent::class)->browser();
}

/**
 * Get the browser version
 *
 * @return string
 */
function browser_version()
{
    return app(UserAgent::class)->version();
}

/**
 * Check if it is mobile
 *
 * @param string $key
 * @return bool
 */
function is_mobile($key = '')
{
    return app(UserAgent::class)->isMobile($key);
}

/**
 * Get the mobile device
 *
 * @return string
 */
function mobile()
{
    return app(UserAgent::class)->mobile();
}

/**
 * Check if it is robot
 *
 * @param string $class
 * @return bool
 */
function is_robot($key = '')
{
    return app(UserAgent::class)->isRobot($key);
}

/**
 * Get the robot name
 *
 * @return string
 */
function robot()
{
    return app(UserAgent::class)->robot();
}

/**
 * Get the platform
 *
 * @return string
 */
function platform()
{
    return app(UserAgent::class)->platform();
}

/**
 * Get the IP
 *
 * @return string
 */
function ip()
{
    return app(UserAgent::class)->ip();
}

/**
 * Is this a referral from another site?
 *
 * @return bool
 */
function is_referral()
{
    return app(UserAgent::class)->isReferral();
}

/**
 * Get the referrer
 *
 * @return string
 */
function referrer()
{
    return app(UserAgent::class)->referrer();
}






/**
 * Randomize positions of an array
 *
 * @param array $array
 * @return array
 */
function array_random($array = [])
{
    if (!is_array($array)) {
        $array = (array) $array;
    }
    if (!$array) {
        return [];
    }
    $keys = array_keys($array);
    shuffle($keys);
    $return = [];
    foreach ($keys as $index) {
        $return[$index] = $array[$index];
    }
    return $return;
}

/**
 * Get a random value from an array
 *
 * @param array $array
 * @return mixed
 */
function array_random_value($array)
{
    return is_array_assoc($array) ? $array[array_rand($array)] : $array[mt_rand(0, count($array) - 1)];
}

/**
 * Check if array is associative
 *
 * @param array $array
 * @return bool
 */
function is_array_assoc(array $array)
{
    if (array() === $array) {
        return false;
    }
    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * Checks if a value is empty
 *
 * @param string $val
 * @return bool
 */
function is_empty($val)
{
    return strlen(trim(preg_replace('/\xc2\xa0/', ' ', $val))) == 0 ? true : false;
}

/**
 * Check if it is a valid email
 *
 * @param string $email
 * @return bool
 */
function is_email($email)
{
    return preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $email) ? true : false;
}

/**
 * Check if it is a valid username
 *
 * @param string $username
 * @return bool
 */
function is_username($username, $max = 20)
{
    return preg_match('/^[a-z\d_.-]{3,' . $max . '}$/i', $username) ? true : false;
}

/**
 * Check if it is a valid URL
 *
 * @param string $url
 * @return bool
 */
function is_url($url)
{
    return preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*'
        . '(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url) ? true : false;
}

/**
 * Check if it is a valid full name
 *
 * @param string $name
 * @return bool
 */
function is_name($name)
{
    return (preg_match("/^[\\p{L}'-.]{3,}(?: [\\p{L}'-.]+)+$/ui", $name)) ? true : false;
}

/**
 * Serialize data if needed
 *
 * @param string $data
 * @return string
 */
function maybe_serialize($data)
{
    return (is_array($data) || is_object($data)) && !is_serialized($data) ? @serialize($data) : $data;
}

/**
 * Unserialize value only if it was serialized
 *
 * @param string $data
 * @return mixed
 */
function maybe_unserialize($data)
{
    return is_serialized($data) ? @unserialize($data) : $data;
}

/**
 * Check value to find if it was serialized
 *
 * @param string $data
 * @return bool
 */
function is_serialized($data)
{
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    if (!preg_match('/^([adObis]):/', $data, $match)) {
        return false;
    }
    switch ($match[1]) {
        case 'a':
        case 'O':
        case 's':
            if (preg_match("/^{$match[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                return true;
            }
            break;
        case 'b':
        case 'i':
        case 'd':
            if (preg_match("/^{$match[1]}:[0-9.E-]+;\$/", $data)) {
                return true;
            }
            break;
    }
    return false;
}

/**
 * Check if it is a valid base64
 *
 * @param string $data
 * @return bool
 */
function is_base64($data)
{
    return base64_encode(base64_decode($data)) === $data;
}

/**
 * Check if it is a valid json
 *
 * @param string $data
 * @return bool
 */
function is_json($data)
{
    json_decode($data);
    return json_last_error() == JSON_ERROR_NONE;
}

/**
 * Check if it is a valid http_query
 *
 * @param string $data
 * @return bool
 */
function is_http_query($data)
{
    return preg_match('/^([+\w\.\/%_-]+=([+\w\.\/%_-]*)?(&[+\w\.\/%_-]+(=[+\w\.\/%_-]*)?)*)?$/', $data) ? true : false;
}
