<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class CreationTranslatableModelTest extends TestCase
{
    /** @test */
    public function it_creates_model_in_custom_locale_without_translations(): void
    {
        $this->app->setLocale('ru');

        $book = BookFactory::new()->create([
            'title' => 'My book',
            'description' => 'Book about birds',
            'version' => '1',
        ]);

        $this->assertDatabaseCount('translations', 0);

        $this->assertDatabaseHas($book->getTable(), [
            'title' => 'My book',
            'description' => 'Book about birds',
            'version' => '1',
        ]);
    }
}

/**
 * @property array content
 * @property DateTimeInterface|null published_at
 */
class BookForCreation extends Model
{
    use HasTranslations;

    protected $table = 'articles';

    protected $translatable = [
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
    ];
}
