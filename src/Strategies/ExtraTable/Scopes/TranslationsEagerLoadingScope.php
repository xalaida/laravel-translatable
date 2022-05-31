<?php

namespace Nevadskiy\Translatable\Strategies\ExtraTable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;

class TranslationsEagerLoadingScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $query, Model $translatable): void
    {
        $query->with(['translations' => function (Relation $query) use ($translatable) {
            $query->forLocale($this->getLocalesToLoad($translatable));
        }]);
    }

    /**
     * Get the locale list that should be eager loaded.
     */
    protected function getLocalesToLoad(Model $translatable): array
    {
        $locales = [$translatable->translator()->getLocale()];

        if ($this->shouldLoadFallbackTranslations($translatable)) {
            $locales[] = $translatable->translator()->getFallbackLocale();
        }

        return $locales;
    }

    /**
     * Determine whether the fallback translations should be eager loaded.
     */
    protected function shouldLoadFallbackTranslations(Model $translatable): bool
    {
        if (! $translatable->translator()->shouldFallback()) {
            return false;
        }

        return ! $translatable->translator()->isFallbackLocale();
    }
}
