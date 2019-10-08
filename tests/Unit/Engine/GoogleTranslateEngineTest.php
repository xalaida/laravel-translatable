<?php

namespace Nevadskiy\Translatable\Tests\Unit\Engine;

use Nevadskiy\Translatable\Engine\GoogleTranslateEngine;
use Nevadskiy\Translatable\Engine\TranslationException;
use Nevadskiy\Translatable\Tests\TestCase;

class GoogleTranslateEngineTest extends TestCase
{
    /**
     * @test
     * @group api
     */
    public function it_translate_strings_successfully_using_api(): void
    {
        try {
            $translation = (new GoogleTranslateEngine())->translate('Book', 'ru', 'en');
            $this->assertEquals('Книга', $translation);
        } catch (TranslationException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
