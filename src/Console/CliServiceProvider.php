<?php

namespace Attla\Console;

use Illuminate\Database\Console\DbCommand;
use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Database\Console\WipeCommand;
use Attla\Console\Commands\{
    KeyGenerateCommand,
    ModelMakeCommand,
    PackageDiscoverCommand,
    RouteListCommand,
    ServeCommand,
    VendorPublishCommand,
    ViewCacheCommand,
    ViewClearCommand,
    ControllerMakeCommand,
    MiddlewareMakeCommand,
    MailMakeCommand,
};
use Illuminate\Support\ServiceProvider;

class CliServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered
     *
     * @var array
     */
    protected $commands = [
        'Db' => DbCommand::class,
        'DbWipe' => 'command.db.wipe',
        'KeyGenerate' => 'command.key.generate',
        'PackageDiscover' => 'command.package.discover',
        'RouteList' => RouteListCommand::class,
        'Seed' => 'command.seed',
        'ViewCache' => 'command.view.cache',
        'ViewClear' => 'command.view.clear',
    ];

    /**
     * The dev commands to be registered
     *
     * @var array
     */
    protected $devCommands = [
        'ControllerMake' => 'command.controller.make',
        'FactoryMake' => 'command.factory.make',
        'MiddlewareMake' => 'command.middleware.make',
        'MailMake' => MailMakeCommand::class,
        'ModelMake' => 'command.model.make',
        'SeederMake' => 'command.seeder.make',
        'Serve' => 'command.serve',
        'VendorPublish' => 'command.vendor.publish',
    ];

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands(array_merge(
            $this->commands,
            $this->devCommands
        ));
    }

    /**
     * Register the given commands
     *
     * @param array $commands
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            $this->{"register{$command}Command"}();
        }

        $this->commands(array_values($commands));
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerControllerMakeCommand()
    {
        $this->app->singleton('command.controller.make', function ($app) {
            return new ControllerMakeCommand($app['files']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerDbCommand()
    {
        $this->app->singleton(DbCommand::class);
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerDbWipeCommand()
    {
        $this->app->singleton('command.db.wipe', function () {
            return new WipeCommand();
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerFactoryMakeCommand()
    {
        $this->app->singleton('command.factory.make', function ($app) {
            return new FactoryMakeCommand($app['files']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerKeyGenerateCommand()
    {
        $this->app->singleton('command.key.generate', function () {
            return new KeyGenerateCommand();
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerMiddlewareMakeCommand()
    {
        $this->app->singleton('command.middleware.make', function ($app) {
            return new MiddlewareMakeCommand($app['files']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerModelMakeCommand()
    {
        $this->app->singleton('command.model.make', function ($app) {
            return new ModelMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMailMakeCommand()
    {
        $this->app->singleton(MailMakeCommand::class, function ($app) {
            return new MailMakeCommand($app['files']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerPackageDiscoverCommand()
    {
        $this->app->singleton('command.package.discover', function () {
            return new PackageDiscoverCommand();
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerRouteListCommand()
    {
        $this->app->singleton(RouteListCommand::class, function ($app) {
            return new RouteListCommand($app['router']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerSeederMakeCommand()
    {
        $this->app->singleton('command.seeder.make', function ($app) {
            return new SeederMakeCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerSeedCommand()
    {
        $this->app->singleton('command.seed', function ($app) {
            return new SeedCommand($app['db']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerServeCommand()
    {
        $this->app->singleton('command.serve', function () {
            return new ServeCommand();
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerVendorPublishCommand()
    {
        $this->app->singleton('command.vendor.publish', function ($app) {
            return new VendorPublishCommand($app['files']);
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerViewCacheCommand()
    {
        $this->app->singleton('command.view.cache', function () {
            return new ViewCacheCommand();
        });
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerViewClearCommand()
    {
        $this->app->singleton('command.view.clear', function ($app) {
            return new ViewClearCommand($app['files']);
        });
    }

    /**
     * Get the services provided by the provider
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(
            array_values($this->commands),
            array_values($this->devCommands)
        );
    }
}
