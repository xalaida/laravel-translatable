<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
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
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_accessors_to_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $book->translator()->add('title', 'лісова пісня', 'uk');

        self::assertEquals('Лісова пісня', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_applies_accessors_to_translatable_attributes_using_getters(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'Forest song';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'лісова пісня';

        self::assertEquals('Лісова пісня', $book->title);
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes_using_getters(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'forest song';
        $book->save();

        self::assertEquals('Forest song', $book->title);
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes_in_fallback_locale(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'forest song';
        $book->save();

        $this->app->setLocale('uk');

        self::assertEquals('Forest song', $book->translator()->get('title'));
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_accessors(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'forest song';
        $book->save();

        $book->translator()->add('title', 'Лісова пісня', 'uk');
        $book->translator()->get('title', 'uk');
        $book->save();

        self::assertEquals('forest song', $book->getRawOriginal('title'));
    }

    /** @test */
    public function it_correctly_stores_translations_after_applied_accessors(): void
    {
        $book = new BookWithRaw();
        $book->title = 'forest song';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'лісова пісня';
        $book->save();

        self::assertEquals('Лісова пісня', $book->title);
        $book->save();

        self::assertEquals('лісова пісня', $book->fresh()->translator()->getRaw('title', 'uk'));
    }

    /** @test */
    public function it_still_applies_accessors_for_non_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'forest song';
        $book->save();

        $book->translator()->add('description', 'Прекрасний предковічний ліс на Волині', 'uk');

        $this->app->setLocale('uk');

        self::assertEquals('Пре...', $book->description_short);
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

/**
 * @property string title
 * @property string description
 * @property string description_short
 */
class BookWithAccessors extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description'
    ];

    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }

    public function getDescriptionShortAttribute(): string
    {
        return Str::limit($this->description, 3);
    }
}
