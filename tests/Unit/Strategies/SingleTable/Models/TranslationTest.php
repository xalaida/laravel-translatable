<?php

namespace Nevadskiy\Translatable\Tests\Unit\Strategies\SingleTable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;
use Nevadskiy\Translatable\Tests\TestCase;

class TranslationTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    /**
     * Set up the database schema.
     */
    private function createSchema(): void
    {
        $this->schema()->create('books', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_be_scoped_by_locale(): void
    {
        $book = new Book();
        $book->save();

        $translation1 = new Translation();
        $translation1->translatable()->associate($book);
        $translation1->translatable_attribute = 'title';
        $translation1->locale = 'uk';
        $translation1->value = 'Спрага музики';
        $translation1->save();

        $translation2 = new Translation();
        $translation2->translatable()->associate($book);
        $translation2->translatable_attribute = 'title';
        $translation2->locale = 'en';
        $translation2->value = 'Thirst for music';
        $translation2->save();

        $translations = Translation::query()
            ->forLocale('uk')
            ->get();

        self::assertCount(1, $translations);
        self::assertTrue($translations->first()->is($translation1));
    }

    /** @test */
    public function it_can_be_scoped_by_array_of_locales(): void
    {
        $book = new Book();
        $book->save();

        $translation1 = new Translation();
        $translation1->translatable()->associate($book);
        $translation1->translatable_attribute = 'title';
        $translation1->locale = 'uk';
        $translation1->value = 'Отак загинув Гуска';
        $translation1->save();

        $translation2 = new Translation();
        $translation2->translatable()->associate($book);
        $translation2->translatable_attribute = 'title';
        $translation2->locale = 'en';
        $translation2->value = 'This is how Guska died';
        $translation2->save();

        $translation3 = new Translation();
        $translation3->translatable()->associate($book);
        $translation3->translatable_attribute = 'title';
        $translation3->locale = 'pl';
        $translation3->value = 'Tak zginęła Guska';
        $translation3->save();

        $translations = Translation::query()
            ->forLocale(['uk', 'pl'])
            ->get();

        self::assertCount(2, $translations);
        self::assertTrue($translations->contains($translation1));
        self::assertTrue($translations->contains($translation3));
    }

    /** @test */
    public function it_can_be_scoped_by_attribute(): void
    {
        $book = new Book();
        $book->save();

        $translation1 = new Translation();
        $translation1->translatable()->associate($book);
        $translation1->translatable_attribute = 'title';
        $translation1->locale = 'uk';
        $translation1->value = 'Спрага музики';
        $translation1->save();

        $translation2 = new Translation();
        $translation2->translatable()->associate($book);
        $translation2->translatable_attribute = 'description';
        $translation2->locale = 'uk';
        $translation2->value = 'Таємний агент радянської розвідки';
        $translation2->save();

        $translations = Translation::query()
            ->forAttribute('title')
            ->get();

        self::assertCount(1, $translations);
        self::assertTrue($translations->first()->is($translation1));
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

class Book extends Model
{
    protected $table = 'books';
}
