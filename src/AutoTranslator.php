<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Engine\TranslatorEngine;

class AutoTranslator
{
    /**
     * @var TranslatorEngine
     */
    private $engine;

    /**
     * AutoTranslator constructor.
     *
     * @param TranslatorEngine $engine
     */
    public function __construct(TranslatorEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Translate model using translatable attributes.
     *
     * @param Model|HasTranslations $translatable
     * @param string $target
     * @param string|null $source
     */
    public function translate(Model $translatable, string $target, ?string $source = null): void
    {
        $translations = [];

        [$target, $source] = [$this->getResolvedTargetLang($target), $this->getResolvedSourceLang($source)];

        foreach ($translatable->getTranslatable() as $attribute) {
            $translations[$attribute] = $this->engine->translate(
                $translatable->getAttribute($attribute), $target, $source
            );
        }

        $translatable->translate($translations, $target);
    }

    /**
     * Get resolved source lang.
     *
     * @param string|null $lang
     * @return string
     */
    private function getResolvedSourceLang(?string $lang): string
    {
        return $lang ?: config('app.fallback_locale');
    }

    /**
     * Get resolved target lang.
     *
     * @param string|null $lang
     * @return string
     */
    private function getResolvedTargetLang(?string $lang): string
    {
        return $lang ?: app()->getLocale();
    }
}
