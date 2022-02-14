<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class MutatorsTranslatableTest extends TestCase
{
    /** @test */
    public function it_uses_model_mutators_for_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $book->translation()->add('title', 'Очень очень длинное название для книги', 'ru');

        $this->assertDatabaseHas('translations', [
            'value' => 'Очень очень длинное название д...',
        ]);
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_mutator_to_translation(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        $book->translation()->add('title', 'Очень очень длинное название для книги', 'ru');

        self::assertEquals('My book', $book->title);
    }

    /** @test */
    public function it_applies_mutator_using_auto_translatable_setter(): void
    {
        $book = BookFactory::new()->create();

        $this->app->setLocale('ru');

        $book->title = 'Очень очень длинное название для книги';

        self::assertEquals('Очень очень длинное название д...', $book->title);
    }
}
