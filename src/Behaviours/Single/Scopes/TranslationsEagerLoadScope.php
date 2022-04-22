<?php

namespace Nevadskiy\Translatable\Behaviours\Single\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;
use Nevadskiy\Translatable\Behaviours\Single\HasTranslations;

class TranslationsEagerLoadScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Model|HasTranslations $translatable
     */
    public function apply(Builder $query, Model $translatable): void
    {
        if ($translatable->translator()->isFallbackLocale()) {
            return;
        }

        if (! $this->shouldEagerLoadTranslations($translatable)) {
            return;
        }

        // TODO Reduce the amount of fields using partition selects.
        // TODO: load only translatable attributes here.
        $query->with(['translations' => static function (Relation $query) use ($translatable) {
            $query->forLocale($translatable->translator()->getLocale());
        }]);
    }

    /**
     * Determine whether the translations should be loaded.
     *
     * @param Model|HasTranslations $translatable
     */
    private function shouldEagerLoadTranslations(Model $translatable): bool
    {
        foreach ($translatable->getTranslatable() as $attribute) {
            if ($translatable->shouldProxyAttributeToTranslator($attribute)) {
                return true;
            }
        }

        return false;
    }
}
