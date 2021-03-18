<?php

namespace Nevadskiy\Translatable\Tests\Integration;

use Illuminate\Foundation\Application;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translatable;

class IgnoreMigrationsTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app[Translatable::class]->ignoreMigrations();

        parent::getEnvironmentSetUp($app);
    }

    /** @test */
    public function it_can_ignore_migrations(): void
    {
        self::assertEmpty($this->app['migrator']->paths());
    }
}
