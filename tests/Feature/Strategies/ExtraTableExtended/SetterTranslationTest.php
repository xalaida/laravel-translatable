<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class SetterTranslationTest extends TestCase
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
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->integer('size')->default(0);
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_stores_translations_for_current_locale_using_model_setter(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');
        $book->title = 'Монстри океану';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'id' => $book->getKey(),
            'title' => 'Ocean monsters',
        ]);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Монстри океану',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_stores_translations_using_update_method(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');
        $book->fillable(['title'])->update(['title' => 'Монстри океану']);
        $book = $book->fresh();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'id' => $book->getKey(),
            'title' => 'Ocean monsters',
        ]);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Монстри океану',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_does_not_store_translation_without_save_method(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');
        $book->title = 'Монстри океану';

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'id' => $book->getKey(),
            'title' => 'Ocean monsters',
        ]);
        $this->assertDatabaseCount('book_translations', 0);
    }

    /** @test */
    public function it_overrides_previous_translations_correctly(): void
    {
        $book = new BookWithSetters();
        $book->translator()->set('title', 'Ocean monsters', 'en');
        $book->translator()->set('title', 'Монстри---океану', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        $book->title = 'Монстри океану';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'id' => $book->getKey(),
            'title' => 'Ocean monsters',
        ]);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Монстри океану',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_does_not_store_retrieved_values_as_translations_after_save_call(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        static::assertSame('Ocean monsters', $book->title);

        $book->save();

        $this->assertDatabaseMissing('book_translations', ['locale' => 'uk']);
    }

    /** @test */
    public function it_stores_correctly_many_attributes_at_once(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->description = 'Dive in and take a closer look at the incredible sea creatures created by nature!';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'Монстри океану';
        $book->description = 'Занурся та розглянь ближче неймовірних морських істот, створених природою!';
        $book->size = 25;
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'title' => 'Ocean monsters',
            'description' => 'Dive in and take a closer look at the incredible sea creatures created by nature!',
        ]);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Монстри океану',
            'description' => 'Занурся та розглянь ближче неймовірних морських істот, створених природою!',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_handles_switching_locales_correctly(): void
    {
        $originalLocale = $this->app->getLocale();

        $book = new BookWithSetters();
        $book->translator()->set('title', 'Ocean monsters', 'en');
        $book->translator()->set('title', 'Монстри океану', 'uk');
        $book->translator()->set('title', 'Morskie potwory', 'pl');
        $book->save();

        $this->app->setLocale('uk');
        static::assertSame('Монстри океану', $book->title);

        $this->app->setLocale('pl');
        static::assertSame('Morskie potwory', $book->title);

        $this->app->setLocale($originalLocale);
        static::assertSame('Ocean monsters', $book->title);
    }

    /** @test */
    public function it_overrides_translations_for_fallback_locale(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean---monsters';
        $book->save();

        $book->title = 'Ocean monsters';
        $book->save();

        static::assertSame('Ocean monsters', $book->title);
        $this->assertDatabaseHas('books', ['title' => 'Ocean monsters']);
        $this->assertDatabaseCount('book_translations', 0);
    }

    /** @test */
    public function it_does_not_store_translations_for_non_translatable_attributes(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        $book->size = 3;
        $book->save();

        $book = $book->fresh();

        static::assertSame(3, $book->size);
        $this->assertDatabaseHas('books', [
            'title' => 'Ocean monsters',
            'size' => 3,
        ]);
        $this->assertDatabaseCount('book_translations', 0);
    }

    /** @test */
    public function it_stores_null_value_as_translation_from_model_setter(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = null;
        $book->save();

        $book = $book->fresh();

        static::assertNull($book->title);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'title' => null,
            'locale' => 'uk',
        ]);
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
 * @property string title
 * @property string|null description
 * @property int size
 */
class BookWithSetters extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];

    protected $casts = [
        'size' => 'int'
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
