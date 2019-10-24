<?php

namespace Nevadskiy\Translatable;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property string locale
 * @property string translatable_attribute
 * @property string translatable_value
 * @property int translatable_id
 * @property string translatable_type
 * @property Collection translations
 * @property HasTranslations translatable
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
     * Translatable morph relation.
     *
     * @return MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
