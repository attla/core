<?php

namespace Attla\Console\Commands;

use Attla\Encrypter;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'key:generate
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Set the application key';

    /**
     * Config repository
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Execute the console command
     *
     * @return void
     */
    public function handle()
    {
        $key = Encrypter::generateKey();
        $this->config = $this->laravel['config'];

        if ($this->option('show')) {
            return $this->line('<comment>' . $key . '</comment>');
        }

        if (!$this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->info('Application key set successfully.');
    }

    /**
     * Set the application key in the environment file
     *
     * @param string $newKey
     * @return bool
     */
    protected function setKeyInEnvironmentFile($newKey)
    {
        $currentKey = $this->config->get('app.key', '');

        if (strlen($currentKey) !== 0 && !$this->confirmToProceed()) {
            return false;
        }

        $this->writeEnvironmentFileWith($newKey, $currentKey);

        return true;
    }

    /**
     * Write a new environment file with the given key
     *
     * @param string $newKey
     * @return void
     */
    protected function writeEnvironmentFileWith($newKey, $currentKey)
    {
        $envFilePath = $this->laravel->environmentFilePath();

        file_put_contents($envFilePath, preg_replace(
            $this->replacementPattern('APP_KEY', $currentKey),
            'APP_KEY=' . $newKey,
            file_get_contents($envFilePath)
        ));
    }

    /**
     * Get a regex pattern that will match env key with any value
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected function replacementPattern($name, $value)
    {
        $escaped = preg_quote('=' . $value, '/');
        return "/^{$name}{$escaped}/m";
    }
}
