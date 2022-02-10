<?php

namespace Nevadskiy\Translatable\Listeners;

use Illuminate\Foundation\Events\LocaleUpdated;
use Nevadskiy\Translatable\ModelTranslator;

class UpdateTranslatorLocale
{
    /**
     * @var ModelTranslator
     */
    private $translator;

    /**
     * Make a new listener instance.
     */
    public function __construct(ModelTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Handle the given event.
     */
    public function handle(LocaleUpdated $event): void
    {
        $this->translator->setLocale(
            $event->locale
        );
    }
}
