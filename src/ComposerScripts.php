<?php

namespace Attla;

use Composer\Script\Event;

class ComposerScripts
{
    /**
     * Handle the post-install Composer event
     *
     * @param \Composer\Script\Event $event
     * @return void
     */
    public static function postInstall(Event $event)
    {
        static::requireComposer($event);
        static::clearCompiled();
    }

    /**
     * Handle the post-update Composer event
     *
     * @param \Composer\Script\Event $event
     * @return void
     */
    public static function postUpdate(Event $event)
    {
        static::requireComposer($event);
        static::clearCompiled();
    }

    /**
     * Handle the post-autoload-dump Composer event
     *
     * @param \Composer\Script\Event $event
     * @return void
     */
    public static function postAutoloadDump(Event $event)
    {
        static::requireComposer($event);
        static::clearCompiled();
    }

    /**
     * Require composer autoloader
     *
     * @return void
     */
    protected static function requireComposer(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
    }

    /**
     * Clear the cached Laravel bootstrapping files
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $app = new Application();

        foreach (
            [
                $app->packageServicesPath(),
            ] as $path
        ) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
