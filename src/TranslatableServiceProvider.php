<?php

namespace Nevadskiy\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->bootTranslator();
        $this->bootCommands();
        $this->publishMigrations();
    }

    /**
     * Boot the translator instance.
     */
    protected function bootTranslator(): void
    {
        Translator::resolveLocaleUsing(function () {
            return app()->getLocale();
        });

        Translator::resolveFallbackLocaleUsing(function () {
            return app()->getFallbackLocale();
        });

        Translator::resolveEventDispatcherUsing(function () {
            return resolve('events');
        });
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
     * Publish any package migrations.
     */
    private function publishMigrations(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'translatable-migrations');
    }
}
