<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
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
    public function it_eager_loads_translations_relation(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'The last prophet', 'en');
        $book->translator()->set('title', 'Останній пророк', 'uk');
        $book->translator()->set('title', 'Ostatni prorok', 'pl');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations()
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(3, $record->translations);
        self::assertEquals('The last prophet', $record->translations[0]->title);
        self::assertEquals('en', $record->translations[0]->locale);
        self::assertEquals('Останній пророк', $record->translations[1]->title);
        self::assertEquals('uk', $record->translations[1]->locale);
        self::assertEquals('Ostatni prorok', $record->translations[2]->title);
        self::assertEquals('pl', $record->translations[2]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_relation_for_all_locales(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'The last prophet', 'en');
        $book->translator()->set('title', 'Останній пророк', 'uk');
        $book->translator()->set('title', 'Ostatni prorok', 'pl');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations(['*'])
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(3, $record->translations);
        self::assertEquals('The last prophet', $record->translations[0]->title);
        self::assertEquals('en', $record->translations[0]->locale);
        self::assertEquals('Останній пророк', $record->translations[1]->title);
        self::assertEquals('uk', $record->translations[1]->locale);
        self::assertEquals('Ostatni prorok', $record->translations[2]->title);
        self::assertEquals('pl', $record->translations[2]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_relation_with_given_locales(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'The last prophet', 'en');
        $book->translator()->set('title', 'Останній пророк', 'uk');
        $book->translator()->set('title', 'Ostatni prorok', 'pl');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations(['uk', 'pl'])
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(2, $record->translations);
        self::assertEquals('Останній пророк', $record->translations[0]->title);
        self::assertEquals('uk', $record->translations[0]->locale);
        self::assertEquals('Ostatni prorok', $record->translations[1]->title);
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
 * @property string|null description
 */
class BookWithTranslations extends Model
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
}
