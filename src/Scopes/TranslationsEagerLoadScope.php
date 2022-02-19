<?php

namespace Nevadskiy\Translatable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;
use Nevadskiy\Translatable\HasTranslations;

class TranslationsEagerLoadScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Model|HasTranslations $translatable
     */
    public function apply(Builder $query, Model $translatable): void
    {
        if ($translatable->translation()->isFallbackLocale()) {
            return;
        }

        if (! $this->shouldLoadTranslations($translatable)) {
            return;
        }


        // TODO Reduce the amount of fields using partition selects.
        // TODO: load only translatable attributes here.
        $query->with(['translations' => static function (Relation $query) use ($translatable) {
            $query->forLocale($translatable->translation()->getLocale());
        }]);
    }

    /**
     * Determine whether the translations should be loaded.
     *
     * @param Model|HasTranslations $translatable
     */
    private function shouldLoadTranslations(Model $translatable): bool
    {
        foreach ($translatable->getTranslatable() as $attribute) {
            if ($translatable->getterAsTranslation($attribute)) {
                return true;
            }
        }

        return false;
    }
}
