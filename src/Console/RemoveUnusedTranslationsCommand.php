<?php

namespace Nevadskiy\Translatable\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Nevadskiy\Translatable\Models\Translation;

class RemoveUnusedTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translatable:remove-unused';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unused translations from the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->removeUnusedTranslations();
        $this->info('All unused translations have been removed.');
    }

    /**
     * Remove all unused translations.
     */
    private function removeUnusedTranslations(): void
    {
        Translation::query()
            ->with(['translatable' => function (MorphTo $query) {
                $query->withoutGlobalScopes();
            }])->each(function (Translation $translation) {
                if (is_null($translation->translatable)) {
                    $translation->delete();
                }
            }, 200);
    }
}