<?php

namespace Nevadskiy\Translatable\Strategies\Entity\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string|null locale
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
     * Scope translations by the given locale.
     */
    public function scopeForLocale(Builder $query, ?string $locale): Builder
    {
        return $query->where('locale', $locale);
    }
}
