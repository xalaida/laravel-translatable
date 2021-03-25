<?php

namespace Nevadskiy\Translatable;

class Translatable
{
    /**
     * Indicates if the package migrations will be booted.
     *
     * @var bool
     */
    protected $bootMigrations = true;

    /**
     * Indicates if translations should be automatically loaded.
     *
     * @var bool
     */
    protected $autoLoadTranslations = true;

    /**
     * Indicates if translations should be automatically saved.
     *
     * @var bool
     */
    protected $autoSaveTranslations = true;

    /**
     * Configure the package to not register its migrations.
     */
    public function ignoreMigrations(): self
    {
        $this->bootMigrations = false;

        return $this;
    }

    /**
     * Determine whether the package migrations should be booted.
     */
    public function shouldBootMigrations(): bool
    {
        return $this->bootMigrations;
    }

    /**
     * Determine whether the translations should be automatically loaded.
     *
     * @return bool
     */
    public function shouldAutoLoadTranslations(): bool
    {
        return $this->autoLoadTranslations;
    }

    /**
     * Disable translations auto loading.
     */
    public function disableAutoLoading(): void
    {
        $this->autoLoadTranslations = false;
    }

    /**
     * Determine whether the translations should be automatically saved.
     *
     * @return bool
     */
    public function shouldAutoSaveTranslations(): bool
    {
        return $this->autoSaveTranslations;
    }

    /**
     * Disable translations auto saving.
     */
    public function disableAutoSaving(): void
    {
        $this->autoSaveTranslations = false;
    }
}
