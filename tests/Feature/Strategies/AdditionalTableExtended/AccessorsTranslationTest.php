<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\AdditionalTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\AdditionalTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

/**
 * TODO: test it using in-memory array strategy and simplify database test to just test attributes and originals
 */
class AccessorsTranslationTest extends TestCase
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
            $table->string('title')->nullable();
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
    public function it_applies_accessor_to_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $book->translator()->add('title', 'Лісова пісня', 'uk');

        self::assertEquals('Лісова пісня.', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_applies_accessor_to_translatable_attributes_using_getter(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'Лісова пісня';

        self::assertEquals('Лісова пісня.', $book->title);
    }

    /** @test */
    public function it_still_applies_accessor_to_original_attributes_using_getter(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        self::assertEquals('Forest song.', $book->title);
    }

    /** @test */
    public function it_still_applies_accessor_to_original_attributes_in_fallback_locale(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $this->app->setLocale('uk');

        self::assertEquals('Forest song.', $book->translator()->get('title'));
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_accessor(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $book->translator()->add('title', 'Лісова пісня', 'uk');
        $book->translator()->get('title', 'uk');
        $book->save();

        $this->assertDatabaseHas('books', ['title' => 'Forest song']);
        self::assertEquals('Forest song', $book->getRawOriginal('title'));
    }

    /** @test */
    public function it_correctly_stores_translations_after_applied_accessor(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'Лісова пісня';
        $book->save();

        self::assertEquals('Лісова пісня.', $book->title);
        $book->save();

        self::assertEquals('Лісова пісня', $book->fresh()->translator()->getRawOrFail('title', 'uk'));
    }

    /** @test */
    public function it_still_applies_accessor_for_non_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $book->translator()->add('title', 'Лісова пісня', 'uk');

        $this->app->setLocale('uk');
        self::assertEquals('Ліс...', $book->title_short);
    }

    /** @test */
    public function it_applies_accessor_for_translatable_attributes_in_fallback_locale_only_once(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        self::assertEquals('Forest song.', $book->title);
    }

    /** @test */
    public function it_applies_accessor_for_nullable_translatable_attribute(): void
    {
        $book = new BookWithAccessors();
        $book->title = null;
        $book->save();

        self::assertEquals('.', $book->title);
    }

    /**
     * @inheritDoc
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
 * @property string|null title_short
 */
class BookWithAccessors extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    public function getTitleAttribute(?string $title): string
    {
        return $title . '.';
    }

    public function getTitleShortAttribute(): string
    {
        return Str::limit($this->title, 3);
    }

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }
}
