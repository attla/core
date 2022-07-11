<?php

namespace Attla\Providers\Console;

use Attla\Providers\Aggregate;
use Attla\Console\CliServiceProvider;

class ConsoleSupportServiceProvider extends Aggregate
{
    /**
     * The provider class names
     *
     * @var string[]
     */

    protected $providers = [
        CliServiceProvider::class,
        \Illuminate\Database\MigrationServiceProvider::class,
        ComposerServiceProvider::class,
    ];
}
