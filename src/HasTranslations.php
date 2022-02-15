<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Events\TranslationNotFound;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;
use Nevadskiy\Translatable\Strategies\SingleTableStrategy;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 * @property Collection|Translation[] translations
 */
trait HasTranslations
{
    use TranslationScopes,
        TranslatableUrlRouting;

    /**
     * The model translator instance.
     */
    protected $translator;

    /**
     * Boot the trait.
     */
    protected static function bootHasTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadScope());

        static::saved(static function (self $translatable) {
            $translatable->handleSavedEvent();
        });

        static::deleted(static function (self $translatable) {
            $translatable->handleDeletedEvent();
        });
    }

    /**
     * Init the trait.
     */
    protected function initializeHasTranslations(): void
    {
        $this->translator = $this->newTranslation();
    }

    /**
     * Make a new translator instance for the model.
     */
    public function newTranslation(): Translator
    {
        return new Translator($this, $this->getTranslationStrategy());
    }

    /**
     * Get the translator instance for the model.
     */
    public function translation(): Translator
    {
        // TODO: maybe consider lazy initialization $this->translator ?: $this->newTranslator();
        return $this->translator;
    }

    /**
     * Get the translation strategy.
     */
    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return new SingleTableStrategy($this);
    }

    /**
     * Get the translations' relation.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        if (! $this->shouldBeTranslated($attribute)) {
            return $this->getOriginalAttribute($attribute);
        }

        // TODO: change API.
        if (! $this->autoLoadTranslations($attribute)) {
            return $this->getOriginalAttribute($attribute);
        }

        return $this->translation()->getOrOriginal($attribute);
    }

    /**
     * Get attribute's default value without translation.
     *
     * @return mixed
     */
    public function getOriginalAttribute(string $attribute)
    {
        return parent::getAttribute($attribute);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($attribute, $value)
    {
        if (! $this->shouldBeTranslated($attribute)) {
            return $this->setOriginalAttribute($attribute, $value);
        }

        // TODO: change API.
        if (! $this->autoSaveTranslations($attribute)) {
            return $this->setOriginalAttribute($attribute, $value);
        }

        $this->translation()->set($attribute, $value);

        return $this;
    }

    /**
     * Set attribute's value without translation.
     *
     * @param mixed $value
     * @return mixed
     */
    public function setOriginalAttribute(string $attribute, $value)
    {
        return parent::setAttribute($attribute, $value);
    }

    /**
     * Determine if the model should automatically load translations on attribute get.
     */
    public function autoLoadTranslations(string $attribute): bool
    {
        return resolve(Translatable::class)->shouldAutoLoadTranslations();
    }

    /**
     * Determine if the model should automatically save translations on attribute set.
     */
    public function autoSaveTranslations(string $attribute): bool
    {
        return resolve(Translatable::class)->shouldAutoSaveTranslations();
    }

    /**
     * Determine whether the attribute should be translated.
     */
    protected function shouldBeTranslated(string $attribute): bool
    {
        // TODO: review this. probably translation can be associated with a model that does not exist yet?

        return $this->exists && $this->isTranslatable($attribute);
    }

    /**
     * Determine whether the attribute is translatable.
     */
    public function isTranslatable(string $attribute): bool
    {
        return collect($this->getTranslatable())->contains($attribute);
    }

    /**
     * Get translatable attributes.
     */
    public function getTranslatable(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Handle the model "saved" event.
     */
    protected function handleSavedEvent(): void
    {
        $this->translation()->save();
    }

    /**
     * Handle the model deleted event.
     */
    protected function handleDeletedEvent(): void
    {
        if ($this->shouldDeleteTranslations()) {
            $this->deleteTranslations();
        }
    }

    /**
     * Determine whether the model should delete translations.
     */
    protected function shouldDeleteTranslations(): bool
    {
        if (! $this->isUsingSoftDeletes()) {
            return true;
        }

        if ($this->isForceDeleting()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the model uses soft deletes.
     */
    protected function isUsingSoftDeletes(): bool
    {
        return collect(class_uses_recursive($this))->contains(SoftDeletes::class);
    }

    /**
     * Delete the model translations.
     */
    protected function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    /**
     * Get the attribute value with all accessors and casts applied.
     *
     * @param mixed $value
     * @return mixed
     */
    public function withAttributeGetter(string $key, $value)
    {
        $original = $this->attributes[$key];

        $this->attributes[$key] = $value;

        $processed = parent::getAttribute($key);

        $this->attributes[$key] = $original;

        return $processed;
    }

    /**
     * Get the attribute value with all mutators and casts applied.
     *
     * @param mixed $value
     * @return mixed
     */
    public function withAttributeSetter(string $key, $value)
    {
        $original = $this->attributes[$key];

        parent::setAttribute($key, $value);

        $processed = $this->attributes[$key];

        $this->attributes[$key] = $original;

        return $processed;
    }

    /**
     * Convert the model's attributes to an array.
     */
    public function attributesToArray(): array
    {
        return array_merge(parent::attributesToArray(), $this->getTranslations());
    }

    /**
     * Get model translations.
     */
    public function getTranslations(string $locale = null): array
    {
        $locale = $locale ?: $this->translation()->getLocale();

        $translations = [];

        foreach ($this->getTranslatable() as $attribute) {
            $translations[$attribute] = $this->translation()->getOrOriginal($attribute, $locale);
        }

        return $translations;
    }
}
