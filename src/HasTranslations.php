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
     * Prepared translations to be saved into the database.
     *
     * @var array
     */
    protected $preparedTranslations = [];

    /**
     * Resolved attribute translations from the database.
     *
     * @var array
     */
    protected $resolvedTranslations = [];

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

        if (! $this->autoLoadTranslations($attribute)) {
            return $this->getOriginalAttribute($attribute);
        }

        return $this->getTranslationOrOriginal($attribute);
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
     * Get translation value for the attribute.
     *
     * @deprecated use $this->translate()->get() method instead.
     * @return mixed
     */
    public function getTranslation(string $attribute, string $locale = null)
    {
        return $this->translation()->get($attribute, $locale);
    }

    /**
     * Get raw translation value for the attribute.
     *
     * @return mixed
     */
    public function getRawTranslation(string $attribute, string $locale = null)
    {
        $locale = $locale ?: $this->translation()->getLocale();

        if (! $this->hasResolvedTranslation($attribute, $locale)) {
            $this->resolveTranslation($attribute, $locale);
        }

        $translation = $this->getResolvedTranslation($attribute, $locale);

        if (is_null($translation)) {
            event(new TranslationNotFound($this, $attribute, $locale));
        }

        return $translation;
    }

    /**
     * Determine whether the attribute has resolved translation according to the given locale.
     */
    protected function hasResolvedTranslation(string $attribute, string $locale): bool
    {
        return isset($this->resolvedTranslations[$locale][$attribute]);
    }

    /**
     * Set the given value as the resolved attribute translation.
     */
    protected function setResolvedTranslation(string $attribute, string $locale, $value): void
    {
        $this->resolvedTranslations[$locale][$attribute] = $value;
    }

    /**
     * Get the loaded attribute translation.
     *
     * @return mixed
     */
    protected function getResolvedTranslation(string $attribute, string $locale)
    {
        return $this->resolvedTranslations[$locale][$attribute];
    }

    /**
     * Resolve a translation for the given attribute and locale.
     */
    protected function resolveTranslation(string $attribute, string $locale): void
    {
        $this->setResolvedTranslation($attribute, $locale, $this->translation()->get($attribute, $locale));
    }

    /**
     * Determine whether the model has same resolved translation.
     *
     * @param $value
     */
    protected function hasSameResolvedTranslation(string $attribute, string $locale, $value): bool
    {
        return $this->hasResolvedTranslation($attribute, $locale)
            && $this->getResolvedTranslation($attribute, $locale) === $value;
    }

    /**
     * Prepare translation to be stored in the database.
     *
     * @return HasTranslations|mixed
     */
    protected function prepareTranslation(string $attribute, string $locale, $value)
    {
        if ($this->hasSameResolvedTranslation($attribute, $locale, $value)) {
            return $this;
        }

        $this->preparedTranslations[$locale][$attribute] = $value;
        $this->setResolvedTranslation($attribute, $locale, $value);

        return $this;
    }

//    /**
//     * Pull any prepared translations.
//     */
//    protected function pullPreparedTranslations(): array
//    {
//        $translations = $this->preparedTranslations;
//
//        $this->preparedTranslations = [];
//
//        return $translations;
//    }

    /**
     * Set translation to the attribute.
     *
     * @deprecated use $this->translate()->set() method instead.
     * @param mixed $value
     * @return HasTranslations|mixed
     */
    public function setTranslation(string $attribute, $value, string $locale = null)
    {
        return $this->translation()->set($attribute, $value, $locale);
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

//    /**
//     * Save the model translations.
//     */
//    protected function savePreparedTranslations(): void
//    {
//        foreach ($this->pullPreparedTranslations() as $locale => $attributes) {
//            // TODO: check if it needs to array_filter here. if it is clear, there is no loop.
//            foreach (array_filter($attributes) as $attribute => $value) {
//                $this->translation()->set($attribute, $value, $locale);
//            }
//        }
//    }

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
     * Get a translation of the attribute or default value if translation is missing.
     *
     * @return mixed
     */
    public function getTranslationOrOriginal(string $attribute, string $locale = null)
    {
        return $this->translation()->getOrOriginal($attribute, $locale);
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
            $translations[$attribute] = $this->getTranslationOrOriginal($attribute, $locale);
        }

        return $translations;
    }
}
