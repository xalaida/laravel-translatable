<?php

namespace Nevadskiy\Translatable\Listeners;

use Nevadskiy\Translatable\Events\TranslationSaved;

class ArchivePreviousTranslations
{
    /**
     * Handle the given event.
     */
    public function handle(TranslationSaved $event): void
    {
        $event->translation->translatable->translations()
            ->forAttribute($event->translation->translatable_attribute)
            ->forLocale($event->translation->locale)
            ->whereKeyNot($event->translation->id)
            ->update(['is_archived' => true]);
    }
}
