<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;

class TranslationsEagerLoadScope implements Scope
{
    /**
     * @param HasTranslations $translatable
     */
    public function apply(Builder $query, Model $translatable): void
    {
        $translationModel = new Translation();

        $joins = 0;

        foreach ($translatable->getTranslatable() as $attribute) {
            $translationsJoinTable = 'translations_reserved_'.$joins++;

            $query->leftJoin("translations as {$translationsJoinTable}", function (JoinClause $join) use ($translatable, $translationModel, $attribute) {
                $join->on($translationModel->qualifyColumn('translatable_id'), $translatable->qualifyColumn('id'))
                    ->where($translationModel->qualifyColumn('translatable_type'), $translatable->getMorphClass())
                    ->where($translationModel->qualifyColumn('translatable_attribute'), $attribute)
                    ->where($translationModel->qualifyColumn('locale'), $attribute);
            })->addSelect($translationModel->qualifyColumn('value'));
        }
    }

//    /**
//     * @inheritDoc
//     */
//    public function apply(Builder $query, Model $translatable): void
//    {
//        $query->with(['translations' => function (Relation $query) use ($translatable) {
//            $query->forLocale($translatable->translator()->getLocale());
//        }]);
//    }
}
