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
        if ($driver == 'sqlite') {
            return [
                'driver' => 'sqlite',
                'url' => config('db.url'),
                'database' => config('db.database', database_path('database.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => config('db.foreign_keys', true),
            ];
        }

        $connection = [
            'url' => config('db.url'),
            'host' => config('db.host', '127.0.0.1'),
            'database' => config('db.database', 'attla'),
            'username' => config('db.username', 'attla'),
            'password' => config('db.password', ''),
            'prefix' => config('db.prefix', ''),
            'prefix_indexes' => true,
        ];

        if ($driver == 'mysql') {
            $connection = array_merge($connection, [
                'driver' => 'mysql',
                'port' => config('db.port', '3306'),
                'unix_socket' => config('db.socket', ''),
                'charset' => config('db.charset', 'utf8mb4'),
                'collation' => config('db.collation', 'utf8mb4_unicode_ci'),
                'strict' => true,
                'engine' => null,
            ]);
        }

        if ($driver == 'pgsql') {
            $connection = array_merge($connection, [
                'driver' => 'pgsql',
                'port' => config('db.port', '5432'),
                'charset' => 'utf8',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]);
        }

        if ($driver == 'sqlsrv') {
            $connection = array_merge($connection, [
                'driver' => 'sqlsrv',
                'host' => config('db.host', 'localhost'),
                'port' => config('db.port', '1433'),
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
