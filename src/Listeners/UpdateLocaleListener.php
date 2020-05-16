<?php

namespace Nevadskiy\Translatable\Listeners;

use Illuminate\Foundation\Events\LocaleUpdated;
use Nevadskiy\Translatable\ModelTranslator;

class UpdateLocaleListener
{
    /**
     * @var ModelTranslator
     */
    private $translator;

    /**
     * UpdateLocaleListener constructor.
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
