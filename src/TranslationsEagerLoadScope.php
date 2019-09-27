<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

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
        if (! $translatable->getTranslator()->isDefaultLocale()) {
            $query->with('translations');
        }
    }
}
