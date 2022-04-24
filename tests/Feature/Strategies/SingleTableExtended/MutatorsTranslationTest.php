<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_mutator_for_translatable_attributes(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Shot on the stairs';
        $book->save();

        $book->translator()->add('title', 'Постріл на сходах', 'uk');

        $this->assertDatabaseHas('translations', [
            'value' => 'Постріл на...',
        ]);
    }

    /** @test */
    public function it_does_not_override_original_attribute_with_mutated_value(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Shot on the stairs';
        $book->save();

        $book->translator()->add('title', 'Постріл на сходах', 'uk');

        self::assertEquals('Shot on th...', $book->translator()->get('title'));
    }

    /** @test */
    public function it_applies_mutator_using_model_setter(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Shot on the stairs';
        $book->save();

        $this->app->setLocale('uk');

        $book->title = 'Постріл на сходах';

        self::assertEquals('Постріл на...', $book->title);
    }

    /** @test */
    public function it_still_applies_mutator_for_non_translatable_attributes(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Shot on the stairs';
        $book->description = 'Detective of the 20s';
        $book->save();

        self::assertEquals('Detective...', $book->description);
    }

    /** @test */
    public function it_applies_mutator_for_translatable_attributes_in_fallback_locale_once(): void
    {
        $book = new BookWithMutators();
        $book->title = 'Forest song';
        $book->caption = 'Ancient forest';
        $book->save();

        self::assertEquals('Ancient forest.', $book->caption);
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
 * @property string description
 * @property string caption
 */
class BookWithMutators extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'caption',
    ];

    public function setTitleAttribute(string $title): void
    {
        $this->attributes['title'] = Str::limit($title, 10);
    }

    public function setDescriptionAttribute(string $description): void
    {
        $this->attributes['description'] = Str::limit($description, 10);
    }

    public function setCaptionAttribute(string $caption): void
    {
        $this->attributes['caption'] = $caption . '.';
    }
}
