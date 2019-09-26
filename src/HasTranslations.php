<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    /**
     * Boot HasTranslations trait.
     */
    public static function bootHasTranslations(): void
    {
        static::retrieved(function (self $translatable) {
            $translatable->translateAttributes();
        });
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
     * Translate model attributes.
     */
    protected function translateAttributes(): void
    {
        if (! $this->getTranslator()->isDefaultLocale()) {
            foreach ($this->getAttributes() as $attribute => $value) {
                $this->translateAttribute($attribute, $value);
            }
        }
    }

    /**
     * Translate model attribute.
     *
     * @param string $attribute
     * @param $value
     */
    protected function translateAttribute(string $attribute, $value): void
    {
        if ($this->isTranslatable($attribute)) {
            $this->attributes[$attribute] = $this->getTranslator()->get($attribute, $this) ?: $value;
        }
    }

    /**
     * Determine if the attribute is translatable.
     *
     * @param string $attribute
     * @return bool
     */
    protected function isTranslatable(string $attribute): bool
    {
        return in_array($attribute, $this->translatable, true);
    }

    /**
     * Get translator.
     *
     * @return Translator
     */
    protected function getTranslator(): Translator
    {
        return app(Translator::class);
    }
}
