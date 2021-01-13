<?php

namespace Nevadskiy\Translatable\Listeners;

use Nevadskiy\Translatable\Events\TranslationSaved;

class SwitchPreferredTranslations
{
    /**
     * Handle the given event.
     */
    public function handle(TranslationSaved $event): void
    {
        $event->translation->translatable->translations()
            ->whereKeyNot($event->translation->id)
            ->update(['is_preferred' => false]);
    }
}
