<?php

namespace Nevadskiy\Translatable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Scope;
use Nevadskiy\Translatable\HasTranslations;

class TranslationsEagerLoadScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $query
     * @param Model|HasTranslations $translatable
     * @return void
     */
    public function apply(Builder $query, Model $translatable): void
    {
        if (! $translatable::getTranslator()->isDefaultLocale()) {
            $query->with(['translations' => function (MorphMany $query) use ($translatable) {
                $query->locale($translatable::getTranslator()->getLocale());
            }]);
        }
    }
}
