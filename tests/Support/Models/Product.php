<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nevadskiy\Translatable\HasTranslations;
use Nevadskiy\Translatable\Models\EntityTranslation;
use Nevadskiy\Translatable\Strategies\AdditionalTableStrategy;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Uuid\Uuid;

/**
 * @property string id
 * @property string title
 * @property string description
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Product extends Model
{
    use Uuid;
    use HasTranslations;

    /**
     * The attributes that can be translatable.
     *
     * @var array
     */
    protected $translatable = [
        'title',
        'description',
    ];

    /**
     * Get the translation strategy.
     */
    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return new AdditionalTableStrategy($this, $this->getConnection());
    }

    /**
     * Get the entity translations' relation.
     */
    public function translations(): HasMany
    {
        /** @var EntityTranslation $instance */
        $instance = $this->newRelatedInstance(EntityTranslation::class)->setTable('product_translations');

        return $this->newHasMany(
            $instance->newQuery(), $this, $instance->getTable().'.'.$this->getForeignKey(), $this->getKeyName()
        );
    }
}
