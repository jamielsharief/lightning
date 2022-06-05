<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use function Lightning\Translator\__;

use Lightning\Translator\TranslatorManager;
use Lightning\Translator\ResourceBundleFactory;

final class FunctionsTest extends TestCase
{
    public function setUp(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');
        TranslatorManager::set($translator);
    }

    public function tearDown(): Void
    {
        TranslatorManager::unset();
    }

    public function testTranslate(): void
    {
        $this->assertEquals('¡Hola Mundo!', __('Hello world!'));
        $this->assertEquals('Tienes 5 mensaje(s)', __('You have {count} messages', ['count' => 5]));
        $this->assertEquals('no string', __('no string'));
        $this->assertEquals('', __(null));
    }
}
