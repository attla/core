<?php

namespace Attla\Bootstrap;

use Attla\Config;
use Carbon\Carbon;
use Attla\Application;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;
use Carbon\CarbonImmutable;
use Attla\Database\Encapsulator;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon as IlluminateCarbon;

class LoadConfiguration
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaultConfig = [
        'app' => [
            'locale' => 'en_US',
            'fallback_locale' => 'en',
            'faker_locale' => 'en_US',
        ],
        'auth' => [
            'guard' => 'web'
        ],
        'database' => [
            'migrations' => 'migrations',
        ],
        'view' => [
            'paths' => [],
            'compiled' => '',
        ],
        'cache' => [
            'stores' => [
                'queries' => [
                    'driver' => 'array',
                ],
            ],
        ],
        'session' => [
            'driver' => 'cookie',
            'lifetime' => 1440 * 365,
            'expire_on_close' => false,
            'encrypt' => false,
            'files' => '',
            'connection' => null,
            'table' => 'sessions',
            'store' => null,
            'lottery' => [2, 100],
            'cookie' => '__session',
            'path' => '/',
            'domain' => false,
            'secure' => false,
            'http_only' => true,
            'same_site' => 'lax',
        ],
    ];

    /**
     * Bootstrap the given application
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->instance('config', $config = new Config($this->appConfig($app)));
        $this->loadPackagesConfigFiles($app, $config);

        date_default_timezone_set($config->get('timezone', 'UTC'));
        mb_internal_encoding('UTF-8');

        $this->resolveLocale($config);
        $this->resolveDatabase($config);
    }

    /**
     * Load the configuration items from all of the files
     *
     * @param \Attla\Application $app
     * @param \Attla\Config $config
     * @return void
     */
    protected function loadPackagesConfigFiles(Application $app, Config $config)
    {
        $files = $this->getConfigurationFiles($app);

        foreach ($files as $key => $path) {
            $config->set($key, require $path);
        }
    }

    /**
     * Get all of the packages configuration files
     *
     * @param \Attla\Application $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = [];
        $configPath = realpath($app->configPath());

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);
            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);
        return $files;
    }

    /**
     * Get the configuration file nesting path
     *
     * @param \SplFileInfo $file
     * @param string $configPath
     * @return string
     */
    protected function getNestedDirectory(\SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }

    /**
     * Resolve locale from application
     *
     * @param \Attla\Config $config
     * @return void
     */
    protected function resolveLocale(Config $config)
    {
        $defaultLocale = 'en_US';
        $locale = $config->get('locale', $defaultLocale);
        $config->set('app.locale', $locale);
        $config->set('app.faker_locale', $config->get('faker_locale', $defaultLocale));
        $this->setCarbonLocale($locale);
    }

    /**
     * Set carbon locale
     *
     * @param string $locale
     * @return void
     */
    protected function setCarbonLocale($locale)
    {
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);
        CarbonPeriod::setLocale($locale);
        CarbonInterval::setLocale($locale);

        if (class_exists(IlluminateCarbon::class)) {
            IlluminateCarbon::setLocale($locale);
        }

        if (class_exists(Date::class)) {
            try {
                $root = Date::getFacadeRoot();
                $root->setLocale($locale);
            } catch (\Throwable $e) {
                // Non Carbon class in use in Date facade
            }
        }
    }

    /**
     * Resolve database connection configuration
     *
     * @param \Attla\Config $config
     * @return void
     */
    protected function resolveDatabase(Config $config)
    {
        $config->set('database.default', $driver = $config->get('db.driver', 'mysql'));
        $config->set('database.connections.' . $driver, Encapsulator::getDriverConfig($driver));
    }

    /**
     * Return application configuration
     *
     * @param \Attla\Application $app
     * @return array
     */
    protected function appConfig(Application $app)
    {
        $config = $this->defaultConfig;

        $config['view']['paths'] = [
            $app->resourcePath('views')
        ];
        $config['view']['compiled'] = $app->storagePath('views');
        $config['session']['files'] = $app->storagePath('sessions');

        return array_merge($config, $app->getEnvironment());
    }
}
