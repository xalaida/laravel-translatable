<?php

namespace Nevadskiy\Translatable\Behaviours\Entity\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Uuid\Uuid;

/**
 * @property string id
 * @property string|null locale
 * @property Carbon updated_at
 * @property Carbon created_at
 */
class Translation extends Model
{
    use Uuid;

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
