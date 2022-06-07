<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class WithTranslationsScopeTest extends TestCase
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
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_eager_loads_translations_relation(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('title', 'Cienie zapomnianych przodków', 'pl');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations()
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(2, $record->translations);

        self::assertEquals('Тіні забутих предків', $record->translations[0]->title);
        self::assertEquals('uk', $record->translations[0]->locale);

        self::assertEquals('Cienie zapomnianych przodków', $record->translations[1]->title);
        self::assertEquals('pl', $record->translations[1]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_relation_for_all_locales(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('title', 'Cienie zapomnianych przodków', 'pl');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations(['*'])
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(2, $record->translations);

        self::assertEquals('Тіні забутих предків', $record->translations[0]->title);
        self::assertEquals('uk', $record->translations[0]->locale);

        self::assertEquals('Cienie zapomnianych przodków', $record->translations[1]->title);
        self::assertEquals('pl', $record->translations[1]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_relation_with_given_locales(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('title', 'Cienie zapomnianych przodków', 'pl');
        $book->translator()->set('title', 'Stíny zapomenutých předků', 'cs');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations(['uk', 'pl'])
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(2, $record->translations);

        self::assertEquals('Тіні забутих предків', $record->translations[0]->title);
        self::assertEquals('uk', $record->translations[0]->locale);

        self::assertEquals('Cienie zapomnianych przodków', $record->translations[1]->title);
        self::assertEquals('pl', $record->translations[1]->locale);
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
 */
class BookWithTranslations extends Model
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
