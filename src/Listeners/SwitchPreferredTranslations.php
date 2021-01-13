<?php

namespace Nevadskiy\Translatable\Listeners;

use Nevadskiy\Translatable\Events\TranslationSavedEvent;

class SwitchPreferredTranslations
{
    /**
     * Handle the given event.
     */
    public function handle(TranslationSavedEvent $event): void
    {
        $event->translation->translatable->translations()
            ->whereKeyNot($event->translation->id)
            ->update(['is_preferred' => false]);
    }
}
