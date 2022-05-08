<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class RawTranslationTest extends TestCase
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
    public function it_retrieves_raw_translation_value_for_given_locale(): void
    {
        $book = new BookWithRaw();
        $book->translator()->set('title', 'лісова пісня', 'uk');
        $book->save();

        self::assertEquals('лісова пісня', $book->translator()->getRawOrFail('title', 'uk'));
    }

    /** @test */
    public function it_throws_exception_when_raw_translation_is_missing(): void
    {
        $book = new BookWithRaw();
        $book->translator()->set('title', 'forest song', 'en');
        $book->save();

        $this->expectException(TranslationMissingException::class);

        $book->translator()->getRawOrFail('title', 'pl');
    }

    /** @test */
    public function it_retrieves_translation_with_accessor_applied_using_get_method(): void
    {
        $book = new BookWithRaw();
        $book->translator()->set('title', 'лісова пісня', 'uk');
        $book->save();

        self::assertEquals('Лісова пісня', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_throws_exception_when_trying_to_get_raw_translation_for_non_translatable_attribute(): void
    {
        $book = new BookWithRaw();
        $book->title = 'forest song';
        $book->save();

        $this->expectException(AttributeNotTranslatableException::class);

        $book->translator()->getRawOrFail('created_at');
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
 */
class BookWithRaw extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }
}
