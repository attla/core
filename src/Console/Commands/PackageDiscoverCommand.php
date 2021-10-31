<?php

namespace Attla\Console\Commands;

use Illuminate\Console\Command;
use Attla\PackageDiscover;

class PackageDiscoverCommand extends Command
{
    /**
     * The console command signature
     *
     * @var string
     */
    protected $signature = 'package:discover';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * Execute the console command
     *
     * @param \Attla\PackageDiscover $discover
     * @return void
     */
    public function handle(PackageDiscover $discover)
    {
        foreach (array_keys($discover->getManifest()) as $package) {
            $this->line("Discovered Package: <info>{$package}</info>");
        }

        $this->info('Package manifest generated successfully.');
    }
}
