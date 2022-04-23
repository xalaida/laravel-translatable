<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
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
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_accessors_to_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'My book';
        $book->save();

        $book->translator()->add('title', 'моя статья', 'ru');

        self::assertEquals('Моя статья', $book->translator()->get('title', 'ru'));
    }

    /** @test */
    public function it_applies_accessors_to_translatable_attributes_using_getters(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'My book';
        $book->save();

        $this->app->setLocale('ru');

        $book->title = 'моя статья';

        self::assertEquals('Моя статья', $book->title);
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes_using_getters(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'my book';
        $book->save();

        self::assertEquals('My book', $book->title);
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes_in_fallback_locale(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'my book';
        $book->save();

        $book->translator()->add('title', 'моя статья', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('My book', $book->getOriginalAttribute('title'));
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_accessors(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'my book';
        $book->save();
        $book->translator()->add('title', 'моя статья', 'ru');

        $book->translator()->get('title', 'ru');
        $book->save();

        self::assertEquals('my book', $book->getRawOriginal('title'));
    }

    /** @test */
    public function it_returns_raw_translation_value_for_given_locale(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'my book';
        $book->save();

        $book->translator()->add('title', 'моя статья', 'ru');

        self::assertEquals('моя статья', $book->translator()->raw('title', 'ru'));
    }

    /** @test */
    public function it_correctly_stores_translations_after_applied_accessors(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'my book';
        $book->save();

        $this->app->setLocale('ru');

        $book->title = 'моя статья';
        $book->save();

        self::assertEquals('Моя статья', $book->title);
        $book->save();

        self::assertEquals('моя статья', $book->fresh()->translator()->raw('title', 'ru'));
    }

    /** @test */
    public function it_still_applies_accessors_for_non_translatable_attributes(): void
    {
        $book = new BookWithAccessors();
        $book->title = 'my book';
        $book->save();

        $book->translator()->add('description', 'Статья про собак', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Ста...', $book->description_short);
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

    public function getDescriptionShortAttribute(): string
    {
        return Str::limit($this->description, 3);
    }

    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }
}
