<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_eager_loads_translations_relation(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('description', 'Про кохання гуцулів Івана й Марічки з ворогуючих родів', 'uk');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations()
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(2, $record->translations);

        self::assertEquals('Тіні забутих предків', $record->translations[0]->value);
        self::assertEquals('title', $record->translations[0]->translatable_attribute);
        self::assertEquals('uk', $record->translations[0]->locale);

        self::assertEquals('Про кохання гуцулів Івана й Марічки з ворогуючих родів', $record->translations[1]->value);
        self::assertEquals('description', $record->translations[1]->translatable_attribute);
        self::assertEquals('uk', $record->translations[1]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_relation_for_all_locales(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('description', 'Про кохання гуцулів Івана й Марічки з ворогуючих родів', 'uk');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations(['*'])
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(2, $record->translations);

        self::assertEquals('Тіні забутих предків', $record->translations[0]->value);
        self::assertEquals('title', $record->translations[0]->translatable_attribute);
        self::assertEquals('uk', $record->translations[0]->locale);

        self::assertEquals('Про кохання гуцулів Івана й Марічки з ворогуючих родів', $record->translations[1]->value);
        self::assertEquals('description', $record->translations[1]->translatable_attribute);
        self::assertEquals('uk', $record->translations[1]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_relation_with_given_locales(): void
    {
        $book = new BookWithTranslations();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('title', 'Cienie zapomnianych przodków', 'pl');
        $book->translator()->set('title', 'Stíny zapomenutých předků', 'cs');
        $book->translator()->set('description', 'Про кохання гуцулів Івана й Марічки з ворогуючих родів', 'uk');
        $book->save();

        [$record] = BookWithTranslations::query()
            ->withTranslations(['uk', 'pl'])
            ->get();

        self::assertTrue($record->relationLoaded('translations'));
        self::assertCount(3, $record->translations);

        self::assertEquals('Cienie zapomnianych przodków', $record->translations[0]->value);
        self::assertEquals('title', $record->translations[0]->translatable_attribute);
        self::assertEquals('pl', $record->translations[0]->locale);

        self::assertEquals('Тіні забутих предків', $record->translations[1]->value);
        self::assertEquals('title', $record->translations[1]->translatable_attribute);
        self::assertEquals('uk', $record->translations[1]->locale);

        self::assertEquals('Про кохання гуцулів Івана й Марічки з ворогуючих родів', $record->translations[2]->value);
        self::assertEquals('description', $record->translations[2]->translatable_attribute);
        self::assertEquals('uk', $record->translations[2]->locale);
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
 */
class BookWithTranslations extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
