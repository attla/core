<?php

namespace Attla\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Encapsulator
{
    /** @var \Illuminate\Database\Capsule\Manager $capsule */
    protected static $capsule;

    /** @var \Illuminate\Database\Schema\Builder $capsule */
    protected static $schema;

    /** @var \Attla\Database\Encapsulator $instance */
    protected static $instance;

    private function __construct()
    {
    }

    /**
     * Returns the instance of the encapsulator class
     *
     * @return \Attla\Database\Encapsulator
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$capsule = new Capsule();

            $config = config();
            $connection = $config->get('database.connections.' . $config->get('database.default', 'mysql'));
            self::$capsule->addConnection($connection);

            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
            self::$schema = self::$capsule->schema();

            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the capsule manager
     *
     * @return \Illuminate\Database\Capsule\Manager
     */
    public function getCapsule()
    {
        return self::$capsule;
    }

    /**
     * Get the schema builder
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchema()
    {
        return self::$schema;
    }
}
