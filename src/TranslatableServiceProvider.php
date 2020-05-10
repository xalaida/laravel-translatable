<?php

namespace Nevadskiy\Translatable;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        LocaleUpdated::class => [
            Listeners\UpdateLocaleListener::class,
        ]
    ];

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerModelTranslator();
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bootMigrations();
        $this->bootEvents();
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
     * Bootstrap package migrations.
     *
     * @return void
     */
    private function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    /**
     * Boot any application events.
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
}
