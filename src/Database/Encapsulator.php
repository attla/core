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

            self::$capsule->addConnection(self::getDriverConfig(config('db.driver', 'mysql')));

            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
            self::$schema = self::$capsule->schema();

            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the driver connection config
     *
     * @return array
     */
    public static function getDriverConfig($driver)
    {
        $config = config();

        if ($driver == 'sqlite') {
            return [
                'driver' => 'sqlite',
                'url' => $config->get('db.url'),
                'database' => $config->get('db.database', database_path('database.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => $config->get('db.foreign_keys', true),
            ];
        }

        $connection = [
            'url' => $config->get('db.url'),
            'host' => $config->get('db.host', '127.0.0.1'),
            'database' => $config->get('db.database', 'attla'),
            'username' => $config->get('db.username', 'attla'),
            'password' => $config->get('db.password', ''),
            'prefix' => $config->get('db.prefix', ''),
            'prefix_indexes' => true,
        ];

        if ($driver == 'mysql') {
            $connection = array_merge($connection, [
                'driver' => 'mysql',
                'port' => $config->get('db.port', '3306'),
                'unix_socket' => $config->get('db.socket', ''),
                'charset' => $config->get('db.charset', 'utf8mb4'),
                'collation' => $config->get('db.collation', 'utf8mb4_unicode_ci'),
                'strict' => true,
                'engine' => null,
            ]);
        }

        if ($driver == 'pgsql') {
            $connection = array_merge($connection, [
                'driver' => 'pgsql',
                'port' => $config->get('db.port', '5432'),
                'charset' => 'utf8',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]);
        }

        if ($driver == 'sqlsrv') {
            $connection = array_merge($connection, [
                'driver' => 'sqlsrv',
                'host' => $config->get('db.host', 'localhost'),
                'port' => $config->get('db.port', '1433'),
                'charset' => 'utf8',
            ]);
        }

        return $connection;
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
