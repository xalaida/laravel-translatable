<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class HasTranslationTest extends TestCase
{
    /**
     * @inheritdoc
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
    public function it_can_determine_if_translation_exists_for_current_locale(): void
    {
        $book = new BookHasTranslation();
        $book->translator()->set('title', 'Spring story', 'en');
        $book->translator()->set('title', 'Весняне оповідання', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        static::assertTrue($book->translator()->has('title'));
    }

    /** @test */
    public function it_can_determine_if_translation_does_not_exists_for_current_locale(): void
    {
        $book = new BookHasTranslation();
        $book->translator()->set('title', 'Spring story', 'en');
        $book->translator()->set('title', 'Весняне оповідання', 'uk');
        $book->save();

        $this->app->setLocale('pl');
        static::assertFalse($book->translator()->has('title'));
    }

    /** @test */
    public function it_can_determine_if_translation_exists_for_custom_locale(): void
    {
        $book = new BookHasTranslation();
        $book->translator()->set('title', 'Spring story', 'en');
        $book->translator()->set('title', 'Весняне оповідання', 'uk');
        $book->save();

        static::assertTrue($book->translator()->has('title', 'en'));
        static::assertTrue($book->translator()->has('title', 'uk'));
        static::assertFalse($book->translator()->has('title', 'pl'));
    }

    /** @test */
    public function it_returns_true_if_translation_is_nullable(): void
    {
        $book = new BookHasTranslation();
        $book->translator()->set('title', 'Spring story', 'en');
        $book->translator()->set('description', null, 'en');
        $book->translator()->set('title', 'Весняне оповідання', 'uk');
        $book->save();

        static::assertTrue($book->translator()->has('description', 'en'));
        static::assertFalse($book->translator()->has('description', 'pl'));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string title
 * @property string|null description
 */
class BookHasTranslation extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
