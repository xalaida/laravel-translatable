<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class NullableTranslationTest extends TestCase
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

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title')->nullable();
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_stores_null_as_translation(): void
    {
        $book = new BookWithNullableTranslation();
        $book->translator()->set('title', 'Thirst for music', 'en');
        $book->translator()->set('title', null, 'uk');
        $book->save();

        static::assertNull($book->translator()->get('title', 'uk'));
        $this->assertDatabaseHas('book_translations', [
            'title' => null,
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_overrides_previous_translation_with_null(): void
    {
        $book = new BookWithNullableTranslation();
        $book->translator()->set('title', 'Thirst for music', 'en');
        $book->translator()->set('title', 'Спрага музики', 'uk');
        $book->save();

        $book->translator()->add('title', null, 'uk');
        static::assertNull($book->translator()->get('title', 'uk'));
        $this->assertDatabaseHas('book_translations', [
            'title' => null,
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_overrides_translation_with_null_in_fallback_locale(): void
    {
        $book = new BookWithNullableTranslation();
        $book->title = 'Thirst for music';
        $book->save();

        $book->translator()->add('title', null, $this->app->getFallbackLocale());

        static::assertNull($book->fresh()->title);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('book_translations');
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string|null title
 */
class BookWithNullableTranslation extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }
}
