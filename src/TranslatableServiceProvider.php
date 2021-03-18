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
            Listeners\UpdateTranslatorLocale::class,
        ],
    ];

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerPackage();
        $this->registerModelTranslator();
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->bootCommands();
        $this->bootEvents();
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
     * Boot any package commands.
     */
    private function bootCommands(): void
    {
        $this->commands([
            Console\RemoveUnusedTranslationsCommand::class,
        ]);
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
