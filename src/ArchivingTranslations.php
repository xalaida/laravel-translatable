<?php

namespace Nevadskiy\Translatable;

use Nevadskiy\Translatable\Models\Translation;

/**
 * @mixin HasTranslations
 */
trait ArchivingTranslations
{
    /**
     * Archive the given translation.
     */
    public function archiveTranslation(string $attribute, string $value, ?string $locale = null): Translation
    {
        $this->assertTranslatableAttribute($attribute);

        if (count(func_get_args()) < 3) {
            $locale = static::translation()->getLocale();
        }

        return static::translation()->add(
            $this, $attribute, $this->withAttributeMutators($attribute, $value), $locale, true
        );
    }
}
