<?php

namespace MichaelRavedoni\LaravelValueObjects\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use MichaelRavedoni\LaravelValueObjects\LaravelValueObjectsServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelValueObjectsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite memory
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        // Create a simple 'users' table for testing purposes
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('gender')->nullable(); // Add the gender column
            $table->timestamps();
        });
    }
}