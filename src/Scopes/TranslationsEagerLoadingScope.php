<?php

namespace Nevadskiy\Translatable\Scopes;

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
        $locales = $translatable->translator()->getStrategy()->getLocalesForEagerLoading();

        if (! $locales) {
            return;
        }

        $query->with(['translations' => function (Relation $query) use ($locales) {
            $query->forLocale($locales);
        }]);
    }
}
