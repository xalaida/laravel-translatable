<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTable\Scopes;

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
            $query->forLocale($translatable->translator()->getLocale())
                ->when(! $translatable->translator()->isFallbackLocale(), function (Builder $query) use ($translatable) {
                    $query->orWhere('locale', $translatable->translator()->getFallbackLocale());
                });
        }]);
    }
}
