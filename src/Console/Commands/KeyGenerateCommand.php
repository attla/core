<?php

namespace Attla\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Attla\Encrypter;

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
     * Execute the console command
     *
     * @return void
     */
    public function handle()
    {
        $key = Encrypter::generateKey();

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
     * @param string $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key)
    {
        $currentKey = $this->laravel['config']['encrypt.secret'];

        if (
            strlen($currentKey) !== 0
            && !$this->confirmToProceed('Confirm to proceed', function () {
                return true;
            })
        ) {
            return false;
        }

        $this->writeEnvironmentFileWith([
            'encrypt' => [
                'mode' => $this->laravel['config']['encrypt.mode'] ?? 'query',
                'secret' => $key,
            ],
        ]);

        return true;
    }

    /**
     * Write a environment file with the given attributes
     *
     * @param array $environments
     * @return void
     */
    protected function writeEnvironmentFileWith($newEnvironments)
    {
        $oldEnvironments = $this->laravel->getEnvironment();
        $environment = array_merge($oldEnvironments, $newEnvironments);
        file_put_contents(
            $this->laravel->environmentFilePath(),
            str_replace('\\/', '/', json_encode($environment, JSON_PRETTY_PRINT))
        );
    }
}
