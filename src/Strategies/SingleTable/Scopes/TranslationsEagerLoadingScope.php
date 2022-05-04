<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;

class TranslationsEagerLoadingScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $query, Model $translatable): void
    {
        $query->with(['translations' => function (Relation $query) use ($translatable) {
            $query->forLocale($translatable->translator()->getLocale())
                ->when($this->shouldLoadFallbackTranslations($translatable), function (Builder $query) use ($translatable) {
                    $query->orWhere('locale', $translatable->translator()->getFallbackLocale());
                });
        }]);
    }

    /**
     * @param Model|HasTranslations $translatable
     * @return bool
     */
    public function shouldLoadFallbackTranslations(Model $translatable): bool
    {
        // TODO: if fallback disabled - do not eager load!

        return ! $translatable->translator()->isFallbackLocale();
    }
}
