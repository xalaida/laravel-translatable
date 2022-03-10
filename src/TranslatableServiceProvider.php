<?php

namespace Nevadskiy\Translatable;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerPackage();
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->bootCommands();
        $this->bootMigrations();
        $this->publishMigrations();
    }

    /**
     * Register the package configurator.
     */
    public function registerPackage(): void
    {
        $this->app->singleton(Translatable::class);
    }

    /**
     * Boot any package commands.
     */
    private function bootCommands(): void
    {
        $this->commands([
            //
        ]);
    }

    /**
     * Boot any package migrations.
     */
    public function bootMigrations(): void
    {
        if ($this->app[Translatable::class]->shouldBootMigrations()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Publish any package migrations.
     */
    private function publishMigrations(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'translatable');
    }
}
