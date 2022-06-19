<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Tests\Support\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class CustomCurrentLocaleTest extends TestCase
{
    /** @test */
    public function it_allows_setting_custom_current_locale_for_translator(): void
    {
        $book = new BookForCustomCurrentLocale();
        $book->translator()->set('title', 'Wings', 'en');
        $book->translator()->set('title', 'Крила', 'uk');

        $book->translator()->locale('uk');

        static::assertSame('Крила', $book->translator()->get('title'));
    }
}

/**
 * @property string title
 */
class BookForCustomCurrentLocale extends Model
{
    use HasTranslations;

    protected $translatable = [
        'title'
    ];
}
