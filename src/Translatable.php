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
}
