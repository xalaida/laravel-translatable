<?php

namespace Nevadskiy\Translatable\Strategies\ExtraTable\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use function is_array;

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
}
