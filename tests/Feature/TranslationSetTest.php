<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Tests\Support\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\TranslationSet;

class TranslationSetTest extends TestCase
{
    /** @test */
    public function it_sets_many_translations_from_translations_object(): void
    {
        $book = new BookForTranslationSet();

        $book->title = new TranslationSet([
            'en' => 'Wings',
            'uk' => 'Крила',
        ]);

        static::assertSame('Wings', $book->translator()->get('title', 'en'));
        static::assertSame('Крила', $book->translator()->get('title', 'uk'));
    }
}

/**
 * @property string title
 */
class BookForTranslationSet extends Model
{
    use HasTranslations;

    protected $translatable = [
        'title'
    ];
}
