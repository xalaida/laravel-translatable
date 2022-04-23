<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class ManyTranslationTest extends TestCase
{
    /**
     * Set up the test environment.
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

    // TODO: mix non-translatable attribute

    /** @test */
    public function it_saves_many_translations_to_the_database(): void
    {
        $book = new BookForManyTranslations();
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
    public function it_throws_an_exception_when_trying_to_save_many_translations_with_mixed_non_translatable_attribute(): void
    {
        $book = new BookForManyTranslations();
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
}

/**
 * @property string title
 * @property string|null description
 */
class BookForManyTranslations extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
