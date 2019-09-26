<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    /**
     * Determine if the attribute is translatable.
     *
     * @param string $attribute
     * @return bool
     */
    public function isTranslatable(string $attribute): bool
    {
        return in_array($attribute, $this->translatable, true);
    }

    /**
     * Morph many translations relation.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get model translated attribute if translation is available.
     *
     * @param $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        if ($this->shouldBeTranslated($attribute)) {
        // TODO: Save original attributes somewhere...
            $this->attributes[$attribute] = $this->getTranslator()->get($attribute, $this);
        }

        return parent::getAttribute($attribute);
    }

    /**
     * Determine if attribute should be translated
     *
     * @param string $attribute
     * @return bool
     */
    public function shouldBeTranslated(string $attribute): bool
    {
        return $this->isTranslatable($attribute)
            && ! $this->getTranslator()->isDefaultLocale();
    }

    /**
     * Get model translator
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return app(Translator::class);
    }
}
