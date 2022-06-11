<?php

namespace Nevadskiy\Translatable\Strategies\ExtraTable\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;
use function is_array;
use function get_class;

/**
 * @property int id
 * @property string locale
 * @property Carbon updated_at
 * @property Carbon created_at
 */
class Translation extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The related translatable model instance.
     *
     * @var Model
     */
    protected $related;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        if (! $this->table) {
            throw new RuntimeException('Table is not defined for Translation model.');
        }

        return $this->table;
    }

    /**
     * Scope translations by the given locale.
     *
     * @param string|array $locale
     */
    protected function scopeForLocale(Builder $query, $locale): Builder
    {
        if (is_array($locale)) {
            return $query->whereIn('locale', $locale);
        }

        return $query->where('locale', $locale);
    }

    /**
     * Set the related model instance.
     */
    public function setRelated(Model $related): void
    {
        $this->related = $related;
    }

    /**
     * Get a relation to translatable model.
     */
    public function translatable(): BelongsTo
    {
        if (! $this->related) {
            throw new RuntimeException('Cannot resolve "translatable" relation. Related model is not specified');
        }

        return $this->belongsTo(get_class($this->related), $this->related->getEntityTranslationForeignKey());
    }
}
