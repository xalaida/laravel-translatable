<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class ManyTranslationsTest extends TestCase
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
            $table->string('description');
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('description');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_saves_many_translations_to_database_at_once(): void
    {
        $book = new BookWithManyTranslations();
        $book->translator()->setMany([
            'title' => 'Atlas of animals',
            'description' => '"Atlas of Animals" - a unique publication about the inhabitants of our planet with author\'s illustrations.',
        ]);
        $book->save();

        $book->translator()->addMany([
            'title' => 'Атлас тварин',
            'description' => '«Атлас тварин» ‒ унікальне видання про мешканців нашої планети з авторськими ілюстраціями.',
        ], 'uk');

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'id' => $book->getKey(),
            'title' => 'Atlas of animals',
            'description' => '"Atlas of Animals" - a unique publication about the inhabitants of our planet with author\'s illustrations.',
        ]);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'book_id' => $book->getKey(),
            'title' => 'Атлас тварин',
            'description' => '«Атлас тварин» ‒ унікальне видання про мешканців нашої планети з авторськими ілюстраціями.',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_trying_to_save_many_translations_with_mixed_non_translatable_attribute(): void
    {
        $book = new BookWithManyTranslations();
        $book->translator()->setMany([
            'title' => 'Atlas of animals',
            'description' => '"Atlas of Animals" - a unique publication about the inhabitants of our planet with author\'s illustrations.',
        ]);
        $book->save();

        try {
            $book->translator()->addMany([
                'title' => 'Атлас тварин',
                'created_at' => now()->setTimezone('Europe/Kiev'),
            ], 'uk');
            self::fail('Exception was not thrown for not translatable attribute');
        } catch (AttributeNotTranslatableException $e) {
            $this->assertDatabaseCount('book_translations', 0);
        }
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
 * @property string description
 */
class BookWithManyTranslations extends Model
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
