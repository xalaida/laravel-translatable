<?php

namespace Nevadskiy\Translatable\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Nevadskiy\Translatable\Events\TranslationSavedEvent;
use Nevadskiy\Translatable\HasTranslations;
use Nevadskiy\Uuid\Uuid;

/**
 * @property string id
 * @property string translatable_type
 * @property string translatable_id
 * @property string translatable_attribute
 * @property Model|HasTranslations translatable
 * @property string value
 * @property string locale
 * @property bool is_preferred
 * @property Carbon updated_at
 * @property Carbon created_at
 */
class Translation extends Model
{
    use Uuid;

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('preferredFirst', static function (Builder $query) {
            $query->preferredFirst();
        });
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preferred' => 'bool',
    ];

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = [
        'translatable',
    ];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => TranslationSavedEvent::class,
    ];

    /**
     * Translatable morph relation.
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope translations by the given locale.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope translations by the given attribute.
     */
    public function scopeForAttribute(Builder $query, string $attribute): Builder
    {
        return $query->where('translatable_attribute', $attribute);
    }

    /**
     * Scope translations by the preferred translations first.
     */
    public function scopePreferredFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_preferred');
    }
}
