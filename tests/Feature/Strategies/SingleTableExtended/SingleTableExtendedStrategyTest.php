<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Tests\TestCase;

// TODO: review this testcase
// TODO: missing translation for locale
// TODO: test custom model (for uuid attributes)
// TODO: using custom (not english) language for default values
class SingleTableExtendedStrategyTest extends TestCase
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
            $table->timestamps();
        });
    }

    /** @test */
    public function it_handles_translations_for_translatable_attributes(): void
    {
        $book = new Book();
        $book->title = 'My first book';
        $book->save();

        $book->translator()->add('title', 'Моя перша книга', 'uk');

        self::assertEquals('Моя перша книга', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_retrieves_correct_translation_from_multiple_locales(): void
    {
        $book = new Book();
        $book->title = 'Amazing birds';
        $book->save();

        $book->translator()->add('title', 'Дивовижні птахи', 'uk');
        $book->translator()->add('title', 'Niesamowite ptaki', 'pl');

        self::assertEquals('Niesamowite ptaki', $book->translator()->get('title', 'pl'));
    }

    /** @test */
    public function it_retrieves_correct_translation_from_multiple_attributes(): void
    {
        $book = new Book();
        $book->title = 'Amazing birds';
        $book->save();

        $book->translator()->add('title', 'Дивовижні птахи', 'uk');
        $book->translator()->add('description', 'Як упізнати птаха? Чому він співає?', 'uk');

        self::assertEquals('Дивовижні птахи', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_retrieves_original_value_for_fallback_locale(): void
    {
        $book = new Book();
        $book->title = 'Book about penguins';
        $book->save();

        $book->translator()->add('title', 'Книга про пінгвінів', 'uk');

        self::assertEquals('Book about penguins', $book->translator()->get('title', 'en'));
    }

    /** @test */
    public function it_updates_original_attribute_when_trying_to_translate_attribute_using_fallback_locale(): void
    {
        $book = new Book();
        $book->title = 'Encyclopedia of animals';
        $book->save();

        $book->translator()->add('title', 'Large encyclopedia of animals', 'en');

        self::assertEquals('Large encyclopedia of animals', $book->title);
        self::assertEmpty($book->translations);
    }

    /** @test */
    public function it_throws_exception_when_trying_to_get_missing_translation(): void
    {
        $book = new Book();
        $book->title = 'Large encyclopedia of animals';
        $book->save();

        $this->expectException(TranslationMissingException::class);

        $book->translator()->getOrFail('title', 'uk');
    }

    /** @test */
    public function it_throws_exception_when_trying_to_get_translation_for_non_translatable_attribute(): void
    {
        $book = new Book();
        $book->title = 'Large encyclopedia of animals';
        $book->save();

        $this->expectException(AttributeNotTranslatableException::class);

        $book->translator()->get('created_at');
    }

    /** @test */
    public function it_throws_exception_when_trying_to_add_translation_for_non_translatable_attribute(): void
    {
        $book = new Book();
        $book->title = 'Large encyclopedia of animals';
        $book->save();

        try {
            $book->translator()->add('created_at', now()->setTimezone('Europe/Kiev'), 'uk');
            self::fail('Exception was not thrown for not translatable attribute');
        } catch (AttributeNotTranslatableException $e) {
            $this->assertDatabaseCount('translations', 0);
        }
    }

    /** @test */
    public function it_returns_fallback_value_if_translation_is_missing(): void
    {
        $book = new Book();
        $book->title = 'Atlas of animals';
        $book->save();

        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_returns_null_if_translation_is_nullable(): void
    {
        $book = new Book();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->add('title', null, 'uk');

        self::assertNull($book->translator()->get('title', 'uk'));
    }

    // TODO: probably move to strategy specific test.

    /** @test */
    public function it_saves_translations_to_database(): void
    {
        $book = new Book();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->add('title', 'Атлас тварин', 'uk');

        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->getKey(),
            'translatable_type' => $book->getMorphClass(),
            'translatable_attribute' => 'title',
            'value' => 'Атлас тварин',
            'locale' => 'uk',
        ]);
    }

    // TODO: extract this test into 'caching' group
    /** @test */
    public function it_retrieves_translation_for_different_locale(): void
    {
        $book = new Book();
        $book->title = 'Wind in willows';
        $book->save();

        $book->translator()->add('title', 'Вітер у вербах', 'uk');
        $book->translator()->add('title', 'Wiatr w wierzbach', 'pl');

        self::assertEquals('Вітер у вербах', $book->translator()->get('title', 'uk'));
        self::assertEquals('Wiatr w wierzbach', $book->translator()->get('title', 'pl'));
        self::assertEquals('Wind in willows', $book->title);
    }

    /** @test */
    public function it_overrides_previous_translations(): void
    {
        $book = new Book();
        $book->title = 'The world around us. Wild animals';
        $book->save();

        $book->translator()->add('title', 'Світ навколо нас', 'uk');
        self::assertEquals('Світ навколо нас', $book->translator()->get('title', 'uk'));

        $book->translator()->add('title', 'Світ навколо нас. Дикі тварини', 'uk');
        self::assertEquals('Світ навколо нас. Дикі тварини', $book->translator()->get('title', 'uk'));

        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_store_pending_translations_twice(): void
    {
        $book = new Book();
        $book->title = 'The world around us';
        $book->save();

        $this->app->setLocale('uk');
        $book->translator()->set('title', 'Світ навколо нас', 'uk');
        $book->save();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        $book->save();

        self::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_duplicate_translations(): void
    {
        $book = new Book();
        $book->title = 'The world around us';
        $book->save();

        $this->app->setLocale('uk');

        $book->translator()->add('title', 'Світ навколо нас', 'uk');
        $book->translator()->add('title', 'Світ навколо нас', 'uk');

        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_perform_additional_query_for_fallback_locale(): void
    {
        $book = new Book();
        $book->title = 'Book about penguins';
        $book->save();

        $book->translator()->add('title', 'Книга про пінгвінів', 'uk');

        $this->app[ConnectionInterface::class]->enableQueryLog();

        $translation = $book->translator()->get('title', 'en');

        self::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
        self::assertEquals('Book about penguins', $translation);
    }

    /** @test */
    public function it_returns_translation_during_saving(): void
    {
        Book::saving(function (Book $book) {
            self::assertEquals('The world around us', $book->translator()->get('title', 'en'));
            self::assertEquals('Світ навколо нас', $book->translator()->get('title', 'uk'));
        });

        $book = new Book();
        $book->translator()->set('title', 'The world around us', 'en');
        $book->translator()->set('title', 'Світ навколо нас', 'uk');
        $book->save();
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
class Book extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
