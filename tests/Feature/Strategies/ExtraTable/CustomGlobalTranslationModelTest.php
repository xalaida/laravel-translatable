<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Nevadskiy\Translatable\Strategies\ExtraTable\ExtraTableStrategy;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
use Nevadskiy\Translatable\Strategies\ExtraTable\Models\Translation as DefaultTranslation;
use Nevadskiy\Translatable\Tests\TestCase;

class CustomGlobalTranslationModelTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        ExtraTableStrategy::useModel(Translation::class);
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
            $table->uuid('id')->primary();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_use_custom_global_translation_model(): void
    {
        $book = new BookWithCustomTranslationModel();
        $book->translator()->set('title', 'Forest song', 'en');
        $book->translator()->set('title', 'Лісова пісня', 'uk');
        $book->save();

        $this->assertDatabaseHas('book_translations', [
            'title' => 'Forest song',
            'locale' => 'en',
        ]);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Лісова пісня',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_translation_model_does_not_extend_default_translation_model(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ExtraTableStrategy::useModel(InvalidTranslation::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        ExtraTableStrategy::useModel(DefaultTranslation::class);

        $this->schema()->drop('book_translations');
        $this->schema()->drop('books');

        parent::tearDown();
    }
}

/**
 * @property string title
 */
class BookWithCustomTranslationModel extends Model
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

class Translation extends DefaultTranslation
{
    protected static function booted(): void
    {
        static::creating(function (self $translation) {
            $translation->setAttribute($translation->getKeyName(), Str::orderedUuid());
        });
    }
}

class InvalidTranslation extends Model
{

}
