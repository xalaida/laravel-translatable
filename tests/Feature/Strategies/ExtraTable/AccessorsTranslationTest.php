<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

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
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_accessor_to_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->translator()->set('title', 'Лісова пісня', 'uk');
        $book->save();

        self::assertEquals('Лісова пісня.', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_applies_accessor_to_translatable_attributes_using_getters(): void
    {
        $book = new BookWithAccessors();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->translator()->set('title', 'Лісова пісня', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        self::assertEquals('Лісова пісня.', $book->title);
    }

    /** @test */
    public function it_still_applies_accessor_to_fallback_attributes_using_getters(): void
    {
        $book = new BookWithAccessors();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->save();

        $this->app->setLocale('uk');
        self::assertEquals('Forest song.', $book->title);
    }

    /** @test */
    public function it_still_applies_accessor_to_original_attributes_in_fallback_locale(): void
    {
        $book = new BookWithAccessors();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->save();

        self::assertEquals('Forest song.', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_does_not_override_fallback_attribute_after_applying_accessor(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $book->translator()->add('title', 'Лісова пісня', 'uk');
        self::assertEquals('Лісова пісня.', $book->translator()->get('title', 'uk'));
        $book->save();

        self::assertEquals('Forest song', $book->translator()->getRawOrFail('title'));
    }

    /** @test */
    public function it_does_not_restores_translations_after_resolving_accessor(): void
    {
        $book = new BookWithAccessors();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->translator()->set('title', 'Лісова пісня', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        self::assertEquals('Лісова пісня.', $book->title);
        $book->save();

        self::assertEquals('Лісова пісня', $book->fresh()->translator()->getRawOrFail('title', 'uk'));
    }

    /** @test */
    public function it_still_applies_accessor_to_non_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->translator()->set('title', 'Лісова пісня', 'uk');
        $book->translator()->set('description', 'Beautiful ancient forest in Volyn', 'en');
        $book->translator()->set('description', 'Прекрасний предковічний ліс на Волині', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        self::assertEquals('Пре...', $book->description_short);
    }

    /** @test */
    public function it_applies_accessor_for_translatable_attributes_in_fallback_locale_only_once(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        self::assertEquals('Forest song.', $book->translator()->get('title'));
    }

    /** @test */
    public function it_applies_accessor_for_nullable_translatable_attribute(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Ancient forest';
        $book->description = null;
        $book->save();

        self::assertEquals(null, $book->description_short);
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
 * @property string title
 * @property string|null description
 * @property string|null description_short
 */
class BookWithAccessors extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }

    public function getTitleAttribute(string $title): string
    {
        return $title . '.';
    }

    public function getDescriptionShortAttribute(): ?string
    {
        return Str::limit($this->description, 3);
    }
}
