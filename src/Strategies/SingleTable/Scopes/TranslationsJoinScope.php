<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;

class TranslationsJoinScope implements Scope
{
    /**
     * The current joins amount.
     */
    private $joins = 0;

    /**
     * @inheritDoc
     */
    public function apply(Builder $query, Model $translatable): void
    {
        $translationModel = $this->resolveTranslationModel();

        if (! $query->getQuery()->columns) {
            $query->addSelect($translatable->qualifyColumn('*'));
        }

        foreach ($translatable->getTranslatable() as $attribute) {
            $translationsJoinTable = $this->getJoinedTranslationsTable();

            $query->addSelect(new Expression("{$translationsJoinTable}.value as {$attribute}"))
                ->leftJoin("{$translationModel->getTable()} as {$translationsJoinTable}", function (JoinClause $join) use ($translatable, $translationsJoinTable, $attribute) {
                    $join->on("{$translationsJoinTable}.translatable_id", $translatable->qualifyColumn('id'))
                        ->where("{$translationsJoinTable}.translatable_type", $translatable->getMorphClass())
                        ->where("{$translationsJoinTable}.translatable_attribute", $attribute)
                        ->where("{$translationsJoinTable}.locale", value(function () use ($translatable) {
                            return $translatable->translator()->getLocale();
                        }));
                });
        }
    }

    /**
     * Resolve the translation model.
     */
    protected function resolveTranslationModel(): Translation
    {
        return new Translation();
    }

    /**
     * Get a table name of the joined translations.
     */
    protected function getJoinedTranslationsTable(): string
    {
        return 'translations_reserved_' . ++$this->joins;
    }
}
