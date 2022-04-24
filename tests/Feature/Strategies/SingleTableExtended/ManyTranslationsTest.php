<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_saves_many_translations_to_database(): void
    {
        $book = new BookWithManyTranslations();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->addMany([
            'title' => 'Атлас тварин',
            'description' => '«Атлас тварин» ‒ унікальне видання про мешканців нашої планети з авторськими ілюстраціями.',
        ], 'uk');

        $this->assertDatabaseCount('translations', 2);
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->getKey(),
            'translatable_type' => $book->getMorphClass(),
            'translatable_attribute' => 'title',
            'value' => 'Атлас тварин',
            'locale' => 'uk',
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->getKey(),
            'translatable_type' => $book->getMorphClass(),
            'translatable_attribute' => 'description',
            'value' => '«Атлас тварин» ‒ унікальне видання про мешканців нашої планети з авторськими ілюстраціями.',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_trying_to_save_many_translations_with_mixed_non_translatable_attribute(): void
    {
        $book = new BookWithManyTranslations();
        $book->title = 'Atlas of animals';
        $book->save();

        try {
            $book->translator()->addMany([
                'title' => 'Атлас тварин',
                'created_at' => now()->setTimezone('Europe/Kiev'),
            ], 'uk');
            self::fail('Exception was not thrown for not translatable attribute');
        } catch (AttributeNotTranslatableException $e) {
            $this->assertDatabaseCount('translations', 0);
        }
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
class BookWithManyTranslations extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
