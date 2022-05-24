<?php

namespace Nevadskiy\Translatable\Tests\Unit\Strategies\AdditionalTable\Models;

use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation;
use Nevadskiy\Translatable\Tests\TestCase;

class TranslationTest extends TestCase
{
    /**
     * @inheritDoc
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
        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_be_scoped_by_locale(): void
    {
        $translation1 = new Translation();
        $translation1->setTable('book_translations');
        $translation1->title = 'Отак загинув Гуска';
        $translation1->locale = 'uk';
        $translation1->save();

        $translation2 = new Translation();
        $translation2->setTable('book_translations');
        $translation2->title = 'This is how Guska died';
        $translation2->locale = 'en';
        $translation2->save();

        $translations = $this->model()
            ->newQuery()
            ->forLocale('uk')
            ->get();

        self::assertCount(1, $translations);
        self::assertTrue($translations->first()->is($translation1));
    }

    /** @test */
    public function it_can_be_scoped_by_locale_array(): void
    {
        $translation1 = $this->model();
        $translation1->title = 'Отак загинув Гуска';
        $translation1->locale = 'uk';
        $translation1->save();

        $translation2 = $this->model();
        $translation2->title = 'This is how Guska died';
        $translation2->locale = 'en';
        $translation2->save();

        $translation3 = $this->model();
        $translation3->title = 'Tak zginęła Guska';
        $translation3->locale = 'pl';
        $translation3->save();

        $translations = $this->model()
            ->newQuery()
            ->forLocale(['uk', 'pl'])
            ->get();

        self::assertCount(2, $translations);
        self::assertTrue($translations->contains($translation1));
        self::assertTrue($translations->contains($translation3));
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('book_translations');
        parent::tearDown();
    }

    /**
     * Get the translation model instance.
     */
    protected function model(): Translation
    {
        return (new Translation())->setTable('book_translations');
    }
}
