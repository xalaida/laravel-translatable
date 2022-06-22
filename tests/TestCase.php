<?php

namespace Nevadskiy\Translatable\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Application;
use Nevadskiy\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('en');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [TranslatableServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * TODO: remove this and refactor tests
     * Freeze the current time.
     */
    protected function freezeTime(Carbon $time = null): Carbon
    {
        $time = $time ?: Carbon::now();

        $time = Carbon::createFromTimestamp($time->getTimestamp());

        Carbon::setTestNow($time);

        return $time;
    }

    /**
     * Get a schema builder instance.
     */
    protected function schema(): Builder
    {
        return Model::getConnectionResolver()
            ->connection()
            ->getSchemaBuilder();
    }
}
