<?php

namespace Nevadskiy\Translatable\Tests;

use Illuminate\Foundation\Application;
use Carbon\Carbon;
use Nevadskiy\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('en');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadMigrationsFrom(__DIR__.'/Support/Migrations');

        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [TranslatableServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
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
     * Freeze the current time.
     *
     * @param Carbon|null $time
     * @return Carbon
     */
    protected function freezeTime(Carbon $time = null): Carbon
    {
        $time = $time ?: Carbon::now();

        $time = Carbon::createFromTimestamp($time->getTimestamp());

        Carbon::setTestNow($time);

        return $time;
    }
}
