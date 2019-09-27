<?php

namespace Nevadskiy\Translatable\Tests\Unit\Engine;

use Nevadskiy\Translatable\Engine\GoogleTranslateEngine;
use Nevadskiy\Translatable\Tests\TestCase;

class GoogleTranslateEngineTest extends TestCase
{
    /**
     * @test
     * @group api
     */
    public function it_translate_strings_successfully_using_api(): void
    {
        $this->assertEquals('Книга', (new GoogleTranslateEngine())->translate('Book', 'ru', 'en'));
    }
}
