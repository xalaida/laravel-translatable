<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Tests\Support\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translations;

class TranslationsObjectTest extends TestCase
{
    /** @test */
    public function it_sets_many_translations_from_translations_object(): void
    {
        $book = new BookForTranslationsObject();

        $book->title = new Translations([
            'en' => 'Wings',
            'uk' => 'Крила',
        ]);

        self::assertEquals('Wings', $book->translator()->get('title', 'en'));
        self::assertEquals('Крила', $book->translator()->get('title', 'uk'));
    }
}

/**
 * @property string title
 */
class BookForTranslationsObject extends Model
{
    use HasTranslations;

    protected $translatable = [
        'title'
    ];
}