<?php

namespace Magdonia\LaravelFactories\Tests;

use Magdonia\LaravelFactories\LaravelFactoriesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelFactoriesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        config()->set('database.default', 'testing');
        config()->set('laravel-factories.default-request-directory', 'Magdonia\\LaravelFactories\\Tests\\Http\\Requests\\');
        config()->set('laravel-factories.default-request-factories-directory', 'Magdonia\\LaravelFactories\\Tests\\Factories\\Requests\\');
        config()->set('laravel-factories.default-resource-directory', 'Magdonia\\LaravelFactories\\Tests\\Http\\Resources\\');
        config()->set('laravel-factories.default-resource-factories-directory', 'Magdonia\\LaravelFactories\\Tests\\Factories\\Resources\\');
    }

    public function getEnvironmentSetUp($app)
    {
        $migration = include __DIR__ . '/Database/Migrations/CreateUserTableMigration.php';
        $migration->up();

        $migration = include __DIR__ . '/Database/Migrations/CreatePostTableMigration.php';
        $migration->up();
    }
}
