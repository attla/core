<?php

namespace Attla\Bootstrap;

use Dotenv\Dotenv;
use Attla\Application;
use Illuminate\Support\Env;
use Dotenv\Exception\InvalidFileException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadEnvironmentVariables
{
    /**
     * Bootstrap the given application.
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->checkForSpecificEnvironmentFile($app);

        try {
            $this->createDotenv($app)->safeLoad();
        } catch (InvalidFileException $cause) {
            $this->writeErrorAndDie($cause, $app);
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists
     *
     * @param \Attla\Application $app
     * @return void
     */
    protected function checkForSpecificEnvironmentFile(Application $app)
    {
        if (
            $app->runningInConsole() &&
            ($input = new ArgvInput())->hasParameterOption('--env') &&
            $this->setEnvironmentFilePath($app, $app->environmentFile() . '.' . $input->getParameterOption('--env'))
        ) {
            return;
        }

        $environment = Env::get('APP_ENV');

        if (!$environment) {
            return;
        }

        $this->setEnvironmentFilePath(
            $app,
            $app->environmentFile() . '.' . $environment
        );
    }

    /**
     * Load a custom environment file
     *
     * @param \Attla\Application $app
     * @param string $file
     * @return bool
     */
    protected function setEnvironmentFilePath($app, $file)
    {
        if (is_file($app->environmentPath() . '/' . $file)) {
            $app->loadEnvironmentFrom($file);
            return true;
        }

        return false;
    }

    /**
     * Create a Dotenv instance
     *
     * @param \Attla\Application $app
     * @return \Dotenv\Dotenv
     */
    protected function createDotenv($app)
    {
        return Dotenv::create(
            Env::getRepository(),
            $app->environmentPath(),
            $app->environmentFile()
        );
    }

    /**
     * Write the error information to the screen and exit
     *
     * @param \Dotenv\Exception\InvalidFileException $cause
     * @param \Attla\Application $app
     * @return void
     */
    protected function writeErrorAndDie(InvalidFileException $cause, Application $app)
    {
        $message = 'The environment file is invalid!';

        if ($app->runningInConsole()) {
            $output = (new ConsoleOutput())->getErrorOutput();

            $output->writeln($message);
            $output->writeln($cause->getMessage());
        } else {
            echo $message . "\n"
                . $cause->getMessage();
        }

        exit(1);
    }
}
