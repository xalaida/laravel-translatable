<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Tests\Support\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translator;

// TODO: move tests from strategies here
class DisableFallbackTest extends TestCase
{
    /** @test */
    public function it_allows_to_disable_fallback_behaviour(): void
    {
        $book = new BookForDisableFallback();
        $book->translator()->set('title', 'Wings', 'en');

        static::assertNull($book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_allows_to_re_enable_fallback_behaviour(): void
    {
        $book = new BookForDisableFallback();
        $book->translator()->set('title', 'Wings', $this->app->getFallbackLocale());

        $book->translator()->enableFallback();

        static::assertSame('Wings', $book->translator()->get('title', 'uk'));
    }
}

/**
 * @property string title
 */
class BookForDisableFallback extends Model
{
    use HasTranslations;

    protected $translatable = [
        'title'
    ];

    protected function configureTranslator(Translator $translator): void
    {
        $translator->disableFallback();
    }
}
