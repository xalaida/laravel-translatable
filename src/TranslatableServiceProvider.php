<?php

namespace Nevadskiy\Translatable;

use Illuminate\Support\ServiceProvider;
use Nevadskiy\Translatable\Engine\GoogleTranslateEngine;
use Nevadskiy\Translatable\Engine\TranslatorEngine;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Translator::class);

        $this->app->singleton(TranslatorEngine::class, function () {
            return new GoogleTranslateEngine();
        });

        $this->app->singleton(AutoTranslator::class, function () {
            return new AutoTranslator($this->app[TranslatorEngine::class]);
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bootMigrations();
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
}
