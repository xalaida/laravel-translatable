<?php

namespace Nevadskiy\Translatable;

use Nevadskiy\Translatable\Models\Translation;

/**
 * @mixin HasTranslations
 */
trait ArchivingTranslations
{
    /**
     * Prepared archived translations to be saved into the database.
     *
     * @var array
     */
    protected $preparedArchivedTranslations = [];

    /**
     * The flag to check if the previous translations should be archived automatically when new one is added.
     *
     * @var bool
     */
    protected $autoArchiveTranslation = false;

    /**
     * Enable auto archiving for the previous translations.
     *
     * @return HasTranslations|mixed
     */
    public function enableArchivingTranslations()
    {
        $this->autoArchiveTranslation = true;

        return $this;
    }

    /**
     * Disable auto archiving for the previous translations.
     *
     * @return HasTranslations|mixed
     */
    public function disableArchivingTranslations()
    {
        $this->autoArchiveTranslation = false;

        return $this;
    }

    /**
     * Determine whether the previous translations should be archived automatically.
     */
    public function shouldArchiveTranslations(): bool
    {
        return $this->autoArchiveTranslation;
    }

    /**
     * Archive the given translation.
     */
    public function archiveTranslation(string $attribute, string $value, ?string $locale = null): Translation
    {
        $this->assertTranslatableAttribute($attribute);

        if (count(func_get_args()) < 3) {
            $locale = static::getTranslator()->getLocale();
        }

        return static::getTranslator()->add(
            $this, $attribute, $this->withAttributeMutators($attribute, $value), $locale, true
        );
    }

    /**
     * Prepare archived translations for the default locale.
     */
    protected function prepareArchivedTranslation(string $attribute): void
    {
        if ($this->shouldArchiveTranslations()) {
            $this->preparedArchivedTranslations[$attribute] = $this->getDefaultTranslation($attribute);
        }
    }

    /**
     * Pull any prepared archived translations.
     */
    protected function pullPreparedArchivedTranslations(): array
    {
        $translations = $this->preparedArchivedTranslations;

        $this->preparedArchivedTranslations = [];

        return $translations;
    }

    /**
     * Archive default translations for the model if the feature is enabled.
     */
    protected function savePreparedArchivedTranslations(): void
    {
        if ($this->shouldArchiveTranslations()) {
            $this->performSavePreparedArchivedTranslations();
        }
    }

    /**
     * Archive default translations for the model.
     */
    protected function performSavePreparedArchivedTranslations(): void
    {
        foreach ($this->pullPreparedArchivedTranslations() as $attribute => $value) {
            static::getTranslator()->add(
                $this, $attribute, $value, static::getTranslator()->getDefaultLocale(), true
            );
        }
    }
}
