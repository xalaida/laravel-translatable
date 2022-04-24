<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
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
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('size')->default(0);
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

        $book = $book->fresh();

        self::assertEquals('Монстри океану', $book->translator()->get('title', 'uk'));
        self::assertEquals('Ocean monsters', $book->getOriginalAttribute('title'));
        $this->assertDatabaseCount('translations', 1);
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

        self::assertEquals('Монстри океану', $book->translator()->get('title', 'uk'));
        self::assertEquals('Ocean monsters', $book->getOriginalAttribute('title'));
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_store_translation_without_save_method(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'Монстри океану';

        $book = $book->fresh();

        self::assertEquals('Ocean monsters', $book->translator()->get('title', 'uk'));
        self::assertEquals('Ocean monsters', $book->title);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_overrides_previous_translations_correctly(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $book->translator()->add('title', 'Монстри---океану', 'uk');

        $this->app->setLocale('uk');

        $book->title = 'Монстри океану';
        $book->save();

        self::assertEquals('Монстри океану', $book->translator()->get('title'));
        self::assertEquals('Ocean monsters', $book->getOriginalAttribute('title'));
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_store_retrieved_values_as_translations_after_save_call(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        self::assertEquals('Ocean monsters', $book->title);

        $book->save();

        $this->assertDatabaseCount('translations', 0);
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

        $book = $book->fresh();

        self::assertEquals('Монстри океану', $book->title);
        self::assertEquals('Занурся та розглянь ближче неймовірних морських істот, створених природою!', $book->description);
        self::assertEquals(25, $book->size);

        $this->assertDatabaseCount('translations', 2);
    }

    /** @test */
    public function it_handles_switching_locales_correctly(): void
    {
        $originalLocale = $this->app->getLocale();

        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $book->translator()->set('title', 'Монстри океану', 'uk');
        $book->translator()->set('title', 'Morskie potwory', 'pl');

        $this->app->setLocale('uk');
        self::assertEquals('Монстри океану', $book->title);

        $this->app->setLocale('pl');
        self::assertEquals('Morskie potwory', $book->title);

        $this->app->setLocale($originalLocale);
        self::assertEquals('Ocean monsters', $book->title);
    }

    /** @test */
    public function it_does_not_save_translations_for_fallback_locale(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean---monsters';
        $book->save();

        $this->app->setLocale('en');

        $book->title = 'Ocean monsters';
        $book->save();

        self::assertEquals('Ocean monsters', $book->title);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_does_not_save_translations_for_non_translatable_attributes(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        $book->size = 3;
        $book->save();

        $book = $book->fresh();

        self::assertEquals(3, $book->size);
        $this->assertDatabaseCount('translations', 0);
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

        self::assertNull($book->title);
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_can_store_null_as_translation_value(): void
    {
        $book = new BookWithSetters();
        $book->title = 'Ocean monsters';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = null;
        $book->save();

        $book = $book->fresh();

        self::assertNull($book->title);
        $this->assertDatabaseCount('translations', 1);
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
}
