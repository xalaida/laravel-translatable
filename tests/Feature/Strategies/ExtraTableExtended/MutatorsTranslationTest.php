<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class MutatorsTranslationTest extends TestCase
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

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_mutator_for_translatable_attributes(): void
    {
        $book = new BookWithMutators();
        $book->translator()->set('title', 'Shot on the stairs', 'en');
        $book->translator()->set('title', 'Постріл на сходах', 'uk');
        $book->save();

        $this->assertDatabaseHas('books', ['title' => 'Shot on the stairs.']);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Постріл на сходах.',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_applies_mutator_using_model_setter(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Shot on the stairs';
        $book->save();

        $this->app->setLocale('uk');
        $book->title = 'Постріл на сходах';

        static::assertSame('Постріл на сходах.', $book->title);
    }

    /** @test */
    public function it_still_applies_mutator_for_non_translatable_attributes(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Shot on the stairs';
        $book->description = 'Detective of the 20s';
        $book->save();

        static::assertSame('Detective...', $book->description);
    }

    /** @test */
    public function it_applies_mutator_for_translatable_attributes_in_fallback_locale_only_once(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Forest song';
        $book->save();

        static::assertSame('Forest song.', $book->title);
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
class BookWithMutators extends Model
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

    public function setTitleAttribute(string $title): void
    {
        $this->attributes['title'] = $title . '.';
    }

    public function setDescriptionAttribute(string $description): void
    {
        $this->attributes['description'] = Str::limit($description, 10);
    }
}
