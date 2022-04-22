<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Behaviours\Single\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class CastTranslationsTest extends TestCase
{
    /** @test */
    public function it_casts_translatable_attributes(): void
    {
        $book = BookFactory::new()->create([
            'content' => [
                'title' => 'Chapter 1',
                'body' => 'Chapter about birds',
            ],
        ]);

        $book->translator()->add('content', ['title' => 'Глава 1', 'body' => 'Глава о птицах'], 'ru');

        $this->app->setLocale('ru');

        self::assertEquals(['title' => 'Глава 1', 'body' => 'Глава о птицах'], $book->content);
        self::assertCount(1, Translation::all());
    }

    /** @test */
    public function it_still_casts_default_value_when_translation_is_not_available(): void
    {
        $book = BookFactory::new()->create([
            'content' => [
                'title' => 'Chapter 1',
                'body' => 'Chapter about birds',
            ],
        ]);

        $this->app->setLocale('ru');

        self::assertEquals(['title' => 'Chapter 1', 'body' => 'Chapter about birds'], $book->content);
    }
}
