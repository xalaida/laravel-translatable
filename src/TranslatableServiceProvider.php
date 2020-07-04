<?php

namespace Nevadskiy\Translatable;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array
     */
    protected $listen = [
        LocaleUpdated::class => [
            Listeners\UpdateLocaleListener::class,
        ],
    ];

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerModelTranslator();
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishMigrations();
        $this->bootMigrations();
        $this->bootEvents();
        $this->bootCommands();
    }

    /**
     * Register the model translator.
     */
    private function registerModelTranslator(): void
    {
        $this->app->singleton(ModelTranslator::class, function () {
            return new ModelTranslator(
                $this->app['config']['app']['fallback_locale']
            );
        });
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

    /**
     * Boot any package migrations.
     */
    public function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Boot any package events.
     */
    private function bootEvents(): void
    {
        $dispatcher = $this->app[Dispatcher::class];

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }

    /**
     * Boot any package commands.
     */
    private function bootCommands(): void
    {
        $this->commands([
            Console\RemoveUnusedTranslationsCommand::class,
        ]);
    }
}
