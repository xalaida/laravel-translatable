<?php

namespace Nevadskiy\Translatable\Strategies\SingleTableExtended\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;

class TranslationsEagerLoadScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $query, Model $translatable): void
    {
        if (! $translatable->translator()->isFallbackLocale()) {
            $query->with(['translations' => function (Relation $query) use ($translatable) {
                $query->forLocale($translatable->translator()->getLocale());
            }]);
        }
    }
}
