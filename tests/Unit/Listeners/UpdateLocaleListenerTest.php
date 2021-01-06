<?php

namespace Nevadskiy\Translatable\Tests\Unit;

use Nevadskiy\Translatable\ModelTranslator;
use Nevadskiy\Translatable\Tests\TestCase;

class UpdateLocaleListenerTest extends TestCase
{
    /** @test */
    public function it_updates_translator_locale_correctly(): void
    {
        $translator = $this->app->make(ModelTranslator::class);
        $originalLocale = $translator->getLocale();

        $this->app->setLocale('ru');

        self::assertEquals('ru', $translator->getLocale());
        $this->assertNotEquals('ru', $originalLocale);
    }
}
